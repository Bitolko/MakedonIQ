<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import CategoryCard from '../../Components/CategoryCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import DemoPreviewSection from '../../Components/DemoPreviewSection.vue';
import { categoryUrl, currentUser, fetchJson, localizedText, preferredLanguage } from '../../api/makedoniq';

const categories = ref([]);
const isLoading = ref(true);
const error = ref('');
const search = ref('');
const language = preferredLanguage();
const user = currentUser();

const tones = ['red', 'gold', 'navy'];

const continueActions = [
    {
        title: 'Browse categories',
        description: 'Choose a topic and start a quiz.',
        href: '#quiz-categories',
        button: 'Browse',
        visual: 'categories',
        tone: 'red',
    },
    {
        title: 'View progress',
        description: 'Review saved quiz attempts and scores.',
        href: '/progress',
        button: 'Progress',
        visual: 'progress',
        tone: 'navy',
    },
    {
        title: 'Map Challenge',
        description: 'Guess Macedonian places from map clues.',
        href: '/map-challenge',
        button: 'Open map',
        visual: 'map',
        tone: 'gold',
    },
    {
        title: 'Review lessons',
        description: 'Read a short lesson before practising.',
        href: '/learn',
        button: 'Review lessons',
        visual: 'lessons',
        tone: 'red',
    },
];

const actionToneClasses = {
    red: {
        panel: 'from-heritage-red-faint via-white to-heritage-gold-faint',
        cta: 'bg-heritage-red text-white group-hover:bg-heritage-red-dark',
    },
    gold: {
        panel: 'from-heritage-gold-faint via-white to-sky-50',
        cta: 'bg-heritage-gold text-heritage-navy group-hover:bg-heritage-gold-soft',
    },
    navy: {
        panel: 'from-heritage-navy-soft via-white to-heritage-panel',
        cta: 'bg-heritage-navy text-white group-hover:bg-heritage-red',
    },
};

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
const heroBadges = computed(() => (isGuest.value
    ? ['EN/MK', 'Demo access', 'Save after signup']
    : ['EN/MK', 'Secure scoring', 'Saved progress']));

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
            <section class="relative mb-10 overflow-hidden rounded-[2.5rem] border border-heritage-gold/30 bg-white p-6 shadow-card md:p-8 lg:p-10">
                <div class="pointer-events-none absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.10) 1px, transparent 0); background-size: 28px 28px;" />

                <div class="relative">
                    <AppBadge>Quiz Categories</AppBadge>
                    <h1 class="mt-4 max-w-4xl text-4xl font-black leading-tight text-heritage-ink md:text-5xl">Explore quiz categories</h1>
                    <p class="mt-4 max-w-3xl text-lg leading-8 text-heritage-muted">
                        {{ isGuest ? 'Try a few demo quizzes free, then create an account to unlock all quizzes and save your scores.' : 'Browse published quiz categories, practise by topic, and keep building your saved progress.' }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span v-for="badge in heroBadges" :key="badge" class="rounded-full border border-heritage-line/60 bg-heritage-panel/70 px-3 py-1 text-xs font-black uppercase text-heritage-muted">
                            {{ badge }}
                        </span>
                    </div>

                    <div class="mt-8 max-w-5xl rounded-[1.75rem] border border-heritage-line/60 bg-heritage-panel/70 p-3">
                        <div class="flex flex-col gap-3 md:flex-row">
                            <input
                                v-model="search"
                                class="field min-w-0 bg-white px-5 py-4 text-base shadow-none md:flex-1"
                                placeholder="Search quiz topics, categories, or skills..."
                                type="search"
                            >
                            <button class="button-soft rounded-2xl px-6 py-4 text-sm font-black" type="button">Filter</button>
                            <button
                                v-if="search"
                                class="rounded-2xl border border-heritage-line/70 bg-white px-6 py-4 text-sm font-black text-heritage-muted transition hover:border-heritage-red/30 hover:text-heritage-red"
                                type="button"
                                @click="search = ''"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <DemoPreviewSection v-if="isGuest" id="free-demos" class-name="mb-10" />

            <section v-else class="mb-10 rounded-[2.25rem] border border-heritage-gold/40 bg-white p-5 shadow-card md:p-8">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <AppBadge variant="gold">CONTINUE PRACTISING</AppBadge>
                        <h2 class="mt-3 text-3xl font-black text-heritage-ink">Choose your next quiz path</h2>
                        <p class="mt-2 max-w-2xl leading-7 text-heritage-muted">
                            Pick a quick practice mode, review progress, or explore geography challenges.
                        </p>
                    </div>
                    <PrimaryButton href="/progress" class="w-full sm:w-auto" variant="soft">View progress</PrimaryButton>
                </div>

                <div class="mt-7 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <a
                        v-for="action in continueActions"
                        :key="action.title"
                        :href="action.href"
                        class="group flex min-h-72 flex-col rounded-3xl border border-heritage-line/55 bg-white p-4 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/30 hover:shadow-soft"
                    >
                        <div :class="['relative h-28 overflow-hidden rounded-[1.35rem] border border-white/80 bg-linear-to-br p-4 shadow-inner', actionToneClasses[action.tone].panel]">
                            <div v-if="action.visual === 'categories'" class="grid h-full grid-cols-2 gap-2">
                                <span class="rounded-2xl bg-white shadow-card" />
                                <span class="rounded-2xl bg-heritage-red shadow-card" />
                                <span class="rounded-2xl bg-heritage-gold shadow-card" />
                                <span class="rounded-2xl bg-white shadow-card" />
                            </div>
                            <div v-else-if="action.visual === 'progress'" class="flex h-full items-center gap-3">
                                <div class="h-16 w-16 rounded-full bg-[conic-gradient(#a40000_0_58%,#fff1ef_58%_100%)] p-2 shadow-card">
                                    <div class="h-full w-full rounded-full bg-white" />
                                </div>
                                <div class="grid flex-1 gap-2">
                                    <span class="h-2 rounded-full bg-heritage-red" />
                                    <span class="h-2 w-4/5 rounded-full bg-heritage-gold" />
                                    <span class="h-2 w-3/5 rounded-full bg-heritage-navy" />
                                </div>
                            </div>
                            <div v-else-if="action.visual === 'map'" class="relative h-full">
                                <span class="absolute left-3 top-5 h-12 w-24 rounded-[50%] bg-sky-100 shadow-inner" />
                                <span class="absolute bottom-4 right-3 h-11 w-20 rounded-[45%_55%_45%_55%] bg-heritage-gold-faint shadow-inner" />
                                <span class="absolute left-16 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-heritage-red shadow-card ring-4 ring-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-white" />
                                </span>
                            </div>
                            <div v-else class="relative h-full">
                                <span class="absolute left-2 top-3 h-20 w-24 rotate-[-7deg] rounded-2xl bg-white shadow-card" />
                                <span class="absolute right-4 top-7 rounded-2xl border border-heritage-red/20 bg-white px-4 py-3 text-lg font-black text-heritage-red shadow-card">Zdravo</span>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col pt-5">
                            <h3 class="text-xl font-black text-heritage-ink group-hover:text-heritage-red">{{ action.title }}</h3>
                            <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ action.description }}</p>
                            <span :class="['mt-auto inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-black shadow-card transition', actionToneClasses[action.tone].cta]">
                                {{ action.button }}
                            </span>
                        </div>
                    </a>
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

            <section v-else-if="filteredCategories.length" id="quiz-categories" class="scroll-mt-24 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <CategoryCard v-for="category in filteredCategories" :key="category.slug" :category="category" />
            </section>

            <section v-else class="section-panel text-center">
                <h2 class="text-2xl font-black text-heritage-ink">No categories found</h2>
                <p class="mt-3 text-heritage-muted">Try a different search term.</p>
            </section>
        </main>
    </PublicLayout>
</template>
