<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch } from 'vue';
import _ from 'lodash';

const props = defineProps({
    vehicles: Object
});

const search = ref('');
const showModalView = ref(false);
const showModalDel = ref(false);
const selectedVehicle = ref(null);

const performSearch = _.debounce((value) => {
    router.get(route('vehicles.index'), 
        { search: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}, 300);

watch(search, (value) => {
    performSearch(value);
});

const openModalView = (vehicle) => {
    selectedVehicle.value = vehicle;
    showModalView.value = true;
}

const openModalDel = (vehicle) => {
    selectedVehicle.value = vehicle;
    showModalDel.value = true;
}

const closeModalView = () => {
    showModalView.value = false;
    selectedVehicle.value = null;
}

const closeModalDel = () => {
    showModalDel.value = false;
    selectedVehicle.value = null;
}

const deleteVehicle = () => {
    router.delete(route('vehicles.destroy', selectedVehicle.value.id_vehicle), {
        onSuccess: () => closeModalDel()
    });
}

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString('fr-FR') : '-';
}
</script>

<template>
    <Head title="Véhicules" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des véhicules
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Barre de recherche -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            v-model="search"
                            class="block w-full p-2 pl-10 text-sm border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Rechercher un véhicule..."
                        >
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full whitespace-no-wrap">
                            <thead>
                                <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Nom</th>
                                    <th class="px-4 py-3">Immatriculation</th>
                                    <th class="px-4 py-3">Chauffeur</th>
                                    <th class="px-4 py-3">Dépôt</th>
                                    <th class="px-4 py-3">Prochain contrôle</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y">
                                <tr v-for="vehicle in vehicles.data" :key="vehicle.id_vehicle" class="text-gray-700">
                                    <td class="px-4 py-3 text-sm">{{ vehicle.vehiclecode }}</td>
                                    <td class="px-4 py-3 text-sm">{{ vehicle.vehiclename }}</td>
                                    <td class="px-4 py-3 text-sm">{{ vehicle.vehicleimmat }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ vehicle.driver ? `${vehicle.driver.drivername} ${vehicle.driver.driversurname}` : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ vehicle.depot?.depotlabel || '-' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ formatDate(vehicle.vehiclenextcontroldate) }}</td>
                                    <td class="px-4 py-3 text-sm space-x-2">
                                        <SecondaryButton @click="openModalView(vehicle)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </SecondaryButton>
                                        <DangerButton @click="openModalDel(vehicle)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </DangerButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="vehicles.links" class="mt-4">
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <!-- ... Pagination component ... -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Voir -->
        <Modal :show="showModalView" @close="closeModalView">
            <div class="p-6" v-if="selectedVehicle">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Détails du véhicule
                </h2>
                <div class="space-y-2">
                    <p>Code: <span class="font-medium">{{ selectedVehicle.vehiclecode }}</span></p>
                    <p>Nom: <span class="font-medium">{{ selectedVehicle.vehiclename }}</span></p>
                    <p>Immatriculation: <span class="font-medium">{{ selectedVehicle.vehicleimmat }}</span></p>
                    <p>Dernier contrôle: <span class="font-medium">{{ formatDate(selectedVehicle.vehiclelastcontroldate) }}</span></p>
                    <p>Prochain contrôle: <span class="font-medium">{{ formatDate(selectedVehicle.vehiclenextcontroldate) }}</span></p>
                    <p>Capacité totale: <span class="font-medium">{{ selectedVehicle.vehicletotalcapacity }} kg</span></p>
                    <p>Poids: <span class="font-medium">{{ selectedVehicle.vehicleweigth }} kg</span></p>
                    <p>Poids maximum: <span class="font-medium">{{ selectedVehicle.vehiclemaxweigth }} kg</span></p>
                    <template v-if="selectedVehicle.vehiclecontractnumber">
                        <p>Numéro de contrat: <span class="font-medium">{{ selectedVehicle.vehiclecontractnumber }}</span></p>
                        <p>Libellé du contrat: <span class="font-medium">{{ selectedVehicle.vehiclecontractlabel }}</span></p>
                        <p>Début du contrat: <span class="font-medium">{{ formatDate(selectedVehicle.vehiclecontractdatestart) }}</span></p>
                        <p>Fin du contrat: <span class="font-medium">{{ formatDate(selectedVehicle.vehiclecontractdateend) }}</span></p>
                    </template>
                    <p>Chauffeur: <span class="font-medium">{{ selectedVehicle.driver ? `${selectedVehicle.driver.drivername} ${selectedVehicle.driver.driversurname}` : '-' }}</span></p>
                    <p>Dépôt: <span class="font-medium">{{ selectedVehicle.depot?.depotlabel || '-' }}</span></p>
                </div>
            </div>
            <div class="p-6 bg-gray-100 flex justify-end">
                <SecondaryButton @click="closeModalView">Fermer</SecondaryButton>
            </div>
        </Modal>

        <!-- Modal Supprimer -->
        <Modal :show="showModalDel" @close="closeModalDel">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Confirmation de suppression
                </h2>
                <p class="mb-4">Êtes-vous sûr de vouloir supprimer ce véhicule ?</p>
            </div>
            <div class="p-6 bg-gray-100 flex justify-end space-x-2">
                <SecondaryButton @click="closeModalDel">Annuler</SecondaryButton>
                <DangerButton @click="deleteVehicle">Supprimer</DangerButton>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>