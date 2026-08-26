<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../../services/api";

const route = useRoute();
const router = useRouter();

const contract = ref(null);
const loading = ref(true);
const error = ref("");

const getContract = async () => {
  try {
    loading.value = true;
    error.value = "";

    const response = await api.get(`/owner/contracts/${route.params.id}`);

    contract.value = response.data.data;
  } catch (err) {
    console.error(err);
    error.value = "Failed to load contract.";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  getContract();
});
</script>

<template>
  <div class="sk-page">
    <div class="sk-header">
      <div>
        <h1>Contract Details</h1>
        <p>View contract information and agreement details.</p>
      </div>

      <button
        class="sk-btn sk-btn-secondary"
        @click="router.push('/owner/contracts')"
      >
        Back to Contracts
      </button>
    </div>

    <div v-if="loading" class="sk-card">Loading contract...</div>

    <div v-else-if="error" class="sk-card">
      {{ error }}
    </div>

    <div v-else-if="contract" class="sk-card">
      <div class="sk-header">
        <div>
          <h2>Contract #{{ contract.id }}</h2>
        </div>

        <span
          class="sk-badge"
          :class="
            contract.status === 'active'
              ? 'sk-badge-active'
              : 'sk-badge-rejected'
          "
        >
          {{ contract.status }}
        </span>
      </div>

      <div class="sk-form">
        <div class="sk-form-group">
          <label class="sk-form-label">Customer</label>
          <div class="sk-form-input">
            {{ contract.customer?.name || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Email</label>
          <div class="sk-form-input">
            {{ contract.customer?.email || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Property</label>
          <div class="sk-form-input">
            {{ contract.unit?.building?.property?.name || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Building</label>
          <div class="sk-form-input">
            {{ contract.unit?.building?.name || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Unit</label>
          <div class="sk-form-input">
            {{ contract.unit?.unit_number || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Start Date</label>
          <div class="sk-form-input">
            {{ contract.start_date || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">End Date</label>
          <div class="sk-form-input">
            {{ contract.end_date || "-" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Monthly Rent</label>
          <div class="sk-form-input">
            EGP {{ contract.monthly_rent || "0" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Security Deposit</label>
          <div class="sk-form-input">
            EGP {{ contract.security_deposit || "0" }}
          </div>
        </div>

        <div class="sk-form-group">
          <label class="sk-form-label">Notes</label>
          <div class="sk-form-input">
            {{ contract.notes || "-" }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
