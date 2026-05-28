<script setup>
import DashboardLayout from '../Components/DashboardLayout.vue';
import StatCard from '../Components/StatCard.vue';
import ProgressBar from '../Components/ProgressBar.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import { dashboardStats, historyQuizzes, progressCategories, recentResults } from '../data/makedoniq';
</script>

<template>
    <DashboardLayout>
        <section class="mb-10">
            <h1 class="text-4xl font-black text-heritage-ink md:text-5xl">Welcome back, Stefan!</h1>
            <p class="mt-3 text-lg text-heritage-muted">You are on a roll. Keep exploring your Macedonian heritage.</p>
        </section>

        <section class="grid gap-6 md:grid-cols-3">
            <StatCard v-for="stat in dashboardStats" :key="stat.label" :stat="stat" />
        </section>

        <section class="mt-10 grid gap-8 lg:grid-cols-[1.4fr_0.8fr]">
            <div class="space-y-8">
                <article class="soft-card overflow-hidden">
                    <div class="heritage-pattern p-8 text-white">
                        <AppBadge variant="gold">Continue learning</AppBadge>
                        <h2 class="mt-4 text-3xl font-black">Ancient Macedonia Basics</h2>
                        <p class="mt-3 max-w-xl text-white/80">Pick up where you left off and finish the history starter quiz.</p>
                    </div>
                    <div class="p-6">
                        <ProgressBar :value="80" label="Quiz progress" />
                        <PrimaryButton href="/quizzes/history/start" class="mt-6">Continue quiz</PrimaryButton>
                    </div>
                </article>

                <article class="soft-card p-6">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-2xl font-black text-heritage-ink">Recommended quizzes</h2>
                        <a href="/quizzes" class="text-sm font-black text-heritage-red">View all</a>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div v-for="quiz in historyQuizzes.slice(1, 5)" :key="quiz.title" class="rounded-2xl bg-heritage-panel p-5">
                            <p class="font-black text-heritage-ink">{{ quiz.title }}</p>
                            <p class="mt-2 text-sm text-heritage-muted">{{ quiz.questions }} questions · {{ quiz.time }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="space-y-8">
                <article class="soft-card p-6">
                    <h2 class="text-2xl font-black text-heritage-ink">Progress by category</h2>
                    <div class="mt-5 grid gap-5">
                        <ProgressBar v-for="category in progressCategories.slice(0, 5)" :key="category.title" :value="category.progress" :label="category.title" />
                    </div>
                </article>

                <article class="soft-card p-6">
                    <h2 class="text-2xl font-black text-heritage-ink">Recent results</h2>
                    <div class="mt-5 grid gap-3">
                        <div v-for="result in recentResults" :key="result.quiz" class="rounded-2xl bg-heritage-panel p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-black text-heritage-ink">{{ result.quiz }}</p>
                                <span class="font-black text-heritage-red">{{ result.score }}</span>
                            </div>
                            <p class="mt-1 text-sm text-heritage-muted">{{ result.points }} points · {{ result.date }}</p>
                        </div>
                    </div>
                </article>
            </aside>
        </section>
    </DashboardLayout>
</template>
