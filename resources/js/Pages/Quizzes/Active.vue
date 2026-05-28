<script setup>
import { computed, ref } from 'vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';

const selected = ref('B');
const language = ref('EN');
const answers = [
    { key: 'A', text: 'Good night' },
    { key: 'B', text: 'Good morning' },
    { key: 'C', text: 'Thank you' },
    { key: 'D', text: 'Goodbye' },
];

const isCorrect = computed(() => selected.value === 'B');
</script>

<template>
    <div class="flex min-h-screen flex-col bg-heritage-bg">
        <header class="sticky top-0 z-40 border-b border-heritage-line/40 bg-white">
            <div class="page-shell py-4">
                <div class="flex items-center justify-between gap-4">
                    <a href="/quizzes/history" class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-muted">Close</a>
                    <div class="hidden flex-1 items-center gap-4 sm:flex">
                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-heritage-panel">
                            <div class="h-full w-[30%] rounded-full bg-heritage-red" />
                        </div>
                        <span class="text-sm font-black text-heritage-muted">3 / 10</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-heritage-panel p-1">
                            <button :class="['rounded-full px-3 py-1 text-xs font-black', language === 'EN' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'EN'">EN</button>
                            <button :class="['rounded-full px-3 py-1 text-xs font-black', language === 'MK' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'MK'">MK</button>
                        </div>
                        <div class="rounded-full bg-heritage-gold-soft px-4 py-2 text-sm font-black text-heritage-gold-deep">450 pts</div>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3 sm:hidden">
                    <div class="h-3 flex-1 overflow-hidden rounded-full bg-heritage-panel">
                        <div class="h-full w-[30%] rounded-full bg-heritage-red" />
                    </div>
                    <span class="text-sm font-black text-heritage-muted">3/10</span>
                </div>
            </div>
        </header>

        <main class="page-shell flex flex-1 flex-col justify-center py-8">
            <section class="mx-auto w-full max-w-3xl text-center">
                <span class="eyebrow">Vocabulary quiz</span>
                <h1 class="mt-6 text-4xl font-black leading-tight text-heritage-ink md:text-5xl">
                    What does <span class="text-heritage-red">“добро утро”</span> mean?
                </h1>
                <div class="mx-auto mt-8 grid max-w-xl grid-cols-5 gap-2 rounded-[2rem] bg-white p-4 shadow-card">
                    <span v-for="letter in ['Д','О','Б','Р','О']" :key="letter" class="rounded-2xl bg-heritage-red-soft py-4 text-2xl font-black text-heritage-red">{{ letter }}</span>
                    <span v-for="letter in ['У','Т','Р','О','☀']" :key="letter" class="rounded-2xl bg-heritage-gold-soft py-4 text-2xl font-black text-heritage-gold-deep">{{ letter }}</span>
                </div>
            </section>

            <section class="mx-auto mt-10 grid w-full max-w-3xl gap-4 md:grid-cols-2">
                <button
                    v-for="answer in answers"
                    :key="answer.key"
                    type="button"
                    :class="[
                        'relative flex items-center rounded-2xl border-2 p-5 text-left transition active:scale-[0.99]',
                        selected === answer.key && answer.key === 'B' ? 'border-heritage-gold bg-heritage-gold-soft text-heritage-gold-deep' : 'border-heritage-line bg-white text-heritage-ink hover:border-heritage-gold',
                        selected === answer.key && answer.key !== 'B' ? 'border-heritage-red bg-heritage-red-soft' : '',
                    ]"
                    @click="selected = answer.key"
                >
                    <span :class="['mr-4 flex h-10 w-10 items-center justify-center rounded-full text-sm font-black', selected === answer.key ? 'bg-heritage-red text-white' : 'bg-heritage-panel text-heritage-muted']">
                        {{ answer.key }}
                    </span>
                    <span class="text-lg font-black">{{ answer.text }}</span>
                    <span v-if="selected === answer.key && answer.key === 'B'" class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-heritage-gold text-sm font-black text-heritage-navy">✓</span>
                </button>
            </section>
        </main>

        <footer class="border-t-4 bg-white" :class="isCorrect ? 'border-heritage-gold' : 'border-heritage-red'">
            <div class="page-shell flex flex-col items-center justify-between gap-5 py-5 md:flex-row">
                <div class="flex items-center gap-4 text-center md:text-left">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-heritage-gold-soft text-2xl font-black text-heritage-gold-deep">
                        {{ isCorrect ? '✓' : '!' }}
                    </div>
                    <div>
                        <p class="text-xl font-black text-heritage-ink">{{ isCorrect ? 'Excellent!' : 'Try another answer' }}</p>
                        <p class="text-sm text-heritage-muted">{{ isCorrect ? 'Correct meaning of the morning greeting. +15 points' : 'The correct answer is available as a visual state.' }}</p>
                    </div>
                </div>
                <PrimaryButton href="/quizzes/history/results">Next</PrimaryButton>
            </div>
        </footer>
    </div>
</template>
