<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { getAdminAttempts } from '../../api/makedoniq';

const loading = ref(true);
const error = ref('');
const attempts = ref([]);
const meta = ref({});
const options = ref({
    categories: [],
    quizzes: [],
});
const filters = ref({
    search: '',
    category_id: '',
    quiz_id: '',
    status: '',
});

const visibleQuizzes = computed(() => {
    if (!filters.value.category_id) {
        return options.value.quizzes || [];
    }

    return (options.value.quizzes || []).filter((quiz) => String(quiz.category_id) === String(filters.value.category_id));
});

onMounted(() => {
    fetchAttempts();
});

async function fetchAttempts(page = 1) {
    loading.value = true;
    error.value = '';

    try {
        const response = await getAdminAttempts({
            ...filters.value,
            page,
        });

        attempts.value = response.data || [];
        meta.value = response.meta || {};
        options.value = response.filters || options.value;
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to view quiz attempts.'
            : caughtError.message || 'Unable to load quiz attempts.';
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    fetchAttempts(1);
}

function resetFilters() {
    filters.value = {
        search: '',
        category_id: '',
        quiz_id: '',
        status: '',
    };

    fetchAttempts(1);
}

function handleCategoryChange() {
    if (!filters.value.category_id) {
        return;
    }

    const selectedQuiz = (options.value.quizzes || []).find((quiz) => String(quiz.id) === String(filters.value.quiz_id));

    if (selectedQuiz && String(selectedQuiz.category_id) !== String(filters.value.category_id)) {
        filters.value.quiz_id = '';
    }
}

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
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <AdminLayout>
        <section class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <AppBadge variant="navy">Admin attempts</AppBadge>
                <h1 class="mt-4 text-4xl font-black text-heritage-ink">Quiz Attempts</h1>
                <p class="mt-2 max-w-3xl text-heritage-muted">Review completed learner attempts, filter by quiz or status, and open saved answer results.</p>
            </div>
        </section>

        <form class="section-panel" @submit.prevent="applyFilters">
            <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr_0.8fr_0.6fr_auto] lg:items-end">
                <label class="grid gap-2">
                    <span class="label">Search</span>
                    <input
                        v-model="filters.search"
                        class="rounded-2xl border border-heritage-line bg-white px-4 py-3 text-sm font-bold text-heritage-ink outline-none transition focus:border-heritage-red"
                        type="search"
                        placeholder="Learner, email, quiz, or category"
                    >
                </label>

                <label class="grid gap-2">
                    <span class="label">Category</span>
                    <select
                        v-model="filters.category_id"
                        class="rounded-2xl border border-heritage-line bg-white px-4 py-3 text-sm font-bold text-heritage-ink outline-none transition focus:border-heritage-red"
                        @change="handleCategoryChange"
                    >
                        <option value="">All categories</option>
                        <option v-for="category in options.categories" :key="category.id" :value="category.id">{{ category.name_en }}</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="label">Quiz</span>
                    <select
                        v-model="filters.quiz_id"
                        class="rounded-2xl border border-heritage-line bg-white px-4 py-3 text-sm font-bold text-heritage-ink outline-none transition focus:border-heritage-red"
                    >
                        <option value="">All quizzes</option>
                        <option v-for="quiz in visibleQuizzes" :key="quiz.id" :value="quiz.id">{{ quiz.title_en }}</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="label">Status</span>
                    <select
                        v-model="filters.status"
                        class="rounded-2xl border border-heritage-line bg-white px-4 py-3 text-sm font-bold text-heritage-ink outline-none transition focus:border-heritage-red"
                    >
                        <option value="">All statuses</option>
                        <option value="passed">Passed</option>
                        <option value="review">Review</option>
                    </select>
                </label>

                <div class="flex gap-3">
                    <PrimaryButton type="submit">Apply</PrimaryButton>
                    <PrimaryButton type="button" variant="soft" @click="resetFilters">Reset</PrimaryButton>
                </div>
            </div>
        </form>

        <article v-if="loading" class="section-panel mt-8">
            <AppBadge variant="gold">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading attempts</h2>
            <p class="mt-2 text-heritage-muted">Gathering completed quiz attempts and saved answer data.</p>
        </article>

        <article v-else-if="error" class="section-panel mt-8">
            <AppBadge variant="red">Attempts unavailable</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load attempts</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <article v-else class="table-shell mt-8">
            <div class="flex flex-col justify-between gap-3 border-b border-heritage-line/50 p-6 md:flex-row md:items-end">
                <div>
                    <h2 class="text-2xl font-black text-heritage-ink">Completed attempts</h2>
                    <p class="mt-1 text-sm text-heritage-muted">
                        Showing {{ formatNumber(meta.from || 0) }}-{{ formatNumber(meta.to || 0) }} of {{ formatNumber(meta.total || attempts.length) }} attempts.
                    </p>
                </div>
                <AppBadge variant="neutral">Newest first</AppBadge>
            </div>

            <div v-if="attempts.length" class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="table-heading">
                        <tr>
                            <th class="px-6 py-4 font-black">Learner</th>
                            <th class="px-6 py-4 font-black">Quiz</th>
                            <th class="px-6 py-4 font-black">Category</th>
                            <th class="px-6 py-4 font-black">Score</th>
                            <th class="px-6 py-4 font-black">Correct</th>
                            <th class="px-6 py-4 font-black">Points</th>
                            <th class="px-6 py-4 font-black">Status</th>
                            <th class="px-6 py-4 font-black">Completed</th>
                            <th class="px-6 py-4 font-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="attempt in attempts" :key="attempt.id" class="border-t border-heritage-line/40">
                            <td class="px-6 py-4">
                                <p class="font-bold text-heritage-ink">{{ attempt.user_name }}</p>
                                <p class="text-xs font-semibold text-heritage-muted">{{ attempt.user_email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-heritage-ink">{{ attempt.quiz_title_en }}</p>
                                <p class="text-xs font-semibold text-heritage-muted">Attempt #{{ attempt.id }}</p>
                            </td>
                            <td class="px-6 py-4 text-heritage-muted">{{ attempt.category_name_en }}</td>
                            <td class="px-6 py-4 font-black text-heritage-red">{{ formatPercentage(attempt.percentage) }}</td>
                            <td class="px-6 py-4 font-bold text-heritage-ink">{{ attempt.correct_answers }}/{{ attempt.total_questions }}</td>
                            <td class="px-6 py-4 font-bold text-heritage-gold-deep">{{ formatNumber(attempt.score) }}</td>
                            <td class="px-6 py-4">
                                <AppBadge :variant="attempt.passed ? 'green' : 'red'">{{ attempt.passed ? 'Passed' : 'Review' }}</AppBadge>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-heritage-muted">{{ formatDate(attempt.completed_at) }}</td>
                            <td class="px-6 py-4">
                                <PrimaryButton :href="attempt.admin_result_url" size="sm" variant="soft">View result</PrimaryButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="p-6">
                <p class="font-bold text-heritage-ink">No attempts match these filters.</p>
                <p class="mt-2 text-sm text-heritage-muted">Try clearing the search or choosing a different category.</p>
            </div>

            <div v-if="meta.last_page > 1" class="flex flex-col justify-between gap-3 border-t border-heritage-line/50 p-6 sm:flex-row sm:items-center">
                <p class="text-sm font-bold text-heritage-muted">Page {{ formatNumber(meta.current_page) }} of {{ formatNumber(meta.last_page) }}</p>
                <div class="flex gap-3">
                    <PrimaryButton
                        variant="soft"
                        size="sm"
                        :disabled="meta.current_page <= 1"
                        @click="fetchAttempts(meta.current_page - 1)"
                    >
                        Previous
                    </PrimaryButton>
                    <PrimaryButton
                        variant="soft"
                        size="sm"
                        :disabled="meta.current_page >= meta.last_page"
                        @click="fetchAttempts(meta.current_page + 1)"
                    >
                        Next
                    </PrimaryButton>
                </div>
            </div>
        </article>
    </AdminLayout>
</template>
