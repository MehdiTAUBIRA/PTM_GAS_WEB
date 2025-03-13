<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    depots: Array,
    selectedDepot: Object,
    stockByProduct: Array
});

const form = useForm({
    depot_id: props.selectedDepot ? props.selectedDepot.id_depot : ''
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('fr-FR').format(num);
};

// Automatiquement soumettre le formulaire lorsque le dépôt change
watch(() => form.depot_id, (newValue) => {
    if (newValue) {
        form.get(route('stocks.by-depot'), {
            preserveState: true,
            preserveScroll: true
        });
    }
});
</script>

<template>
    <Head title="Stocks par dépôt" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stocks par dépôt
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Sélection du dépôt -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <form @submit.prevent="form.get(route('stocks.by-depot'))">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="depot_id" value="Sélectionner un dépôt" />
                                <select
                                    id="depot_id"
                                    v-model="form.depot_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Choisir un dépôt</option>
                                    <option v-for="depot in depots" :key="depot.id_depot" :value="depot.id_depot">
                                        {{ depot.depotlabel }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <PrimaryButton type="submit" :disabled="!form.depot_id">
                                    Afficher le stock
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Informations du dépôt -->
                <div v-if="selectedDepot" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations du dépôt</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Nom du dépôt</p>
                            <p class="font-medium">{{ selectedDepot.depotlabel }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Adresse</p>
                            <p class="font-medium">
                                {{ selectedDepot.depotaddress }}, {{ selectedDepot.depotpostalcode }} {{ selectedDepot.depotcity }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Téléphone</p>
                            <p class="font-medium">{{ selectedDepot.depotphone }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des stocks -->
                <div v-if="stockByProduct" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Stock du dépôt {{ selectedDepot?.depotlabel }}
                        </h3>
                        
                        <div v-if="stockByProduct.length === 0" class="text-center py-8 text-gray-500">
                            Aucun produit en stock dans ce dépôt.
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
                                            Quantité en stock
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dernière mise à jour
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stock in stockByProduct" :key="stock.id_product" :class="{ 'bg-red-50': stock.is_below_threshold }">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ stock.prolibcommercial }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ stock.procode }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatNumber(stock.quantity) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(stock.last_updated) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="stock.is_below_threshold" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Alerte stock bas
                                            </span>
                                            <span v-else-if="stock.has_alert" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Stock suffisant
                                            </span>
                                            <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Non surveillé
                                            </span>
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