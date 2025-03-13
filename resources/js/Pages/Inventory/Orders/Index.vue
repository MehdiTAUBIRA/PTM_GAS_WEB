<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import _ from 'lodash';

const props = defineProps({
    inventoryOrders: Object,
    depots: Array,
    employees: Array,
    statuses: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const depotFilter = ref(props.filters.depot_id || '');
const employeeFilter = ref(props.filters.employee_id || '');

const performSearch = _.debounce((value) => {
    router.get(route('inventory-orders.index'), 
        { 
            search: value,
            status: statusFilter.value,
            depot_id: depotFilter.value,
            employee_id: employeeFilter.value
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

watch([statusFilter, depotFilter, employeeFilter], () => {
    performSearch(search.value);
});

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString('fr-FR') : '-';
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Ordres d'Inventaire" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des ordres d'inventaire
                </h2>
                <Link 
                    :href="route('inventory-orders.create')"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                >
                    Nouvel ordre d'inventaire
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="mb-4 flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
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
                                placeholder="Rechercher un ordre..."
                            >
                        </div>
                    </div>
                    <div class="w-40">
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
                    <div class="w-48">
                        <select 
                            v-model="depotFilter"
                            class="block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Tous les dépôts</option>
                            <option 
                                v-for="depot in depots" 
                                :key="depot.id_depot" 
                                :value="depot.id_depot"
                            >
                                {{ depot.depot_name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-48">
                        <select 
                            v-model="employeeFilter"
                            class="block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Tous les employés</option>
                            <option 
                                v-for="employee in employees" 
                                :key="employee.id_employee" 
                                :value="employee.id_employee"
                            >
                                {{ employee.lastname }} {{ employee.firstname }}
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dépôt</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date de l'ordre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créé par</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigné à</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in inventoryOrders.data" :key="order.id_inventory_order">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ order.id_inventory_order }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ order.depot.depot_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.order_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ order.creator.lastname }} {{ order.creator.firstname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ order.assignedEmployee.lastname }} {{ order.assignedEmployee.firstname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(order.status)]">
                                            {{ statuses[order.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                        <Link 
                                            :href="route('inventory-orders.show', order.id_inventory_order)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Détails
                                        </Link>
                                        <Link 
                                            v-if="order.status === 'pending'"
                                            :href="route('inventory-orders.edit', order.id_inventory_order)"
                                            class="text-green-600 hover:text-green-900 ml-2"
                                        >
                                            Modifier
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="inventoryOrders.data.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Aucun ordre d'inventaire trouvé
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="inventoryOrders.links" class="mt-4">
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Affichage de
                                    <span class="font-medium">{{ inventoryOrders.from }}</span>
                                    à
                                    <span class="font-medium">{{ inventoryOrders.to }}</span>
                                    sur
                                    <span class="font-medium">{{ inventoryOrders.total }}</span>
                                    résultats
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in inventoryOrders.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold"
                                        :class="{ 
                                            'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600': link.active,
                                            'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0': !link.active 
                                        }"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>