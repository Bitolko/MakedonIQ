<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import { getAdminQuestions } from '../../api/makedoniq';

const loading = ref(true);
const error = ref('');
const questions = ref([]);
const search = ref('');
const quizFilter = ref('all');
const categoryFilter = ref('all');
const statusFilter = ref('all');

onMounted(async () => {
    try {
        const response = await getAdminQuestions();
        questions.value = response.data || [];
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to view admin questions.'
            : caughtError.message || 'Unable to load questions.';
    } finally {
        loading.value = false;
    }
});

const quizzes = computed(() => {
    const byId = new Map();

    questions.value.forEach((question) => {
        byId.set(question.quiz_id, {
            id: question.quiz_id,
            title: question.quiz_title_en,
        });
    });

    return [...byId.values()].sort((first, second) => first.title.localeCompare(second.title));
});

const categories = computed(() => {
    const bySlug = new Map();

    questions.value.forEach((question) => {
        bySlug.set(question.category_slug, {
            slug: question.category_slug,
            name: question.category_name_en,
        });
    });

    return [...bySlug.values()].sort((first, second) => first.name.localeCompare(second.name));
});

const filteredQuestions = computed(() => {
    const needle = search.value.trim().toLowerCase();

    return questions.value.filter((question) => {
        const matchesSearch = !needle
            || question.question_en.toLowerCase().includes(needle)
            || (question.question_mk || '').toLowerCase().includes(needle)
            || question.quiz_title_en.toLowerCase().includes(needle)
            || question.category_name_en.toLowerCase().includes(needle)
            || (question.correct_answer_en || '').toLowerCase().includes(needle);
        const matchesQuiz = quizFilter.value === 'all' || Number(quizFilter.value) === question.quiz_id;
        const matchesCategory = categoryFilter.value === 'all' || categoryFilter.value === question.category_slug;
        const matchesStatus = statusFilter.value === 'all'
            || (statusFilter.value === 'published' && question.is_published)
            || (statusFilter.value === 'unpublished' && !question.is_published);

        return matchesSearch && matchesQuiz && matchesCategory && matchesStatus;
    });
});

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
                <h1 class="text-4xl font-black text-heritage-ink">Admin Questions</h1>
                <p class="mt-2 text-heritage-muted">Read-only question bank with answer keys for admins. Editing tools are coming soon.</p>
            </div>
            <PrimaryButton variant="soft">Add question: Coming soon</PrimaryButton>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading questions</h2>
            <p class="mt-2 text-heritage-muted">Fetching bilingual questions, quiz context, and answer counts.</p>
        </article>

        <article v-else-if="error" class="section-panel">
            <AppBadge variant="red">Admin access</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load questions</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <template v-else>
            <section class="mb-5 grid gap-3 rounded-[1.5rem] bg-white p-4 shadow-card lg:grid-cols-[1fr_auto_auto_auto]">
                <input v-model="search" class="field" placeholder="Search questions, quizzes, answers..." type="search">
                <select v-model="categoryFilter" class="field lg:w-52">
                    <option value="all">All categories</option>
                    <option v-for="category in categories" :key="category.slug" :value="category.slug">{{ category.name }}</option>
                </select>
                <select v-model="quizFilter" class="field lg:w-56">
                    <option value="all">All quizzes</option>
                    <option v-for="quiz in quizzes" :key="quiz.id" :value="String(quiz.id)">{{ quiz.title }}</option>
                </select>
                <select v-model="statusFilter" class="field lg:w-44">
                    <option value="all">All status</option>
                    <option value="published">Published</option>
                    <option value="unpublished">Unpublished</option>
                </select>
            </section>

            <section class="table-shell">
                <div class="border-b border-heritage-line/50 p-6">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-2xl font-black text-heritage-ink">Question bank</h2>
                            <p class="mt-1 text-sm text-heritage-muted">{{ filteredQuestions.length }} of {{ questions.length }} questions shown.</p>
                        </div>
                        <AppBadge variant="gold">Read only</AppBadge>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1180px] text-left">
                        <thead class="table-heading">
                            <tr>
                                <th class="px-6 py-4 font-black">Question</th>
                                <th class="px-6 py-4 font-black">Quiz</th>
                                <th class="px-6 py-4 font-black">Correct answer</th>
                                <th class="px-6 py-4 font-black">Answers</th>
                                <th class="px-6 py-4 font-black">Points</th>
                                <th class="px-6 py-4 font-black">Status</th>
                                <th class="px-6 py-4 font-black">Sort</th>
                                <th class="px-6 py-4 font-black">Updated</th>
                                <th class="px-6 py-4 font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="question in filteredQuestions" :key="question.id" class="border-t border-heritage-line/40 align-top">
                                <td class="max-w-[360px] px-6 py-4">
                                    <p class="font-bold leading-snug text-heritage-ink">{{ question.question_en }}</p>
                                    <p v-if="question.question_mk" class="mt-2 text-xs font-semibold leading-snug text-heritage-muted">{{ question.question_mk }}</p>
                                    <p v-if="question.explanation_en" class="mt-3 line-clamp-2 text-xs font-semibold text-heritage-muted">{{ question.explanation_en }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-heritage-ink">{{ question.quiz_title_en }}</p>
                                    <p class="text-xs font-semibold text-heritage-muted">{{ question.category_name_en }}</p>
                                </td>
                                <td class="max-w-[260px] px-6 py-4">
                                    <p class="font-bold text-heritage-ink">{{ question.correct_answer_en || 'No answer marked' }}</p>
                                    <p v-if="question.correct_answer_mk" class="mt-1 text-xs font-semibold text-heritage-muted">{{ question.correct_answer_mk }}</p>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ question.answers_count }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ question.points }}</td>
                                <td class="px-6 py-4">
                                    <AppBadge :variant="question.is_published ? 'green' : 'neutral'">{{ question.is_published ? 'Published' : 'Unpublished' }}</AppBadge>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ question.sort_order }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ formatDate(question.updated_at) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <button class="button-soft rounded-xl px-3 py-2 text-xs font-black" type="button">Edit soon</button>
                                        <button class="rounded-xl bg-heritage-red-faint px-3 py-2 text-xs font-black text-heritage-red" type="button">Delete soon</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!filteredQuestions.length" class="p-6 text-heritage-muted">No questions match the current filters.</div>
            </section>
        </template>
    </AdminLayout>
</template>
