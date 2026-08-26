<script setup>
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()
const id = route.params.id
</script>

<template>
  <div>
    <RouterLink to="/owner/properties" class="sk-back">&larr; Back to Properties</RouterLink>
    
    <div class="sk-header">
      <h1>Edit Property #{{ id }}</h1>
      <p>Update an existing property listing.</p>
    </div>

    <div class="sk-form">
      <div class="sk-form-group">
        <label class="sk-form-label">Property Name</label>
        <input type="text" class="sk-form-input" value="Ocean View Villa" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Location</label>
        <input type="text" class="sk-form-input" value="Alexandria" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Property Type</label>
        <select class="sk-form-select">
          <option value="apartment">Apartment</option>
          <option value="villa" selected>Villa</option>
          <option value="commercial">Commercial</option>
        </select>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Description</label>
        <textarea class="sk-form-textarea">A luxury villa located by the sea.</textarea>
      </div>

      <div class="sk-form-actions">
        <button class="sk-btn sk-btn-primary">Update Property</button>
        <RouterLink to="/owner/properties" class="sk-btn sk-btn-secondary">Cancel</RouterLink>
      </div>
    </div>
  </div>
</template>
