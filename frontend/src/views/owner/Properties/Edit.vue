<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/properties" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Properties
    </RouterLink>
    <OwnerPageHeader :title="`Edit Property #${id}`" subtitle="Update property information and availability." />
    <div class="owner-card owner-form-card">
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Property name</label><input class="owner-input"
                        value="Ocean View Villa"></div>
                <div class="owner-field"><label>Location</label><input class="owner-input" value="Alexandria"></div>
                <div class="owner-field"><label>Property type</label><select class="owner-select">
                        <option selected>Villa</option>
                        <option>Apartment</option>
                        <option>Commercial</option>
                    </select></div>
                <div class="owner-field"><label>Status</label><select class="owner-select">
                        <option selected>Active</option>
                        <option>Archived</option>
                    </select></div>
                <div class="owner-field full"><label>Description</label><textarea
                        class="owner-textarea">A luxury villa located by the sea.</textarea></div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-primary">Update Property</button>
                <RouterLink to="/owner/properties" class="owner-btn owner-btn-light">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>
