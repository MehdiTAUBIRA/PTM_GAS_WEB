<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    customers: Object,
    products: Array,
    statuses: Object
});

const selectedCustomer = ref(null);
const customerAddresses = computed(() => {
    if (!selectedCustomer.value) return [];
    return customers.value.find(c => c.id_customer === selectedCustomer.value)?.addresses || [];
});

const form = useForm({
    id_customer: '',
    id_cus_address: '',
    order_date: new Date().toISOString().split('T')[0],
    delivery_date: '',
    status: 'pending',
    comments: '',
    orderDetails: [
        {
            id_product: '',
            quantity: 1,
            unit_price: 0
        }
    ]
});

const addProduct = () => {
    form.orderDetails.push({
        id_product: '',
        quantity: 1,
        unit_price: 0
    });
};

const removeProduct = (index) => {
    if (form.orderDetails.length > 1) {
        form.orderDetails.splice(index, 1);
    }
};

// Mettre à jour le prix unitaire quand un produit est sélectionné
const updateUnitPrice = (index) => {
    const productId = form.orderDetails[index].id_product;
    if (productId) {
        const product = props.products.find(p => p.id_product === productId);
        if (product) {
            form.orderDetails[index].unit_price = product.proprice;
        }
    }
};

// Calculer le total de la commande
const totalAmount = computed(() => {
    return form.orderDetails.reduce((total, detail) => {
        return total + (detail.quantity * detail.unit_price);
    }, 0);
});

const submit = () => {
    form.post(route('orders.store'));
};
</script>

<template>
    <Head title="Nouvelle commande" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nouvelle commande
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <!-- Informations client -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Client et livraison</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client</label>
                                    <select 
                                        v-model="form.id_customer"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        @change="form.id_cus_address = ''"
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
                                    <label class="block text-sm font-medium text-gray-700">Adresse de livraison</label>
                                    <select 
                                        v-model="form.id_cus_address"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        :disabled="!form.id_customer"
                                    >
                                        <option value="">Sélectionnez une adresse</option>
                                        <option 
                                            v-for="address in customerAddresses" 
                                            :key="address.id_cus_address" 
                                            :value="address.id_cus_address"
                                        >
                                            {{ address.address }} - {{ address.postalcode }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.id_cus_address" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.id_cus_address }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date de livraison souhaitée</label>
                                    <input 
                                        type="date" 
                                        v-model="form.delivery_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        :min="form.order_date"
                                    >
                                    <div v-if="form.errors.delivery_date" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.delivery_date }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select 
                                        v-model="form.status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                        <option 
                                            v-for="(label, value) in statuses" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.status }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Produits -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Produits</h3>
                                <button 
                                    type="button"
                                    @click="addProduct"
                                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600"
                                >
                                    Ajouter un produit
                                </button>
                            </div>

                            <div v-for="(detail, index) in form.orderDetails" :key="index" class="mb-6 p-4 border rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-medium">Produit {{ index + 1 }}</h4>
                                    <button 
                                        v-if="form.orderDetails.length > 1"
                                        type="button"
                                        @click="removeProduct(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        Supprimer
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Produit</label>
                                        <select 
                                            v-model="detail.id_product"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            @change="updateUnitPrice(index)"
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
                                        <div v-if="form.errors[`orderDetails.${index}.id_product`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`orderDetails.${index}.id_product`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantité</label>
                                        <input 
                                            type="number" 
                                            v-model="detail.quantity"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`orderDetails.${index}.quantity`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`orderDetails.${index}.quantity`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Prix unitaire</label>
                                        <input 
                                            type="number" 
                                            v-model="detail.unit_price"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`orderDetails.${index}.unit_price`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`orderDetails.${index}.unit_price`] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 text-right text-sm text-gray-600">
                                    Sous-total: {{ (detail.quantity * detail.unit_price).toFixed(2) }}€
                                </div>
                            </div>
                        </div>

                        <!-- Commentaires -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700">Commentaires</label>
                            <textarea 
                                v-model="form.comments"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            ></textarea>
                        </div>

                        <!-- Total et validation -->
                        <div class="flex justify-between items-center">
                            <div class="text-xl font-semibold">
                                Total: {{ totalAmount.toFixed(2) }}€
                            </div>
                            <button 
                                type="submit" 
                                class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Création...' : 'Créer la commande' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>