<template>
  <div class="form-section-header mb-3" :class="`form-section-header--${variant}`">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <h5 class="form-section-title mb-0">{{ title }}</h5>
      <span v-if="badge" class="badge form-section-badge" :class="badgeClass">{{ badge }}</span>
    </div>
    <p v-if="description" class="form-section-description mb-0 mt-1">{{ description }}</p>
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
  --brand: #ab0a3d;
  --brand-dark: #440412;
  --gold: #b68400;
  --ink-soft: #7a6a6d;
  padding: 0.85rem 1.1rem;
  border-radius: 12px;
  border: 1px solid #e7d9dc;
  border-left: 4px solid var(--brand);
  background: #faf6f4;
}

.form-section-header--catalog {
  border-left-color: #7a6a6d;
  background: #f3eeec;
}

.form-section-header--session {
  border-left-color: var(--brand);
  background: rgba(171, 10, 61, 0.06);
}

.form-section-header--capture {
  border-left-color: var(--gold);
  background: rgba(182, 132, 0, 0.07);
}

.form-section-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--brand-dark);
}

.form-section-description {
  font-size: 0.86rem;
  color: var(--ink-soft);
  line-height: 1.45;
}

.form-section-badge {
  font-weight: 600;
  font-size: 0.72rem;
  letter-spacing: 0.02em;
}
</style>
