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
    quizStartUrl,
} from '../api/makedoniq';

const category = ref(null);
const quizzes = ref([]);
const questions = ref([]);
const isLoading = ref(true);
const error = ref('');
const demoMapQuizSlug = 'macedonia-map-challenge-demo';
const mapQuizSlugs = [
    'macedonia-map-challenge-demo',
    'cities-of-macedonia-map-quiz',
    'lakes-and-nature-map-quiz',
    'landmarks-and-regions-map-quiz',
];

const mapQuizDetails = {
    'macedonia-map-challenge-demo': {
        badge: 'Demo',
        shortTitle: 'Demo map challenge',
        description: 'Try a short beginner-friendly map challenge with key places.',
        icon: 'PIN',
        accent: 'bg-heritage-red-faint text-heritage-red',
        action: 'Start demo',
    },
    'cities-of-macedonia-map-quiz': {
        badge: 'Cities',
        shortTitle: 'Cities map quiz',
        description: 'Practise recognising important Macedonian cities.',
        icon: 'CITY',
        accent: 'bg-heritage-gold-faint text-heritage-gold-deep',
        action: 'Start quiz',
    },
    'lakes-and-nature-map-quiz': {
        badge: 'Nature',
        shortTitle: 'Lakes and nature map quiz',
        description: 'Practise lakes, mountains, parks, and natural places.',
        icon: 'LAKE',
        accent: 'bg-sky-50 text-sky-800',
        action: 'Start quiz',
    },
    'landmarks-and-regions-map-quiz': {
        badge: 'Landmarks',
        shortTitle: 'Landmarks and regions map quiz',
        description: 'Explore landmarks, regions, old towns, and cultural places.',
        icon: 'MARK',
        accent: 'bg-heritage-navy-soft text-heritage-navy',
        action: 'Start quiz',
    },
};

const language = preferredLanguage();
const user = currentUser();

const mapQuestions = computed(() => questions.value.filter((question) => question.question_type === 'map_guess'));
const firstMapQuestion = computed(() => mapQuestions.value[0] || null);
const mapMetadata = computed(() => firstMapQuestion.value?.metadata || {});
const categoryName = computed(() => localizedText(category.value, 'name', language) || 'Geography');
const demoStartUrl = computed(() => quizStartUrl('geography', demoMapQuizSlug));
const mapQuizCards = computed(() => {
    const quizBySlug = new Map(
        quizzes.value
            .filter((quiz) => quiz.has_map_questions || mapQuizSlugs.includes(quiz.slug))
            .map((quiz) => [quiz.slug, quiz]),
    );

    return mapQuizSlugs
        .map((slug) => {
            const quiz = quizBySlug.get(slug);

            if (!quiz) {
                return null;
            }

            const details = mapQuizDetails[slug];
            const isLocked = Boolean(quiz.is_locked);
            const isCompleted = Boolean(quiz.user_progress?.completed);
            const startHref = quizStartUrl('geography', quiz.slug);

            return {
                ...quiz,
                ...details,
                title: localizedText(quiz, 'title', language) || details.shortTitle,
                description: localizedText(quiz, 'description', language) || details.description,
                difficultyLabel: difficultyLabel(quiz.difficulty),
                questionCount: quiz.questions_count || quiz.map_questions_count || 5,
                timeLabel: quiz.estimated_minutes ? `${quiz.estimated_minutes} min` : 'Self-paced',
                isLocked,
                isCompleted,
                startHref,
                registerHref: authHref('/register', startHref),
                loginHref: authHref('/login', startHref),
                buttonLabel: isCompleted ? 'Try again' : details.action,
            };
        })
        .filter(Boolean);
});
const heroQuestionCountLabel = computed(() => {
    const count = mapQuizCards.value.reduce((total, quiz) => total + Number(quiz.questionCount || 0), 0);

    return count > 0 ? `${count} map clues` : '20 map clues';
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
        const [categoryResponse, questionsResponse] = await Promise.all([
            fetchJson('/api/categories/geography'),
            fetchJson(`/api/quizzes/${demoMapQuizSlug}/questions`),
        ]);

        category.value = categoryResponse.data.category;
        quizzes.value = categoryResponse.data.quizzes || [];
        questions.value = questionsResponse.data.questions || [];
    } catch (caughtError) {
        error.value = caughtError.status === 404
            ? 'The Macedonia Map Challenge has not been seeded yet.'
            : caughtError.message || 'The map challenge could not be loaded.';
    } finally {
        isLoading.value = false;
    }
});

function authHref(path, intended) {
    return `${path}?intended=${encodeURIComponent(intended || window.location.pathname)}`;
}
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
                                    <AppBadge variant="navy">{{ mapQuizCards.length }} challenges</AppBadge>
                                    <AppBadge variant="red">{{ heroQuestionCountLabel }}</AppBadge>
                                </div>

                                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                    <PrimaryButton :href="demoStartUrl" class="w-full sm:w-auto" size="lg">Start demo challenge</PrimaryButton>
                                    <PrimaryButton href="#choose-map-challenge" class="w-full sm:w-auto" variant="gold" size="lg">Choose challenge</PrimaryButton>
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

                    <div id="choose-map-challenge" class="bg-heritage-bg/60 p-5 md:p-6 lg:p-8">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                            <div>
                                <AppBadge variant="gold">Choose a map challenge</AppBadge>
                                <h2 class="mt-4 text-3xl font-black text-heritage-ink">Choose a map challenge</h2>
                                <p class="mt-2 max-w-2xl text-sm font-bold leading-6 text-heritage-muted md:text-base">
                                    Start with the demo, then unlock cities, lakes, nature, landmarks, and regions.
                                </p>
                            </div>
                            <PrimaryButton href="/quizzes/geography" variant="ghost" class="w-full sm:w-auto">Geography quizzes</PrimaryButton>
                        </div>

                        <div class="mt-7 grid gap-5 lg:grid-cols-2">
                            <article
                                v-for="card in mapQuizCards"
                                :key="card.slug"
                                :class="['flex min-h-full flex-col rounded-3xl border bg-white p-5 shadow-card transition hover:-translate-y-1 hover:shadow-soft md:p-6', card.isLocked ? 'border-heritage-gold/55' : 'border-heritage-line/70 hover:border-heritage-red/30']"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <span :class="['flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-sm font-black shadow-inner', card.accent]">
                                        {{ card.icon }}
                                    </span>
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <AppBadge v-if="card.is_demo" variant="gold">Demo</AppBadge>
                                        <AppBadge v-if="card.isLocked" variant="neutral">Locked</AppBadge>
                                        <AppBadge variant="red">{{ card.badge }}</AppBadge>
                                    </div>
                                </div>

                                <h3 class="mt-5 text-2xl font-black leading-tight text-heritage-ink">{{ card.title }}</h3>
                                <p class="mt-3 flex-1 text-sm font-bold leading-7 text-heritage-muted md:text-base">{{ card.description }}</p>

                                <div class="mt-5 flex flex-wrap gap-2 text-sm font-bold text-heritage-muted">
                                    <span class="rounded-full border border-heritage-line/60 bg-heritage-panel px-3 py-1">{{ card.questionCount }} questions</span>
                                    <span class="rounded-full border border-heritage-line/60 bg-heritage-panel px-3 py-1">{{ card.difficultyLabel }}</span>
                                    <span class="rounded-full border border-heritage-line/60 bg-heritage-panel px-3 py-1">{{ card.timeLabel }}</span>
                                </div>

                                <div v-if="card.isLocked" class="mt-6 rounded-[1.25rem] border border-heritage-gold/45 bg-heritage-gold-faint p-4">
                                    <p class="text-xs font-black uppercase text-heritage-gold-deep">Create a free account to unlock</p>
                                    <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">Preview this challenge now, then sign up to play and save your score.</p>
                                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                        <PrimaryButton :href="card.registerHref" class="w-full" size="sm">Unlock free</PrimaryButton>
                                        <PrimaryButton :href="card.loginHref" class="w-full" variant="soft" size="sm">Log in</PrimaryButton>
                                    </div>
                                </div>

                                <PrimaryButton v-else :href="card.startHref" class="mt-6 w-full sm:w-auto" :variant="card.is_demo ? 'red' : 'gold'">
                                    {{ card.buttonLabel }}
                                </PrimaryButton>
                            </article>
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
