<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import CategoryCard from '../../Components/CategoryCard.vue';
import AppBadge from '../../Components/AppBadge.vue';
import { categories } from '../../data/makedoniq';
</script>

<template>
    <PublicLayout>
        <main class="page-shell py-10 md:py-14">
            <section class="mb-10 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <AppBadge>Quiz Categories</AppBadge>
                    <h1 class="mt-4 text-4xl font-black text-heritage-ink md:text-5xl">Explore Categories</h1>
                    <p class="mt-3 max-w-2xl text-lg text-heritage-muted">
                        Master Macedonian language, culture, history, geography, food, music, and traditions through friendly quizzes.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                    <input class="field min-w-0 sm:w-72" placeholder="Search topics..." type="search">
                    <button class="rounded-2xl bg-heritage-panel px-5 py-3 text-sm font-black text-heritage-muted">Filter</button>
                </div>
            </section>

            <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <CategoryCard v-for="category in categories" :key="category.slug" :category="category" />
            </section>
        </main>
    </PublicLayout>
</template>
