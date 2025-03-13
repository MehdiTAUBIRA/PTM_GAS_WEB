<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const props = defineProps({
    productsForecasts: Array
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-FR').format(num);
};

const sortOption = ref('name'); // Options: 'name', 'stock', 'forecast'
const searchQuery = ref('');

// Filtrer et trier les produits
const filteredAndSortedProducts = computed(() => {
    let result = [...props.productsForecasts];
    
    // Filtrer par recherche
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(p => 
            p.prolibcommercial.toLowerCase().includes(query)
        );
    }
    
    // Trier selon l'option choisie
    if (sortOption.value === 'name') {
        result.sort((a, b) => a.prolibcommercial.localeCompare(b.prolibcommercial));
    } else if (sortOption.value === 'stock') {
        result.sort((a, b) => a.current_stock - b.current_stock);
    } else if (sortOption.value === 'forecast') {
        result.sort((a, b) => a.estimated_stock_3m - b.estimated_stock_3m);
    }
    
    return result;
});

// Données pour le graphique
const chartData = ref(null);
const selectedProduct = ref(null);

const prepareChartData = (product) => {
    if (!product || !product.monthly_sales || Object.keys(product.monthly_sales).length === 0) {
        return null;
    }
    
    const months = Object.keys(product.monthly_sales).sort();
    const sales = months.map(month => product.monthly_sales[month]);
    
    // Prévisions pour les 3 prochains mois
    const lastMonth = months[months.length - 1];
    const [year, month] = lastMonth.split('-');
    const nextMonths = [];
    let nextQuantities = [];
    
    // Calculer les mois suivants
    for (let i = 1; i <= 3; i++) {
        const nextDate = new Date(parseInt(year), parseInt(month) - 1 + i, 1);
        const nextMonthFormatted = `${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}`;
        nextMonths.push(nextMonthFormatted);
    }
    
    // Calculer les ventes prévues
    const avgSales = product.avg_monthly_sales;
    const trend = product.trend;
    
    for (let i = 0; i < 3; i++) {
        nextQuantities.push(avgSales + trend * (i + 1));
    }
    
    return {
        labels: [...months, ...nextMonths],
        datasets: [
            {
                label: 'Ventes historiques',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                data: [...sales, null, null, null],
                borderDash: []
            },
            {
                label: 'Prévisions',
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                data: [...Array(months.length).fill(null), ...nextQuantities],
                borderDash: [5, 5]
            }
        ]
    };
};

const showProductChart = (product) => {
    selectedProduct.value = product;
    chartData.value = prepareChartData(product);
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Historique des ventes et prévisions'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'Quantité'
            }
        },
        x: {
            title: {
                display: true,
                text: 'Mois'
            }
        }
    }
};
</script>

<template>
    <Head title="Prévisions des stocks" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Prévisions des stocks
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Introduction -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Analyse prévisionnelle des stocks</h3>
                    <p class="text-gray-600 mb-4">
                        Cette analyse est basée sur les données de vente des 6 derniers mois et projette l'état des stocks pour les 3 prochains mois.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Rechercher un produit</label>
                            <input
                                type="text"
                                id="search"
                                v-model="searchQuery"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Nom du produit..."
                            />
                        </div>
                        <div>
                            <label for="sortOption" class="block text-sm font-medium text-gray-700">Trier par</label>
                            <select
                                id="sortOption"
                                v-model="sortOption"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="name">Nom du produit</option>
                                <option value="stock">Stock actuel (croissant)</option>
                                <option value="forecast">Stock prévu (croissant)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Graphique pour le produit sélectionné -->
                <div v-if="selectedProduct" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Prévisions pour {{ selectedProduct.prolibcommercial }}
                    </h3>
                    
                    <div class="h-64 mb-4">
                        <Line 
                            v-if="chartData" 
                            :data="chartData" 
                            :options="chartOptions" 
                        />
                        <div v-else class="flex items-center justify-center h-full text-gray-500">
                            Données insuffisantes pour générer un graphique
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="text-sm text-gray-600 mb-1">Vente moyenne mensuelle</p>
                            <p class="text-xl font-bold">{{ formatNumber(selectedProduct.avg_monthly_sales.toFixed(1)) }}</p>
                        </div>
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="text-sm text-gray-600 mb-1">Stock actuel</p>
                            <p class="text-xl font-bold">{{ formatNumber(selectedProduct.current_stock) }}</p>
                        </div>
                        <div class="border rounded-lg p-4" :class="selectedProduct.will_be_below_threshold ? 'bg-red-50' : 'bg-green-50'">
                            <p class="text-sm text-gray-600 mb-1">Stock estimé dans 3 mois</p>
                            <p class="text-xl font-bold" :class="selectedProduct.will_be_below_threshold ? 'text-red-600' : 'text-green-600'">
                                {{ formatNumber(Math.max(0, selectedProduct.estimated_stock_3m.toFixed(0))) }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des prévisions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Prévisions par produit</h3>
                        
                        <div v-if="filteredAndSortedProducts.length === 0" class="text-center py-8 text-gray-500">
                            Aucune donnée de vente disponible pour générer des prévisions.
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produit
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vente moyenne mensuelle
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tendance
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock actuel
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock estimé dans 3 mois
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
                                    <tr v-for="product in filteredAndSortedProducts" :key="product.id_product" :class="product.will_be_below_threshold ? 'bg-red-50' : ''">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ product.prolibcommercial }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatNumber(product.avg_monthly_sales.toFixed(1)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="product.trend > 0 ? 'text-green-600' : product.trend < 0 ? 'text-red-600' : 'text-gray-600'">
                                                {{ product.trend > 0 ? '+' : '' }}{{ formatNumber(product.trend.toFixed(1)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatNumber(product.current_stock) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatNumber(Math.max(0, product.estimated_stock_3m.toFixed(0))) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="product.will_be_below_threshold" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Stock insuffisant prévu
                                            </span>
                                            <span v-else-if="product.alert_threshold" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Stock suffisant prévu
                                            </span>
                                            <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Non surveillé
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="showProductChart(product)" class="text-indigo-600 hover:text-indigo-900">
                                                Voir graphique
                                            </button>
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