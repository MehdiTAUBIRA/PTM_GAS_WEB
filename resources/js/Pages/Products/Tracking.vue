<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    movements: Object,
    maintenances: Object,
    filters: Object,
    movementTypes: Object,
    statuses: Object,
    maintenanceTypes: Object,
    maintenanceStatuses: Object
});

const activeTab = ref('movements');

const form = useForm({
    search: props.filters?.search || '',
    movement_type: props.filters?.movement_type || '',
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || ''
});

const applyFilters = () => {
    form.get(route('product-tracking.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.search = '';
    form.movement_type = '';
    form.status = '';
    form.date_from = '';
    form.date_to = '';
    applyFilters();
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getMovementTypeClass = (type) => {
    const classes = {
        'delivery': 'bg-green-100 text-green-800',
        'return': 'bg-blue-100 text-blue-800',
        'transfer': 'bg-yellow-100 text-yellow-800',
        'maintenance': 'bg-purple-100 text-purple-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const getResultClass = (result) => {
    const classes = {
        'passed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'needs_repair': 'bg-yellow-100 text-yellow-800'
    };
    return classes[result] || 'bg-gray-100 text-gray-800';
};

const getMaintenanceStatusClass = (status) => {
    const classes = {
        'planned': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Suivi des Produits" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Suivi des Produits
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <form @submit.prevent="applyFilters">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="search" value="Recherche (Code barre/N° série)" />
                                <TextInput
                                    id="search"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.search"
                                />
                                <InputError :message="form.errors.search" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="movement_type" value="Type de mouvement" />
                                <select
                                    id="movement_type"
                                    v-model="form.movement_type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                >
                                    <option value="">Tous</option>
                                    <option 
                                        v-for="(label, value) in movementTypes" 
                                        :key="value" 
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.movement_type" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="status" value="Statut" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                >
                                    <option value="">Tous</option>
                                    <option 
                                        v-for="(label, value) in statuses" 
                                        :key="value" 
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <InputLabel for="date_from" value="Date de début" />
                                <TextInput
                                    id="date_from"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.date_from"
                                />
                                <InputError :message="form.errors.date_from" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="date_to" value="Date de fin" />
                                <TextInput
                                    id="date_to"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.date_to"
                                />
                                <InputError :message="form.errors.date_to" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6">
                            <SecondaryButton
                                type="button"
                                @click="resetFilters"
                                class="mr-3"
                            >
                                Réinitialiser
                            </SecondaryButton>
                            <PrimaryButton 
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing"
                            >
                                Filtrer
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                
                <!-- Onglets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button 
                                @click="activeTab = 'movements'" 
                                class="px-6 py-4 text-center border-b-2 font-medium text-sm"
                                :class="activeTab === 'movements' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Mouvements
                            </button>
                            <button 
                                @click="activeTab = 'maintenance'" 
                                class="px-6 py-4 text-center border-b-2 font-medium text-sm"
                                :class="activeTab === 'maintenance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Maintenance
                            </button>
                        </nav>
                    </div>
                </div>
                
                <!-- Tableau des mouvements -->
                <div v-if="activeTab === 'movements'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produit
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            N° Série
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Origine
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Destination
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="movement in movements.data" :key="movement.id_movement">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(movement.movement_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.product_attribute?.product?.prolibcommercial || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.product_attribute?.serial_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getMovementTypeClass(movement.movement_type)">
                                                {{ movementTypes[movement.movement_type] || movement.movement_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.source_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ movement.destination_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(movement.status)">
                                                {{ statuses[movement.status] || movement.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('product-tracking.history', movement.id_product_attribut)" class="text-indigo-600 hover:text-indigo-900">
                                                Détails
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="movements.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Aucun mouvement trouvé
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="movements.links" class="mt-6" />
                    </div>
                </div>
                
                <!-- Tableau des maintenances -->
                <div v-if="activeTab === 'maintenance'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date planifiée
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date réalisée
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produit
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            N° Série
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Résultat
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="maintenance in maintenances.data" :key="maintenance.id_maintenance">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(maintenance.planned_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ maintenance.actual_date ? formatDate(maintenance.actual_date) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ maintenance.product_attribute?.product?.prolibcommercial || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ maintenance.product_attribute?.serial_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ maintenanceTypes[maintenance.maintenance_type] || maintenance.maintenance_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="maintenance.maintenance_result" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getResultClass(maintenance.maintenance_result)">
                                                {{ maintenance.maintenance_result === 'passed' ? 'Passé' : 
                                                   maintenance.maintenance_result === 'failed' ? 'Échoué' : 
                                                   maintenance.maintenance_result === 'needs_repair' ? 'Nécessite réparation' : 
                                                   maintenance.maintenance_result }}
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getMaintenanceStatusClass(maintenance.status)">
                                                {{ maintenanceStatuses[maintenance.status] || maintenance.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('product-tracking.history', maintenance.id_product_attribut)" class="text-indigo-600 hover:text-indigo-900">
                                                Détails
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="maintenances.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Aucune maintenance trouvée
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="maintenances.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>