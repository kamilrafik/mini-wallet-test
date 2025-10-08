<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'
import axios from 'axios'
import {TransactionDetailsDto} from "@/Dtos/TransactionDetailsDto.js";
import {Formatter} from "@/Utils/Formatter.js";
import {TransactionDetailsPresentedDto} from "@/Dtos/TransactionDetailsPresentedDto.js";

const api = axios.create({
  baseURL: '/',
})
api.interceptors.response.use(
    (response) => {
      if (!response.data.success) {
        console.error('Business logic error:', error)
        return Promise.reject(response)
      }
      return response
    },
    (error) => {
      console.error('API error:', error)
      return Promise.reject(error)
    }
)

const balance = ref(null)
const transactions = ref([])
const form = ref({ email: null, amount: null })
const success = ref(null)
const error = ref(null)
const defaultErrorValue = 'Unknown internal error.'
const user = usePage().props.auth.user;

async function getBalance() {
  try {
    const { data } = await api.get('/balance')
    console.log('Balance:', data);
    balance.value = data.balance;
  } catch (e) {
    console.error('Failed to get balance.', e)
  }
}

async function getTransactions() {
  try {
    const { data } = await api.get('/transactions')
    console.log('Transactions:', data)
    transactions.value = data.transactions.map(t => new TransactionDetailsDto(t))
  } catch (e) {
    console.error('Failed to get transactions.', e)
  }
}

async function postTransaction() {
  error.value = null
  success.value = null
  try {
    // here we parse errors by ourselves
    const { data } = await axios.post('/transactions', form.value)
    if (data.success) {
      form.value = { email: null, amount: null }
      success.value = 'Payment has been successfully sent for processing.'
    } else {
      console.error('Business logic error:', data)
      error.value = data.result.primaryError ?? defaultErrorValue
    }
  } catch (e) {
    console.error('API error:', e)
    error.value = defaultErrorValue
  }
}

function listenChannels() {
  window.Echo.private(`users.${user.uuid}`)
      .listen('.transaction.created', (e) => {
        console.log('Event:', e)
        transactions.value.unshift(new TransactionDetailsDto(e))
      })
      .listen('.balance.updated', (e) => {
        console.log('Event:', e)
        balance.value = e.balanceAmount
      })
}

onMounted(async () => {
  await Promise.all([getBalance(), getTransactions()])
  listenChannels()
})

</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2
          class="text-xl font-semibold leading-tight text-gray-800"
      >
        Dashboard
      </h2>
    </template>

    <div class="grid grid-cols-2 gap-6 p-6">

      <div class="space-y-6">

        <div v-if="balance !== null" class="text-xl font-semibold">
          Balance: {{ Formatter.asCurrency(balance) }}
        </div>

        <form @submit.prevent="postTransaction" class="space-y-3">
          <p v-if="error !== null" class="text-red-600 text-sm mt-2">{{ error }}</p>
          <p v-if="success !== null" class="text-green-600 text-sm mt-2">{{ success }}</p>
          <div>
            <label class="block text-sm font-medium">Email</label>
            <input v-model="form.email" type="email" required class="border rounded w-full p-2" />
          </div>
          <div>
            <label class="block text-sm font-medium">Amount</label>
            <input v-model.number="form.amount" type="number" required class="border rounded w-full p-2" />
          </div>
          <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
          >
            Send payment
          </button>
        </form>
      </div>

      <div>
        <h3 class="text-xl font-semibold mb-3">Transactions</h3>
        <div v-if="!transactions.length">
          Not found.
        </div>
        <ul class="space-y-2" v-if="transactions.length">
          <li
              v-for="transaction in transactions.map(t => new TransactionDetailsPresentedDto(t))"
              :key="transaction.uuid"
              class="flex justify-between border-b pb-1"
          >
            <span :style="'color:' + transaction.color">
              {{ transaction.amount }}
            </span>
            <span class="text-gray-600">
              {{ transaction.counterpart }}
            </span>
          </li>
        </ul>
      </div>
    </div>

  </AuthenticatedLayout>
</template>
