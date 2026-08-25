<script setup>
const placeholderRequests = [
  { id: 'PR-1001', property: 'Ocean View Villa', unit: 'Unit A1', date: 'Oct 12, 2026', status: 'Pending' },
  { id: 'PR-1002', property: 'Downtown Penthouse', unit: 'Unit B2', date: 'Oct 10, 2026', status: 'Approved' },
  { id: 'PR-1003', property: 'Garden Townhouse', unit: 'Unit C3', date: 'Sep 25, 2026', status: 'Rejected' },
]

const getBadgeClass = (status) => {
  if (status === 'Pending') return 'sk-badge-pending'
  if (status === 'Approved') return 'sk-badge-approved'
  return 'sk-badge-rejected'
}
import CustomerDashboardLayout from '../../../components/customer/CustomerDashboardLayout.vue'
</script>

<template>
  <CustomerDashboardLayout>
    <div class="sk-header">
      <h1>My Purchase Requests</h1>
      <p>Track the status of your property purchase inquiries.</p>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="req in placeholderRequests" :key="req.id">
            <td><strong>{{ req.id }}</strong></td>
            <td>{{ req.property }}</td>
            <td>{{ req.unit }}</td>
            <td>{{ req.date }}</td>
            <td>
              <span class="sk-badge" :class="getBadgeClass(req.status)">{{ req.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </CustomerDashboardLayout>
</template>
