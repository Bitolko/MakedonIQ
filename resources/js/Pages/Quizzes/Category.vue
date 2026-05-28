<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import QuizCard from '../../Components/QuizCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import ProgressBar from '../../Components/ProgressBar.vue';
import { categoryUrl, currentCategorySlug, difficultyLabel, fetchJson, quizStartUrl } from '../../api/makedoniq';

const category = ref(null);
const categories = ref([]);
const quizzes = ref([]);
const isLoading = ref(true);
const error = ref('');

const activeSlug = currentCategorySlug();

const categoryLinks = computed(() => categories.value.map((item) => ({
    label: item.name_en,
    href: categoryUrl(item.slug),
    active: item.slug === activeSlug,
})));

const quizCards = computed(() => quizzes.value.map((quiz) => ({
    title: quiz.title_en,
    description: quiz.description_en,
    difficulty: difficultyLabel(quiz.difficulty),
    questions: quiz.questions_count || 0,
    time: quiz.estimated_minutes ? `${quiz.estimated_minutes} min` : 'Self-paced',
    progress: 0,
    status: 'Start',
    href: quizStartUrl(category.value?.slug || activeSlug, quiz.slug),
})));

onMounted(async () => {
    try {
        const [categoryResponse, categoriesResponse] = await Promise.all([
            fetchJson(`/api/categories/${activeSlug}`),
            fetchJson('/api/categories'),
        ]);

        category.value = categoryResponse.data.category;
        quizzes.value = categoryResponse.data.quizzes || [];
        categories.value = categoriesResponse.data || [];
    } catch (exception) {
        error.value = 'This category could not be loaded. Please check the seeded quiz data.';
    } finally {
        isLoading.value = false;
    }
});
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
                        <h1 class="mt-5 text-4xl font-black md:text-5xl">{{ category.name_en }}</h1>
                        <p class="mt-4 text-lg leading-8 text-white/80">
                            {{ category.description_en }}
                        </p>
                    </div>
                    <div class="mt-8 max-w-xl rounded-2xl bg-white/10 p-5 backdrop-blur">
                        <ProgressBar :value="0" label="Category progress will appear after scoring is added" tone="gold" />
                    </div>
                </div>

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
