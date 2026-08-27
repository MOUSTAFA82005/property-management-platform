<script setup>
import { RouterLink } from 'vue-router'
import OwnerPageHeader from '../../components/owner/OwnerPageHeader.vue'
import StatusBadge from '../../components/owner/StatusBadge.vue'
import RevenueChart from './RevenueChart/RevenueChart.vue'
import UnitsChart from './Units/UnitsChart.vue'
const stats = [['Properties', '12', '+2 this month', 'fa-solid fa-building'], ['Available Units', '45', '+8.4% vs last month', 'fa-solid fa-door-open'], ['Pending Requests', '8', '3 need attention', 'fa-solid fa-clock'], ['Monthly Revenue', 'EGP 250K', '+12.5% vs last month', 'fa-solid fa-sack-dollar']]
const requests = [{ id: 'PR-1001', customer: 'John Doe', property: 'Ocean View Villa', unit: 'Unit A1', status: 'Pending' }, { id: 'PR-1002', customer: 'Alice Brown', property: 'Downtown Penthouse', unit: 'Unit B2', status: 'Approved' }, { id: 'PR-1003', customer: 'Michael Lee', property: 'Palm Residence', unit: 'Unit C4', status: 'Pending' }]
const activities = [['John Doe', 'Submitted a purchase request', '2 min ago'], ['Payment #PAY-8821', 'Payment received', '1 hour ago'], ['Contract #CTR-2026-001', 'Contract signed', 'Yesterday'], ['Ocean View Villa', 'Property updated', 'Yesterday']]
</script>
<template>
  <OwnerPageHeader title="Good morning, Owner"
    subtitle="Here’s what’s happening across your property portfolio today." />
  <div class="owner-stats">
    <div v-for="s in stats" :key="s[0]" class="owner-card owner-stat">
      <div class="stat-icon"><i :class="s[3]"></i></div>
      <div class="owner-stat-label">{{ s[0] }}</div>
      <div class="owner-stat-value">{{ s[1] }}</div>
      <div class="owner-stat-trend">{{ s[2] }}</div>
    </div>
  </div>
  <div class="owner-quick">
    <RouterLink to="/owner/properties/create"><i class="fa-solid fa-plus"></i> Add Property <span><i
          class="fa-solid fa-arrow-right"></i></span></RouterLink>
    <RouterLink to="/owner/units/create"><i class="fa-solid fa-plus"></i> Add Unit <span><i
          class="fa-solid fa-arrow-right"></i></span></RouterLink>
    <RouterLink to="/owner/contracts/create"><i class="fa-solid fa-file-circle-plus"></i> Create Contract <span><i
          class="fa-solid fa-arrow-right"></i></span></RouterLink>
  </div>

  <div class="owner-dashboard-grid">
    <RevenueChart />
    <UnitsChart />

    </div>
    <div class="owner-card">
      <div class="owner-card-head">
        <h2>Recent activity</h2><span>View all</span>
      </div>
      <div class="owner-list">
        <div v-for="a in activities" :key="a[0]" class="owner-list-item">
          <div class="owner-list-main">
            <div class="owner-mini-avatar"><i class="fa-solid fa-user"></i></div>
            <div><b>{{ a[0] }}</b><small>{{ a[1] }} · {{ a[2] }}</small></div>
          </div>
        </div>
      </div>
    </div>
  <!-- </div> -->
  <div class="owner-card">
    <div class="owner-card-head">
      <h2>Recent purchase requests</h2>
      <RouterLink to="/owner/purchase-requests" class="owner-btn owner-btn-light">View all</RouterLink>
    </div>
    <div class="owner-table-wrap">
      <table class="owner-table">
        <thead>
          <tr>
            <th>Request</th>
            <th>Customer</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in requests" :key="r.id">
            <td><strong>{{ r.id }}</strong></td>
            <td>{{ r.customer }}</td>
            <td>{{ r.property }}</td>
            <td>{{ r.unit }}</td>
            <td>
              <StatusBadge :status="r.status" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
