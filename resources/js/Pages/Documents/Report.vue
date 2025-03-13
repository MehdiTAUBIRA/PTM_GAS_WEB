<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    documents: Array,
    stats: Object,
    customers: Array,
    documentTypes: Object,
    paymentStatuses: Object,
    paymentMethods: Object,
    filters: Object
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const documentType = ref(props.filters.document_type || '');
const paymentStatus = ref(props.filters.payment_status || '');
const customerId = ref(props.filters.customer_id || '');

const performFilter = debounce(() => {
    router.get(route('documents.report'), 
        { 
            document_type: documentType.value,
            payment_status: paymentStatus.value,
            customer_id: customerId.value,
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

watch([startDate, endDate, documentType, paymentStatus, customerId], () => {
    performFilter();
});

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString('fr-FR') : '-';
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const printDocument = (document) => {
    window.open(route('documents.print', document.id_document), '_blank');
};

const emailDocument = (document) => {
    router.post(route('documents.email', document.id_document));
};
</script>

<template>
    <Head title="Rapport des documents" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rapport des documents
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres de date -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de début</label>
                            <input 
                                type="date" 
                                v-model="startDate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                            <input 
                                type="date" 
                                v-model="endDate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type de document</label>
                            <select 
                                v-model="documentType"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">Tous les types</option>
                                <option 
                                    v-for="(label, type) in documentTypes" 
                                    :key="type" 
                                    :value="type"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Statut de paiement</label>
                            <select 
                                v-model="paymentStatus"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">Tous les statuts</option>
                                <option 
                                    v-for="(label, status) in paymentStatuses" 
                                    :key="status" 
                                    :value="status"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Client</label>
                            <select 
                                v-model="customerId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">Tous les clients</option>
                                <option 
                                    v-for="customer in customers" 
                                    :key="customer.id_customer" 
                                    :value="customer.id_customer"
                                >
                                    {{ customer.customer_name }} {{ customer.customer_firstname }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Total des documents</h3>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Nombre total</p>
                                <p class="text-3xl font-bold">{{ stats.total_documents }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Montant total</p>
                                <p class="text-3xl font-bold">{{ formatPrice(stats.total_amount) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Par statut de paiement</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-green-600">Payé</span>
                                <span class="font-medium">{{ formatPrice(stats.paid_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-yellow-600">En attente</span>
                                <span class="font-medium">{{ formatPrice(stats.pending_amount) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 h-2 mt-2 rounded-full overflow-hidden">
                                <div 
                                    class="bg-green-500 h-full" 
                                    :style="`width: ${stats.paid_amount / stats.total_amount * 100}%`"
                                ></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Par type de document</h3>
                        <div class="space-y-2">
                            <div v-for="(count, type) in stats.by_type" :key="type" class="flex justify-between">
                                <span class="text-sm">{{ documentTypes[type] }}</span>
                                <span class="font-medium">{{ count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des documents -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Document</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="document in documents" :key="document.id_document">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a :href="route('documents.show', document.id_document)" class="text-blue-600 hover:text-blue-900">
                                            {{ document.document_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ documentTypes[document.document_type] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ formatDate(document.document_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ document.customer.customer_name }} {{ document.customer.customer_firstname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ formatPrice(document.total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(document.payment_status)]"
                                        >
                                            {{ paymentStatuses[document.payment_status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <button @click="printDocument(document)" class="text-blue-600 hover:text-blue-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                            </button>
                                            <button @click="emailDocument(document)" class="text-green-600 hover:text-green-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        </div>
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