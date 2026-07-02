<template>
  <div class="form-section-header mb-3" :class="`form-section-header--${variant}`">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <h5 class="form-section-title mb-0">{{ title }}</h5>
      <span v-if="badge" class="badge" :class="badgeClass">{{ badge }}</span>
    </div>
    <p v-if="description" class="form-section-description text-muted small mb-0 mt-1">{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  badge: { type: String, default: '' },
  badgeClass: { type: String, default: 'bg-primary' },
  description: { type: String, default: '' },
  variant: { type: String, default: 'capture' },
})
</script>

<style scoped>
.form-section-header {
  padding: 0.75rem 1rem;
  border-radius: 6px;
  border-left: 4px solid #6969f4;
  background: rgba(105, 105, 244, 0.06);
}

.form-section-header--catalog {
  border-left-color: #6c757d;
  background: rgba(108, 117, 125, 0.08);
}

.form-section-header--session {
  border-left-color: #0d6efd;
  background: rgba(13, 110, 253, 0.06);
}

.form-section-title {
  font-size: 1.05rem;
  font-weight: 600;
}
</style>
