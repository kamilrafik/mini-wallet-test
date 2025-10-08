<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue'
import axios from 'axios'
import {TransactionDetailsDto} from "@/Dtos/TransactionDetailsDto.js";

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

async function getBalance() {
  try {
    const { data } = await api.get('/balance')
    balance.value = data.balance;
  } catch (e) {
    console.error('Failed to get balance.', e)
  }
}

async function getTransactions() {
  try {
    const { data } = await api.get('/transactions')
    transactions.value = data.transactions.map(transaction => new TransactionDetailsDto(transaction))
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
  const user = usePage().props.auth.user;
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
