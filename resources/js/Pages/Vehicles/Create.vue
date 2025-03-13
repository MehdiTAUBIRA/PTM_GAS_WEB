<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    drivers: Array,
    depots: Array
});

const form = useForm({
    vehiclecode: '',
    vehiclename: '',
    vehicleimmat: '',
    vehiclelastcontroldate: '',
    vehiclenextcontroldate: '',
    vehicletotalcapacity: '',
    vehiclecontractnumber: '',
    vehiclecontractlabel: '',
    vehiclecontractdatestart: '',
    vehiclecontractdateend: '',
    vehicleweigth: '',
    vehiclemaxweigth: '',
    id_driver: '',
    id_depot: ''
});

const submit = () => {
    form.post(route('vehicles.store'));
};
</script>

<template>
    <Head title="Créer un véhicule" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un véhicule
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code véhicule</label>
                                <input 
                                    type="text" 
                                    v-model="form.vehiclecode" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    maxlength="20"
                                >
                                <div v-if="form.errors.vehiclecode" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclecode }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom du véhicule</label>
                                <input 
                                    type="text" 
                                    v-model="form.vehiclename" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclename" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclename }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Immatriculation</label>
                                <input 
                                    type="text" 
                                    v-model="form.vehicleimmat" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehicleimmat" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehicleimmat }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dernier contrôle</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.vehiclelastcontroldate" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclelastcontroldate" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclelastcontroldate }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prochain contrôle</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.vehiclenextcontroldate" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclenextcontroldate" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclenextcontroldate }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Capacité totale (kg)</label>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    v-model="form.vehicletotalcapacity" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehicletotalcapacity" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehicletotalcapacity }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Numéro de contrat</label>
                                <input 
                                    type="text" 
                                    v-model="form.vehiclecontractnumber" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclecontractnumber" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclecontractnumber }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Libellé du contrat</label>
                                <input 
                                    type="text" 
                                    v-model="form.vehiclecontractlabel" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclecontractlabel" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclecontractlabel }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Début du contrat</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.vehiclecontractdatestart" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclecontractdatestart" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclecontractdatestart }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fin du contrat</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.vehiclecontractdateend" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclecontractdateend" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclecontractdateend }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Poids (kg)</label>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    v-model="form.vehicleweigth" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehicleweigth" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehicleweigth }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Poids maximum (kg)</label>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    v-model="form.vehiclemaxweigth" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.vehiclemaxweigth" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.vehiclemaxweigth }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Chauffeur</label>
                                <select 
                                    v-model="form.id_driver" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Sélectionnez un chauffeur</option>
                                    <option v-for="driver in drivers" :key="driver.id_driver" :value="driver.id_driver">
                                        {{ driver.drivername }} {{ driver.driversurname }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_driver" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.id_driver }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dépôt</label>
                                <select 
                                    v-model="form.id_depot" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Sélectionnez un dépôt</option>
                                    <option v-for="depot in depots" :key="depot.id_depot" :value="depot.id_depot">
                                        {{ depot.depotlabel }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_depot" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.id_depot }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button 
                                type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Création...' : 'Créer le véhicule' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>