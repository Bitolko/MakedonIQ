<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    createAdminLesson,
    deleteAdminLesson,
    getAdminCategories,
    getAdminLessons,
    updateAdminLesson,
} from '../../api/makedoniq';

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const success = ref('');
const lessons = ref([]);
const categories = ref([]);
const search = ref('');
const statusFilter = ref('all');
const difficultyFilter = ref('all');
const categoryFilter = ref('all');
const showForm = ref(false);
const editingLesson = ref(null);
const formErrors = ref({});

const blankForm = (categoryId = '') => ({
    category_id: categoryId,
    title_en: '',
    title_mk: '',
    slug: '',
    summary_en: '',
    summary_mk: '',
    content_en: '',
    content_mk: '',
    difficulty: 'beginner',
    estimated_minutes: '',
    sort_order: 0,
    is_published: true,
    is_demo: false,
});

const form = ref(blankForm());
const difficulties = computed(() => ['beginner', 'intermediate', 'advanced']);

const filteredLessons = computed(() => {
    const needle = search.value.trim().toLowerCase();

    return lessons.value.filter((lesson) => {
        const matchesSearch = !needle
            || lesson.title_en.toLowerCase().includes(needle)
            || (lesson.title_mk || '').toLowerCase().includes(needle)
            || lesson.category_name_en.toLowerCase().includes(needle)
            || lesson.slug.toLowerCase().includes(needle);
        const matchesStatus = statusFilter.value === 'all'
            || (statusFilter.value === 'published' && lesson.is_published)
            || (statusFilter.value === 'unpublished' && !lesson.is_published);
        const matchesDifficulty = difficultyFilter.value === 'all' || lesson.difficulty === difficultyFilter.value;
        const matchesCategory = categoryFilter.value === 'all' || Number(categoryFilter.value) === lesson.category_id;

        return matchesSearch && matchesStatus && matchesDifficulty && matchesCategory;
    });
});

onMounted(() => {
    loadData();
});

async function loadData(showLoading = true) {
    if (showLoading) {
        loading.value = true;
    }

    try {
        const [lessonResponse, categoryResponse] = await Promise.all([
            getAdminLessons(),
            getAdminCategories(),
        ]);

        lessons.value = lessonResponse.data || [];
        categories.value = categoryResponse.data || [];
        error.value = '';
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to manage lessons.'
            : caughtError.message || 'Unable to load lessons.';
    } finally {
        if (showLoading) {
            loading.value = false;
        }
    }
}

function openCreateForm() {
    editingLesson.value = null;
    form.value = blankForm(categories.value[0]?.id || '');
    formErrors.value = {};
    success.value = '';
    error.value = '';
    showForm.value = true;
}

function openEditForm(lesson) {
    editingLesson.value = lesson;
    form.value = {
        category_id: lesson.category_id,
        title_en: lesson.title_en || '',
        title_mk: lesson.title_mk || '',
        slug: lesson.slug || '',
        summary_en: lesson.summary_en || '',
        summary_mk: lesson.summary_mk || '',
        content_en: lesson.content_en || '',
        content_mk: lesson.content_mk || '',
        difficulty: lesson.difficulty || 'beginner',
        estimated_minutes: lesson.estimated_minutes ?? '',
        sort_order: lesson.sort_order ?? 0,
        is_published: Boolean(lesson.is_published),
        is_demo: Boolean(lesson.is_demo),
    };
    formErrors.value = {};
    success.value = '';
    error.value = '';
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingLesson.value = null;
    form.value = blankForm(categories.value[0]?.id || '');
    formErrors.value = {};
}

async function saveLesson() {
    saving.value = true;
    error.value = '';
    success.value = '';
    formErrors.value = {};

    try {
        const payload = lessonPayload(form.value);
        if (editingLesson.value) {
            await updateAdminLesson(editingLesson.value.id, payload);
            success.value = 'Lesson updated.';
        } else {
            await createAdminLesson(payload);
            success.value = 'Lesson created.';
        }

        closeForm();
        await loadData(false);
    } catch (caughtError) {
        formErrors.value = caughtError.payload?.errors || {};
        error.value = caughtError.message || 'Unable to save lesson.';
    } finally {
        saving.value = false;
    }
}

async function togglePublished(lesson) {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await updateAdminLesson(lesson.id, lessonPayload({
            ...lesson,
            is_published: !lesson.is_published,
        }));
        success.value = lesson.is_published ? 'Lesson unpublished.' : 'Lesson published.';
        await loadData(false);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to update lesson status.';
    } finally {
        saving.value = false;
    }
}

async function removeLesson(lesson) {
    if (!window.confirm(`Delete "${lesson.title_en}"? This is only allowed when no quizzes are linked to the lesson.`)) {
        return;
    }

    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await deleteAdminLesson(lesson.id);
        success.value = 'Lesson deleted.';
        await loadData(false);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to delete lesson.';
        formErrors.value = caughtError.payload?.errors || {};
    } finally {
        saving.value = false;
    }
}

function lessonPayload(source) {
    return {
        category_id: source.category_id === '' ? null : Number(source.category_id),
        title_en: source.title_en,
        title_mk: nullableString(source.title_mk),
        slug: nullableString(source.slug),
        summary_en: nullableString(source.summary_en),
        summary_mk: nullableString(source.summary_mk),
        content_en: source.content_en,
        content_mk: nullableString(source.content_mk),
        difficulty: source.difficulty,
        estimated_minutes: nullableNumber(source.estimated_minutes),
        sort_order: numberOrDefault(source.sort_order, 0),
        is_published: Boolean(source.is_published),
        is_demo: Boolean(source.is_demo),
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
                <h1 class="text-4xl font-black text-heritage-ink">Admin Lessons</h1>
                <p class="mt-2 text-heritage-muted">Create, edit, publish, and safely manage bilingual Learn content.</p>
            </div>
            <PrimaryButton variant="soft" @click="openCreateForm">Add lesson</PrimaryButton>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading lessons</h2>
            <p class="mt-2 text-heritage-muted">Fetching lesson content, categories, and linked quiz counts.</p>
        </article>

        <article v-else-if="error && !lessons.length" class="section-panel">
            <AppBadge variant="red">Admin access</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load lessons</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <template v-else>
            <div v-if="success" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                {{ success }}
            </div>
            <div v-if="error" class="mb-5 rounded-2xl border border-heritage-red/20 bg-heritage-red-faint px-5 py-4 text-sm font-bold text-heritage-red-dark">
                {{ error }}
            </div>

            <form v-if="showForm" class="section-panel mb-6 grid gap-5" @submit.prevent="saveLesson">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <AppBadge variant="gold">{{ editingLesson ? 'Edit lesson' : 'New lesson' }}</AppBadge>
                        <h2 class="mt-3 text-2xl font-black text-heritage-ink">{{ editingLesson ? editingLesson.title_en : 'Create lesson' }}</h2>
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

                <div class="grid gap-5 lg:grid-cols-[1fr_12rem_12rem_10rem]">
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

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Summary English</span>
                        <textarea v-model="form.summary_en" class="field mt-2 min-h-24" />
                        <span v-if="fieldError('summary_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('summary_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Summary Macedonian</span>
                        <textarea v-model="form.summary_mk" class="field mt-2 min-h-24" />
                        <span v-if="fieldError('summary_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('summary_mk') }}</span>
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Content English</span>
                        <textarea v-model="form.content_en" class="field mt-2 min-h-64" />
                        <span v-if="fieldError('content_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('content_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Content Macedonian</span>
                        <textarea v-model="form.content_mk" class="field mt-2 min-h-64" />
                        <span v-if="fieldError('content_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('content_mk') }}</span>
                    </label>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                    <label class="inline-flex items-center gap-3 text-sm font-black text-heritage-ink">
                        <input v-model="form.is_published" class="h-5 w-5 rounded border-heritage-line text-heritage-red" type="checkbox">
                        Published publicly
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm font-black text-heritage-ink">
                        <input v-model="form.is_demo" class="h-5 w-5 rounded border-heritage-line text-heritage-red" type="checkbox">
                        Demo lesson
                    </label>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <PrimaryButton type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save lesson' }}</PrimaryButton>
                    <button class="button-ghost rounded-2xl px-6 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>
            </form>

            <section class="mb-5 grid gap-3 rounded-[1.5rem] bg-white p-4 shadow-card lg:grid-cols-[1fr_auto_auto_auto]">
                <input v-model="search" class="field" placeholder="Search lessons..." type="search">
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
                    <table class="w-full min-w-[1120px] text-left">
                        <thead class="table-heading">
                            <tr>
                                <th class="px-6 py-4 font-black">Lesson</th>
                                <th class="px-6 py-4 font-black">Category</th>
                                <th class="px-6 py-4 font-black">Difficulty</th>
                                <th class="px-6 py-4 font-black">Time</th>
                                <th class="px-6 py-4 font-black">Linked quizzes</th>
                                <th class="px-6 py-4 font-black">Status</th>
                                <th class="px-6 py-4 font-black">Sort</th>
                                <th class="px-6 py-4 font-black">Updated</th>
                                <th class="px-6 py-4 font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lesson in filteredLessons" :key="lesson.id" class="border-t border-heritage-line/40 align-top">
                                <td class="max-w-[340px] px-6 py-4">
                                    <p class="font-bold text-heritage-ink">{{ lesson.title_en }}</p>
                                    <p class="text-xs font-semibold text-heritage-muted">{{ lesson.title_mk }}</p>
                                    <p class="mt-1 text-xs font-semibold text-heritage-muted">{{ lesson.slug }}</p>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ lesson.category_name_en }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ lesson.difficulty }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span></td>
                                <td class="px-6 py-4 text-heritage-muted">{{ lesson.linked_quizzes_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <AppBadge :variant="lesson.is_published ? 'green' : 'neutral'">{{ lesson.is_published ? 'Published' : 'Unpublished' }}</AppBadge>
                                        <AppBadge v-if="lesson.is_demo" variant="gold">Demo</AppBadge>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ lesson.sort_order }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ formatDate(lesson.updated_at) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button class="button-soft rounded-xl px-3 py-2 text-xs font-black" type="button" :disabled="saving" @click="openEditForm(lesson)">Edit</button>
                                        <button class="rounded-xl bg-heritage-gold-faint px-3 py-2 text-xs font-black text-heritage-gold-deep" type="button" :disabled="saving" @click="togglePublished(lesson)">
                                            {{ lesson.is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <button class="rounded-xl bg-heritage-red-faint px-3 py-2 text-xs font-black text-heritage-red" type="button" :disabled="saving" @click="removeLesson(lesson)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!filteredLessons.length" class="p-6 text-heritage-muted">No lessons match the current filters.</div>
            </section>
        </template>
    </AdminLayout>
</template>
