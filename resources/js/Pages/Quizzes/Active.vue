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
        <header class="sticky top-0 z-40 border-b border-heritage-line/40 bg-white/95 shadow-card backdrop-blur">
            <div class="page-shell py-4">
                <div class="flex items-center justify-between gap-4">
                    <a href="/quizzes/history" class="button-soft rounded-2xl px-4 py-3 text-sm font-black">Close</a>
                    <div class="hidden flex-1 items-center gap-4 sm:flex">
                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-heritage-panel ring-1 ring-heritage-line/50">
                            <div class="h-full w-[30%] rounded-full bg-linear-to-r from-heritage-red to-heritage-gold" />
                        </div>
                        <span class="text-sm font-black text-heritage-muted">Question 3 / 10</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-heritage-panel p-1">
                            <button :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'EN' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'EN'">EN</button>
                            <button :class="['rounded-full px-3 py-1 text-xs font-black transition', language === 'MK' ? 'bg-white text-heritage-red shadow-card' : 'text-heritage-muted']" @click="language = 'MK'">MK</button>
                        </div>
                        <div class="rounded-full bg-heritage-gold-faint px-4 py-2 text-sm font-black text-heritage-gold-deep">450 pts</div>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3 sm:hidden">
                    <div class="h-3 flex-1 overflow-hidden rounded-full bg-heritage-panel">
                        <div class="h-full w-[30%] rounded-full bg-linear-to-r from-heritage-red to-heritage-gold" />
                    </div>
                    <span class="text-sm font-black text-heritage-muted">3/10</span>
                </div>
            </div>
        </header>

        <main class="page-shell flex flex-1 flex-col justify-center py-8">
            <section class="mx-auto w-full max-w-3xl">
                <div class="soft-card p-6 text-center md:p-8">
                    <span class="eyebrow">Vocabulary quiz</span>
                    <h1 class="mt-6 text-3xl font-black leading-tight text-heritage-ink md:text-5xl">
                        What does <span class="text-heritage-red">"добро утро"</span> mean?
                    </h1>
                    <div class="mx-auto mt-8 grid max-w-xl grid-cols-5 gap-2 rounded-[2rem] bg-heritage-panel p-4">
                        <span v-for="letter in ['Д','О','Б','Р','О']" :key="letter" class="rounded-2xl bg-heritage-red-faint py-4 text-2xl font-black text-heritage-red">{{ letter }}</span>
                        <span v-for="letter in ['У','Т','Р','О','AM']" :key="letter" class="rounded-2xl bg-heritage-gold-faint py-4 text-2xl font-black text-heritage-gold-deep">{{ letter }}</span>
                    </div>
                </div>
            </section>

            <section class="mx-auto mt-6 grid w-full max-w-3xl gap-4 md:grid-cols-2">
                <button
                    v-for="answer in answers"
                    :key="answer.key"
                    type="button"
                    :class="[
                        'relative flex min-h-20 items-center rounded-2xl border-2 p-5 text-left shadow-card transition active:scale-[0.99]',
                        selected === answer.key && answer.key === 'B' ? 'border-heritage-gold bg-heritage-gold-faint text-heritage-gold-deep' : 'border-heritage-line bg-white text-heritage-ink hover:border-heritage-gold hover:bg-heritage-gold-faint/40',
                        selected === answer.key && answer.key !== 'B' ? 'border-heritage-red bg-heritage-red-faint' : '',
                    ]"
                    @click="selected = answer.key"
                >
                    <span :class="['mr-4 flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-black', selected === answer.key ? 'bg-heritage-red text-white' : 'bg-heritage-panel text-heritage-muted']">
                        {{ answer.key }}
                    </span>
                    <span class="text-lg font-black">{{ answer.text }}</span>
                    <span v-if="selected === answer.key && answer.key === 'B'" class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-heritage-gold text-sm font-black text-heritage-navy">OK</span>
                </button>
            </section>
        </main>

        <footer class="border-t-4 bg-white" :class="isCorrect ? 'border-heritage-gold' : 'border-heritage-red'">
            <div class="page-shell flex flex-col items-center justify-between gap-5 py-5 md:flex-row">
                <div class="flex items-center gap-4 text-center md:text-left">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-heritage-gold-faint text-lg font-black text-heritage-gold-deep">
                        {{ isCorrect ? 'OK' : '!' }}
                    </div>
                    <div>
                        <p class="text-xl font-black text-heritage-ink">{{ isCorrect ? 'Excellent!' : 'Try another answer' }}</p>
                        <p class="text-sm text-heritage-muted">{{ isCorrect ? 'Correct meaning of the morning greeting. +15 points' : 'The correct answer is available as a visual state.' }}</p>
                    </div>
                </div>
                <PrimaryButton href="/quizzes/history/results" size="lg">Next</PrimaryButton>
            </div>
        </footer>
    </div>
</template>
