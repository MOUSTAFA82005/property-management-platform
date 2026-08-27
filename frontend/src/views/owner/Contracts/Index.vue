<script
    setup>    import { RouterLink } from 'vue-router';
     import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const contracts = [{ id: 'CTR-2026-001', customer: 'John Doe', property: 'Ocean View Villa', unit: 'Unit A1', date: 'Oct 15, 2026', status: 'Active' }, { id: 'CTR-2025-089', customer: 'Alice Brown', property: 'Downtown Penthouse', unit: 'Unit B2', date: 'Dec 05, 2025', status: 'Closed' }]</script>
<template>
    <OwnerPageHeader title="Contracts" subtitle="Manage signed agreements and property contracts."
        action-text="Create Contract" action-to="/owner/contracts/create" />
    <div class="owner-card">
        <div class="owner-card-head"><input class="owner-search" placeholder="Search contracts..." /><span>2
                contracts</span></div>
        <div class="owner-table-wrap">
            <table class="owner-table">
                <thead>
                    <tr>
                        <th>Contract ID</th>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Unit</th>
                        <th>Date Signed</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in contracts" :key="c.id">
                        <td><strong>{{ c.id }}</strong></td>
                        <td>{{ c.customer }}</td>
                        <td>{{ c.property }}</td>
                        <td>{{ c.unit }}</td>
                        <td>{{ c.date }}</td>
                        <td>
                            <StatusBadge :status="c.status" />
                        </td>
                        <td>
                            <RouterLink :to="`/owner/contracts/${c.id}`" class="owner-btn owner-btn-light">View
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
