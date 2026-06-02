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

const language = preferredLanguage();
const user = currentUser();

const quizTitle = computed(() => localizedText(quiz.value, 'title', language));
const quizDescription = computed(() => localizedText(quiz.value, 'description', language));
const categoryName = computed(() => localizedText(quiz.value?.category, 'name', language));
const mapQuestions = computed(() => questions.value.filter((question) => question.question_type === 'map_guess'));
const firstMapQuestion = computed(() => mapQuestions.value[0] || null);
const mapMetadata = computed(() => firstMapQuestion.value?.metadata || {});
const activeUrl = computed(() => quiz.value ? quizActiveUrl(quiz.value.category.slug, quiz.value.slug) : '/quizzes/geography/macedonia-map-challenge/active');
const startUrl = computed(() => quiz.value ? quizStartUrl(quiz.value.category.slug, quiz.value.slug) : '/quizzes/geography/macedonia-map-challenge/start');

const featureCards = [
    { title: 'Cities', detail: 'Skopje, Bitola, Ohrid, Prilep, and regional centres.' },
    { title: 'Lakes', detail: 'Ohrid, Prespa, Dojran, and blue map clues.' },
    { title: 'Mountains', detail: 'Highland terrain, national parks, and ridgelines.' },
    { title: 'Landmarks', detail: 'Canyons, peaks, and places that shape geography.' },
];

onMounted(async () => {
    try {
        const [quizResponse, questionsResponse] = await Promise.all([
            fetchJson('/api/quizzes/macedonia-map-challenge'),
            fetchJson('/api/quizzes/macedonia-map-challenge/questions'),
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
                <section class="grid gap-8 rounded-[2.5rem] border border-heritage-line/50 bg-white p-6 shadow-card md:p-8 lg:grid-cols-[1fr_0.95fr] lg:items-center">
                    <div class="space-y-5">
                        <AppBadge variant="red">Interactive geography</AppBadge>
                        <h1 class="max-w-3xl text-4xl font-black leading-tight text-heritage-ink sm:text-5xl lg:text-6xl">
                            Macedonia Map Challenge
                        </h1>
                        <p class="max-w-2xl text-lg leading-8 text-heritage-muted">
                            Study the highlighted place and guess the city, lake, or landmark.
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <AppBadge>{{ categoryName || 'Geography' }}</AppBadge>
                            <AppBadge v-if="quiz?.is_demo" variant="gold">Demo</AppBadge>
                            <AppBadge variant="navy">{{ difficultyLabel(quiz?.difficulty) }}</AppBadge>
                            <AppBadge variant="red">{{ mapQuestions.length || quiz?.questions_count || 0 }} map questions</AppBadge>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <article v-for="item in featureCards" :key="item.title" class="rounded-[1.25rem] bg-heritage-panel p-5">
                                <p class="text-lg font-black text-heritage-red">{{ item.title }}</p>
                                <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                            </article>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton :href="activeUrl" size="lg">Start Map Challenge</PrimaryButton>
                            <PrimaryButton :href="startUrl" variant="soft" size="lg">View quiz intro</PrimaryButton>
                        </div>

                        <p v-if="!user" class="mt-4 rounded-2xl bg-heritage-gold-faint px-4 py-3 text-sm font-bold text-heritage-gold-deep">
                            You can try this demo now. Create a free account to save your score and unlock the full quiz path.
                        </p>
                    </div>

                    <div class="grid gap-4">
                        <div class="rounded-[2rem] bg-heritage-navy p-4">
                            <MacedoniaMap
                                :x="mapMetadata.map_x || 52"
                                :y="mapMetadata.map_y || 28"
                                :target-type="mapMetadata.target_type || 'city'"
                                variant="wide"
                            />
                        </div>
                        <div class="rounded-[2rem] bg-heritage-navy p-6 text-white shadow-card">
                            <p class="label text-heritage-gold">How it works</p>
                            <h2 class="mt-2 text-2xl font-black">{{ quizTitle || 'Guess the highlighted place' }}</h2>
                            <p class="mt-3 leading-7 text-white/80">
                                The marker gives you the geography clue. Your answer is still scored securely by MakedonIQ after you submit.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="mt-12 rounded-[2rem] bg-white p-6 shadow-card md:p-8">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="navy">Challenge preview</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink">Places you will practise</h2>
                        </div>
                        <PrimaryButton href="/learn/geography" variant="soft">Read Geography lessons</PrimaryButton>
                    </div>

                    <div v-if="mapQuestions.length" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <article v-for="(question, index) in mapQuestions.slice(0, 8)" :key="question.id" class="rounded-2xl border border-heritage-line bg-heritage-panel p-4 transition hover:-translate-y-1 hover:bg-white hover:shadow-card">
                            <p class="text-sm font-black uppercase text-heritage-red">Map clue {{ index + 1 }}</p>
                            <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">
                                {{ localizedText(question, 'question', language) }}
                            </p>
                        </article>
                    </div>
                    <div v-else class="mt-6 rounded-2xl border border-heritage-line bg-heritage-panel p-5 text-center">
                        <h3 class="text-xl font-black text-heritage-ink">No map clues yet</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-heritage-muted">
                            The challenge quiz exists, but it does not have map questions published yet.
                        </p>
                    </div>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
