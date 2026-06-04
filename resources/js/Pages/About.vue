<script setup>
import PublicLayout from '../Components/PublicLayout.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import { currentUser } from '../api/makedoniq';

const user = currentUser();
const isGuest = !user;

const missionPillars = [
    {
        title: 'Learn the language',
        detail: 'Short bilingual lessons for phrases, Cyrillic letters, vocabulary, and everyday words.',
        icon: 'MK',
        tone: 'bg-heritage-red text-white',
    },
    {
        title: 'Practise through play',
        detail: 'Quizzes, map challenges, picture clues, and sound quiz support help make learning active.',
        icon: 'QUIZ',
        tone: 'bg-heritage-gold text-heritage-navy',
    },
    {
        title: 'Keep culture connected',
        detail: 'Food, music, folklore, geography, history, and family traditions help learners connect with heritage.',
        icon: 'ROOT',
        tone: 'bg-heritage-navy text-white',
    },
];

const audienceCards = [
    {
        title: 'Families',
        detail: 'For parents and children learning together at home.',
        icon: 'HOME',
    },
    {
        title: 'Learners',
        detail: 'For beginners who want simple English and Macedonian explanations.',
        icon: 'EN/MK',
    },
    {
        title: 'Schools and community groups',
        detail: 'For teachers and organisers who need modern learning tools.',
        icon: 'CLASS',
    },
    {
        title: 'Diaspora',
        detail: 'For Macedonians in Australia and around the world who want to stay connected.',
        icon: 'WORLD',
    },
];

const differentiators = [
    'Bilingual EN/MK learning',
    'Short lessons connected to quizzes',
    'Map Challenge for geography',
    'Picture quizzes for visual learning',
    'Folklore and sound quiz support is being prepared',
    'Progress tracking after account signup',
    'Admin tools for future schools and teachers',
];

const trustItems = [
    'Lessons are written for MakedonIQ.',
    'Textbook content is not copied directly.',
    'Images and audio should be original, public domain, or properly licensed.',
    'Folklore lessons focus on respectful cultural learning.',
    'The platform avoids political claims and keeps content beginner-friendly.',
];

const heroVisuals = [
    {
        label: 'Greetings',
        tag: 'Language',
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
        tag: 'Culture',
        image: '/images/demo/demo-sound.png',
        alt: 'Sound quiz visual',
    },
];
</script>

<template>
    <PublicLayout>
        <main class="overflow-hidden">
            <section class="relative bg-heritage-bg py-10 md:py-14 lg:py-16">
                <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.12) 1px, transparent 0); background-size: 32px 32px;" />
                <div class="page-shell relative grid gap-10 lg:grid-cols-[0.98fr_1.02fr] lg:items-center">
                    <div class="min-w-0">
                        <AppBadge>About MakedonIQ</AppBadge>
                        <h1 class="mt-5 max-w-4xl text-4xl font-black leading-tight text-heritage-ink md:text-5xl lg:text-6xl">
                            Helping Macedonian families keep language, culture, and identity alive.
                        </h1>
                        <p class="mt-5 max-w-3xl text-base font-bold leading-8 text-heritage-muted md:text-lg">
                            MakedonIQ is a bilingual learning platform for Macedonian families in Australia and learners worldwide. It combines short lessons, quizzes, map challenges, picture quizzes, and folklore learning to make heritage education simple and fun.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <PrimaryButton href="/learn" size="lg">Start learning</PrimaryButton>
                            <PrimaryButton href="/quizzes" variant="gold" size="lg">Try demo quizzes</PrimaryButton>
                            <PrimaryButton href="/contact" variant="soft" size="lg">Contact us</PrimaryButton>
                        </div>
                    </div>

                    <div class="relative min-w-0">
                        <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-heritage-gold/25 blur-2xl" />
                        <div class="absolute -bottom-8 -left-5 h-32 w-32 rounded-full bg-heritage-red/15 blur-2xl" />
                        <div class="relative overflow-hidden rounded-[2rem] border border-heritage-gold/45 bg-heritage-navy p-4 text-white shadow-soft md:p-6">
                            <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/10 via-transparent to-heritage-red/25" />
                            <div class="pointer-events-none absolute inset-0 opacity-35" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.2) 1px, transparent 0); background-size: 26px 26px;" />

                            <div class="relative grid gap-4">
                                <div class="flex flex-col justify-between gap-3 rounded-[1.5rem] border border-white/15 bg-white p-5 text-heritage-ink shadow-card sm:flex-row sm:items-center">
                                    <div>
                                        <p class="label text-heritage-red">Heritage learning path</p>
                                        <p class="mt-2 text-2xl font-black leading-tight">Learn &rarr; Practice &rarr; Remember</p>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full bg-heritage-gold-faint px-4 py-2 text-sm font-black text-heritage-gold-deep shadow-card">EN/MK</span>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <article
                                        v-for="visual in heroVisuals"
                                        :key="visual.label"
                                        class="group overflow-hidden rounded-[1.5rem] border border-white/15 bg-white text-heritage-ink shadow-card transition hover:-translate-y-1 hover:shadow-soft"
                                    >
                                        <div class="relative h-40 overflow-hidden bg-heritage-panel sm:h-44 lg:h-48">
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
                </div>
            </section>

            <section class="page-shell py-14 md:py-16">
                <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                    <div>
                        <AppBadge variant="red">Our mission</AppBadge>
                        <h2 class="mt-4 text-3xl font-black leading-tight text-heritage-ink md:text-4xl">
                            To make Macedonian language and culture easier to learn, practise, and pass on.
                        </h2>
                        <p class="mt-4 text-base font-bold leading-8 text-heritage-muted">
                            MakedonIQ gives learners a warm path from reading and recognition to active practice, so heritage learning can happen at home, in class, and in community spaces.
                        </p>
                    </div>
                    <div class="grid gap-5 md:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                        <article
                            v-for="pillar in missionPillars"
                            :key="pillar.title"
                            class="group rounded-[1.5rem] border border-heritage-line/70 bg-white p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-gold/70 hover:shadow-soft"
                        >
                            <span :class="['flex h-12 w-12 items-center justify-center rounded-2xl text-xs font-black shadow-card', pillar.tone]">{{ pillar.icon }}</span>
                            <h3 class="mt-5 text-xl font-black text-heritage-ink group-hover:text-heritage-red">{{ pillar.title }}</h3>
                            <p class="mt-3 text-sm font-bold leading-6 text-heritage-muted">{{ pillar.detail }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="bg-white py-14 md:py-16">
                <div class="page-shell">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                        <div class="max-w-3xl">
                            <AppBadge variant="gold">Community</AppBadge>
                            <h2 class="mt-4 text-3xl font-black text-heritage-ink md:text-4xl">Built for the Macedonian community</h2>
                            <p class="mt-3 leading-7 text-heritage-muted">Simple tools for families, learners, schools, and diaspora communities who want to keep Macedonian learning close.</p>
                        </div>
                    </div>
                    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <article
                            v-for="audience in audienceCards"
                            :key="audience.title"
                            class="rounded-[1.5rem] border border-heritage-line/70 bg-heritage-panel p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/25 hover:bg-white hover:shadow-soft"
                        >
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black uppercase text-heritage-red shadow-card">{{ audience.icon }}</span>
                            <h3 class="mt-5 text-xl font-black text-heritage-ink">{{ audience.title }}</h3>
                            <p class="mt-3 text-sm font-bold leading-6 text-heritage-muted">{{ audience.detail }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="page-shell py-14 md:py-16">
                <div class="rounded-[2rem] border border-heritage-gold/45 bg-white p-5 shadow-soft md:p-8">
                    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                        <div>
                            <AppBadge variant="navy">Different by design</AppBadge>
                            <h2 class="mt-4 text-3xl font-black leading-tight text-heritage-ink md:text-4xl">Why MakedonIQ is different</h2>
                            <p class="mt-3 leading-7 text-heritage-muted">
                                It connects heritage content with real practice, instead of leaving learners with long pages of text and nowhere to use what they learned.
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="item in differentiators"
                                :key="item"
                                class="flex items-start gap-3 rounded-2xl border border-heritage-line/60 bg-heritage-panel px-4 py-4"
                            >
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-heritage-red text-white shadow-[0_2px_0_0_#760000]">
                                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 16 16">
                                        <path d="M3.5 8.3 6.6 11 12.5 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" />
                                    </svg>
                                </span>
                                <span class="text-sm font-black leading-6 text-heritage-ink">{{ item }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-heritage-navy py-14 text-white md:py-16">
                <div class="page-shell grid gap-8 lg:grid-cols-[1fr_0.95fr] lg:items-center">
                    <div>
                        <AppBadge variant="gold">Made for heritage learning</AppBadge>
                        <h2 class="mt-5 text-3xl font-black leading-tight md:text-4xl">A warmer way to practise what children hear at home.</h2>
                        <p class="mt-4 max-w-3xl leading-8 text-white/80">
                            MakedonIQ was created to make Macedonian learning more accessible, especially for families living outside Macedonia. Many children grow up hearing Macedonian at home but need a simple, fun way to practise reading, words, places, songs, and traditions.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <article class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                            <p class="text-sm font-black uppercase text-heritage-gold">Family learning</p>
                            <h3 class="mt-3 text-2xl font-black">Read, say, remember</h3>
                            <p class="mt-3 text-sm leading-6 text-white/75">Short prompts make it easier to practise together without turning heritage learning into homework.</p>
                        </article>
                        <article class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                            <p class="text-sm font-black uppercase text-heritage-gold">Community learning</p>
                            <h3 class="mt-3 text-2xl font-black">Classroom ready direction</h3>
                            <p class="mt-3 text-sm leading-6 text-white/75">The platform is shaped for future use by Macedonian schools, teachers, and organisers.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="page-shell py-14 md:py-16">
                <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                    <div>
                        <AppBadge variant="red">Content trust</AppBadge>
                        <h2 class="mt-4 text-3xl font-black text-heritage-ink md:text-4xl">Original, respectful learning content</h2>
                        <p class="mt-3 leading-7 text-heritage-muted">
                            MakedonIQ treats heritage learning with care, especially around culture, history, songs, and images.
                        </p>
                    </div>
                    <div class="grid gap-3">
                        <div
                            v-for="item in trustItems"
                            :key="item"
                            class="flex gap-3 rounded-2xl border border-heritage-gold/35 bg-white px-4 py-4 shadow-card"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-heritage-gold text-heritage-navy">
                                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 16 16">
                                    <path d="M3.5 8.3 6.6 11 12.5 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" />
                                </svg>
                            </span>
                            <p class="text-sm font-bold leading-6 text-heritage-muted">{{ item }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-shell pb-16">
                <div class="relative overflow-hidden rounded-[2rem] bg-heritage-red p-6 text-white shadow-soft md:p-10">
                    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/10 via-transparent to-heritage-navy/30" />
                    <div class="relative grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <AppBadge variant="gold">{{ isGuest ? 'Start today' : 'Keep going' }}</AppBadge>
                            <h2 class="mt-4 text-3xl font-black leading-tight md:text-4xl">
                                {{ isGuest ? 'Start learning Macedonian today' : 'Continue your learning journey' }}
                            </h2>
                            <p class="mt-3 max-w-3xl leading-7 text-white/85">
                                {{ isGuest ? 'Try a few free demos, then create a free account to unlock more lessons, quizzes, and saved progress.' : 'Browse lessons, practise with quizzes, and review your saved progress when you are ready.' }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                            <template v-if="isGuest">
                                <PrimaryButton href="/quizzes" variant="gold" size="lg">Try demo quizzes</PrimaryButton>
                                <PrimaryButton href="/register" variant="white" size="lg">Create free account</PrimaryButton>
                            </template>
                            <template v-else>
                                <PrimaryButton href="/learn" variant="gold" size="lg">Browse lessons</PrimaryButton>
                                <PrimaryButton href="/quizzes" variant="white" size="lg">Take a quiz</PrimaryButton>
                                <PrimaryButton href="/progress" variant="soft" size="lg">View progress</PrimaryButton>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
