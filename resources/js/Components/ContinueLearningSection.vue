<script setup>
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

const actions = [
    {
        title: 'Browse all lessons',
        description: 'Explore all learning paths and choose your next topic.',
        href: '/learn',
        cta: 'Browse lessons',
        visual: 'lessons',
        tone: 'red',
    },
    {
        title: 'Take a quiz',
        description: 'Practise what you remember with category quizzes.',
        href: '/quizzes',
        cta: 'Start quiz',
        visual: 'quiz',
        tone: 'gold',
    },
    {
        title: 'View progress',
        description: 'Review saved attempts, scores, and learning history.',
        href: '/progress',
        cta: 'View progress',
        visual: 'progress',
        tone: 'navy',
    },
    {
        title: 'Try Map Challenge',
        description: 'Guess Macedonian cities, lakes, and landmarks from map clues.',
        href: '/map-challenge',
        cta: 'Open map',
        visual: 'map',
        tone: 'red',
    },
];

const cardToneClasses = {
    red: {
        panel: 'from-heritage-red-faint via-white to-heritage-gold-faint',
        cta: 'bg-heritage-red text-white group-hover:bg-heritage-red-dark',
    },
    gold: {
        panel: 'from-heritage-gold-faint via-white to-heritage-panel',
        cta: 'bg-heritage-gold text-heritage-navy group-hover:bg-heritage-gold-soft',
    },
    navy: {
        panel: 'from-heritage-navy-soft via-white to-heritage-panel',
        cta: 'bg-heritage-navy text-white group-hover:bg-heritage-red',
    },
};
</script>

<template>
    <section class="mt-10 rounded-[2rem] border border-heritage-gold/40 bg-white p-5 shadow-card md:p-8">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
            <div class="max-w-3xl">
                <AppBadge variant="gold">CONTINUE</AppBadge>
                <h2 class="mt-3 text-3xl font-black text-heritage-ink md:text-4xl">Continue your learning journey</h2>
                <p class="mt-3 max-w-2xl leading-7 text-heritage-muted">
                    Choose your next step: read a lesson, practise with quizzes, review progress, or explore the map challenge.
                </p>
            </div>
            <PrimaryButton href="/dashboard" class="w-full sm:w-auto" variant="soft">Open dashboard</PrimaryButton>
        </div>

        <div class="mt-7 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <a
                v-for="item in actions"
                :key="item.title"
                :href="item.href"
                class="group flex min-h-[23rem] flex-col overflow-hidden rounded-3xl border border-heritage-line/55 bg-white p-4 shadow-card transition duration-200 hover:-translate-y-1 hover:border-heritage-red/30 hover:shadow-soft"
            >
                <div :class="['relative h-40 overflow-hidden rounded-[1.35rem] border border-white/80 bg-linear-to-br p-4 shadow-inner', cardToneClasses[item.tone].panel]">
                    <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.12) 1px, transparent 0); background-size: 22px 22px;" />

                    <div v-if="item.visual === 'lessons'" class="relative h-full">
                        <div class="absolute left-1 top-3 h-24 w-24 rotate-[-7deg] rounded-2xl border border-heritage-line/60 bg-white p-3 shadow-card">
                            <div class="h-3 w-9 rounded-full bg-heritage-red-faint" />
                            <div class="mt-3 h-2 w-14 rounded-full bg-heritage-line/70" />
                            <div class="mt-2 h-2 w-10 rounded-full bg-heritage-line/50" />
                            <div class="absolute bottom-3 right-3 flex h-8 w-8 items-center justify-center rounded-xl bg-heritage-red text-sm font-black text-white">B</div>
                        </div>
                        <div class="absolute left-14 top-8 h-24 w-28 rotate-[5deg] rounded-2xl border border-heritage-gold/50 bg-white p-3 shadow-card">
                            <div class="h-3 w-12 rounded-full bg-heritage-gold" />
                            <div class="mt-3 h-2 w-16 rounded-full bg-heritage-line/60" />
                            <div class="mt-2 h-2 w-12 rounded-full bg-heritage-line/45" />
                            <div class="absolute bottom-3 right-3 flex h-8 w-8 items-center justify-center rounded-xl bg-heritage-gold text-sm font-black text-heritage-navy">P</div>
                        </div>
                        <div class="absolute bottom-2 right-1 rounded-2xl border border-heritage-red/20 bg-white px-4 py-3 text-lg font-black text-heritage-red shadow-card">Здраво</div>
                    </div>

                    <div v-else-if="item.visual === 'quiz'" class="relative grid h-full grid-cols-[1fr_auto] items-center gap-3">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 rounded-2xl border border-heritage-line/60 bg-white px-3 py-2 shadow-card">
                                <span class="h-4 w-4 rounded-full border-4 border-heritage-red" />
                                <span class="h-2 w-20 rounded-full bg-heritage-line/70" />
                            </div>
                            <div class="flex items-center gap-2 rounded-2xl border border-heritage-gold/60 bg-white px-3 py-2 shadow-card">
                                <span class="h-4 w-4 rounded-full bg-heritage-gold ring-4 ring-heritage-gold-faint" />
                                <span class="h-2 w-16 rounded-full bg-heritage-line/60" />
                            </div>
                            <div class="flex items-center gap-2 rounded-2xl border border-heritage-line/60 bg-white px-3 py-2 shadow-card">
                                <span class="h-4 w-4 rounded-full border-4 border-heritage-navy/60" />
                                <span class="h-2 w-14 rounded-full bg-heritage-line/50" />
                            </div>
                        </div>
                        <div class="flex h-24 w-20 flex-col items-center justify-center rounded-2xl bg-heritage-navy text-white shadow-card">
                            <span class="text-xs font-black uppercase text-heritage-gold">Quiz</span>
                            <span class="mt-2 text-4xl font-black leading-none">?</span>
                        </div>
                    </div>

                    <div v-else-if="item.visual === 'progress'" class="relative grid h-full grid-cols-[auto_1fr] items-center gap-4">
                        <div class="grid gap-3">
                            <div class="h-16 w-16 rounded-full bg-[conic-gradient(#a40000_0_62%,#fff1ef_62%_100%)] p-2 shadow-card">
                                <div class="h-full w-full rounded-full bg-white" />
                            </div>
                            <div class="h-12 w-12 rounded-full bg-[conic-gradient(#f4c430_0_48%,#fff8d7_48%_100%)] p-2 shadow-card">
                                <div class="h-full w-full rounded-full bg-white" />
                            </div>
                        </div>
                        <div class="rounded-2xl border border-heritage-line/60 bg-white p-4 shadow-card">
                            <div class="h-3 w-24 rounded-full bg-heritage-navy-soft" />
                            <div class="mt-4 space-y-2">
                                <div class="h-2 w-full rounded-full bg-heritage-panel">
                                    <div class="h-2 w-2/3 rounded-full bg-heritage-red" />
                                </div>
                                <div class="h-2 w-full rounded-full bg-heritage-panel">
                                    <div class="h-2 w-1/2 rounded-full bg-heritage-gold" />
                                </div>
                                <div class="h-2 w-full rounded-full bg-heritage-panel">
                                    <div class="h-2 w-3/5 rounded-full bg-heritage-navy" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="relative h-full">
                        <div class="absolute inset-2 rounded-[1.25rem] border border-heritage-line/60 bg-white shadow-card">
                            <div class="absolute left-5 top-5 h-14 w-24 rounded-full bg-sky-100" />
                            <div class="absolute bottom-5 right-5 h-12 w-20 rounded-full bg-heritage-gold-faint" />
                            <div class="absolute left-8 top-10 h-16 w-20 rounded-[55%_45%_55%_45%] bg-heritage-panel" />
                            <div class="absolute right-9 top-6 h-16 w-14 rounded-[45%_55%_45%_55%] bg-heritage-red-faint" />
                            <div class="absolute left-8 top-9 h-2 w-24 rotate-12 rounded-full bg-heritage-navy/20" />
                            <div class="absolute left-16 top-16 h-2 w-20 -rotate-12 rounded-full bg-heritage-navy/20" />
                        </div>
                        <div class="absolute left-1/2 top-7 flex h-11 w-11 -translate-x-1/2 items-center justify-center rounded-full bg-heritage-red text-white shadow-card ring-4 ring-white">
                            <span class="h-3 w-3 rounded-full bg-white" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-1 flex-col px-1 pt-5">
                    <h3 class="text-xl font-black text-heritage-ink transition group-hover:text-heritage-red">{{ item.title }}</h3>
                    <p class="mt-2 min-h-16 text-sm font-bold leading-6 text-heritage-muted">{{ item.description }}</p>
                    <span :class="['mt-auto inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-black shadow-card transition', cardToneClasses[item.tone].cta]">
                        {{ item.cta }}
                    </span>
                </div>
            </a>
        </div>
    </section>
</template>
