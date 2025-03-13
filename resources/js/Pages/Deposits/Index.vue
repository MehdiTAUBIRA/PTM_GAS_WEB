<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import _ from 'lodash';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    deposits: Object,
    paymentMethods: Object
});

const search = ref('');
const statusFilter = ref('');
const showReturnModal = ref(false);
const selectedDeposit = ref(null);

const performSearch = _.debounce((value) => {
    router.get(route('deposits.index'), 
        { 
            search: value,
            status: statusFilter.value
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

watch(statusFilter, (value) => {
    performSearch(search.value);
});

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString('fr-FR') : '-';
};

const openReturnModal = (deposit) => {
    selectedDeposit.value = deposit;
    showReturnModal.value = true;
};

const closeReturnModal = () => {
    showReturnModal.value = false;
    selectedDeposit.value = null;
};

const returnForm = useForm({
    id_customer_deposit: '',
    id_employee: 1, // Vous devrez remplacer cela par l'ID de l'employé connecté
    comments: ''
});

// Ajout de variables pour le modal de reçu
const showReceiptOptionsModal = ref(false);
const receiptDeposit = ref(null);

// Fonction de remboursement modifiée
const processReturn = () => {
    returnForm.id_customer_deposit = selectedDeposit.value.id_customer_deposit;
    returnForm.post(route('deposits.return'), {
        onSuccess: (response) => {
            closeReturnModal();
            receiptDeposit.value = response.props.flash.deposit;
            showReceiptOptionsModal.value = true;
        }
    });
};

// Fonctions pour les actions sur le reçu
const printReturnReceipt = () => {
    window.open(route('deposits.return.receipt.print', receiptDeposit.value.id_customer_deposit), '_blank');
};

const emailReturnReceipt = () => {
    router.post(route('deposits.return.receipt.email', receiptDeposit.value.id_customer_deposit));
    showReceiptOptionsModal.value = false;
};

const closeReceiptOptionsModal = () => {
    showReceiptOptionsModal.value = false;
};

</script>

<template>
    <Head title="Dépôts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des dépôts
                </h2>
                <Link 
                    :href="route('deposits.create')"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                >
                    Nouveau dépôt
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="mb-4 flex gap-4">
                    <div class="flex-1">
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
                                placeholder="Rechercher un dépôt..."
                            >
                        </div>
                    </div>
                    <div class="w-64">
                        <select 
                            v-model="statusFilter"
                            class="block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="paid">Payés</option>
                            <option value="unpaid">Non payés</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paiement</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="deposit in deposits.data" :key="deposit.id_customer_deposit">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ deposit.customer ? `${deposit.customer.customer_name} ${deposit.customer.customer_firstname}` : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ deposit.product ? deposit.product.prolibcommercial : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ deposit.deposit_amount }}€</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(deposit.deposit_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="deposit.deposit_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ deposit.deposit_paid ? 'Payé' : 'En attente' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ deposit.deposit_payment_method ? paymentMethods[deposit.deposit_payment_method] : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button 
                                            v-if="deposit.deposit_paid"
                                            @click="openReturnModal(deposit)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Rembourser
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour le remboursement -->
        <Modal :show="showReturnModal" @close="closeReturnModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Confirmer le remboursement du dépôt
                </h2>
                <p v-if="selectedDeposit" class="mb-4">
                    Vous êtes sur le point de rembourser le dépôt de 
                    <span class="font-medium">{{ selectedDeposit.deposit_amount }}€</span> 
                    pour le client 
                    <span class="font-medium">{{ selectedDeposit.customer?.customer_name }} {{ selectedDeposit.customer?.customer_firstname }}</span>.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Commentaires</label>
                    <textarea 
                        v-model="returnForm.comments"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        placeholder="Ajoutez un commentaire sur ce remboursement..."
                    ></textarea>
                </div>
            </div>
            <div class="p-6 bg-gray-100 flex justify-end space-x-2">
                <SecondaryButton @click="closeReturnModal">Annuler</SecondaryButton>
                <DangerButton 
                    @click="processReturn"
                    :disabled="returnForm.processing"
                >
                    Confirmer le remboursement
                </DangerButton>
            </div>
        </Modal>
        <!-- Nouveau modal pour les options de reçu de remboursement -->
        <Modal :show="showReceiptOptionsModal" @close="closeReceiptOptionsModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Remboursement effectué
                </h2>
                <p class="mb-4">
                    Le remboursement a été enregistré. Que souhaitez-vous faire avec le reçu ?
                </p>
                <div class="flex flex-col space-y-3">
                    <PrimaryButton @click="printReturnReceipt" class="justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimer le reçu
                    </PrimaryButton>
                    <PrimaryButton @click="emailReturnReceipt" class="justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Envoyer par email
                    </PrimaryButton>
                    <SecondaryButton @click="closeReceiptOptionsModal" class="justify-center">
                        Terminer sans reçu
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>