<script setup>
import AppBadge from './AppBadge.vue';
import ProgressBar from './ProgressBar.vue';
import PrimaryButton from './PrimaryButton.vue';

defineProps({
    quiz: {
        type: Object,
        required: true,
    },
});

const difficultyVariant = {
    Beginner: 'gold',
    Intermediate: 'red',
    Advanced: 'navy',
};
</script>

<template>
    <article class="soft-card soft-card-hover flex h-full flex-col p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-heritage-gold-soft text-lg font-black text-heritage-gold-deep">
                ★
            </div>
            <AppBadge :variant="quiz.status === 'Completed' ? 'green' : difficultyVariant[quiz.difficulty]">
                {{ quiz.status }}
            </AppBadge>
        </div>
        <h3 class="mt-6 text-2xl font-black text-heritage-ink">{{ quiz.title }}</h3>
        <p class="mt-3 flex-1 text-heritage-muted">{{ quiz.description }}</p>
        <div class="mt-5 flex flex-wrap gap-3 text-sm font-bold text-heritage-muted">
            <span>{{ quiz.difficulty }}</span>
            <span>{{ quiz.questions }} questions</span>
            <span>{{ quiz.time }}</span>
        </div>
        <div class="mt-6">
            <ProgressBar :value="quiz.progress" label="Progress" />
        </div>
        <PrimaryButton class="mt-6" :href="quiz.href" :variant="quiz.status === 'Completed' ? 'gold' : 'red'">
            {{ quiz.status === 'Completed' ? 'Review' : quiz.status }}
        </PrimaryButton>
    </article>
</template>
