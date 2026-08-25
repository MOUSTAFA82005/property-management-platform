<script setup>
const placeholderContracts = [
  { id: 'CTR-2026-001', customer: 'John Doe', property: 'Ocean View Villa', unit: 'Unit A1', date: 'Oct 15, 2026', status: 'Active' },
  { id: 'CTR-2025-089', customer: 'Alice Brown', property: 'Downtown Penthouse', unit: 'Unit B2', date: 'Dec 05, 2025', status: 'Closed' },
]
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Contracts</h1>
      <p>Manage signed agreements and property contracts.</p>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Contract ID</th>
            <th>Customer</th>
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
            <td>{{ contract.customer }}</td>
            <td>{{ contract.property }}</td>
            <td>{{ contract.unit }}</td>
            <td>{{ contract.date }}</td>
            <td>
              <span class="sk-badge" :class="contract.status === 'Active' ? 'sk-badge-active' : 'sk-badge-rejected'">{{ contract.status }}</span>
            </td>
            <td>
              <button class="sk-btn sk-btn-secondary">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
