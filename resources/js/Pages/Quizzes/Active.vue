<script setup>
import { computed, onMounted, ref } from 'vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import {
    categoryUrl,
    currentCategorySlug,
    currentQuizSlug,
    fetchJson,
    quizResultsUrl,
} from '../../api/makedoniq';

const language = ref('EN');
const quiz = ref(null);
const questions = ref([]);
const selectedAnswers = ref({});
const currentIndex = ref(0);
const isLoading = ref(true);
const error = ref('');

const categorySlug = currentCategorySlug();
const quizSlug = currentQuizSlug();
const optionLabels = ['A', 'B', 'C', 'D'];

const currentQuestion = computed(() => questions.value[currentIndex.value] || null);
const selectedAnswerId = computed(() => selectedAnswers.value[currentQuestion.value?.id] || null);
const totalQuestions = computed(() => questions.value.length);
const answeredCount = computed(() => Object.keys(selectedAnswers.value).length);
const isLastQuestion = computed(() => currentIndex.value >= totalQuestions.value - 1);
const categoryHref = computed(() => categoryUrl(quiz.value?.category?.slug || categorySlug));
const resultsHref = computed(() => quizResultsUrl(quiz.value?.category?.slug || categorySlug, quiz.value?.slug || quizSlug));
const progressPercent = computed(() => {
    if (!totalQuestions.value) {
        return 0;
    }

    return Math.round(((currentIndex.value + 1) / totalQuestions.value) * 100);
});

const displayQuestion = computed(() => {
    if (!currentQuestion.value) {
        return '';
    }

    return language.value === 'MK' && currentQuestion.value.question_mk
        ? currentQuestion.value.question_mk
        : currentQuestion.value.question_en;
});

function answerText(answer) {
    return language.value === 'MK' && answer.answer_mk ? answer.answer_mk : answer.answer_en;
}

function selectAnswer(answerId) {
    selectedAnswers.value = {
        ...selectedAnswers.value,
        [currentQuestion.value.id]: answerId,
    };
}

function goNext() {
    if (!isLastQuestion.value) {
        currentIndex.value += 1;
    }
}

onMounted(async () => {
    try {
        const response = await fetchJson(`/api/quizzes/${quizSlug}/questions`);
        quiz.value = response.data.quiz;
        questions.value = response.data.questions || [];
    } catch (exception) {
        error.value = 'This quiz could not be loaded. Please check that the seeded questions exist.';
    } finally {
        isLoading.value = false;
    }
});
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
                            <button :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'EN' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'EN'">EN</button>
                            <button :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'MK' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'MK'">MK</button>
                        </div>
                        <div class="rounded-full bg-heritage-gold-faint px-4 py-2 text-sm font-black text-heritage-gold-deep">{{ answeredCount }} saved</div>
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
                    <h1 class="text-3xl font-black text-heritage-ink">Quiz unavailable</h1>
                    <p class="mt-3 leading-7 text-heritage-muted">{{ error }}</p>
                </div>
            </section>

            <template v-else-if="currentQuestion">
                <section class="mx-auto w-full max-w-3xl">
                    <div class="soft-card p-6 text-center md:p-8">
                        <span class="eyebrow">{{ quiz.title_en }}</span>
                        <h1 class="mt-6 text-3xl font-black leading-tight text-heritage-ink md:text-5xl">
                            {{ displayQuestion }}
                        </h1>
                        <div class="mx-auto mt-8 rounded-[2rem] bg-heritage-panel p-4">
                            <p class="text-sm font-bold leading-7 text-heritage-muted">
                                Choose the answer that feels right. Final scoring will be checked securely on the backend in the next step.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="mx-auto mt-6 grid w-full max-w-3xl gap-4 md:grid-cols-2">
                    <button
                        v-for="(answer, index) in currentQuestion.answers"
                        :key="answer.id"
                        type="button"
                        :class="[
                            'relative flex min-h-20 items-center rounded-2xl border-2 p-5 text-left shadow-card transition active:scale-[0.99]',
                            selectedAnswerId === answer.id ? 'border-heritage-gold bg-heritage-gold-faint text-heritage-gold-deep' : 'border-heritage-line bg-white text-heritage-ink hover:border-heritage-gold hover:bg-heritage-gold-faint/40',
                        ]"
                        @click="selectAnswer(answer.id)"
                    >
                        <span :class="['mr-4 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-black', selectedAnswerId === answer.id ? 'bg-heritage-red text-white' : 'bg-heritage-panel text-heritage-muted']">
                            {{ optionLabels[index] || index + 1 }}
                        </span>
                        <span class="text-lg font-black">{{ answerText(answer) }}</span>
                        <span v-if="selectedAnswerId === answer.id" class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-heritage-gold text-sm font-black text-heritage-navy">OK</span>
                    </button>
                </section>
            </template>
        </main>

        <footer class="border-t-4 border-heritage-gold bg-white">
            <div class="page-shell flex flex-col items-center justify-between gap-5 py-5 md:flex-row">
                <div class="flex items-center gap-4 text-center md:text-left">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-heritage-gold-faint text-lg font-black text-heritage-gold-deep">
                        {{ selectedAnswerId ? 'OK' : '?' }}
                    </div>
                    <div>
                        <p class="text-xl font-black text-heritage-ink">{{ selectedAnswerId ? 'Answer saved' : 'Choose an answer' }}</p>
                        <p class="text-sm text-heritage-muted">Correctness is not exposed in this public quiz screen. Secure scoring comes next.</p>
                    </div>
                </div>
                <PrimaryButton v-if="isLastQuestion" :href="resultsHref" size="lg">Submit quiz</PrimaryButton>
                <PrimaryButton v-else size="lg" @click="goNext">Next</PrimaryButton>
            </div>
        </footer>
    </div>
</template>
