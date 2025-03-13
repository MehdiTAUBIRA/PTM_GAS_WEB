<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    product: Object,
    movements: Array,
    maintenances: Array,
    movementTypes: Object,
    statuses: Object,
    maintenanceTypes: Object,
    maintenanceStatuses: Object
});

const activeTab = ref('movements');

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStateLabel = (state) => {
    const states = {
        'N': 'Neuf',
        'U': 'Utilisé',
        'D': 'Endommagé',
        'R': 'Réparé',
        'O': 'Hors service'
    };
    return states[state] || state;
};

const getStateClass = (state) => {
    const classes = {
        'N': 'bg-green-100 text-green-800',
        'U': 'bg-blue-100 text-blue-800',
        'D': 'bg-red-100 text-red-800',
        'R': 'bg-yellow-100 text-yellow-800',
        'O': 'bg-gray-100 text-gray-800'
    };
    return classes[state] || 'bg-gray-100 text-gray-800';
};

const getMovementTypeBackgroundClass = (type) => {
    const classes = {
        'delivery': 'bg-green-500',
        'return': 'bg-blue-500',
        'transfer': 'bg-yellow-500',
        'maintenance': 'bg-purple-500'
    };
    return classes[type] || 'bg-gray-500';
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getMaintenanceTypeBackgroundClass = (type) => {
    const classes = {
        'inspection': 'bg-blue-500',
        'test': 'bg-indigo-500',
        'repair': 'bg-orange-500',
        'certification': 'bg-green-500'
    };
    return classes[type] || 'bg-gray-500';
};

const getResultClass = (result) => {
    const classes = {
        'passed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'needs_repair': 'bg-yellow-100 text-yellow-800'
    };
    return classes[result] || 'bg-gray-100 text-gray-800';
};

const getMaintenanceStatusClass = (status) => {
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
    <Head title="Historique du Produit" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Historique du Produit
                </h2>
                <Link
                    :href="route('product-tracking.index')"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
                >
                    Retour à la liste
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Infos produit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations du produit</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-3">
                                <span class="font-semibold">Produit:</span>
                                <span class="ml-2">{{ product.product?.prolibcommercial || 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="font-semibold">Code barre:</span>
                                <span class="ml-2">{{ product.barcode }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="font-semibold">N° série:</span>
                                <span class="ml-2">{{ product.serial_number }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="font-semibold">Propriété:</span>
                                <span class="ml-2">{{ product.ownership || 'N/A' }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="mb-3">
                                <span class="font-semibold">Date de fabrication:</span>
                                <span class="ml-2">{{ formatDate(product.manufacture_date) }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="font-semibold">Date d'expiration:</span>
                                <span class="ml-2">{{ product.expiration_date ? formatDate(product.expiration_date) : 'N/A' }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="font-semibold">Type de valve:</span>
                                <span class="ml-2">{{ product.valve_type }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="font-semibold">État:</span>
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStateClass(product.state)">
                                    {{ getStateLabel(product.state) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Onglets -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button 
                                @click="activeTab = 'movements'" 
                                class="px-6 py-4 text-center border-b-2 font-medium text-sm"
                                :class="activeTab === 'movements' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Historique des mouvements
                            </button>
                            <button 
                                @click="activeTab = 'maintenance'" 
                                class="px-6 py-4 text-center border-b-2 font-medium text-sm"
                                :class="activeTab === 'maintenance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Historique des maintenances
                            </button>
                        </nav>
                    </div>
                </div>
                
                <!-- Timeline des mouvements -->
                <div v-if="activeTab === 'movements'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li v-for="(movement, index) in movements" :key="movement.id_movement">
                                    <div class="relative pb-8">
                                        <span v-if="index !== movements.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="getMovementTypeBackgroundClass(movement.movement_type)">
                                                    <!-- Icône qui dépend du type de mouvement -->
                                                    <svg v-if="movement.movement_type === 'delivery'" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h4a1 1 0 011 1v6h-2.05a2.5 2.5 0 01-4.9 0H14a1 1 0 01-1-1V8a1 1 0 011-1z" />
                                                    </svg>
                                                    <svg v-else-if="movement.movement_type === 'return'" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    <svg v-else-if="movement.movement_type === 'transfer'" class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z" />
                                                    </svg>
                                                    <svg v-else class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        <span class="font-medium text-gray-900">{{ movementTypes[movement.movement_type] || movement.movement_type }}</span>
                                                        <span v-if="movement.source_details">
                                                            de {{ movement.source_details.name }}
                                                        </span>
                                                        <span v-if="movement.destination_details">
                                                            vers {{ movement.destination_details.name }}
                                                        </span>
                                                    </p>
                                                    <p v-if="movement.comments" class="mt-1 text-sm text-gray-500">
                                                        Commentaire: {{ movement.comments }}
                                                    </p>
                                                    <p v-if="movement.order" class="mt-1 text-sm text-gray-500">
                                                        Commande: {{ movement.order.order_number }}
                                                    </p>
                                                    <p v-if="movement.route" class="mt-1 text-sm text-gray-500">
                                                        Route: {{ movement.route.route_date }} 
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time>{{ formatDate(movement.movement_date) }}</time>
                                                    <p class="mt-1">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(movement.status)">
                                                            {{ statuses[movement.status] || movement.status }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li v-if="movements.length === 0">
                                    <div class="p-4 text-center text-gray-500">
                                        Aucun mouvement trouvé pour ce produit
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline des maintenances -->
                <div v-if="activeTab === 'maintenance'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li v-for="(maintenance, index) in maintenances" :key="maintenance.id_maintenance">
                                    <div class="relative pb-8">
                                        <span v-if="index !== maintenances.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="getMaintenanceTypeBackgroundClass(maintenance.maintenance_type)">
                                                    <!-- Icône pour la maintenance -->
                                                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        <span class="font-medium text-gray-900">{{ maintenanceTypes[maintenance.maintenance_type] || maintenance.maintenance_type }}</span>
                                                        <span v-if="maintenance.performed_by" class="ml-1">effectué par {{ maintenance.performed_by }}</span>
                                                    </p>
                                                    <p v-if="maintenance.maintenance_result" class="mt-1 text-sm text-gray-500">
                                                        Résultat: 
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getResultClass(maintenance.maintenance_result)">
                                                            {{ maintenance.maintenance_result === 'passed' ? 'Passé' : 
                                                               maintenance.maintenance_result === 'failed' ? 'Échoué' : 
                                                               maintenance.maintenance_result === 'needs_repair' ? 'Nécessite réparation' : 
                                                               maintenance.maintenance_result }}
                                                        </span>
                                                    </p>
                                                    <p v-if="maintenance.certificate_number" class="mt-1 text-sm text-gray-500">
                                                        N° de Certificat: {{ maintenance.certificate_number }}
                                                    </p>
                                                    <p v-if="maintenance.maintenance_cost" class="mt-1 text-sm text-gray-500">
                                                        Coût: {{ maintenance.maintenance_cost }} €
                                                    </p>
                                                    <p v-if="maintenance.next_maintenance_date" class="mt-1 text-sm text-gray-500">
                                                        Prochaine maintenance: {{ formatDate(maintenance.next_maintenance_date) }}
                                                    </p>
                                                    <p v-if="maintenance.comments" class="mt-1 text-sm text-gray-500">
                                                        Commentaire: {{ maintenance.comments }}
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <div>
                                                        <span class="text-gray-900">Planifiée:</span>
                                                        <time>{{ formatDate(maintenance.planned_date) }}</time>
                                                    </div>
                                                    <div v-if="maintenance.actual_date" class="mt-1">
                                                        <span class="text-gray-900">Réalisée:</span>
                                                        <time>{{ formatDate(maintenance.actual_date) }}</time>
                                                    </div>
                                                    <p class="mt-1">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getMaintenanceStatusClass(maintenance.status)">
                                                            {{ maintenanceStatuses[maintenance.status] || maintenance.status }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li v-if="maintenances.length === 0">
                                    <div class="p-4 text-center text-gray-500">
                                        Aucune maintenance trouvée pour ce produit
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>