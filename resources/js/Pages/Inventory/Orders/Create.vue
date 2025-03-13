<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    depots: Array,
    employees: Array,
    products: Array
});

const form = useForm({
    id_depot: '',
    id_employee_assigned: '',
    comments: '',
    products: [
        { id_product: '', expected_quantity: 0 }
    ]
});

const addProduct = () => {
    form.products.push({
        id_product: '',
        expected_quantity: 0
    });
};

const removeProduct = (index) => {
    form.products.splice(index, 1);
};

const submit = () => {
    form.post(route('inventory-orders.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const getProductById = (id) => {
    return props.products.find(product => product.id_product == id);
};
</script>

<template>
    <Head title="Créer un ordre d'inventaire" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Créer un nouvel ordre d'inventaire
                </h2>
                <Link
                    :href="route('inventory-orders.index')"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
                >
                    Retour à la liste
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Dépôt -->
                            <div>
                                <InputLabel for="id_depot" value="Dépôt" />
                                <select
                                    id="id_depot"
                                    v-model="form.id_depot"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Sélectionner un dépôt</option>
                                    <option 
                                        v-for="depot in depots" 
                                        :key="depot.id_depot" 
                                        :value="depot.id_depot"
                                    >
                                        {{ depot.depot_name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.id_depot" class="mt-2" />
                            </div>

                            <!-- Employé assigné -->
                            <div>
                                <InputLabel for="id_employee_assigned" value="Employé assigné" />
                                <select
                                    id="id_employee_assigned"
                                    v-model="form.id_employee_assigned"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Sélectionner un employé</option>
                                    <option 
                                        v-for="employee in employees" 
                                        :key="employee.id_employee" 
                                        :value="employee.id_employee"
                                    >
                                        {{ employee.lastname }} {{ employee.firstname }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.id_employee_assigned" class="mt-2" />
                            </div>
                        </div>

                        <!-- Commentaires -->
                        <div class="mb-6">
                            <InputLabel for="comments" value="Commentaires" />
                            <textarea
                                id="comments"
                                v-model="form.comments"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.comments" class="mt-2" />
                        </div>

                        <!-- Produits -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium">Produits à inventorier</h3>
                                <SecondaryButton type="button" @click="addProduct">
                                    Ajouter un produit
                                </SecondaryButton>
                            </div>

                            <div v-if="form.errors['products']" class="mb-4 text-sm text-red-600">
                                {{ form.errors['products'] }}
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div 
                                    v-for="(product, index) in form.products" 
                                    :key="index"
                                    class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 pb-4 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0"
                                >
                                    <!-- Produit -->
                                    <div class="md:col-span-7">
                                        <InputLabel :for="`product-${index}`" value="Produit" class="text-sm" />
                                        <select
                                            :id="`product-${index}`"
                                            v-model="product.id_product"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            required
                                        >
                                            <option value="" disabled>Sélectionner un produit</option>
                                            <option 
                                                v-for="p in products" 
                                                :key="p.id_product" 
                                                :value="p.id_product"
                                            >
                                                {{ p.product_reference }} - {{ p.product_name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors[`products.${index}.id_product`]" class="mt-1" />
                                    </div>

                                    <!-- Quantité attendue -->
                                    <div class="md:col-span-3">
                                        <InputLabel :for="`quantity-${index}`" value="Quantité attendue" class="text-sm" />
                                        <TextInput
                                            :id="`quantity-${index}`"
                                            type="number"
                                            v-model="product.expected_quantity"
                                            min="0"
                                            class="mt-1 block w-full text-sm"
                                            required
                                        />
                                        <InputError :message="form.errors[`products.${index}.expected_quantity`]" class="mt-1" />
                                    </div>

                                    <!-- Actions -->
                                    <div class="md:col-span-2 flex items-end">
                                        <DangerButton 
                                            type="button" 
                                            @click="removeProduct(index)"
                                            :disabled="form.products.length <= 1"
                                            class="mt-1 w-full justify-center"
                                        >
                                            Supprimer
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <PrimaryButton 
                                :class="{ 'opacity-25': form.processing }" 
                                :disabled="form.processing"
                            >
                                Créer l'ordre d'inventaire
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>