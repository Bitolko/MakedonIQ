<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '../Components/DashboardLayout.vue';
import StatCard from '../Components/StatCard.vue';
import ProgressBar from '../Components/ProgressBar.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import { currentUser, getDashboard } from '../api/makedoniq';

const loading = ref(true);
const error = ref('');
const dashboard = ref(null);

onMounted(async () => {
    try {
        const response = await getDashboard();
        dashboard.value = response.data;
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to load your dashboard right now.';
    } finally {
        loading.value = false;
    }
});

const user = computed(() => dashboard.value?.user || currentUser() || {});
const summary = computed(() => dashboard.value?.summary || {});
const displayName = computed(() => user.value.name || 'learner');
const recentAttempts = computed(() => dashboard.value?.recent_attempts || []);
const recommendedQuizzes = computed(() => dashboard.value?.recommended_quizzes || []);
const categoryProgress = computed(() => dashboard.value?.category_progress || []);

const heroStats = computed(() => [
    {
        value: `${summary.value.current_streak || 0}`,
        label: 'day streak',
    },
    {
        value: formatNumber(summary.value.completed_quizzes_count),
        label: 'quizzes',
    },
    {
        value: formatNumber(summary.value.total_points),
        label: 'points',
    },
]);

const statCards = computed(() => [
    {
        label: 'Total points',
        value: formatNumber(summary.value.total_points),
        detail: `${formatNumber(summary.value.passed_attempts_count)} passed attempts`,
        icon: 'XP',
        tone: 'gold',
    },
    {
        label: 'Completed quizzes',
        value: formatNumber(summary.value.completed_quizzes_count),
        detail: `${formatNumber(summary.value.total_attempts_count)} total attempts`,
        icon: 'OK',
        tone: 'red',
    },
    {
        label: 'Average score',
        value: formatPercentage(summary.value.average_percentage),
        detail: `Best score ${formatPercentage(summary.value.best_percentage)}`,
        icon: '%',
        tone: 'navy',
    },
    {
        label: 'Current streak',
        value: `${formatNumber(summary.value.current_streak)} days`,
        detail: 'Completed quiz days in a row',
        icon: 'ST',
        tone: 'gold',
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
</script>

<template>
    <DashboardLayout>
        <section class="heritage-pattern mb-10 rounded-[2rem] p-6 text-white shadow-soft md:p-8">
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <AppBadge variant="gold">Learner dashboard</AppBadge>
                    <h1 class="mt-4 break-words text-4xl font-black md:text-5xl">Welcome back, {{ displayName }}!</h1>
                    <p class="mt-3 max-w-2xl text-lg leading-8 text-white/80">Continue a quiz, review recent results, and keep exploring your Macedonian heritage.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div v-for="item in heroStats" :key="item.label" class="rounded-2xl bg-white/10 p-4 text-center">
                        <p class="text-2xl font-black text-heritage-gold">{{ item.value }}</p>
                        <p class="text-xs font-bold text-white/70">{{ item.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading your dashboard</h2>
            <p class="mt-2 text-heritage-muted">Your quiz attempts and learning stats are being gathered.</p>
        </article>

        <article v-else-if="error" class="section-panel">
            <AppBadge variant="red">Dashboard error</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">We could not load your dashboard</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
            <PrimaryButton href="/login" class="mt-6" variant="gold">Log in again</PrimaryButton>
        </article>

        <template v-else>
            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <StatCard v-for="stat in statCards" :key="stat.label" :stat="stat" />
            </section>

            <section class="mt-10 grid gap-8 lg:grid-cols-[1.4fr_0.8fr]">
                <div class="space-y-8">
                    <article class="section-panel">
                        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                            <div>
                                <h2 class="text-2xl font-black text-heritage-ink">Recent results</h2>
                                <p class="mt-1 text-sm text-heritage-muted">Your latest completed quiz attempts.</p>
                            </div>
                            <PrimaryButton href="/progress" variant="soft" size="sm">View progress</PrimaryButton>
                        </div>

                        <div v-if="recentAttempts.length" class="grid gap-3">
                            <a
                                v-for="attempt in recentAttempts"
                                :key="attempt.id"
                                :href="attempt.result_url || '/progress'"
                                class="rounded-2xl border border-heritage-line/50 bg-heritage-panel p-4 transition hover:bg-white hover:shadow-card"
                            >
                                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                                    <div class="min-w-0">
                                        <p class="font-black text-heritage-ink">{{ attempt.quiz_title_en }}</p>
                                        <p class="mt-1 text-sm text-heritage-muted">{{ attempt.category_name_en }} / {{ formatDate(attempt.completed_at) }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <span :class="['rounded-full px-3 py-1 text-xs font-black uppercase', attempt.passed ? 'bg-emerald-50 text-emerald-800' : 'bg-heritage-red-faint text-heritage-red']">
                                            {{ attempt.passed ? 'Passed' : 'Review' }}
                                        </span>
                                        <span class="text-2xl font-black text-heritage-red">{{ formatPercentage(attempt.percentage) }}</span>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-heritage-muted">
                                    {{ attempt.correct_answers }} of {{ attempt.total_questions }} correct / {{ formatNumber(attempt.score) }} points
                                </p>
                            </a>
                        </div>

                        <div v-else class="rounded-2xl bg-heritage-panel p-5">
                            <p class="font-bold text-heritage-ink">No completed quizzes yet.</p>
                            <p class="mt-2 text-sm text-heritage-muted">Start your first quiz to unlock points, results, and progress tracking.</p>
                            <PrimaryButton href="/quizzes" class="mt-5" size="sm">Start your first quiz</PrimaryButton>
                        </div>
                    </article>

                    <article class="section-panel">
                        <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                            <div>
                                <h2 class="text-2xl font-black text-heritage-ink">Recommended quizzes</h2>
                                <p class="mt-1 text-sm text-heritage-muted">Published quizzes selected from your current learning history.</p>
                            </div>
                            <PrimaryButton href="/quizzes" variant="soft" size="sm">View all</PrimaryButton>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <a
                                v-for="quiz in recommendedQuizzes"
                                :key="quiz.id"
                                :href="quiz.start_url"
                                class="rounded-2xl border border-heritage-line/50 bg-heritage-panel p-5 transition hover:bg-white hover:shadow-card"
                            >
                                <p class="font-black text-heritage-ink">{{ quiz.title_en }}</p>
                                <p class="mt-2 text-sm text-heritage-muted">{{ quiz.category_name_en }} / {{ quiz.question_count }} questions / {{ quiz.estimated_minutes || 8 }} min</p>
                                <div class="mt-4">
                                    <AppBadge variant="neutral">{{ quiz.difficulty }}</AppBadge>
                                </div>
                            </a>
                        </div>
                    </article>
                </div>

                <aside class="space-y-8">
                    <article class="section-panel">
                        <h2 class="text-2xl font-black text-heritage-ink">Progress by category</h2>
                        <div class="mt-5 grid gap-5">
                            <div v-for="category in categoryProgress" :key="category.slug">
                                <ProgressBar :value="Number(category.progress_percentage || 0)" :label="category.name_en" />
                                <p class="mt-2 text-sm text-heritage-muted">
                                    {{ category.completed_quizzes }} of {{ category.total_published_quizzes }} quizzes completed / {{ formatNumber(category.total_points) }} points
                                    <span v-if="category.best_percentage !== null"> / best {{ formatPercentage(category.best_percentage) }}</span>
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="section-panel">
                        <h2 class="text-2xl font-black text-heritage-ink">Learning streak</h2>
                        <div class="mt-5 rounded-2xl bg-heritage-gold-faint p-5">
                            <p class="text-4xl font-black text-heritage-gold-deep">{{ summary.current_streak || 0 }} days</p>
                            <p class="mt-2 text-sm font-semibold text-heritage-muted">Based on consecutive days with completed quiz attempts.</p>
                        </div>
                    </article>
                </aside>
            </section>
        </template>
    </DashboardLayout>
</template>
