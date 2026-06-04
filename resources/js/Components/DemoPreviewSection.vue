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
                visual: 'greetings',
            },
            {
                title: 'Cyrillic Alphabet Basics',
                description: 'Preview letters, sounds, and first words.',
                practice: 'Learn your first Cyrillic letters.',
                href: '/quizzes/macedonian-alphabet/cyrillic-alphabet-basics/start',
                cta: 'Try demo',
                type: 'Alphabet',
                visual: 'alphabet',
            },
            {
                title: 'Macedonia Map Challenge',
                description: 'Guess cities, lakes, and landmarks.',
                practice: 'Explore geography through play.',
                href: '/map-challenge',
                cta: 'Open demo',
                type: 'Map Challenge',
                visual: 'map',
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

            <div class="mt-7 grid gap-5 lg:grid-cols-3">
                <article
                    v-for="item in items"
                    :key="item.title"
                    class="group flex min-h-[30rem] flex-col overflow-hidden rounded-[1.6rem] border border-heritage-line/70 bg-white p-4 shadow-card transition hover:-translate-y-1 hover:border-heritage-gold/70 hover:shadow-soft focus-within:-translate-y-1 focus-within:border-heritage-gold/70 focus-within:shadow-soft"
                >
                    <div class="relative min-h-48 overflow-hidden rounded-[1.25rem] border border-heritage-gold/25 bg-heritage-panel p-4 shadow-inner">
                        <div class="absolute inset-0 bg-linear-to-br from-white via-heritage-gold-faint to-heritage-red-faint" />
                        <div class="absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(20, 28, 54, 0.11) 1px, transparent 0); background-size: 24px 24px;" />

                        <div class="relative z-10 flex items-start justify-between gap-2">
                            <AppBadge variant="red">Demo</AppBadge>
                            <span class="rounded-full bg-heritage-navy px-3 py-1 text-[0.68rem] font-black uppercase text-white shadow-card">{{ item.type }}</span>
                        </div>

                        <div v-if="item.visual === 'greetings'" class="relative z-10 mt-6 grid gap-3">
                            <div class="max-w-[11rem] rounded-[1.2rem] rounded-bl-sm bg-white px-4 py-3 text-lg font-black text-heritage-red shadow-card">Здраво</div>
                            <div class="ml-auto max-w-[12rem] rounded-[1.2rem] rounded-br-sm bg-heritage-gold px-4 py-3 text-lg font-black text-heritage-navy shadow-card">Добро утро</div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-heritage-navy px-3 py-1 text-xs font-black text-white shadow-card">MK</span>
                                <span class="h-1 flex-1 rounded-full bg-heritage-gold/70" />
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-heritage-red shadow-card">EN</span>
                            </div>
                        </div>

                        <div v-else-if="item.visual === 'alphabet'" class="relative z-10 mt-5">
                            <div class="grid grid-cols-5 gap-2">
                                <span
                                    v-for="(letter, index) in ['А', 'Б', 'В', 'М', 'К']"
                                    :key="letter"
                                    :class="[
                                        'flex aspect-square items-center justify-center rounded-2xl text-2xl font-black shadow-card',
                                        index % 3 === 0 ? 'bg-white text-heritage-red' : (index % 3 === 1 ? 'bg-heritage-gold text-heritage-navy' : 'bg-heritage-red text-white')
                                    ]"
                                >
                                    {{ letter }}
                                </span>
                            </div>
                            <div class="mt-5 rounded-[1.25rem] bg-white/90 p-3 shadow-card">
                                <div class="h-2 w-24 rounded-full bg-heritage-gold" />
                                <div class="mt-3 h-2 w-32 rounded-full bg-heritage-red/35" />
                                <div class="mt-3 h-2 w-20 rounded-full bg-heritage-navy/20" />
                            </div>
                        </div>

                        <div v-else class="relative z-10 mt-4 min-h-32 rounded-[1.25rem] bg-white/85 p-4 shadow-card">
                            <div class="absolute left-5 top-8 h-10 w-16 rounded-[50%] bg-sky-200" />
                            <div class="absolute bottom-6 right-6 h-10 w-20 rounded-[50%] bg-sky-200" />
                            <div class="absolute bottom-5 left-5 h-0 w-0 border-x-[28px] border-b-[48px] border-x-transparent border-b-heritage-navy/20" />
                            <div class="absolute left-10 right-12 top-16 h-1 rotate-[-10deg] rounded-full bg-heritage-gold" />
                            <div class="absolute right-12 top-8 flex h-14 w-14 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white shadow-[0_4px_0_0_#760000] ring-4 ring-heritage-gold/35">
                                PIN
                            </div>
                            <div class="absolute bottom-7 left-16 h-4 w-4 rounded-full border-4 border-heritage-red bg-white" />
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-2 pt-5">
                        <h3 class="text-xl font-black leading-tight text-heritage-ink group-hover:text-heritage-red">{{ item.title }}</h3>
                        <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.description }}</p>
                        <div class="mt-4 rounded-2xl bg-heritage-panel p-3">
                            <p class="text-[0.68rem] font-black uppercase text-heritage-red">You will practise</p>
                            <p class="mt-1 text-sm font-black leading-6 text-heritage-ink">{{ item.practice }}</p>
                        </div>
                        <PrimaryButton :href="item.href" class="mt-auto w-full" size="sm" variant="red">{{ item.cta }}</PrimaryButton>
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
