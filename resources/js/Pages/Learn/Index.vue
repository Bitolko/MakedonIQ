<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import DemoPreviewSection from '../../Components/DemoPreviewSection.vue';
import FeaturedLessonCard from '../../Components/FeaturedLessonCard.vue';
import LearnHero from '../../Components/LearnHero.vue';
import {
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
const user = computed(() => currentUser());
const isGuest = computed(() => !user.value);

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
    'folklore-songs': {
        icon: 'FS',
        accent: 'text-heritage-red',
        note: 'Song titles, vocabulary, rhythm, and cultural memory.',
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
    isDemo: isGuest.value && Boolean(lesson.is_demo),
    isLocked: Boolean(lesson.is_locked),
    relatedQuizLocked: Boolean(lesson.related_quiz?.is_locked),
    actionLabel: user.value ? 'Start lesson' : (lesson.is_demo ? 'Try demo' : 'Start lesson'),
    registerHref: authHref('/register', lessonUrl(lesson.category_slug, lesson.slug)),
    loginHref: authHref('/login', lessonUrl(lesson.category_slug, lesson.slug)),
    relatedQuizRegisterHref: authHref('/register', lesson.related_quiz?.start_url || lessonUrl(lesson.category_slug, lesson.slug)),
})));

const demoItems = [
    {
        title: 'Basic Macedonian Greetings',
        description: 'Start with friendly everyday phrases.',
        practice: 'Practise greetings, meanings, and first phrases.',
        href: '/learn/macedonian-language/basic-macedonian-greetings',
        cta: 'Try demo',
        type: 'Lesson + Quiz',
        badge: 'Demo',
        visual: 'greetings',
        image: '/images/demo/demo-greetings.png',
        imageAlt: 'Greetings visual',
    },
    {
        title: 'Cyrillic Alphabet Basics',
        description: 'Learn your first Macedonian letters.',
        practice: 'Preview letters, sounds, and first words.',
        href: '/learn/macedonian-alphabet/introduction-to-macedonian-cyrillic-alphabet',
        cta: 'Try demo',
        type: 'Alphabet',
        badge: 'Demo',
        visual: 'alphabet',
        image: '/images/demo/demo-alphabet.png',
        imageAlt: 'Cyrillic alphabet visual',
    },
    {
        title: 'Macedonia Map Challenge',
        description: 'Guess cities, lakes, and landmarks.',
        practice: 'Explore geography through play.',
        href: '/map-challenge',
        cta: 'Open demo',
        type: 'Map Challenge',
        badge: 'Demo',
        visual: 'map',
        image: '/images/demo/demo-map.png',
        imageAlt: 'Macedonia map challenge visual',
    },
    {
        title: 'Folklore Sound Quiz',
        description: 'Guess songs from audio clues.',
        practice: 'Preview listening with folklore song clues.',
        href: '/quizzes/folklore-songs/guess-the-macedonian-folk-song/start',
        cta: 'Preview',
        type: 'Sound Quiz',
        badge: 'Locked preview',
        isLocked: true,
        visual: 'sound',
        image: '/images/demo/demo-sound.png',
        imageAlt: 'Sound quiz visual',
    },
];

const continueItems = [
    {
        title: 'Browse all lessons',
        detail: 'Return to the full Learn path and choose your next topic.',
        href: '/learn',
        accent: 'bg-heritage-red text-white',
    },
    {
        title: 'Take a quiz',
        detail: 'Practise what you remember with category quizzes.',
        href: '/quizzes',
        accent: 'bg-heritage-gold text-heritage-navy',
    },
    {
        title: 'View progress',
        detail: 'Review saved attempts, scores, and learning history.',
        href: '/progress',
        accent: 'bg-heritage-navy text-white',
    },
    {
        title: 'Try Map Challenge',
        detail: 'Guess Macedonian cities, lakes, and landmarks from map clues.',
        href: '/map-challenge',
        accent: 'bg-heritage-red text-white',
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
            <LearnHero :is-guest="isGuest" />

                <DemoPreviewSection v-if="isGuest" :items="demoItems" class-name="mt-10" />

                <section v-else class="mt-10 rounded-[2rem] border border-heritage-gold/40 bg-white p-6 shadow-card md:p-8">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <AppBadge variant="gold">CONTINUE</AppBadge>
                            <h2 class="mt-3 text-3xl font-black text-heritage-ink">Continue your learning journey</h2>
                            <p class="mt-2 max-w-2xl text-heritage-muted">Pick up a lesson, practise with quizzes, or review your progress.</p>
                        </div>
                        <PrimaryButton href="/dashboard" variant="soft">Open dashboard</PrimaryButton>
                    </div>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <a
                            v-for="item in continueItems"
                            :key="item.title"
                            :href="item.href"
                            class="group rounded-[1.5rem] border border-heritage-line bg-heritage-panel p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/30 hover:bg-white hover:shadow-soft"
                        >
                            <span :class="['flex h-11 w-11 items-center justify-center rounded-2xl text-xs font-black shadow-card', item.accent]">GO</span>
                            <h3 class="mt-4 text-xl font-black text-heritage-ink group-hover:text-heritage-red">{{ item.title }}</h3>
                            <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                        </a>
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
                            <p class="mt-2 max-w-2xl text-heritage-muted">Start with short bilingual lessons, then practise with a related quiz.</p>
                        </div>
                        <span class="inline-flex w-fit rounded-full border border-heritage-line/60 bg-white px-3 py-1 text-xs font-black uppercase text-heritage-muted shadow-card">
                            {{ lessons.length }} lessons
                        </span>
                    </div>

                    <div v-if="featuredLessons.length" class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <FeaturedLessonCard v-for="lesson in featuredLessons" :key="lesson.slug" :lesson="lesson" />
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
