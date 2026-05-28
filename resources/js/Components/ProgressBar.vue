<script setup>
defineProps({
    value: {
        type: Number,
        default: 0,
    },
    label: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div>
        <div v-if="label" class="mb-2 flex items-center justify-between text-sm">
            <span class="font-semibold text-heritage-muted">{{ label }}</span>
            <span class="font-black text-heritage-red">{{ value }}%</span>
        </div>
        <div class="h-3 overflow-hidden rounded-full bg-heritage-panel">
            <div class="h-full rounded-full bg-heritage-red transition-all duration-500" :style="{ width: `${Math.min(value, 100)}%` }" />
        </div>
    </div>
</template>
