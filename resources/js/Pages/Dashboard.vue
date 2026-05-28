<script setup>
import DashboardLayout from '../Components/DashboardLayout.vue';
import StatCard from '../Components/StatCard.vue';
import ProgressBar from '../Components/ProgressBar.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import { dashboardStats, historyQuizzes, progressCategories, recentResults } from '../data/makedoniq';

const user = window.MakedonIQ?.auth?.user;
const displayName = user?.name || 'learner';
</script>

<template>
    <DashboardLayout>
        <section class="heritage-pattern mb-10 rounded-[2rem] p-6 text-white shadow-soft md:p-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <AppBadge variant="gold">Learner dashboard</AppBadge>
                    <h1 class="mt-4 text-4xl font-black md:text-5xl">Welcome back, {{ displayName }}!</h1>
                    <p class="mt-3 max-w-2xl text-lg leading-8 text-white/80">You are on a roll. Continue a quiz, review recent results, and keep exploring your Macedonian heritage.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 p-4 text-center">
                        <p class="text-2xl font-black text-heritage-gold">5</p>
                        <p class="text-xs font-bold text-white/70">day streak</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 text-center">
                        <p class="text-2xl font-black text-heritage-gold">12</p>
                        <p class="text-xs font-bold text-white/70">quizzes</p>
                    </div>
                    <div class="col-span-2 rounded-2xl bg-white/10 p-4 text-center sm:col-span-1">
                        <p class="text-2xl font-black text-heritage-gold">1,250</p>
                        <p class="text-xs font-bold text-white/70">points</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-3">
            <StatCard v-for="stat in dashboardStats" :key="stat.label" :stat="stat" />
        </section>

        <section class="mt-10 grid gap-8 lg:grid-cols-[1.4fr_0.8fr]">
            <div class="space-y-8">
                <article class="soft-card overflow-hidden">
                    <div class="bg-white p-6 md:p-8">
                        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                            <div>
                                <AppBadge variant="red">Continue learning</AppBadge>
                                <h2 class="mt-4 text-3xl font-black text-heritage-ink">Ancient Macedonia Basics</h2>
                                <p class="mt-3 max-w-xl text-heritage-muted">Pick up where you left off and finish the history starter quiz.</p>
                            </div>
                            <div class="rounded-2xl bg-heritage-gold-faint px-5 py-4 text-center">
                                <p class="text-2xl font-black text-heritage-gold-deep">80%</p>
                                <p class="text-xs font-black uppercase text-heritage-muted">Complete</p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <ProgressBar :value="80" label="Quiz progress" tone="navy" />
                        </div>
                        <PrimaryButton href="/quizzes/history/start" class="mt-6">Continue quiz</PrimaryButton>
                    </div>
                </article>

                <article class="section-panel">
                    <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-2xl font-black text-heritage-ink">Recommended quizzes</h2>
                            <p class="mt-1 text-sm text-heritage-muted">Short lessons based on your recent history progress.</p>
                        </div>
                        <PrimaryButton href="/quizzes" variant="soft" size="sm">View all</PrimaryButton>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div v-for="quiz in historyQuizzes.slice(1, 5)" :key="quiz.title" class="rounded-2xl border border-heritage-line/50 bg-heritage-panel p-5 transition hover:bg-white hover:shadow-card">
                            <p class="font-black text-heritage-ink">{{ quiz.title }}</p>
                            <p class="mt-2 text-sm text-heritage-muted">{{ quiz.questions }} questions / {{ quiz.time }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="space-y-8">
                <article class="section-panel">
                    <h2 class="text-2xl font-black text-heritage-ink">Progress by category</h2>
                    <div class="mt-5 grid gap-5">
                        <ProgressBar v-for="category in progressCategories.slice(0, 5)" :key="category.title" :value="category.progress" :label="category.title" />
                    </div>
                </article>

                <article class="section-panel">
                    <h2 class="text-2xl font-black text-heritage-ink">Recent results</h2>
                    <div class="mt-5 grid gap-3">
                        <div v-for="result in recentResults" :key="result.quiz" class="rounded-2xl bg-heritage-panel p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-black text-heritage-ink">{{ result.quiz }}</p>
                                <span class="font-black text-heritage-red">{{ result.score }}</span>
                            </div>
                            <p class="mt-1 text-sm text-heritage-muted">{{ result.points }} points / {{ result.date }}</p>
                        </div>
                    </div>
                </article>
            </aside>
        </section>
    </DashboardLayout>
</template>
