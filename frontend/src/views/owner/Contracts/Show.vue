<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/contracts" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Contracts
    </RouterLink>
    <OwnerPageHeader :title="`Contract ${id}`" subtitle="Signed agreement details and payment schedule." />
    <div class="owner-dashboard-grid">
        <div class="owner-card">
            <div class="owner-card-head">
                <h2>Contract information</h2>
                <StatusBadge status="Active" />
            </div>
            <div class="owner-form">
                <div class="owner-form-grid">
                    <div class="owner-field"><label>Customer</label><input class="owner-input" value="John Doe"
                            readonly></div>
                    <div class="owner-field"><label>Property</label><input class="owner-input" value="Ocean View Villa"
                            readonly></div>
                    <div class="owner-field"><label>Unit</label><input class="owner-input" value="Unit A1" readonly>
                    </div>
                    <div class="owner-field"><label>Date signed</label><input class="owner-input" value="Oct 15, 2026"
                            readonly></div>
                </div>
            </div>
        </div>
        <div class="owner-card">
            <div class="owner-card-head">
                <h2>Payment schedule</h2>
            </div>
            <div class="owner-list">
                <div class="owner-list-item">
                    <div><b>First installment</b><small>Oct 20, 2026</small></div><strong>EGP 150,000</strong>
                </div>
                <div class="owner-list-item">
                    <div><b>Second installment</b><small>Nov 20, 2026</small></div><strong>EGP 50,000</strong>
                </div>
            </div>
        </div>
    </div>
</template>
