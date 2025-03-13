<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    document: Object,
    paymentMethods: Object
});

const showPaymentModal = ref(false);

const paymentForm = useForm({
    payment_method: '',
    payment_date: new Date().toISOString().split('T')[0]
});

const openPaymentModal = () => {
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    paymentForm.reset();
};

const markAsPaid = () => {
    paymentForm.patch(route('documents.mark-as-paid', props.document.id_document), {
        onSuccess: () => closePaymentModal()
    });
};

const printDocument = () => {
    window.open(route('documents.print', props.document.id_document), '_blank');
};

const emailDocument = () => {
    router.post(route('documents.email', props.document.id_document));
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString('fr-FR') : '-';
};

const getDocumentTypeName = () => {
    const types = {
        'invoice': 'Facture',
        'delivery_note': 'Bon de livraison',
        'return_note': 'Note de retour'
    };
    return types[props.document.document_type] || props.document.document_type;
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusName = (status) => {
    const statuses = {
        'pending': 'En attente',
        'paid': 'Payé',
        'cancelled': 'Annulé'
    };
    return statuses[status] || status;
};
</script>

<template>
    <Head :title="getDocumentTypeName() + ' ' + document.document_number" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ getDocumentTypeName() }} #{{ document.document_number }}
                </h2>
                <div class="flex space-x-2">
                    <button 
                        v-if="document.payment_status === 'pending'"
                        @click="openPaymentModal"
                        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600"
                    >
                        Marquer comme payé
                    </button>
                    <button 
                        @click="printDocument"
                        class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                    >
                        Imprimer
                    </button>
                    <button 
                        @click="emailDocument"
                        class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600"
                    >
                        Envoyer par email
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations du document</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Numéro :</span> {{ document.document_number }}</p>
                                <p><span class="font-medium">Type :</span> {{ getDocumentTypeName() }}</p>
                                <p><span class="font-medium">Date :</span> {{ formatDate(document.document_date) }}</p>
                                <p>
                                    <span class="font-medium">Statut de paiement :</span> 
                                    <span 
                                        :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(document.payment_status)]"
                                    >
                                        {{ getStatusName(document.payment_status) }}
                                    </span>
                                </p>
                                <template v-if="document.payment_status === 'paid'">
                                    <p><span class="font-medium">Date de paiement :</span> {{ formatDate(document.payment_date) }}</p>
                                    <p><span class="font-medium">Méthode de paiement :</span> {{ document.payment_method }}</p>
                                </template>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Client</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Nom :</span> {{ document.customer.customer_name }} {{ document.customer.customer_firstname }}</p>
                                <p><span class="font-medium">Email :</span> {{ document.customer.customer_email }}</p>
                                <p><span class="font-medium">Téléphone :</span> {{ document.customer.customer_phone }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6" v-if="document.order">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Détails de la commande</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="detail in document.order.order_details" :key="detail.id_order_detail">
                                        <td class="px-6 py-4">{{ detail.product.prolibcommercial }}</td>
                                        <td class="px-6 py-4">{{ detail.quantity }}</td>
                                        <td class="px-6 py-4">{{ detail.unit_price }}€</td>
                                        <td class="px-6 py-4">{{ (detail.quantity * detail.unit_price).toFixed(2) }}€</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-medium">Total</td>
                                        <td class="px-6 py-4 font-medium">{{ document.total_amount }}€</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <div v-if="document.comments">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Commentaires</h3>
                        <p class="text-gray-700">{{ document.comments }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour marquer comme payé -->
        <Modal :show="showPaymentModal" @close="closePaymentModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Marquer comme payé
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Méthode de paiement</label>
                        <select 
                            v-model="paymentForm.payment_method"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Sélectionnez une méthode</option>
                            <option 
                                v-for="(label, method) in paymentMethods" 
                                :key="method" 
                                :value="method"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <div v-if="paymentForm.errors.payment_method" class="text-red-500 text-sm mt-1">
                            {{ paymentForm.errors.payment_method }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de paiement</label>
                        <input 
                            type="date" 
                            v-model="paymentForm.payment_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        >
                        <div v-if="paymentForm.errors.payment_date" class="text-red-500 text-sm mt-1">
                            {{ paymentForm.errors.payment_date }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-gray-100 flex justify-end space-x-2">
                <SecondaryButton @click="closePaymentModal">Annuler</SecondaryButton>
                <PrimaryButton 
                    @click="markAsPaid"
                    :disabled="paymentForm.processing"
                >
                    Confirmer
                </PrimaryButton>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>