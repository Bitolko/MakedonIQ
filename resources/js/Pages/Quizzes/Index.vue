<script setup>
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '../../Components/PublicLayout.vue';
import CategoryCard from '../../Components/CategoryCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { categoryUrl, fetchJson, localizedText, preferredLanguage } from '../../api/makedoniq';

const categories = ref([]);
const isLoading = ref(true);
const error = ref('');
const search = ref('');
const language = preferredLanguage();

const tones = ['red', 'gold', 'navy'];
const demoItems = [
    {
        title: 'Basic Macedonian Greetings',
        detail: 'Try friendly everyday phrases.',
        href: '/quizzes/macedonian-language/basic-macedonian-greetings/start',
    },
    {
        title: 'Cyrillic Alphabet Basics',
        detail: 'Practise first Macedonian letters.',
        href: '/quizzes/macedonian-alphabet/cyrillic-alphabet-basics/start',
    },
    {
        title: 'Macedonia Map Challenge',
        detail: 'Play the basic map challenge demo.',
        href: '/map-challenge',
    },
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

            <section class="mb-10 rounded-[2rem] border border-heritage-gold/40 bg-white p-6 shadow-card md:p-8">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <AppBadge variant="gold">Try these demos</AppBadge>
                        <h2 class="mt-3 text-3xl font-black text-heritage-ink">Start playing now</h2>
                    </div>
                    <PrimaryButton href="/register" variant="soft">Create free account</PrimaryButton>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <article v-for="item in demoItems" :key="item.title" class="rounded-[1.5rem] border border-heritage-line bg-heritage-panel p-5">
                        <p class="text-xs font-black uppercase text-heritage-red">Demo</p>
                        <h3 class="mt-2 text-xl font-black text-heritage-ink">{{ item.title }}</h3>
                        <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                        <PrimaryButton :href="item.href" class="mt-4 w-full" size="sm" variant="soft">Try demo</PrimaryButton>
                    </article>
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
