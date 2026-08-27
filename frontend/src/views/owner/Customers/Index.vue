<script
    setup>    import { RouterLink } from 'vue-router'; import OwnerPageHeader from '../../../components/owner/OwnerPageHeader.vue'; import StatusBadge from '../../../components/owner/StatusBadge.vue'; const customers = [{ id: 1, name: 'John Doe', email: 'john@example.com', phone: '+20 10 1111 2222', requests: 2, status: 'Active' }, { id: 2, name: 'Jane Smith', email: 'jane@example.com', phone: '+20 10 3333 4444', requests: 0, status: 'Active' }, { id: 3, name: 'Alice Brown', email: 'alice@example.com', phone: '+20 10 5555 6666', requests: 1, status: 'Active' }]</script>
<template>
    <OwnerPageHeader title="Customers" subtitle="Manage prospective and current property buyers." />
    <div class="owner-card">
        <div class="owner-card-head"><input class="owner-search" placeholder="Search customers..." /><span>3
                customers</span></div>
        <div class="owner-table-wrap">
            <table class="owner-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Requests</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in customers" :key="c.id">
                        <td><strong>{{ c.name }}</strong></td>
                        <td>{{ c.email }}</td>
                        <td>{{ c.phone }}</td>
                        <td>{{ c.requests }}</td>
                        <td>
                            <StatusBadge :status="c.status" />
                        </td>
                        <td>
                            <RouterLink :to="`/owner/customers/${c.id}`" class="owner-btn owner-btn-light">View
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
