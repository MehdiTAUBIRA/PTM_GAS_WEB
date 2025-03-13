<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    alerts: Array,
    products: Array
});

const showAddForm = ref(false);
const editingAlert = ref(null);

const newAlertForm = useForm({
    id_product: '',
    threshold_quantity: '',
    is_active: true
});

const editForm = useForm({
    threshold_quantity: '',
    is_active: true
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-FR').format(num);
};

const addAlert = () => {
    newAlertForm.post(route('stocks.alerts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            newAlertForm.reset();
            showAddForm.value = false;
        }
    });
};

const startEditing = (alert) => {
    editingAlert.value = alert;
    editForm.threshold_quantity = alert.threshold_quantity;
    editForm.is_active = alert.is_active;
};

const updateAlert = () => {
    editForm.put(route('stocks.alerts.update', editingAlert.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingAlert.value = null;
        }
    });
};

const cancelEdit = () => {
    editingAlert.value = null;
    editForm.reset();
};

const deleteAlert = (alertId) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette alerte ?')) {
        useForm().delete(route('stocks.alerts.delete', alertId), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Gestion des alertes de stock" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestion des alertes de stock
                </h2>
                <button
                    @click="showAddForm = !showAddForm"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 transition"
                >
                    {{ showAddForm ? 'Annuler' : 'Nouvelle alerte' }}
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Formulaire d'ajout -->
                <div v-if="showAddForm" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ajouter une nouvelle alerte</h3>
                    
                    <form @submit.prevent="addAlert">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <InputLabel for="id_product" value="Produit" />
                                <select
                                    id="id_product"
                                    v-model="newAlertForm.id_product"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Sélectionner un produit</option>
                                    <option v-for="product in products" :key="product.id_product" :value="product.id_product">
                                        {{ product.prolibcommercial }}
                                    </option>
                                </select>
                                <InputError :message="newAlertForm.errors.id_product" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="threshold_quantity" value="Seuil d'alerte" />
                                <TextInput
                                    id="threshold_quantity"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    v-model="newAlertForm.threshold_quantity"
                                    required
                                />
                                <InputError :message="newAlertForm.errors.threshold_quantity" class="mt-2" />
                            </div>
                            
                            <div class="flex items-center mt-4">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="newAlertForm.is_active" class="rounded border-gray-300 text-indigo-600">
                                    <span class="ml-2 text-sm text-gray-600">Alerte active</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <SecondaryButton
                                type="button"
                                @click="showAddForm = false"
                                class="mr-3"
                            >
                                Annuler
                            </SecondaryButton>
                            <PrimaryButton
                                :class="{ 'opacity-25': newAlertForm.processing }"
                                :disabled="newAlertForm.processing"
                            >
                                Enregistrer
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                
                <!-- Liste des alertes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Alertes de stock configurées</h3>
                        
                        <div v-if="alerts.length === 0" class="text-center py-8 text-gray-500">
                            Aucune alerte de stock configurée. Cliquez sur "Nouvelle alerte" pour en créer une.
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produit
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Code
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Seuil d'alerte
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stock actuel
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="alert in alerts" :key="alert.id" :class="{ 'bg-red-50': alert.is_below_threshold, 'bg-gray-50': !alert.is_active }">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ alert.prolibcommercial }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ alert.procode }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <!-- Affichage normal ou formulaire d'édition -->
                                            <div v-if="editingAlert && editingAlert.id === alert.id">
                                                <TextInput
                                                    type="number"
                                                    min="1"
                                                    class="w-24"
                                                    v-model="editForm.threshold_quantity"
                                                    required
                                                />
                                                <InputError :message="editForm.errors.threshold_quantity" class="mt-1" />
                                            </div>
                                            <div v-else>
                                                {{ formatNumber(alert.threshold_quantity) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatNumber(alert.current_stock) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <!-- Affichage normal ou formulaire d'édition pour l'état actif -->
                                            <div v-if="editingAlert && editingAlert.id === alert.id" class="flex items-center">
                                                <input type="checkbox" v-model="editForm.is_active" class="rounded border-gray-300 text-indigo-600">
                                                <span class="ml-2 text-sm text-gray-600">Actif</span>
                                            </div>
                                            <div v-else>
                                                <span v-if="!alert.is_active" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Désactivée
                                                </span>
                                                <span v-else-if="alert.is_below_threshold" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Niveau critique
                                                </span>
                                                <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Niveau normal
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <!-- Boutons d'action normaux ou boutons de sauvegarde/annulation -->
                                            <div v-if="editingAlert && editingAlert.id === alert.id" class="flex space-x-2">
                                                <button 
                                                    @click="updateAlert" 
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    :disabled="editForm.processing"
                                                >
                                                    Sauvegarder
                                                </button>
                                                <button 
                                                    @click="cancelEdit" 
                                                    class="text-gray-600 hover:text-gray-900"
                                                >
                                                    Annuler
                                                </button>
                                            </div>
                                            <div v-else class="flex space-x-2">
                                                <button 
                                                    @click="startEditing(alert)" 
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Modifier
                                                </button>
                                                <button 
                                                    @click="deleteAlert(alert.id)" 
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Supprimer
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