<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    damagedProducts: Array
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const getStateLabel = (state) => {
    const states = {
        'D': 'Endommagé',
        'R': 'En réparation',
        'N': 'Neuf',
        'U': 'Utilisé',
        'O': 'Hors service'
    };
    return states[state] || state;
};

const getStateClass = (state) => {
    const classes = {
        'D': 'bg-red-100 text-red-800',
        'R': 'bg-yellow-100 text-yellow-800',
        'N': 'bg-green-100 text-green-800',
        'U': 'bg-blue-100 text-blue-800',
        'O': 'bg-gray-100 text-gray-800'
    };
    return classes[state] || 'bg-gray-100 text-gray-800';
};

const getMaintenanceStatusLabel = (status) => {
    const statuses = {
        'planned': 'Planifiée',
        'in_progress': 'En cours',
        'completed': 'Terminée',
        'cancelled': 'Annulée'
    };
    return statuses[status] || status;
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

const getMaintenanceTypeLabel = (type) => {
    const types = {
        'inspection': 'Inspection',
        'test': 'Test',
        'repair': 'Réparation',
        'certification': 'Certification'
    };
    return types[type] || type;
};
</script>

<template>
    <Head title="Produits endommagés" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Produits endommagés ou en réparation
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-if="damagedProducts.length === 0" class="text-center py-8 text-gray-500">
                            Aucun produit endommagé ou en réparation.
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produit
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            N° Série
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            État
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date de fabrication
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Maintenance
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut maintenance
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date prévue
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in damagedProducts" :key="product.id_product_attribut" :class="product.state === 'D' ? 'bg-red-50' : 'bg-yellow-50'">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ product.prolibcommercial }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ product.serial_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStateClass(product.state)">
                                                {{ getStateLabel(product.state) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(product.manufacture_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span v-if="product.maintenance">
                                                {{ getMaintenanceTypeLabel(product.maintenance.maintenance_type) }}
                                            </span>
                                            <span v-else class="text-gray-500">
                                                Non planifiée
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="product.maintenance" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getMaintenanceStatusClass(product.maintenance.status)">
                                                {{ getMaintenanceStatusLabel(product.maintenance.status) }}
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ product.maintenance ? formatDate(product.maintenance.planned_date) : '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>