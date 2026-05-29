<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '../Components/DashboardLayout.vue';
import ProgressBar from '../Components/ProgressBar.vue';
import AppBadge from '../Components/AppBadge.vue';
import StatCard from '../Components/StatCard.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import { getProgress } from '../api/makedoniq';

const loading = ref(true);
const error = ref('');
const progress = ref(null);

onMounted(async () => {
    try {
        const response = await getProgress();
        progress.value = response.data;
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to load your progress right now.';
    } finally {
        loading.value = false;
    }
});

const overall = computed(() => progress.value?.overall || {});
const categoryProgress = computed(() => progress.value?.category_progress || []);
const quizHistory = computed(() => progress.value?.quiz_history || []);
const achievements = computed(() => progress.value?.achievements || []);
const scoreTrends = computed(() => progress.value?.score_trends || []);

const overallCompletion = computed(() => {
    const totalQuizzes = categoryProgress.value.reduce((sum, category) => sum + Number(category.total_published_quizzes || 0), 0);
    const completedQuizzes = categoryProgress.value.reduce((sum, category) => sum + Number(category.completed_quizzes || 0), 0);

    return totalQuizzes > 0 ? Math.round((completedQuizzes / totalQuizzes) * 1000) / 10 : 0;
});

const statCards = computed(() => [
    {
        label: 'Total points',
        value: formatNumber(overall.value.total_points),
        detail: `${formatNumber(overall.value.passed_attempts_count)} passed attempts`,
        icon: 'XP',
        tone: 'gold',
    },
    {
        label: 'Total attempts',
        value: formatNumber(overall.value.total_attempts),
        detail: `${formatNumber(overall.value.completed_quizzes_count)} unique quizzes`,
        icon: 'AT',
        tone: 'red',
    },
    {
        label: 'Average score',
        value: formatPercentage(overall.value.average_percentage),
        detail: 'Across completed attempts',
        icon: '%',
        tone: 'navy',
    },
    {
        label: 'Best score',
        value: formatPercentage(overall.value.best_percentage),
        detail: 'Highest completed attempt',
        icon: 'PB',
        tone: 'gold',
    },
    {
        label: 'Current streak',
        value: `${formatNumber(overall.value.current_streak)} days`,
        detail: 'Consecutive completed quiz days',
        icon: 'ST',
        tone: 'navy',
    },
]);

function formatNumber(value) {
    return new Intl.NumberFormat('en-AU').format(Number(value || 0));
}

function formatPercentage(value) {
    const percentage = Number(value || 0);

    return `${Number.isInteger(percentage) ? percentage : percentage.toFixed(1)}%`;
}

function formatDate(value) {
    if (!value) {
        return 'Not dated';
    }

    return new Intl.DateTimeFormat('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function achievementIcon(key) {
    return {
        first_quiz_completed: '01',
        passed_first_quiz: '70',
        perfect_score: '100',
        completed_three_quizzes: '03',
        macedonian_explorer: 'MK',
        dedicated_learner: 'ST',
    }[key] || 'IQ';
}
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
                <p class="mt-3 text-5xl font-black text-heritage-red">{{ formatPercentage(overallCompletion) }}</p>
                <div class="mt-5">
                    <ProgressBar :value="overallCompletion" tone="navy" />
                </div>
            </div>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading your progress</h2>
            <p class="mt-2 text-heritage-muted">Your quiz history and achievements are being gathered.</p>
        </article>

        <article v-else-if="error" class="section-panel">
            <AppBadge variant="red">Progress error</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">We could not load your progress</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
            <PrimaryButton href="/login" class="mt-6" variant="gold">Log in again</PrimaryButton>
        </article>

        <template v-else>
            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
                <StatCard v-for="stat in statCards" :key="stat.label" :stat="stat" />
            </section>

            <section class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article v-for="category in categoryProgress" :key="category.slug" class="soft-card soft-card-hover p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xl font-black text-heritage-ink">{{ category.name_en }}</p>
                            <p class="mt-1 text-sm text-heritage-muted">{{ category.completed_quizzes }} of {{ category.total_published_quizzes }} quizzes completed</p>
                        </div>
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-heritage-panel text-sm font-black text-heritage-red">{{ category.icon || 'IQ' }}</span>
                    </div>
                    <ProgressBar :value="Number(category.progress_percentage || 0)" label="Progress" />
                    <p class="mt-3 text-sm font-semibold text-heritage-muted">
                        Best score:
                        <span class="text-heritage-ink">{{ category.best_percentage === null ? 'No attempts yet' : formatPercentage(category.best_percentage) }}</span>
                        / {{ formatNumber(category.total_points) }} points
                    </p>
                </article>
            </section>

            <section class="mt-10 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <article class="table-shell">
                    <div class="border-b border-heritage-line/50 p-6">
                        <h2 class="text-2xl font-black text-heritage-ink">Quiz history</h2>
                        <p class="mt-1 text-sm text-heritage-muted">Latest completed quiz attempts.</p>
                    </div>
                    <div v-if="quizHistory.length" class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left">
                            <thead class="table-heading">
                                <tr>
                                    <th class="px-6 py-4 font-black">Quiz</th>
                                    <th class="px-6 py-4 font-black">Category</th>
                                    <th class="px-6 py-4 font-black">Score</th>
                                    <th class="px-6 py-4 font-black">Status</th>
                                    <th class="px-6 py-4 font-black">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="attempt in quizHistory" :key="attempt.id" class="border-t border-heritage-line/40">
                                    <td class="px-6 py-4">
                                        <a :href="attempt.result_url || '/progress'" class="font-bold text-heritage-ink hover:text-heritage-red">{{ attempt.quiz_title_en }}</a>
                                        <p class="mt-1 text-xs font-semibold text-heritage-muted">{{ attempt.correct_answers }} of {{ attempt.total_questions }} correct</p>
                                    </td>
                                    <td class="px-6 py-4 text-heritage-muted">{{ attempt.category_name_en }}</td>
                                    <td class="px-6 py-4 font-black text-heritage-red">{{ formatPercentage(attempt.percentage) }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="['rounded-full px-3 py-1 text-xs font-black uppercase', attempt.passed ? 'bg-emerald-50 text-emerald-800' : 'bg-heritage-red-faint text-heritage-red']">
                                            {{ attempt.passed ? 'Passed' : 'Review' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-heritage-muted">{{ formatDate(attempt.completed_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-6 text-heritage-muted">
                        <p class="font-bold text-heritage-ink">No quiz history yet.</p>
                        <p class="mt-2 text-sm">Explore a category and complete a quiz to start building your progress timeline.</p>
                        <PrimaryButton href="/quizzes" class="mt-5" size="sm">Explore quizzes</PrimaryButton>
                    </div>
                </article>

                <aside class="space-y-8">
                    <article class="section-panel">
                        <h2 class="text-2xl font-black text-heritage-ink">Badges and achievements</h2>
                        <div class="mt-5 grid gap-3">
                            <div
                                v-for="achievement in achievements"
                                :key="achievement.key"
                                :class="['flex gap-4 rounded-2xl p-4', achievement.unlocked ? 'bg-heritage-panel' : 'bg-white opacity-60 ring-1 ring-heritage-line/50']"
                            >
                                <span :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl font-black', achievement.unlocked ? 'bg-heritage-gold-faint text-heritage-gold-deep' : 'bg-heritage-panel text-heritage-muted']">
                                    {{ achievementIcon(achievement.key) }}
                                </span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black text-heritage-ink">{{ achievement.title }}</p>
                                        <AppBadge :variant="achievement.unlocked ? 'green' : 'neutral'">{{ achievement.unlocked ? 'Unlocked' : 'Locked' }}</AppBadge>
                                    </div>
                                    <p class="text-sm text-heritage-muted">{{ achievement.description }}</p>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="section-panel">
                        <h2 class="text-2xl font-black text-heritage-ink">Score trends</h2>
                        <div v-if="scoreTrends.length" class="mt-6 flex h-44 items-end gap-3 rounded-2xl bg-heritage-panel p-4">
                            <div v-for="trend in scoreTrends" :key="trend.attempt_id" class="flex h-full w-full flex-col justify-end gap-2">
                                <div
                                    class="w-full rounded-t-xl bg-linear-to-t from-heritage-red to-heritage-gold transition-all"
                                    :style="{ height: `${Math.max(Number(trend.percentage || 0), 6)}%` }"
                                />
                                <p class="text-center text-xs font-black text-heritage-muted">{{ Math.round(trend.percentage) }}</p>
                            </div>
                        </div>
                        <div v-else class="mt-6 rounded-2xl bg-heritage-panel p-5 text-heritage-muted">
                            Complete a quiz to see your score trend.
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-heritage-red-faint p-4">
                                <p class="label">Completed</p>
                                <p class="mt-1 text-2xl font-black text-heritage-red">{{ formatNumber(overall.completed_quizzes_count) }}</p>
                            </div>
                            <div class="rounded-2xl bg-heritage-panel p-4">
                                <p class="label">Average</p>
                                <p class="mt-1 text-2xl font-black text-heritage-muted">{{ formatPercentage(overall.average_percentage) }}</p>
                            </div>
                        </div>
                    </article>
                </aside>
            </section>
        </template>
    </DashboardLayout>
</template>
