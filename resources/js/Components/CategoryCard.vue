<script setup>
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

defineProps({
    category: {
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
    <article class="soft-card soft-card-hover group flex h-full flex-col overflow-hidden">
        <div class="h-2 bg-linear-to-r from-heritage-red via-heritage-gold to-heritage-navy" />
        <div class="flex flex-1 flex-col p-6">
            <div class="flex items-start justify-between gap-4">
                <div :class="['flex h-16 w-16 items-center justify-center rounded-2xl border text-lg font-black shadow-card transition group-hover:scale-105', toneClasses[category.tone] || toneClasses.red]">
                    {{ category.icon }}
                </div>
                <AppBadge :variant="category.tone === 'navy' ? 'navy' : category.tone">{{ category.level }}</AppBadge>
            </div>
            <h3 class="mt-6 text-2xl font-black text-heritage-ink">{{ category.title }}</h3>
            <p class="mt-3 flex-1 leading-7 text-heritage-muted">{{ category.description }}</p>
            <div class="mt-6 flex items-center justify-between gap-4 border-t border-heritage-line/40 pt-5">
                <span class="text-sm font-black text-heritage-muted">{{ category.quizzes }} quizzes</span>
                <PrimaryButton :href="category.href" size="sm">Start</PrimaryButton>
            </div>
        </div>
    </article>
</template>
