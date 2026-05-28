<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
import {
    categoryUrl,
    currentCategorySlug,
    currentQuizSlug,
    fetchJson,
    quizActiveUrl,
    quizStartUrl,
} from '../../api/makedoniq';

const quiz = ref(null);
const isLoading = ref(true);
const error = ref('');

const categorySlug = currentCategorySlug();
const quizSlug = currentQuizSlug();

const reviewUrl = computed(() => quizActiveUrl(quiz.value?.category?.slug || categorySlug, quiz.value?.slug || quizSlug));
const tryAgainUrl = computed(() => quizStartUrl(quiz.value?.category?.slug || categorySlug, quiz.value?.slug || quizSlug));
const continueUrl = computed(() => categoryUrl(quiz.value?.category?.slug || categorySlug));

onMounted(async () => {
    try {
        const response = await fetchJson(`/api/quizzes/${quizSlug}`);
        quiz.value = response.data;
    } catch (exception) {
        error.value = 'Quiz details could not be loaded. Results are still a placeholder until scoring is added.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-12">
            <section class="mx-auto max-w-4xl text-center">
                <AppBadge variant="gold">Quiz complete</AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-red md:text-5xl">Great Job!</h1>
                <p class="mt-4 text-lg text-heritage-muted">
                    <span v-if="isLoading">Loading quiz summary...</span>
                    <span v-else-if="quiz">You completed {{ quiz.title_en }}. Real scoring and saved attempts will be added next.</span>
                    <span v-else>{{ error }}</span>
                </p>
            </section>

            <section class="mx-auto mt-10 grid max-w-5xl gap-6 md:grid-cols-3">
                <article class="soft-card p-8 text-center">
                    <p class="label">Final score</p>
                    <p class="mt-4 text-5xl font-black text-heritage-red">90%</p>
                </article>
                <article class="soft-card p-8 text-center">
                    <p class="label">Correct answers</p>
                    <p class="mt-4 text-5xl font-black text-heritage-ink">9/10</p>
                </article>
                <article class="soft-card p-8 text-center">
                    <p class="label">Points earned</p>
                    <p class="mt-4 text-5xl font-black text-heritage-gold-deep">135</p>
                </article>
            </section>

            <section class="mx-auto mt-8 grid max-w-5xl gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <article class="heritage-pattern rounded-[2rem] p-8 text-center text-white shadow-soft">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white text-3xl font-black text-heritage-red">HI</div>
                    <h2 class="mt-5 text-2xl font-black">Badge earned</h2>
                    <p class="mt-2 font-semibold text-white/80">History Starter</p>
                </article>
                <article class="soft-card p-8">
                    <h2 class="text-2xl font-black text-heritage-ink">Achievement message</h2>
                    <p class="mt-3 leading-7 text-heritage-muted">
                        You are building a strong foundation through bilingual Macedonian learning. This screen is ready for real score data once secure quiz submission is added.
                    </p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <PrimaryButton :href="reviewUrl" variant="soft">Review answers</PrimaryButton>
                        <PrimaryButton :href="tryAgainUrl" variant="ghost">Try again</PrimaryButton>
                        <PrimaryButton :href="continueUrl">Continue learning</PrimaryButton>
                    </div>
                </article>
            </section>
        </main>
    </PublicLayout>
</template>
