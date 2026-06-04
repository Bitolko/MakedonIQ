<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../Components/AdminLayout.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PictureQuestionVisual from '../../Components/PictureQuestionVisual.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { currentAdminAttemptId, getAdminAttempt } from '../../api/makedoniq';

const loading = ref(true);
const error = ref('');
const result = ref(null);
const attemptId = currentAdminAttemptId();

const attempt = computed(() => result.value?.attempt || null);
const learner = computed(() => result.value?.learner || null);
const quiz = computed(() => result.value?.quiz || null);
const category = computed(() => result.value?.category || null);
const learnerQuizStats = computed(() => result.value?.learner_quiz_stats || {});
const answers = computed(() => result.value?.answers || []);

onMounted(async () => {
    try {
        const response = await getAdminAttempt(attemptId);
        result.value = response.data;
    } catch (caughtError) {
        error.value = caughtError.status === 403
            ? 'You do not have permission to view this attempt result.'
            : caughtError.message || 'Unable to load this attempt result.';
    } finally {
        loading.value = false;
    }
});

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

function answerText(answer, fallback = 'No answer saved') {
    if (!answer) {
        return fallback;
    }

    return answer.answer_en || answer.answer_mk || fallback;
}

function secondaryAnswerText(answer) {
    return answer?.answer_mk && answer.answer_mk !== answer.answer_en ? answer.answer_mk : '';
}

function isPictureQuestion(question) {
    return question?.question_type === 'picture_choice';
}

function isSoundQuestion(question) {
    return question?.question_type === 'sound_choice';
}

function soundAltText(question) {
    return question?.metadata?.audio_alt_en || question?.metadata?.audio_alt_mk || 'Saved quiz audio clue';
}
</script>

<template>
    <AdminLayout>
        <section v-if="loading" class="section-panel">
            <AppBadge variant="gold">Loading result</AppBadge>
            <h1 class="mt-4 text-3xl font-black text-heritage-ink">Loading learner attempt</h1>
            <p class="mt-2 text-heritage-muted">Gathering saved answers, learner context, and quiz metadata.</p>
        </section>

        <section v-else-if="error" class="section-panel">
            <AppBadge variant="red">Attempt unavailable</AppBadge>
            <h1 class="mt-4 text-3xl font-black text-heritage-ink">Unable to load attempt result</h1>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <PrimaryButton href="/admin/attempts" variant="soft">Back to Attempts</PrimaryButton>
                <PrimaryButton href="/admin">Back to Admin Dashboard</PrimaryButton>
            </div>
        </section>

        <template v-else>
            <section class="mx-auto max-w-5xl text-center">
                <AppBadge :variant="attempt?.passed ? 'green' : 'gold'">Quiz Attempt Result</AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-red md:text-5xl">Learner Attempt Review</h1>
                <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-heritage-muted">
                    {{ learner?.name }} completed {{ quiz?.title_en }} in {{ category?.name_en }}.
                </p>
                <p class="mt-2 text-sm font-bold text-heritage-muted">
                    Attempt #{{ attempt?.id }} / Completed {{ formatDate(attempt?.completed_at) }}
                </p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <PrimaryButton href="/admin/attempts" variant="soft">Back to Attempts</PrimaryButton>
                    <PrimaryButton href="/admin" variant="ghost">Back to Admin Dashboard</PrimaryButton>
                    <PrimaryButton href="/admin/quizzes" variant="soft">View Quiz</PrimaryButton>
                </div>
            </section>

            <section class="mx-auto mt-10 grid max-w-5xl gap-6 md:grid-cols-2 xl:grid-cols-4">
                <article class="soft-card p-6 text-center">
                    <p class="label">Final score</p>
                    <p class="mt-4 text-4xl font-black text-heritage-red">{{ formatPercentage(attempt?.percentage) }}</p>
                </article>
                <article class="soft-card p-6 text-center">
                    <p class="label">Correct answers</p>
                    <p class="mt-4 text-4xl font-black text-heritage-ink">{{ attempt?.correct_answers }}/{{ attempt?.total_questions }}</p>
                </article>
                <article class="soft-card p-6 text-center">
                    <p class="label">Points earned</p>
                    <p class="mt-4 text-4xl font-black text-heritage-gold-deep">{{ formatNumber(attempt?.score) }}</p>
                </article>
                <article class="soft-card p-6 text-center">
                    <p class="label">Status</p>
                    <div class="mt-5 flex justify-center">
                        <AppBadge :variant="attempt?.passed ? 'green' : 'red'">{{ attempt?.passed ? 'Passed' : 'Review' }}</AppBadge>
                    </div>
                </article>
            </section>

            <section class="mx-auto mt-8 grid max-w-5xl gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <article class="heritage-pattern rounded-[2rem] p-8 text-white shadow-soft">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[1.5rem] bg-white text-2xl font-black text-heritage-red">
                            {{ category?.icon || 'IQ' }}
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-heritage-gold">Learner</p>
                            <h2 class="mt-2 break-words text-2xl font-black">{{ learner?.name }}</h2>
                            <p class="mt-1 break-words text-sm font-semibold text-white/75">{{ learner?.email }}</p>
                        </div>
                    </div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-white/60">Attempts on quiz</p>
                            <p class="mt-1 text-2xl font-black text-heritage-gold">{{ formatNumber(learnerQuizStats.total_attempts) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-white/60">Learner best</p>
                            <p class="mt-1 text-2xl font-black text-heritage-gold">{{ formatPercentage(learnerQuizStats.best_percentage) }}</p>
                        </div>
                    </div>
                </article>

                <article class="soft-card p-8">
                    <AppBadge variant="navy">Quiz context</AppBadge>
                    <h2 class="mt-4 text-2xl font-black text-heritage-ink">{{ quiz?.title_en }}</h2>
                    <p v-if="quiz?.title_mk" class="mt-1 font-semibold text-heritage-muted">{{ quiz.title_mk }}</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-heritage-panel p-4">
                            <p class="label">Category</p>
                            <p class="mt-1 font-black text-heritage-ink">{{ category?.name_en }}</p>
                        </div>
                        <div class="rounded-2xl bg-heritage-panel p-4">
                            <p class="label">Best points</p>
                            <p class="mt-1 font-black text-heritage-ink">{{ formatNumber(learnerQuizStats.best_points) }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-sm font-semibold leading-6 text-heritage-muted">
                        Points shown on this page are for this specific attempt only. Learner total points continue to use the best attempt per quiz.
                    </p>
                </article>
            </section>

            <section class="mx-auto mt-10 max-w-5xl">
                <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <AppBadge variant="navy">Answer review</AppBadge>
                        <h2 class="mt-3 text-3xl font-black text-heritage-ink">Saved learner answers</h2>
                    </div>
                    <p class="text-sm font-bold text-heritage-muted">Loaded from the saved quiz attempt answer records.</p>
                </div>

                <div class="grid gap-4">
                    <article v-for="answer in answers" :key="answer.id" class="soft-card p-5 md:p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <AppBadge :variant="answer.is_correct ? 'green' : 'red'">
                                    {{ answer.is_correct ? 'Correct' : 'Review' }}
                                </AppBadge>
                                <h3 class="mt-3 text-xl font-black text-heritage-ink">
                                    {{ answer.number }}. {{ answer.question.question_en || 'Question text unavailable' }}
                                </h3>
                                <p v-if="answer.question.question_mk" class="mt-2 font-semibold text-heritage-muted">{{ answer.question.question_mk }}</p>
                            </div>
                            <div class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-muted">
                                {{ formatNumber(answer.points_awarded) }} pts
                            </div>
                        </div>

                        <PictureQuestionVisual
                            v-if="isPictureQuestion(answer.question)"
                            class="mt-5"
                            :metadata="answer.question.metadata || {}"
                            language="en"
                            compact
                        />

                        <div v-if="isSoundQuestion(answer.question)" class="mt-5 rounded-[1.5rem] border border-heritage-gold/40 bg-heritage-gold-faint p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="label">Sound clue</p>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-heritage-gold-deep">{{ answer.question.metadata?.audio_type || 'sound' }}</span>
                            </div>
                            <audio
                                v-if="answer.question.metadata?.audio_path"
                                class="mt-3 w-full"
                                :aria-label="soundAltText(answer.question)"
                                controls
                                preload="metadata"
                                :src="answer.question.metadata.audio_path"
                            />
                            <p v-else class="mt-3 rounded-2xl bg-white/70 p-4 text-sm font-bold text-heritage-gold-deep">No audio path was saved for this question.</p>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-2">
                            <div :class="['rounded-2xl border p-4', answer.is_correct ? 'border-emerald-200 bg-emerald-50' : 'border-heritage-red/20 bg-heritage-red-faint']">
                                <p class="label">Learner answer</p>
                                <p class="mt-2 font-black text-heritage-ink">{{ answerText(answer.selected_answer) }}</p>
                                <p v-if="secondaryAnswerText(answer.selected_answer)" class="mt-1 text-sm font-semibold text-heritage-muted">{{ secondaryAnswerText(answer.selected_answer) }}</p>
                            </div>
                            <div class="rounded-2xl border border-heritage-gold/40 bg-heritage-gold-faint p-4">
                                <p class="label">Correct answer</p>
                                <p class="mt-2 font-black text-heritage-ink">{{ answerText(answer.correct_answer, 'Correct answer unavailable') }}</p>
                                <p v-if="secondaryAnswerText(answer.correct_answer)" class="mt-1 text-sm font-semibold text-heritage-gold-deep">{{ secondaryAnswerText(answer.correct_answer) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <p v-if="answer.question.explanation_en" class="rounded-2xl bg-white/70 p-4 text-sm font-semibold leading-6 text-heritage-muted">
                                {{ answer.question.explanation_en }}
                            </p>
                            <p v-if="answer.question.explanation_mk" class="rounded-2xl bg-white/70 p-4 text-sm font-semibold leading-6 text-heritage-muted">
                                {{ answer.question.explanation_mk }}
                            </p>
                        </div>
                    </article>
                </div>
            </section>
        </template>
    </AdminLayout>
</template>
