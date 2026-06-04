<script setup>
import AppBadge from './AppBadge.vue';
import PrimaryButton from './PrimaryButton.vue';

defineProps({
    isGuest: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <section class="relative overflow-hidden rounded-[2.5rem] border border-heritage-gold/45 bg-white p-5 shadow-soft md:p-8 lg:p-10">
        <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-heritage-gold-faint via-white to-heritage-red-faint/80" />
        <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.14) 1px, transparent 0); background-size: 30px 30px;" />
        <div class="relative grid gap-8 lg:grid-cols-[0.94fr_1.06fr] lg:items-center">
            <div class="min-w-0">
                <AppBadge variant="red">LEARN MACEDONIAN</AppBadge>
                <h1 class="mt-5 max-w-3xl text-4xl font-black leading-tight text-heritage-ink md:text-5xl lg:text-6xl">
                    Learn Macedonian step by step
                </h1>
                <p class="mt-5 max-w-2xl text-base font-bold leading-8 text-heritage-muted md:text-lg">
                    {{ isGuest ? 'Try a few lessons free, then create an account to unlock all lessons, quizzes, progress tracking, and saved results.' : 'Explore lessons, practise with quizzes, and keep building your Macedonian learning path.' }}
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <PrimaryButton href="#learn-categories" size="lg">
                        {{ isGuest ? 'Start learning' : 'Browse lessons' }}
                    </PrimaryButton>
                    <PrimaryButton href="/quizzes" variant="gold" size="lg">Take a quiz</PrimaryButton>
                    <PrimaryButton v-if="isGuest" href="/register" variant="soft" size="lg">Create free account</PrimaryButton>
                    <PrimaryButton v-else href="/progress" variant="soft" size="lg">View progress</PrimaryButton>
                    <PrimaryButton href="/map-challenge" variant="white" size="lg">Map Challenge</PrimaryButton>
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-heritage-line/60 bg-white/75 p-4 shadow-card">
                        <p class="text-xl font-black text-heritage-red">EN/MK</p>
                        <p class="mt-1 text-xs font-black uppercase text-heritage-muted">Bilingual lessons</p>
                    </div>
                    <div class="rounded-2xl border border-heritage-line/60 bg-white/75 p-4 shadow-card">
                        <p class="text-xl font-black text-heritage-gold-deep">Quiz</p>
                        <p class="mt-1 text-xs font-black uppercase text-heritage-muted">Practise after reading</p>
                    </div>
                    <div class="rounded-2xl border border-heritage-line/60 bg-white/75 p-4 shadow-card">
                        <p class="text-xl font-black text-heritage-navy">Map</p>
                        <p class="mt-1 text-xs font-black uppercase text-heritage-muted">Play with places</p>
                    </div>
                </div>
            </div>

            <div class="relative min-w-0">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-heritage-gold/25 blur-2xl" />
                <div class="absolute -bottom-8 -left-4 h-32 w-32 rounded-full bg-heritage-red/15 blur-2xl" />
                <div class="relative overflow-hidden rounded-[2rem] border border-heritage-gold/45 bg-heritage-navy p-4 text-white shadow-soft md:p-5">
                    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/10 via-transparent to-heritage-red/25" />
                    <div class="pointer-events-none absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.22) 1px, transparent 0); background-size: 26px 26px;" />

                    <div class="relative grid gap-4">
                        <div class="grid gap-4 sm:grid-cols-[1.1fr_0.9fr]">
                            <div class="rounded-[1.5rem] border border-white/15 bg-white p-5 text-heritage-ink shadow-card">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-heritage-red-faint px-3 py-1 text-xs font-black uppercase text-heritage-red">Phrase</span>
                                    <span class="rounded-full bg-heritage-navy px-3 py-1 text-xs font-black uppercase text-white">MK -> EN</span>
                                </div>
                                <p class="mt-5 text-3xl font-black text-heritage-red">Добро утро</p>
                                <p class="mt-2 text-lg font-black text-heritage-navy">Good morning</p>
                                <div class="mt-5 flex items-center gap-2">
                                    <span class="h-2 flex-1 rounded-full bg-heritage-red/25" />
                                    <span class="h-2 flex-1 rounded-full bg-heritage-gold" />
                                    <span class="h-2 flex-1 rounded-full bg-heritage-navy/20" />
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] border border-white/15 bg-heritage-gold-faint p-5 text-heritage-ink shadow-card">
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-black uppercase text-heritage-gold-deep shadow-card">Alphabet</span>
                                <div class="mt-5 grid grid-cols-3 gap-2">
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-white text-3xl font-black text-heritage-red shadow-card">А</span>
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-gold text-3xl font-black text-heritage-navy shadow-card">Б</span>
                                    <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-red text-3xl font-black text-white shadow-card">В</span>
                                </div>
                                <p class="mt-4 text-sm font-black text-heritage-muted">Cyrillic basics</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="relative min-h-44 overflow-hidden rounded-[1.5rem] border border-white/15 bg-white/95 p-5 text-heritage-ink shadow-card">
                                <span class="relative z-10 rounded-full bg-heritage-navy px-3 py-1 text-xs font-black uppercase text-white">Map Challenge</span>
                                <div class="absolute left-7 top-20 h-9 w-14 rounded-[50%] bg-sky-200" />
                                <div class="absolute bottom-7 right-8 h-9 w-20 rounded-[50%] bg-sky-200" />
                                <div class="absolute bottom-7 left-7 h-0 w-0 border-x-[30px] border-b-[54px] border-x-transparent border-b-heritage-navy/20" />
                                <div class="absolute left-16 right-16 top-24 h-1 rotate-[-9deg] rounded-full bg-heritage-gold" />
                                <div class="absolute right-12 top-14 flex h-14 w-14 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white shadow-[0_4px_0_0_#760000] ring-4 ring-heritage-gold/35">PIN</div>
                            </div>

                            <div class="rounded-[1.5rem] border border-white/15 bg-white p-5 text-heritage-ink shadow-card">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-heritage-red-faint px-3 py-1 text-xs font-black uppercase text-heritage-red">Folklore songs</span>
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-heritage-gold text-lg font-black text-heritage-navy">♪</span>
                                </div>
                                <div class="mt-6 flex h-20 items-center gap-2">
                                    <span class="h-8 w-3 rounded-full bg-heritage-red" />
                                    <span class="h-14 w-3 rounded-full bg-heritage-gold" />
                                    <span class="h-10 w-3 rounded-full bg-heritage-navy" />
                                    <span class="h-20 w-3 rounded-full bg-heritage-red" />
                                    <span class="h-12 w-3 rounded-full bg-heritage-gold" />
                                    <span class="h-7 w-3 rounded-full bg-heritage-navy" />
                                </div>
                                <p class="mt-2 text-sm font-black text-heritage-muted">Listen, recognise, remember</p>
                            </div>
                        </div>

                        <div class="grid gap-3 rounded-[1.5rem] border border-white/15 bg-white/10 p-4 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/90 px-4 py-3 text-sm font-black text-heritage-ink">Learn</div>
                            <div class="rounded-2xl bg-heritage-gold px-4 py-3 text-sm font-black text-heritage-navy">Practise</div>
                            <div class="rounded-2xl bg-heritage-red px-4 py-3 text-sm font-black text-white">Track</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
