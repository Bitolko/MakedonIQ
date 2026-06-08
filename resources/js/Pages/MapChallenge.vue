<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../Components/PublicLayout.vue';
import AppBadge from '../Components/AppBadge.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import MacedoniaMap from '../Components/MacedoniaMap.vue';
import {
    currentUser,
    difficultyLabel,
    fetchJson,
    localizedText,
    preferredLanguage,
    quizActiveUrl,
    quizStartUrl,
} from '../api/makedoniq';

const quiz = ref(null);
const questions = ref([]);
const isLoading = ref(true);
const error = ref('');
const demoMapQuizSlug = 'macedonia-map-challenge-demo';

const language = preferredLanguage();
const user = currentUser();

const categoryName = computed(() => localizedText(quiz.value?.category, 'name', language));
const mapQuestions = computed(() => questions.value.filter((question) => question.question_type === 'map_guess'));
const firstMapQuestion = computed(() => mapQuestions.value[0] || null);
const mapMetadata = computed(() => firstMapQuestion.value?.metadata || {});
const activeUrl = computed(() => quiz.value ? quizActiveUrl(quiz.value.category.slug, quiz.value.slug) : `/quizzes/geography/${demoMapQuizSlug}/active`);
const startUrl = computed(() => quiz.value ? quizStartUrl(quiz.value.category.slug, quiz.value.slug) : `/quizzes/geography/${demoMapQuizSlug}/start`);
const mapQuestionCountLabel = computed(() => {
    const count = mapQuestions.value.length || quiz.value?.questions_count || 0;

    return count > 0 ? `${count} map clues` : 'Beginner map clues';
});

const featureCards = [
    {
        title: 'Cities',
        detail: 'Skopje, Bitola, Ohrid, Prilep, and regional centres.',
        visual: 'cities',
        tone: 'bg-heritage-red-faint text-heritage-red',
    },
    {
        title: 'Lakes',
        detail: 'Ohrid, Prespa, Dojran, and blue map clues.',
        visual: 'lakes',
        tone: 'bg-sky-50 text-sky-800',
    },
    {
        title: 'Mountains',
        detail: 'Highland terrain, parks, and ridgelines.',
        visual: 'mountains',
        tone: 'bg-heritage-gold-faint text-heritage-gold-deep',
    },
    {
        title: 'Landmarks',
        detail: 'Canyons, old towns, peaks, and cultural places.',
        visual: 'landmarks',
        tone: 'bg-heritage-navy-soft text-heritage-navy',
    },
];

const challengeSteps = [
    { step: '01', title: 'Look', detail: 'Study the highlighted marker.' },
    { step: '02', title: 'Guess', detail: 'Choose the city, lake, or landmark.' },
    { step: '03', title: 'Score', detail: 'Your answer is checked securely after submission.' },
];

onMounted(async () => {
    try {
        const [quizResponse, questionsResponse] = await Promise.all([
            fetchJson(`/api/quizzes/${demoMapQuizSlug}`),
            fetchJson(`/api/quizzes/${demoMapQuizSlug}/questions`),
        ]);

        quiz.value = quizResponse.data;
        questions.value = questionsResponse.data.questions || [];
    } catch (caughtError) {
        error.value = caughtError.status === 404
            ? 'The Macedonia Map Challenge has not been seeded yet.'
            : caughtError.message || 'The map challenge could not be loaded.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-10 md:py-14">
            <section v-if="isLoading" class="grid gap-8 lg:grid-cols-[1fr_0.95fr] lg:items-center">
                <div class="section-panel min-h-96 animate-pulse">
                    <div class="h-6 w-36 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-14 w-4/5 rounded-full bg-heritage-panel" />
                    <div class="mt-5 h-5 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-3 h-5 w-3/4 rounded-full bg-heritage-panel" />
                </div>
                <div class="soft-card min-h-80 animate-pulse p-6" />
            </section>

            <section v-else-if="error" class="section-panel mx-auto max-w-3xl text-center">
                <AppBadge variant="red">Map Challenge</AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-ink">Map challenge unavailable</h1>
                <p class="mx-auto mt-4 max-w-xl leading-7 text-heritage-muted">{{ error }}</p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <PrimaryButton href="/quizzes/geography" variant="soft">Geography quizzes</PrimaryButton>
                    <PrimaryButton href="/learn/geography">Learn geography</PrimaryButton>
                </div>
            </section>

            <template v-else>
                <section class="overflow-hidden rounded-[2.5rem] border border-heritage-gold/40 bg-white shadow-card">
                    <div class="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="relative overflow-hidden bg-heritage-panel p-6 md:p-8">
                            <div class="pointer-events-none absolute inset-0 opacity-55" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.12) 1px, transparent 0); background-size: 26px 26px;" />
                            <div class="relative flex h-full flex-col justify-center">
                                <div class="flex">
                                    <AppBadge variant="red">Interactive geography</AppBadge>
                                </div>
                                <h1 class="mt-5 max-w-2xl text-4xl font-black leading-tight text-heritage-ink sm:text-5xl">
                                    Macedonia Map Challenge
                                </h1>
                                <p class="mt-4 max-w-xl text-lg leading-8 text-heritage-muted">
                                    Study the highlighted place and guess the city, lake, or landmark.
                                </p>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    <AppBadge>{{ categoryName || 'Geography' }}</AppBadge>
                                    <AppBadge variant="navy">{{ difficultyLabel(quiz?.difficulty) }}</AppBadge>
                                    <AppBadge variant="red">{{ mapQuestionCountLabel }}</AppBadge>
                                </div>

                                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                    <PrimaryButton :href="activeUrl" class="w-full sm:w-auto" size="lg">Start Map Challenge</PrimaryButton>
                                    <PrimaryButton :href="startUrl" class="w-full sm:w-auto" variant="gold" size="lg">View quiz intro</PrimaryButton>
                                    <PrimaryButton href="/learn/geography" class="w-full sm:w-auto" variant="ghost" size="lg">Read Geography lessons</PrimaryButton>
                                </div>

                                <p v-if="!user" class="mt-5 rounded-2xl bg-heritage-gold-faint px-4 py-3 text-sm font-bold text-heritage-gold-deep">
                                    You can try this demo now. Create a free account to save your score and unlock the full quiz path.
                                </p>
                            </div>
                        </div>

                        <div class="p-5 md:p-6 lg:p-8">
                            <div class="relative rounded-3xl border border-heritage-gold/30 bg-white p-4 shadow-soft md:p-5">
                                <div class="mb-3 flex">
                                    <AppBadge variant="gold">Map preview</AppBadge>
                                </div>

                                <div class="rounded-[1.75rem] bg-heritage-navy p-1.5 shadow-inner">
                                    <MacedoniaMap
                                        :x="mapMetadata.map_x || 52"
                                        :y="mapMetadata.map_y || 28"
                                        :target-type="mapMetadata.target_type || 'city'"
                                        variant="wide"
                                    />
                                </div>

                                <div class="mt-4 rounded-[1.5rem] bg-heritage-panel px-4 py-3">
                                    <div>
                                        <p class="text-xs font-black uppercase text-heritage-muted">Challenge clue</p>
                                        <p class="mt-1 text-lg font-black text-heritage-ink">Find the highlighted place</p>
                                        <p class="mt-1 text-sm font-bold text-heritage-muted">Use the marker as your clue.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 bg-white p-5 md:p-6 lg:grid-cols-[1.05fr_0.95fr] lg:p-8">
                        <div>
                            <h2 class="mb-4 text-lg font-black text-heritage-ink">What you'll practise</h2>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <article v-for="item in featureCards" :key="item.title" class="flex min-h-28 gap-3 rounded-2xl bg-heritage-panel p-4">
                                    <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl shadow-inner', item.tone]">
                                        <div v-if="item.visual === 'cities'" class="relative h-7 w-7">
                                            <span class="absolute bottom-0 left-1 h-5 w-2 rounded-t bg-current opacity-45" />
                                            <span class="absolute bottom-0 left-3 h-7 w-2 rounded-t bg-current opacity-75" />
                                            <span class="absolute bottom-0 right-1 h-4 w-2 rounded-t bg-current opacity-55" />
                                        </div>
                                        <div v-else-if="item.visual === 'lakes'" class="h-6 w-8 rounded-[50%] bg-current opacity-60" />
                                        <div v-else-if="item.visual === 'mountains'" class="relative h-7 w-8">
                                            <span class="absolute bottom-0 left-0 h-0 w-0 border-x-[13px] border-b-[24px] border-x-transparent border-b-current opacity-55" />
                                            <span class="absolute bottom-0 right-0 h-0 w-0 border-x-[12px] border-b-[19px] border-x-transparent border-b-current opacity-80" />
                                        </div>
                                        <div v-else class="relative h-7 w-7">
                                            <span class="absolute inset-x-2 top-0 h-7 rounded-full bg-current opacity-70" />
                                            <span class="absolute bottom-1 left-1 h-2 w-5 rounded-full bg-current opacity-35" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-heritage-ink">{{ item.title }}</h3>
                                        <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-heritage-panel p-5">
                            <p class="text-xs font-black uppercase text-heritage-red">How it works</p>
                            <div class="mt-4 grid gap-3">
                                <article v-for="step in challengeSteps" :key="step.title" class="flex gap-3 rounded-2xl bg-white/85 p-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-heritage-red-faint text-xs font-black text-heritage-red">{{ step.step }}</span>
                                    <div>
                                        <h3 class="font-black text-heritage-ink">{{ step.title }}</h3>
                                        <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">{{ step.detail }}</p>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="!mapQuestions.length" class="mt-6 rounded-3xl bg-heritage-panel p-6 text-center">
                    <h3 class="text-xl font-black text-heritage-ink">No map clues yet</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-heritage-muted">
                        The challenge quiz exists, but it does not have map questions published yet.
                    </p>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
