<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    inventoryOrder: Object
});

const showCancelModal = ref(false);
const showCompleteModal = ref(false);

const completeForm = useForm({
    details: computed(() => {
        return props.inventoryOrder.details.map(detail => ({
            id_inventory_order_detail: detail.id_inventory_order_detail,
            counted_quantity: detail.counted_quantity || 0,
            comments: detail.comments || ''
        }));
    })
});

const startInventory = () => {
    useForm().post(route('inventory-orders.start', props.inventoryOrder.id_inventory_order), {
        preserveScroll: true
    });
};

const completeInventory = () => {
    completeForm.post(route('inventory-orders.complete', props.inventoryOrder.id_inventory_order), {
        preserveScroll: true,
        onSuccess: () => {
            showCompleteModal.value = false;
        }
    });
};

const cancelInventory = () => {
    useForm().post(route('inventory-orders.cancel', props.inventoryOrder.id_inventory_order), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
        }
    });
};

const deleteInventory = () => {
    useForm().delete(route('inventory-orders.destroy', props.inventoryOrder.id_inventory_order));
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString('fr-FR') : '-';
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'pending': 'En attente',
        'in_progress': 'En cours',
        'completed': 'Terminé',
        'cancelled': 'Annulé'
    };
    return texts[status] || status;
};

const getDifferenceClass = (difference) => {
    if (!difference) return '';
    return difference < 0 ? 'text-red-600' : (difference > 0 ? 'text-green-600' : '');
};
</script>

<template>
    <Head title="Détails de l'ordre d'inventaire" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ordre d'inventaire #{{ inventoryOrder.id_inventory_order }}
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="route('inventory-orders.index')"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
                    >
                        Retour à la liste
                    </Link>
                    <Link
                        v-if="inventoryOrder.status === 'pending'"
                        :href="route('inventory-orders.edit', inventoryOrder.id_inventory_order)"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        Modifier
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Status actions -->
                <div class="mb-6 flex justify-end space-x-3">
                    <PrimaryButton 
                        v-if="inventoryOrder.status === 'pending'"
                        @click="startInventory"
                    >
                        Démarrer l'inventaire
                    </PrimaryButton>
                    <PrimaryButton 
                        v-if="inventoryOrder.status === 'in_progress'"
                        @click="showCompleteModal = true"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        Compléter l'inventaire
                    </PrimaryButton>
                    <DangerButton 
                        v-if="['pending', 'in_progress'].includes(inventoryOrder.status)"
                        @click="showCancelModal = true"
                    >
                        Annuler l'inventaire
                    </DangerButton>
                    <DangerButton 
                        v-if="inventoryOrder.status === 'pending'"
                        @click="deleteInventory"
                    >
                        Supprimer
                    </DangerButton>
                </div>

                <!-- Order Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Informations générales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">Statut</p>
                                <p class="mt-1">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(inventoryOrder.status)]">
                                        {{ getStatusText(inventoryOrder.status) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Dépôt</p>
                                <p class="mt-1 font-medium">{{ inventoryOrder.depot.depot_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Date de création</p>
                                <p class="mt-1">{{ formatDate(inventoryOrder.order_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Créé par</p>
                                <p class="mt-1">{{ inventoryOrder.creator.lastname }} {{ inventoryOrder.creator.firstname }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Assigné à</p>
                                <p class="mt-1">{{ inventoryOrder.assignedEmployee.lastname }} {{ inventoryOrder.assignedEmployee.firstname }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Date de début</p>
                                <p class="mt-1">{{ formatDate(inventoryOrder.start_date) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Date de complétion</p>
                                <p class="mt-1">{{ formatDate(inventoryOrder.completion_date) }}</p>
                            </div>
                        </div>

                        <div v-if="inventoryOrder.comments" class="mt-6">
                            <p class="text-sm text-gray-600">Commentaires</p>
                            <p class="mt-1">{{ inventoryOrder.comments }}</p>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Produits à inventorier</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité attendue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité comptée</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Différence</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commentaires</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="detail in inventoryOrder.details" :key="detail.id_inventory_order_detail">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ detail.product.product_reference }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ detail.product.product_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">{{ detail.expected_quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">{{ detail.counted_quantity || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center" :class="getDifferenceClass(detail.difference)">
                                            {{ detail.difference ? detail.difference : '-' }}
                                        </td>
                                        <td class="px-6 py-4">{{ detail.comments || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour annuler l'inventaire -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Annuler l'ordre d'inventaire
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Êtes-vous sûr de vouloir annuler cet ordre d'inventaire ? Cette action est irréversible.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showCancelModal = false">Annuler</SecondaryButton>
                    <DangerButton class="ml-3" @click="cancelInventory">
                        Confirmer l'annulation
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modal pour compléter l'inventaire -->
        <Modal :show="showCompleteModal" @close="showCompleteModal = false" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Compléter l'ordre d'inventaire
                </h2>

                <p class="mb-4 text-sm text-gray-600">
                    Veuillez saisir les quantités comptées pour chaque produit.
                </p>

                <form @submit.prevent="completeInventory">
                    <div class="space-y-4">
                        <div v-for="(detail, index) in inventoryOrder.details" :key="detail.id_inventory_order_detail" class="border-b pb-4 last:border-b-0">
                            <div class="flex justify-between">
                                <div class="font-medium">{{ detail.product.product_reference }} - {{ detail.product.product_name }}</div>
                                <div>Quantité attendue: <span class="font-medium">{{ detail.expected_quantity }}</span></div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                <div>
                                    <InputLabel :for="`counted_quantity_${index}`" value="Quantité comptée" />
                                    <TextInput
                                        :id="`counted_quantity_${index}`"
                                        type="number"
                                        v-model="completeForm.details[index].counted_quantity"
                                        class="mt-1 block w-full"
                                        min="0"
                                        required
                                    />
                                    <InputError :message="completeForm.errors[`details.${index}.counted_quantity`]" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel :for="`comments_${index}`" value="Commentaires" />
                                    <textarea
                                        :id="`comments_${index}`"
                                        v-model="completeForm.details[index].comments"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        rows="2"
                                    ></textarea>
                                    <InputError :message="completeForm.errors[`details.${index}.comments`]" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton type="button" @click="showCompleteModal = false">Annuler</SecondaryButton>
                        <PrimaryButton 
                            class="ml-3" 
                            :disabled="completeForm.processing"
                        >
                            Compléter l'inventaire
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>