<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const props = defineProps({
    product: Object,
    sales: Array,
    totalQuantity: Number,
    totalAmount: Number,
    periods: Object,
    filters: Object,
    salesTrend: Array
});

const form = useForm({
    period: props.filters.period || 'month',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const showCustomDateRange = ref(form.period === 'custom');
const chartDataSales = ref(null);
const chartDataQuantity = ref(null);

// Mettre à jour l'affichage des champs de date personnalisés
watch(() => form.period, (newValue) => {
    showCustomDateRange.value = newValue === 'custom';
    
    if (newValue !== 'custom') {
        form.get(route('products.report.details', props.product.id_product), {
            preserveState: true,
            preserveScroll: true,
            only: ['sales', 'totalQuantity', 'totalAmount', 'filters', 'salesTrend']
        });
    }
});

const applyFilters = () => {
    form.get(route('products.report.details', props.product.id_product), {
        preserveState: true,
        preserveScroll: true,
        only: ['sales', 'totalQuantity', 'totalAmount', 'filters', 'salesTrend']
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    prepareChartData();
});

watch(() => props.salesTrend, () => {
    prepareChartData();
}, { deep: true });

const prepareChartData = () => {
    const labels = props.salesTrend.map(item => item.date);
    
    // Données pour les montants
    chartDataSales.value = {
        labels,
        datasets: [
            {
                label: 'Montant des ventes',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                data: props.salesTrend.map(item => item.amount)
            }
        ]
    };
    
    // Données pour les quantités
    chartDataQuantity.value = {
        labels,
        datasets: [
            {
                label: 'Quantités vendues',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                data: props.salesTrend.map(item => item.quantity)
            }
        ]
    };
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true
        }
    }
};
</script>

<template>
    <Head :title="`Rapport - ${product.prolibcommercial}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rapport du produit: {{ product.prolibcommercial }}
                </h2>
                <Link
                    :href="route('products.report')"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
                >
                    Retour au rapport
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <form @submit.prevent="applyFilters">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="period" value="Période" />
                                <select
                                    id="period"
                                    v-model="form.period"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                >
                                    <option v-for="(label, value) in periods" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.period" class="mt-2" />
                            </div>
                            
                            <div v-if="showCustomDateRange">
                                <InputLabel for="start_date" value="Date de début" />
                                <TextInput
                                    id="start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.start_date"
                                    required
                                />
                                <InputError :message="form.errors.start_date" class="mt-2" />
                            </div>
                            
                            <div v-if="showCustomDateRange">
                                <InputLabel for="end_date" value="Date de fin" />
                                <TextInput
                                    id="end_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.end_date"
                                    required
                                />
                                <InputError :message="form.errors.end_date" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6" v-if="showCustomDateRange">
                            <PrimaryButton 
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing"
                            >
                                Appliquer
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                
                <!-- Informations sur le produit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Informations sur le produit</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-600 text-sm">Code</p>
                            <p class="font-semibold">{{ product.procode }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-600 text-sm">Libellé</p>
                            <p class="font-semibold">{{ product.prolibcommercial }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-600 text-sm">Capacité</p>
                            <p class="font-semibold">{{ product.pro_real_capacity }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Résumé des ventes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Résumé des ventes</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border rounded-lg p-4 bg-blue-50">
                                <p class="text-sm text-gray-600">Total vendu</p>
                                <p class="text-2xl font-bold text-blue-600">{{ totalQuantity }}</p>
                                <p class="text-xs text-gray-500">unités</p>
                            </div>
                            <div class="border rounded-lg p-4 bg-green-50">
                                <p class="text-sm text-gray-600">Montant total</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalAmount) }}</p>
                                <p class="text-xs text-gray-500">pour la période</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Prix moyen</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border rounded-lg p-4 bg-purple-50">
                                <p class="text-sm text-gray-600">Prix moyen unitaire</p>
                                <p class="text-2xl font-bold text-purple-600">{{ totalQuantity ? formatCurrency(totalAmount / totalQuantity) : formatCurrency(0) }}</p>
                                <p class="text-xs text-gray-500">pour la période</p>
                            </div>
                            <div class="border rounded-lg p-4 bg-yellow-50">
                                <p class="text-sm text-gray-600">Prix catalogue</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ formatCurrency(product.proprice) }}</p>
                                <p class="text-xs text-gray-500">prix de référence</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Évolution des ventes (Montant)</h3>
                        <div class="h-64">
                            <Line v-if="chartDataSales" :data="chartDataSales" :options="chartOptions" />
                            <div v-else class="flex items-center justify-center h-full text-gray-500">
                                Chargement des données...
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Évolution des ventes (Quantité)</h3>
                        <div class="h-64">
                            <Line v-if="chartDataQuantity" :data="chartDataQuantity" :options="chartOptions" />
                            <div v-else class="flex items-center justify-center h-full text-gray-500">
                                Chargement des données...
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Historique des ventes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Historique des ventes</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            N° Commande
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantité
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Prix unitaire
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Montant
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in sales" :key="sale.id_order">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(sale.order_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <Link :href="route('orders.show', sale.id_order)" class="text-indigo-600 hover:text-indigo-900">
                                                {{ sale.order_number }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ sale.customer_name }} {{ sale.customer_firstname }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ sale.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(sale.unit_price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(sale.total_amount) }}
                                        </td>
                                    </tr>
                                    <tr v-if="sales.length === 0">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Aucune vente durant cette période
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="sales.length > 0" class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Total
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ totalQuantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ totalQuantity ? formatCurrency(totalAmount / totalQuantity) : formatCurrency(0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ formatCurrency(totalAmount) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>