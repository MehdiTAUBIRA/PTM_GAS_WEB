<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    routes: Object,
    drivers: Array,
    statuses: Object,
    filters: Object
});

const form = useForm({
    date: props.filters.date || '',
    driver_id: props.filters.driver_id || '',
    status: props.filters.status || ''
});

// Soumettre automatiquement quand les filtres changent
watch([() => form.date, () => form.driver_id, () => form.status], () => {
    form.get(route('delivery-routes.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['routes']
    });
});

const resetFilters = () => {
    form.date = '';
    form.driver_id = '';
    form.status = '';
    form.get(route('delivery-routes.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['routes']
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatTime = (dateTimeString) => {
    if (!dateTimeString) return '-';
    const date = new Date(dateTimeString);
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (status) => {
    const classes = {
        'planned': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Gestion des tournées" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestion des tournées
        </h2>
        <div class="flex space-x-2">
            <Link
                :href="route('orders.create')"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-300 transition"
            >
                Nouvelle commande
            </Link>
            <Link
                :href="route('delivery-routes.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 transition"
            >
                Nouvelle tournée
            </Link>
        </div>
    </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <InputLabel for="date" value="Date" />
                            <TextInput
                                id="date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.date"
                            />
                        </div>
                        
                        <div>
                            <InputLabel for="driver_id" value="Chauffeur" />
                            <select
                                id="driver_id"
                                v-model="form.driver_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="">Tous les chauffeurs</option>
                                <option v-for="driver in drivers" :key="driver.id_driver" :value="driver.id_driver">
                                    {{ driver.drivername }} {{ driver.driversurname }}
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <InputLabel for="status" value="Statut" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >
                                <option value="">Tous les statuts</option>
                                <option v-for="(label, value) in statuses" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <SecondaryButton @click="resetFilters" class="h-10">
                                Réinitialiser
                            </SecondaryButton>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des tournées -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Horaires
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Chauffeur
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Véhicule
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Livraisons
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="deliveryRoute in routes.data" :key="deliveryRoute.id_route">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(deliveryRoute.route_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatTime(deliveryRoute.start_time) }} - {{ formatTime(deliveryRoute.end_time) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ deliveryRoute.driver.drivername }} {{ deliveryRoute.driver.driversurname }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ deliveryRoute.vehicle.vehiclename }} ({{ deliveryRoute.vehicle.vehicleimmat }})
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(deliveryRoute.status)">
                                                {{ statuses[deliveryRoute.status] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ deliveryRoute.stops_count || '0' }} arrêts
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link :href="route('delivery-routes.show', deliveryRoute.id_route)" class="text-indigo-600 hover:text-indigo-900">
                                                    Détails
                                                </Link>
                                                <Link v-if="deliveryRoute.status !== 'completed' && deliveryRoute.status !== 'cancelled'" :href="route('delivery-routes.edit', deliveryRoute.id_route)" class="text-yellow-600 hover:text-yellow-900">
                                                    Modifier
                                                </Link>
                                                <button 
                                                    v-if="deliveryRoute.status !== 'completed'" 
                                                    @click="$inertia.delete(route('delivery-routes.destroy', deliveryRoute.id_route), { 
                                                        onBefore: () => confirm('Êtes-vous sûr de vouloir supprimer cette tournée ?') 
                                                    })"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="routes.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Aucune tournée trouvée
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <Pagination :links="routes.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>