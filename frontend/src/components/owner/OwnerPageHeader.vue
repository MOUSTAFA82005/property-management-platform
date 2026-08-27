<script setup>
defineProps({ title: String, subtitle: String, actionText: String, actionTo: String })
</script>
<template>
  <div class="owner-page-head">
    <div><div class="owner-eyebrow">Owner workspace</div><h1>{{ title }}</h1><p>{{ subtitle }}</p></div>
    <RouterLink v-if="actionTo" :to="actionTo" class="owner-btn owner-btn-primary"><i class="fa-solid fa-plus"></i> {{ actionText }}</RouterLink>
  </div>
</template>
