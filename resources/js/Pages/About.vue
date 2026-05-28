<script setup>
import PublicLayout from '../Components/PublicLayout.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
</script>

<template>
    <PublicLayout>
        <main>
            <section class="page-shell grid gap-10 py-14 md:grid-cols-[1fr_0.9fr] md:items-center">
                <div>
                    <AppBadge>About MakedonIQ</AppBadge>
                    <h1 class="mt-5 text-4xl font-black leading-tight text-heritage-ink md:text-5xl">
                        Helping Macedonian families preserve language and culture.
                    </h1>
                    <p class="mt-5 text-lg leading-8 text-heritage-muted">
                        MakedonIQ is a bilingual learning platform built for Macedonian families in Australia and learners worldwide. It makes heritage learning simple, fun, and easy to keep practising.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <PrimaryButton href="/quizzes">Start learning</PrimaryButton>
                        <PrimaryButton href="/contact" variant="soft">Contact us</PrimaryButton>
                    </div>
                </div>
                <div class="heritage-pattern rounded-[2rem] p-6 text-white shadow-soft">
                    <div class="rounded-[1.5rem] bg-white p-6 text-heritage-ink">
                        <p class="label">Mission</p>
                        <p class="mt-4 text-3xl font-black leading-tight">Simple, fun, bilingual learning for families, schools, and community groups.</p>
                    </div>
                </div>
            </section>

            <section class="bg-white py-14">
                <div class="page-shell grid gap-6 md:grid-cols-3">
                    <article class="soft-card soft-card-hover p-6">
                        <h2 class="text-xl font-black text-heritage-ink">Preserve language</h2>
                        <p class="mt-3 leading-7 text-heritage-muted">Short bilingual prompts help learners practise Macedonian words, phrases, and Cyrillic letters.</p>
                    </article>
                    <article class="soft-card soft-card-hover p-6">
                        <h2 class="text-xl font-black text-heritage-ink">Celebrate culture</h2>
                        <p class="mt-3 leading-7 text-heritage-muted">Quizzes cover traditions, history, geography, food, music, and everyday family knowledge.</p>
                    </article>
                    <article class="soft-card soft-card-hover p-6">
                        <h2 class="text-xl font-black text-heritage-ink">Support schools</h2>
                        <p class="mt-3 leading-7 text-heritage-muted">The admin direction is ready for future quiz management by teachers and community organisers.</p>
                    </article>
                </div>
            </section>

            <section class="page-shell py-14">
                <div class="rounded-[2rem] bg-heritage-panel p-8 md:p-10">
                    <AppBadge variant="red">Schools and community</AppBadge>
                    <h2 class="mt-4 text-3xl font-black text-heritage-ink">For schools and community groups</h2>
                    <p class="mt-4 max-w-3xl leading-7 text-heritage-muted">
                        MakedonIQ can become a friendly companion for Macedonian language classes, cultural days, community workshops, and at-home family learning.
                    </p>
                    <div class="mt-8">
                        <PrimaryButton href="/contact" variant="gold">Contact us</PrimaryButton>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
