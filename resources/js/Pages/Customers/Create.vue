<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    addressTypes: Object
});

// Formulaire principal
const form = useForm({
    customer_name: '',
    customer_firstname: '',
    customer_phone: '',
    customer_email: '',
    addresses: [
        {
            address: '',
            postalcode: '',
            country: '',
            address_type: ''
        }
    ]
});

// Ajouter une nouvelle adresse
const addAddress = () => {
    form.addresses.push({
        address: '',
        postalcode: '',
        country: '',
        address_type: ''
    });
};

// Supprimer une adresse
const removeAddress = (index) => {
    if (form.addresses.length > 1) {
        form.addresses.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('customers.store'));
};
</script>

<template>
    <Head title="Créer un client" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un client
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <!-- Informations du client -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations du client</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input 
                                        type="text" 
                                        v-model="form.customer_name" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.customer_name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.customer_name }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                    <input 
                                        type="text" 
                                        v-model="form.customer_firstname" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.customer_firstname" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.customer_firstname }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input 
                                        type="email" 
                                        v-model="form.customer_email" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.customer_email" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.customer_email }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                    <input 
                                        type="text" 
                                        v-model="form.customer_phone" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                    <div v-if="form.errors.customer_phone" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.customer_phone }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Adresses -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Adresses</h3>
                                <button 
                                    type="button"
                                    @click="addAddress"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                                >
                                    Ajouter une adresse
                                </button>
                            </div>

                            <div v-for="(address, index) in form.addresses" :key="index" class="mb-6 p-4 border rounded-lg">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-medium">Adresse {{ index + 1 }}</h4>
                                    <button 
                                        v-if="form.addresses.length > 1"
                                        type="button"
                                        @click="removeAddress(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        Supprimer
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type d'adresse</label>
                                        <select 
                                            v-model="address.address_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="">Sélectionnez un type</option>
                                            <option 
                                                v-for="(label, type) in addressTypes" 
                                                :key="type" 
                                                :value="type"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors[`addresses.${index}.address_type`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`addresses.${index}.address_type`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Adresse</label>
                                        <input 
                                            type="text" 
                                            v-model="address.address" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`addresses.${index}.address`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`addresses.${index}.address`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Code postal</label>
                                        <input 
                                            type="text" 
                                            v-model="address.postalcode" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`addresses.${index}.postalcode`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`addresses.${index}.postalcode`] }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Pays</label>
                                        <input 
                                            type="text" 
                                            v-model="address.country" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        <div v-if="form.errors[`addresses.${index}.country`]" class="text-red-500 text-sm mt-1">
                                            {{ form.errors[`addresses.${index}.country`] }}
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
                                {{ form.processing ? 'Création...' : 'Créer le client' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>