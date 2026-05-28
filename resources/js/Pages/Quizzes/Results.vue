<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import AppBadge from '../../Components/AppBadge.vue';
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-12">
            <section class="mx-auto max-w-4xl text-center">
                <AppBadge variant="gold">Quiz complete</AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-red md:text-5xl">Great Job!</h1>
                <p class="mt-4 text-lg text-heritage-muted">You completed Ancient Macedonia Basics and earned a new learning badge.</p>
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
                <article class="soft-card p-8 text-center">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[2rem] bg-heritage-gold-soft text-5xl font-black text-heritage-gold-deep">★</div>
                    <h2 class="mt-5 text-2xl font-black text-heritage-ink">Badge earned</h2>
                    <p class="mt-2 font-semibold text-heritage-muted">History Starter</p>
                </article>
                <article class="soft-card p-8">
                    <h2 class="text-2xl font-black text-heritage-ink">Achievement message</h2>
                    <p class="mt-3 leading-7 text-heritage-muted">
                        You are building a strong foundation in Macedonian history. Keep going with cultural heritage, geography, and language quizzes to connect the story together.
                    </p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="/quizzes/history/active" class="rounded-2xl bg-heritage-panel px-6 py-3 text-center text-sm font-black text-heritage-muted">Review answers</a>
                        <a href="/quizzes/history/start" class="rounded-2xl bg-heritage-panel px-6 py-3 text-center text-sm font-black text-heritage-muted">Try again</a>
                        <PrimaryButton href="/quizzes">Continue learning</PrimaryButton>
                    </div>
                </article>
            </section>
        </main>
    </PublicLayout>
</template>
