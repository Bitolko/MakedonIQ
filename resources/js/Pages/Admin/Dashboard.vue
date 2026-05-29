<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import StatCard from '../../Components/StatCard.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import { getAdminOverview } from '../../api/makedoniq';

const loading = ref(true);
const error = ref('');
const overview = ref(null);

onMounted(async () => {
    try {
        const response = await getAdminOverview();
        overview.value = response.data;
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to view admin reporting.'
            : caughtError.message || 'Unable to load admin overview.';
    } finally {
        loading.value = false;
    }
});

const totals = computed(() => overview.value?.totals || {});
const recentAttempts = computed(() => overview.value?.recent_attempts || []);
const popularQuizzes = computed(() => overview.value?.most_popular_quizzes || []);
const categoryCounts = computed(() => overview.value?.quick_counts_by_category || []);

const stats = computed(() => [
    { label: 'Users', value: formatNumber(totals.value.total_users), detail: 'Registered learners', icon: 'U', tone: 'red' },
    { label: 'Categories', value: formatNumber(totals.value.total_categories), detail: `${formatNumber(totals.value.total_published_categories)} published`, icon: 'C', tone: 'gold' },
    { label: 'Quizzes', value: formatNumber(totals.value.total_quizzes), detail: `${formatNumber(totals.value.total_published_quizzes)} published`, icon: 'Q', tone: 'navy' },
    { label: 'Questions', value: formatNumber(totals.value.total_questions), detail: `${formatNumber(totals.value.total_published_questions)} published`, icon: '?', tone: 'red' },
    { label: 'Attempts', value: formatNumber(totals.value.total_attempts), detail: `${formatPercentage(totals.value.pass_rate)} pass rate`, icon: 'OK', tone: 'gold' },
    { label: 'Average score', value: formatPercentage(totals.value.average_score), detail: 'Across completed attempts', icon: '%', tone: 'navy' },
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
    <AdminLayout>
        <section class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <h1 class="text-4xl font-black text-heritage-ink">Dashboard Overview</h1>
                <p class="mt-2 text-heritage-muted">Live read-only reporting from learners, quizzes, questions, and saved attempts.</p>
            </div>
            <PrimaryButton variant="soft">Add Quiz: Coming soon</PrimaryButton>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading admin overview</h2>
            <p class="mt-2 text-heritage-muted">Gathering database totals and recent attempt activity.</p>
        </article>

        <article v-else-if="error" class="section-panel">
            <AppBadge variant="red">Admin access</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load admin overview</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
            <PrimaryButton href="/dashboard" class="mt-6" variant="gold">Back to dashboard</PrimaryButton>
        </article>

        <template v-else>
            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <StatCard v-for="stat in stats" :key="stat.label" :stat="stat" />
            </section>

            <section class="mt-10 grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
                <article class="table-shell">
                    <div class="border-b border-heritage-line/50 p-6">
                        <h2 class="text-2xl font-black text-heritage-ink">Recent attempts</h2>
                        <p class="mt-1 text-sm text-heritage-muted">Latest completed quiz attempts across all learners.</p>
                    </div>
                    <div v-if="recentAttempts.length" class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left">
                            <thead class="table-heading">
                                <tr>
                                    <th class="px-6 py-4 font-black">Learner</th>
                                    <th class="px-6 py-4 font-black">Quiz</th>
                                    <th class="px-6 py-4 font-black">Score</th>
                                    <th class="px-6 py-4 font-black">Status</th>
                                    <th class="px-6 py-4 font-black">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="attempt in recentAttempts" :key="attempt.id" class="border-t border-heritage-line/40">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-heritage-ink">{{ attempt.user_name }}</p>
                                        <p class="text-xs font-semibold text-heritage-muted">{{ attempt.user_email }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-heritage-ink">{{ attempt.quiz_title_en }}</p>
                                        <p class="text-xs font-semibold text-heritage-muted">{{ attempt.category_name_en }}</p>
                                    </td>
                                    <td class="px-6 py-4 font-black text-heritage-red">{{ formatPercentage(attempt.percentage) }}</td>
                                    <td class="px-6 py-4">
                                        <AppBadge :variant="attempt.passed ? 'green' : 'red'">{{ attempt.passed ? 'Passed' : 'Review' }}</AppBadge>
                                    </td>
                                    <td class="px-6 py-4 text-heritage-muted">{{ formatDate(attempt.completed_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-6 text-heritage-muted">No quiz attempts have been completed yet.</div>
                </article>

                <aside class="space-y-8">
                    <article class="section-panel">
                        <h2 class="text-2xl font-black text-heritage-ink">Most popular quizzes</h2>
                        <div class="mt-5 grid gap-3">
                            <div v-for="quiz in popularQuizzes" :key="quiz.id" class="rounded-2xl bg-heritage-panel p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="font-black text-heritage-ink">{{ quiz.title_en }}</p>
                                    <span class="font-black text-heritage-red">{{ formatNumber(quiz.attempts_count) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-heritage-muted">{{ quiz.category_name_en }} / avg {{ quiz.average_percentage === null ? 'n/a' : formatPercentage(quiz.average_percentage) }}</p>
                            </div>
                            <p v-if="!popularQuizzes.length" class="rounded-2xl bg-heritage-panel p-4 text-sm font-semibold text-heritage-muted">No popularity data yet.</p>
                        </div>
                    </article>

                    <article class="section-panel">
                        <h2 class="text-2xl font-black text-heritage-ink">Category summary</h2>
                        <div class="mt-5 grid gap-3">
                            <div v-for="category in categoryCounts" :key="category.id" class="rounded-2xl bg-heritage-panel p-4">
                                <p class="font-black text-heritage-ink">{{ category.name_en }}</p>
                                <p class="mt-1 text-sm text-heritage-muted">
                                    {{ formatNumber(category.quiz_count) }} quizzes / {{ formatNumber(category.question_count) }} questions / {{ formatNumber(category.attempt_count) }} attempts
                                </p>
                            </div>
                        </div>
                    </article>
                </aside>
            </section>
        </template>
    </AdminLayout>
</template>
