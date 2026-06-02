<script setup>
import { computed, onMounted, ref } from 'vue';
import MacedoniaMap from '../../Components/MacedoniaMap.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import {
    ApiError,
    categoryUrl,
    currentCategorySlug,
    currentQuizSlug,
    currentUser,
    fetchJson,
    preferredLanguageToggleValue,
    quizResultsUrl,
    submitQuizAttempt,
} from '../../api/makedoniq';

const language = ref(preferredLanguageToggleValue());
const quiz = ref(null);
const questions = ref([]);
const selectedAnswers = ref({});
const currentIndex = ref(0);
const isLoading = ref(true);
const isSubmitting = ref(false);
const error = ref('');
const isLocked = ref(false);
const submitError = ref('');
const showAuthPrompt = ref(false);

const categorySlug = currentCategorySlug();
const quizSlug = currentQuizSlug();
const user = currentUser();
const optionLabels = ['A', 'B', 'C', 'D'];

const currentQuestion = computed(() => questions.value[currentIndex.value] || null);
const selectedAnswerId = computed(() => selectedAnswers.value[currentQuestion.value?.id] || null);
const totalQuestions = computed(() => questions.value.length);
const answeredCount = computed(() => Object.keys(selectedAnswers.value).length);
const isLastQuestion = computed(() => currentIndex.value >= totalQuestions.value - 1);
const allAnswered = computed(() => totalQuestions.value > 0 && answeredCount.value === totalQuestions.value);
const guestDemoCompleted = computed(() => !user && allAnswered.value && isLastQuestion.value);
const categoryHref = computed(() => categoryUrl(quiz.value?.category?.slug || categorySlug));
const placeholderResultsHref = computed(() => quizResultsUrl(quiz.value?.category?.slug || categorySlug, quiz.value?.slug || quizSlug));
const tryAnotherDemoHref = computed(() => {
    if (quiz.value?.slug === 'basic-macedonian-greetings') {
        return '/quizzes/macedonian-alphabet/cyrillic-alphabet-basics/start';
    }

    if (quiz.value?.slug === 'cyrillic-alphabet-basics') {
        return '/map-challenge';
    }

    return '/quizzes/macedonian-language/basic-macedonian-greetings/start';
});
const relatedLesson = computed(() => quiz.value?.related_lesson || null);
const isMapQuestion = computed(() => currentQuestion.value?.question_type === 'map_guess');
const mapMetadata = computed(() => currentQuestion.value?.metadata || {});
const mapPrompt = computed(() => {
    const targetType = mapMetadata.value.target_type || 'place';

    return `Which ${targetType} is highlighted on the map?`;
});
const progressPercent = computed(() => {
    if (!totalQuestions.value) {
        return 0;
    }

    return Math.round(((currentIndex.value + 1) / totalQuestions.value) * 100);
});

const displayQuestion = computed(() => {
    return questionText(currentQuestion.value);
});

function questionText(question) {
    if (!question) {
        return '';
    }

    return language.value === 'MK' && question.question_mk
        ? question.question_mk
        : question.question_en;
}

function answerText(answer, question = currentQuestion.value) {
    if (!answer) {
        return '';
    }

    if (question?.translation_direction === 'mk_to_en') {
        return answer.answer_en || answer.answer_mk || '';
    }

    if (question?.translation_direction === 'en_to_mk') {
        return answer.answer_mk || answer.answer_en || '';
    }

    return language.value === 'MK' && answer.answer_mk ? answer.answer_mk : answer.answer_en;
}

function selectAnswer(answerId) {
    selectedAnswers.value = {
        ...selectedAnswers.value,
        [currentQuestion.value.id]: answerId,
    };
    submitError.value = '';
    showAuthPrompt.value = false;
}

function goNext() {
    if (!isLastQuestion.value) {
        currentIndex.value += 1;
    }
}

function goPrevious() {
    if (currentIndex.value > 0) {
        currentIndex.value -= 1;
    }
}

async function submitAttempt() {
    submitError.value = '';

    if (!allAnswered.value) {
        submitError.value = 'Please answer every question before submitting.';
        return;
    }

    if (!user) {
        showAuthPrompt.value = true;
        return;
    }

    isSubmitting.value = true;

    try {
        const payload = questions.value.map((question) => ({
            question_id: question.id,
            answer_id: selectedAnswers.value[question.id],
        }));

        const response = await submitQuizAttempt(quiz.value.slug, payload);
        window.location.href = response.data.result_url;
    } catch (exception) {
        if (exception instanceof ApiError && exception.status === 401) {
            showAuthPrompt.value = true;
            return;
        }

        if (exception instanceof ApiError && exception.status === 422) {
            submitError.value = exception.payload?.message || 'Please check your answers and try again.';
            return;
        }

        if (exception instanceof ApiError && exception.status === 419) {
            submitError.value = 'Your session expired. Refresh the page and try again.';
            return;
        }

        submitError.value = 'The quiz could not be submitted. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
}

onMounted(async () => {
    try {
        const response = await fetchJson(`/api/quizzes/${quizSlug}/questions`);
        quiz.value = response.data.quiz;
        questions.value = response.data.questions || [];
    } catch (exception) {
        isLocked.value = exception instanceof ApiError && exception.status === 403;
        error.value = isLocked.value
            ? exception.message
            : 'This quiz could not be loaded. Please check that the seeded questions exist.';
    } finally {
        isLoading.value = false;
    }
});

function authHref(path) {
    return `${path}?intended=${encodeURIComponent(window.location.pathname)}`;
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-heritage-bg">
        <header class="sticky top-0 z-40 border-b border-heritage-line/40 bg-white/95 shadow-card backdrop-blur">
            <div class="page-shell py-4">
                <div class="flex items-center justify-between gap-4">
                    <a :href="categoryHref" class="button-soft rounded-2xl px-4 py-3 text-sm font-black">Close</a>
                    <div class="hidden flex-1 items-center gap-4 sm:flex">
                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-heritage-panel ring-1 ring-heritage-line/50">
                            <div class="h-full rounded-full bg-linear-to-r from-heritage-red to-heritage-gold" :style="{ width: `${progressPercent}%` }" />
                        </div>
                        <span class="text-sm font-black text-heritage-muted">Question {{ currentIndex + 1 }} / {{ totalQuestions || 1 }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-heritage-panel p-1">
                            <button type="button" aria-label="Show English quiz text" :aria-pressed="language === 'EN'" :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'EN' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'EN'">EN</button>
                            <button type="button" aria-label="Show Macedonian quiz text" :aria-pressed="language === 'MK'" :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'MK' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'MK'">MK</button>
                        </div>
                        <div class="rounded-full bg-heritage-gold-faint px-4 py-2 text-sm font-black text-heritage-gold-deep">{{ answeredCount }} / {{ totalQuestions || 0 }}</div>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3 sm:hidden">
                    <div class="h-3 flex-1 overflow-hidden rounded-full bg-heritage-panel">
                        <div class="h-full rounded-full bg-linear-to-r from-heritage-red to-heritage-gold" :style="{ width: `${progressPercent}%` }" />
                    </div>
                    <span class="text-sm font-black text-heritage-muted">{{ currentIndex + 1 }}/{{ totalQuestions || 1 }}</span>
                </div>
            </div>
        </header>

        <main class="page-shell flex flex-1 flex-col justify-center py-8">
            <section v-if="isLoading" class="mx-auto w-full max-w-3xl">
                <div class="soft-card min-h-80 animate-pulse p-8">
                    <div class="mx-auto h-5 w-40 rounded-full bg-heritage-panel" />
                    <div class="mx-auto mt-10 h-12 w-4/5 rounded-full bg-heritage-panel" />
                    <div class="mx-auto mt-5 h-12 w-3/5 rounded-full bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="mx-auto w-full max-w-3xl">
                <div class="soft-card p-8 text-center">
                    <div v-if="isLocked" class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-[1.25rem] border border-heritage-gold/40 bg-heritage-gold-faint text-xs font-black text-heritage-gold-deep shadow-card">
                        LOCK
                    </div>
                    <h1 class="text-3xl font-black text-heritage-ink">{{ isLocked ? 'This quiz is locked for guests.' : 'Quiz unavailable' }}</h1>
                    <p class="mt-3 leading-7 text-heritage-muted">
                        {{ isLocked ? 'Create a free account to unlock all quizzes and save your progress.' : error }}
                    </p>
                    <div v-if="isLocked" class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        <PrimaryButton :href="authHref('/register')">Create free account</PrimaryButton>
                        <PrimaryButton :href="authHref('/login')" variant="soft">Log in</PrimaryButton>
                        <PrimaryButton :href="categoryHref" variant="ghost">Back to Quizzes</PrimaryButton>
                    </div>
                </div>
            </section>

            <template v-else-if="currentQuestion">
                <section class="mx-auto w-full max-w-3xl">
                    <div class="soft-card p-6 text-center md:p-8">
                        <span class="eyebrow">{{ quiz.title_en }}</span>
                        <h1 class="mt-6 text-2xl font-black leading-tight text-heritage-ink sm:text-3xl md:text-5xl">
                            {{ displayQuestion }}
                        </h1>
                        <div v-if="isMapQuestion" class="mx-auto mt-8 max-w-xl">
                            <MacedoniaMap
                                :x="mapMetadata.map_x"
                                :y="mapMetadata.map_y"
                                :target-type="mapMetadata.target_type || 'place'"
                                variant="compact"
                            />
                            <p class="mt-4 rounded-2xl bg-white px-4 py-3 text-sm font-black text-heritage-red shadow-card">
                                {{ mapPrompt }}
                            </p>
                        </div>
                        <div class="mx-auto mt-8 rounded-[2rem] bg-heritage-panel p-4">
                            <p class="text-sm font-bold leading-7 text-heritage-muted">
                                Pick one answer for each question. Your score is calculated securely after submission.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="mx-auto mt-6 grid w-full max-w-3xl gap-4 md:grid-cols-2">
                    <button
                        v-for="(answer, index) in currentQuestion.answers"
                        :key="answer.id"
                        type="button"
                        :aria-label="`Answer ${optionLabels[index] || index + 1}: ${answerText(answer)}`"
                        :aria-pressed="selectedAnswerId === answer.id"
                        :class="[
                            'relative flex min-h-20 items-center rounded-2xl border-2 p-5 text-left shadow-card transition active:scale-[0.99]',
                            selectedAnswerId === answer.id ? 'border-heritage-gold bg-heritage-gold-faint text-heritage-gold-deep' : 'border-heritage-line bg-white text-heritage-ink hover:border-heritage-gold hover:bg-heritage-gold-faint/40',
                        ]"
                        @click="selectAnswer(answer.id)"
                    >
                        <span :class="['mr-4 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-black', selectedAnswerId === answer.id ? 'bg-heritage-red text-white' : 'bg-heritage-panel text-heritage-muted']">
                            {{ optionLabels[index] || index + 1 }}
                        </span>
                        <span class="min-w-0 break-words text-lg font-black">{{ answerText(answer) }}</span>
                        <span v-if="selectedAnswerId === answer.id" class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-heritage-gold text-sm font-black text-heritage-navy">OK</span>
                    </button>
                </section>
            </template>
        </main>

        <footer v-if="!error" class="border-t-4 border-heritage-gold bg-white">
            <div class="page-shell grid gap-5 py-5 md:grid-cols-[1fr_auto] md:items-center">
                <div class="flex items-start gap-4 text-left">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-heritage-gold-faint text-base font-black text-heritage-gold-deep sm:h-14 sm:w-14 sm:text-lg">
                        {{ selectedAnswerId ? 'OK' : '?' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xl font-black text-heritage-ink">{{ selectedAnswerId ? 'Answer selected' : 'Choose an answer' }}</p>
                        <p class="text-sm text-heritage-muted">
                            {{ allAnswered ? 'Ready to submit when you reach the end.' : `${answeredCount} of ${totalQuestions || 0} questions answered.` }}
                        </p>
                        <p v-if="submitError" class="mt-2 text-sm font-bold text-heritage-red" role="alert">{{ submitError }}</p>
                        <div v-if="showAuthPrompt || guestDemoCompleted" class="mt-3 rounded-2xl border border-heritage-gold/40 bg-heritage-gold-faint p-4">
                            <p class="text-base font-black text-heritage-ink">Nice work!</p>
                            <p class="mt-1 text-sm font-bold leading-6 text-heritage-gold-deep">Create a free account to save your score and unlock all lessons and quizzes.</p>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                                <a class="pressable-gold rounded-2xl px-4 py-2 text-center text-sm font-black" :href="authHref('/register')">Create free account</a>
                                <a class="button-soft rounded-2xl px-4 py-2 text-center text-sm font-black" :href="authHref('/login')">Log in</a>
                                <a class="button-ghost rounded-2xl px-4 py-2 text-center text-sm font-black" :href="tryAnotherDemoHref">Try another demo</a>
                            </div>
                        </div>
                        <a v-if="relatedLesson" :href="relatedLesson.url" class="mt-3 inline-flex text-sm font-black text-heritage-red hover:text-heritage-red-dark">
                            Need help? Review lesson
                        </a>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row md:justify-end">
                    <PrimaryButton v-if="currentIndex > 0" class="w-full sm:w-auto" variant="soft" size="lg" @click="goPrevious">Previous</PrimaryButton>
                    <PrimaryButton v-if="!isLastQuestion" class="w-full sm:w-auto" size="lg" @click="goNext">Next</PrimaryButton>
                    <PrimaryButton v-else-if="guestDemoCompleted" class="w-full sm:w-auto" :href="authHref('/register')" size="lg">Save score free</PrimaryButton>
                    <PrimaryButton
                        v-else
                        class="w-full sm:w-auto"
                        size="lg"
                        :disabled="!allAnswered || isSubmitting"
                        @click="submitAttempt"
                    >
                        {{ isSubmitting ? 'Submitting...' : 'Submit quiz' }}
                    </PrimaryButton>
                    <PrimaryButton v-if="!user && !guestDemoCompleted" class="w-full sm:w-auto" :href="placeholderResultsHref" variant="ghost" size="lg">Results info</PrimaryButton>
                    <PrimaryButton v-if="guestDemoCompleted" class="w-full sm:w-auto" :href="tryAnotherDemoHref" variant="ghost" size="lg">Try another demo</PrimaryButton>
                </div>
            </div>
        </footer>
    </div>
</template>
