<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import QuizCard from '../../Components/QuizCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import ProgressBar from '../../Components/ProgressBar.vue';
import { historyQuizzes } from '../../data/makedoniq';

const categoryLinks = ['History', 'Language', 'Alphabet', 'Geography', 'Culture', 'Food and Music'];
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-8 py-10 lg:grid-cols-[16rem_1fr]">
            <aside class="hidden self-start rounded-[1.5rem] bg-white p-5 shadow-card lg:block">
                <h2 class="text-lg font-black text-heritage-ink">Categories</h2>
                <nav class="mt-5 grid gap-2">
                    <a
                        v-for="item in categoryLinks"
                        :key="item"
                        :href="item === 'History' ? '/quizzes/history' : '/quizzes'"
                        :class="[
                            'rounded-2xl px-4 py-3 text-sm font-black transition',
                            item === 'History' ? 'bg-heritage-gold-faint text-heritage-gold-deep shadow-card' : 'text-heritage-muted hover:bg-heritage-panel',
                        ]"
                    >
                        {{ item }}
                    </a>
                </nav>
            </aside>

            <section>
                <div class="heritage-pattern overflow-hidden rounded-[2rem] p-8 text-white md:p-10">
                    <div class="max-w-2xl">
                        <AppBadge variant="gold">History category</AppBadge>
                        <h1 class="mt-5 text-4xl font-black md:text-5xl">History of Macedonia</h1>
                        <p class="mt-4 text-lg leading-8 text-white/80">
                            Journey through ancient Macedonia, cultural heritage, important figures, and modern Macedonian stories.
                        </p>
                    </div>
                    <div class="mt-8 max-w-xl rounded-2xl bg-white/10 p-5 backdrop-blur">
                        <ProgressBar :value="64" label="Category completion" tone="gold" />
                    </div>
                </div>

                <div class="mt-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-2xl font-black text-heritage-ink">Available history quizzes</h2>
                        <p class="mt-1 text-heritage-muted">Start with the basics or continue where you left off.</p>
                    </div>
                    <AppBadge variant="navy">{{ historyQuizzes.length }} quizzes</AppBadge>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <QuizCard v-for="quiz in historyQuizzes" :key="quiz.title" :quiz="quiz" />
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
