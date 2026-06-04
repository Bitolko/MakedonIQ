<script setup>
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

const props = defineProps({
    items: {
        type: Array,
        default: () => [
            {
                title: 'Basic Macedonian Greetings',
                description: 'Start with friendly everyday phrases.',
                practice: 'Practise greetings, meanings, and first phrases.',
                href: '/quizzes/macedonian-language/basic-macedonian-greetings/start',
                cta: 'Try demo',
                type: 'Lesson + Quiz',
                badge: 'Demo',
                visual: 'greetings',
                image: '/images/demo/demo-greetings.png',
                imageAlt: 'Greetings visual',
            },
            {
                title: 'Cyrillic Alphabet Basics',
                description: 'Learn your first Macedonian letters.',
                practice: 'Preview letters, sounds, and first words.',
                href: '/quizzes/macedonian-alphabet/cyrillic-alphabet-basics/start',
                cta: 'Try demo',
                type: 'Alphabet',
                badge: 'Demo',
                visual: 'alphabet',
                image: '/images/demo/demo-alphabet.png',
                imageAlt: 'Cyrillic alphabet visual',
            },
            {
                title: 'Macedonia Map Challenge',
                description: 'Guess cities, lakes, and landmarks.',
                practice: 'Explore geography through play.',
                href: '/map-challenge',
                cta: 'Open demo',
                type: 'Map Challenge',
                badge: 'Demo',
                visual: 'map',
                image: '/images/demo/demo-map.png',
                imageAlt: 'Macedonia map challenge visual',
            },
            {
                title: 'Folklore Sound Quiz',
                description: 'Guess songs from audio clues.',
                practice: 'Preview listening with folklore song clues.',
                href: '/quizzes/folklore-songs/guess-the-macedonian-folk-song/start',
                cta: 'Preview',
                type: 'Sound Quiz',
                badge: 'Locked preview',
                isLocked: true,
                visual: 'sound',
                image: '/images/demo/demo-sound.png',
                imageAlt: 'Sound quiz visual',
            },
        ],
    },
    className: {
        type: String,
        default: '',
    },
});

const benefits = [
    'Unlock all lessons',
    'Unlock all quizzes',
    'Save quiz scores',
    'Track progress',
    'Continue learning anytime',
];
</script>

<template>
    <section :class="['relative overflow-hidden rounded-[2rem] border border-heritage-gold/45 bg-white p-5 shadow-soft md:p-8', props.className]">
        <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-heritage-gold-faint via-white to-heritage-red-faint/70" />
        <div class="pointer-events-none absolute inset-0 opacity-45" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.13) 1px, transparent 0); background-size: 30px 30px;" />
        <div class="relative">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <AppBadge variant="gold">TRY THESE DEMOS</AppBadge>
                    <h2 class="mt-4 text-3xl font-black leading-tight text-heritage-ink md:text-4xl">Free previews to start with</h2>
                    <p class="mt-3 text-base font-bold leading-7 text-heritage-muted md:text-lg">
                        Try a few beginner-friendly lessons and quizzes for free before creating your account.
                    </p>
                </div>
                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <PrimaryButton href="/register" class="w-full sm:w-auto" variant="gold">Create free account</PrimaryButton>
                    <PrimaryButton href="/login" class="w-full sm:w-auto" variant="soft">Log in</PrimaryButton>
                </div>
            </div>

            <div class="mt-7 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="item in items"
                    :key="item.title"
                    class="group flex min-h-[28rem] flex-col overflow-hidden rounded-[1.6rem] border border-heritage-line/70 bg-white p-4 shadow-card transition hover:-translate-y-1 hover:border-heritage-gold/70 hover:shadow-soft focus-within:-translate-y-1 focus-within:border-heritage-gold/70 focus-within:shadow-soft"
                >
                    <div class="relative flex h-60 flex-col overflow-hidden rounded-[1.25rem] border border-heritage-gold/25 bg-heritage-panel p-3 shadow-inner sm:h-64 xl:h-56">
                        <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white via-heritage-gold-faint to-heritage-red-faint" />
                        <div class="relative z-10 flex h-8 shrink-0 items-center justify-between gap-2">
                            <AppBadge :variant="item.isLocked ? 'gold' : 'red'" class="shrink-0 whitespace-nowrap px-2.5 py-1 text-[0.62rem] leading-none">{{ item.badge || 'Demo' }}</AppBadge>
                            <span class="shrink-0 whitespace-nowrap rounded-full bg-heritage-navy px-2.5 py-1 text-[0.62rem] font-black uppercase leading-none text-white shadow-card">{{ item.type }}</span>
                        </div>

                        <div class="relative z-10 mt-3 min-h-0 flex-1 overflow-hidden rounded-[1rem] border border-white/80 bg-white/90 shadow-card">
                            <div class="absolute inset-0 bg-heritage-panel" />
                            <img
                                :src="item.image"
                                :alt="item.imageAlt || `${item.title} visual`"
                                class="relative h-full w-full object-contain p-2 transition duration-300 group-hover:scale-[1.03]"
                                loading="lazy"
                                decoding="async"
                                width="900"
                                height="900"
                            >
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-2 pt-5">
                        <h3 class="text-xl font-black leading-tight text-heritage-ink group-hover:text-heritage-red">{{ item.title }}</h3>
                        <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.description }}</p>
                        <div class="mt-4 rounded-2xl bg-heritage-panel p-3">
                            <p class="text-[0.68rem] font-black uppercase text-heritage-red">You will practise</p>
                            <p class="mt-1 text-sm font-black leading-6 text-heritage-ink">{{ item.practice }}</p>
                        </div>
                        <PrimaryButton :href="item.href" class="mt-auto w-full" size="sm" :variant="item.isLocked ? 'gold' : 'red'">{{ item.cta }}</PrimaryButton>
                    </div>
                </article>
            </div>

            <aside class="mt-6 grid gap-5 rounded-[1.5rem] border border-heritage-gold/45 bg-white/90 p-5 shadow-card lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <h3 class="text-2xl font-black text-heritage-ink">Create a free account to unlock more</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div v-for="benefit in benefits" :key="benefit" class="flex items-start gap-2 rounded-2xl bg-heritage-panel px-3 py-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-heritage-red text-white shadow-[0_2px_0_0_#760000]">
                                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 16 16">
                                    <path d="M3.5 8.3 6.6 11 12.5 5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" />
                                </svg>
                            </span>
                            <span class="text-sm font-black leading-5 text-heritage-ink">{{ benefit }}</span>
                        </div>
                    </div>
                </div>
                <PrimaryButton href="/register" class="w-full lg:w-auto" variant="gold">Create free account</PrimaryButton>
            </aside>
        </div>
    </section>
</template>
