<script setup>
defineProps({
    variant: {
        type: String,
        default: 'gold',
    },
});

const variants = {
    gold: 'bg-heritage-gold-soft text-heritage-gold-deep',
    red: 'bg-heritage-red-soft text-heritage-red-dark',
    navy: 'bg-heritage-navy/10 text-heritage-navy',
    green: 'bg-emerald-100 text-emerald-800',
    neutral: 'bg-heritage-panel text-heritage-muted',
};
</script>

<template>
    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase', variants[variant] || variants.neutral]">
        <slot />
    </span>
</template>
