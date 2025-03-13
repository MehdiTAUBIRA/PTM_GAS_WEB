<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    orders: Array,
    stats: Object,
    startDate: String,
    endDate: String,
    statuses: Object
});

const filters = ref({
    start_date: props.startDate,
    end_date: props.endDate
});

const updateReport = () => {
    router.get(route('orders.report'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
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
    <Head title="Rapport des commandes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rapport des commandes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres de date -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de début</label>
                            <input 
                                type="date" 
                                v-model="filters.start_date"
                                class="mt-1 block rounded-md border-gray-300 shadow-sm"
                                @change="updateReport"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                            <input 
                                type="date" 
                                v-model="filters.end_date"
                                class="mt-1 block rounded-md border-gray-300 shadow-sm"
                                @change="updateReport"
                            >
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Total commandes</h3>
                        <p class="text-3xl font-bold">{{ stats.total_orders }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Montant total</h3>
                        <p class="text-3xl font-bold">{{ formatPrice(stats.total_amount) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Moyenne par commande</h3>
                        <p class="text-3xl font-bold">{{ formatPrice(stats.average_amount) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Par statut</h3>
                        <div class="space-y-2">
                            <div v-for="(count, status) in stats.status_count" :key="status" class="flex justify-between">
                                <span :class="getStatusClass(status)" class="px-2 py-1 rounded-full text-xs">
                                    {{ statuses[status] }}
                                </span>
                                <span class="font-medium">{{ count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des commandes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Commande</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders" :key="order.id_order">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ order.order_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.order_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ order.customer.customer_name }} {{ order.customer.customer_firstname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatPrice(order.total_amount) }}</td>
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>