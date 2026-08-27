<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/units" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Units</RouterLink>
    <OwnerPageHeader :title="`Edit Unit #${id}`" subtitle="Update unit pricing, status and details." />
    <div class="owner-card owner-form-card">
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Unit number</label><input class="owner-input" value="A1"></div>
                <div class="owner-field"><label>Property</label><select class="owner-select">
                        <option>Ocean View Villa</option>
                    </select></div>
                <div class="owner-field"><label>Price</label><input class="owner-input" value="EGP 2,500,000"></div>
                <div class="owner-field"><label>Area</label><input class="owner-input" value="150 m²"></div>
                <div class="owner-field"><label>Status</label><select class="owner-select">
                        <option selected>Available</option>
                        <option>Sold</option>
                    </select></div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-primary">Update Unit</button>
                <RouterLink to="/owner/units" class="owner-btn owner-btn-light">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>
