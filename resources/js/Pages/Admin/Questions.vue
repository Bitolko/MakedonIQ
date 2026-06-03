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
const pictureImageTypes = [
    { value: 'food', label: 'Food' },
    { value: 'city', label: 'City' },
    { value: 'lake', label: 'Lake' },
    { value: 'landmark', label: 'Landmark' },
    { value: 'alphabet', label: 'Alphabet' },
    { value: 'culture', label: 'Culture' },
    { value: 'music', label: 'Music' },
    { value: 'other', label: 'Other' },
];
const soundAudioTypes = [
    { value: 'folklore', label: 'Folklore' },
    { value: 'pronunciation', label: 'Pronunciation' },
    { value: 'music', label: 'Music' },
    { value: 'other', label: 'Other' },
];

const blankAnswers = () => [1, 2, 3, 4].map((sortOrder, index) => ({
    answer_en: '',
    answer_mk: '',
    sort_order: sortOrder,
    is_correct: index === 0,
}));

const blankForm = () => ({
    question_type: 'multiple_choice',
    translation_direction: '',
    question_en: '',
    question_mk: '',
    explanation_en: '',
    explanation_mk: '',
    sort_order: nextSortOrder(),
    points: '',
    is_published: true,
    metadata: {
        map_x: 50,
        map_y: 50,
        target_type: 'city',
        map_target_key: '',
        map_target_label_en: '',
        map_target_label_mk: '',
        image_path: '',
        image_alt_en: '',
        image_alt_mk: '',
        image_type: 'food',
        image_credit: '',
        audio_path: '',
        audio_alt_en: '',
        audio_alt_mk: '',
        audio_type: 'folklore',
        audio_credit: '',
    },
    answers: blankAnswers(),
});

const form = ref(blankForm());

const selectedQuiz = computed(() => quizzes.value.find((quiz) => quiz.id === Number(selectedQuizId.value)) || null);
const hasSelectedQuiz = computed(() => Boolean(selectedQuiz.value));
const selectedQuizLessonUrl = computed(() => {
    if (!selectedQuiz.value?.lesson_slug || !selectedQuiz.value?.lesson_category_slug) {
        return '';
    }

    return `/learn/${selectedQuiz.value.lesson_category_slug}/${selectedQuiz.value.lesson_slug}`;
});

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
        question_type: question.question_type || 'multiple_choice',
        translation_direction: question.translation_direction || '',
        question_en: question.question_en || '',
        question_mk: question.question_mk || '',
        explanation_en: question.explanation_en || '',
        explanation_mk: question.explanation_mk || '',
        sort_order: question.sort_order ?? 0,
        points: question.points ?? '',
        is_published: Boolean(question.is_published),
        metadata: normalizedMetadata(question.metadata),
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
        question_type: source.question_type || 'multiple_choice',
        translation_direction: normalizeTranslationDirection(source.translation_direction),
        metadata: metadataPayload(source),
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

function normalizedMetadata(metadata = {}) {
    return {
        map_x: numberOrDefault(metadata?.map_x, 50),
        map_y: numberOrDefault(metadata?.map_y, 50),
        target_type: metadata?.target_type || 'city',
        map_target_key: metadata?.map_target_key || '',
        map_target_label_en: metadata?.map_target_label_en || '',
        map_target_label_mk: metadata?.map_target_label_mk || '',
        image_path: metadata?.image_path || '',
        image_alt_en: metadata?.image_alt_en || '',
        image_alt_mk: metadata?.image_alt_mk || '',
        image_type: metadata?.image_type || 'food',
        image_credit: metadata?.image_credit || '',
        audio_path: metadata?.audio_path || '',
        audio_alt_en: metadata?.audio_alt_en || '',
        audio_alt_mk: metadata?.audio_alt_mk || '',
        audio_type: metadata?.audio_type || 'folklore',
        audio_credit: metadata?.audio_credit || '',
    };
}

function metadataPayload(source) {
    if (source.question_type === 'map_guess') {
        return mapMetadataPayload(source.metadata);
    }

    if (source.question_type === 'picture_choice') {
        return pictureMetadataPayload(source.metadata);
    }

    if (source.question_type === 'sound_choice') {
        return soundMetadataPayload(source.metadata);
    }

    return null;
}

function mapMetadataPayload(metadata = {}) {
    return {
        map_x: numberOrDefault(metadata?.map_x, 50),
        map_y: numberOrDefault(metadata?.map_y, 50),
        target_type: metadata?.target_type || 'city',
        map_target_key: nullableString(metadata?.map_target_key),
        map_target_label_en: nullableString(metadata?.map_target_label_en),
        map_target_label_mk: nullableString(metadata?.map_target_label_mk),
    };
}

function pictureMetadataPayload(metadata = {}) {
    const imageType = pictureImageTypes.some((type) => type.value === metadata?.image_type)
        ? metadata.image_type
        : 'other';

    return {
        image_path: nullableString(metadata?.image_path),
        image_alt_en: nullableString(metadata?.image_alt_en),
        image_alt_mk: nullableString(metadata?.image_alt_mk),
        image_type: imageType,
        image_credit: nullableString(metadata?.image_credit),
    };
}

function soundMetadataPayload(metadata = {}) {
    const audioPath = nullableString(metadata?.audio_path);
    const audioType = soundAudioTypes.some((type) => type.value === metadata?.audio_type)
        ? metadata.audio_type
        : 'folklore';

    return {
        audio_path: audioPath && !audioPath.startsWith('/') ? `/${audioPath}` : audioPath,
        audio_alt_en: nullableString(metadata?.audio_alt_en),
        audio_alt_mk: nullableString(metadata?.audio_alt_mk),
        audio_type: audioType,
        audio_credit: nullableString(metadata?.audio_credit),
    };
}

function normalizeTranslationDirection(value) {
    return value && value !== 'general' ? value : null;
}

function formatTranslationDirection(value) {
    return {
        mk_to_en: 'MK -> EN',
        en_to_mk: 'EN -> MK',
    }[value] || 'General';
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
                    <a
                        v-if="selectedQuizLessonUrl"
                        class="rounded-full bg-heritage-navy-soft px-4 py-2 text-xs font-black text-heritage-navy hover:bg-heritage-gold-faint"
                        :href="selectedQuizLessonUrl"
                    >
                        Lesson: {{ selectedQuiz.lesson_title_en }}
                    </a>
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

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-[14rem_14rem_10rem_10rem_1fr] xl:items-end">
                    <label class="block">
                        <span class="label">Question type</span>
                        <select v-model="form.question_type" class="field mt-2">
                            <option value="multiple_choice">Multiple choice</option>
                            <option value="map_guess">Map guess</option>
                            <option value="picture_choice">Picture choice</option>
                            <option value="sound_choice">Sound choice</option>
                        </select>
                        <span v-if="fieldError('question_type')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('question_type') }}</span>
                    </label>
                    <label class="block">
                        <span class="label">Translation direction</span>
                        <select v-model="form.translation_direction" class="field mt-2">
                            <option value="">General / none</option>
                            <option value="mk_to_en">Macedonian to English</option>
                            <option value="en_to_mk">English to Macedonian</option>
                        </select>
                        <span v-if="fieldError('translation_direction')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('translation_direction') }}</span>
                    </label>
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

                <section v-if="form.question_type === 'map_guess'" class="grid gap-5 rounded-[1.5rem] border border-heritage-gold/40 bg-heritage-gold-faint p-5">
                    <div>
                        <h3 class="text-xl font-black text-heritage-ink">Map marker metadata</h3>
                        <p class="mt-1 text-sm font-semibold text-heritage-gold-deep">Coordinates are percentages on the map image. Example: x=50, y=40. Target labels stay admin-only.</p>
                        <span v-if="fieldError('metadata')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata') }}</span>
                    </div>
                    <div class="grid gap-5 md:grid-cols-3">
                        <label class="block">
                            <span class="label">Map X percentage</span>
                            <input v-model.number="form.metadata.map_x" class="field mt-2 bg-white" min="0" max="100" step="0.1" type="number">
                            <span v-if="fieldError('metadata.map_x')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.map_x') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Map Y percentage</span>
                            <input v-model.number="form.metadata.map_y" class="field mt-2 bg-white" min="0" max="100" step="0.1" type="number">
                            <span v-if="fieldError('metadata.map_y')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.map_y') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Target type</span>
                            <select v-model="form.metadata.target_type" class="field mt-2 bg-white">
                                <option value="city">City</option>
                                <option value="lake">Lake</option>
                                <option value="landmark">Landmark</option>
                                <option value="region">Region</option>
                            </select>
                            <span v-if="fieldError('metadata.target_type')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.target_type') }}</span>
                        </label>
                    </div>
                    <div class="grid gap-5 md:grid-cols-3">
                        <label class="block">
                            <span class="label">Target key admin-only</span>
                            <input v-model="form.metadata.map_target_key" class="field mt-2 bg-white" placeholder="skopje" type="text">
                            <span v-if="fieldError('metadata.map_target_key')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.map_target_key') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Target label English</span>
                            <input v-model="form.metadata.map_target_label_en" class="field mt-2 bg-white" type="text">
                            <span v-if="fieldError('metadata.map_target_label_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.map_target_label_en') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Target label Macedonian</span>
                            <input v-model="form.metadata.map_target_label_mk" class="field mt-2 bg-white" type="text">
                            <span v-if="fieldError('metadata.map_target_label_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.map_target_label_mk') }}</span>
                        </label>
                    </div>
                </section>

                <section v-if="form.question_type === 'picture_choice'" class="grid gap-5 rounded-[1.5rem] border border-heritage-gold/40 bg-white p-5 shadow-card">
                    <div>
                        <h3 class="text-xl font-black text-heritage-ink">Picture metadata</h3>
                        <p class="mt-1 text-sm font-semibold leading-6 text-heritage-muted">
                            Image path is optional for now. If left blank, users will see a placeholder card. Use original, public domain, or properly licensed images only. Do not use copied textbook images.
                        </p>
                        <span v-if="fieldError('metadata')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata') }}</span>
                    </div>
                    <div class="grid gap-5 md:grid-cols-[1fr_14rem]">
                        <label class="block">
                            <span class="label">Image path optional</span>
                            <input v-model="form.metadata.image_path" class="field mt-2 bg-white" placeholder="/images/quizzes/quiz_img_001.jpg" type="text">
                            <span v-if="fieldError('metadata.image_path')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.image_path') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Image type</span>
                            <select v-model="form.metadata.image_type" class="field mt-2 bg-white">
                                <option v-for="type in pictureImageTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <span v-if="fieldError('metadata.image_type')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.image_type') }}</span>
                        </label>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="label">Image alt English</span>
                            <input v-model="form.metadata.image_alt_en" class="field mt-2 bg-white" type="text">
                            <span v-if="fieldError('metadata.image_alt_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.image_alt_en') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Image alt Macedonian</span>
                            <input v-model="form.metadata.image_alt_mk" class="field mt-2 bg-white" type="text">
                            <span v-if="fieldError('metadata.image_alt_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.image_alt_mk') }}</span>
                        </label>
                    </div>
                    <label class="block">
                        <span class="label">Image credit or source note</span>
                        <textarea v-model="form.metadata.image_credit" class="field mt-2 min-h-20 bg-white" />
                        <span v-if="fieldError('metadata.image_credit')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.image_credit') }}</span>
                    </label>
                </section>

                <section v-if="form.question_type === 'sound_choice'" class="grid gap-5 rounded-[1.5rem] border border-heritage-gold/40 bg-white p-5 shadow-card">
                    <div>
                        <h3 class="text-xl font-black text-heritage-ink">Sound metadata</h3>
                        <p class="mt-1 text-sm font-semibold leading-6 text-heritage-muted">
                            Audio path is optional for now. If left blank, users will see an audio placeholder. Use original MakedonIQ recordings, public domain, or properly licensed audio only. Do not use Pesna.org audio or commercial recordings without permission.
                        </p>
                        <span v-if="fieldError('metadata')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata') }}</span>
                    </div>
                    <div class="grid gap-5 md:grid-cols-[1fr_14rem]">
                        <label class="block">
                            <span class="label">Audio path optional</span>
                            <input v-model="form.metadata.audio_path" class="field mt-2 bg-white" placeholder="/audio/quizzes/song_001.mp3" type="text">
                            <span v-if="fieldError('metadata.audio_path')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.audio_path') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Audio type</span>
                            <select v-model="form.metadata.audio_type" class="field mt-2 bg-white">
                                <option v-for="type in soundAudioTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <span v-if="fieldError('metadata.audio_type')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.audio_type') }}</span>
                        </label>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="label">Audio alt English</span>
                            <input v-model="form.metadata.audio_alt_en" class="field mt-2 bg-white" placeholder="Folklore audio clue" type="text">
                            <span v-if="fieldError('metadata.audio_alt_en')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.audio_alt_en') }}</span>
                        </label>
                        <label class="block">
                            <span class="label">Audio alt Macedonian</span>
                            <input v-model="form.metadata.audio_alt_mk" class="field mt-2 bg-white" placeholder="Аудио загатка од народна песна" type="text">
                            <span v-if="fieldError('metadata.audio_alt_mk')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.audio_alt_mk') }}</span>
                        </label>
                    </div>
                    <label class="block">
                        <span class="label">Audio credit or source note</span>
                        <textarea v-model="form.metadata.audio_credit" class="field mt-2 min-h-20 bg-white" placeholder="Placeholder. Original MakedonIQ recording to be added later." />
                        <span v-if="fieldError('metadata.audio_credit')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError('metadata.audio_credit') }}</span>
                    </label>
                    <div v-if="form.metadata.audio_path" class="rounded-2xl bg-heritage-panel p-4">
                        <p class="label">Current audio</p>
                        <audio class="mt-3 w-full" controls preload="metadata" :src="form.metadata.audio_path" />
                    </div>
                    <div v-else class="rounded-2xl bg-heritage-gold-faint p-4 text-sm font-bold leading-6 text-heritage-gold-deep">
                        Audio coming soon. Learners will see a placeholder until an original recording is added to public/audio/quizzes/.
                    </div>
                </section>

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
                    <p v-if="questions.length" class="mt-3 text-xs font-bold text-heritage-muted sm:hidden">
                        Scroll sideways to review every question column.
                    </p>
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
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <AppBadge v-if="question.question_type === 'map_guess'" variant="gold">Map guess</AppBadge>
                                        <AppBadge v-if="question.question_type === 'picture_choice'" variant="gold">Picture choice</AppBadge>
                                        <AppBadge v-if="question.question_type === 'sound_choice'" variant="gold">Sound choice</AppBadge>
                                        <AppBadge v-if="question.translation_direction" variant="navy">{{ formatTranslationDirection(question.translation_direction) }}</AppBadge>
                                        <AppBadge v-if="question.used_in_attempts" variant="red">Has attempts</AppBadge>
                                    </div>
                                    <p v-if="question.question_type === 'sound_choice' && question.metadata?.audio_path" class="mt-2 break-all text-xs font-semibold text-heritage-muted">
                                        {{ question.metadata.audio_path }}
                                    </p>
                                    <p v-else-if="question.question_type === 'sound_choice'" class="mt-2 text-xs font-semibold text-heritage-muted">
                                        Audio placeholder active
                                    </p>
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
