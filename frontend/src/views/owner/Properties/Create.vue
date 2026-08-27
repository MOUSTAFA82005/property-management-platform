<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'</script>
<template>
    <RouterLink to="/owner/properties" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Properties
    </RouterLink>
    <OwnerPageHeader title="Add New Property" subtitle="Create a new property listing for your portfolio." />
    <div class="owner-card owner-form-card">
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Property name</label><input class="owner-input"
                        placeholder="e.g. Nile View Tower"></div>
                <div class="owner-field"><label>Location</label><input class="owner-input"
                        placeholder="e.g. Alexandria"></div>
                <div class="owner-field"><label>Property type</label><select class="owner-select">
                        <option>Apartment</option>
                        <option>Villa</option>
                        <option>Commercial</option>
                        <option>Penthouse</option>
                    </select></div>
                <div class="owner-field"><label>Status</label><select class="owner-select">
                        <option>Active</option>
                        <option>Draft</option>
                    </select></div>
                <div class="owner-field full"><label>Description</label><textarea class="owner-textarea"
                        placeholder="Describe the property..."></textarea></div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-primary">Save Property</button>
                <RouterLink to="/owner/properties" class="owner-btn owner-btn-light">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>
