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
    tone: {
        type: String,
        default: 'red',
    },
});

const tones = {
    red: 'from-heritage-red to-heritage-red-dark',
    gold: 'from-heritage-gold to-heritage-gold-deep',
    navy: 'from-heritage-navy to-heritage-red',
};
</script>

<template>
    <div>
        <div v-if="label" class="mb-2 flex items-center justify-between text-sm">
            <span class="font-semibold text-heritage-muted">{{ label }}</span>
            <span class="font-black text-heritage-red">{{ value }}%</span>
        </div>
        <div class="h-3 overflow-hidden rounded-full bg-heritage-panel ring-1 ring-heritage-line/50">
            <div :class="['h-full rounded-full bg-linear-to-r transition-all duration-500', tones[tone] || tones.red]" :style="{ width: `${Math.min(value, 100)}%` }" />
        </div>
    </div>
</template>
