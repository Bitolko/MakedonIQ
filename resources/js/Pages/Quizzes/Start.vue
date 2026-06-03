<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import {
    ApiError,
    categoryUrl,
    currentCategorySlug,
    currentQuizSlug,
    currentUser,
    difficultyLabel,
    fetchJson,
    localizedText,
    preferredLanguage,
    quizActiveUrl,
} from '../../api/makedoniq';

const quiz = ref(null);
const questions = ref([]);
const isLoading = ref(true);
const error = ref('');
const isLocked = ref(false);
const language = preferredLanguage();
const user = currentUser();

const categorySlug = currentCategorySlug();
const quizSlug = currentQuizSlug();

const categoryName = computed(() => localizedText(quiz.value?.category, 'name', language));
const quizTitle = computed(() => localizedText(quiz.value, 'title', language));
const quizDescription = computed(() => localizedText(quiz.value, 'description', language));
const relatedLesson = computed(() => quiz.value?.related_lesson || null);
const relatedLessonTitle = computed(() => localizedText(relatedLesson.value, 'title', language));
const relatedLessonSummary = computed(() => localizedText(relatedLesson.value, 'summary', language));
const isMapChallenge = computed(() => Boolean(quiz.value?.has_map_questions || questions.value.some((question) => question.question_type === 'map_guess')));
const isPictureQuiz = computed(() => Boolean(quiz.value?.has_picture_questions || questions.value.some((question) => question.question_type === 'picture_choice')));
const isSoundQuiz = computed(() => Boolean(quiz.value?.has_sound_questions || questions.value.some((question) => question.question_type === 'sound_choice')));
const questionCount = computed(() => quiz.value?.questions_count || questions.value.length || 0);

const learnItems = computed(() => {
    if (isMapChallenge.value) {
        return [
            'Look at the highlighted point on the map',
            'Choose the correct city, lake, or landmark',
            'Use the answer choices while you learn the place names',
            'Submit normally for secure backend scoring',
        ];
    }

    if (isPictureQuiz.value) {
        return [
            'Look at the image or picture clue',
            'Use placeholders safely while final images are being prepared',
            'Choose the correct Macedonian food, place, letter, or culture answer',
            'Submit normally for secure backend scoring',
        ];
    }

    if (isSoundQuiz.value) {
        return [
            'Listen to the MP3 sound clue',
            'Choose the matching folklore song title',
            'Use the related lesson lyrics and context for review',
            'Submit normally for secure backend scoring',
        ];
    }

    if (!questions.value.length) {
        return [
            'Build confidence with bilingual Macedonian quiz prompts',
            'Practise recognising key words and ideas',
            'Prepare for a short, family-friendly learning quiz',
            'Track your progress once scoring is connected',
        ];
    }

    return questions.value.slice(0, 4).map((question) => localizedText(question, 'question', language));
});

const activeUrl = computed(() => (
    quiz.value ? quizActiveUrl(quiz.value.category.slug, quiz.value.slug) : quizActiveUrl(categorySlug, quizSlug)
));

const backUrl = computed(() => (
    quiz.value ? categoryUrl(quiz.value.category.slug) : categoryUrl(categorySlug)
));

const pointsAvailable = computed(() => {
    if (!quiz.value) {
        return 0;
    }

    return (quiz.value.questions_count || questions.value.length || 0) * (quiz.value.points_per_question || 10);
});

const overviewText = computed(() => {
    if (isMapChallenge.value) {
        return `A short geography round with ${questionCount.value} map clues. Results are scored securely after you submit.`;
    }

    if (isPictureQuiz.value) {
        return `A visual quiz with ${questionCount.value} picture clues. Some clues may use placeholders until final images are added.`;
    }

    if (isSoundQuiz.value) {
        return `A listening quiz with ${questionCount.value} sound clues. Results are scored securely after you submit.`;
    }

    return 'Questions are short, friendly, and built for bilingual learning. You can review your results after finishing.';
});

const guideTitle = computed(() => {
    if (isMapChallenge.value) {
        return 'How the challenge works';
    }

    if (isPictureQuiz.value) {
        return 'How the picture quiz works';
    }

    if (isSoundQuiz.value) {
        return 'How the sound quiz works';
    }

    return 'What you will learn';
});

const overviewLabel = computed(() => {
    if (isMapChallenge.value) {
        return 'Map overview';
    }

    if (isPictureQuiz.value) {
        return 'Picture overview';
    }

    return isSoundQuiz.value ? 'Sound overview' : 'Quiz overview';
});

const startButtonLabel = computed(() => {
    if (!user && quiz.value?.is_demo) {
        if (isMapChallenge.value) {
            return 'Start demo challenge';
        }

        if (isPictureQuiz.value) {
            return 'Start picture demo';
        }

        return isSoundQuiz.value ? 'Start sound demo' : 'Start demo';
    }

    if (isMapChallenge.value) {
        return 'Start Map Challenge';
    }

    if (isPictureQuiz.value) {
        return 'Start Picture Quiz';
    }

    return isSoundQuiz.value ? 'Start Sound Quiz' : 'Start Quiz';
});

onMounted(async () => {
    try {
        const [quizResponse, questionsResponse] = await Promise.all([
            fetchJson(`/api/quizzes/${quizSlug}`),
            fetchJson(`/api/quizzes/${quizSlug}/questions`),
        ]);

        quiz.value = quizResponse.data;
        questions.value = questionsResponse.data.questions || [];
    } catch (exception) {
        isLocked.value = exception instanceof ApiError && exception.status === 403;
        error.value = isLocked.value
            ? exception.message
            : 'This quiz could not be loaded. Please check that the seeded quiz exists.';
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
        <main class="page-shell grid gap-10 py-12 lg:grid-cols-[1.2fr_0.8fr] lg:items-start">
            <section v-if="isLoading" class="lg:col-span-2">
                <div class="soft-card min-h-96 animate-pulse p-8">
                    <div class="h-6 w-32 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-12 w-2/3 rounded-full bg-heritage-panel" />
                    <div class="mt-6 h-5 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-3 h-5 w-4/5 rounded-full bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="section-panel text-center lg:col-span-2">
                <div v-if="isLocked" class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-[1.25rem] border border-heritage-gold/40 bg-heritage-gold-faint text-xs font-black text-heritage-gold-deep shadow-card">
                    LOCK
                </div>
                <AppBadge :variant="isLocked ? 'gold' : 'red'">{{ isLocked ? 'Locked quiz' : 'Quiz unavailable' }}</AppBadge>
                <h1 class="mt-4 text-3xl font-black text-heritage-ink">
                    {{ isLocked ? 'This quiz is locked for guests.' : 'Quiz unavailable' }}
                </h1>
                <p class="mx-auto mt-3 max-w-2xl leading-7 text-heritage-muted">
                    {{ isLocked ? 'Create a free account to unlock all quizzes and save your progress.' : error }}
                </p>
                <div v-if="isLocked" class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <PrimaryButton :href="authHref('/register')">Create free account</PrimaryButton>
                    <PrimaryButton :href="authHref('/login')" variant="soft">Log in</PrimaryButton>
                    <PrimaryButton :href="backUrl" variant="ghost">Back to Quizzes</PrimaryButton>
                </div>
            </section>

            <template v-else>
            <section>
                <div class="flex flex-wrap gap-2">
                    <AppBadge>{{ categoryName }}</AppBadge>
                    <AppBadge v-if="quiz.is_demo" variant="gold">Demo</AppBadge>
                    <AppBadge v-if="isMapChallenge" variant="gold">Map Challenge</AppBadge>
                    <AppBadge v-if="isPictureQuiz" variant="gold">Picture Quiz</AppBadge>
                    <AppBadge v-if="isSoundQuiz" variant="gold">Sound Quiz</AppBadge>
                </div>
                <h1 class="mt-5 text-4xl font-black leading-tight text-heritage-red md:text-5xl">
                    {{ quizTitle }}
                </h1>
                <p class="mt-5 max-w-3xl text-lg leading-8 text-heritage-muted">
                    {{ quizDescription }}
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="metric-card text-center">
                        <p class="label">Difficulty</p>
                        <p class="mt-2 text-xl font-black text-heritage-ink">{{ difficultyLabel(quiz.difficulty) }}</p>
                    </div>
                    <div class="metric-card text-center">
                        <p class="label">Questions</p>
                        <p class="mt-2 text-xl font-black text-heritage-ink">{{ quiz.questions_count || questions.length }} items</p>
                    </div>
                    <div class="metric-card text-center">
                        <p class="label">Time</p>
                        <p class="mt-2 text-xl font-black text-heritage-ink">{{ quiz.estimated_minutes || 'Self-paced' }}<span v-if="quiz.estimated_minutes"> min</span></p>
                    </div>
                </div>

                <section class="section-panel mt-8">
                    <h2 class="text-2xl font-black text-heritage-ink">{{ guideTitle }}</h2>
                    <p v-if="isMapChallenge" class="mt-3 leading-7 text-heritage-muted">
                        Look at the highlighted point on the map and choose the correct city, lake, or landmark.
                    </p>
                    <p v-else-if="isPictureQuiz" class="mt-3 leading-7 text-heritage-muted">
                        Look at the image or picture clue and choose the correct answer. Some picture clues may use placeholders while images are being prepared.
                    </p>
                    <p v-else-if="isSoundQuiz" class="mt-3 leading-7 text-heritage-muted">
                        Play the sound clue and choose the folklore song title that matches it.
                    </p>
                    <div class="mt-6 grid gap-4">
                        <div v-for="(item, index) in learnItems" :key="item" class="flex gap-3 rounded-2xl bg-heritage-panel p-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white">{{ index + 1 }}</span>
                            <span class="font-semibold text-heritage-muted">{{ item }}</span>
                        </div>
                    </div>
                </section>

                <section v-if="relatedLesson" class="mt-8 rounded-[2rem] border border-heritage-gold/40 bg-heritage-gold-faint p-6">
                    <AppBadge variant="gold">Recommended lesson</AppBadge>
                    <h2 class="mt-4 text-2xl font-black text-heritage-ink">{{ relatedLessonTitle }}</h2>
                    <p class="mt-3 leading-7 text-heritage-gold-deep">{{ relatedLessonSummary }}</p>
                    <PrimaryButton :href="relatedLesson.url" class="mt-5" variant="soft">Read lesson first</PrimaryButton>
                </section>
            </section>

            <aside class="soft-card sticky top-28 p-6 md:p-8">
                <div class="rounded-[1.5rem] bg-heritage-panel p-5">
                    <p class="label">{{ overviewLabel }}</p>
                    <p class="mt-3 text-3xl font-black text-heritage-ink">Earn up to {{ pointsAvailable }} points</p>
                    <p class="mt-3 leading-7 text-heritage-muted">
                        {{ overviewText }}
                    </p>
                </div>
                <PrimaryButton :href="activeUrl" class="mt-6 w-full" size="lg">{{ startButtonLabel }}</PrimaryButton>
                <PrimaryButton :href="backUrl" variant="soft" class="mt-4 w-full">Back to quizzes</PrimaryButton>
            </aside>
            </template>
        </main>
    </PublicLayout>
</template>
