<script setup>
import { RouterLink } from 'vue-router';
 import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'
 </script>
<template>
    <RouterLink to="/owner/contracts" class="owner-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Contracts
    </RouterLink>
    <OwnerPageHeader title="Create Contract" subtitle="Create a new agreement for an approved purchase request." />
    <div class="owner-card owner-form-card">
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Customer</label><select class="owner-select">
                        <option>John Doe</option>
                        <option>Alice Brown</option>
                    </select></div>
                <div class="owner-field"><label>Property</label><select class="owner-select">
                        <option>Ocean View Villa</option>
                        <option>Downtown Penthouse</option>
                    </select></div>
                <div class="owner-field"><label>Unit</label><select class="owner-select">
                        <option>Unit A1</option>
                        <option>Unit B2</option>
                    </select></div>
                <div class="owner-field"><label>Contract date</label><input class="owner-input" type="date"></div>
                <div class="owner-field full"><label>Terms</label><textarea class="owner-textarea"
                        placeholder="Add contract terms..."></textarea></div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-primary">Create Contract</button>
                <RouterLink to="/owner/contracts" class="owner-btn owner-btn-light">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>
