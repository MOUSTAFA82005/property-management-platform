<script setup>
const placeholderPayments = [
  { id: 'PAY-8821', customer: 'John Doe', contract: 'CTR-2026-001', amount: 'EGP 150,000', date: 'Oct 20, 2026', status: 'Paid' },
  { id: 'PAY-8822', customer: 'John Doe', contract: 'CTR-2026-001', amount: 'EGP 50,000', date: 'Nov 20, 2026', status: 'Pending' },
  { id: 'PAY-8700', customer: 'Alice Brown', contract: 'CTR-2025-089', amount: 'EGP 10,000', date: 'Jan 05, 2026', status: 'Paid' },
]
</script>

<template>
  <div>
    <div class="sk-header">
      <h1>Payments</h1>
      <p>Track incoming installments and completed payments.</p>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Customer</th>
            <th>Contract Ref</th>
            <th>Amount</th>
            <th>Due Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pay in placeholderPayments" :key="pay.id">
            <td><strong>{{ pay.id }}</strong></td>
            <td>{{ pay.customer }}</td>
            <td>{{ pay.contract }}</td>
            <td>{{ pay.amount }}</td>
            <td>{{ pay.date }}</td>
            <td>
              <span class="sk-badge" :class="pay.status === 'Paid' ? 'sk-badge-paid' : 'sk-badge-pending'">{{ pay.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
