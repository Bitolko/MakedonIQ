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

const heroChecklist = ['Learn', 'Practice', 'Save progress'];

const categoryVisuals = {
    'macedonian-language': {
        icon: 'MK',
        visual: 'language',
        badge: 'Language',
        accent: 'text-heritage-red',
        note: 'Phrases, greetings, and everyday vocabulary.',
    },
    'macedonian-alphabet': {
        icon: 'А',
        visual: 'alphabet',
        badge: 'Alphabet',
        accent: 'text-heritage-gold-deep',
        note: 'Cyrillic letters, sounds, and first words.',
    },
    geography: {
        icon: 'MAP',
        visual: 'map',
        badge: 'Geography',
        accent: 'text-heritage-navy',
        note: 'Cities, lakes, mountains, and map practice.',
    },
    'history-of-macedonia': {
        icon: 'H',
        visual: 'history',
        badge: 'History',
        accent: 'text-heritage-red',
        note: 'Places, stories, monuments, and memory.',
    },
    'culture-and-traditions': {
        icon: 'ORO',
        visual: 'culture',
        badge: 'Culture',
        accent: 'text-heritage-gold-deep',
        note: 'Family, celebrations, dance, and customs.',
    },
    'food-and-music': {
        icon: 'FM',
        visual: 'food',
        badge: 'Food + Music',
        accent: 'text-heritage-navy',
        note: 'Tastes, rhythms, songs, and celebrations.',
    },
};

const demoItems = [
    {
        title: 'Basic Macedonian Greetings',
        detail: 'Start speaking with friendly phrases used at home, school, and community events.',
        href: '/learn/macedonian-language/basic-macedonian-greetings',
        cta: 'Try demo',
        type: 'Lesson + Quiz',
        visual: 'phrases',
        practise: ['Zdravo and good morning', 'Polite words', 'Simple conversation'],
    },
    {
        title: 'Cyrillic Alphabet Basics',
        detail: 'Preview Macedonian Cyrillic letters with simple shapes, sounds, and first words.',
        href: '/learn/macedonian-alphabet/introduction-to-macedonian-cyrillic-alphabet',
        cta: 'Try demo',
        type: 'Alphabet lesson',
        visual: 'letters',
        practise: ['Letter recognition', 'А, Б, В sounds', 'First words'],
    },
    {
        title: 'Macedonia Map Challenge',
        detail: 'Practise geography by finding cities, lakes, mountains, and landmarks on a map.',
        href: '/map-challenge',
        cta: 'Try demo',
        type: 'Map challenge',
        visual: 'map',
        practise: ['Cities and regions', 'Lakes and mountains', 'Landmark clues'],
    },
];

const howItWorks = [
    {
        step: '01',
        title: 'Learn',
        detail: 'Read short bilingual lessons.',
        visual: 'LESSON',
    },
    {
        step: '02',
        title: 'Practise',
        detail: 'Take quizzes and map challenges.',
        visual: 'QUIZ',
    },
    {
        step: '03',
        title: 'Track',
        detail: 'Create a free account to save scores and progress.',
        visual: 'SAVED',
    },
];

const accountBenefits = [
    'Unlock all lessons',
    'Unlock all quizzes',
    'Save quiz scores',
    'Track progress',
    'Continue learning anytime',
];

const categoryCards = computed(() => categories.value.map((category) => {
    const visual = categoryVisuals[category.slug] || {
        icon: category.icon || initials(category.name_en || category.name_mk || 'IQ'),
        visual: 'default',
        badge: localizedText(category, 'name', language) || 'Learning path',
        accent: 'text-heritage-red',
        note: localizedText(category, 'description', language),
    };
    const lessonCount = Number(category.lessons_count || 0);

    return {
        ...category,
        title: localizedText(category, 'name', language),
        description: localizedText(category, 'description', language),
        href: learnCategoryUrl(category.slug),
        visual,
        lessonLabel: lessonCount > 0 ? `${lessonCount} lesson${lessonCount === 1 ? '' : 's'}` : 'Lessons coming soon',
        accessLabel: isGuest.value ? 'Demo previews open' : 'Full path open',
    };
}));

const featuredLessons = computed(() => lessons.value.slice(0, 6).map((lesson) => {
    const title = localizedText(lesson, 'title', language);

    return {
        ...lesson,
        title,
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
        visualLabel: Boolean(lesson.is_locked) ? 'LOCK' : initials(title),
        visual: categoryVisuals[lesson.category_slug]?.visual || 'default',
    };
}));

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

function initials(value) {
    return (value || 'IQ')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase();
}
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-8 md:py-12">
            <section class="relative overflow-hidden rounded-[2rem] border border-heritage-line/50 bg-white p-5 shadow-soft md:p-8 lg:p-10">
                <div class="pointer-events-none absolute inset-0 opacity-70" style="background-image: linear-gradient(135deg, rgba(244,196,48,0.16) 0 1px, transparent 1px), radial-gradient(circle at 2px 2px, rgba(164,0,0,0.11) 1px, transparent 0); background-size: 42px 42px, 28px 28px;" />
                <div class="relative grid gap-8 lg:grid-cols-[1fr_0.92fr] lg:items-center">
                    <div class="max-w-3xl">
                        <AppBadge variant="red">LEARN</AppBadge>
                        <h1 class="mt-5 text-4xl font-black leading-tight text-heritage-ink md:text-6xl">Learn Macedonian step by step</h1>
                        <p class="mt-5 max-w-2xl text-lg leading-8 text-heritage-navy/80">
                            Start with short bilingual lessons, then practise with quizzes and map challenges.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton :href="isGuest ? '#free-demos' : '#learn-categories'" size="lg">Start learning</PrimaryButton>
                            <PrimaryButton href="/quizzes" variant="gold" size="lg">Take a quiz</PrimaryButton>
                            <PrimaryButton href="/map-challenge" variant="white" size="lg">Map Challenge</PrimaryButton>
                        </div>
                    </div>

                    <aside class="relative overflow-hidden rounded-[1.75rem] border border-heritage-gold/40 bg-heritage-panel p-4 shadow-card md:p-5">
                        <div class="absolute inset-x-0 top-0 h-2 bg-linear-to-r from-heritage-red via-heritage-gold to-heritage-navy" />
                        <div class="grid gap-4 rounded-[1.4rem] border border-white/80 bg-white p-4 shadow-card sm:p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="rounded-full bg-heritage-red-faint px-3 py-1 text-xs font-black uppercase text-heritage-red">Daily lesson</span>
                                <span class="rounded-full bg-heritage-gold-faint px-3 py-1 text-xs font-black uppercase text-heritage-gold-deep">8 min</span>
                            </div>

                            <div class="rounded-[1.25rem] border border-heritage-line/45 bg-heritage-red p-5 text-white shadow-[0_5px_0_0_#760000]">
                                <p class="text-sm font-black uppercase text-heritage-gold">Phrase card</p>
                                <p class="mt-3 text-3xl font-black leading-tight">Добро утро</p>
                                <p class="mt-1 text-lg font-bold text-white/85">Good morning</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-[0.9fr_1.1fr]">
                                <div class="rounded-[1.25rem] border border-heritage-line/45 bg-heritage-gold-faint p-4">
                                    <p class="text-xs font-black uppercase text-heritage-gold-deep">Alphabet</p>
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <span class="flex aspect-square items-center justify-center rounded-2xl bg-white text-2xl font-black text-heritage-red shadow-card">А</span>
                                        <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-gold text-2xl font-black text-heritage-navy shadow-card">Б</span>
                                        <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-navy text-2xl font-black text-white shadow-card">В</span>
                                    </div>
                                </div>

                                <div class="relative min-h-36 overflow-hidden rounded-[1.25rem] border border-heritage-line/45 bg-heritage-navy p-4 text-white">
                                    <p class="text-xs font-black uppercase text-heritage-gold">Map mini card</p>
                                    <div class="absolute bottom-4 left-4 right-4 top-12 rounded-[1rem] border border-white/20 bg-white/10">
                                        <div class="absolute left-5 right-5 top-1/2 h-1 rounded-full bg-heritage-gold/80" />
                                        <div class="absolute bottom-4 left-10 top-4 w-1 rounded-full bg-heritage-red-soft/80" />
                                        <div class="absolute right-7 top-5 flex h-12 w-12 items-center justify-center rounded-full bg-heritage-red text-[0.65rem] font-black text-white shadow-[0_4px_0_0_#760000] ring-4 ring-heritage-gold/25">
                                            PIN
                                        </div>
                                        <div class="absolute bottom-5 left-6 rounded-full bg-white px-3 py-1 text-xs font-black text-heritage-navy">MK</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-3">
                                <div v-for="item in heroChecklist" :key="item" class="rounded-2xl border border-heritage-line/45 bg-heritage-panel px-4 py-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-heritage-red text-[0.65rem] font-black text-white">OK</span>
                                    <p class="mt-2 text-sm font-black text-heritage-ink">{{ item }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section v-if="isGuest" id="free-demos" class="mt-10 scroll-mt-24">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <AppBadge variant="gold">Free preview</AppBadge>
                        <h2 class="mt-3 text-3xl font-black text-heritage-ink md:text-4xl">Start with a free preview</h2>
                        <p class="mt-2 max-w-2xl text-heritage-muted">Try a lesson, an alphabet preview, and a geography challenge before you create an account.</p>
                    </div>
                    <PrimaryButton href="/register" variant="soft">Create free account</PrimaryButton>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-3">
                    <article
                        v-for="item in demoItems"
                        :key="item.title"
                        class="soft-card group flex min-h-[31rem] flex-col overflow-hidden p-4 transition hover:-translate-y-1 hover:border-heritage-red/25 hover:shadow-soft"
                    >
                        <div class="relative min-h-44 overflow-hidden rounded-[1.35rem] border border-heritage-line/45 bg-linear-to-br from-white via-heritage-panel to-heritage-gold-faint p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-full bg-heritage-red px-3 py-1 text-[0.68rem] font-black uppercase text-white shadow-[0_3px_0_0_#760000]">Demo</span>
                                <span class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black uppercase text-heritage-navy shadow-card">{{ item.type }}</span>
                            </div>

                            <div v-if="item.visual === 'phrases'" class="mt-5 grid gap-2">
                                <div class="max-w-[11rem] rounded-2xl bg-white px-4 py-3 text-sm font-black text-heritage-red shadow-card">Здраво</div>
                                <div class="max-w-[12rem] justify-self-end rounded-2xl bg-heritage-red px-4 py-3 text-sm font-black text-white shadow-[0_4px_0_0_#760000]">Добро утро</div>
                                <div class="max-w-[10rem] rounded-2xl bg-heritage-gold px-4 py-3 text-sm font-black text-heritage-navy shadow-card">Thank you</div>
                            </div>

                            <div v-else-if="item.visual === 'letters'" class="mt-5 grid grid-cols-3 gap-3">
                                <span class="flex aspect-square items-center justify-center rounded-2xl bg-white text-3xl font-black text-heritage-red shadow-card">А</span>
                                <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-gold text-3xl font-black text-heritage-navy shadow-card">Б</span>
                                <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-navy text-3xl font-black text-white shadow-card">В</span>
                            </div>

                            <div v-else class="relative mt-4 min-h-32 rounded-[1.25rem] bg-heritage-navy p-4 shadow-card">
                                <div class="absolute left-6 right-6 top-1/2 h-1 rounded-full bg-heritage-gold/80" />
                                <div class="absolute bottom-6 left-10 top-6 w-1 rounded-full bg-heritage-red-soft/80" />
                                <div class="absolute right-9 top-7 flex h-12 w-12 items-center justify-center rounded-full bg-heritage-red text-[0.65rem] font-black text-white shadow-[0_4px_0_0_#760000] ring-4 ring-heritage-gold/25">
                                    PIN
                                </div>
                                <div class="absolute bottom-7 left-12 rounded-full bg-white px-3 py-1 text-xs font-black text-heritage-navy">MK</div>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col px-2 pt-5">
                            <h3 class="text-2xl font-black leading-tight text-heritage-ink">{{ item.title }}</h3>
                            <p class="mt-3 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                            <div class="mt-5 rounded-[1.25rem] border border-heritage-line/45 bg-heritage-panel p-4">
                                <p class="text-[0.68rem] font-black uppercase text-heritage-red">What you'll practise</p>
                                <ul class="mt-3 grid gap-2 text-sm font-bold leading-6 text-heritage-muted">
                                    <li v-for="practice in item.practise" :key="practice" class="flex gap-2">
                                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-heritage-gold" />
                                        <span>{{ practice }}</span>
                                    </li>
                                </ul>
                            </div>
                            <a :href="item.href" class="pressable-red mt-auto inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black">
                                {{ item.cta }} <span aria-hidden="true">></span>
                            </a>
                        </div>
                    </article>
                </div>
            </section>

            <section v-if="isLoading" class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="index in 6" :key="index" class="soft-card min-h-60 animate-pulse p-6">
                    <div class="h-10 w-24 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-7 w-3/4 rounded-full bg-heritage-panel" />
                    <div class="mt-4 h-4 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-3 h-4 w-2/3 rounded-full bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="section-panel mt-12 text-center">
                <AppBadge variant="red">Lessons unavailable</AppBadge>
                <h2 class="mt-4 text-2xl font-black text-heritage-ink">We could not load Learn content</h2>
                <p class="mt-2 text-heritage-muted">{{ error }}</p>
            </section>

            <template v-else>
                <section id="learn-categories" class="mt-12 scroll-mt-24">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="red">Learning paths</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink md:text-4xl">Choose your learning path</h2>
                            <p class="mt-2 max-w-2xl text-heritage-muted">Pick a topic, learn the essentials, then practise with quizzes and map challenges.</p>
                        </div>
                        <AppBadge variant="navy">{{ lessons.length }} lessons</AppBadge>
                    </div>

                    <div v-if="categoryCards.length" class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <a
                            v-for="category in categoryCards"
                            :key="category.slug"
                            :href="category.href"
                            class="soft-card group flex min-h-[22rem] flex-col justify-between overflow-hidden p-5 transition hover:-translate-y-1 hover:border-heritage-red/25 hover:shadow-soft"
                        >
                            <div class="relative min-h-36 overflow-hidden rounded-[1.25rem] border border-heritage-line/45 bg-linear-to-br from-white via-heritage-panel to-heritage-gold-faint p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-full bg-white px-3 py-1 text-[0.68rem] font-black uppercase text-heritage-muted shadow-card">{{ category.visual.badge }}</span>
                                    <span class="rounded-full bg-heritage-gold-faint px-3 py-1 text-[0.68rem] font-black uppercase text-heritage-gold-deep">{{ category.lessonLabel }}</span>
                                </div>

                                <div v-if="category.visual.visual === 'language'" class="mt-5 grid gap-2">
                                    <span class="max-w-[10rem] rounded-2xl bg-white px-4 py-3 text-sm font-black text-heritage-red shadow-card">Здраво</span>
                                    <span class="max-w-[11rem] justify-self-end rounded-2xl bg-heritage-red px-4 py-3 text-sm font-black text-white shadow-[0_4px_0_0_#760000]">Good morning</span>
                                </div>

                                <div v-else-if="category.visual.visual === 'alphabet'" class="mt-5 grid grid-cols-4 gap-2">
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-white text-2xl font-black text-heritage-red shadow-card">А</span>
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-gold text-2xl font-black text-heritage-navy shadow-card">Б</span>
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-navy text-2xl font-black text-white shadow-card">В</span>
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-white text-2xl font-black text-heritage-red shadow-card">Г</span>
                                </div>

                                <div v-else-if="category.visual.visual === 'map'" class="relative mt-4 min-h-28 rounded-[1rem] bg-heritage-navy p-4">
                                    <div class="absolute left-5 right-5 top-1/2 h-1 rounded-full bg-heritage-gold/80" />
                                    <div class="absolute bottom-5 left-9 top-5 w-1 rounded-full bg-heritage-red-soft/80" />
                                    <div class="absolute right-8 top-6 flex h-11 w-11 items-center justify-center rounded-full bg-heritage-red text-[0.65rem] font-black text-white shadow-[0_4px_0_0_#760000] ring-4 ring-heritage-gold/25">PIN</div>
                                    <div class="absolute bottom-6 left-10 rounded-full bg-white px-3 py-1 text-xs font-black text-heritage-navy">MK</div>
                                </div>

                                <div v-else-if="category.visual.visual === 'history'" class="mt-5 grid gap-2">
                                    <div class="h-2 rounded-full bg-heritage-red/80" />
                                    <div class="ml-6 h-2 rounded-full bg-heritage-gold/80" />
                                    <div class="ml-12 h-2 rounded-full bg-heritage-navy/80" />
                                    <div class="mt-3 rounded-2xl bg-white px-4 py-3 text-sm font-black text-heritage-ink shadow-card">Timeline</div>
                                </div>

                                <div v-else-if="category.visual.visual === 'culture'" class="mt-5 grid grid-cols-3 gap-2">
                                    <span class="flex aspect-square items-center justify-center rounded-full bg-white text-sm font-black text-heritage-red shadow-card">ORO</span>
                                    <span class="flex aspect-square items-center justify-center rounded-full bg-heritage-gold text-sm font-black text-heritage-navy shadow-card">HOME</span>
                                    <span class="flex aspect-square items-center justify-center rounded-full bg-heritage-navy text-sm font-black text-white shadow-card">FAM</span>
                                </div>

                                <div v-else-if="category.visual.visual === 'food'" class="mt-5 grid grid-cols-[1fr_auto] items-center gap-3">
                                    <div class="rounded-full border-8 border-heritage-gold bg-white p-7 shadow-card" />
                                    <div class="grid gap-2">
                                        <span class="rounded-full bg-heritage-red px-3 py-1 text-xs font-black text-white">MUSIC</span>
                                        <span class="rounded-full bg-heritage-navy px-3 py-1 text-xs font-black text-white">FOOD</span>
                                    </div>
                                </div>

                                <div v-else class="mt-5 flex h-20 items-center justify-center rounded-[1rem] bg-white text-3xl font-black shadow-card" :class="category.visual.accent">
                                    {{ category.visual.icon }}
                                </div>
                            </div>

                            <div class="mt-5 flex flex-1 flex-col">
                                <div class="flex flex-wrap items-center gap-2">
                                    <AppBadge variant="neutral">{{ category.accessLabel }}</AppBadge>
                                </div>
                                <h3 class="mt-4 text-2xl font-black text-heritage-ink group-hover:text-heritage-red">{{ category.title }}</h3>
                                <p class="mt-3 flex-1 leading-7 text-heritage-muted">{{ category.description || category.visual.note }}</p>
                                <span class="button-soft mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black group-hover:bg-heritage-red group-hover:text-white">
                                    Explore <span aria-hidden="true">></span>
                                </span>
                            </div>
                        </a>
                    </div>

                    <div v-else class="section-panel mt-6 text-center">
                        <h3 class="text-2xl font-black text-heritage-ink">No lessons yet</h3>
                        <p class="mt-2 text-heritage-muted">Published lessons will appear here.</p>
                    </div>
                </section>

                <section class="mt-12">
                    <div class="grid gap-5 lg:grid-cols-3">
                        <article v-for="item in howItWorks" :key="item.step" class="soft-card overflow-hidden p-5">
                            <div class="flex min-h-24 items-center justify-between gap-4 rounded-[1.25rem] bg-heritage-panel p-4">
                                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-sm font-black text-heritage-red shadow-card">{{ item.step }}</span>
                                <span class="rounded-full bg-heritage-gold-faint px-4 py-2 text-xs font-black text-heritage-gold-deep">{{ item.visual }}</span>
                            </div>
                            <h2 class="mt-5 text-2xl font-black text-heritage-ink">{{ item.title }}</h2>
                            <p class="mt-2 leading-7 text-heritage-muted">{{ item.detail }}</p>
                        </article>
                    </div>
                </section>

                <section v-if="isGuest" class="heritage-pattern mt-12 overflow-hidden rounded-[2rem] p-6 text-white shadow-soft md:p-8">
                    <div class="grid gap-8 lg:grid-cols-[1fr_0.9fr] lg:items-center">
                        <div>
                            <AppBadge variant="gold">Free account</AppBadge>
                            <h2 class="mt-4 max-w-3xl text-3xl font-black leading-tight md:text-5xl">Create a free account to unlock the full path</h2>
                            <p class="mt-4 max-w-2xl text-base font-semibold leading-7 text-white/82 md:text-lg">
                                Keep every lesson, quiz, and map challenge connected to your own learning journey.
                            </p>
                            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                <PrimaryButton href="/register" variant="gold">Create free account</PrimaryButton>
                                <PrimaryButton href="/login" variant="white">Log in</PrimaryButton>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div v-for="benefit in accountBenefits" :key="benefit" class="rounded-2xl border border-white/20 bg-white/95 px-4 py-3 text-sm font-black text-heritage-ink shadow-card">
                                {{ benefit }}
                            </div>
                        </div>
                    </div>
                </section>

                <section v-else class="mt-12 rounded-[2rem] border border-heritage-gold/40 bg-white p-6 shadow-card md:p-8">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="gold">Continue learning</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink md:text-4xl">Continue your learning journey</h2>
                            <p class="mt-2 max-w-2xl text-heritage-muted">Browse lessons, practise by topic, and review saved results when you are ready.</p>
                        </div>
                    </div>
                    <div class="mt-6 grid gap-3 md:grid-cols-3">
                        <PrimaryButton href="/dashboard" class="w-full" variant="soft">Dashboard</PrimaryButton>
                        <PrimaryButton href="/progress" class="w-full" variant="soft">Progress</PrimaryButton>
                        <PrimaryButton href="/quizzes" class="w-full" variant="soft">Browse quizzes</PrimaryButton>
                    </div>
                </section>

                <section class="mt-12">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="navy">Lesson shelf</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink md:text-4xl">Featured lessons</h2>
                            <p class="mt-2 max-w-2xl text-heritage-muted">Short reads designed to prepare you for a related quiz.</p>
                        </div>
                    </div>

                    <div v-if="featuredLessons.length" class="mt-6 grid gap-5 lg:grid-cols-3">
                        <article v-for="lesson in featuredLessons" :key="lesson.slug" :class="['soft-card flex min-h-[31rem] flex-col overflow-hidden', lesson.isLocked ? 'border-heritage-gold/50' : '']">
                            <div :class="['relative min-h-36 overflow-hidden p-5', lesson.isLocked ? 'bg-heritage-gold-faint' : 'bg-heritage-panel']">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-black uppercase text-heritage-red shadow-card">{{ lesson.category }}</span>
                                    <AppBadge v-if="lesson.isDemo" variant="gold">Demo</AppBadge>
                                    <AppBadge v-if="lesson.isLocked" variant="neutral">Locked</AppBadge>
                                </div>
                                <div class="absolute bottom-5 right-5 flex h-20 w-20 items-center justify-center rounded-[1.5rem] bg-white text-lg font-black shadow-card" :class="lesson.isLocked ? 'text-heritage-gold-deep' : 'text-heritage-red'">
                                    {{ lesson.visualLabel }}
                                </div>
                                <div v-if="lesson.visual === 'map'" class="absolute bottom-5 left-5 h-16 w-32 rounded-[1rem] bg-heritage-navy">
                                    <div class="absolute left-4 right-4 top-1/2 h-1 rounded-full bg-heritage-gold/80" />
                                    <div class="absolute right-5 top-4 h-7 w-7 rounded-full bg-heritage-red ring-4 ring-heritage-gold/25" />
                                </div>
                                <div v-else class="absolute bottom-6 left-5 flex gap-2">
                                    <span class="h-8 w-8 rounded-2xl bg-heritage-red/15" />
                                    <span class="h-8 w-8 rounded-2xl bg-heritage-gold/45" />
                                    <span class="h-8 w-8 rounded-2xl bg-heritage-navy/15" />
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col p-6">
                                <div class="flex flex-wrap items-center gap-2">
                                    <AppBadge variant="gold">{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                    <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">
                                        {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                                    </span>
                                </div>
                                <h3 class="mt-4 text-2xl font-black leading-tight text-heritage-ink">{{ lesson.title }}</h3>
                                <p class="mt-3 flex-1 leading-7 text-heritage-muted">{{ lesson.summary }}</p>
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
            </template>
        </main>
    </PublicLayout>
</template>
