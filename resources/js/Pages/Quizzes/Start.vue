<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import {
    categoryUrl,
    currentCategorySlug,
    currentQuizSlug,
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
const language = preferredLanguage();

const categorySlug = currentCategorySlug();
const quizSlug = currentQuizSlug();

const learnItems = computed(() => {
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

const categoryName = computed(() => localizedText(quiz.value?.category, 'name', language));
const quizTitle = computed(() => localizedText(quiz.value, 'title', language));
const quizDescription = computed(() => localizedText(quiz.value, 'description', language));

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

onMounted(async () => {
    try {
        const [quizResponse, questionsResponse] = await Promise.all([
            fetchJson(`/api/quizzes/${quizSlug}`),
            fetchJson(`/api/quizzes/${quizSlug}/questions`),
        ]);

        quiz.value = quizResponse.data;
        questions.value = questionsResponse.data.questions || [];
    } catch (exception) {
        error.value = 'This quiz could not be loaded. Please check that the seeded quiz exists.';
    } finally {
        isLoading.value = false;
    }
});
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
                <h1 class="text-3xl font-black text-heritage-ink">Quiz unavailable</h1>
                <p class="mx-auto mt-3 max-w-2xl leading-7 text-heritage-muted">{{ error }}</p>
            </section>

            <template v-else>
            <section>
                <AppBadge>{{ categoryName }}</AppBadge>
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
                    <h2 class="text-2xl font-black text-heritage-ink">What you will learn</h2>
                    <div class="mt-6 grid gap-4">
                        <div v-for="(item, index) in learnItems" :key="item" class="flex gap-3 rounded-2xl bg-heritage-panel p-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white">{{ index + 1 }}</span>
                            <span class="font-semibold text-heritage-muted">{{ item }}</span>
                        </div>
                    </div>
                </section>
            </section>

            <aside class="soft-card sticky top-28 p-6 md:p-8">
                <div class="rounded-[1.5rem] bg-heritage-panel p-5">
                    <p class="label">Quiz overview</p>
                    <p class="mt-3 text-3xl font-black text-heritage-ink">Earn up to {{ pointsAvailable }} points</p>
                    <p class="mt-3 leading-7 text-heritage-muted">
                        Questions are short, friendly, and built for bilingual learning. You can review your results after finishing.
                    </p>
                </div>
                <PrimaryButton :href="activeUrl" class="mt-6 w-full" size="lg">Start Quiz</PrimaryButton>
                <PrimaryButton :href="backUrl" variant="soft" class="mt-4 w-full">Back to quizzes</PrimaryButton>
            </aside>
            </template>
        </main>
    </PublicLayout>
</template>
