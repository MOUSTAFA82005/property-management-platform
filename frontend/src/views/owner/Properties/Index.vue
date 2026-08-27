<script setup>
import { ref, computed } from 'vue'; import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'
const q = ref(''); const props = [{ id: 1, name: 'Ocean View Villa', location: 'Alexandria', units: 5, type: 'Villa', status: 'Active' }, { id: 2, name: 'Downtown Penthouse', location: 'Cairo', units: 1, type: 'Penthouse', status: 'Active' }, { id: 3, name: 'Palm Residence', location: 'New Cairo', units: 12, type: 'Residential', status: 'Active' }]; const filtered = computed(() => props.filter(p => (p.name + p.location).toLowerCase().includes(q.value.toLowerCase())))
</script>
<template>
    <OwnerPageHeader title="Properties" subtitle="Manage your portfolio, listings and property information."
        action-text="Add Property" action-to="/owner/properties/create" />
    <div class="owner-card">
        <div class="owner-card-head">
            <div class="owner-search-row"><input v-model="q" class="owner-search"
                    placeholder="Search properties..." /><select class="owner-select">
                    <option>All statuses</option>
                    <option>Active</option>
                    <option>Archived</option>
                </select></div><span>{{ filtered.length }} properties</span>
        </div>
        <div class="owner-table-wrap">
            <table class="owner-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in filtered" :key="p.id">
                        <td><strong>{{ p.name }}</strong></td>
                        <td>{{ p.location }}</td>
                        <td>{{ p.type }}</td>
                        <td>{{ p.units }}</td>
                        <td>
                            <StatusBadge :status="p.status" />
                        </td>
                        <td>
                            <RouterLink :to="`/owner/properties/${p.id}`" class="owner-btn owner-btn-light">View
                            </RouterLink>
                            <RouterLink :to="`/owner/properties/${p.id}/edit`" class="owner-btn owner-btn-light">Edit
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
