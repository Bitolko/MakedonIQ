<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PictureQuestionVisual from '../../Components/PictureQuestionVisual.vue';
import {
    ApiError,
    categoryUrl,
    currentAttemptId,
    currentCategorySlug,
    currentQuizSlug,
    fetchJson,
    getQuizAttempt,
    localizedText,
    preferredLanguage,
    quizActiveUrl,
    quizStartUrl,
} from '../../api/makedoniq';

const attemptResult = ref(null);
const quiz = ref(null);
const isLoading = ref(true);
const error = ref('');
const isLocked = ref(false);
const language = preferredLanguage();

const categorySlug = currentCategorySlug();
const quizSlug = currentQuizSlug();
const attemptId = currentAttemptId();

const hasAttempt = computed(() => Boolean(attemptId));
const resultAttempt = computed(() => attemptResult.value?.attempt || null);
const resultQuiz = computed(() => attemptResult.value?.quiz || quiz.value || null);
const resultCategory = computed(() => attemptResult.value?.category || resultQuiz.value?.category || null);
const reviewAnswers = computed(() => attemptResult.value?.answers || []);
const reviewUrl = computed(() => quizActiveUrl(resultCategory.value?.slug || categorySlug, resultQuiz.value?.slug || quizSlug));
const tryAgainUrl = computed(() => quizStartUrl(resultCategory.value?.slug || categorySlug, resultQuiz.value?.slug || quizSlug));
const continueUrl = computed(() => categoryUrl(resultCategory.value?.slug || categorySlug));
const relatedLesson = computed(() => resultQuiz.value?.related_lesson || null);
const relatedLessonTitle = computed(() => localizedText(relatedLesson.value, 'title', language));
const relatedLessonSummary = computed(() => localizedText(relatedLesson.value, 'summary', language));
const isMapChallenge = computed(() => Boolean(resultQuiz.value?.has_map_questions));
const isPictureQuiz = computed(() => Boolean(resultQuiz.value?.has_picture_questions || reviewAnswers.value.some((answer) => answer.question?.question_type === 'picture_choice')));
const resultPercentage = computed(() => Number(resultAttempt.value?.percentage || 0));
const scoreMessage = computed(() => {
    if (!resultAttempt.value) {
        return 'Complete a quiz to see your saved result.';
    }

    if (isMapChallenge.value) {
        if (resultPercentage.value >= 90) {
            return 'Excellent geography knowledge!';
        }

        if (resultPercentage.value >= 70) {
            return 'Great work — you know many key places.';
        }

        return 'Good start — review the geography lesson and try again.';
    }

    return resultAttempt.value.passed
        ? 'Excellent work. You passed this quiz and saved your progress.'
        : 'Good effort. Review the answers and try again when you are ready.';
});
const resultQuizTitle = computed(() => localizedText(resultQuiz.value, 'title', language));
const resultCategoryName = computed(() => localizedText(resultCategory.value, 'name', language));

function formatDate(value) {
    if (!value) {
        return 'Just now';
    }

    return new Intl.DateTimeFormat('en-AU', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function questionText(question) {
    return localizedText(question, 'question', language);
}

function explanationText(question) {
    return localizedText(question, 'explanation', language);
}

function answerText(answer, question) {
    if (!answer) {
        return '';
    }

    if (question?.translation_direction === 'mk_to_en') {
        return answer.answer_en || answer.answer_mk || '';
    }

    if (question?.translation_direction === 'en_to_mk') {
        return answer.answer_mk || answer.answer_en || '';
    }

    return localizedText(answer, 'answer', language);
}

function isPictureQuestion(question) {
    return question?.question_type === 'picture_choice';
}

onMounted(async () => {
    try {
        if (attemptId) {
            const response = await getQuizAttempt(attemptId);
            attemptResult.value = response.data;
            return;
        }

        const response = await fetchJson(`/api/quizzes/${quizSlug}`);
        quiz.value = response.data;
    } catch (exception) {
        isLocked.value = exception instanceof ApiError && exception.status === 403;
        error.value = isLocked.value
            ? exception.message
            : (attemptId
                ? 'This result could not be loaded. Make sure you are logged in with the account that completed the quiz.'
                : 'Quiz details could not be loaded.');
    } finally {
        isLoading.value = false;
    }
});

function authHref(path) {
    return `${path}?intended=${encodeURIComponent(window.location.pathname)}`;
}
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-12">
            <section v-if="isLoading" class="mx-auto max-w-4xl text-center">
                <AppBadge variant="gold">Loading result</AppBadge>
                <div class="mx-auto mt-8 h-12 w-2/3 animate-pulse rounded-full bg-heritage-panel" />
                <div class="mx-auto mt-4 h-5 w-1/2 animate-pulse rounded-full bg-heritage-panel" />
            </section>

            <section v-else-if="error" class="section-panel mx-auto max-w-4xl text-center">
                <div v-if="isLocked" class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-[1.25rem] border border-heritage-gold/40 bg-heritage-gold-faint text-xs font-black text-heritage-gold-deep shadow-card">
                    LOCK
                </div>
                <AppBadge :variant="isLocked ? 'gold' : 'red'">{{ isLocked ? 'Locked quiz' : 'Result unavailable' }}</AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-red md:text-5xl">
                    {{ isLocked ? 'This quiz is locked for guests.' : 'We could not load this result' }}
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-heritage-muted">
                    {{ isLocked ? 'Create a free account to unlock all quizzes and save your progress.' : error }}
                </p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <PrimaryButton v-if="isLocked" :href="authHref('/register')">Create free account</PrimaryButton>
                    <PrimaryButton :href="authHref('/login')" variant="soft">Log in</PrimaryButton>
                    <PrimaryButton v-if="!isLocked" :href="tryAgainUrl">Start quiz</PrimaryButton>
                    <PrimaryButton v-else :href="continueUrl" variant="ghost">Back to Quizzes</PrimaryButton>
                </div>
            </section>

            <template v-else>
            <section class="mx-auto max-w-4xl text-center">
                <AppBadge :variant="resultAttempt?.passed ? 'green' : 'gold'">
                    {{ isMapChallenge ? 'Map Challenge result' : (isPictureQuiz ? 'Picture Quiz result' : (hasAttempt ? 'Quiz result' : 'Results preview')) }}
                </AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-red md:text-5xl">
                    {{ isMapChallenge && hasAttempt ? 'Map Challenge Complete' : (hasAttempt ? (resultAttempt?.passed ? 'Great Job!' : 'Keep Going!') : 'Complete a Quiz First') }}
                </h1>
                <p class="mt-4 text-lg text-heritage-muted">
                    <span v-if="hasAttempt">You completed {{ resultQuizTitle }}. {{ scoreMessage }}</span>
                    <span v-else>Start {{ resultQuizTitle || 'a quiz' }} and submit your answers to see a real saved result here.</span>
                </p>
                <p v-if="resultAttempt?.completed_at" class="mt-2 text-sm font-bold text-heritage-muted">
                    Completed {{ formatDate(resultAttempt.completed_at) }}
                </p>
            </section>

            <section class="mx-auto mt-10 grid max-w-5xl gap-6 md:grid-cols-3">
                <article class="soft-card p-8 text-center">
                    <p class="label">Final score</p>
                    <p class="mt-4 text-5xl font-black text-heritage-red">{{ resultAttempt ? `${Math.round(resultAttempt.percentage)}%` : '--' }}</p>
                </article>
                <article class="soft-card p-8 text-center">
                    <p class="label">Correct answers</p>
                    <p class="mt-4 text-5xl font-black text-heritage-ink">
                        {{ resultAttempt ? `${resultAttempt.correct_answers}/${resultAttempt.total_questions}` : '--' }}
                    </p>
                </article>
                <article class="soft-card p-8 text-center">
                    <p class="label">Points earned</p>
                    <p class="mt-4 text-5xl font-black text-heritage-gold-deep">{{ resultAttempt?.score ?? '--' }}</p>
                </article>
            </section>

            <section class="mx-auto mt-8 grid max-w-5xl gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <article class="heritage-pattern rounded-[2rem] p-8 text-center text-white shadow-soft">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white text-3xl font-black text-heritage-red">
                        {{ resultCategory?.icon || 'IQ' }}
                    </div>
                    <h2 class="mt-5 text-2xl font-black">{{ resultAttempt?.passed ? 'Badge earned' : 'Practice mode' }}</h2>
                    <p class="mt-2 font-semibold text-white/80">
                        {{ resultAttempt?.passed ? `${resultCategoryName || 'MakedonIQ'} Starter` : 'Review and try again' }}
                    </p>
                </article>
                <article class="soft-card p-8">
                    <h2 class="text-2xl font-black text-heritage-ink">Achievement message</h2>
                    <p class="mt-3 leading-7 text-heritage-muted">{{ scoreMessage }}</p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <PrimaryButton v-if="relatedLesson" :href="relatedLesson.url" variant="soft">{{ isMapChallenge ? 'Review geography lesson' : 'Review lesson' }}</PrimaryButton>
                        <PrimaryButton v-if="hasAttempt && !isMapChallenge" :href="reviewUrl" variant="soft">Take again</PrimaryButton>
                        <PrimaryButton :href="tryAgainUrl" variant="ghost">Try again</PrimaryButton>
                        <PrimaryButton :href="isMapChallenge ? '/learn/geography' : continueUrl">{{ isMapChallenge ? 'Explore Learn Geography' : 'Continue learning' }}</PrimaryButton>
                    </div>
                </article>
            </section>

            <section v-if="relatedLesson" class="mx-auto mt-8 max-w-5xl rounded-[2rem] border border-heritage-gold/40 bg-heritage-gold-faint p-6 md:p-8">
                <AppBadge variant="gold">Review lesson</AppBadge>
                <h2 class="mt-4 text-2xl font-black text-heritage-ink">{{ relatedLessonTitle }}</h2>
                <p class="mt-3 max-w-3xl leading-7 text-heritage-gold-deep">{{ relatedLessonSummary }}</p>
                <PrimaryButton :href="relatedLesson.url" class="mt-5" variant="soft">{{ isMapChallenge ? 'Review geography lesson' : 'Open lesson' }}</PrimaryButton>
            </section>

            <section v-if="hasAttempt" class="mx-auto mt-8 max-w-5xl">
                <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <AppBadge variant="navy">Review answers</AppBadge>
                        <h2 class="mt-3 text-3xl font-black text-heritage-ink">What you selected</h2>
                    </div>
                    <p class="text-sm font-bold text-heritage-muted">Correct answers are shown only after completion.</p>
                </div>

                <div class="grid gap-4">
                    <article v-for="(answer, index) in reviewAnswers" :key="answer.id" class="soft-card p-5 md:p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <AppBadge :variant="answer.is_correct ? 'green' : 'red'">
                                    {{ answer.is_correct ? 'Correct' : 'Review' }}
                                </AppBadge>
                                <h3 class="mt-3 text-xl font-black text-heritage-ink">
                                    {{ index + 1 }}. {{ questionText(answer.question) }}
                                </h3>
                                <p v-if="language !== 'mk' && answer.question.question_mk" class="mt-2 font-semibold text-heritage-muted">{{ answer.question.question_mk }}</p>
                            </div>
                            <div class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-muted">
                                {{ answer.points_awarded }} pts
                            </div>
                        </div>

                        <PictureQuestionVisual
                            v-if="isPictureQuestion(answer.question)"
                            class="mt-5"
                            :metadata="answer.question.metadata || {}"
                            :language="language"
                            compact
                        />

                        <div class="mt-5 grid gap-3 md:grid-cols-2">
                            <div :class="['rounded-2xl border p-4', answer.is_correct ? 'border-emerald-200 bg-emerald-50' : 'border-heritage-red/20 bg-heritage-red-faint']">
                                <p class="label">Your answer</p>
                                <p class="mt-2 font-black text-heritage-ink">{{ answerText(answer.selected_answer, answer.question) }}</p>
                            </div>
                            <div class="rounded-2xl border border-heritage-gold/40 bg-heritage-gold-faint p-4">
                                <p class="label">Correct answer</p>
                                <p class="mt-2 font-black text-heritage-ink">{{ answerText(answer.correct_answer, answer.question) }}</p>
                            </div>
                        </div>

                        <p v-if="explanationText(answer.question)" class="mt-4 rounded-2xl bg-white/70 p-4 leading-7 text-heritage-muted">
                            {{ explanationText(answer.question) }}
                        </p>
                    </article>
                </div>
            </section>
            </template>
        </main>
    </PublicLayout>
</template>
