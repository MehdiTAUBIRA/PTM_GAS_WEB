<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    drivercode: '',
    drivername: '',
    driversurname: '',
    driverlastmedicalvisit: '',
    driverlastlicensecontrol: '',
    driveraccreditation: '',
    driveraddress: '',
    driveraddressnext: '',
    driverphone: '',
    drivercity: '',
    driverpostalcode: '',
    drivercountry: '',
    drivercomments: '',
    driveremail: '',
    drivermobile: '',
    driverattestationdate: '',
    drivernextmedicalvisit: '',
    drivernextlicensecontrol: '',
    username: '',
    driverpassword: ''
});

const submit = () => {
    form.post(route('drivers.store'));
};
</script>

<template>
    <Head title="Créer un chauffeur" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Créer un chauffeur
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code chauffeur</label>
                                <input 
                                    type="text" 
                                    v-model="form.drivercode" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    maxlength="5"
                                >
                                <div v-if="form.errors.drivercode" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivercode }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input 
                                    type="text" 
                                    v-model="form.drivername" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.drivername" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivername }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input 
                                    type="text" 
                                    v-model="form.driversurname" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driversurname" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driversurname }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input 
                                    type="email" 
                                    v-model="form.driveremail" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driveremail" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driveremail }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input 
                                    type="text" 
                                    v-model="form.driverphone" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driverphone" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driverphone }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mobile</label>
                                <input 
                                    type="text" 
                                    v-model="form.drivermobile" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.drivermobile" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivermobile }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adresse</label>
                                <input 
                                    type="text" 
                                    v-model="form.driveraddress" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driveraddress" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driveraddress }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Complément d'adresse</label>
                                <input 
                                    type="text" 
                                    v-model="form.driveraddressnext" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driveraddressnext" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driveraddressnext }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ville</label>
                                <input 
                                    type="text" 
                                    v-model="form.drivercity" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.drivercity" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivercity }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code postal</label>
                                <input 
                                    type="text" 
                                    v-model="form.driverpostalcode" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driverpostalcode" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driverpostalcode }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pays</label>
                                <input 
                                    type="text" 
                                    v-model="form.drivercountry" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    maxlength="3"
                                >
                                <div v-if="form.errors.drivercountry" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivercountry }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dernière visite médicale</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.driverlastmedicalvisit" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driverlastmedicalvisit" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driverlastmedicalvisit }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prochain visite médicale</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.drivernextmedicalvisit" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.drivernextmedicalvisit" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivernextmedicalvisit }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dernier contrôle permis</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.driverlastlicensecontrol" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driverlastlicensecontrol" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driverlastlicensecontrol }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prochain contrôle permis</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.drivernextlicensecontrol" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.drivernextlicensecontrol" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivernextlicensecontrol }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date d'attestation</label>
                                <input 
                                    type="datetime-local" 
                                    v-model="form.driverattestationdate" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driverattestationdate" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driverattestationdate }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Accréditation</label>
                                <input 
                                    type="text" 
                                    v-model="form.driveraccreditation" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driveraccreditation" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driveraccreditation }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Commentaires</label>
                                <input 
                                    type="text" 
                                    v-model="form.drivercomments" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.drivercomments" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.drivercomments }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                                <input 
                                    type="text" 
                                    v-model="form.username" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.username }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
                                <input 
                                    type="password" 
                                    v-model="form.driverpassword" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                <div v-if="form.errors.driverpassword" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.driverpassword }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button 
                                type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Création...' : 'Créer le chauffeur' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>