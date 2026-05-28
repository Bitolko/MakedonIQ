<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';

const learnItems = [
    'Key people and places in ancient Macedonian history',
    'How historical stories connect with culture and identity',
    'Vocabulary used in bilingual Macedonian history prompts',
    'Confidence answering short quiz questions under light time pressure',
];
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-10 py-12 lg:grid-cols-[1.2fr_0.8fr] lg:items-start">
            <section>
                <AppBadge>History and culture</AppBadge>
                <h1 class="mt-5 text-4xl font-black leading-tight text-heritage-red md:text-5xl">
                    Ancient Macedonia Basics
                </h1>
                <p class="mt-5 max-w-3xl text-lg leading-8 text-heritage-muted">
                    A friendly starter quiz about ancient Macedonia, designed for learners who want a quick and confident introduction to important people, places, and ideas.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="metric-card text-center">
                        <p class="label">Difficulty</p>
                        <p class="mt-2 text-xl font-black text-heritage-ink">Beginner</p>
                    </div>
                    <div class="metric-card text-center">
                        <p class="label">Questions</p>
                        <p class="mt-2 text-xl font-black text-heritage-ink">10 items</p>
                    </div>
                    <div class="metric-card text-center">
                        <p class="label">Time</p>
                        <p class="mt-2 text-xl font-black text-heritage-ink">8 min</p>
                    </div>
                </div>

                <section class="section-panel mt-8">
                    <h2 class="text-2xl font-black text-heritage-ink">What you will learn</h2>
                    <div class="mt-6 grid gap-4">
                        <div v-for="(item, index) in learnItems" :key="item" class="flex gap-3 rounded-2xl bg-heritage-panel p-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white">{{ index + 1 }}</span>
                            <span class="font-semibold text-heritage-muted">{{ item }}</span>
                        </div>
                    </div>
                </section>
            </section>

            <aside class="soft-card sticky top-28 p-6 md:p-8">
                <div class="rounded-[1.5rem] bg-heritage-panel p-5">
                    <p class="label">Quiz overview</p>
                    <p class="mt-3 text-3xl font-black text-heritage-ink">Earn up to 150 points</p>
                    <p class="mt-3 leading-7 text-heritage-muted">
                        Questions are short, friendly, and built for bilingual learning. You can review your results after finishing.
                    </p>
                </div>
                <PrimaryButton href="/quizzes/history/active" class="mt-6 w-full" size="lg">Start Quiz</PrimaryButton>
                <PrimaryButton href="/quizzes/history" variant="soft" class="mt-4 w-full">Back to history quizzes</PrimaryButton>
            </aside>
        </main>
    </PublicLayout>
</template>
