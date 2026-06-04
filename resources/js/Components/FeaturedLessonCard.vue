<script setup>
import { computed } from 'vue';
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

const props = defineProps({
    lesson: {
        type: Object,
        required: true,
    },
});

const categoryVisuals = {
    'macedonian-language': {
        type: 'language',
        label: 'Macedonian Language',
        accent: 'text-heritage-red',
    },
    'macedonian-alphabet': {
        type: 'alphabet',
        label: 'Macedonian Alphabet',
        accent: 'text-heritage-gold-deep',
    },
    geography: {
        type: 'map',
        label: 'Geography',
        accent: 'text-heritage-navy',
    },
    'history-of-macedonia': {
        type: 'history',
        label: 'History',
        accent: 'text-heritage-red',
    },
    'culture-and-traditions': {
        type: 'culture',
        label: 'Culture and Traditions',
        accent: 'text-heritage-gold-deep',
    },
    'food-and-music': {
        type: 'foodMusic',
        label: 'Food and Music',
        accent: 'text-heritage-navy',
    },
    'folklore-songs': {
        type: 'foodMusic',
        label: 'Folklore Songs',
        accent: 'text-heritage-red',
    },
};

const visual = computed(() => categoryVisuals[props.lesson.category_slug] || {
    type: 'default',
    label: props.lesson.category || 'Lesson',
    accent: 'text-heritage-red',
});

const readingTime = computed(() => (
    props.lesson.estimated_minutes ? `${props.lesson.estimated_minutes} min` : 'Self-paced'
));

const difficultyLabel = computed(() => ({
    beginner: 'Beginner',
    intermediate: 'Intermediate',
    advanced: 'Advanced',
    fundamental: 'Fundamental',
})[props.lesson.difficulty] || props.lesson.difficulty || 'Lesson');
</script>

<template>
    <article
        :class="[
            'group flex h-full min-h-[25rem] flex-col rounded-[1.6rem] border bg-white p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/25 hover:shadow-soft focus-within:-translate-y-1 focus-within:border-heritage-red/25 focus-within:shadow-soft',
            lesson.isLocked ? 'border-heritage-line/80' : 'border-heritage-line/70'
        ]"
    >
        <div class="flex items-start justify-between gap-3">
            <p class="min-w-0 text-xs font-black uppercase leading-5 text-heritage-red">{{ lesson.category }}</p>
            <div class="flex shrink-0 flex-wrap justify-end gap-2">
                <AppBadge v-if="lesson.isDemo" variant="gold">Demo</AppBadge>
                <AppBadge v-if="lesson.isLocked" variant="neutral">Locked</AppBadge>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-[7.5rem_1fr] lg:grid-cols-1 xl:grid-cols-[7.5rem_1fr]">
            <div class="relative min-h-28 overflow-hidden rounded-[1.25rem] border border-heritage-gold/30 bg-heritage-panel p-3 shadow-inner">
                <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(circle at 2px 2px, rgba(164,0,0,0.12) 1px, transparent 0); background-size: 18px 18px;" />

                <div v-if="visual.type === 'language'" class="relative grid gap-2">
                    <span class="max-w-[5.9rem] rounded-xl rounded-bl-sm bg-white px-3 py-2 text-sm font-black text-heritage-red shadow-card">Здраво</span>
                    <span class="ml-auto max-w-[6.2rem] rounded-xl rounded-br-sm bg-heritage-gold px-3 py-2 text-sm font-black text-heritage-navy shadow-card">Добро</span>
                </div>

                <div v-else-if="visual.type === 'alphabet'" class="relative grid grid-cols-3 gap-2">
                    <span class="flex aspect-square items-center justify-center rounded-xl bg-white text-xl font-black text-heritage-red shadow-card">А</span>
                    <span class="flex aspect-square items-center justify-center rounded-xl bg-heritage-gold text-xl font-black text-heritage-navy shadow-card">Б</span>
                    <span class="flex aspect-square items-center justify-center rounded-xl bg-heritage-red text-xl font-black text-white shadow-card">В</span>
                </div>

                <div v-else-if="visual.type === 'map'" class="relative min-h-24">
                    <span class="absolute left-1 top-9 h-7 w-10 rounded-[50%] bg-sky-200" />
                    <span class="absolute bottom-2 right-2 h-7 w-12 rounded-[50%] bg-sky-200" />
                    <span class="absolute bottom-2 left-3 h-0 w-0 border-x-[20px] border-b-[36px] border-x-transparent border-b-heritage-navy/20" />
                    <span class="absolute left-6 right-8 top-12 h-1 rotate-[-10deg] rounded-full bg-heritage-gold" />
                    <span class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-heritage-red text-[0.62rem] font-black text-white shadow-[0_3px_0_0_#760000]">PIN</span>
                </div>

                <div v-else-if="visual.type === 'history'" class="relative min-h-24">
                    <div class="absolute left-3 top-3 h-20 w-16 rounded-xl border border-heritage-line bg-white shadow-card" />
                    <div class="absolute left-6 top-6 h-2 w-10 rounded-full bg-heritage-red/35" />
                    <div class="absolute left-6 top-11 h-2 w-8 rounded-full bg-heritage-gold" />
                    <div class="absolute bottom-4 right-3 grid gap-2">
                        <span class="h-3 w-3 rounded-full bg-heritage-red" />
                        <span class="h-3 w-3 rounded-full bg-heritage-gold" />
                        <span class="h-3 w-3 rounded-full bg-heritage-navy" />
                    </div>
                </div>

                <div v-else-if="visual.type === 'culture'" class="relative flex min-h-24 items-center justify-center">
                    <span class="absolute h-16 w-16 rounded-full border-[6px] border-heritage-gold" />
                    <span class="absolute left-3 top-5 h-8 w-8 rounded-full bg-heritage-red shadow-card" />
                    <span class="absolute right-3 top-5 h-8 w-8 rounded-full bg-heritage-navy shadow-card" />
                    <span class="absolute bottom-3 h-8 w-8 rounded-full bg-white shadow-card" />
                </div>

                <div v-else-if="visual.type === 'foodMusic'" class="relative min-h-24">
                    <span class="absolute left-3 top-6 flex h-14 w-14 items-center justify-center rounded-full border-[6px] border-heritage-gold bg-white shadow-card" />
                    <span class="absolute right-5 top-4 text-4xl font-black text-heritage-red">♪</span>
                    <div class="absolute bottom-4 right-3 flex items-end gap-1">
                        <span class="h-5 w-2 rounded-full bg-heritage-red" />
                        <span class="h-9 w-2 rounded-full bg-heritage-gold" />
                        <span class="h-7 w-2 rounded-full bg-heritage-navy" />
                    </div>
                </div>

                <div v-else class="relative flex min-h-24 items-center justify-center">
                    <span :class="['flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-base font-black shadow-card', visual.accent]">
                        IQ
                    </span>
                </div>

                <span v-if="lesson.isLocked" class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full border border-heritage-line bg-white/90 text-heritage-red shadow-card">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 16 16">
                        <path d="M4.5 7V5.7a3.5 3.5 0 0 1 7 0V7" stroke="currentColor" stroke-linecap="round" stroke-width="1.8" />
                        <path d="M4 7h8v6H4z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.8" />
                    </svg>
                </span>
            </div>

            <div class="min-w-0">
                <div class="flex flex-wrap gap-2">
                    <AppBadge variant="gold">{{ difficultyLabel }}</AppBadge>
                    <span class="rounded-full border border-heritage-line/60 bg-heritage-panel px-3 py-1 text-xs font-black uppercase leading-snug text-heritage-muted">
                        {{ readingTime }}
                    </span>
                </div>
                <h3 class="mt-3 text-xl font-black leading-tight text-heritage-ink group-hover:text-heritage-red">{{ lesson.title }}</h3>
                <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ lesson.summary }}</p>
            </div>
        </div>

        <div class="mt-auto pt-5">
            <div v-if="lesson.isLocked" class="rounded-[1.2rem] border border-heritage-line/70 bg-heritage-panel p-4">
                <p class="text-sm font-black text-heritage-ink">Create a free account to unlock this lesson.</p>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <PrimaryButton :href="lesson.registerHref" class="w-full" size="sm">Unlock free</PrimaryButton>
                    <PrimaryButton :href="lesson.loginHref" class="w-full" size="sm" variant="soft">Log in</PrimaryButton>
                </div>
            </div>

            <div v-else class="flex flex-col gap-2 sm:flex-row">
                <PrimaryButton :href="lesson.href" class="w-full" size="sm">Read lesson</PrimaryButton>
                <PrimaryButton
                    v-if="lesson.related_quiz && !lesson.relatedQuizLocked"
                    :href="lesson.related_quiz.start_url"
                    class="w-full"
                    size="sm"
                    variant="gold"
                >
                    Take quiz
                </PrimaryButton>
                <PrimaryButton
                    v-else-if="lesson.relatedQuizLocked"
                    :href="lesson.relatedQuizRegisterHref"
                    class="w-full"
                    size="sm"
                    variant="soft"
                >
                    Unlock quiz
                </PrimaryButton>
            </div>
        </div>
    </article>
</template>
