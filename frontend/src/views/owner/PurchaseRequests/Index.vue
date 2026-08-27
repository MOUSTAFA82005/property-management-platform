<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const requests = [{ id: 'PR-1001', customer: 'John Doe', property: 'Ocean View Villa', unit: 'Unit A1', date: 'Oct 12, 2026', status: 'Pending' }, { id: 'PR-1002', customer: 'Alice Brown', property: 'Downtown Penthouse', unit: 'Unit B2', date: 'Oct 10, 2026', status: 'Approved' }, { id: 'PR-1003', customer: 'Jane Smith', property: 'Palm Residence', unit: 'Unit C4', date: 'Oct 08, 2026', status: 'Rejected' }]</script>
<template>
    <OwnerPageHeader title="Purchase Requests" subtitle="Review and manage customer requests across your properties." />
    <div class="owner-card">
        <div class="owner-card-head">
            <div class="owner-search-row"><input class="owner-search" placeholder="Search requests..." /><select
                    class="owner-select">
                    <option>All statuses</option>
                    <option>Pending</option>
                    <option>Approved</option>
                    <option>Rejected</option>
                </select></div><span>{{ requests.length }} requests</span>
        </div>
        <div class="owner-table-wrap">
            <table class="owner-table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Unit</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in requests" :key="r.id">
                        <td><strong>{{ r.id }}</strong></td>
                        <td>{{ r.customer }}</td>
                        <td>{{ r.property }}</td>
                        <td>{{ r.unit }}</td>
                        <td>{{ r.date }}</td>
                        <td>
                            <StatusBadge :status="r.status" />
                        </td>
                        <td>
                            <RouterLink :to="`/owner/purchase-requests/${r.id}`" class="owner-btn owner-btn-light">
                                Review</RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
