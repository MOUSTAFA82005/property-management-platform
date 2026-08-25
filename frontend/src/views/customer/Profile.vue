<script setup>
import CustomerDashboardLayout from '../../components/customer/CustomerDashboardLayout.vue'
</script>

<template>
  <CustomerDashboardLayout>
    <div class="dash-welcome-section">
      <h2 class="dash-welcome">Welcome back, Mustafa Soliman</h2>
      <p class="dash-subtitle">Here is a quick overview of your account.</p>
    </div>
    
    <div class="dash-section">
      <div class="dash-section-header">
        <h3 class="dash-section-title">Personal Information</h3>
      </div>
      
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Full Name</span>
          <span class="info-value">Mustafa Soliman</span>
        </div>
        <div class="info-item">
          <span class="info-label">Email</span>
          <span class="info-value">customer@example.com</span>
        </div>
        <div class="info-item">
          <span class="info-label">Phone</span>
          <span class="info-value">+20 XXX XXX XXXX</span>
        </div>
      </div>
      
      <button class="btn-primary-dash">Edit Profile</button>
    </div>

    <!-- Summary Cards -->
    <div class="dash-stats">
      <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value">3</div>
        <div class="stat-title">Purchase Requests</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-value">1</div>
        <div class="stat-title">Active Contracts</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">💳</div>
        <div class="stat-value">2</div>
        <div class="stat-title">Pending Payments</div>
      </div>
    </div>
  </CustomerDashboardLayout>
</template>

<style scoped>
.dash-welcome-section {
  margin-bottom: 2.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.dash-welcome {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.25rem;
}

.dash-subtitle {
  color: #64748b;
  font-size: 0.95rem;
}

.dash-section {
  margin-bottom: 3rem;
}

.dash-section-header {
  margin-bottom: 1.5rem;
}

.dash-section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.info-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-value {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
}

.btn-primary-dash {
  display: inline-block;
  padding: 0.65rem 1.25rem;
  background: rgba(134, 76, 255, 0.08);
  color: #864CFF;
  border: 1px solid rgba(134, 76, 255, 0.2);
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary-dash:hover {
  background: #864CFF;
  color: #fff;
  border-color: #864CFF;
}

.dash-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.25rem;
}

.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  font-size: 1.5rem;
  margin-bottom: 0.75rem;
  background: #f8fafc;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
  line-height: 1;
}

.stat-title {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}
</style>
