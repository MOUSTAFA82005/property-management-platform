<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div>
    <RouterLink to="/owner/payments" class="sk-back">&larr; Back to Payments</RouterLink>

    <div class="sk-header">
      <h1>Add New Payment</h1>
      <p>Create a new payment record.</p>
    </div>

    <div style="padding: 2rem 1rem; text-align: center; color: #6b7280;">
      <p style="margin-bottom: 1rem;">Use the "+ Add Payment" button on the <RouterLink to="/owner/payments" style="color: #864CFF; font-weight: 600;">Payments list</RouterLink> to create a new payment.</p>
      <RouterLink to="/owner/payments" class="sk-btn sk-btn-primary">Go to Payments</RouterLink>
    </div>
  </div>
</template>
