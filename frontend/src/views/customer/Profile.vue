<script setup>
</script>

<template>
  <div class="sk-page">
    <div class="sk-header">
      <h1>My Profile</h1>
      <p>Manage your account settings and personal information.</p>
    </div>

    <div class="sk-form">
      <div class="sk-form-group">
        <label class="sk-form-label">Full Name</label>
        <input type="text" class="sk-form-input" value="Jane Doe" readonly />
      </div>
      
      <div class="sk-form-group">
        <label class="sk-form-label">Email Address</label>
        <input type="email" class="sk-form-input" value="jane.doe@example.com" readonly />
      </div>

      <div class="sk-form-group">
        <label class="sk-form-label">Phone Number</label>
        <input type="tel" class="sk-form-input" value="+20 10 9876 5432" readonly />
      </div>

      <div class="sk-form-actions" style="margin-top: 2rem;">
        <button class="sk-btn sk-btn-primary">Edit Profile</button>
        <button class="sk-btn sk-btn-secondary">Change Password</button>
      </div>
    </div>
  </div>
</template>
