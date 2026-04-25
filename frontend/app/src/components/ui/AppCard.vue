<template>
  <div
    :class="[
      'bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden',
      padding ? 'p-6' : ''
    ]"
  >
    <div v-if="$slots.header" class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
      <slot name="header" />
    </div>
    
    <div :class="[padding ? '' : 'p-6']">
      <slot />
    </div>

    <div v-if="$slots.footer" class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  padding: { type: Boolean, default: true }
});
</script>
