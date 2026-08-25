<script setup>
const placeholderContracts = [
  { id: 'CTR-2026-001', property: 'Downtown Penthouse', unit: 'Unit B2', date: 'Oct 15, 2026', status: 'Active' },
  { id: 'CTR-2025-089', property: 'Nile View Apartment', unit: 'Floor 12, Apt 4', date: 'Dec 05, 2025', status: 'Closed' },
]
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'
</script>

<template>
  <CustomerDashboardLayout>
    <div class="sk-header">
      <h1>My Contracts</h1>
      <p>View and manage your signed property contracts.</p>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Contract ID</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Date Signed</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contract in placeholderContracts" :key="contract.id">
            <td><strong>{{ contract.id }}</strong></td>
            <td>{{ contract.property }}</td>
            <td>{{ contract.unit }}</td>
            <td>{{ contract.date }}</td>
            <td>
              <span class="sk-badge" :class="contract.status === 'Active' ? 'sk-badge-active' : 'sk-badge-rejected'">{{ contract.status }}</span>
            </td>
            <td>
              <button class="sk-btn sk-btn-secondary">View Contract</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </CustomerDashboardLayout>
</template>
