<script setup>
const placeholderRequests = [
  { id: 'PR-1001', customer: 'John Doe', property: 'Ocean View Villa', unit: 'Unit A1', date: 'Oct 12, 2026', status: 'Pending' },
  { id: 'PR-1002', customer: 'Alice Brown', property: 'Downtown Penthouse', unit: 'Unit B2', date: 'Oct 10, 2026', status: 'Approved' },
]
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Purchase Requests</h1>
      <p>Review and act on property purchase inquiries from customers.</p>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Customer</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="req in placeholderRequests" :key="req.id">
            <td><strong>{{ req.id }}</strong></td>
            <td>{{ req.customer }}</td>
            <td>{{ req.property }}</td>
            <td>{{ req.unit }}</td>
            <td>{{ req.date }}</td>
            <td>
              <span class="sk-badge" :class="req.status === 'Pending' ? 'sk-badge-pending' : 'sk-badge-approved'">{{ req.status }}</span>
            </td>
            <td>
              <div v-if="req.status === 'Pending'" style="display: flex; gap: 0.25rem;">
                <button class="sk-btn sk-btn-success">Approve</button>
                <button class="sk-btn sk-btn-danger">Reject</button>
              </div>
              <div v-else>
                <button class="sk-btn sk-btn-secondary" disabled>Processed</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
