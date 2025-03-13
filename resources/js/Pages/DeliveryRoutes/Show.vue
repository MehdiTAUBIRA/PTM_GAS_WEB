<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    route: Object,
    stops: Array,
    stopStatuses: Object
});

// Formulaire pour la mise à jour du statut d'un arrêt
const stopForm = useForm({
    status: '',
    actual_arrival_time: ''
});

// ID de l'arrêt en cours de modification
const editingStopId = ref(null);

// Démarrer l'édition d'un arrêt
const startEditingStop = (stop) => {
    editingStopId.value = stop.id_route_stop;
    stopForm.status = stop.status;
    stopForm.actual_arrival_time = stop.actual_arrival_time 
        ? new Date(stop.actual_arrival_time).toTimeString().substring(0, 5) 
        : '';
};

// Annuler l'édition
const cancelEditingStop = () => {
    editingStopId.value = null;
    stopForm.reset();
};

// Mettre à jour le statut d'un arrêt
const updateStopStatus = (stopId) => {
    stopForm.put(route('delivery-routes.update-stop', [props.route.id_route, stopId]), {
        preserveScroll: true,
        onSuccess: () => {
            editingStopId.value = null;
        }
    });
};

// Formatage des dates et heures
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

// Classes CSS pour les statuts
const getStatusClass = (status) => {
    const classes = {
        'planned': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'pending': 'bg-gray-100 text-gray-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

// Obtenir le statut de la tournée formaté
const getRouteStatus = () => {
    const statuses = {
        'planned': 'Planifiée',
        'in_progress': 'En cours',
        'completed': 'Terminée',
        'cancelled': 'Annulée'
    };
    return statuses[props.route.status] || props.route.status;
};
</script>

<template>
    <Head :title="`Détail de la tournée du ${formatDate(route.route_date)}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Détail de la tournée
                </h2>
                <div class="flex space-x-2">
                    <Link
                        v-if="route.status !== 'completed' && route.status !== 'cancelled'"
                        :href="route('delivery-routes.edit', route.id_route)"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring focus:ring-yellow-300 transition"
                    >
                        Modifier
                    </Link>
                    <Link
                        :href="route('delivery-routes.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 transition"
                    >
                        Retour
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Informations générales de la tournée -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations de la tournée</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="font-medium">{{ formatDate(route.route_date) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Horaires</p>
                            <p class="font-medium">{{ formatTime(route.start_time) }} - {{ formatTime(route.end_time) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Statut</p>
                            <p class="font-medium">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(route.status)">
                                    {{ getRouteStatus() }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Chauffeur</p>
                            <p class="font-medium">{{ route.driver.drivername }} {{ route.driver.driversurname }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Véhicule</p>
                            <p class="font-medium">{{ route.vehicle.vehiclename }} ({{ route.vehicle.vehicleimmat }})</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nombre d'arrêts</p>
                            <p class="font-medium">{{ stops.length }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des arrêts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Arrêts programmés</h3>
                    
                    <div v-if="stops.length === 0" class="text-center text-gray-500 py-4">
                        Aucun arrêt programmé pour cette tournée
                    </div>
                    
                    <div v-else class="space-y-6">
                        <div v-for="stop in stops" :key="stop.id_route_stop" class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex items-start space-x-4">
                                    <div class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0">
                                        {{ stop.stop_order }}
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ stop.order.order_number }}</p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Client:</strong> {{ stop.order.customer.customer_name }} {{ stop.order.customer.customer_firstname }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Adresse:</strong> {{ stop.order.address.address }}, {{ stop.order.address.postalcode }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Téléphone:</strong> {{ stop.order.customer.customer_phone }}
                                        </p>
                                        
                                        <!-- Détails de la commande -->
                                        <div class="mt-2">
                                            <p class="text-sm font-medium text-gray-700">Produits à livrer:</p>
                                            <ul class="list-disc list-inside text-sm text-gray-600 ml-2">
                                                <li v-for="detail in stop.order.details" :key="detail.id_order_detail">
                                                    {{ detail.quantity }} × {{ detail.product.prolibcommercial }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <!-- Affichage du statut ou formulaire d'édition -->
                                    <div v-if="editingStopId === stop.id_route_stop" class="bg-gray-50 p-3 rounded-lg">
                                        <InputLabel for="status" value="Statut" class="mb-1" />
                                        <select
                                            id="status"
                                            v-model="stopForm.status"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                            required
                                        >
                                            <option v-for="(label, value) in stopStatuses" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>
                                        <InputError :message="stopForm.errors.status" class="mt-1" />
                                        
                                        <InputLabel for="actual_arrival_time" value="Heure d'arrivée" class="mb-1 mt-3" />
                                        <TextInput
                                            id="actual_arrival_time"
                                            type="time"
                                            class="w-full"
                                            v-model="stopForm.actual_arrival_time"
                                        />
                                        <InputError :message="stopForm.errors.actual_arrival_time" class="mt-1" />
                                        
                                        <div class="flex justify-end space-x-2 mt-3">
                                            <SecondaryButton type="button" @click="cancelEditingStop" class="text-xs">
                                                Annuler
                                            </SecondaryButton>
                                            <PrimaryButton 
                                                type="button" 
                                                @click="updateStopStatus(stop.id_route_stop)"
                                                :disabled="stopForm.processing"
                                                class="text-xs"
                                            >
                                                Enregistrer
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                    
                                    <div v-else>
                                        <div class="text-right mb-2">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(stop.status)">
                                                {{ stopStatuses[stop.status] }}
                                            </span>
                                        </div>
                                        
                                        <div v-if="stop.actual_arrival_time" class="text-sm text-gray-600 text-right mb-2">
                                            <strong>Arrivée:</strong> {{ formatTime(stop.actual_arrival_time) }}
                                        </div>
                                        
                                        <button 
                                            v-if="route.status !== 'completed' && route.status !== 'cancelled'"
                                            type="button"
                                            @click="startEditingStop(stop)"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm"
                                        >
                                            Mettre à jour
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>