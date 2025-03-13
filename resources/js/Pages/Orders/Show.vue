<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed } from 'vue';

const props = defineProps({
    order: Object,
    statuses: Object
});

const form = useForm({
    status: props.order.status
});

const updateStatus = () => {
    form.patch(route('orders.updateStatus', props.order.id_order));
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString('fr-FR') : '-';
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

const totalAmount = computed(() => {
    return props.order.order_details.reduce((total, detail) => {
        return total + (detail.quantity * detail.unit_price);
    }, 0);
});
</script>

<template>
    <Head :title="`Commande ${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Commande {{ order.order_number }}
                </h2>
                <Link 
                    :href="route('orders.index')"
                    class="text-gray-600 hover:text-gray-900"
                >
                    Retour aux commandes
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- En-tête de la commande -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations commande</h3>
                            <div class="space-y-2">
                                <p>Date de commande: <span class="font-medium">{{ formatDate(order.order_date) }}</span></p>
                                <p>Date de livraison: <span class="font-medium">{{ formatDate(order.delivery_date) }}</span></p>
                                <div class="flex items-center gap-4">
                                    <p>Statut:</p>
                                    <select 
                                        v-model="form.status"
                                        class="rounded-md border-gray-300 shadow-sm"
                                        @change="updateStatus"
                                    >
                                        <option 
                                            v-for="(label, value) in statuses" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Client</h3>
                            <div class="space-y-2">
                                <p>Nom: <span class="font-medium">{{ order.customer.customer_name }} {{ order.customer.customer_firstname }}</span></p>
                                <p>Email: <span class="font-medium">{{ order.customer.customer_email }}</span></p>
                                <p>Téléphone: <span class="font-medium">{{ order.customer.customer_phone }}</span></p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Adresse de livraison</h3>
                            <div class="space-y-2">
                                <p>{{ order.customer_address.address }}</p>
                                <p>{{ order.customer_address.postalcode }}, {{ order.customer_address.country }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Détails de la commande -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Produits commandés</h3>
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
                                    <tr v-for="detail in order.order_details" :key="detail.id_order_detail">
                                        <td class="px-6 py-4">{{ detail.product.prolibcommercial }}</td>
                                        <td class="px-6 py-4">{{ detail.quantity }}</td>
                                        <td class="px-6 py-4">{{ detail.unit_price }}€</td>
                                        <td class="px-6 py-4">{{ (detail.quantity * detail.unit_price).toFixed(2) }}€</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-medium">Total</td>
                                        <td class="px-6 py-4 font-medium">{{ totalAmount.toFixed(2) }}€</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Commentaires -->
                    <div v-if="order.comments">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Commentaires</h3>
                        <p class="text-gray-600">{{ order.comments }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>