<script setup>
const placeholderCustomers = [
  { id: 1, name: 'John Doe', email: 'john@example.com', phone: '+20 10 1111 2222', requests: 2, status: 'Active' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', phone: '+20 10 3333 4444', requests: 0, status: 'Active' },
]
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Customers</h1>
      <p>Manage prospective and current property buyers.</p>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Customer Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Purchase Requests</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in placeholderCustomers" :key="customer.id">
            <td><strong>{{ customer.name }}</strong></td>
            <td>{{ customer.email }}</td>
            <td>{{ customer.phone }}</td>
            <td>{{ customer.requests }}</td>
            <td>
              <span class="sk-badge sk-badge-active">{{ customer.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
