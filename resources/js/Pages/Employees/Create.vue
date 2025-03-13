<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    depots: Array,
    roles: Object
});

const form = useForm({
    employee_code: '',
    employee_firstname: '',
    employee_lastname: '',
    employee_email: '',
    employee_phone: '',
    employee_role: '',
    id_depot: '',
    hire_date: new Date().toISOString().split('T')[0],
    username: '',
    password: ''
});

const submit = () => {
    form.post(route('employees.store'));
};
</script>

<template>
    <Head title="Créer un employé" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un employé
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code employé</label>
                                <input 
                                    type="text" 
                                    v-model="form.employee_code" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.employee_code" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.employee_code }}
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input 
                                    type="text" 
                                    v-model="form.employee_lastname" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.employee_lastname" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.employee_lastname }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input 
                                    type="text" 
                                    v-model="form.employee_firstname" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.employee_firstname" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.employee_firstname }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input 
                                    type="email" 
                                    v-model="form.employee_email" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.employee_email" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.employee_email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input 
                                    type="text" 
                                    v-model="form.employee_phone" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.employee_phone" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.employee_phone }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Rôle</label>
                                <select 
                                    v-model="form.employee_role" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Sélectionnez un rôle</option>
                                    <option v-for="(label, role) in roles" :key="role" :value="role">
                                        {{ label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.employee_role" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.employee_role }}
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

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date d'embauche</label>
                                <input 
                                    type="date" 
                                    v-model="form.hire_date" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.hire_date" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.hire_date }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                                <input 
                                    type="text" 
                                    v-model="form.username" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.username }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                                <input 
                                    type="password" 
                                    v-model="form.password" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.password }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button 
                                type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Création...' : 'Créer l\'employé' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>