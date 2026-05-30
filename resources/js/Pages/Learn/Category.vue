<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
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
                <section class="heritage-pattern rounded-[2rem] p-8 text-white md:p-10">
                    <div class="max-w-3xl">
                        <AppBadge variant="gold">Learn category</AppBadge>
                        <h1 class="mt-5 text-4xl font-black md:text-5xl">{{ categoryName }}</h1>
                        <p class="mt-4 text-lg leading-8 text-white/85">{{ categoryDescription }}</p>
                    </div>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <PrimaryButton :href="learnUrl()" variant="white">All lessons</PrimaryButton>
                        <PrimaryButton :href="`/quizzes/${category.slug}`" variant="gold">Category quizzes</PrimaryButton>
                    </div>
                </section>

                <section class="mt-10">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                        <div>
                            <h2 class="text-3xl font-black text-heritage-ink">Lessons</h2>
                            <p class="mt-2 text-heritage-muted">Read first, then take a related quiz when you are ready.</p>
                        </div>
                        <AppBadge variant="navy">{{ lessons.length }} lessons</AppBadge>
                    </div>

                    <div v-if="lessonCards.length" class="mt-6 grid gap-5 lg:grid-cols-2">
                        <article v-for="lesson in lessonCards" :key="lesson.slug" class="soft-card p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <AppBadge>{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                                <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">
                                    {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                                </span>
                            </div>
                            <h3 class="mt-4 text-2xl font-black text-heritage-ink">{{ lesson.title }}</h3>
                            <p class="mt-3 leading-7 text-heritage-muted">{{ lesson.summary }}</p>
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <PrimaryButton :href="lesson.href">Start lesson</PrimaryButton>
                                <PrimaryButton v-if="lesson.related_quiz" :href="lesson.related_quiz.start_url" variant="soft">Related quiz</PrimaryButton>
                            </div>
                        </article>
                    </div>

                    <section v-else class="section-panel mt-6 text-center">
                        <h2 class="text-2xl font-black text-heritage-ink">No lessons yet</h2>
                        <p class="mt-3 text-heritage-muted">Published lessons for this category will appear here.</p>
                    </section>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
