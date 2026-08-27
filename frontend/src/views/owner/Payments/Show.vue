<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/payments" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Payments
    </RouterLink>
    <OwnerPageHeader :title="`Payment ${id}`" subtitle="Payment transaction details." />
    <div class="owner-card owner-form-card">
        <div class="owner-card-head">
            <h2>Payment information</h2>
            <StatusBadge status="Paid" />
        </div>
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Payment ID</label><input class="owner-input" :value="id" readonly></div>
                <div class="owner-field"><label>Customer</label><input class="owner-input" value="John Doe" readonly>
                </div>
                <div class="owner-field"><label>Contract</label><input class="owner-input" value="CTR-2026-001"
                        readonly></div>
                <div class="owner-field"><label>Amount</label><input class="owner-input" value="EGP 150,000" readonly>
                </div>
                <div class="owner-field"><label>Payment date</label><input class="owner-input" value="Oct 20, 2026"
                        readonly></div>
            </div>
        </div>
    </div>
</template>
