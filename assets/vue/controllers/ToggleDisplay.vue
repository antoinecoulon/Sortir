<template>
  <div class="inline-flex rounded-md shadow-xs" role="group">
    <button @click="display = 'array'" id="toggleArray" type="button"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border border-gray-900 rounded-s-lg hover:bg-blue-300 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700">
      <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
           width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-width="2"
              d="M3 11h18m-9 0v8m-8 0h16a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z"/>
      </svg>
      Tableau
    </button>
    <button @click="display = 'card'" id="toggleCard" type="button"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-transparent border border-gray-900 rounded-e-lg hover:bg-blue-300 hover:text-white focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700">
      <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
           width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
        <path
            d="M5 3a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5Zm14 18a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4ZM5 11a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5Zm14 2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4Z"/>
      </svg>
      Carte
    </button>
  </div>
</template>
<script setup>
import {watch, ref, onMounted} from 'vue'

let display = ref('card')
const arrayEL = document.getElementById('array')
const cardEl = document.getElementById('card')

onMounted(() => {
  let storageDisplay = localStorage.getItem('display')
  if (storageDisplay) {
    toggle(storageDisplay)
  } else {
    toggle(display)
  }
})

watch(display, val => {
  toggle(val)
})

function toggle(val) {
  if (val === 'card') {
    cardEl.removeAttribute('hidden')
    arrayEL.setAttribute('hidden', true)
    localStorage.setItem('display', 'card')
  } else {
    arrayEL.removeAttribute('hidden')
    cardEl.setAttribute('hidden', true)
    localStorage.setItem('display', 'array')
  }
}


</script>


<style scoped>

</style>