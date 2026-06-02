<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    difficultyLabel,
    currentUser,
    getLessons,
    learnCategoryUrl,
    lessonUrl,
    localizedText,
    preferredLanguage,
} from '../../api/makedoniq';

const categories = ref([]);
const lessons = ref([]);
const isLoading = ref(true);
const error = ref('');
const language = preferredLanguage();
const user = currentUser();
const isGuest = computed(() => !user);

const categoryVisuals = {
    'macedonian-language': {
        icon: 'MK',
        accent: 'text-heritage-red',
        note: 'Phrases, greetings, and everyday vocabulary.',
    },
    'macedonian-alphabet': {
        icon: 'А',
        accent: 'text-heritage-gold-deep',
        note: 'Cyrillic letters, sounds, and first words.',
    },
    geography: {
        icon: 'MAP',
        accent: 'text-heritage-navy',
        note: 'Cities, lakes, mountains, and map practice.',
    },
    'history-of-macedonia': {
        icon: 'H',
        accent: 'text-heritage-red',
        note: 'Places, stories, monuments, and memory.',
    },
    'culture-and-traditions': {
        icon: 'ORO',
        accent: 'text-heritage-gold-deep',
        note: 'Family, celebrations, dance, and customs.',
    },
    'food-and-music': {
        icon: 'FM',
        accent: 'text-heritage-navy',
        note: 'Tastes, rhythms, songs, and celebrations.',
    },
};

const categoryCards = computed(() => categories.value.map((category) => ({
    ...category,
    title: localizedText(category, 'name', language),
    description: localizedText(category, 'description', language),
    href: learnCategoryUrl(category.slug),
    visual: categoryVisuals[category.slug] || {
        icon: category.icon || category.name_en?.slice(0, 2).toUpperCase() || 'IQ',
        accent: 'text-heritage-red',
        note: localizedText(category, 'description', language),
    },
})));

const featuredLessons = computed(() => lessons.value.slice(0, 6).map((lesson) => ({
    ...lesson,
    title: localizedText(lesson, 'title', language),
    summary: localizedText(lesson, 'summary', language),
    category: language === 'mk' && lesson.category_name_mk ? lesson.category_name_mk : lesson.category_name_en,
    href: lessonUrl(lesson.category_slug, lesson.slug),
    isDemo: Boolean(lesson.is_demo),
    isLocked: Boolean(lesson.is_locked),
    relatedQuizLocked: Boolean(lesson.related_quiz?.is_locked),
    actionLabel: user ? 'Start lesson' : (lesson.is_demo ? 'Try demo' : 'Start lesson'),
    registerHref: authHref('/register', lessonUrl(lesson.category_slug, lesson.slug)),
    loginHref: authHref('/login', lessonUrl(lesson.category_slug, lesson.slug)),
    relatedQuizRegisterHref: authHref('/register', lesson.related_quiz?.start_url || lessonUrl(lesson.category_slug, lesson.slug)),
})));

const demoItems = [
    {
        title: 'Basic Macedonian Greetings',
        detail: 'Try a friendly first lesson and quiz.',
        href: '/learn/macedonian-language/basic-macedonian-greetings',
        cta: 'Try demo lesson',
    },
    {
        title: 'Cyrillic Alphabet Basics',
        detail: 'Preview letters, sounds, and first words.',
        href: '/learn/macedonian-alphabet/introduction-to-macedonian-cyrillic-alphabet',
        cta: 'Try demo lesson',
    },
    {
        title: 'Macedonia Map Challenge',
        detail: 'Guess cities, lakes, and landmarks on the map.',
        href: '/map-challenge',
        cta: 'Open demo',
    },
];

onMounted(async () => {
    try {
        const response = await getLessons();
        categories.value = response.data.categories || [];
        lessons.value = response.data.lessons || [];
    } catch (caughtError) {
        error.value = caughtError.message || 'Lessons could not be loaded.';
    } finally {
        isLoading.value = false;
    }
});

function authHref(path, intendedUrl) {
    return `${path}?intended=${encodeURIComponent(intendedUrl)}`;
}
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-8 md:py-12">
            <section class="relative overflow-hidden rounded-[2.5rem] border border-heritage-line/40 bg-heritage-panel p-8 shadow-card md:p-14">
                <div class="pointer-events-none absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 2px 2px, rgba(164,0,0,0.18) 1px, transparent 0); background-size: 32px 32px;" />
                <div class="relative mx-auto max-w-3xl text-center">
                    <AppBadge variant="red">Learn</AppBadge>
                    <h1 class="mt-5 text-4xl font-black leading-tight text-heritage-red md:text-6xl">Learn Macedonian step by step</h1>
                    <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-heritage-navy/80">
                        {{ isGuest ? 'Try a few lessons free, then create an account to unlock all lessons, quizzes, progress tracking, and saved results.' : 'Explore published lessons, practise with quizzes, and keep your learning moving at your own pace.' }}
                    </p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <PrimaryButton href="#learn-categories" size="lg">Start learning</PrimaryButton>
                        <PrimaryButton href="/quizzes" variant="gold" size="lg">Take a quiz</PrimaryButton>
                        <PrimaryButton href="/map-challenge" variant="white" size="lg">Map Challenge</PrimaryButton>
                    </div>
                </div>
                </section>

                <section v-if="isGuest" class="mt-10 rounded-[2rem] border border-heritage-gold/40 bg-white p-6 shadow-card md:p-8">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="gold">Try these demos</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink">Start with a free preview</h2>
                        </div>
                        <PrimaryButton href="/register" variant="soft">Create free account</PrimaryButton>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <article v-for="item in demoItems" :key="item.title" class="rounded-[1.5rem] border border-heritage-line bg-heritage-panel p-5">
                            <p class="text-xs font-black uppercase text-heritage-red">Demo</p>
                            <h3 class="mt-2 text-xl font-black text-heritage-ink">{{ item.title }}</h3>
                            <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                            <PrimaryButton :href="item.href" class="mt-4 w-full" size="sm" variant="soft">{{ item.cta }}</PrimaryButton>
                        </article>
                    </div>
                </section>

                <section v-else class="mt-10 rounded-[2rem] border border-heritage-gold/40 bg-white p-6 shadow-card md:p-8">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="gold">Continue learning</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink">Pick up your learning path</h2>
                        </div>
                        <PrimaryButton href="/dashboard" variant="soft">Open dashboard</PrimaryButton>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-4">
                        <PrimaryButton href="#learn-categories" class="w-full" variant="soft">Browse all lessons</PrimaryButton>
                        <PrimaryButton href="/quizzes" class="w-full" variant="soft">Take a quiz</PrimaryButton>
                        <PrimaryButton href="/progress" class="w-full" variant="soft">View progress</PrimaryButton>
                        <PrimaryButton href="/map-challenge" class="w-full" variant="soft">Try Map Challenge</PrimaryButton>
                    </div>
                </section>

            <section v-if="isLoading" class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="index in 6" :key="index" class="soft-card min-h-60 animate-pulse p-6">
                    <div class="h-10 w-24 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-7 w-3/4 rounded-full bg-heritage-panel" />
                    <div class="mt-4 h-4 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-3 h-4 w-2/3 rounded-full bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="section-panel mt-8 text-center">
                <AppBadge variant="red">Lessons unavailable</AppBadge>
                <h2 class="mt-4 text-2xl font-black text-heritage-ink">We could not load Learn content</h2>
                <p class="mt-2 text-heritage-muted">{{ error }}</p>
            </section>

            <template v-else>
                <section id="learn-categories" class="mt-12 scroll-mt-24">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-3xl font-black text-heritage-ink">Explore categories</h2>
                            <p class="mt-2 text-heritage-muted">Choose a topic, read a short lesson, then take the matching quiz.</p>
                        </div>
                        <AppBadge variant="gold">Select your path</AppBadge>
                    </div>

                    <div v-if="categoryCards.length" class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <a
                            v-for="category in categoryCards"
                            :key="category.slug"
                            :href="category.href"
                            class="soft-card group flex min-h-64 flex-col justify-between p-7 transition hover:-translate-y-1 hover:border-heritage-red/25 hover:shadow-soft"
                        >
                            <div>
                                <span :class="['flex h-14 w-14 items-center justify-center rounded-2xl bg-heritage-panel text-base font-black shadow-inner', category.visual.accent]">
                                    {{ category.visual.icon }}
                                </span>
                                <h3 class="mt-6 text-2xl font-black text-heritage-ink group-hover:text-heritage-red">{{ category.title }}</h3>
                                <p class="mt-3 leading-7 text-heritage-muted">{{ category.description || category.visual.note }}</p>
                            </div>
                            <div class="mt-6 flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-heritage-red">Start learning</span>
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-heritage-red-faint text-lg font-black text-heritage-red transition group-hover:bg-heritage-red group-hover:text-white">›</span>
                            </div>
                        </a>
                    </div>

                    <div v-else class="section-panel mt-6 text-center">
                        <h3 class="text-2xl font-black text-heritage-ink">No lessons yet</h3>
                        <p class="mt-2 text-heritage-muted">Published lessons will appear here.</p>
                    </div>
                </section>

                <section class="mt-12">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-3xl font-black text-heritage-ink">Featured lessons</h2>
                            <p class="mt-2 text-heritage-muted">Short reads designed to prepare you for a related quiz.</p>
                        </div>
                        <AppBadge variant="navy">{{ lessons.length }} lessons</AppBadge>
                    </div>

                    <div v-if="featuredLessons.length" class="mt-6 grid gap-5 lg:grid-cols-3">
                        <article v-for="lesson in featuredLessons" :key="lesson.slug" :class="['soft-card overflow-hidden', lesson.isLocked ? 'border-heritage-gold/50' : '']">
                            <div :class="['flex h-32 items-end p-5', lesson.isLocked ? 'bg-heritage-gold-faint' : 'bg-heritage-panel']">
                                <div>
                                    <p class="text-xs font-black uppercase text-heritage-red">{{ lesson.category }}</p>
                                    <p :class="['mt-1 text-3xl font-black', lesson.isLocked ? 'text-heritage-gold-deep/25' : 'text-heritage-red/15']">
                                        {{ lesson.isLocked ? 'LOCK' : lesson.title.slice(0, 2).toUpperCase() }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <AppBadge variant="gold">{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                <AppBadge v-if="lesson.isDemo" variant="gold">Demo</AppBadge>
                                <AppBadge v-if="lesson.isLocked" variant="neutral">Locked</AppBadge>
                                <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">
                                    {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                                </span>
                            </div>
                            <h3 class="mt-4 text-2xl font-black text-heritage-ink">{{ lesson.title }}</h3>
                            <p class="mt-3 leading-7 text-heritage-muted">{{ lesson.summary }}</p>
                            <p v-if="lesson.isLocked" class="mt-4 rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-bold text-heritage-muted">
                                Create a free account to unlock.
                            </p>
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <PrimaryButton v-if="lesson.isLocked" :href="lesson.registerHref" variant="soft">Unlock free</PrimaryButton>
                                <a v-if="lesson.isLocked" :href="lesson.loginHref" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-center text-xs font-black text-heritage-red hover:text-heritage-red-dark">
                                    Already have an account? Log in
                                </a>
                                <PrimaryButton v-if="!lesson.isLocked" :href="lesson.href" variant="soft">{{ lesson.actionLabel }}</PrimaryButton>
                                <PrimaryButton v-if="!lesson.isLocked && lesson.related_quiz && !lesson.relatedQuizLocked" :href="lesson.related_quiz.start_url" variant="soft">Take quiz</PrimaryButton>
                                <PrimaryButton v-else-if="!lesson.isLocked && lesson.relatedQuizLocked" :href="lesson.relatedQuizRegisterHref" variant="soft">Unlock quiz</PrimaryButton>
                            </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="mt-12 grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="rounded-[2rem] bg-heritage-red p-7 text-white shadow-card md:p-8">
                        <AppBadge variant="gold">Practice loop</AppBadge>
                        <h2 class="mt-4 text-3xl font-black">Learn, quiz, review</h2>
                        <p class="mt-3 max-w-2xl leading-7 text-white/85">
                            Read a lesson first, take the related quiz, then revisit the lesson from your results page when you want a refresher.
                        </p>
                    </div>
                    <div class="rounded-[2rem] border border-heritage-gold/40 bg-heritage-gold-faint p-7 shadow-card md:p-8">
                        <p class="label text-heritage-gold-deep">Geography feature</p>
                        <h3 class="mt-3 text-2xl font-black text-heritage-ink">Try the Macedonia Map Challenge</h3>
                        <p class="mt-3 leading-7 text-heritage-gold-deep">Guess cities, lakes, and landmarks from highlighted map clues.</p>
                        <PrimaryButton href="/map-challenge" variant="gold" class="mt-5">Open challenge</PrimaryButton>
                    </div>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
