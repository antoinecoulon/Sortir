<template>
  <div id="location-modal" tabindex="-1" aria-hidden="true"
       class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <!-- Modal content -->
      <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
        <!-- Modal header -->
        <div
            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Créer un lieu de rendez-vous
          </h3>
          <button @click="closeModal()" type="button"
                  class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Close modal</span>
          </button>
        </div>
        <!-- Modal body -->
        <form @submit.prevent="sendForm" ref="addressForm" class="p-4 md:p-5">
          <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
              <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Saissez une
                adresse</label>
              <input  v-model="search" type="text" id="address"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                     placeholder="Rechercher une adresse" required>
            </div>
            <div v-if="addresses.length" tabindex="1"
                 class="absolute mt-20 bg-gray-300 dark:text-white dark:bg-gray-600  text-black rounded-lg">
              <ul class="flex flex-col p-3 gap-2 ">
                <li class="cursor-pointer" @click="selectAddress(address)" v-for="address in addresses">
                  {{ address.properties?.label }}
                </li>
              </ul>
            </div>
            <div id="data-address"></div>
            <div class="col-span-2">
              <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nom du lieu</label>
              <input v-model="name" type="text" name="name" id="location-name-input"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                     placeholder="Pizza Del Arte" required>
            </div>
          </div>
          <div class="flex flex-row justify-center">
            <button type="submit" id="createAddress"
                    class="text-white inline-flex items-center bg-gradient-to-br from-purple-600 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
              <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                   xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                      clip-rule="evenodd"></path>
              </svg>
              Ajouter le lieu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</template>

<script setup>
import {ref, watch, onMounted, onBeforeMount, nextTick} from 'vue'
import axios from 'axios'
import {Modal} from "flowbite";

const search = ref('')
const name = ref('')
let flowbiteModal
let addresses = ref([])
let address = ref(null)
const addressList = ref(null)
const isSelectingAddress = ref(false)
const addressForm = ref(false)
const selectLocation = document.getElementById("event_location")

watch(search, val => {
  if (isSelectingAddress.value) {
    return
  }
  if (val && val.length > 3) {
    getAddress()
  } else {
    addresses.value = []
  }
})

onMounted(() => {
  document.addEventListener('open-location-modal', openModal);
})

onBeforeMount(() => {
  document.removeEventListener('open-location-modal', openModal);
})

function openModal() {
  const modal = document.getElementById('location-modal');
  flowbiteModal = new Modal(modal);
  flowbiteModal.show();
}

function closeModal() {
  flowbiteModal.hide()
}

async function getAddress() {
  const encodedQuery = encodeURIComponent(search.value)
  const url = `https://api-adresse.data.gouv.fr/search/?q=${encodedQuery}`
  try {
    const {data} = await axios.get(url)
    addresses.value = data.features
  } catch (e) {
    console.error(e)
  }
}

async function selectAddress(selectedAddress) {
  address.value = selectedAddress
  isSelectingAddress.value = true
  search.value = selectedAddress.properties.label
  addresses.value = []
  await nextTick(() => {
    isSelectingAddress.value = false;
  });
}

async function sendForm() {
  if (address.value && name.value && addressForm.value.checkValidity()) {
    const location = {
      name: name.value,
      street: address.value.properties.street,
      streetNumber: address.value.properties.housenumber ?? null,
      city: address.value.properties.city,
      cp: address.value.properties.postcode,
      longitude: address.value.geometry.coordinates[0],
      latitude: address.value.geometry.coordinates[1]
    }

    try {
      const {data} = await axios.post('/location/create', location)
      let newLocationOption = document.createElement('option');
      newLocationOption.value = data?.locationId;
      newLocationOption.textContent = data?.locationName;
      selectLocation.appendChild(newLocationOption)
      selectLocation.value = newLocationOption.value
      flowbiteModal.hide()
    } catch (error) {
      console.error(error)
    }
  }
}

</script>

<style scoped>

</style>