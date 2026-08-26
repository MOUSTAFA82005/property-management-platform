<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../../services/api";

const route = useRoute();
const router = useRouter();

const customer = ref(null);
const loading = ref(true);
const error = ref("");

const getCustomer = async () => {
  try {
    loading.value = true;
    error.value = "";

    const response = await api.get(`/owner/customers/${route.params.id}`);

    customer.value = response.data.data;
  } catch (err) {
    console.error(err);
    error.value = "Failed to load customer.";
  } finally {
    loading.value = false;
  }
};

const backToCustomers = () => {
  router.push("/owner/customers");
};

onMounted(() => {
  getCustomer();
});
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Customer Details</h1>
      <p>View customer information and related contracts.</p>

      <button class="sk-btn sk-btn-secondary" @click="backToCustomers">
        Back to Customers
      </button>
    </div>

    <div v-if="loading" class="sk-card">Loading customer...</div>

    <div v-else-if="error" class="sk-card">
      {{ error }}
    </div>

    <div v-else-if="customer" class="sk-card">
      <h2>{{ customer.name }}</h2>

      <div class="sk-form-grid">
        <div>
          <label>Name</label>
          <input :value="customer.name" disabled />
        </div>

        <div>
          <label>Email</label>
          <input :value="customer.email" disabled />
        </div>

        <div>
          <label>Phone</label>
          <input :value="customer.phone || '-'" disabled />
        </div>

        <div>
          <label>Status</label>
          <input :value="customer.status" disabled />
        </div>
      </div>

      <hr />

      <h3>Contracts</h3>

      <div v-if="customer.contracts?.length">
        <div
          v-for="contract in customer.contracts"
          :key="contract.id"
          class="sk-card"
        >
          <h3>Contract #{{ contract.id }}</h3>

          <p>
            <strong>Property:</strong>
            {{ contract.unit?.building?.property?.name || "-" }}
          </p>

          <p>
            <strong>Building:</strong>
            {{ contract.unit?.building?.name || "-" }}
          </p>

          <p>
            <strong>Unit:</strong>
            {{ contract.unit?.unit_number || "-" }}
          </p>

          <p>
            <strong>Start Date:</strong>
            {{ contract.start_date }}
          </p>

          <p>
            <strong>End Date:</strong>
            {{ contract.end_date }}
          </p>

          <p>
            <strong>Monthly Rent:</strong>
            EGP {{ contract.monthly_rent }}
          </p>

          <p>
            <strong>Security Deposit:</strong>
            EGP {{ contract.security_deposit }}
          </p>

          <p>
            <strong>Status:</strong>
            {{ contract.status }}
          </p>

          <p>
            <strong>Notes:</strong>
            {{ contract.notes || "-" }}
          </p>
        </div>
      </div>

      <p v-else>No contracts found.</p>
    </div>
  </div>
</template>
