<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    depotpostalcode: '',
    depotcode: '',
    depotlabel: '',
    depotaddress: '',
    depotcity: '',
    depotphone: '',
    depotgpsx: null,
    depotgpsy: null,
});

const submit = () => {
    form.post(route('depots.store'));
};
</script>

<template>
    <Head title="Créer un dépôt" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un dépôt
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code postal</label>
                                <input 
                                    type="text" 
                                    v-model="form.depotpostalcode" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotpostalcode" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotpostalcode }}
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code dépôt</label>
                                <input 
                                    type="text" 
                                    v-model="form.depotcode" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotcode" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotcode }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom du dépôt</label>
                                <input 
                                    type="text" 
                                    v-model="form.depotlabel" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotlabel" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotlabel }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adresse</label>
                                <input 
                                    type="text" 
                                    v-model="form.depotaddress" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotaddress" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotaddress }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ville</label>
                                <input 
                                    type="text" 
                                    v-model="form.depotcity" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotcity" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotcity }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input 
                                    type="text" 
                                    v-model="form.depotphone" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotphone" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotphone }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">GPS X</label>
                                <input 
                                    type="number" 
                                    step="0.0000000001" 
                                    v-model="form.depotgpsx" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotgpsx" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotgpsx }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">GPS Y</label>
                                <input 
                                    type="number" 
                                    step="0.0000000001" 
                                    v-model="form.depotgpsy" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.depotgpsy" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.depotgpsy }}
                                </div>
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>