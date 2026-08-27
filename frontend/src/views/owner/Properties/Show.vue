<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/properties" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Properties
    </RouterLink>
    <OwnerPageHeader :title="`Ocean View Villa`" subtitle="Property details and unit overview."
        :action-text="'Edit Property'" :action-to="`/owner/properties/${id}/edit`" />
    <div class="owner-dashboard-grid">
        <div class="owner-card">
            <div class="owner-card-head">
                <h2>Property information</h2>
                <StatusBadge status="Active" />
            </div>
            <div class="owner-form">
                <div class="owner-form-grid">
                    <div class="owner-field"><label>Property ID</label><input class="owner-input" value="#1" readonly>
                    </div>
                    <div class="owner-field"><label>Location</label><input class="owner-input" value="Alexandria"
                            readonly></div>
                    <div class="owner-field"><label>Type</label><input class="owner-input" value="Villa" readonly></div>
                    <div class="owner-field"><label>Total units</label><input class="owner-input" value="5" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="owner-card">
            <div class="owner-card-head">
                <h2>Units</h2><span>5 total</span>
            </div>
            <div class="owner-list">
                <div v-for="u in ['Unit A1', 'Unit A2', 'Unit B1', 'Unit B2']" :key="u" class="owner-list-item">
                    <div class="owner-list-main">
                        <div class="owner-mini-avatar"><i class="fa-solid fa-door-open"></i></div>
                        <div><b>{{ u }}</b><small>150 m² · EGP 2.5M</small></div>
                    </div>
                    <StatusBadge status="Available" />
                </div>
            </div>
        </div>
    </div>
</template>
