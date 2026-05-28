<script setup>
import PublicLayout from '../Components/PublicLayout.vue';
import CategoryCard from '../Components/CategoryCard.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import ProgressBar from '../Components/ProgressBar.vue';
import { categories, historyQuizzes } from '../data/makedoniq';

const steps = [
    { title: 'Choose a topic', text: 'Pick language, alphabet, history, geography, food, music, or traditions.', icon: '1' },
    { title: 'Answer questions', text: 'Learn through short bilingual prompts made for families and classrooms.', icon: '2' },
    { title: 'Earn points', text: 'Celebrate progress with badges, streaks, and friendly score feedback.', icon: '3' },
    { title: 'Track progress', text: 'See strengths by category and keep learning at your own pace.', icon: '4' },
];
</script>

<template>
    <PublicLayout>
        <main>
            <section class="page-shell grid gap-12 py-12 md:grid-cols-2 md:items-center md:py-20">
                <div>
                    <span class="eyebrow">Australia's Macedonian learning hub</span>
                    <h1 class="mt-6 max-w-2xl text-4xl font-black leading-tight text-heritage-ink sm:text-5xl lg:text-6xl">
                        Learn Macedonian through fun quizzes
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-heritage-muted">
                        A bilingual learning platform for Macedonian families in Australia.
                    </p>
                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <PrimaryButton href="/register">Start Learning <span aria-hidden="true">▶</span></PrimaryButton>
                        <PrimaryButton href="/quizzes" variant="gold">Explore Quizzes <span aria-hidden="true">↗</span></PrimaryButton>
                    </div>
                </div>

                <div class="heritage-pattern rounded-[2rem] p-4 shadow-soft">
                    <div class="rounded-[1.5rem] bg-white p-5 shadow-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="label">Today's quiz</p>
                                <h2 class="mt-1 text-2xl font-black text-heritage-red">Добро утро</h2>
                            </div>
                            <AppBadge>+15 points</AppBadge>
                        </div>
                        <div class="mt-6 rounded-2xl bg-heritage-panel p-5">
                            <p class="text-sm font-bold text-heritage-muted">What does this phrase mean?</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <button class="rounded-2xl border-2 border-heritage-line bg-white p-4 text-left font-bold text-heritage-muted">Good night</button>
                                <button class="rounded-2xl border-2 border-heritage-gold bg-heritage-gold-soft p-4 text-left font-black text-heritage-gold-deep">Good morning</button>
                                <button class="rounded-2xl border-2 border-heritage-line bg-white p-4 text-left font-bold text-heritage-muted">Thank you</button>
                                <button class="rounded-2xl border-2 border-heritage-line bg-white p-4 text-left font-bold text-heritage-muted">Goodbye</button>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-heritage-red-soft p-4">
                                <p class="label">Daily streak</p>
                                <p class="mt-2 text-2xl font-black text-heritage-red">5 days</p>
                            </div>
                            <div class="rounded-2xl bg-heritage-panel p-4">
                                <ProgressBar :value="62" label="History progress" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-16">
                <div class="page-shell">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-black text-heritage-ink">Discover your roots</h2>
                        <p class="mt-3 text-heritage-muted">Interactive modules covering language, alphabet, history, geography, and culture.</p>
                    </div>
                    <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <CategoryCard v-for="category in categories.slice(0, 5)" :key="category.slug" :category="category" />
                    </div>
                </div>
            </section>

            <section class="page-shell py-16">
                <div class="grid gap-6 md:grid-cols-4">
                    <article v-for="step in steps" :key="step.title" class="soft-card p-6">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-heritage-red text-xl font-black text-white">
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
                        <h2 class="mt-5 text-3xl font-black">Made for Macedonian families, students, and schools</h2>
                        <p class="mt-4 leading-7 text-white/75">
                            MakedonIQ is designed for learners who want a warm, modern way to reconnect with Macedonian language and heritage at home, at school, or in community groups.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-white/10 p-5">
                            <p class="text-3xl font-black text-heritage-gold">6</p>
                            <p class="mt-2 text-sm text-white/75">Learning categories</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-5">
                            <p class="text-3xl font-black text-heritage-gold">50+</p>
                            <p class="mt-2 text-sm text-white/75">Sample quiz prompts</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-5">
                            <p class="text-3xl font-black text-heritage-gold">EN/MK</p>
                            <p class="mt-2 text-sm text-white/75">Bilingual learning flow</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-shell py-16">
                <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <h2 class="text-3xl font-black text-heritage-ink">Quiz preview</h2>
                        <p class="mt-2 text-heritage-muted">A quick look at the friendly, bite-sized learning format.</p>
                    </div>
                    <a href="/quizzes/history" class="font-black text-heritage-red">View history quizzes</a>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <article v-for="quiz in historyQuizzes.slice(0, 3)" :key="quiz.title" class="soft-card p-6">
                        <AppBadge>{{ quiz.difficulty }}</AppBadge>
                        <h3 class="mt-4 text-xl font-black text-heritage-ink">{{ quiz.title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-heritage-muted">{{ quiz.description }}</p>
                        <p class="mt-5 text-sm font-bold text-heritage-muted">{{ quiz.questions }} questions · {{ quiz.time }}</p>
                    </article>
                </div>
            </section>

            <section class="page-shell pb-16">
                <div class="rounded-[2rem] bg-heritage-red p-8 text-center text-white md:p-12">
                    <h2 class="text-3xl font-black">Start learning Macedonian today</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-white/80">Pick a short quiz, learn a phrase, earn points, and build confidence one question at a time.</p>
                    <div class="mt-8">
                        <PrimaryButton href="/quizzes" variant="gold">Explore Quizzes</PrimaryButton>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
