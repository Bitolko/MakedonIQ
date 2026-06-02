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

const statusVariant = {
    Completed: 'green',
    'Not started': 'neutral',
    Start: 'gold',
    Locked: 'neutral',
};
</script>

<template>
    <article class="soft-card soft-card-hover flex h-full flex-col p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-heritage-gold/40 bg-heritage-gold-faint text-lg font-black text-heritage-gold-deep">
                Q
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <AppBadge v-if="quiz.isDemo" variant="gold">Demo</AppBadge>
                <AppBadge v-if="quiz.isLocked" variant="neutral">Locked</AppBadge>
                <AppBadge :variant="statusVariant[quiz.status] || difficultyVariant[quiz.difficulty]">
                    {{ quiz.status }}
                </AppBadge>
                <AppBadge v-if="quiz.isMapChallenge" variant="gold">Map</AppBadge>
            </div>
        </div>
        <h3 class="mt-6 text-2xl font-black text-heritage-ink">{{ quiz.title }}</h3>
        <p class="mt-3 flex-1 text-heritage-muted">{{ quiz.description }}</p>
        <div class="mt-5 flex flex-wrap gap-2 text-sm font-bold text-heritage-muted">
            <span class="rounded-full bg-heritage-panel px-3 py-1">{{ quiz.difficulty }}</span>
            <span class="rounded-full bg-heritage-panel px-3 py-1">{{ quiz.questions }} questions</span>
            <span class="rounded-full bg-heritage-panel px-3 py-1">{{ quiz.time }}</span>
        </div>
        <div class="mt-6">
            <ProgressBar :value="quiz.progress" :label="quiz.progressLabel || 'Progress'" />
            <div v-if="quiz.progressDetails?.length" class="mt-3 flex flex-wrap gap-2 text-xs font-black text-heritage-muted">
                <span v-for="detail in quiz.progressDetails" :key="detail" class="rounded-full bg-heritage-panel px-3 py-1">
                    {{ detail }}
                </span>
            </div>
        </div>
        <div v-if="quiz.isLocked" class="mt-6 rounded-2xl border border-heritage-line bg-heritage-panel p-4">
            <p class="text-sm font-bold leading-6 text-heritage-muted">
                {{ quiz.lockedMessage || 'Create account to unlock all quizzes.' }}
            </p>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                <PrimaryButton :href="quiz.registerHref || '/register'" class="w-full" size="sm">Create account</PrimaryButton>
                <PrimaryButton :href="quiz.loginHref || '/login'" class="w-full" variant="soft" size="sm">Log in</PrimaryButton>
            </div>
        </div>
        <PrimaryButton v-else class="mt-6" :href="quiz.href" :variant="quiz.status === 'Completed' ? 'gold' : 'red'">
            {{ quiz.actionLabel || (quiz.status === 'Completed' ? 'Review' : quiz.status) }}
        </PrimaryButton>
    </article>
</template>
