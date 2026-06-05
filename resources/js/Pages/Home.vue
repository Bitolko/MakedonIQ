<script setup>
import PublicLayout from '../Components/PublicLayout.vue';
import CategoryCard from '../Components/CategoryCard.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import MacedoniaMap from '../Components/MacedoniaMap.vue';
import DemoPreviewSection from '../Components/DemoPreviewSection.vue';
import WelcomeBackSection from '../Components/WelcomeBackSection.vue';
import { categories, historyQuizzes } from '../data/makedoniq';
import { currentUser } from '../api/makedoniq';

const user = currentUser();
const isGuest = !user;

const steps = [
    { title: 'Choose a topic', text: 'Pick language, alphabet, history, geography, food, music, or traditions.', icon: '01' },
    { title: 'Answer questions', text: 'Practise with short bilingual prompts that feel playful and clear.', icon: '02' },
    { title: 'Earn points', text: 'Celebrate progress with points, streaks, badges, and encouraging feedback.', icon: '03' },
    { title: 'Track progress', text: 'See strengths by category and keep learning at your own pace.', icon: '04' },
];

const homeActions = [
    {
        title: 'Dashboard',
        detail: 'Open your learning overview.',
        href: '/dashboard',
        badge: 'Stats',
    },
    {
        title: 'Progress',
        detail: 'Review saved quiz results.',
        href: '/progress',
        badge: 'Review',
    },
    {
        title: 'Continue lessons',
        detail: 'Browse the Learn path.',
        href: '/learn',
        badge: 'Learn',
    },
    {
        title: 'Map Challenge',
        detail: 'Practise geography clues.',
        href: '/map-challenge',
        badge: 'Map',
    },
];
</script>

<template>
    <PublicLayout>
        <main>
            <section class="page-shell grid gap-12 py-12 md:grid-cols-[1.05fr_0.95fr] md:items-center md:py-20">
                <div>
                    <span class="eyebrow">Australia's Macedonian learning hub</span>
                    <h1 class="mt-6 max-w-3xl text-4xl font-black leading-tight text-heritage-ink sm:text-5xl lg:text-6xl">
                        {{ isGuest ? 'Learn Macedonian through fun quizzes' : 'Continue learning Macedonian' }}
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-heritage-muted">
                        {{ isGuest ? 'Try a few lessons and quizzes free. Create a free account to unlock the full learning path, save scores, and track progress.' : 'Explore lessons, practise with quizzes, and keep building your progress.' }}
                    </p>
                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <PrimaryButton :href="isGuest ? '/quizzes#free-demos' : '/dashboard'" variant="gold" size="lg">{{ isGuest ? 'Try demo quizzes' : 'Dashboard' }}</PrimaryButton>
                        <PrimaryButton :href="isGuest ? '/register' : '/learn'" size="lg">{{ isGuest ? 'Create free account' : 'Continue learning' }}</PrimaryButton>
                        <PrimaryButton v-if="!isGuest" href="/map-challenge" variant="white" size="lg">Map Challenge</PrimaryButton>
                    </div>
                    <div class="mt-8 grid max-w-xl grid-cols-3 gap-3">
                        <div class="metric-card">
                            <p class="text-2xl font-black text-heritage-red">6</p>
                            <p class="mt-1 text-xs font-bold text-heritage-muted">Categories</p>
                        </div>
                        <div class="metric-card">
                            <p class="text-2xl font-black text-heritage-gold-deep">50+</p>
                            <p class="mt-1 text-xs font-bold text-heritage-muted">Quiz prompts</p>
                        </div>
                        <div class="metric-card">
                            <p class="text-2xl font-black text-heritage-navy">EN/MK</p>
                            <p class="mt-1 text-xs font-bold text-heritage-muted">Bilingual</p>
                        </div>
                    </div>
                </div>

                <div class="heritage-pattern rounded-[2rem] p-4 shadow-soft">
                    <div v-if="isGuest" class="rounded-[1.5rem] bg-white p-5 shadow-card">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="label">Demo quiz preview</p>
                                <h2 class="mt-1 text-2xl font-black text-heritage-red">Добро утро</h2>
                            </div>
                            <AppBadge variant="gold">DEMO PREVIEW</AppBadge>
                        </div>
                        <div class="mt-6 rounded-2xl bg-heritage-panel p-5">
                            <p class="text-sm font-black text-heritage-muted">What does this phrase mean?</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <button class="rounded-2xl border-2 border-heritage-line bg-white p-4 text-left font-bold text-heritage-muted">Good night</button>
                                <button class="rounded-2xl border-2 border-heritage-gold bg-heritage-gold-faint p-4 text-left font-black text-heritage-gold-deep shadow-card">Good morning</button>
                                <button class="rounded-2xl border-2 border-heritage-line bg-white p-4 text-left font-bold text-heritage-muted">Thank you</button>
                                <button class="rounded-2xl border-2 border-heritage-line bg-white p-4 text-left font-bold text-heritage-muted">Goodbye</button>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-heritage-red-faint p-4">
                                <p class="label">Free demos</p>
                                <p class="mt-2 text-2xl font-black text-heritage-red">3 unlocked</p>
                            </div>
                            <div class="rounded-2xl bg-heritage-panel p-4">
                                <p class="label">Save progress</p>
                                <p class="mt-2 text-2xl font-black text-heritage-navy">After signup</p>
                            </div>
                        </div>
                        <PrimaryButton href="/quizzes/macedonian-language/basic-macedonian-greetings/start" class="mt-5 w-full" variant="soft">Try demo quiz</PrimaryButton>
                    </div>

                    <div v-else class="rounded-[1.5rem] bg-white p-5 shadow-card">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="label">Signed-in learning</p>
                                <h2 class="mt-1 text-2xl font-black text-heritage-red">Continue your Macedonian journey</h2>
                            </div>
                            <AppBadge variant="gold">WELCOME BACK</AppBadge>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <a
                                v-for="action in homeActions"
                                :key="action.title"
                                :href="action.href"
                                class="rounded-2xl border border-heritage-line bg-heritage-panel p-4 shadow-card transition hover:-translate-y-0.5 hover:border-heritage-gold hover:bg-white"
                            >
                                <span class="rounded-full bg-heritage-gold-faint px-3 py-1 text-xs font-black text-heritage-gold-deep">{{ action.badge }}</span>
                                <h3 class="mt-3 text-lg font-black text-heritage-ink">{{ action.title }}</h3>
                                <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">{{ action.detail }}</p>
                            </a>
                        </div>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton href="/dashboard" class="w-full" variant="soft">Go to dashboard</PrimaryButton>
                            <PrimaryButton href="/learn" class="w-full" variant="gold">Continue learning</PrimaryButton>
                        </div>
                    </div>
                </div>
            </section>

            <div v-if="isGuest" class="page-shell pb-6">
                <DemoPreviewSection />
            </div>

            <WelcomeBackSection v-else />

            <section class="bg-white py-16">
                <div class="page-shell">
                    <div class="mx-auto max-w-2xl text-center">
                        <AppBadge>Feature cards</AppBadge>
                        <h2 class="mt-4 text-3xl font-black text-heritage-ink md:text-4xl">Discover your roots</h2>
                        <p class="mt-3 text-heritage-muted">Interactive modules covering language, alphabet, history, geography, and culture.</p>
                    </div>
                    <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <CategoryCard v-for="category in categories.slice(0, 5)" :key="category.slug" :category="category" />
                    </div>
                </div>
            </section>

            <section class="page-shell py-16">
                <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <MacedoniaMap :x="52" :y="28" target-type="city" />
                    <div>
                        <AppBadge variant="gold">New geography mode</AppBadge>
                        <h2 class="mt-5 text-3xl font-black leading-tight text-heritage-ink md:text-4xl">
                            Try the Macedonia Map Challenge
                        </h2>
                        <p class="mt-4 text-lg leading-8 text-heritage-muted">
                            Guess the highlighted Macedonian city, lake, or landmark from a lightweight illustrated map. It uses the same secure quiz scoring when you submit.
                        </p>
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton href="/map-challenge">Open Map Challenge</PrimaryButton>
                            <PrimaryButton href="/learn/geography" variant="soft">Learn geography first</PrimaryButton>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-shell py-16">
                <div class="mb-8 text-center">
                    <AppBadge variant="red">How it works</AppBadge>
                    <h2 class="mt-4 text-3xl font-black text-heritage-ink md:text-4xl">Learn in small, confident steps</h2>
                </div>
                <div class="grid gap-6 md:grid-cols-4">
                    <article v-for="step in steps" :key="step.title" class="soft-card soft-card-hover p-6">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-heritage-red text-sm font-black text-white">
                            {{ step.icon }}
                        </div>
                        <h3 class="text-xl font-black text-heritage-ink">{{ step.title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-heritage-muted">{{ step.text }}</p>
                    </article>
                </div>
            </section>

            <section class="bg-heritage-navy py-16 text-white">
                <div class="page-shell grid gap-8 md:grid-cols-[1fr_1.2fr] md:items-center">
                    <div>
                        <AppBadge variant="gold">Community focused</AppBadge>
                        <h2 class="mt-5 text-3xl font-black md:text-4xl">Made for Macedonian families, students, and schools</h2>
                        <p class="mt-4 leading-7 text-white/75">
                            MakedonIQ is designed for learners who want a warm, modern way to reconnect with Macedonian language and heritage at home, at school, or in community groups.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                            <p class="text-3xl font-black text-heritage-gold">Home</p>
                            <p class="mt-2 text-sm text-white/75">Family-friendly practice</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                            <p class="text-3xl font-black text-heritage-gold">Class</p>
                            <p class="mt-2 text-sm text-white/75">Useful for schools</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                            <p class="text-3xl font-black text-heritage-gold">Group</p>
                            <p class="mt-2 text-sm text-white/75">Ready for community learning</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-shell py-16">
                <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <AppBadge variant="navy">Quiz preview</AppBadge>
                        <h2 class="mt-4 text-3xl font-black text-heritage-ink md:text-4xl">Bite-sized history quizzes</h2>
                        <p class="mt-2 text-heritage-muted">A quick look at the friendly, repeatable learning format.</p>
                    </div>
                    <PrimaryButton href="/quizzes/history" variant="soft" size="sm">View history quizzes</PrimaryButton>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <article v-for="quiz in historyQuizzes.slice(0, 3)" :key="quiz.title" class="soft-card soft-card-hover p-6">
                        <AppBadge>{{ quiz.difficulty }}</AppBadge>
                        <h3 class="mt-4 text-xl font-black text-heritage-ink">{{ quiz.title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-heritage-muted">{{ quiz.description }}</p>
                        <p class="mt-5 rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-muted">{{ quiz.questions }} questions / {{ quiz.time }}</p>
                    </article>
                </div>
            </section>

            <section class="page-shell pb-16">
                <div class="heritage-pattern rounded-[2rem] p-8 text-center text-white md:p-12">
                    <h2 class="text-3xl font-black md:text-4xl">Start learning Macedonian today</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-white/80">Pick a short quiz, learn a phrase, earn points, and build confidence one question at a time.</p>
                    <div class="mt-8 flex justify-center">
                        <PrimaryButton href="/quizzes" variant="gold" size="lg">Explore Quizzes</PrimaryButton>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
