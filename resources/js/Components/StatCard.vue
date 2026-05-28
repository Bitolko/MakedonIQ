<script setup>
defineProps({
    stat: {
        type: Object,
        required: true,
    },
});

const toneClasses = {
    red: 'bg-heritage-red-soft text-heritage-red border-heritage-red/20',
    gold: 'bg-heritage-gold-soft text-heritage-gold-deep border-heritage-gold/40',
    navy: 'bg-heritage-navy/10 text-heritage-navy border-heritage-navy/10',
};
</script>

<template>
    <article class="soft-card p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="label">{{ stat.label }}</p>
                <p class="mt-3 text-3xl font-black text-heritage-ink">{{ stat.value }}</p>
                <p class="mt-2 text-sm font-semibold text-heritage-muted">{{ stat.detail }}</p>
            </div>
            <div :class="['flex h-12 w-12 items-center justify-center rounded-2xl border text-xl font-black', toneClasses[stat.tone] || toneClasses.red]">
                {{ stat.icon }}
            </div>
        </div>
    </article>
</template>
