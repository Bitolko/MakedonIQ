<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    createAdminCategory,
    deleteAdminCategory,
    getAdminCategories,
    updateAdminCategory,
} from '../../api/makedoniq';

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const success = ref('');
const categories = ref([]);
const search = ref('');
const statusFilter = ref('all');
const showForm = ref(false);
const editingCategory = ref(null);
const formErrors = ref({});

const blankForm = () => ({
    name_en: '',
    name_mk: '',
    slug: '',
    description_en: '',
    description_mk: '',
    icon: '',
    sort_order: 0,
    is_published: true,
});

const form = ref(blankForm());

onMounted(() => {
    loadCategories();
});

const filteredCategories = computed(() => {
    const needle = search.value.trim().toLowerCase();

    return categories.value.filter((category) => {
        const matchesSearch = !needle
            || category.name_en.toLowerCase().includes(needle)
            || (category.name_mk || '').toLowerCase().includes(needle)
            || category.slug.toLowerCase().includes(needle);
        const matchesStatus = statusFilter.value === 'all'
            || (statusFilter.value === 'published' && category.is_published)
            || (statusFilter.value === 'unpublished' && !category.is_published);

        return matchesSearch && matchesStatus;
    });
});

async function loadCategories(showLoading = true) {
    if (showLoading) {
        loading.value = true;
    }

    try {
        const response = await getAdminCategories();
        categories.value = response.data || [];
        error.value = '';
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to manage categories.'
            : caughtError.message || 'Unable to load categories.';
    } finally {
        if (showLoading) {
            loading.value = false;
        }
    }
}

function openCreateForm() {
    editingCategory.value = null;
    form.value = blankForm();
    formErrors.value = {};
    success.value = '';
    error.value = '';
    showForm.value = true;
}

function openEditForm(category) {
    editingCategory.value = category;
    form.value = {
        name_en: category.name_en || '',
        name_mk: category.name_mk || '',
        slug: category.slug || '',
        description_en: category.description_en || '',
        description_mk: category.description_mk || '',
        icon: category.icon || '',
        sort_order: category.sort_order ?? 0,
        is_published: Boolean(category.is_published),
    };
    formErrors.value = {};
    success.value = '';
    error.value = '';
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingCategory.value = null;
    form.value = blankForm();
    formErrors.value = {};
}

async function saveCategory() {
    saving.value = true;
    error.value = '';
    success.value = '';
    formErrors.value = {};

    try {
        const payload = categoryPayload(form.value);
        if (editingCategory.value) {
            await updateAdminCategory(editingCategory.value.id, payload);
            success.value = 'Category updated.';
        } else {
            await createAdminCategory(payload);
            success.value = 'Category created.';
        }

        closeForm();
        await loadCategories(false);
    } catch (caughtError) {
        formErrors.value = caughtError.payload?.errors || {};
        error.value = caughtError.message || 'Unable to save category.';
    } finally {
        saving.value = false;
    }
}

async function togglePublished(category) {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await updateAdminCategory(category.id, categoryPayload({
            ...category,
            is_published: !category.is_published,
        }));
        success.value = category.is_published ? 'Category unpublished.' : 'Category published.';
        await loadCategories(false);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to update category status.';
    } finally {
        saving.value = false;
    }
}

async function removeCategory(category) {
    if (!window.confirm(`Delete "${category.name_en}"? This is only allowed when the category has no quizzes.`)) {
        return;
    }

    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await deleteAdminCategory(category.id);
        success.value = 'Category deleted.';
        await loadCategories(false);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to delete category.';
        formErrors.value = caughtError.payload?.errors || {};
    } finally {
        saving.value = false;
    }
}

function categoryPayload(source) {
    return {
        name_en: source.name_en,
        name_mk: nullableString(source.name_mk),
        slug: nullableString(source.slug),
        description_en: nullableString(source.description_en),
        description_mk: nullableString(source.description_mk),
        icon: nullableString(source.icon),
        sort_order: numberOrZero(source.sort_order),
        is_published: Boolean(source.is_published),
    };
}

function nullableString(value) {
    const text = String(value ?? '').trim();

    return text.length ? text : null;
}

function numberOrZero(value) {
    if (value === '' || value === null || value === undefined) {
        return 0;
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
                <h1 class="text-4xl font-black text-heritage-ink">Admin Categories</h1>
                <p class="mt-2 text-heritage-muted">Manage quiz categories, publication status, slugs, and ordering.</p>
            </div>
            <PrimaryButton variant="soft" @click="openCreateForm">Add category</PrimaryButton>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading categories</h2>
            <p class="mt-2 text-heritage-muted">Fetching category inventory and dependent content counts.</p>
        </article>

        <article v-else-if="error && !categories.length" class="section-panel">
            <AppBadge variant="red">Admin access</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load categories</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <template v-else>
            <div v-if="success" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                {{ success }}
            </div>
            <div v-if="error" class="mb-5 rounded-2xl border border-heritage-red/20 bg-heritage-red-faint px-5 py-4 text-sm font-bold text-heritage-red-dark">
                {{ error }}
            </div>

            <form v-if="showForm" class="section-panel mb-6 grid gap-5" @submit.prevent="saveCategory">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <AppBadge variant="gold">{{ editingCategory ? 'Edit category' : 'New category' }}</AppBadge>
                        <h2 class="mt-3 text-2xl font-black text-heritage-ink">{{ editingCategory ? editingCategory.name_en : 'Create category' }}</h2>
                    </div>
                    <button class="button-ghost rounded-2xl px-5 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Name English</span>
                        <input v-model="form.name_en" class="field mt-2" type="text">
                        <span v-if="fieldError('name_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('name_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Name Macedonian</span>
                        <input v-model="form.name_mk" class="field mt-2" type="text">
                        <span v-if="fieldError('name_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('name_mk') }}</span>
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-[1fr_10rem_10rem]">
                    <label class="block">
                        <span class="label">Slug optional</span>
                        <input v-model="form.slug" class="field mt-2" placeholder="Auto-generated from English name" type="text">
                        <span v-if="fieldError('slug')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('slug') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Icon</span>
                        <input v-model="form.icon" class="field mt-2" type="text">
                        <span v-if="fieldError('icon')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('icon') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Sort order</span>
                        <input v-model.number="form.sort_order" class="field mt-2" min="0" type="number">
                        <span v-if="fieldError('sort_order')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('sort_order') }}</span>
                    </label>
                </div>

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
                    <PrimaryButton type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save category' }}</PrimaryButton>
                    <button class="button-ghost rounded-2xl px-6 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>
            </form>

            <section class="mb-5 grid gap-3 rounded-[1.5rem] bg-white p-4 shadow-card md:grid-cols-[1fr_auto]">
                <input v-model="search" class="field" placeholder="Search categories..." type="search">
                <select v-model="statusFilter" class="field md:w-44">
                    <option value="all">All status</option>
                    <option value="published">Published</option>
                    <option value="unpublished">Unpublished</option>
                </select>
            </section>

            <section class="table-shell">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left">
                        <thead class="table-heading">
                            <tr>
                                <th class="px-6 py-4 font-black">Category</th>
                                <th class="px-6 py-4 font-black">Slug</th>
                                <th class="px-6 py-4 font-black">Quizzes</th>
                                <th class="px-6 py-4 font-black">Questions</th>
                                <th class="px-6 py-4 font-black">Status</th>
                                <th class="px-6 py-4 font-black">Sort</th>
                                <th class="px-6 py-4 font-black">Updated</th>
                                <th class="px-6 py-4 font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in filteredCategories" :key="category.id" class="border-t border-heritage-line/40">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-heritage-ink">{{ category.name_en }}</p>
                                    <p class="text-xs font-semibold text-heritage-muted">{{ category.name_mk }}</p>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ category.slug }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ category.published_quizzes_count }} / {{ category.quizzes_count }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ category.questions_count }}</td>
                                <td class="px-6 py-4">
                                    <AppBadge :variant="category.is_published ? 'green' : 'neutral'">{{ category.is_published ? 'Published' : 'Unpublished' }}</AppBadge>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ category.sort_order }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ formatDate(category.updated_at) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button class="button-soft rounded-xl px-3 py-2 text-xs font-black" type="button" :disabled="saving" @click="openEditForm(category)">Edit</button>
                                        <button class="rounded-xl bg-heritage-gold-faint px-3 py-2 text-xs font-black text-heritage-gold-deep" type="button" :disabled="saving" @click="togglePublished(category)">
                                            {{ category.is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <button class="rounded-xl bg-heritage-red-faint px-3 py-2 text-xs font-black text-heritage-red" type="button" :disabled="saving" @click="removeCategory(category)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!filteredCategories.length" class="p-6 text-heritage-muted">No categories match the current filters.</div>
            </section>
        </template>
    </AdminLayout>
</template>
