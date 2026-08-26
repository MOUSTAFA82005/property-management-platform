<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div>
    <RouterLink to="/owner/properties" class="sk-back">&larr; Back to Properties</RouterLink>
    
    <div class="sk-header">
      <h1>Add New Property</h1>
      <p>Create a new property listing.</p>
    </div>

    <div class="sk-form">
      <div class="sk-form-group">
        <label class="sk-form-label">Property Name</label>
        <input type="text" class="sk-form-input" placeholder="e.g. Nile View Tower" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Location</label>
        <input type="text" class="sk-form-input" placeholder="e.g. Cairo" />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Property Type</label>
        <select class="sk-form-select">
          <option value="apartment">Apartment</option>
          <option value="villa">Villa</option>
          <option value="commercial">Commercial</option>
        </select>
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Description</label>
        <textarea class="sk-form-textarea" placeholder="Detailed property description..."></textarea>
      </div>

      <div class="sk-form-actions">
        <button class="sk-btn sk-btn-primary">Save Property</button>
        <RouterLink to="/owner/properties" class="sk-btn sk-btn-secondary">Cancel</RouterLink>
      </div>
    </div>
  </div>
</template>
