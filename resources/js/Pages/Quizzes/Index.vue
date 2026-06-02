<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import CategoryCard from '../../Components/CategoryCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { categoryUrl, currentUser, fetchJson, localizedText, preferredLanguage } from '../../api/makedoniq';

const categories = ref([]);
const isLoading = ref(true);
const error = ref('');
const search = ref('');
const language = preferredLanguage();
const user = currentUser();

const tones = ['red', 'gold', 'navy'];
const demoItems = [
    {
        title: 'Basic Macedonian Greetings',
        detail: 'Try friendly everyday phrases and start speaking with confidence.',
        href: '/quizzes/macedonian-language/basic-macedonian-greetings/start',
        category: 'Language',
        visual: 'phrases',
        practise: 'Greetings, polite words, family phrases',
        questions: 5,
        time: '6 min',
        accent: 'red',
        visualClass: 'bg-linear-to-br from-heritage-red-faint via-white to-heritage-gold-faint',
    },
    {
        title: 'Cyrillic Alphabet Basics',
        detail: 'Practise first Macedonian letters with a quick beginner round.',
        href: '/quizzes/macedonian-alphabet/cyrillic-alphabet-basics/start',
        category: 'Alphabet',
        visual: 'letters',
        practise: 'Letter shapes, sounds, first words',
        questions: 5,
        time: '7 min',
        accent: 'gold',
        visualClass: 'bg-linear-to-br from-heritage-gold-faint via-white to-heritage-navy-soft',
    },
    {
        title: 'Macedonia Map Challenge',
        detail: 'Guess cities, lakes, and places from a playful map clue.',
        href: '/map-challenge',
        category: 'Geography',
        visual: 'map',
        practise: 'Cities, lakes, landmarks',
        questions: 10,
        time: '8 min',
        accent: 'navy',
        visualClass: 'bg-linear-to-br from-heritage-navy-soft via-white to-heritage-gold-faint',
    },
];

const accountBenefits = [
    'Unlock all lessons',
    'Unlock all quizzes',
    'Save quiz scores',
    'Track progress',
    'Continue learning anytime',
    'Access Map Challenge and future picture quizzes',
];

const categoryCards = computed(() => categories.value.map((category, index) => ({
    slug: category.slug,
    title: localizedText(category, 'name', language),
    description: localizedText(category, 'description', language),
    level: 'Published',
    quizzes: category.quizzes_count || 0,
    icon: category.icon || category.name_en.slice(0, 2).toUpperCase(),
    href: categoryUrl(category.slug),
    tone: tones[index % tones.length],
})));

const filteredCategories = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return categoryCards.value;
    }

    return categoryCards.value.filter((category) => (
        category.title.toLowerCase().includes(term)
        || (category.description || '').toLowerCase().includes(term)
    ));
});

const isGuest = computed(() => !user);

onMounted(async () => {
    try {
        const response = await fetchJson('/api/categories');
        categories.value = response.data || [];
    } catch (exception) {
        error.value = 'Quiz categories could not be loaded. Please check the database connection and seeded content.';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-10 md:py-14">
            <section class="mb-10 grid gap-6 rounded-[2rem] bg-white p-6 shadow-card md:grid-cols-[1fr_auto] md:items-end md:p-8">
                <div>
                    <AppBadge>Quiz Categories</AppBadge>
                    <h1 class="mt-4 text-4xl font-black text-heritage-ink md:text-5xl">Explore Categories</h1>
                    <p class="mt-3 max-w-2xl text-lg leading-8 text-heritage-muted">
                        Try a few demo quizzes free. Create a free account to unlock all quizzes, save scores, and track progress.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                    <input v-model="search" class="field min-w-0 sm:w-72" placeholder="Search topics..." type="search">
                    <button class="button-soft rounded-2xl px-5 py-3 text-sm font-black" type="button">Filter</button>
                </div>
            </section>

            <section v-if="isGuest" id="free-demos" class="mb-10 overflow-hidden rounded-[2rem] border border-heritage-gold/40 bg-heritage-navy text-white shadow-soft">
                <div class="grid gap-8 p-5 md:p-8 lg:grid-cols-[1.45fr_0.85fr] lg:items-stretch">
                    <div class="min-w-0">
                        <AppBadge variant="gold">FREE DEMOS</AppBadge>
                        <h2 class="mt-4 max-w-3xl text-3xl font-black leading-tight text-white md:text-5xl">
                            Try MakedonIQ before you join
                        </h2>
                        <p class="mt-4 max-w-3xl text-base font-semibold leading-7 text-white/80 md:text-lg">
                            Play a few sample quizzes for free. Create an account to unlock all lessons, quizzes, saved scores, and progress tracking.
                        </p>
                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton href="/register" variant="gold">Create free account</PrimaryButton>
                            <PrimaryButton href="/login" variant="white">Log in</PrimaryButton>
                            <a href="#demo-cards" class="button-soft inline-flex justify-center rounded-2xl px-6 py-3 text-sm font-black">
                                Explore demos
                            </a>
                        </div>

                        <div id="demo-cards" class="mt-8 grid gap-4 lg:grid-cols-3">
                            <article
                                v-for="item in demoItems"
                                :key="item.title"
                                class="group flex min-h-[28rem] flex-col overflow-hidden rounded-[1.75rem] border border-white/70 bg-white p-4 text-heritage-ink shadow-card transition hover:-translate-y-1 hover:shadow-soft"
                            >
                                <div :class="['relative min-h-36 overflow-hidden rounded-[1.35rem] border border-white/80 p-4 shadow-card', item.visualClass]">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="rounded-full bg-white/85 px-3 py-1 text-[0.68rem] font-black uppercase text-heritage-red shadow-card">Demo</span>
                                        <span class="rounded-full bg-heritage-navy px-3 py-1 text-[0.68rem] font-black uppercase text-white">{{ item.category }}</span>
                                    </div>

                                    <div v-if="item.visual === 'phrases'" class="mt-5 grid gap-2">
                                        <div class="ml-3 max-w-[10rem] rounded-2xl bg-white px-4 py-3 text-sm font-black text-heritage-red shadow-card">Zdravo</div>
                                        <div class="mr-3 max-w-[11rem] justify-self-end rounded-2xl bg-heritage-red px-4 py-3 text-sm font-black text-white shadow-[0_4px_0_0_#760000]">Thank you</div>
                                        <div class="max-w-[9rem] rounded-2xl bg-heritage-gold px-4 py-3 text-sm font-black text-heritage-navy shadow-card">Kako si?</div>
                                    </div>

                                    <div v-else-if="item.visual === 'letters'" class="mt-5 grid grid-cols-3 gap-3">
                                        <span class="flex aspect-square items-center justify-center rounded-2xl bg-white text-3xl font-black text-heritage-red shadow-card">А</span>
                                        <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-gold text-3xl font-black text-heritage-navy shadow-card">Б</span>
                                        <span class="flex aspect-square items-center justify-center rounded-2xl bg-heritage-red text-3xl font-black text-white shadow-card">В</span>
                                    </div>

                                    <div v-else class="relative mt-4 min-h-28 rounded-[1.25rem] bg-white/80 p-4 shadow-card">
                                        <div class="absolute left-5 right-5 top-1/2 h-1 rounded-full bg-heritage-gold/70" />
                                        <div class="absolute bottom-5 left-7 top-5 w-1 rounded-full bg-heritage-red/70" />
                                        <div class="absolute right-9 top-6 flex h-12 w-12 items-center justify-center rounded-full bg-heritage-red text-xs font-black text-white shadow-[0_4px_0_0_#760000] ring-4 ring-heritage-gold/30">
                                            PIN
                                        </div>
                                        <div class="absolute bottom-6 left-12 rounded-full bg-heritage-navy px-3 py-1 text-xs font-black text-white">MK</div>
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col p-2 pt-5">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full bg-heritage-gold-faint px-3 py-1 text-xs font-black text-heritage-gold-deep">{{ item.questions }} questions</span>
                                        <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">{{ item.time }}</span>
                                    </div>
                                    <h3 class="mt-4 text-xl font-black leading-tight text-heritage-ink">{{ item.title }}</h3>
                                    <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                                    <div class="mt-4 rounded-2xl bg-heritage-panel p-3">
                                        <p class="text-[0.68rem] font-black uppercase text-heritage-red">Practise</p>
                                        <p class="mt-1 text-sm font-black leading-6 text-heritage-ink">{{ item.practise }}</p>
                                    </div>
                                    <a :href="item.href" class="pressable-red mt-auto inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black">
                                        Try demo <span aria-hidden="true">></span>
                                    </a>
                                </div>
                            </article>
                        </div>
                    </div>

                    <aside class="rounded-[1.5rem] border border-heritage-gold/35 bg-white p-5 text-heritage-ink shadow-card md:p-6">
                        <div class="rounded-[1.25rem] bg-heritage-gold-faint p-4">
                            <p class="text-xs font-black uppercase text-heritage-gold-deep">Free account</p>
                            <h3 class="mt-2 text-2xl font-black leading-tight text-heritage-ink">Unlock the full learning path</h3>
                            <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">
                                The demos are open now. Your account turns the rest of MakedonIQ into a saved learning journey.
                            </p>
                        </div>
                        <div class="mt-5 grid gap-3">
                            <div v-for="benefit in accountBenefits" :key="benefit" class="flex items-start gap-3 rounded-2xl border border-heritage-line/50 bg-heritage-panel p-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-heritage-red text-[0.68rem] font-black text-white">OK</span>
                                <span class="text-sm font-black leading-6 text-heritage-ink">{{ benefit }}</span>
                            </div>
                        </div>
                        <PrimaryButton href="/register" class="mt-5 w-full" variant="gold">Create free account</PrimaryButton>
                    </aside>
                </div>
            </section>

            <section v-if="isLoading" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="index in 6" :key="index" class="soft-card min-h-72 animate-pulse bg-white p-6">
                    <div class="h-14 w-14 rounded-2xl bg-heritage-panel" />
                    <div class="mt-8 h-7 w-2/3 rounded-full bg-heritage-panel" />
                    <div class="mt-4 h-4 w-full rounded-full bg-heritage-panel" />
                    <div class="mt-3 h-4 w-4/5 rounded-full bg-heritage-panel" />
                </div>
            </section>

            <section v-else-if="error" class="section-panel text-center">
                <h2 class="text-2xl font-black text-heritage-ink">Categories unavailable</h2>
                <p class="mx-auto mt-3 max-w-2xl leading-7 text-heritage-muted">{{ error }}</p>
            </section>

            <section v-else-if="filteredCategories.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <CategoryCard v-for="category in filteredCategories" :key="category.slug" :category="category" />
            </section>

            <section v-else class="section-panel text-center">
                <h2 class="text-2xl font-black text-heritage-ink">No categories found</h2>
                <p class="mt-3 text-heritage-muted">Try a different search term.</p>
            </section>
        </main>
    </PublicLayout>
</template>
