<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Bar, Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    stats: Object,
    orderChartData: Array,
    topSellingProducts: Array
});

// Préparer les données pour le graphique des commandes
const orderChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: true,
            text: 'Évolution des commandes'
        }
    }
};

const orderChartData = {
    labels: props.orderChartData.map(item => item.month),
    datasets: [
        {
            label: 'Nombre de commandes',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            data: props.orderChartData.map(item => item.count)
        }
    ]
};

// Formatage des nombres
const formatNumber = (num) => {
    return new Intl.NumberFormat().format(num);
};
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tableau de bord
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistiques principales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Produits en stock -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 mr-4">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Produits en stock</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.productsInStock) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Produits chez les clients -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 mr-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Produits chez clients</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.productsWithCustomers) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Produits défectueux -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 mr-4">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Produits défectueux</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.defectiveProducts) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Produits en maintenance -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 mr-4">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Produits en maintenance</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.productsInMaintenance) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commandes et livraisons -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Commandes aujourd'hui -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 mr-4">
                                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Commandes aujourd'hui</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.ordersToday) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Commandes ce mois -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 mr-4">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Commandes ce mois</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.ordersThisMonth) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Livraisons prévues aujourd'hui -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 mr-4">
                                <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Livraisons aujourd'hui</p>
                                <p class="text-2xl font-bold">{{ formatNumber(stats.deliveriesToday) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphique et Top produits -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Graphique d'évolution des commandes -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des commandes cette année</h3>
                        <div class="h-64">
                            <Bar :data="orderChartData" :options="orderChartOptions" />
                        </div>
                    </div>

                    <!-- Top produits vendus -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top produits vendus</h3>
                        <div class="space-y-4">
                            <div v-if="topSellingProducts.length === 0" class="text-center text-gray-500 py-6">
                                Aucune donnée disponible
                            </div>
                            <div v-else>
                                <div v-for="(product, index) in topSellingProducts" :key="product.id_product" class="flex items-center justify-between py-2 border-b">
                                    <div class="flex items-center">
                                        <div class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-3">
                                            {{ index + 1 }}
                                        </div>
                                        <span class="text-sm">{{ product.prolibcommercial }}</span>
                                    </div>
                                    <span class="font-bold">{{ formatNumber(product.total_quantity) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
