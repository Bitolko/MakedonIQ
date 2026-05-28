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
    red: 'bg-heritage-red-soft text-heritage-red',
    gold: 'bg-heritage-gold-soft text-heritage-gold-deep',
    navy: 'bg-heritage-navy/10 text-heritage-navy',
};
</script>

<template>
    <article class="soft-card soft-card-hover flex h-full flex-col p-6">
        <div :class="['mb-6 flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-black', toneClasses[category.tone] || toneClasses.red]">
            {{ category.icon }}
        </div>
        <AppBadge :variant="category.tone === 'navy' ? 'navy' : category.tone">{{ category.level }}</AppBadge>
        <h3 class="mt-4 text-2xl font-black text-heritage-ink">{{ category.title }}</h3>
        <p class="mt-3 flex-1 text-heritage-muted">{{ category.description }}</p>
        <div class="mt-6 flex items-center justify-between gap-4">
            <span class="text-sm font-bold text-heritage-muted">{{ category.quizzes }} quizzes</span>
            <PrimaryButton :href="category.href">Start</PrimaryButton>
        </div>
    </article>
</template>
