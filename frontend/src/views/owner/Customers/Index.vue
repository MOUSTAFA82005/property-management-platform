<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../../services/api";

const router = useRouter();

const customers = ref([]);
const loading = ref(true);
const error = ref("");

const getCustomers = async () => {
  try {
    loading.value = true;
    error.value = "";

    const response = await api.get("/owner/customers");

    customers.value = response.data.data;
  } catch (err) {
    console.error(err);
    error.value = "Failed to load customers.";
  } finally {
    loading.value = false;
  }
};

const viewCustomer = (id) => {
  router.push(`/owner/customers/${id}`);
};

onMounted(() => {
  getCustomers();
});
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Customers</h1>
      <p>Manage prospective and current property buyers.</p>
    </div>

    <div v-if="loading" class="sk-card">Loading customers...</div>

    <div v-else-if="error" class="sk-card">
      {{ error }}
    </div>

    <div v-else class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Customer Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Purchase Requests</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="customer in customers" :key="customer.id">
            <td>
              <strong>{{ customer.name }}</strong>
            </td>

            <td>{{ customer.email }}</td>

            <td>{{ customer.phone || "-" }}</td>

            <td>{{ customer.purchase_requests_count }}</td>

            <td>
              <span
                class="sk-badge"
                :class="
                  customer.status === 'active'
                    ? 'sk-badge-active'
                    : 'sk-badge-rejected'
                "
              >
                {{ customer.status }}
              </span>
            </td>

            <td>
              <button
                class="sk-btn sk-btn-secondary"
                @click="viewCustomer(customer.id)"
              >
                View
              </button>
            </td>
          </tr>

          <tr v-if="customers.length === 0">
            <td colspan="6" style="text-align: center">No customers found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
