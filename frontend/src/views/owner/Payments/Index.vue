<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const payments = [{ id: 'PAY-8821', customer: 'John Doe', contract: 'CTR-2026-001', amount: 'EGP 150,000', date: 'Oct 20, 2026', status: 'Paid' }, { id: 'PAY-8822', customer: 'John Doe', contract: 'CTR-2026-001', amount: 'EGP 50,000', date: 'Nov 20, 2026', status: 'Pending' }, { id: 'PAY-8700', customer: 'Alice Brown', contract: 'CTR-2025-089', amount: 'EGP 10,000', date: 'Jan 05, 2026', status: 'Paid' }]</script>
<template>
    <OwnerPageHeader title="Payments" subtitle="Track incoming installments and completed payments."
        action-text="Add Payment" action-to="/owner/payments/create" />
    <div class="owner-stats">
        <div class="owner-card owner-stat">
            <div class="owner-stat-label">Collected</div>
            <div class="owner-stat-value">EGP 160K</div>
            <div class="owner-stat-trend">This month</div>
        </div>
        <div class="owner-card owner-stat">
            <div class="owner-stat-label">Pending</div>
            <div class="owner-stat-value">EGP 50K</div>
            <div class="owner-stat-trend">1 payment</div>
        </div>
    </div>
    <div class="owner-card">
        <div class="owner-card-head"><input class="owner-search" placeholder="Search payments..." /><span>3
                payments</span></div>
        <div class="owner-table-wrap">
            <table class="owner-table">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Customer</th>
                        <th>Contract</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payments" :key="p.id">
                        <td><strong>{{ p.id }}</strong></td>
                        <td>{{ p.customer }}</td>
                        <td>{{ p.contract }}</td>
                        <td><strong>{{ p.amount }}</strong></td>
                        <td>{{ p.date }}</td>
                        <td>
                            <StatusBadge :status="p.status" />
                        </td>
                        <td>
                            <RouterLink :to="`/owner/payments/${p.id}`" class="owner-btn owner-btn-light">View
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
