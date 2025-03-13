<template>
  <aside class="z-20 hidden w-64 overflow-y-auto bg-blue-700 md:block flex-shrink-0">
    <div class="py-4 text-gray-100">
      <Link class="ml-6 text-lg font-bold text-gray-800" :href="route('dashboard')">
        PTM
      </Link>

      <ul class="mt-6">
        <li class="relative px-6 py-3">
          <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <template #icon>
              <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                   stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
            </template>
            Dashboard
          </NavLink>
        </li>

        <li class="relative px-6 py-3">
          <NavLink :href="route('users.index')" :active="route().current('users.index')">
            <template #icon>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                   xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </template>
            Users
          </NavLink>
        </li>

        <li class="relative px-6 py-3">
          <button 
            @click="toggleMenu('depot')"
            class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
            aria-haspopup="true"
          >
            <span class="inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                </svg>
                <span class="ml-4">Dépôts</span>
            </span>
            <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                <path 
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                ></path>
            </svg>
          </button>
          <ul 
            v-show="isMenuOpen('depot')" 
            class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
            aria-label="submenu"
          >
            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('depots.index')" :active="route().current('depots.index')" class="w-full">
                    Liste des dépôts
                </NavLink>
            </li>
            <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('depots.create')" :active="route().current('depots.create')" class="w-full">
                    Créer un dépôt
                </NavLink>
            </li>
        
          </ul>
        </li>

        <li class="relative px-6 py-3">
            <button 
                @click="toggleMenu('employee')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true"
            >
                <span class="inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span class="ml-4">Employés</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path 
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </button>
            <ul 
                v-show="isMenuOpen('employee')" 
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu"
            >
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('employees.index')" :active="route().current('employees.index')" class="w-full">
                        Liste des employés
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('employees.create')" :active="route().current('employees.create')" class="w-full">
                        Créer un employé
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button 
                @click="toggleMenu('driver')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true"
            >
                <span class="inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span class="ml-4">Chauffeurs</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path 
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </button>
            <ul 
                v-show="isMenuOpen('driver')" 
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu"
            >
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('drivers.index')" :active="route().current('drivers.index')" class="w-full">
                        Liste des chauffeurs
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('drivers.create')" :active="route().current('drivers.create')" class="w-full">
                        Créer un chauffeur
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button @click="toggleMenu('vehicle')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"></path>
                    </svg>
                    <span class="ml-4">Véhicules</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
            <ul v-show="isMenuOpen('vehicle')" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu">
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('vehicles.index')" :active="route().current('vehicles.index')" class="w-full">
                        Liste des véhicules
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('vehicles.create')" :active="route().current('vehicles.create')" class="w-full">
                        Créer un véhicule
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button @click="toggleMenu('customer')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="ml-4">Clients</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
            <ul v-show="isMenuOpen('customer')" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu">
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('customers.index')" :active="route().current('customers.index')" class="w-full">
                        Liste des clients
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('customers.create')" :active="route().current('customers.create')" class="w-full">
                        Créer un client
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button @click="toggleMenu('product')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"></path>
                    </svg>
                    <span class="ml-4">Produits</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
            <ul v-show="isMenuOpen('product')" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu">
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('products.index')" :active="route().current('products.index')" class="w-full">
                        Liste des produits
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('products.create')" :active="route().current('products.create')" class="w-full">
                        Créer un produit
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('product-tracking.index')" :active="route().current('product-tracking.*')" class="w-full">
                        Suivi des produits
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('products.report')" :active="route().current('products.report')" class="w-full">
                        Rapport des produits
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button @click="toggleMenu('order')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span class="ml-4">Commandes</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
            <ul v-show="isMenuOpen('order')" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu">
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('orders.index')" :active="route().current('orders.index')" class="w-full">
                        Liste des commandes
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('orders.create')" :active="route().current('orders.create')" class="w-full">
                        Nouvelle commande
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('orders.report')" :active="route().current('orders.report')" class="w-full">
                        Rapport des commandes
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button @click="toggleMenu('deposit')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="ml-4">Dépôts de garantie</span>
                </span>
                <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
            <ul v-show="isMenuOpen('deposit')" class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu">
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('deposits.index')" :active="route().current('deposits.index')" class="w-full">
                        Liste des dépôts
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('deposits.create')" :active="route().current('deposits.create')" class="w-full">
                        Nouveau dépôt
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('deposits.report')" :active="route().current('deposits.report')" class="w-full">
                        Rapport des dépôts
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button 
                @click="toggleMenu('document')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true"
            >
                <span class="inline-flex items-center">
                    <svg 
                        class="w-5 h-5" 
                        aria-hidden="true" 
                        fill="none" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="ml-4">Documents</span>
                </span>
                <svg 
                    class="w-4 h-4" 
                    aria-hidden="true" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path 
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </button>
            <ul 
                v-show="isMenuOpen('document')" 
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu"
            >
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('documents.index')" :active="route().current('documents.index')" class="w-full">
                        Liste des documents
                    </NavLink>
                </li>

                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('documents.report')" :active="route().current('documents.report')" class="w-full">
                        Rapports des documents
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button 
                @click="toggleMenu('inventory')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true"
            >
                <span class="inline-flex items-center">
                    <svg 
                        class="w-5 h-5" 
                        aria-hidden="true" 
                        fill="none" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span class="ml-4">Inventaires</span>
                </span>
                <svg 
                    class="w-4 h-4" 
                    aria-hidden="true" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                >
                    <path 
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </button>
            <ul 
                v-show="isMenuOpen('inventory')" 
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu"
            >
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('inventory-orders.index')" :active="route().current('inventory-orders.index')" class="w-full">
                        Liste des ordres d'inventaire
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('inventory-orders.create')" :active="route().current('inventory-orders.create')" class="w-full">
                        Créer un ordre d'inventaire
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('inventory.index')" :active="route().current('inventory.index')" class="w-full">
                        État de l'inventaire
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('inventory.report')" :active="route().current('inventory.report')" class="w-full">
                        Rapports d'inventaire
                    </NavLink>
                </li>
            </ul>
        </li>

        <li class="relative px-6 py-3">
            <button 
                @click="toggleMenu('stock')"
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                aria-haspopup="true"
            >
                <span class="inline-flex items-center">
                <svg 
                    class="w-5 h-5" 
                    aria-hidden="true" 
                    fill="none" 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="ml-4">Stocks</span>
                </span>
                <svg 
                class="w-4 h-4" 
                aria-hidden="true" 
                fill="currentColor" 
                viewBox="0 0 20 20"
                >
                <path 
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                ></path>
                </svg>
            </button>
            <ul 
                v-show="isMenuOpen('stock')" 
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu"
            >
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('stocks.overview')" :active="route().current('stocks.overview')" class="w-full">
                    Vue générale
                </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('stocks.by-depot')" :active="route().current('stocks.by-depot')" class="w-full">
                    Stocks par dépôt
                </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('stocks.alerts')" :active="route().current('stocks.alerts')" class="w-full">
                    Gestion des alertes
                </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('stocks.damaged')" :active="route().current('stocks.damaged')" class="w-full">
                    Produits endommagés
                </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                <NavLink :href="route('stocks.forecast')" :active="route().current('stocks.forecast')" class="w-full">
                    Prévisions
                </NavLink>
                </li>
            </ul>
            </li>

            <li class="relative px-6 py-3">
                <button 
                    @click="toggleMenu('deliveryRoutes')"
                    class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800"
                    aria-haspopup="true"
                >
                    <span class="inline-flex items-center">
                        <svg 
                            class="w-5 h-5" 
                            aria-hidden="true" 
                            fill="none" 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                        <span class="ml-4">Tournées</span>
                    </span>
                        <svg 
                class="w-4 h-4" 
                aria-hidden="true" 
                fill="currentColor" 
                viewBox="0 0 20 20"
            >
                <path 
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                ></path>
            </svg>
            </button>
            <ul 
                v-show="isMenuOpen('deliveryRoutes')" 
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50"
                aria-label="submenu"
            >
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('delivery-routes.index')" :active="route().current('delivery-routes.index')" class="w-full">
                        Liste des tournées
                    </NavLink>
                </li>
                <li class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('delivery-routes.create')" :active="route().current('delivery-routes.create')" class="w-full">
                        Nouvelle tournée
                    </NavLink>
                </li>
                <li v-if="$page.props.auth.user.role === 'driver'" class="px-2 py-1 transition-colors duration-150 hover:text-gray-800">
                    <NavLink :href="route('driver-routes', $page.props.auth.user.id_driver)" :active="route().current('driver-routes')" class="w-full">
                        Mes tournées
                    </NavLink>
                </li>
            </ul>
        </li>

      </ul>
    </div>
  </aside>
</template>

<script>
import NavLink from '@/Components/NavLink.vue'
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue'

export default {
  components: {
    NavLink,
    Link,
  },

  setup() {
    // Variable unique pour suivre le menu actuellement ouvert
    const activeMenu = ref(null);
    
    // Fonction pour basculer un menu
    const toggleMenu = (menuName) => {
      if (activeMenu.value === menuName) {
        // Si c'est déjà le menu actif, on le ferme
        activeMenu.value = null;
      } else {
        // Sinon, on ferme le précédent et on ouvre celui-ci
        activeMenu.value = menuName;
      }
    };
    
    // Fonction pour vérifier si un menu est ouvert
    const isMenuOpen = (menuName) => {
      return activeMenu.value === menuName;
    };
    
    return {
      activeMenu,
      toggleMenu,
      isMenuOpen,
    }
  },
}
</script>
