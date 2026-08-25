<script setup>
import { RouterLink } from 'vue-router'

const placeholderProperties = [
  { id: 1, name: 'Ocean View Villa', location: 'Alexandria', units: 5, status: 'Active' },
  { id: 2, name: 'Downtown Penthouse', location: 'Cairo', units: 1, status: 'Active' },
]
</script>

<template>
  <div>
    <div class="sk-toolbar">
      <div class="sk-header" style="border: none; padding: 0; margin: 0;">
        <h1 style="margin-bottom: 0;">Properties</h1>
      </div>
      <RouterLink to="/owner/properties/create" class="sk-btn sk-btn-primary">+ Add Property</RouterLink>
    </div>

    <!-- Table -->
    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Property Name</th>
            <th>Location</th>
            <th>Units</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="prop in placeholderProperties" :key="prop.id">
            <td><strong>{{ prop.name }}</strong></td>
            <td>{{ prop.location }}</td>
            <td>{{ prop.units }}</td>
            <td>
              <span class="sk-badge sk-badge-active">{{ prop.status }}</span>
            </td>
            <td>
              <div style="display: flex; gap: 0.25rem;">
                <RouterLink :to="`/owner/properties/${prop.id}/edit`" class="sk-btn sk-btn-secondary">Edit</RouterLink>
                <button class="sk-btn sk-btn-danger">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
