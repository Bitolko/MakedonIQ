<script setup>
import DashboardLayout from '../Components/DashboardLayout.vue';
import ProgressBar from '../Components/ProgressBar.vue';
import AppBadge from '../Components/AppBadge.vue';
import { achievements, progressCategories, recentResults } from '../data/makedoniq';
</script>

<template>
    <DashboardLayout>
        <section class="mb-10 grid gap-6 md:grid-cols-[1.35fr_0.65fr] md:items-center">
            <div>
                <AppBadge>My Progress</AppBadge>
                <h1 class="mt-4 text-4xl font-black text-heritage-ink md:text-5xl">Overall learning progress</h1>
                <p class="mt-3 max-w-2xl text-lg leading-8 text-heritage-muted">Track category mastery, quiz history, badges, and score trends across the MakedonIQ learning path.</p>
            </div>
            <div class="soft-card p-6 text-center">
                <p class="label">Overall completion</p>
                <p class="mt-3 text-5xl font-black text-heritage-red">61%</p>
                <div class="mt-5">
                    <ProgressBar :value="61" tone="navy" />
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <article v-for="category in progressCategories" :key="category.title" class="soft-card soft-card-hover p-6">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xl font-black text-heritage-ink">{{ category.title }}</p>
                        <p class="mt-1 text-sm text-heritage-muted">{{ category.completed }} of {{ category.total }} quizzes completed</p>
                    </div>
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-heritage-panel text-sm font-black text-heritage-red">{{ category.icon }}</span>
                </div>
                <ProgressBar :value="category.progress" label="Progress" />
            </article>
        </section>

        <section class="mt-10 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <article class="table-shell">
                <div class="border-b border-heritage-line/50 p-6">
                    <h2 class="text-2xl font-black text-heritage-ink">Quiz history</h2>
                    <p class="mt-1 text-sm text-heritage-muted">Recent completed and in-progress quiz attempts.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left">
                        <thead class="table-heading">
                            <tr>
                                <th class="px-6 py-4 font-black">Quiz</th>
                                <th class="px-6 py-4 font-black">Score</th>
                                <th class="px-6 py-4 font-black">Points</th>
                                <th class="px-6 py-4 font-black">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in recentResults" :key="result.quiz" class="border-t border-heritage-line/40">
                                <td class="px-6 py-4 font-bold text-heritage-ink">{{ result.quiz }}</td>
                                <td class="px-6 py-4 font-black text-heritage-red">{{ result.score }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ result.points }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ result.date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="space-y-8">
                <article class="section-panel">
                    <h2 class="text-2xl font-black text-heritage-ink">Badges and achievements</h2>
                    <div class="mt-5 grid gap-3">
                        <div v-for="achievement in achievements" :key="achievement.title" class="flex gap-4 rounded-2xl bg-heritage-panel p-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-heritage-gold-faint font-black text-heritage-gold-deep">{{ achievement.icon }}</span>
                            <div>
                                <p class="font-black text-heritage-ink">{{ achievement.title }}</p>
                                <p class="text-sm text-heritage-muted">{{ achievement.description }}</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="section-panel">
                    <h2 class="text-2xl font-black text-heritage-ink">Score trends</h2>
                    <div class="mt-6 flex h-40 items-end gap-3 rounded-2xl bg-heritage-panel p-4">
                        <div class="w-full rounded-t-xl bg-heritage-red" style="height: 45%" />
                        <div class="w-full rounded-t-xl bg-heritage-gold" style="height: 58%" />
                        <div class="w-full rounded-t-xl bg-heritage-red" style="height: 72%" />
                        <div class="w-full rounded-t-xl bg-heritage-gold" style="height: 84%" />
                        <div class="w-full rounded-t-xl bg-heritage-red" style="height: 90%" />
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-heritage-red-faint p-4">
                            <p class="label">Completed</p>
                            <p class="mt-1 text-2xl font-black text-heritage-red">31</p>
                        </div>
                        <div class="rounded-2xl bg-heritage-panel p-4">
                            <p class="label">Not completed</p>
                            <p class="mt-1 text-2xl font-black text-heritage-muted">21</p>
                        </div>
                    </div>
                </article>
            </aside>
        </section>
    </DashboardLayout>
</template>
