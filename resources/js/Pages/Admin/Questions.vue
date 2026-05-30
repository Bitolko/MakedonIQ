<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    createAdminQuestion,
    deleteAdminQuestion,
    getAdminQuizQuestions,
    getAdminQuizzes,
    updateAdminQuestion,
} from '../../api/makedoniq';

const loadingQuizzes = ref(true);
const loadingQuestions = ref(false);
const saving = ref(false);
const error = ref('');
const success = ref('');
const quizzes = ref([]);
const questions = ref([]);
const selectedQuizId = ref('');
const showForm = ref(false);
const editingQuestion = ref(null);
const formErrors = ref({});
const correctIndex = ref(0);

const blankAnswers = () => [1, 2, 3, 4].map((sortOrder, index) => ({
    answer_en: '',
    answer_mk: '',
    sort_order: sortOrder,
    is_correct: index === 0,
}));

const blankForm = () => ({
    question_en: '',
    question_mk: '',
    explanation_en: '',
    explanation_mk: '',
    sort_order: nextSortOrder(),
    points: '',
    is_published: true,
    answers: blankAnswers(),
});

const form = ref(blankForm());

const selectedQuiz = computed(() => quizzes.value.find((quiz) => quiz.id === Number(selectedQuizId.value)) || null);
const hasSelectedQuiz = computed(() => Boolean(selectedQuiz.value));

onMounted(async () => {
    await loadQuizzes();
});

watch(selectedQuizId, async (quizId) => {
    if (!quizId) {
        questions.value = [];
        return;
    }

    updateUrlQuizParam(quizId);
    closeForm();
    await loadQuestions();
});

async function loadQuizzes() {
    loadingQuizzes.value = true;
    error.value = '';

    try {
        const response = await getAdminQuizzes();
        quizzes.value = response.data || [];
        const requestedQuizId = new URLSearchParams(window.location.search).get('quiz');
        const requestedExists = quizzes.value.some((quiz) => String(quiz.id) === String(requestedQuizId));
        selectedQuizId.value = requestedExists ? requestedQuizId : String(quizzes.value[0]?.id || '');

        if (!selectedQuizId.value) {
            questions.value = [];
        }
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to manage questions.'
            : caughtError.message || 'Unable to load quizzes.';
    } finally {
        loadingQuizzes.value = false;
    }
}

async function loadQuestions() {
    if (!selectedQuizId.value) {
        questions.value = [];
        return;
    }

    loadingQuestions.value = true;
    error.value = '';

    try {
        const response = await getAdminQuizQuestions(selectedQuizId.value);
        questions.value = response.data || [];
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to manage questions.'
            : caughtError.message || 'Unable to load questions.';
    } finally {
        loadingQuestions.value = false;
    }
}

function openCreateForm() {
    editingQuestion.value = null;
    correctIndex.value = 0;
    form.value = blankForm();
    formErrors.value = {};
    error.value = '';
    success.value = '';
    showForm.value = true;
}

function openEditForm(question) {
    const answers = normalizedAnswers(question.answers);
    const existingCorrectIndex = answers.findIndex((answer) => answer.is_correct);

    editingQuestion.value = question;
    correctIndex.value = existingCorrectIndex >= 0 ? existingCorrectIndex : 0;
    form.value = {
        question_en: question.question_en || '',
        question_mk: question.question_mk || '',
        explanation_en: question.explanation_en || '',
        explanation_mk: question.explanation_mk || '',
        sort_order: question.sort_order ?? 0,
        points: question.points ?? '',
        is_published: Boolean(question.is_published),
        answers,
    };
    formErrors.value = {};
    error.value = '';
    success.value = '';
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingQuestion.value = null;
    form.value = blankForm();
    formErrors.value = {};
    correctIndex.value = 0;
}

async function saveQuestion() {
    if (!selectedQuizId.value) {
        error.value = 'Select a quiz before saving a question.';
        return;
    }

    saving.value = true;
    error.value = '';
    success.value = '';
    formErrors.value = {};

    try {
        const payload = questionPayload(form.value);
        if (editingQuestion.value) {
            await updateAdminQuestion(editingQuestion.value.id, payload);
            success.value = 'Question updated.';
        } else {
            await createAdminQuestion(selectedQuizId.value, payload);
            success.value = 'Question created.';
        }

        closeForm();
        await loadQuestions();
        await loadQuizzes();
    } catch (caughtError) {
        formErrors.value = caughtError.payload?.errors || {};
        error.value = caughtError.message || 'Unable to save question.';
    } finally {
        saving.value = false;
    }
}

async function togglePublished(question) {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        const answers = normalizedAnswers(question.answers);
        const answerCorrectIndex = Math.max(0, answers.findIndex((answer) => answer.is_correct));

        await updateAdminQuestion(question.id, questionPayload({
            ...question,
            is_published: !question.is_published,
            answers,
        }, answerCorrectIndex));
        success.value = question.is_published ? 'Question unpublished.' : 'Question published.';
        await loadQuestions();
        await loadQuizzes();
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to update question status.';
    } finally {
        saving.value = false;
    }
}

async function removeQuestion(question) {
    if (!window.confirm(`Delete this question? Questions used in attempts cannot be deleted.`)) {
        return;
    }

    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        await deleteAdminQuestion(question.id);
        success.value = 'Question deleted.';
        await loadQuestions();
        await loadQuizzes();
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to delete question.';
        formErrors.value = caughtError.payload?.errors || {};
    } finally {
        saving.value = false;
    }
}

function questionPayload(source, selectedCorrectIndex = correctIndex.value) {
    return {
        question_en: source.question_en,
        question_mk: nullableString(source.question_mk),
        explanation_en: nullableString(source.explanation_en),
        explanation_mk: nullableString(source.explanation_mk),
        sort_order: numberOrDefault(source.sort_order, 0),
        points: nullableNumber(source.points),
        is_published: Boolean(source.is_published),
        answers: normalizedAnswers(source.answers).map((answer, index) => ({
            answer_en: answer.answer_en,
            answer_mk: nullableString(answer.answer_mk),
            sort_order: index + 1,
            is_correct: index === selectedCorrectIndex,
        })),
    };
}

function normalizedAnswers(answers = []) {
    const rows = answers
        .slice(0, 4)
        .map((answer, index) => ({
            id: answer.id || null,
            answer_en: answer.answer_en || '',
            answer_mk: answer.answer_mk || '',
            sort_order: answer.sort_order ?? index + 1,
            is_correct: Boolean(answer.is_correct),
        }));

    while (rows.length < 4) {
        rows.push({
            id: null,
            answer_en: '',
            answer_mk: '',
            sort_order: rows.length + 1,
            is_correct: rows.length === 0,
        });
    }

    return rows.sort((first, second) => Number(first.sort_order) - Number(second.sort_order));
}

function nextSortOrder() {
    if (!questions.value.length) {
        return 1;
    }

    return Math.max(...questions.value.map((question) => Number(question.sort_order || 0))) + 1;
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

function updateUrlQuizParam(quizId) {
    const url = new URL(window.location.href);
    url.searchParams.set('quiz', quizId);
    window.history.replaceState({}, '', url);
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
                <h1 class="text-4xl font-black text-heritage-ink">Question Builder</h1>
                <p class="mt-2 text-heritage-muted">Manage bilingual questions and exactly four answer options for each quiz.</p>
            </div>
            <PrimaryButton variant="soft" :disabled="!hasSelectedQuiz" @click="openCreateForm">Add question</PrimaryButton>
        </section>

        <article v-if="loadingQuizzes" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading quizzes</h2>
            <p class="mt-2 text-heritage-muted">Preparing the question builder.</p>
        </article>

        <article v-else-if="error && !quizzes.length" class="section-panel">
            <AppBadge variant="red">Admin access</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load question builder</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <template v-else>
            <div v-if="success" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                {{ success }}
            </div>
            <div v-if="error" class="mb-5 rounded-2xl border border-heritage-red/20 bg-heritage-red-faint px-5 py-4 text-sm font-bold text-heritage-red-dark">
                {{ error }}
            </div>

            <section class="section-panel mb-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                    <label class="block">
                        <span class="label">Select quiz</span>
                        <select v-model="selectedQuizId" class="field mt-2">
                            <option value="">Select a quiz</option>
                            <option v-for="quiz in quizzes" :key="quiz.id" :value="String(quiz.id)">
                                {{ quiz.title_en }} / {{ quiz.category_name_en }}
                            </option>
                        </select>
                    </label>
                    <PrimaryButton variant="gold" :disabled="!hasSelectedQuiz" @click="openCreateForm">Add question</PrimaryButton>
                </div>
                <div v-if="selectedQuiz" class="mt-5 flex flex-wrap gap-3">
                    <AppBadge :variant="selectedQuiz.is_published ? 'green' : 'neutral'">{{ selectedQuiz.is_published ? 'Quiz published' : 'Quiz unpublished' }}</AppBadge>
                    <AppBadge variant="navy">{{ selectedQuiz.published_questions_count }} / {{ selectedQuiz.questions_count }} published questions</AppBadge>
                    <AppBadge variant="gold">{{ selectedQuiz.difficulty }}</AppBadge>
                </div>
            </section>

            <form v-if="showForm" class="section-panel mb-6 grid gap-6" @submit.prevent="saveQuestion">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <AppBadge variant="gold">{{ editingQuestion ? 'Edit question' : 'New question' }}</AppBadge>
                        <h2 class="mt-3 text-2xl font-black text-heritage-ink">{{ editingQuestion ? 'Update question' : 'Create question' }}</h2>
                        <p v-if="editingQuestion?.used_in_attempts" class="mt-2 text-sm font-bold text-heritage-red">
                            This question has attempt history. Answer changes are blocked; unpublish and create a replacement if needed.
                        </p>
                    </div>
                    <button class="button-ghost rounded-2xl px-5 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>

                <span v-if="fieldError('question')" class="block text-sm font-bold text-heritage-red">{{ fieldError('question') }}</span>
                <span v-if="fieldError('answers')" class="block text-sm font-bold text-heritage-red">{{ fieldError('answers') }}</span>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Question English</span>
                        <textarea v-model="form.question_en" class="field mt-2 min-h-28" />
                        <span v-if="fieldError('question_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('question_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Question Macedonian</span>
                        <textarea v-model="form.question_mk" class="field mt-2 min-h-28" />
                        <span v-if="fieldError('question_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('question_mk') }}</span>
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="label">Explanation English</span>
                        <textarea v-model="form.explanation_en" class="field mt-2 min-h-24" />
                        <span v-if="fieldError('explanation_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('explanation_en') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Explanation Macedonian</span>
                        <textarea v-model="form.explanation_mk" class="field mt-2 min-h-24" />
                        <span v-if="fieldError('explanation_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('explanation_mk') }}</span>
                    </label>
                </div>

                <div class="grid gap-5 md:grid-cols-[12rem_12rem_1fr] md:items-end">
                    <label class="block">
                        <span class="label">Points optional</span>
                        <input v-model.number="form.points" class="field mt-2" min="1" max="100" type="number">
                        <span v-if="fieldError('points')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('points') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Sort order</span>
                        <input v-model.number="form.sort_order" class="field mt-2" min="0" type="number">
                        <span v-if="fieldError('sort_order')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('sort_order') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-3 rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-ink">
                        <input v-model="form.is_published" class="h-5 w-5 rounded border-heritage-line text-heritage-red" type="checkbox">
                        Published publicly
                    </label>
                </div>

                <section class="grid gap-4">
                    <div>
                        <h3 class="text-xl font-black text-heritage-ink">Answer options</h3>
                        <p class="mt-1 text-sm font-semibold text-heritage-muted">Four fixed rows are required. Select exactly one correct answer.</p>
                    </div>

                    <div
                        v-for="(answer, index) in form.answers"
                        :key="index"
                        class="grid gap-4 rounded-2xl bg-heritage-panel p-4 lg:grid-cols-[auto_1fr_1fr]"
                    >
                        <label class="flex items-center gap-3 text-sm font-black text-heritage-ink">
                            <input v-model="correctIndex" class="h-5 w-5 border-heritage-line text-heritage-red" name="correct-answer" type="radio" :value="index">
                            Correct
                        </label>
                        <label class="block">
                            <span class="label">Answer {{ index + 1 }} English</span>
                            <input v-model="answer.answer_en" class="field mt-2 bg-white" type="text">
                            <span v-if="fieldError(`answers.${index}.answer_en`)" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(`answers.${index}.answer_en`) }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Answer {{ index + 1 }} Macedonian</span>
                            <input v-model="answer.answer_mk" class="field mt-2 bg-white" type="text">
                            <span v-if="fieldError(`answers.${index}.answer_mk`)" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(`answers.${index}.answer_mk`) }}</span>
                        </label>
                    </div>
                </section>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <PrimaryButton type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save question' }}</PrimaryButton>
                    <button class="button-ghost rounded-2xl px-6 py-3 text-sm font-black" type="button" @click="closeForm">Cancel</button>
                </div>
            </form>

            <article v-if="!hasSelectedQuiz" class="section-panel text-center">
                <h2 class="text-2xl font-black text-heritage-ink">No quizzes available</h2>
                <p class="mt-2 text-heritage-muted">Create a quiz before adding questions.</p>
                <PrimaryButton href="/admin/quizzes" class="mt-6" variant="gold">Go to quizzes</PrimaryButton>
            </article>

            <article v-else-if="loadingQuestions" class="section-panel">
                <AppBadge variant="navy">Loading</AppBadge>
                <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading questions</h2>
                <p class="mt-2 text-heritage-muted">Fetching questions and answer keys for this quiz.</p>
            </article>

            <section v-else class="table-shell">
                <div class="border-b border-heritage-line/50 p-6">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-2xl font-black text-heritage-ink">{{ selectedQuiz?.title_en }}</h2>
                            <p class="mt-1 text-sm text-heritage-muted">{{ questions.length }} questions in this quiz.</p>
                        </div>
                        <AppBadge variant="gold">Question builder</AppBadge>
                    </div>
                </div>

                <div v-if="questions.length" class="overflow-x-auto">
                    <table class="w-full min-w-[1120px] text-left">
                        <thead class="table-heading">
                            <tr>
                                <th class="px-6 py-4 font-black">Sort</th>
                                <th class="px-6 py-4 font-black">Question</th>
                                <th class="px-6 py-4 font-black">Correct answer</th>
                                <th class="px-6 py-4 font-black">Answers</th>
                                <th class="px-6 py-4 font-black">Points</th>
                                <th class="px-6 py-4 font-black">Status</th>
                                <th class="px-6 py-4 font-black">Updated</th>
                                <th class="px-6 py-4 font-black">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="question in questions" :key="question.id" class="border-t border-heritage-line/40 align-top">
                                <td class="px-6 py-4 font-black text-heritage-red">{{ question.sort_order }}</td>
                                <td class="max-w-[390px] px-6 py-4">
                                    <p class="font-bold leading-snug text-heritage-ink">{{ question.question_en }}</p>
                                    <p v-if="question.question_mk" class="mt-2 text-xs font-semibold leading-snug text-heritage-muted">{{ question.question_mk }}</p>
                                    <AppBadge v-if="question.used_in_attempts" class="mt-3" variant="red">Has attempts</AppBadge>
                                </td>
                                <td class="max-w-[260px] px-6 py-4">
                                    <p class="font-bold text-heritage-ink">{{ question.correct_answer_en || 'No answer marked' }}</p>
                                    <p v-if="question.correct_answer_mk" class="mt-1 text-xs font-semibold text-heritage-muted">{{ question.correct_answer_mk }}</p>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ question.answers_count }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ question.points || selectedQuiz?.points_per_question }}</td>
                                <td class="px-6 py-4">
                                    <AppBadge :variant="question.is_published ? 'green' : 'neutral'">{{ question.is_published ? 'Published' : 'Unpublished' }}</AppBadge>
                                </td>
                                <td class="px-6 py-4 text-heritage-muted">{{ formatDate(question.updated_at) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <button class="button-soft rounded-xl px-3 py-2 text-xs font-black" type="button" :disabled="saving" @click="openEditForm(question)">Edit</button>
                                        <button class="rounded-xl bg-heritage-gold-faint px-3 py-2 text-xs font-black text-heritage-gold-deep" type="button" :disabled="saving" @click="togglePublished(question)">
                                            {{ question.is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <button class="rounded-xl bg-heritage-red-faint px-3 py-2 text-xs font-black text-heritage-red" type="button" :disabled="saving" @click="removeQuestion(question)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-8 text-center">
                    <h2 class="text-2xl font-black text-heritage-ink">No questions yet</h2>
                    <p class="mt-2 text-heritage-muted">Add the first bilingual question and four answer options for this quiz.</p>
                    <PrimaryButton class="mt-6" variant="gold" @click="openCreateForm">Add question</PrimaryButton>
                </div>
            </section>
        </template>
    </AdminLayout>
</template>
