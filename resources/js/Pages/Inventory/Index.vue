<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import _ from 'lodash';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    inventory: Object,
    depots: Array,
    products: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const depotFilter = ref(props.filters.depot_id || '');
const productFilter = ref(props.filters.product_id || '');

const showAdjustModal = ref(false);
const selectedProduct = ref(null);
const selectedDepot = ref(null);

const adjustForm = useForm({
    id_depot: '',
    id_product: '',
    quantity: 0,
    comments: ''
});

const openAdjustModal = (item) => {
    selectedProduct.value = props.products.find(p => p.id_product === item.id_product);
    selectedDepot.value = props.depots.find(d => d.id_depot === item.id_depot);
    
    adjustForm.id_depot = item.id_depot;
    adjustForm.id_product = item.id_product;
    adjustForm.quantity = item.quantity;
    adjustForm.comments = '';
    
    showAdjustModal.value = true;
};

const submitAdjustment = () => {
    adjustForm.post(route('inventory.adjust'), {
        preserveScroll: true,
        onSuccess: () => {
            showAdjustModal.value = false;
        }
    });
};

const performSearch = _.debounce(() => {
    router.get(route('inventory.index'), 
        { 
            search: search.value,
            depot_id: depotFilter.value,
            product_id: productFilter.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}, 300);

watch([search, depotFilter, productFilter], () => {
    performSearch();
});

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString('fr-FR') : '-';
};
</script>

<template>
    <Head title="État de l'inventaire" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    État de l'inventaire
                </h2>
                <Link 
                    :href="route('inventory.report')"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                >
                    Rapports d'inventaire
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="mb-4 flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
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
                                placeholder="Rechercher un produit..."
                            >
                        </div>
                    </div>
                    <div class="w-48">
                        <select 
                            v-model="depotFilter"
                            class="block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Tous les dépôts</option>
                            <option 
                                v-for="depot in depots" 
                                :key="depot.id_depot" 
                                :value="depot.id_depot"
                            >
                                {{ depot.depot_name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-48">
                        <select 
                            v-model="productFilter"
                            class="block w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="">Tous les produits</option>
                            <option 
                                v-for="product in products" 
                                :key="product.id_product" 
                                :value="product.id_product"
                            >
                                {{ product.product_reference }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dépôt</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière mise à jour</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mis à jour par</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in inventory.data" :key="`${item.id_depot}-${item.id_product}`">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.depot.depot_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.product.product_reference }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.product.product_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ item.quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(item.last_updated) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ item.lastUpdatedBy.lastname }} {{ item.lastUpdatedBy.firstname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button 
                                            @click="openAdjustModal(item)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            Ajuster
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="inventory.data.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Aucun produit en inventaire trouvé
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="inventory.links" class="mt-4">
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Affichage de
                                    <span class="font-medium">{{ inventory.from }}</span>
                                    à
                                    <span class="font-medium">{{ inventory.to }}</span>
                                    sur
                                    <span class="font-medium">{{ inventory.total }}</span>
                                    résultats
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link
                                        v-for="(link, i) in inventory.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold"
                                        :class="{ 
                                            'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600': link.active,
                                            'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0': !link.active 
                                        }"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal d'ajustement d'inventaire -->
        <Modal :show="showAdjustModal" @close="showAdjustModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Ajuster l'inventaire
                </h2>

                <div v-if="selectedProduct && selectedDepot" class="mb-4">
                    <p class="font-medium">{{ selectedProduct.product_reference }} - {{ selectedProduct.product_name }}</p>
                    <p class="text-sm text-gray-600">Dépôt: {{ selectedDepot.depot_name }}</p>
                </div>

                <form @submit.prevent="submitAdjustment">
                    <div>
                        <InputLabel for="quantity" value="Nouvelle quantité" />
                        <TextInput
                            id="quantity"
                            type="number"
                            v-model="adjustForm.quantity"
                            class="mt-1 block w-full"
                            min="0"
                            required
                        />
                        <InputError :message="adjustForm.errors.quantity" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="comments" value="Commentaires" />
                        <textarea
                            id="comments"
                            v-model="adjustForm.comments"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            rows="3"
                        ></textarea>
                        <InputError :message="adjustForm.errors.comments" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton type="button" @click="showAdjustModal = false">Annuler</SecondaryButton>
                        <PrimaryButton 
                            class="ml-3" 
                            :disabled="adjustForm.processing"
                        >
                            Confirmer l'ajustement
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>