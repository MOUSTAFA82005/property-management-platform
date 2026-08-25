import { defineStore } from 'pinia'
import { ref } from 'vue'

import {
  getOwnerContracts,
  createContract,
  getOwnerContract,
  updateContract,
  deleteContract,
  getCustomerContracts,
  getCustomerContract,
} from '../services/contracts'

export const useContractsStore = defineStore('contracts', () => {
  const contracts = ref([])
  const contract = ref(null)
  const loading = ref(false)

  const fetchOwnerContracts = async () => {
    loading.value = true

    try {
      const response = await getOwnerContracts()
      contracts.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  const fetchOwnerContract = async (id) => {
    loading.value = true

    try {
      const response = await getOwnerContract(id)
      contract.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  const addContract = async (data) => {
    loading.value = true

    try {
      const response = await createContract(data)
      contracts.value.unshift(response.data.data)
      return response.data
    } finally {
      loading.value = false
    }
  }

  const editContract = async (id, data) => {
    loading.value = true

    try {
      const response = await updateContract(id, data)

      const updatedContract = response.data.data

      const index = contracts.value.findIndex(
        (item) => item.id === id
      )

      if (index !== -1) {
        contracts.value[index] = updatedContract
      }

      contract.value = updatedContract

      return response.data
    } finally {
      loading.value = false
    }
  }

  const removeContract = async (id) => {
    loading.value = true

    try {
      await deleteContract(id)

      contracts.value = contracts.value.filter(
        (item) => item.id !== id
      )

      if (contract.value?.id === id) {
        contract.value = null
      }
    } finally {
      loading.value = false
    }
  }

  const fetchCustomerContracts = async () => {
    loading.value = true

    try {
      const response = await getCustomerContracts()
      contracts.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  const fetchCustomerContract = async (id) => {
    loading.value = true

    try {
      const response = await getCustomerContract(id)
      contract.value = response.data.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  return {
    contracts,
    contract,
    loading,
    fetchOwnerContracts,
    fetchOwnerContract,
    addContract,
    editContract,
    removeContract,
    fetchCustomerContracts,
    fetchCustomerContract,
  }
})