<script setup>
const placeholderUnits = [
  { id: 101, unit: 'Unit A1', property: 'Ocean View Villa', price: 'EGP 2,500,000', area: '150 m2', status: 'Available' },
  { id: 102, unit: 'Unit B2', property: 'Downtown Penthouse', price: 'EGP 4,200,000', area: '200 m2', status: 'Sold' },
]
</script>

<template>
  <div>
    <div class="sk-toolbar">
      <div class="sk-header" style="border: none; padding: 0; margin: 0;">
        <h1 style="margin-bottom: 0;">Units</h1>
      </div>
      <button class="sk-btn sk-btn-primary">+ Add Unit (Demo)</button>
    </div>

    <div class="sk-table-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th>Unit Name/Number</th>
            <th>Property</th>
            <th>Price</th>
            <th>Area</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="unit in placeholderUnits" :key="unit.id">
            <td><strong>{{ unit.unit }}</strong></td>
            <td>{{ unit.property }}</td>
            <td>{{ unit.price }}</td>
            <td>{{ unit.area }}</td>
            <td>
              <span class="sk-badge" :class="unit.status === 'Available' ? 'sk-badge-available' : 'sk-badge-sold'">{{ unit.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
