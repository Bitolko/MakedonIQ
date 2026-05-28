<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import QuizCard from '../../Components/QuizCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import { historyQuizzes } from '../../data/makedoniq';

const categoryLinks = ['History', 'Language', 'Alphabet', 'Geography', 'Culture', 'Food and Music'];
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-8 py-10 lg:grid-cols-[16rem_1fr]">
            <aside class="hidden rounded-3xl bg-white p-5 shadow-card lg:block">
                <h2 class="text-lg font-black text-heritage-ink">Categories</h2>
                <nav class="mt-5 grid gap-2">
                    <a
                        v-for="item in categoryLinks"
                        :key="item"
                        href="/quizzes/history"
                        :class="[
                            'rounded-2xl px-4 py-3 text-sm font-bold',
                            item === 'History' ? 'bg-heritage-gold-soft text-heritage-gold-deep' : 'text-heritage-muted hover:bg-heritage-panel',
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
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <QuizCard v-for="quiz in historyQuizzes" :key="quiz.title" :quiz="quiz" />
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
