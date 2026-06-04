<script setup>
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

defineProps({
    isGuest: {
        type: Boolean,
        default: true,
    },
});

const heroVisuals = [
    {
        label: 'Greetings',
        tag: 'Phrases',
        image: '/images/demo/demo-greetings.png',
        alt: 'Greetings visual',
    },
    {
        label: 'Alphabet',
        tag: 'Letters',
        image: '/images/demo/demo-alphabet.png',
        alt: 'Cyrillic alphabet visual',
    },
    {
        label: 'Map',
        tag: 'Places',
        image: '/images/demo/demo-map.png',
        alt: 'Macedonia map challenge visual',
    },
    {
        label: 'Songs',
        tag: 'Sound',
        image: '/images/demo/demo-sound.png',
        alt: 'Sound quiz visual',
    },
];
</script>

<template>
    <section class="relative overflow-hidden rounded-[2.5rem] border border-heritage-gold/45 bg-white p-5 shadow-soft md:p-8 lg:p-10">
        <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-heritage-gold-faint via-white to-heritage-red-faint/80" />
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.14) 1px, transparent 0); background-size: 30px 30px;" />
        <div class="relative grid gap-8 lg:grid-cols-[0.94fr_1.06fr] lg:items-center">
            <div class="min-w-0">
                <AppBadge variant="red">LEARN MACEDONIAN</AppBadge>
                <h1 class="mt-5 max-w-3xl text-4xl font-black leading-tight text-heritage-ink md:text-5xl lg:text-6xl">
                    Learn Macedonian step by step
                </h1>
                <p class="mt-5 max-w-2xl text-base font-bold leading-8 text-heritage-muted md:text-lg">
                    {{ isGuest ? 'Try a few lessons free, then create an account to unlock all lessons, quizzes, progress tracking, and saved results.' : 'Explore lessons, practise with quizzes, and keep building your Macedonian learning path.' }}
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <PrimaryButton href="#learn-categories" size="lg">
                        {{ isGuest ? 'Start learning' : 'Browse lessons' }}
                    </PrimaryButton>
                    <PrimaryButton href="/quizzes" variant="gold" size="lg">Take a quiz</PrimaryButton>
                    <PrimaryButton v-if="isGuest" href="/register" variant="soft" size="lg">Create free account</PrimaryButton>
                    <PrimaryButton v-else href="/progress" variant="soft" size="lg">View progress</PrimaryButton>
                    <PrimaryButton href="/map-challenge" variant="white" size="lg">Map Challenge</PrimaryButton>
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-heritage-line/60 bg-white/75 p-4 shadow-card">
                        <p class="text-xl font-black text-heritage-red">EN/MK</p>
                        <p class="mt-1 text-xs font-black uppercase text-heritage-muted">Bilingual lessons</p>
                    </div>
                    <div class="rounded-2xl border border-heritage-line/60 bg-white/75 p-4 shadow-card">
                        <p class="text-xl font-black text-heritage-gold-deep">Quiz</p>
                        <p class="mt-1 text-xs font-black uppercase text-heritage-muted">Practise after reading</p>
                    </div>
                    <div class="rounded-2xl border border-heritage-line/60 bg-white/75 p-4 shadow-card">
                        <p class="text-xl font-black text-heritage-navy">Map</p>
                        <p class="mt-1 text-xs font-black uppercase text-heritage-muted">Play with places</p>
                    </div>
                </div>
            </div>

            <div class="relative min-w-0">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-heritage-gold/25 blur-2xl" />
                <div class="absolute -bottom-8 -left-4 h-32 w-32 rounded-full bg-heritage-red/15 blur-2xl" />
                <div class="relative overflow-hidden rounded-[2rem] border border-heritage-gold/45 bg-heritage-navy p-4 text-white shadow-soft md:p-5">
                    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/10 via-transparent to-heritage-red/25" />
                    <div class="pointer-events-none absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.22) 1px, transparent 0); background-size: 26px 26px;" />

                    <div class="relative grid gap-4 sm:grid-cols-2">
                        <article
                            v-for="visual in heroVisuals"
                            :key="visual.label"
                            class="group overflow-hidden rounded-[1.5rem] border border-white/15 bg-white text-heritage-ink shadow-card transition hover:-translate-y-1 hover:shadow-soft"
                        >
                            <div class="relative h-36 overflow-hidden bg-heritage-panel sm:h-40 lg:h-44">
                                <div class="absolute inset-0 bg-linear-to-br from-white via-heritage-gold-faint to-heritage-red-faint/70" />
                                <img
                                    :src="visual.image"
                                    :alt="visual.alt"
                                    class="relative h-full w-full object-contain p-2 transition duration-300 group-hover:scale-[1.03]"
                                    loading="eager"
                                    decoding="async"
                                    width="900"
                                    height="900"
                                >
                            </div>
                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <span class="text-sm font-black text-heritage-ink">{{ visual.label }}</span>
                                <span class="rounded-full border border-heritage-gold/35 bg-heritage-gold-faint px-3 py-1 text-[0.68rem] font-black uppercase text-heritage-gold-deep">{{ visual.tag }}</span>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
