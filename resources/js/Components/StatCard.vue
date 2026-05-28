<script setup>
defineProps({
    stat: {
        type: Object,
        required: true,
    },
});

const toneClasses = {
    red: 'bg-heritage-red-faint text-heritage-red border-heritage-red/20',
    gold: 'bg-heritage-gold-faint text-heritage-gold-deep border-heritage-gold/40',
    navy: 'bg-heritage-navy-soft text-heritage-navy border-heritage-navy/10',
};
</script>

<template>
    <article class="soft-card soft-card-hover p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="label">{{ stat.label }}</p>
                <p class="mt-3 text-3xl font-black text-heritage-ink md:text-4xl">{{ stat.value }}</p>
                <p class="mt-2 text-sm font-semibold text-heritage-muted">{{ stat.detail }}</p>
            </div>
            <div :class="['flex h-13 w-13 items-center justify-center rounded-2xl border text-xl font-black shadow-card', toneClasses[stat.tone] || toneClasses.red]">
                {{ stat.icon }}
            </div>
        </div>
    </article>
</template>
