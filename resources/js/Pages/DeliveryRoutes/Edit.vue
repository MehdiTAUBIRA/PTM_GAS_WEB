<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    route: Object,
    drivers: Array,
    vehicles: Array,
    pendingOrders: Array,
    currentStops: Array
});

const statuses = {
    'planned': 'Planifiée',
    'in_progress': 'En cours',
    'completed': 'Terminée',
    'cancelled': 'Annulée'
};

const form = useForm({
    route_date: props.route.route_date,
    id_vehicle: props.route.id_vehicle,
    id_driver: props.route.id_driver,
    start_time: props.route.start_time,
    end_time: props.route.end_time || '',
    status: props.route.status,
    orders: []
});

// Initialiser les ordres sélectionnés à partir de currentStops
const selectedOrders = ref([]);

// Méthode pour initialiser les ordres sélectionnés
const initializeSelectedOrders = () => {
    // Trouver les ordres correspondants dans pendingOrders et les ajouter à selectedOrders
    const currentOrderIds = props.currentStops.map(stop => stop.id_order);
    
    currentOrderIds.forEach(orderId => {
        const order = props.pendingOrders.find(o => o.id_order === orderId);
        if (order) {
            const stopOrder = props.currentStops.find(s => s.id_order === orderId).stop_order;
            selectedOrders.value.push({
                ...order,
                stop_order: stopOrder
            });
        }
    });
    
    // Trier par stop_order
    selectedOrders.value.sort((a, b) => a.stop_order - b.stop_order);
    updateFormOrders();
};

// Initialiser au chargement du composant
onMounted(() => {
    initializeSelectedOrders();
});

// Méthode pour ajouter un ordre
const addOrder = (order) => {
    if (!selectedOrders.value.find(o => o.id_order === order.id_order)) {
        selectedOrders.value.push({
            ...order,
            stop_order: selectedOrders.value.length + 1
        });
        updateFormOrders();
    }
};

// Méthode pour supprimer un ordre
const removeOrder = (orderId) => {
    selectedOrders.value = selectedOrders.value.filter(o => o.id_order !== orderId);
    // Réordonner les arrêts
    selectedOrders.value.forEach((order, index) => {
        order.stop_order = index + 1;
    });
    updateFormOrders();
};

// Méthode pour déplacer un ordre vers le haut
const moveOrderUp = (index) => {
    if (index > 0) {
        const temp = selectedOrders.value[index - 1];
        selectedOrders.value[index - 1] = selectedOrders.value[index];
        selectedOrders.value[index] = temp;
        
        // Mise à jour des numéros d'arrêt
        selectedOrders.value.forEach((order, i) => {
            order.stop_order = i + 1;
        });
        
        updateFormOrders();
    }
};

// Méthode pour déplacer un ordre vers le bas
const moveOrderDown = (index) => {
    if (index < selectedOrders.value.length - 1) {
        const temp = selectedOrders.value[index + 1];
        selectedOrders.value[index + 1] = selectedOrders.value[index];
        selectedOrders.value[index] = temp;
        
        // Mise à jour des numéros d'arrêt
        selectedOrders.value.forEach((order, i) => {
            order.stop_order = i + 1;
        });
        
        updateFormOrders();
    }
};

// Mettre à jour les ordres dans le formulaire
const updateFormOrders = () => {
    form.orders = selectedOrders.value.map(order => ({
        id_order: order.id_order,
        stop_order: order.stop_order
    }));
};

// Liste des commandes disponibles (non sélectionnées)
const availableOrders = computed(() => {
    return props.pendingOrders.filter(order => 
        !selectedOrders.value.some(o => o.id_order === order.id_order)
    );
});

// Soumettre le formulaire
const submitForm = () => {
    form.put(route('delivery-routes.update', props.route.id_route));
};

// Formater la date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Modifier une tournée" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifier la tournée
                </h2>
                <Link
                    :href="route('delivery-routes.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 transition"
                >
                    Retour
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitForm">
                    <!-- Informations de la tournée -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informations de la tournée</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="route_date" value="Date de la tournée" />
                                <TextInput
                                    id="route_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.route_date"
                                    required
                                />
                                <InputError :message="form.errors.route_date" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="id_driver" value="Chauffeur" />
                                <select
                                    id="id_driver"
                                    v-model="form.id_driver"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Sélectionner un chauffeur</option>
                                    <option v-for="driver in drivers" :key="driver.id_driver" :value="driver.id_driver">
                                        {{ driver.drivername }} {{ driver.driversurname }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.id_driver" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="id_vehicle" value="Véhicule" />
                                <select
                                    id="id_vehicle"
                                    v-model="form.id_vehicle"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Sélectionner un véhicule</option>
                                    <option v-for="vehicle in vehicles" :key="vehicle.id_vehicle" :value="vehicle.id_vehicle">
                                        {{ vehicle.vehiclename }} ({{ vehicle.vehicleimmat }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.id_vehicle" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="status" value="Statut" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    required
                                >
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="start_time" value="Heure de départ" />
                                <TextInput
                                    id="start_time"
                                    type="time"
                                    class="mt-1 block w-full"
                                    v-model="form.start_time"
                                    required
                                />
                                <InputError :message="form.errors.start_time" class="mt-2" />
                            </div>
                            
                            <div>
                                <InputLabel for="end_time" value="Heure de fin estimée" />
                                <TextInput
                                    id="end_time"
                                    type="time"
                                    class="mt-1 block w-full"
                                    v-model="form.end_time"
                                />
                                <InputError :message="form.errors.end_time" class="mt-2" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sélection des commandes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Commandes disponibles -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Commandes disponibles</h3>
                            
                            <div v-if="availableOrders.length === 0" class="text-center text-gray-500 py-4">
                                Aucune commande disponible pour livraison
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div v-for="order in availableOrders" :key="order.id_order" class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">{{ order.order_number }}</p>
                                            <p class="text-sm text-gray-600">
                                                Client: {{ order.customer.customer_name }} {{ order.customer.customer_firstname }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Adresse: {{ order.address.address }}, {{ order.address.postalcode }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Date de commande: {{ formatDate(order.order_date) }}
                                            </p>
                                        </div>
                                        <button 
                                            type="button"
                                            @click="addOrder(order)"
                                            class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                                        >
                                            Ajouter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Commandes sélectionnées -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Commandes sélectionnées</h3>
                            
                            <div v-if="selectedOrders.length === 0" class="text-center text-gray-500 py-4">
                                Aucune commande sélectionnée pour cette tournée
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div v-for="(order, index) in selectedOrders" :key="order.id_order" class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center">
                                            <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-3">
                                                {{ order.stop_order }}
                                            </span>
                                            <div>
                                                <p class="font-medium">{{ order.order_number }}</p>
                                                <p class="text-sm text-gray-600">
                                                    Client: {{ order.customer.customer_name }} {{ order.customer.customer_firstname }}
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    Adresse: {{ order.address.address }}, {{ order.address.postalcode }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col space-y-2">
                                            <button 
                                                type="button"
                                                @click="moveOrderUp(index)"
                                                class="px-2 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm"
                                                :disabled="index === 0"
                                                :class="{ 'opacity-50 cursor-not-allowed': index === 0 }"
                                            >
                                                ↑
                                            </button>
                                            <button 
                                                type="button"
                                                @click="moveOrderDown(index)"
                                                class="px-2 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm"
                                                :disabled="index === selectedOrders.length - 1"
                                                :class="{ 'opacity-50 cursor-not-allowed': index === selectedOrders.length - 1 }"
                                            >
                                                ↓
                                            </button>
                                            <button 
                                                type="button"
                                                @click="removeOrder(order.id_order)"
                                                class="px-2 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <InputError :message="form.errors.orders" class="mt-2" />
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton
                            type="button"
                            @click="$inertia.visit(route('delivery-routes.index'))"
                        >
                            Annuler
                        </SecondaryButton>
                        <PrimaryButton
                            :disabled="form.processing || selectedOrders.length === 0"
                            :class="{ 'opacity-50': form.processing || selectedOrders.length === 0 }"
                        >
                            Sauvegarder
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>