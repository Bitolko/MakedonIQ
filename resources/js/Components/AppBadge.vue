<script setup>
defineProps({
    variant: {
        type: String,
        default: 'gold',
    },
});

const variants = {
    gold: 'border-heritage-gold/40 bg-heritage-gold-faint text-heritage-gold-deep',
    red: 'border-heritage-red/20 bg-heritage-red-faint text-heritage-red-dark',
    navy: 'border-heritage-navy/10 bg-heritage-navy-soft text-heritage-navy',
    green: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    neutral: 'border-heritage-line/60 bg-heritage-panel text-heritage-muted',
};
</script>

<template>
    <span :class="['inline-flex items-center rounded-full border px-3 py-1 text-xs font-black uppercase', variants[variant] || variants.neutral]">
        <slot />
    </span>
</template>
