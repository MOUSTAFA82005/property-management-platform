<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'</script>
<template>
    <RouterLink to="/owner/payments" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Payments
    </RouterLink>
    <OwnerPageHeader title="Add Payment" subtitle="Record an incoming payment against a contract." />
    <div class="owner-card owner-form-card">
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Customer</label><select class="owner-select">
                        <option>John Doe</option>
                        <option>Alice Brown</option>
                    </select></div>
                <div class="owner-field"><label>Contract</label><select class="owner-select">
                        <option>CTR-2026-001</option>
                        <option>CTR-2025-089</option>
                    </select></div>
                <div class="owner-field"><label>Amount</label><input class="owner-input" placeholder="EGP 50,000"></div>
                <div class="owner-field"><label>Payment date</label><input class="owner-input" type="date"></div>
                <div class="owner-field full"><label>Notes</label><textarea class="owner-textarea"
                        placeholder="Optional notes..."></textarea></div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-primary">Save Payment</button>
                <RouterLink to="/owner/payments" class="owner-btn owner-btn-light">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>
