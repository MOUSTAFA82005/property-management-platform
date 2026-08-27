<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'</script>
<template>
    <RouterLink to="/owner/units" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Units</RouterLink>
    <OwnerPageHeader title="Add New Unit" subtitle="Create a unit and assign it to one of your properties." />
    <div class="owner-card owner-form-card">
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Unit number</label><input class="owner-input" placeholder="e.g. A1">
                </div>
                <div class="owner-field"><label>Property</label><select class="owner-select">
                        <option>Ocean View Villa</option>
                        <option>Downtown Penthouse</option>
                    </select></div>
                <div class="owner-field"><label>Price</label><input class="owner-input" placeholder="EGP 2,500,000">
                </div>
                <div class="owner-field"><label>Area</label><input class="owner-input" placeholder="150 m²"></div>
                <div class="owner-field"><label>Status</label><select class="owner-select">
                        <option>Available</option>
                        <option>Sold</option>
                    </select></div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-primary">Save Unit</button>
                <RouterLink to="/owner/units" class="owner-btn owner-btn-light">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>
