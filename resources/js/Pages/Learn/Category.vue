<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import MacedoniaMap from '../../Components/MacedoniaMap.vue';
import {
    currentCategorySlug,
    difficultyLabel,
    getCategoryLessons,
    learnUrl,
    lessonUrl,
    localizedText,
    preferredLanguage,
} from '../../api/makedoniq';

const category = ref(null);
const lessons = ref([]);
const isLoading = ref(true);
const error = ref('');
const language = preferredLanguage();
const categorySlug = currentCategorySlug();

const categoryName = computed(() => localizedText(category.value, 'name', language));
const categoryDescription = computed(() => localizedText(category.value, 'description', language));
const isGeography = computed(() => category.value?.slug === 'geography');

const categoryModule = computed(() => {
    const slug = category.value?.slug || categorySlug;

    const modules = {
        'macedonian-language': {
            badge: 'Language module',
            title: categoryName.value || 'Macedonian Language',
            subtitle: 'Build everyday phrases, greetings, and vocabulary before you practise.',
            icon: 'MK',
            sideTitle: 'Phrase practice',
            facts: ['Greetings and polite words', 'Family-friendly vocabulary', 'Translation direction practice'],
        },
        'macedonian-alphabet': {
            badge: 'Foundation series',
            title: categoryName.value || 'Macedonian Alphabet',
            subtitle: 'Learn the Cyrillic letters, sounds, and word patterns used in Macedonian.',
            icon: 'А',
            sideTitle: 'Letter path',
            facts: ['Cyrillic recognition', 'Example words', 'Alphabet quiz prep'],
        },
        geography: {
            badge: 'Geography module',
            title: 'Macedonian Geography',
            subtitle: 'Explore lakes, mountains, cities, and homeland landmarks through a visual learning path.',
            icon: 'MAP',
            sideTitle: 'What you will learn',
            facts: ['Identify cities and regions', 'Recognise lakes and mountains', 'Practise with the Map Challenge'],
        },
        'history-of-macedonia': {
            badge: 'History module',
            title: categoryName.value || 'History of Macedonia',
            subtitle: 'Learn through places, stories, museums, monuments, and cultural memory.',
            icon: 'H',
            sideTitle: 'Learning focus',
            facts: ['Beginner-friendly context', 'Important places', 'Respectful historical framing'],
        },
        'culture-and-traditions': {
            badge: 'Culture module',
            title: categoryName.value || 'Culture and Traditions',
            subtitle: 'Explore family celebrations, oro, holidays, and traditions that connect generations.',
            icon: 'ORO',
            sideTitle: 'Learning focus',
            facts: ['Family celebrations', 'Dance and customs', 'Community identity'],
        },
        'food-and-music': {
            badge: 'Food and music module',
            title: categoryName.value || 'Food and Music',
            subtitle: 'Learn about Macedonian dishes, songs, rhythms, and celebration traditions.',
            icon: 'FM',
            sideTitle: 'Learning focus',
            facts: ['Traditional dishes', 'Folk music', 'Celebration vocabulary'],
        },
    };

    return modules[slug] || {
        badge: 'Learn category',
        title: categoryName.value,
        subtitle: categoryDescription.value,
        icon: category.value?.icon || 'IQ',
        sideTitle: 'Learning focus',
        facts: ['Read short lessons', 'Practise with quizzes', 'Review after results'],
    };
});

const lessonCards = computed(() => lessons.value.map((lesson) => ({
    ...lesson,
    title: localizedText(lesson, 'title', language),
    summary: localizedText(lesson, 'summary', language),
    href: lessonUrl(lesson.category_slug, lesson.slug),
})));

onMounted(async () => {
    try {
        const response = await getCategoryLessons(categorySlug);
        category.value = response.data.category;
        lessons.value = response.data.lessons || [];
    } catch (caughtError) {
        error.value = caughtError.status === 404
            ? 'This Learn category could not be found.'
            : caughtError.message || 'Lessons could not be loaded.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-10 md:py-14">
            <section v-if="isLoading" class="section-panel min-h-72 animate-pulse">
                <div class="h-6 w-32 rounded-full bg-heritage-panel" />
                <div class="mt-8 h-12 w-2/3 rounded-full bg-heritage-panel" />
                <div class="mt-4 h-5 w-full rounded-full bg-heritage-panel" />
            </section>

            <section v-else-if="error" class="section-panel text-center">
                <AppBadge variant="red">Learn category</AppBadge>
                <h1 class="mt-4 text-3xl font-black text-heritage-ink">Category unavailable</h1>
                <p class="mx-auto mt-3 max-w-2xl text-heritage-muted">{{ error }}</p>
                <PrimaryButton :href="learnUrl()" class="mt-6">Back to Learn</PrimaryButton>
            </section>

            <template v-else>
                <section class="grid gap-6 rounded-[2.5rem] border border-heritage-line/50 bg-white p-6 shadow-card md:p-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div class="space-y-5">
                        <AppBadge :variant="isGeography ? 'red' : 'gold'">{{ categoryModule.badge }}</AppBadge>
                        <h1 class="max-w-2xl text-4xl font-black leading-tight text-heritage-ink md:text-6xl">{{ categoryModule.title }}</h1>
                        <p class="max-w-2xl text-lg leading-8 text-heritage-muted">{{ categoryModule.subtitle || categoryDescription }}</p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton :href="learnUrl()" variant="soft">All lessons</PrimaryButton>
                            <PrimaryButton :href="`/quizzes/${category.slug}`" variant="gold">Category quizzes</PrimaryButton>
                        </div>
                    </div>

                    <div v-if="isGeography" class="rounded-[2rem] bg-heritage-navy p-4 shadow-soft">
                        <MacedoniaMap :x="52" :y="28" target-type="city" />
                    </div>
                    <div v-else class="relative overflow-hidden rounded-[2rem] bg-heritage-panel p-8">
                        <div class="absolute -right-8 -top-8 h-36 w-36 rounded-full bg-heritage-gold/25" />
                        <div class="relative">
                            <div class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white text-3xl font-black text-heritage-red shadow-card">
                                {{ categoryModule.icon }}
                            </div>
                            <h2 class="mt-6 text-2xl font-black text-heritage-ink">{{ categoryModule.sideTitle }}</h2>
                            <ul class="mt-4 grid gap-3 text-sm font-bold leading-6 text-heritage-muted">
                                <li v-for="fact in categoryModule.facts" :key="fact" class="rounded-2xl bg-white px-4 py-3 shadow-card">{{ fact }}</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mt-10 grid gap-6 lg:grid-cols-[1fr_20rem]">
                    <div>
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                            <div>
                                <h2 class="text-3xl font-black text-heritage-ink">Curriculum</h2>
                                <p class="mt-2 text-heritage-muted">Read first, then take a related quiz when you are ready.</p>
                            </div>
                            <AppBadge variant="navy">{{ lessons.length }} lessons</AppBadge>
                        </div>

                        <div v-if="lessonCards.length" class="mt-6 grid gap-5">
                            <article v-for="lesson in lessonCards" :key="lesson.slug" class="soft-card p-5 transition hover:-translate-y-1 hover:shadow-soft md:p-6">
                                <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <AppBadge>{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                            <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">
                                                {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                                            </span>
                                        </div>
                                        <h3 class="mt-4 text-2xl font-black text-heritage-ink">{{ lesson.title }}</h3>
                                        <p class="mt-3 leading-7 text-heritage-muted">{{ lesson.summary }}</p>
                                    </div>
                                    <div class="flex flex-col gap-3 sm:min-w-40">
                                        <PrimaryButton :href="lesson.href">Start</PrimaryButton>
                                        <PrimaryButton v-if="lesson.related_quiz" :href="lesson.related_quiz.start_url" variant="soft">Quiz</PrimaryButton>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <section v-else class="section-panel mt-6 text-center">
                            <h2 class="text-2xl font-black text-heritage-ink">No lessons yet</h2>
                            <p class="mt-3 text-heritage-muted">Published lessons for this category will appear here.</p>
                        </section>
                    </div>

                    <aside class="grid content-start gap-5">
                        <div class="soft-card p-6">
                            <h2 class="text-2xl font-black text-heritage-ink">{{ categoryModule.sideTitle }}</h2>
                            <ul class="mt-4 grid gap-3 text-sm font-bold leading-6 text-heritage-muted">
                                <li v-for="fact in categoryModule.facts" :key="`side-${fact}`" class="flex gap-3 rounded-2xl bg-heritage-panel px-4 py-3">
                                    <span class="text-heritage-gold-deep">✓</span>
                                    <span>{{ fact }}</span>
                                </li>
                            </ul>
                        </div>
                        <div v-if="category.slug === 'geography'" class="overflow-hidden rounded-[2rem] bg-heritage-navy p-6 text-white shadow-card">
                            <p class="label text-heritage-gold">Map practice</p>
                            <h2 class="mt-3 text-2xl font-black">Map Challenge</h2>
                            <p class="mt-3 leading-7 text-white/80">
                                Guess highlighted Macedonian cities, lakes, and landmarks on a polished local map.
                            </p>
                            <PrimaryButton href="/map-challenge" variant="gold" class="mt-5 w-full">Try the Map Challenge</PrimaryButton>
                        </div>
                    </aside>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
