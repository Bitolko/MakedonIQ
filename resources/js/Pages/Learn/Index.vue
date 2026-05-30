<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    difficultyLabel,
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

const categoryCards = computed(() => categories.value.map((category) => ({
    ...category,
    title: localizedText(category, 'name', language),
    description: localizedText(category, 'description', language),
    href: learnCategoryUrl(category.slug),
})));

const featuredLessons = computed(() => lessons.value.slice(0, 6).map((lesson) => ({
    ...lesson,
    title: localizedText(lesson, 'title', language),
    summary: localizedText(lesson, 'summary', language),
    category: language === 'mk' && lesson.category_name_mk ? lesson.category_name_mk : lesson.category_name_en,
    href: lessonUrl(lesson.category_slug, lesson.slug),
})));

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
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-10 md:py-14">
            <section class="heritage-pattern overflow-hidden rounded-[2rem] p-8 text-white md:p-10">
                <div class="max-w-3xl">
                    <AppBadge variant="gold">Learn</AppBadge>
                    <h1 class="mt-5 text-4xl font-black leading-tight md:text-5xl">Learn Macedonian before you quiz</h1>
                    <p class="mt-4 text-lg leading-8 text-white/85">
                        Short bilingual lessons about language, alphabet, history, geography, culture, food, and music.
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <PrimaryButton href="/quizzes" variant="white">Explore quizzes</PrimaryButton>
                        <PrimaryButton href="/register" variant="gold">Start learning</PrimaryButton>
                    </div>
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
                <section class="mt-10">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-3xl font-black text-heritage-ink">Learn by theme</h2>
                            <p class="mt-2 text-heritage-muted">Choose a topic, read a short lesson, then take the matching quiz.</p>
                        </div>
                        <AppBadge variant="navy">{{ lessons.length }} lessons</AppBadge>
                    </div>

                    <div v-if="categoryCards.length" class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <a
                            v-for="category in categoryCards"
                            :key="category.slug"
                            :href="category.href"
                            class="soft-card group p-6 transition hover:-translate-y-1 hover:shadow-soft"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-heritage-red text-sm font-black text-white">
                                    {{ category.icon || category.title.slice(0, 2).toUpperCase() }}
                                </span>
                                <AppBadge variant="gold">{{ category.lessons_count }} lessons</AppBadge>
                            </div>
                            <h3 class="mt-5 text-2xl font-black text-heritage-ink group-hover:text-heritage-red">{{ category.title }}</h3>
                            <p class="mt-3 leading-7 text-heritage-muted">{{ category.description }}</p>
                        </a>
                    </div>

                    <div v-else class="section-panel mt-6 text-center">
                        <h3 class="text-2xl font-black text-heritage-ink">No lessons yet</h3>
                        <p class="mt-2 text-heritage-muted">Published lessons will appear here.</p>
                    </div>
                </section>

                <section class="mt-12">
                    <div>
                        <h2 class="text-3xl font-black text-heritage-ink">Featured lessons</h2>
                        <p class="mt-2 text-heritage-muted">Short reads designed to prepare you for a related quiz.</p>
                    </div>

                    <div v-if="featuredLessons.length" class="mt-6 grid gap-5 lg:grid-cols-2">
                        <article v-for="lesson in featuredLessons" :key="lesson.slug" class="soft-card p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <AppBadge>{{ lesson.category }}</AppBadge>
                                <AppBadge variant="gold">{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">
                                    {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                                </span>
                            </div>
                            <h3 class="mt-4 text-2xl font-black text-heritage-ink">{{ lesson.title }}</h3>
                            <p class="mt-3 leading-7 text-heritage-muted">{{ lesson.summary }}</p>
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <PrimaryButton :href="lesson.href">Start lesson</PrimaryButton>
                                <PrimaryButton v-if="lesson.related_quiz" :href="lesson.related_quiz.start_url" variant="soft">Take quiz</PrimaryButton>
                            </div>
                        </article>
                    </div>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
