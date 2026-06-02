<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import QuizCard from '../../Components/QuizCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import ProgressBar from '../../Components/ProgressBar.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { categoryUrl, currentCategorySlug, difficultyLabel, fetchJson, localizedText, preferredLanguage, quizStartUrl } from '../../api/makedoniq';

const category = ref(null);
const categories = ref([]);
const quizzes = ref([]);
const userProgress = ref(null);
const isLoading = ref(true);
const error = ref('');
const language = preferredLanguage();

const activeSlug = currentCategorySlug();

const categoryLinks = computed(() => categories.value.map((item) => ({
    label: localizedText(item, 'name', language),
    href: categoryUrl(item.slug),
    active: item.slug === activeSlug,
})));

const quizCards = computed(() => quizzes.value.map((quiz) => ({
    title: localizedText(quiz, 'title', language),
    description: localizedText(quiz, 'description', language),
    difficulty: difficultyLabel(quiz.difficulty),
    questions: quiz.questions_count || 0,
    time: quiz.estimated_minutes ? `${quiz.estimated_minutes} min` : 'Self-paced',
    progress: quiz.user_progress?.best_percentage || 0,
    status: quizStatus(quiz),
    actionLabel: quizActionLabel(quiz),
    progressLabel: quizProgressLabel(quiz),
    progressDetails: quizProgressDetails(quiz),
    href: quizStartUrl(category.value?.slug || activeSlug, quiz.slug),
    isDemo: Boolean(quiz.is_demo),
    isLocked: Boolean(quiz.is_locked),
    lockedMessage: 'Create a free account to unlock',
    registerHref: authHref('/register', quizStartUrl(category.value?.slug || activeSlug, quiz.slug)),
    loginHref: authHref('/login', quizStartUrl(category.value?.slug || activeSlug, quiz.slug)),
    isMapChallenge: Boolean(quiz.has_map_questions),
})));

const categoryName = computed(() => localizedText(category.value, 'name', language));
const categoryDescription = computed(() => localizedText(category.value, 'description', language));
const isProgressAuthenticated = computed(() => Boolean(userProgress.value?.is_authenticated));
const completedQuizzes = computed(() => userProgress.value?.completed_quizzes ?? 0);
const totalQuizzes = computed(() => userProgress.value?.total_quizzes ?? quizzes.value.length);
const progressPercentage = computed(() => Number(userProgress.value?.progress_percentage || 0));
const progressSummary = computed(() => `${completedQuizzes.value} of ${totalQuizzes.value} quizzes completed`);
const progressMessage = computed(() => userProgress.value?.message || 'Your saved quiz attempts are counted here.');
const bestScore = computed(() => formatPercentage(userProgress.value?.best_percentage));
const averageScore = computed(() => formatPercentage(userProgress.value?.average_percentage));
const totalPoints = computed(() => userProgress.value?.total_points ?? 0);

onMounted(async () => {
    try {
        const [categoryResponse, categoriesResponse] = await Promise.all([
            fetchJson(`/api/categories/${activeSlug}`),
            fetchJson('/api/categories'),
        ]);

        category.value = categoryResponse.data.category;
        quizzes.value = categoryResponse.data.quizzes || [];
        userProgress.value = categoryResponse.data.user_progress || null;
        categories.value = categoriesResponse.data || [];
    } catch (exception) {
        error.value = 'This category could not be loaded. Please check the seeded quiz data.';
    } finally {
        isLoading.value = false;
    }
});

function quizStatus(quiz) {
    if (quiz.is_locked) {
        return 'Locked';
    }

    if (!quiz.user_progress) {
        return 'Start';
    }

    return quiz.user_progress.completed ? 'Completed' : 'Not started';
}

function quizActionLabel(quiz) {
    if (quiz.is_locked) {
        return 'Unlock free';
    }

    if (quiz.is_demo && !quiz.user_progress) {
        return 'Start demo';
    }

    return quiz.user_progress?.completed ? 'Try again' : 'Start';
}

function quizProgressLabel(quiz) {
    if (!quiz.user_progress) {
        return 'Progress';
    }

    if (!quiz.user_progress.completed) {
        return 'Not started';
    }

    return `Best: ${formatPercentage(quiz.user_progress.best_percentage)}`;
}

function quizProgressDetails(quiz) {
    const progress = quiz.user_progress;

    if (!progress?.completed) {
        return [];
    }

    const details = [`${progress.attempts_count} ${progress.attempts_count === 1 ? 'attempt' : 'attempts'}`];

    if (progress.last_attempted_at) {
        details.push(`Last: ${formatDate(progress.last_attempted_at)}`);
    }

    return details;
}

function formatPercentage(value) {
    if (value === null || value === undefined) {
        return '--';
    }

    return `${Math.round(Number(value))}%`;
}

function formatDate(value) {
    return new Intl.DateTimeFormat('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function authHref(path, intendedUrl) {
    return `${path}?intended=${encodeURIComponent(intendedUrl)}`;
}
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-8 py-10 lg:grid-cols-[16rem_1fr]">
            <aside class="hidden self-start rounded-[1.5rem] bg-white p-5 shadow-card lg:block">
                <h2 class="text-lg font-black text-heritage-ink">Categories</h2>
                <nav class="mt-5 grid gap-2">
                    <a
                        v-for="item in categoryLinks"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'rounded-2xl px-4 py-3 text-sm font-black transition',
                            item.active ? 'bg-heritage-gold-faint text-heritage-gold-deep shadow-card' : 'text-heritage-muted hover:bg-heritage-panel',
                        ]"
                    >
                        {{ item.label }}
                    </a>
                </nav>
            </aside>

            <section v-if="isLoading" class="grid gap-6">
                <div class="heritage-pattern min-h-72 animate-pulse rounded-[2rem] p-8" />
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="index in 3" :key="index" class="soft-card min-h-80 animate-pulse p-6">
                        <div class="h-12 w-12 rounded-2xl bg-heritage-panel" />
                        <div class="mt-8 h-7 w-4/5 rounded-full bg-heritage-panel" />
                        <div class="mt-4 h-4 w-full rounded-full bg-heritage-panel" />
                        <div class="mt-3 h-4 w-3/4 rounded-full bg-heritage-panel" />
                    </div>
                </div>
            </section>

            <section v-else-if="error" class="section-panel text-center">
                <h1 class="text-3xl font-black text-heritage-ink">Category unavailable</h1>
                <p class="mx-auto mt-3 max-w-2xl leading-7 text-heritage-muted">{{ error }}</p>
            </section>

            <section v-else>
                <div class="heritage-pattern overflow-hidden rounded-[2rem] p-8 text-white md:p-10">
                    <div class="max-w-2xl">
                        <AppBadge variant="gold">Quiz category</AppBadge>
                        <h1 class="mt-5 text-4xl font-black md:text-5xl">{{ categoryName }}</h1>
                        <p class="mt-4 text-lg leading-8 text-white/80">
                            {{ categoryDescription }}
                        </p>
                    </div>
                    <div class="mt-8 max-w-2xl rounded-2xl bg-white/10 p-5 backdrop-blur">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-heritage-gold">Category progress</p>
                                <p class="mt-2 text-xl font-black">{{ isProgressAuthenticated ? progressSummary : progressMessage }}</p>
                                <p v-if="isProgressAuthenticated" class="mt-1 text-sm font-semibold text-white/75">
                                    {{ userProgress.total_attempts || 0 }} completed {{ (userProgress.total_attempts || 0) === 1 ? 'attempt' : 'attempts' }} in this category
                                </p>
                            </div>
                            <p class="text-4xl font-black text-heritage-gold">{{ Math.round(progressPercentage) }}%</p>
                        </div>

                        <div class="mt-5">
                            <ProgressBar :value="progressPercentage" tone="gold" />
                        </div>

                        <div v-if="isProgressAuthenticated" class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/10 p-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-white/60">Best score</p>
                                <p class="mt-1 text-lg font-black">{{ bestScore }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-white/60">Average</p>
                                <p class="mt-1 text-lg font-black">{{ averageScore }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-white/60">Points</p>
                                <p class="mt-1 text-lg font-black">{{ totalPoints }}</p>
                            </div>
                        </div>

                        <div v-else class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton href="/login" variant="gold">Login</PrimaryButton>
                            <PrimaryButton href="/register" variant="soft">Create free account</PrimaryButton>
                        </div>
                    </div>
                </div>

                <section v-if="activeSlug === 'geography'" class="mt-8 rounded-[2rem] border border-heritage-gold/40 bg-white p-6 shadow-card md:p-8">
                    <AppBadge variant="gold">Interactive geography</AppBadge>
                    <div class="mt-4 grid gap-5 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <h2 class="text-2xl font-black text-heritage-ink">Macedonia Map Challenge</h2>
                            <p class="mt-2 leading-7 text-heritage-muted">Practise geography by guessing the highlighted city, lake, or landmark on a simple illustrated map.</p>
                        </div>
                        <a href="/map-challenge" class="pressable-gold inline-flex justify-center rounded-2xl px-6 py-3 text-sm font-black">Open challenge</a>
                    </div>
                </section>

                <div class="mt-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-2xl font-black text-heritage-ink">Available quizzes</h2>
                        <p class="mt-1 text-heritage-muted">Start with the basics or continue where you left off.</p>
                    </div>
                    <AppBadge variant="navy">{{ quizzes.length }} quizzes</AppBadge>
                </div>

                <div v-if="quizCards.length" class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <QuizCard v-for="quiz in quizCards" :key="quiz.href" :quiz="quiz" />
                </div>

                <div v-else class="section-panel mt-6 text-center">
                    <h2 class="text-2xl font-black text-heritage-ink">No quizzes yet</h2>
                    <p class="mt-3 text-heritage-muted">Published quizzes for this category will appear here.</p>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
