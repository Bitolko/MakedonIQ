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
        eyebrow: 'Learn',
        chipClass: 'bg-heritage-red-faint',
        ctaClass: 'bg-heritage-red text-white group-hover:bg-heritage-red-dark',
    },
    {
        title: 'Take a quiz',
        text: 'Practise what you remember.',
        href: '/quizzes',
        visual: 'quiz',
        cta: 'Start quiz',
        eyebrow: 'Practise',
        chipClass: 'bg-heritage-gold-faint',
        ctaClass: 'bg-heritage-gold text-heritage-navy group-hover:bg-heritage-gold-soft',
    },
    {
        title: 'View progress',
        text: 'Review saved attempts and learning history.',
        href: '/progress',
        visual: 'progress',
        cta: 'Review',
        eyebrow: 'Track',
        chipClass: 'bg-heritage-navy-soft',
        ctaClass: 'bg-heritage-navy text-white group-hover:bg-heritage-red',
    },
    {
        title: 'Map Challenge',
        text: 'Practise Macedonian cities, lakes, and landmarks.',
        href: '/map-challenge',
        visual: 'map',
        cta: 'Open map',
        eyebrow: 'Explore',
        chipClass: 'bg-white',
        ctaClass: 'bg-heritage-gold text-heritage-navy group-hover:bg-heritage-gold-soft',
    },
];
</script>

<template>
    <section class="page-shell pb-6">
        <div class="relative overflow-hidden rounded-3xl border border-heritage-gold/40 bg-heritage-panel p-5 shadow-soft md:p-7 lg:p-8">
            <div
                class="pointer-events-none absolute inset-0 opacity-60"
                style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.10) 1px, transparent 0); background-size: 26px 26px;"
            />

            <div class="relative">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <AppBadge variant="gold">WELCOME BACK</AppBadge>
                        <h2 class="mt-5 text-3xl font-black leading-tight text-heritage-ink md:text-4xl">
                            Keep building your Macedonian path
                        </h2>
                        <p class="mt-3 max-w-2xl leading-7 text-heritage-muted">
                            Choose your next step: read a lesson, practise with a quiz, explore the map, or review your progress.
                        </p>
                    </div>

                    <PrimaryButton href="/dashboard" class="w-full shrink-0 sm:w-auto" variant="soft">
                        Dashboard
                    </PrimaryButton>
                </div>

                <div class="mt-7 grid gap-4 md:grid-cols-2">
                    <a
                        v-for="action in actions"
                        :key="action.title"
                        :href="action.href"
                        class="group flex min-h-56 flex-col rounded-3xl border border-heritage-gold/25 bg-white p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/30 hover:shadow-soft md:p-6"
                    >
                        <div class="flex items-start gap-4">
                            <div :class="['flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-white/80 shadow-inner', action.chipClass]">
                                <div v-if="action.visual === 'lesson'" class="relative h-10 w-10">
                                    <span class="absolute left-0 top-2 h-8 w-7 rotate-[-8deg] rounded-lg bg-heritage-red-faint ring-1 ring-heritage-red/20" />
                                    <span class="absolute right-0 top-0 h-10 w-7 rounded-lg bg-white ring-1 ring-heritage-line" />
                                    <span class="absolute right-2 top-3 h-1.5 w-3 rounded-full bg-heritage-gold" />
                                    <span class="absolute right-2 top-5 h-1.5 w-4 rounded-full bg-heritage-red" />
                                </div>

                                <div v-else-if="action.visual === 'quiz'" class="relative h-10 w-10">
                                    <span class="absolute inset-0 rounded-2xl bg-white ring-1 ring-heritage-line" />
                                    <span class="absolute left-3 top-2 h-3 w-3 rounded-full bg-heritage-gold" />
                                    <span class="absolute right-2 top-2 h-3 w-5 rounded-full bg-heritage-red" />
                                    <span class="absolute bottom-2 left-2 right-2 h-2 rounded-full bg-heritage-navy" />
                                </div>

                                <div v-else-if="action.visual === 'progress'" class="grid w-10 gap-2">
                                    <span class="h-2.5 rounded-full bg-heritage-red" />
                                    <span class="h-2.5 w-5/6 rounded-full bg-heritage-gold" />
                                    <span class="h-2.5 w-2/3 rounded-full bg-heritage-navy" />
                                </div>

                                <div v-else class="relative h-10 w-10">
                                    <span class="absolute inset-x-1 bottom-1 h-6 rounded-[50%] bg-sky-100" />
                                    <span class="absolute left-3 top-0 h-7 w-7 rounded-full bg-heritage-red ring-4 ring-white" />
                                    <span class="absolute left-[1.18rem] top-2.5 h-2.5 w-2.5 rounded-full bg-white" />
                                </div>
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase text-heritage-muted">{{ action.eyebrow }}</p>
                                <h3 class="mt-1 text-xl font-black leading-snug text-heritage-ink transition group-hover:text-heritage-red">
                                    {{ action.title }}
                                </h3>
                                <p class="mt-2 max-w-xl text-sm font-bold leading-6 text-heritage-muted">
                                    {{ action.text }}
                                </p>
                            </div>
                        </div>

                        <span
                            :class="[
                                'mt-auto inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-black shadow-card transition sm:w-fit sm:min-w-[9rem]',
                                action.ctaClass,
                            ]"
                        >
                            {{ action.cta }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
