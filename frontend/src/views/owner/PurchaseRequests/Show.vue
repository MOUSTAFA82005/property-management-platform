<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/purchase-requests" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Purchase
        Requests</RouterLink>
    <OwnerPageHeader :title="`Request ${id}`" subtitle="Review customer interest and take action." />
    <div class="owner-card owner-form-card">
        <div class="owner-card-head">
            <h2>Request details</h2>
            <StatusBadge status="Pending" />
        </div>
        <div class="owner-form">
            <div class="owner-form-grid">
                <div class="owner-field"><label>Customer</label><input class="owner-input" value="John Doe" readonly>
                </div>
                <div class="owner-field"><label>Property</label><input class="owner-input" value="Ocean View Villa"
                        readonly></div>
                <div class="owner-field"><label>Unit</label><input class="owner-input" value="Unit A1" readonly></div>
                <div class="owner-field"><label>Submitted</label><input class="owner-input" value="Oct 12, 2026"
                        readonly></div>
                <div class="owner-field full"><label>Customer message</label><textarea class="owner-textarea"
                        readonly>I am interested in purchasing this unit and would like to discuss the payment plan.</textarea>
                </div>
            </div>
            <div class="owner-form-actions"><button class="owner-btn owner-btn-success"><i
                        class="fa-solid fa-check"></i> Approve Request</button><button
                    class="owner-btn owner-btn-danger"><i class="fa-solid fa-xmark"></i> Reject</button></div>
        </div>
    </div>
</template>