<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    customers: Array,
    products: Array,
    employees: Array,
    paymentMethods: Object,
    depositRates: Array
});

const form = useForm({
    id_customer: '',
    id_product: '',
    deposit_paid: true,
    deposit_amount: '',
    deposit_date: new Date().toISOString().split('T')[0],
    deposit_payment_method: 'cash',
    id_order: '',
    id_employee: '',
    comments: '',
    generate_receipt: true
});

const showReceiptModal = ref(false);
const receiptData = ref(null);

// Mettre à jour le montant du dépôt en fonction du produit sélectionné
watch(() => form.id_product, (newProductId) => {
    if (newProductId) {
        const depositRate = props.depositRates.find(
            rate => rate.id_product === parseInt(newProductId)
        );
        if (depositRate) {
            form.deposit_amount = depositRate.deposit_amount;
        }
    }
});

// Fonction pour gérer la soumission du formulaire
const submit = () => {
    form.post(route('deposits.store'), {
        onSuccess: (response) => {
            if (form.generate_receipt && form.deposit_paid) {
                // Si le dépôt est payé et qu'un reçu est demandé, on affiche le modal
                receiptData.value = response.props.flash.deposit;
                showReceiptModal.value = true;
            }
        }
    });
};

// Fonctions pour les actions sur le reçu
const printReceipt = () => {
    window.open(route('deposits.receipt.print', receiptData.value.id_customer_deposit), '_blank');
};

const emailReceipt = () => {
    router.post(route('deposits.receipt.email', receiptData.value.id_customer_deposit));
    showReceiptModal.value = false;
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
    router.visit(route('deposits.index'));
};

</script>

<template>
    <Head title="Nouveau dépôt" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nouveau dépôt
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Client</label>
                                <select 
                                    v-model="form.id_customer"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Sélectionnez un client</option>
                                    <option 
                                        v-for="customer in customers" 
                                        :key="customer.id_customer" 
                                        :value="customer.id_customer"
                                    >
                                        {{ customer.customer_name }} {{ customer.customer_firstname }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_customer" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.id_customer }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Produit</label>
                                <select 
                                    v-model="form.id_product"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Sélectionnez un produit</option>
                                    <option 
                                        v-for="product in products" 
                                        :key="product.id_product" 
                                        :value="product.id_product"
                                    >
                                        {{ product.prolibcommercial }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_product" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.id_product }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Employé</label>
                                <select 
                                    v-model="form.id_employee"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Sélectionnez un employé</option>
                                    <option 
                                        v-for="employee in employees" 
                                        :key="employee.id_employee" 
                                        :value="employee.id_employee"
                                    >
                                        {{ employee.employee_firstname }} {{ employee.employee_lastname }}
                                    </option>
                                </select>
                                <div v-if="form.errors.id_employee" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.id_employee }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Statut du paiement</label>
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input 
                                            type="radio" 
                                            v-model="form.deposit_paid" 
                                            :value="true" 
                                            class="form-radio"
                                        >
                                        <span class="ml-2">Payé</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input 
                                            type="radio" 
                                            v-model="form.deposit_paid" 
                                            :value="false" 
                                            class="form-radio"
                                        >
                                        <span class="ml-2">Non payé</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date du dépôt</label>
                                <input 
                                    type="date" 
                                    v-model="form.deposit_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.deposit_date" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.deposit_date }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Montant du dépôt</label>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    v-model="form.deposit_amount"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.deposit_amount" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.deposit_amount }}
                                </div>
                            </div>

                            <div v-if="form.deposit_paid">
                                <label class="block text-sm font-medium text-gray-700">Méthode de paiement</label>
                                <select 
                                    v-model="form.deposit_payment_method"
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
                                <div v-if="form.errors.deposit_payment_method" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.deposit_payment_method }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Commande associée (optionnel)</label>
                                <input 
                                    type="text" 
                                    v-model="form.id_order"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    placeholder="ID de la commande"
                                >
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Commentaires</label>
                                <textarea 
                                    v-model="form.comments"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                ></textarea>
                            </div>

                            <div class="col-span-2" v-if="form.deposit_paid">
                                <label class="inline-flex items-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="form.generate_receipt" 
                                        class="form-checkbox"
                                    >
                                    <span class="ml-2">Générer un reçu de dépôt</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button 
                                type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Création...' : 'Créer le dépôt' }}
                            </button>
                        </div>
                    </form>
                </div>
                    <!-- Modal pour les actions après création du dépôt -->
                    <Modal :show="showReceiptModal" @close="closeReceiptModal">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">
                                Dépôt créé avec succès
                            </h2>
                            <p class="mb-4">
                                Le dépôt a été enregistré. Que souhaitez-vous faire avec le reçu ?
                            </p>
                            <div class="flex flex-col space-y-3">
                                <PrimaryButton @click="printReceipt" class="justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Imprimer le reçu
                                </PrimaryButton>
                                <PrimaryButton @click="emailReceipt" class="justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Envoyer par email
                                </PrimaryButton>
                                <SecondaryButton @click="closeReceiptModal" class="justify-center">
                                    Terminer sans reçu
                                </SecondaryButton>
                            </div>
                        </div>
                    </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>