<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    productTypes: Object,
    unitTypes: Object,
    valveTypes: Object
});

// Formulaire principal avec un attribut par défaut
const form = useForm({
    procode: '',
    protype: '',
    unitcode: '',
    proprice: '',
    prolibcommercial: '',
    pro_real_capacity: '',
    attributes: [
        {
            barcode: '',
            serial_number: '',
            ownership: '',
            manufacture_date: '',
            expiration_date: '',
            valve_type: '',
            manufacturer: '',
            last_test_date: '',
            state: 'A'  // A pour Actif par défaut
        }
    ]
});

// Ajouter un nouvel attribut
const addAttribute = () => {
    form.attributes.push({
        barcode: '',
        serial_number: '',
        ownership: '',
        manufacture_date: '',
        expiration_date: '',
        valve_type: '',
        manufacturer: '',
        last_test_date: '',
        state: 'A'
    });
};

// Supprimer un attribut
const removeAttribute = (index) => {
    if (form.attributes.length > 1) {
        form.attributes.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Créer un produit" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un produit
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <!-- Informations du produit -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations du produit</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Code produit</label>
                                    <input 
                                        type="text" 
                                        v-model="form.procode" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        maxlength="5"
                                    >
                                    <div v-if="form.errors.procode" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.procode }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type de produit</label>
                                    <select 
                                        v-model="form.protype"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                        <option value="">Sélectionnez un type</option>
                                        <option 
                                            v-for="(label, value) in productTypes" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.protype" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.protype }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unité</label>
                                    <select 
                                        v-model="form.unitcode"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                        <option value="">Sélectionnez une unité</option>
                                        <option 
                                            v-for="(label, value) in unitTypes" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.unitcode" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.unitcode }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prix</label>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        v-model="form.proprice" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.proprice" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.proprice }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Libellé commercial</label>
                                    <input 
                                        type="text" 
                                        v-model="form.prolibcommercial" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.prolibcommercial" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.prolibcommercial }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Capacité réelle</label>
                                    <input 
                                        type="number" 
                                        step="0.0000000001"
                                        v-model="form.pro_real_capacity" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.pro_real_capacity" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.pro_real_capacity }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attributs -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Attributs du produit</h3>
                                <button 
                                    type="button"
                                    @click="addAttribute"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                                >
                                    Ajouter un attribut
                                </button>
                            </div>

                            <div v-for="(attribute, index) in form.attributes" :key="index" class="mb-6 p-4 border rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-medium">Attribut {{ index + 1 }}</h4>
                                    <button 
                                        v-if="form.attributes.length > 1"
                                        type="button"
                                        @click="removeAttribute(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        Supprimer
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Code-barres</label>
                                        <input 
                                            type="text" 
                                            v-model="attribute.barcode" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`attributes.${index}.barcode`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`attributes.${index}.barcode`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Numéro de série</label>
                                        <input 
                                            type="text" 
                                            v-model="attribute.serial_number" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`attributes.${index}.serial_number`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`attributes.${index}.serial_number`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Propriétaire</label>
                                        <input 
                                            type="text" 
                                            v-model="attribute.ownership" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type de valve</label>
                                        <select 
                                            v-model="attribute.valve_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">Sélectionnez un type</option>
                                            <option 
                                                v-for="(label, type) in valveTypes" 
                                                :key="type" 
                                                :value="type"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors[`attributes.${index}.valve_type`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`attributes.${index}.valve_type`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fabricant</label>
                                        <input 
                                            type="text" 
                                            v-model="attribute.manufacturer" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date de fabrication</label>
                                        <input 
                                            type="datetime-local" 
                                            v-model="attribute.manufacture_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`attributes.${index}.manufacture_date`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`attributes.${index}.manufacture_date`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date d'expiration</label>
                                        <input 
                                            type="datetime-local" 
                                            v-model="attribute.expiration_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date du dernier test</label>
                                        <input 
                                            type="datetime-local" 
                                            v-model="attribute.last_test_date" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">État</label>
                                        <select 
                                            v-model="attribute.state"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="A">Actif</option>
                                            <option value="I">Inactif</option>
                                        </select>
                                        <div v-if="form.errors[`attributes.${index}.state`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`attributes.${index}.state`] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button 
                                type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Création...' : 'Créer le produit' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>