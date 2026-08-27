<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const units = [{ id: 101, unit: 'Unit A1', property: 'Ocean View Villa', price: 'EGP 2,500,000', area: '150 m²', status: 'Available' }, { id: 102, unit: 'Unit B2', property: 'Downtown Penthouse', price: 'EGP 4,200,000', area: '200 m²', status: 'Sold' }, { id: 103, unit: 'Unit C4', property: 'Palm Residence', price: 'EGP 1,850,000', area: '135 m²', status: 'Available' }]</script>
<template>
    <OwnerPageHeader title="Units" subtitle="Track availability, pricing and occupancy for every unit."
        action-text="Add Unit" action-to="/owner/units/create" />
    <div class="owner-card">
        <div class="owner-card-head">
            <div class="owner-search-row"><input class="owner-search"
                    placeholder="Search units or properties..." /><select class="owner-select">
                    <option>All statuses</option>
                    <option>Available</option>
                    <option>Sold</option>
                </select></div><span>{{ units.length }} units</span>
        </div>
        <div class="owner-table-wrap">
            <table class="owner-table">
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Property</th>
                        <th>Price</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in units" :key="u.id">
                        <td><strong>{{ u.unit }}</strong></td>
                        <td>{{ u.property }}</td>
                        <td>{{ u.price }}</td>
                        <td>{{ u.area }}</td>
                        <td>
                            <StatusBadge :status="u.status" />
                        </td>
                        <td>
                            <RouterLink :to="`/owner/units/${u.id}/edit`" class="owner-btn owner-btn-light">Edit
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
