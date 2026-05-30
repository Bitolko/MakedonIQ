<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    createAdminQuiz,
    deleteAdminQuiz,
    getAdminCategories,
    getAdminQuizzes,
    updateAdminQuiz,
} from '../../api/makedoniq';

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const success = ref('');
const quizzes = ref([]);
const categories = ref([]);
const search = ref('');
const statusFilter = ref('all');
const difficultyFilter = ref('all');
const categoryFilter = ref('all');
const showForm = ref(false);
const editingQuiz = ref(null);
const formErrors = ref({});

const blankForm = (categoryId = '') => ({
    category_id: categoryId,
    title_en: '',
    title_mk: '',
    slug: '',
    description_en: '',
    description_mk: '',
    difficulty: 'beginner',
    estimated_minutes: '',
    points_per_question: 10,
    sort_order: 0,
    is_published: true,
});

const form = ref(blankForm());

onMounted(() => {
    loadData();
});

const difficulties = computed(() => ['beginner', 'intermediate', 'advanced']);

const filteredQuizzes = computed(() => {
    const needle = search.value.trim().toLowerCase();

    return quizzes.value.filter((quiz) => {
        const matchesSearch = !needle
            || quiz.title_en.toLowerCase().includes(needle)
            || (quiz.title_mk || '').toLowerCase().includes(needle)
            || quiz.category_name_en.toLowerCase().includes(needle)
            || quiz.slug.toLowerCase().includes(needle);
        const matchesStatus = statusFilter.value === 'all'
            || (statusFilter.value === 'published' && quiz.is_published)
            || (statusFilter.value === 'unpublished' && !quiz.is_published);
        const matchesDifficulty = difficultyFilter.value === 'all' || quiz.difficulty === difficultyFilter.value;
        const matchesCategory = categoryFilter.value === 'all' || Number(categoryFilter.value) === quiz.category_id;

        return matchesSearch && matchesStatus && matchesDifficulty && matchesCategory;
    });
});

async function loadData(showLoading = true) {
    if (showLoading) {
        loading.value = true;
    }

    try {
        const [quizResponse, categoryResponse] = await Promise.all([
            getAdminQuizzes(),
            getAdminCategories(),
        ]);
        quizzes.value = quizResponse.data || [];
        categories.value = categoryResponse.data || [];
        error.value = '';
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to manage quizzes.'
            : caughtError.message || 'Unable to load quizzes.';
    } finally {
        if (showLoading) {
            loading.value = false;
        }
    }
}

function openCreateForm() {
    editingQuiz.value = null;
    form.value = blankForm(categories.value[0]?.id || '');
    formErrors.value = {};
    success.value = '';
    error.value = '';
    showForm.value = true;
}

function openEditForm(quiz) {
    editingQuiz.value = quiz;
    form.value = {
        category_id: quiz.category_id,
        title_en: quiz.title_en || '',
        title_mk: quiz.title_mk || '',
        slug: quiz.slug || '',
        description_en: quiz.description_en || '',
        description_mk: quiz.description_mk || '',
        difficulty: quiz.difficulty || 'beginner',
        estimated_minutes: quiz.estimated_minutes ?? '',
        points_per_question: quiz.points_per_question ?? 10,
        sort_order: quiz.sort_order ?? 0,
        is_published: Boolean(quiz.is_published),
    };
    formErrors.value = {};
    success.value = '';
    error.value = '';
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingQuiz.value = null;
    form.value = blankForm(categories.value[0]?.id || '');
    formErrors.value = {};
}

async function saveQuiz() {
    saving.value = true;
    error.value = '';
    success.value = '';
    formErrors.value = {};

    try {
        const payload = quizPayload(form.value);
        if (editingQuiz.value) {
            await updateAdminQuiz(editingQuiz.value.id, payload);
            success.value = 'Quiz updated.';
        } else {
            await createAdminQuiz(payload);
            success.value = 'Quiz created.';
        }

        closeForm();
        await loadData(false);
    } catch (caughtError) {
        formErrors.value = caughtError.payload?.errors || {};
        error.value = caughtError.message || 'Unable to save quiz.';
    } finally {
        saving.value = false;
    }
}

async function togglePublished(quiz) {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await updateAdminQuiz(quiz.id, quizPayload({
            ...quiz,
            is_published: !quiz.is_published,
        }));
        success.value = quiz.is_published ? 'Quiz unpublished.' : 'Quiz published.';
        await loadData(false);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to update quiz status.';
    } finally {
        saving.value = false;
    }
}

async function removeQuiz(quiz) {
    if (!window.confirm(`Delete "${quiz.title_en}"? This is only allowed when the quiz has no questions or attempts.`)) {
        return;
    }

    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await deleteAdminQuiz(quiz.id);
        success.value = 'Quiz deleted.';
        await loadData(false);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to delete quiz.';
        formErrors.value = caughtError.payload?.errors || {};
    } finally {
        saving.value = false;
    }
}

function quizPayload(source) {
    return {
        category_id: source.category_id === '' ? null : Number(source.category_id),
        title_en: source.title_en,
        title_mk: nullableString(source.title_mk),
        slug: nullableString(source.slug),
        description_en: nullableString(source.description_en),
        description_mk: nullableString(source.description_mk),
        difficulty: source.difficulty,
        estimated_minutes: nullableNumber(source.estimated_minutes),
        points_per_question: numberOrDefault(source.points_per_question, 10),
        sort_order: numberOrDefault(source.sort_order, 0),
        is_published: Boolean(source.is_published),
    };
}

function nullableString(value) {
    const text = String(value ?? '').trim();

    return text.length ? text : null;
}

function nullableNumber(value) {
    if (value === '' || value === null || value === undefined) {
        return null;
    }

    return Number(value);
}

function numberOrDefault(value, fallback) {
    if (value === '' || value === null || value === undefined) {
        return fallback;
    }

    return Number(value);
}

function fieldError(field) {
    return formErrors.value?.[field]?.[0] || '';
}

function formatPercentage(value) {
    if (value === null || value === undefined) {
        return 'n/a';
    }

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
                <h1 class="text-4xl font-black text-heritage-ink">Admin Quizzes</h1>
                <p class="mt-2 text-heritage-muted">Create, edit, publish, and safely manage quiz records.</p>
            </div>
            <PrimaryButton variant="soft" @click="openCreateForm">Add quiz</PrimaryButton>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading quizzes</h2>
            <p class="mt-2 text-heritage-muted">Fetching quiz content, categories, and attempt summaries.</p>
        </article>

        <article v-else-if="error && !quizzes.length" class="section-panel">
            <AppBadge variant="red">Admin access</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load quizzes</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <template v-else>
            <div v-if="success" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                {{ success }}
            </div>
            <div v-if="error" class="mb-5 rounded-2xl border border-heritage-red/20 bg-heritage-red-faint px-5 py-4 text-sm font-bold text-heritage-red-dark">
                {{ error }}
            </div>

            <form v-if="showForm" class="section-panel mb-6 grid gap-5" @submit.prevent="saveQuiz">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <AppBadge variant="gold">{{ editingQuiz ? 'Edit quiz' : 'New quiz' }}</AppBadge>
                        <h2 class="mt-3 text-2xl font-black text-heritage-ink">{{ editingQuiz ? editingQuiz.title_en : 'Create quiz' }}</h2>
                    </div>
                    <button class="button-ghost rounded-2xl px-5 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>

                <label class="block">
                    <span class="label">Category</span>
                    <select v-model.number="form.category_id" class="field mt-2">
                        <option value="">Select category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name_en }} ({{ category.is_published ? 'published' : 'unpublished' }})
                        </option>
                    </select>
                    <span v-if="fieldError('category_id')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('category_id') }}</span>
                </label>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Title English</span>
                        <input v-model="form.title_en" class="field mt-2" type="text">
                        <span v-if="fieldError('title_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('title_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Title Macedonian</span>
                        <input v-model="form.title_mk" class="field mt-2" type="text">
                        <span v-if="fieldError('title_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('title_mk') }}</span>
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-[1fr_12rem_12rem_10rem]">
                    <label class="block">
                        <span class="label">Slug optional</span>
                        <input v-model="form.slug" class="field mt-2" placeholder="Auto-generated from English title" type="text">
                        <span v-if="fieldError('slug')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('slug') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Difficulty</span>
                        <select v-model="form.difficulty" class="field mt-2">
                            <option v-for="difficulty in difficulties" :key="difficulty" :value="difficulty">{{ difficulty }}</option>
                        </select>
                        <span v-if="fieldError('difficulty')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('difficulty') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Estimated minutes</span>
                        <input v-model.number="form.estimated_minutes" class="field mt-2" min="1" max="300" type="number">
                        <span v-if="fieldError('estimated_minutes')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('estimated_minutes') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Sort order</span>
                        <input v-model.number="form.sort_order" class="field mt-2" min="0" type="number">
                        <span v-if="fieldError('sort_order')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('sort_order') }}</span>
                    </label>
                </div>

                <label class="block">
                    <span class="label">Points per question</span>
                    <input v-model.number="form.points_per_question" class="field mt-2 max-w-xs" min="1" max="100" type="number">
                    <span v-if="fieldError('points_per_question')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('points_per_question') }}</span>
                </label>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Description English</span>
                        <textarea v-model="form.description_en" class="field mt-2 min-h-28" />
                        <span v-if="fieldError('description_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('description_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Description Macedonian</span>
                        <textarea v-model="form.description_mk" class="field mt-2 min-h-28" />
                        <span v-if="fieldError('description_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('description_mk') }}</span>
                    </label>
                </div>

                <label class="inline-flex items-center gap-3 text-sm font-black text-heritage-ink">
                    <input v-model="form.is_published" class="h-5 w-5 rounded border-heritage-line text-heritage-red" type="checkbox">
                    Published publicly
                </label>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <PrimaryButton type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save quiz' }}</PrimaryButton>
                    <button class="button-ghost rounded-2xl px-6 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>
            </form>

            <section class="mb-5 grid gap-3 rounded-[1.5rem] bg-white p-4 shadow-card lg:grid-cols-[1fr_auto_auto_auto]">
                <input v-model="search" class="field" placeholder="Search quizzes..." type="search">
                <select v-model="categoryFilter" class="field lg:w-52">
                    <option value="all">All categories</option>
                    <option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.name_en }}</option>
                </select>
                <select v-model="difficultyFilter" class="field lg:w-48">
                    <option value="all">All difficulty</option>
                    <option v-for="difficulty in difficulties" :key="difficulty" :value="difficulty">{{ difficulty }}</option>
                </select>
                <select v-model="statusFilter" class="field lg:w-44">
                    <option value="all">All status</option>
                    <option value="published">Published</option>
                    <option value="unpublished">Unpublished</option>
                </select>
            </section>

            <section class="table-shell">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1180px] text-left">
                        <thead class="table-heading">
                            <tr>
                                <th class="px-6 py-4 font-black">Quiz title</th>
                                <th class="px-6 py-4 font-black">Category</th>
                                <th class="px-6 py-4 font-black">Difficulty</th>
                                <th class="px-6 py-4 font-black">Details</th>
                                <th class="px-6 py-4 font-black">Questions</th>
                                <th class="px-6 py-4 font-black">Attempts</th>
                                <th class="px-6 py-4 font-black">Avg score</th>
                                <th class="px-6 py-4 font-black">Status</th>
                                <th class="px-6 py-4 font-black">Updated</th>
                                <th class="px-6 py-4 font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="quiz in filteredQuizzes" :key="quiz.id" class="border-t border-heritage-line/40">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-heritage-ink">{{ quiz.title_en }}</p>
                                    <p class="text-xs font-semibold text-heritage-muted">{{ quiz.title_mk }}</p>
                                    <p class="mt-1 text-xs font-semibold text-heritage-muted">{{ quiz.slug }} / sort {{ quiz.sort_order }}</p>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ quiz.category_name_en }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ quiz.difficulty }}</td>
                                <td class="px-6 py-4 text-heritage-muted">
                                    <p>{{ quiz.estimated_minutes || 'Self-paced' }} min</p>
                                    <p class="text-xs font-semibold">{{ quiz.points_per_question }} pts/question</p>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ quiz.published_questions_count }} / {{ quiz.questions_count }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ quiz.attempts_count }}</td>
                                <td class="px-6 py-4 font-black text-heritage-red">{{ formatPercentage(quiz.average_percentage) }}</td>
                                <td class="px-6 py-4">
                                    <AppBadge :variant="quiz.is_published ? 'green' : 'neutral'">{{ quiz.is_published ? 'Published' : 'Unpublished' }}</AppBadge>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ formatDate(quiz.updated_at) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="rounded-xl bg-heritage-navy-soft px-3 py-2 text-xs font-black text-heritage-navy" :href="`/admin/questions?quiz=${quiz.id}`">Questions</a>
                                        <button class="button-soft rounded-xl px-3 py-2 text-xs font-black" type="button" :disabled="saving" @click="openEditForm(quiz)">Edit</button>
                                        <button class="rounded-xl bg-heritage-gold-faint px-3 py-2 text-xs font-black text-heritage-gold-deep" type="button" :disabled="saving" @click="togglePublished(quiz)">
                                            {{ quiz.is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <button class="rounded-xl bg-heritage-red-faint px-3 py-2 text-xs font-black text-heritage-red" type="button" :disabled="saving" @click="removeQuiz(quiz)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!filteredQuizzes.length" class="p-6 text-heritage-muted">No quizzes match the current filters.</div>
            </section>
        </template>
    </AdminLayout>
</template>
