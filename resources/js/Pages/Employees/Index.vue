<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    employees: Object
});

const search = ref('');
const showModalView = ref(false);
const showModalDel = ref(false);
const selectedEmployee = ref(null);

const performSearch = debounce((value) => {
    router.get(route('employees.index'), 
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

const openModalView = (employee) => {
    selectedEmployee.value = employee;
    showModalView.value = true;
}

const openModalDel = (employee) => {
    selectedEmployee.value = employee;
    showModalDel.value = true;
}

const closeModalView = () => {
    showModalView.value = false;
    selectedEmployee.value = null;
}

const closeModalDel = () => {
    showModalDel.value = false;
    selectedEmployee.value = null;
}

const deleteEmployee = () => {
    router.delete(route('employees.destroy', selectedEmployee.value.id_employee), {
        onSuccess: () => closeModalDel()
    });
}

const getRoleLabel = (role) => {
    const roles = {
        'admin': 'Administrateur',
        'manager': 'Manager',
        'inventory_clerk': 'Gestionnaire de stock',
        'warehouse_worker': 'Employé d\'entrepôt'
    };
    return roles[role] || role;
}
</script>

<template>
    <Head title="Employés" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des employés
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
                            placeholder="Rechercher un employé..."
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
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Rôle</th>
                                    <th class="px-4 py-3">Dépôt</th>
                                    <th class="px-4 py-3">Statut</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y">
                                <tr v-for="employee in employees.data" :key="employee.id_employee" class="text-gray-700">
                                    <td class="px-4 py-3 text-sm">{{ employee.employee_code }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ employee.employee_lastname }} {{ employee.employee_firstname }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ employee.employee_email }}</td>
                                    <td class="px-4 py-3 text-sm">{{ getRoleLabel(employee.employee_role) }}</td>
                                    <td class="px-4 py-3 text-sm">{{ employee.depot?.depotlabel }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span 
                                            :class="employee.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 py-1 rounded-full text-xs"
                                        >
                                            {{ employee.status ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm space-x-2">
                                        <SecondaryButton @click="openModalView(employee)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </SecondaryButton>
                                        <DangerButton 
                                            v-if="employee.status"
                                            @click="openModalDel(employee)"
                                        >
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
                <div v-if="employees.links" class="mt-4">
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <!-- ... Pagination component ... -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Voir -->
        <Modal :show="showModalView" @close="closeModalView">
            <div class="p-6" v-if="selectedEmployee">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Détails de l'employé
                </h2>
                <div class="space-y-2">
                    <p>Code: <span class="font-medium">{{ selectedEmployee.employee_code }}</span></p>
                    <p>Nom: <span class="font-medium">{{ selectedEmployee.employee_lastname }}</span></p>
                    <p>Prénom: <span class="font-medium">{{ selectedEmployee.employee_firstname }}</span></p>
                    <p>Email: <span class="font-medium">{{ selectedEmployee.employee_email }}</span></p>
                    <p>Téléphone: <span class="font-medium">{{ selectedEmployee.employee_phone }}</span></p>
                    <p>Rôle: <span class="font-medium">{{ getRoleLabel(selectedEmployee.employee_role) }}</span></p>
                    <p>Dépôt: <span class="font-medium">{{ selectedEmployee.depot?.depotlabel }}</span></p>
                    <p>Date d'embauche: <span class="font-medium">{{ selectedEmployee.hire_date }}</span></p>
                    <p>Statut: <span class="font-medium">{{ selectedEmployee.status ? 'Actif' : 'Inactif' }}</span></p>
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
                    Confirmation de désactivation
                </h2>
                <p class="mb-4">Êtes-vous sûr de vouloir désactiver cet employé ?</p>
            </div>
            <div class="p-6 bg-gray-100 flex justify-end space-x-2">
                <SecondaryButton @click="closeModalDel">Annuler</SecondaryButton>
                <DangerButton @click="deleteEmployee">Désactiver</DangerButton>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>