<script setup>
import { RouterLink } from 'vue-router'

const placeholderProperties = [
  { id: 1, name: 'Ocean View Villa', location: 'Alexandria', type: 'Villa', price: 'EGP 6,000,000' },
  { id: 2, name: 'Downtown Penthouse', location: 'Cairo', type: 'Apartment', price: 'EGP 4,200,000' },
  { id: 3, name: 'Garden Townhouse', location: 'New Cairo', type: 'Townhouse', price: 'EGP 5,500,000' },
]
</script>

<template>
  <div class="sk-page">
    <div class="sk-header">
      <h1>Properties</h1>
      <p>Browse our available properties and find your next home.</p>
    </div>

    <!-- Toolbar -->
    <div class="sk-toolbar">
      <input type="text" class="sk-search" placeholder="Search properties..." />
      <div class="sk-form-group" style="margin: 0;">
        <select class="sk-form-select" style="width: 200px;">
          <option value="">All Types</option>
          <option value="apartment">Apartment</option>
          <option value="villa">Villa</option>
        </select>
      </div>
    </div>

    <!-- Cards -->
    <div class="sk-cards">
      <div v-for="prop in placeholderProperties" :key="prop.id" class="sk-card">
        <div class="sk-card-img">🏢</div>
        <div class="sk-card-title">{{ prop.name }}</div>
        <div class="sk-card-meta">{{ prop.location }} &bull; {{ prop.type }}</div>
        <div class="sk-card-price">{{ prop.price }}</div>
        <RouterLink :to="`/properties/${prop.id}`" class="sk-btn sk-btn-primary" style="width: 100%; justify-content: center;">View Details</RouterLink>
      </div>
    </div>
  </div>
</template>
