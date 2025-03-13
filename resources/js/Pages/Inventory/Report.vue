<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import _ from 'lodash';

const props = defineProps({
    orders: Array,
    stats: Object,
    depots: Array,
    filters: Object
});

const depotFilter = ref(props.filters.depot_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const performFilter = _.debounce(() => {
    router.get(route('inventory.report'), 
        { 
            depot_id: depotFilter.value,
            start_date: startDate.value,
            end_date: endDate.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}, 300);

watch([depotFilter, startDate, endDate], () => {
    performFilter();
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

const getStatusText = (status) => {
    const texts = {
        'pending': 'En attente',
        'in_progress': 'En cours',
        'completed': 'Terminé',
        'cancelled': 'Annulé'
    };
    return texts[status] || status;
};

const getDifferenceClass = (difference) => {
    if (!difference) return '';
    return difference < 0 ? 'text-red-600' : (difference > 0 ? 'text-green-600' : '');
};

// Initialiser les charts
let productChartRendered = false;
let depotChartRendered = false;
let historyChartRendered = false;

onMounted(() => {
    renderCharts();
});

watch(() => props.stats, () => {
    setTimeout(() => {
        renderCharts();
    }, 100);
}, { deep: true });

const renderCharts = () => {
    if (window.Chart) {
        renderProductDifferencesChart();
        renderDepotDifferencesChart();
        renderInventoryHistoryChart();
    }
};

const renderProductDifferencesChart = () => {
    if (!props.stats.product_differences || props.stats.product_differences.length === 0) return;
    
    const ctx = document.getElementById('productDifferencesChart');
    if (!ctx) return;
    
    if (productChartRendered) {
        return;
    }
    
    productChartRendered = true;
    
    const labels = props.stats.product_differences.map(p => p.product_reference);
    const data = props.stats.product_differences.map(p => p.total_difference);
    const colors = data.map(val => val < 0 ? 'rgba(239, 68, 68, 0.7)' : 'rgba(34, 197, 94, 0.7)');
    
    new window.Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Différence totale',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            const idx = tooltipItems[0].dataIndex;
                            return props.stats.product_differences[idx].product_name;
                        }
                    }
                }
            }
        }
    });
};

const renderDepotDifferencesChart = () => {
    if (!props.stats.depot_differences || props.stats.depot_differences.length === 0) return;
    
    const ctx = document.getElementById('depotDifferencesChart');
    if (!ctx) return;
    
    if (depotChartRendered) {
        return;
    }
    
    depotChartRendered = true;
    
    const labels = props.stats.depot_differences.map(d => d.depot_name);
    const data = props.stats.depot_differences.map(d => d.total_difference);
    
    new window.Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(236, 72, 153, 0.7)',
                    'rgba(248, 113, 113, 0.7)',
                    'rgba(251, 146, 60, 0.7)',
                    'rgba(251, 191, 36, 0.7)',
                    'rgba(163, 230, 53, 0.7)',
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(99, 102, 241, 1)',
                    'rgba(139, 92, 246, 1)',
                    'rgba(236, 72, 153, 1)',
                    'rgba(248, 113, 113, 1)',
                    'rgba(251, 146, 60, 1)',
                    'rgba(251, 191, 36, 1)',
                    'rgba(163, 230, 53, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return `${label}: ${value} unités`;
                        }
                    }
                }
            }
        }
    });
};

const renderInventoryHistoryChart = () => {
    if (!props.stats.inventory_history || props.stats.inventory_history.length === 0) return;
    
    const ctx = document.getElementById('inventoryHistoryChart');
    if (!ctx) return;
    
    if (historyChartRendered) {
        return;
    }
    
    historyChartRendered = true;
    
    const labels = props.stats.inventory_history.map(h => {
        const [year, month] = h.month.split('-');
        return `${month}/${year}`;
    });
    const data = props.stats.inventory_history.map(h => h.count);
    
    new window.Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nombre d\'inventaires',
                data: data,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
};
</script>

<template>
    <Head title="Rapports d'inventaire" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rapports d'inventaire
                </h2>
                <Link 
                    :href="route('inventory.index')"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                >
                    État de l'inventaire
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Filtres</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dépôt</label>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                            <input 
                                type="date" 
                                v-model="startDate"
                                class="block w-full rounded-md border-gray-300 shadow-sm"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                            <input 
                                type="date" 
                                v-model="endDate"
                                class="block w-full rounded-md border-gray-300 shadow-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Statistiques générales -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Statistiques générales</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-blue-600">Total des ordres</p>
                                <p class="text-2xl font-bold mt-1">{{ stats.total_orders }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-green-600">Total des produits inventoriés</p>
                                <p class="text-2xl font-bold mt-1">{{ stats.total_products }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Produits avec les plus grandes différences -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Top produits par différence d'inventaire</h3>
                        <div class="h-64">
                            <canvas id="productDifferencesChart"></canvas>
                        </div>
                        <div class="mt-4 max-h-64 overflow-y-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Référence</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Produit</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Différence</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Moyenne</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Inventaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in stats.product_differences" :key="product.id_product">
                                        <td class="px-2 py-2 text-sm">{{ product.product_reference }}</td>
                                        <td class="px-2 py-2 text-sm">{{ product.product_name }}</td>
                                        <td class="px-2 py-2 text-sm text-right" :class="getDifferenceClass(product.total_difference)">
                                            {{ product.total_difference }}
                                        </td>
                                        <td class="px-2 py-2 text-sm text-right" :class="getDifferenceClass(product.avg_difference)">
                                            {{ Math.round(product.avg_difference * 10) / 10 }}
                                        </td>
                                        <td class="px-2 py-2 text-sm text-right">{{ product.count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Dépôts avec le plus de différences -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Différences par dépôt</h3>
                        <div class="h-64">
                            <canvas id="depotDifferencesChart"></canvas>
                        </div>
                        <div class="mt-4 max-h-64 overflow-y-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Dépôt</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Différence totale</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Produits</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Inventaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="depot in stats.depot_differences" :key="depot.id_depot">
                                        <td class="px-2 py-2 text-sm">{{ depot.depot_name }}</td>
                                        <td class="px-2 py-2 text-sm text-right" :class="getDifferenceClass(depot.total_difference)">
                                            {{ depot.total_difference }}
                                        </td>
                                        <td class="px-2 py-2 text-sm text-right">{{ depot.total_products }}</td>
                                        <td class="px-2 py-2 text-sm text-right">{{ depot.count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Historique des inventaires -->
                <div class="mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Historique des inventaires</h3>
                        <div class="h-64">
                            <canvas id="inventoryHistoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Ordres d'inventaire récents -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Ordres d'inventaire sur la période</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dépôt</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date de complétion</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Réalisé par</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Différence totale</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="order in orders" :key="order.id_inventory_order">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ order.id_inventory_order }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ order.depot.depot_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.completion_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ order.assignedEmployee.lastname }} {{ order.assignedEmployee.firstname }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">{{ order.details.length }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center" :class="getDifferenceClass(order.details.reduce((sum, detail) => sum + (detail.difference || 0), 0))">
                                            {{ order.details.reduce((sum, detail) => sum + (detail.difference || 0), 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link 
                                                :href="route('inventory-orders.show', order.id_inventory_order)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Détails
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="orders.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            Aucun ordre d'inventaire trouvé pour cette période
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