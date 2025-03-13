<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    depositDocuments: Object,
    paymentMethods: Object
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

// Fonctions pour l'impression et l'envoi des documents
const printDocument = (document, type) => {
    let route = type === 'deposit' 
        ? 'deposits.receipt.print' 
        : 'deposits.return.receipt.print';
    
    window.open(route(route, document.id_customer_deposit), '_blank');
};

const emailDocument = (document, type) => {
    let route = type === 'deposit' 
        ? 'deposits.receipt.email' 
        : 'deposits.return.receipt.email';
    
    router.post(route(route, document.id_customer_deposit));
};

</script>

<template>
    <Head title="Rapport de dépôts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rapport des dépôts
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistiques globales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nombre de dépôts</h3>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="text-3xl font-bold">{{ stats.total_deposits }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-green-500">Payés</p>
                                <p class="text-2xl font-semibold text-green-600">{{ stats.paid_deposits }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-yellow-500">En attente</p>
                                <p class="text-2xl font-semibold text-yellow-600">{{ stats.pending_deposits }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Montant des dépôts</h3>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="text-3xl font-bold">{{ formatPrice(stats.total_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-green-500">Payés</p>
                                <p class="text-2xl font-semibold text-green-600">{{ formatPrice(stats.paid_amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-yellow-500">En attente</p>
                                <p class="text-2xl font-semibold text-yellow-600">{{ formatPrice(stats.pending_amount) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Méthodes de paiement</h3>
                        <div class="space-y-2">
                            <div v-for="item in stats.payment_methods" :key="item.deposit_payment_method" class="flex justify-between">
                                <span>{{ paymentMethods[item.deposit_payment_method] || item.deposit_payment_method }}</span>
                                <span class="font-medium">{{ item.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par produit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dépôts par produit</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="item in stats.deposits_by_product" :key="item.prolibcommercial" class="p-4 border rounded-lg">
                            <div class="flex justify-between items-center">
                                <p class="font-medium">{{ item.prolibcommercial }}</p>
                                <p class="text-xl font-bold">{{ item.count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents de dépôt -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Reçus de dépôt -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reçus de dépôt</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Document</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="doc in depositDocuments.deposit_receipt" :key="doc.id_deposit_document">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ doc.document_number }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ formatDate(doc.document_date) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ doc.customer.customer_name }} {{ doc.customer.customer_firstname }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ formatPrice(doc.amount) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button @click="printDocument(doc, 'deposit')" class="text-blue-600 hover:text-blue-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                    </svg>
                                                </button>
                                                <button @click="emailDocument(doc, 'deposit')" class="text-green-600 hover:text-green-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    <!-- Reçus de remboursement -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reçus de remboursement</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">N° Document</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="doc in depositDocuments.return_receipt" :key="doc.id_deposit_document">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ doc.document_number }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ formatDate(doc.document_date) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ doc.customer.customer_name }} {{ doc.customer.customer_firstname }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ formatPrice(doc.amount) }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button @click="printDocument(doc, 'deposit')" class="text-blue-600 hover:text-blue-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                    </svg>
                                                </button>
                                                <button @click="emailDocument(doc, 'deposit')" class="text-green-600 hover:text-green-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        </div>
    </AuthenticatedLayout>
</template>