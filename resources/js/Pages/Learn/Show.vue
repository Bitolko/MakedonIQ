<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    currentCategorySlug,
    currentLessonSlug,
    difficultyLabel,
    getLesson,
    learnCategoryUrl,
    learnUrl,
    localizedText,
    preferredLanguage,
} from '../../api/makedoniq';

const lesson = ref(null);
const isLoading = ref(true);
const error = ref('');
const language = ref(preferredLanguage() === 'mk' ? 'mk' : 'en');

const categorySlug = currentCategorySlug();
const lessonSlug = currentLessonSlug();

const title = computed(() => localizedText(lesson.value, 'title', language.value));
const summary = computed(() => localizedText(lesson.value, 'summary', language.value));
const categoryName = computed(() => localizedText(lesson.value?.category, 'name', language.value));
const content = computed(() => localizedText(lesson.value, 'content', language.value));
const paragraphs = computed(() => content.value.split(/\n{2,}/).map((item) => item.trim()).filter(Boolean));
const relatedQuizzes = computed(() => lesson.value?.related_quizzes || []);
const firstQuiz = computed(() => relatedQuizzes.value[0] || null);
const categoryHref = computed(() => learnCategoryUrl(lesson.value?.category?.slug || categorySlug));

onMounted(async () => {
    try {
        const response = await getLesson(lessonSlug);
        lesson.value = response.data;

        if (lesson.value.category?.slug !== categorySlug) {
            error.value = 'This lesson belongs to a different Learn category.';
        }
    } catch (caughtError) {
        error.value = caughtError.status === 404
            ? 'This lesson could not be found.'
            : caughtError.message || 'Lesson content could not be loaded.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-10 md:py-14">
            <section v-if="isLoading" class="mx-auto max-w-4xl">
                <div class="soft-card min-h-96 animate-pulse p-8">
                    <div class="h-6 w-32 rounded-full bg-heritage-panel" />
                    <div class="mt-8 h-12 w-3/4 rounded-full bg-heritage-panel" />
                    <div class="mt-4 h-5 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-10 h-40 rounded-[2rem] bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="section-panel mx-auto max-w-4xl text-center">
                <AppBadge variant="red">Lesson unavailable</AppBadge>
                <h1 class="mt-4 text-3xl font-black text-heritage-ink">We could not load this lesson</h1>
                <p class="mx-auto mt-3 max-w-2xl text-heritage-muted">{{ error }}</p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <PrimaryButton :href="learnUrl()">Back to Learn</PrimaryButton>
                    <PrimaryButton :href="categoryHref" variant="soft">Category lessons</PrimaryButton>
                </div>
            </section>

            <template v-else>
                <section class="mx-auto max-w-5xl">
                    <div class="heritage-pattern rounded-[2rem] p-8 text-white md:p-10">
                        <div class="flex flex-wrap gap-2">
                            <AppBadge variant="gold">{{ categoryName }}</AppBadge>
                            <AppBadge variant="navy">{{ difficultyLabel(lesson.difficulty) }}</AppBadge>
                            <span class="rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase">
                                {{ lesson.estimated_minutes || 'Self-paced' }}<span v-if="lesson.estimated_minutes"> min</span>
                            </span>
                        </div>
                        <h1 class="mt-5 text-4xl font-black leading-tight md:text-5xl">{{ title }}</h1>
                        <p class="mt-4 max-w-3xl text-lg leading-8 text-white/85">{{ summary }}</p>
                    </div>
                </section>

                <section class="mx-auto mt-8 grid max-w-5xl gap-8 lg:grid-cols-[1fr_18rem]">
                    <article class="soft-card p-6 md:p-8">
                        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <div>
                                <AppBadge>Lesson</AppBadge>
                                <h2 class="mt-3 text-2xl font-black text-heritage-ink">Read and remember</h2>
                            </div>
                            <div class="rounded-full bg-heritage-panel p-1">
                                <button
                                    type="button"
                                    aria-label="Show English lesson text"
                                    :aria-pressed="language === 'en'"
                                    :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'en' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']"
                                    @click="language = 'en'"
                                >
                                    EN
                                </button>
                                <button
                                    type="button"
                                    aria-label="Show Macedonian lesson text"
                                    :aria-pressed="language === 'mk'"
                                    :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'mk' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']"
                                    @click="language = 'mk'"
                                >
                                    MK
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-5">
                            <p v-for="paragraph in paragraphs" :key="paragraph" class="text-base leading-8 text-heritage-muted md:text-lg md:leading-9">
                                {{ paragraph }}
                            </p>
                        </div>

                        <div class="mt-8 rounded-[1.5rem] bg-heritage-gold-faint p-5">
                            <p class="label text-heritage-gold-deep">Key points</p>
                            <ul class="mt-3 grid gap-2 text-sm font-bold leading-6 text-heritage-gold-deep">
                                <li v-for="paragraph in paragraphs.slice(0, 3)" :key="`key-${paragraph}`">- {{ paragraph }}</li>
                            </ul>
                        </div>
                    </article>

                    <aside class="grid content-start gap-5">
                        <div class="soft-card p-5">
                            <p class="label">Learning loop</p>
                            <h3 class="mt-3 text-2xl font-black text-heritage-ink">Ready to practise?</h3>
                            <p class="mt-3 text-sm leading-6 text-heritage-muted">Take the related quiz, then come back here to review anything that felt tricky.</p>
                            <PrimaryButton v-if="firstQuiz" :href="firstQuiz.start_url" class="mt-5 w-full">Take related quiz</PrimaryButton>
                            <PrimaryButton v-else href="/quizzes" class="mt-5 w-full">Explore quizzes</PrimaryButton>
                        </div>
                        <div class="soft-card p-5">
                            <p class="label">Navigation</p>
                            <div class="mt-4 grid gap-3">
                                <PrimaryButton :href="categoryHref" variant="soft">Back to category</PrimaryButton>
                                <PrimaryButton :href="learnUrl()" variant="ghost">All lessons</PrimaryButton>
                            </div>
                        </div>
                    </aside>
                </section>
            </template>
        </main>
    </PublicLayout>
</template>
