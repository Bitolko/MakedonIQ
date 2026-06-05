<script setup>
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

const actions = [
    {
        title: 'Continue learning',
        text: 'Pick up a lesson or choose a new topic.',
        href: '/learn',
        visual: 'lesson',
        cta: 'Continue',
        ctaClass: 'bg-heritage-red text-white group-hover:bg-heritage-red-dark',
    },
    {
        title: 'View progress',
        text: 'Review saved attempts and completed work.',
        href: '/progress',
        visual: 'progress',
        cta: 'Review',
        ctaClass: 'bg-heritage-navy text-white group-hover:bg-heritage-red',
    },
    {
        title: 'Map Challenge',
        text: 'Practise cities, lakes, and landmarks.',
        href: '/map-challenge',
        visual: 'map',
        cta: 'Open map',
        ctaClass: 'bg-heritage-gold text-heritage-navy group-hover:bg-heritage-gold-soft',
    },
    {
        title: 'Take a quiz',
        text: 'Test what you remember.',
        href: '/quizzes',
        visual: 'quiz',
        cta: 'Start quiz',
        ctaClass: 'bg-white text-heritage-red ring-1 ring-heritage-line group-hover:bg-heritage-red-faint',
    },
];
</script>

<template>
    <section class="page-shell pb-6">
        <div class="overflow-hidden rounded-3xl border border-heritage-gold/40 bg-white shadow-card">
            <div class="grid gap-0 lg:grid-cols-[0.96fr_1.04fr]">
                <div class="relative overflow-hidden bg-heritage-panel p-6 md:p-8">
                    <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.12) 1px, transparent 0); background-size: 26px 26px;" />
                    <div class="relative">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <AppBadge variant="gold">WELCOME BACK</AppBadge>
                            <PrimaryButton href="/dashboard" class="w-full sm:w-auto" variant="soft">Dashboard</PrimaryButton>
                        </div>
                        <h2 class="mt-5 max-w-xl text-3xl font-black leading-tight text-heritage-ink md:text-4xl">
                            Keep building your Macedonian path
                        </h2>
                        <p class="mt-3 max-w-xl leading-7 text-heritage-muted">
                            Choose your next step: read a lesson, practise with a quiz, explore the map, or review your progress.
                        </p>

                        <div class="mt-7 rounded-[1.5rem] border border-white/80 bg-white/85 p-4 shadow-card">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-heritage-red-faint p-4">
                                    <p class="text-xs font-black uppercase text-heritage-red">Learn</p>
                                    <p class="mt-2 text-lg font-black text-heritage-ink">Phrase</p>
                                </div>
                                <div class="rounded-2xl bg-heritage-gold-faint p-4">
                                    <p class="text-xs font-black uppercase text-heritage-gold-deep">Practise</p>
                                    <p class="mt-2 text-lg font-black text-heritage-ink">Quiz</p>
                                </div>
                                <div class="rounded-2xl bg-heritage-navy-soft p-4">
                                    <p class="text-xs font-black uppercase text-heritage-navy">Track</p>
                                    <p class="mt-2 text-lg font-black text-heritage-ink">Review</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-5 md:p-6 lg:grid-cols-[0.78fr_1.22fr] lg:p-8">
                    <div class="relative min-h-64 overflow-hidden rounded-[1.5rem] border border-heritage-line/50 bg-linear-to-br from-heritage-red-faint via-white to-heritage-gold-faint p-5 shadow-inner">
                        <div class="absolute right-5 top-5 flex gap-2">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-lg font-black text-heritage-red shadow-card">A</span>
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-lg font-black text-heritage-gold-deep shadow-card">B</span>
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-lg font-black text-heritage-navy shadow-card">V</span>
                        </div>

                        <div class="absolute left-5 top-8 rounded-2xl border border-heritage-red/20 bg-white px-4 py-3 shadow-card">
                            <p class="text-xs font-black uppercase text-heritage-muted">Phrase card</p>
                            <p class="mt-1 text-2xl font-black text-heritage-red">Здраво</p>
                        </div>

                        <div class="absolute bottom-5 left-5 right-5 rounded-2xl border border-white/80 bg-white/90 p-4 shadow-card">
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full bg-heritage-red" />
                                <span class="h-1 flex-1 rounded-full bg-heritage-line/70" />
                                <span class="h-3 w-3 rounded-full bg-heritage-gold" />
                                <span class="h-1 flex-1 rounded-full bg-heritage-line/70" />
                                <span class="h-3 w-3 rounded-full bg-heritage-navy" />
                            </div>
                            <div class="mt-3 flex justify-between text-xs font-black uppercase text-heritage-muted">
                                <span>Learn</span>
                                <span>Practise</span>
                                <span>Track</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <a
                            v-for="action in actions"
                            :key="action.title"
                            :href="action.href"
                            class="group flex min-h-40 flex-col rounded-[1.5rem] border border-heritage-line/55 bg-white p-4 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/30 hover:shadow-soft"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-heritage-panel shadow-inner">
                                    <div v-if="action.visual === 'lesson'" class="relative h-8 w-8">
                                        <span class="absolute left-0 top-1 h-7 w-6 rotate-[-8deg] rounded-lg bg-heritage-red-faint ring-1 ring-heritage-red/20" />
                                        <span class="absolute right-0 top-0 h-8 w-6 rounded-lg bg-white ring-1 ring-heritage-line" />
                                    </div>
                                    <div v-else-if="action.visual === 'progress'" class="grid w-8 gap-1.5">
                                        <span class="h-2 rounded-full bg-heritage-red" />
                                        <span class="h-2 w-5/6 rounded-full bg-heritage-gold" />
                                        <span class="h-2 w-2/3 rounded-full bg-heritage-navy" />
                                    </div>
                                    <div v-else-if="action.visual === 'map'" class="relative h-8 w-8">
                                        <span class="absolute inset-x-1 bottom-1 h-5 rounded-[50%] bg-sky-100" />
                                        <span class="absolute left-3 top-0 h-6 w-6 rounded-full bg-heritage-red ring-4 ring-white" />
                                        <span class="absolute left-[1.1rem] top-2 h-2 w-2 rounded-full bg-white" />
                                    </div>
                                    <div v-else class="grid h-8 w-8 gap-1.5">
                                        <span class="h-2 rounded-full bg-heritage-line" />
                                        <span class="h-2 rounded-full bg-heritage-gold" />
                                        <span class="h-2 rounded-full bg-heritage-red" />
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-heritage-ink transition group-hover:text-heritage-red">{{ action.title }}</h3>
                                    <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">{{ action.text }}</p>
                                </div>
                            </div>
                            <span :class="['mt-auto inline-flex w-full items-center justify-center rounded-2xl px-4 py-2.5 text-sm font-black shadow-card transition', action.ctaClass]">
                                {{ action.cta }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
