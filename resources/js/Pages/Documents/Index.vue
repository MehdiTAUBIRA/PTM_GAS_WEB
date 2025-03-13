<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    documents: Object,
    customers: Array,
    documentTypes: Object,
    paymentStatuses: Object,
    paymentMethods: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const documentType = ref(props.filters.document_type || '');
const paymentStatus = ref(props.filters.payment_status || '');
const customerId = ref(props.filters.customer_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const performFilter = debounce(() => {
    router.get(route('documents.index'), 
        { 
            search: search.value,
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

watch([search, documentType, paymentStatus, customerId, startDate, endDate], () => {
    performFilter();
});

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString('fr-FR') : '-';
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

const clearFilters = () => {
    search.value = '';
    documentType.value = '';
    paymentStatus.value = '';
    customerId.value = '';
    startDate.value = '';
    endDate.value = '';
    performFilter();
};
</script>

<template>
    <Head title="Documents" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des documents
                </h2>
                <Link 
                    :href="route('documents.report')"
                    class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600"
                >
                    Rapports
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Filtres</h3>
                        <button 
                            @click="clearFilters"
                            class="text-sm text-gray-600 hover:text-gray-900"
                        >
                            Réinitialiser les filtres
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recherche</label>
                            <input
                                type="text"
                                v-model="search"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="N° document ou client..."
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
                    </div>
                </div>

                <!-- Table -->
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
                                <tr v-for="document in documents.data" :key="document.id_document">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Link :href="route('documents.show', document.id_document)" class="text-blue-600 hover:text-blue-900">
                                            {{ document.document_number }}
                                        </Link>
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
                                        {{ document.total_amount }}€
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

                <!-- Pagination -->
                <div v-if="documents.links" class="mt-4">
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <!-- ... Pagination component ... -->
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>