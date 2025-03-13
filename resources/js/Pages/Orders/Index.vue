<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, watch } from 'vue';
import _ from 'lodash';

const props = defineProps({
    orders: Object,
    statuses: Object
});

const search = ref('');
const statusFilter = ref('');

const performSearch = _.debounce((value) => {
    router.get(route('orders.index'), 
        { 
            search: value,
            status: statusFilter.value
        },
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

watch(statusFilter, (value) => {
    performSearch(search.value);
});

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString('fr-FR') : '-';
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'in_delivery': 'bg-indigo-100 text-indigo-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Commandes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des commandes
                </h2>
                <Link 
                    :href="route('orders.create')"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                >
                    Nouvelle commande
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="mb-4 flex gap-4">
                    <div class="flex-1">
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
                                placeholder="Rechercher une commande..."
                            >
                        </div>
                    </div>
                    <div class="w-64">
                        <select 
                            v-model="statusFilter"
                            class="block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Tous les statuts</option>
                            <option 
                                v-for="(label, value) in statuses" 
                                :key="value" 
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livraison</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders.data" :key="order.id_order">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ order.order_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ order.customer.customer_name }} {{ order.customer.customer_firstname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.order_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.delivery_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ order.total_amount }}€</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(order.status)]">
                                            {{ statuses[order.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link 
                                            :href="route('orders.show', order.id_order)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Détails
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links" class="mt-4">
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <!-- ... Pagination component ... -->
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>