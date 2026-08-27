<script
    setup>    import { RouterLink, useRoute } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const id = useRoute().params.id</script>
<template>
    <RouterLink to="/owner/customers" class="owner-back"><i class="fa-solid fa-arrow-left"></i> Back to Customers
    </RouterLink>
    <OwnerPageHeader title="John Doe" subtitle="Customer profile and purchase activity." />
    <div class="owner-dashboard-grid">
        <div class="owner-card">
            <div class="owner-card-head">
                <h2>Customer information</h2>
                <StatusBadge status="Active" />
            </div>
            <div class="owner-form">
                <div class="owner-form-grid">
                    <div class="owner-field"><label>Customer ID</label><input class="owner-input" :value="`#${id}`"
                            readonly></div>
                    <div class="owner-field"><label>Full name</label><input class="owner-input" value="John Doe"
                            readonly></div>
                    <div class="owner-field"><label>Email</label><input class="owner-input" value="john@example.com"
                            readonly></div>
                    <div class="owner-field"><label>Phone</label><input class="owner-input" value="+20 10 1111 2222"
                            readonly></div>
                </div>
            </div>
        </div>
        <div class="owner-card">
            <div class="owner-card-head">
                <h2>Purchase requests</h2>
            </div>
            <div class="owner-list">
                <div class="owner-list-item">
                    <div><b>Ocean View Villa · Unit A1</b><small>Submitted Oct 12, 2026</small></div>
                    <StatusBadge status="Pending" />
                </div>
                <div class="owner-list-item">
                    <div><b>Downtown Penthouse · Unit B2</b><small>Submitted Sep 20, 2026</small></div>
                    <StatusBadge status="Approved" />
                </div>
            </div>
        </div>
    </div>
</template>
