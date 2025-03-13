<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    driver: Object,
    routes: Array
});

// Formulaire pour la mise à jour du statut d'un arrêt
const stopForm = useForm({
    status: '',
    actual_arrival_time: ''
});

// ID de l'arrêt en cours de modification
const editingStopId = ref(null);
// ID de la tournée en cours d'affichage détaillée
const expandedRouteId = ref(null);

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
const updateStopStatus = (routeId, stopId) => {
    stopForm.put(route('delivery-routes.update-stop', [routeId, stopId]), {
        preserveScroll: true,
        onSuccess: () => {
            editingStopId.value = null;
        }
    });
};

// Basculer l'affichage détaillé d'une tournée
const toggleRouteExpand = (routeId) => {
    expandedRouteId.value = expandedRouteId.value === routeId ? null : routeId;
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

// Statut en français
const getStatusText = (status) => {
    const statuses = {
        'planned': 'Planifiée',
        'in_progress': 'En cours',
        'completed': 'Terminée',
        'cancelled': 'Annulée',
        'pending': 'En attente',
        'completed': 'Terminé',
        'failed': 'Échoué'
    };
    return statuses[status] || status;
};

// Vérifier si une tournée est prévue pour aujourd'hui
const isToday = (dateString) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const date = new Date(dateString);
    date.setHours(0, 0, 0, 0);
    
    return today.getTime() === date.getTime();
};
</script>

<template>
    <Head :title="`Tournées de ${driver.drivername} ${driver.driversurname}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mes tournées
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Informations du chauffeur -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-4 rounded-full mr-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ driver.drivername }} {{ driver.driversurname }}</h3>
                            <p class="text-sm text-gray-600">{{ driver.driverphone }}</p>
                            <p class="text-sm text-gray-600">{{ driver.driveremail }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des tournées -->
                <div v-if="routes.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500">Aucune tournée prévue</p>
                </div>
                
                <div v-else class="space-y-6">
                    <!-- Tournées d'aujourd'hui -->
                    <div v-for="deliveryRoute in routes" :key="deliveryRoute.id_route">
                        <div 
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4"
                            :class="isToday(deliveryRoute.route_date) ? 'border-blue-500' : 'border-gray-300'"
                        >
                            <!-- En-tête de la tournée -->
                            <div class="flex justify-between items-center cursor-pointer" @click="toggleRouteExpand(deliveryRoute.id_route)">
                                <div>
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            Tournée du {{ formatDate(deliveryRoute.route_date) }}
                                        </h3>
                                        <span v-if="isToday(deliveryRoute.route_date)" class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            Aujourd'hui
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ formatTime(deliveryRoute.start_time) }} - {{ formatTime(deliveryRoute.end_time) }} | 
                                        Véhicule: {{ deliveryRoute.vehicle.vehiclename }} ({{ deliveryRoute.vehicle.vehicleimmat }})
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full mr-3" :class="getStatusClass(deliveryRoute.status)">
                                        {{ getStatusText(deliveryRoute.status) }}
                                    </span>
                                    <svg class="w-6 h-6 text-gray-500 transform transition-transform" :class="{ 'rotate-180': expandedRouteId === deliveryRoute.id_route }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Détails de la tournée -->
                            <div v-if="expandedRouteId === deliveryRoute.id_route" class="mt-4 pl-4 border-l-2 border-gray-200">
                                <h4 class="text-md font-medium text-gray-700 mb-2">Arrêts programmés</h4>
                                
                                <div class="space-y-4">
                                    <div v-for="stop in deliveryRoute.stops" :key="stop.id_route_stop" class="border rounded-lg p-4">
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
                                                        <option value="pending">En attente</option>
                                                        <option value="completed">Terminé</option>
                                                        <option value="failed">Échoué</option>
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
                                                            @click="updateStopStatus(deliveryRoute.id_route, stop.id_route_stop)"
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
                                                            {{ getStatusText(stop.status) }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div v-if="stop.actual_arrival_time" class="text-sm text-gray-600 text-right mb-2">
                                                        <strong>Arrivée:</strong> {{ formatTime(stop.actual_arrival_time) }}
                                                    </div>
                                                    
                                                    <button 
                                                        v-if="isToday(deliveryRoute.route_date) && deliveryRoute.status === 'in_progress'"
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>