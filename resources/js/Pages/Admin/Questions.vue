<script setup>
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
</script>

<template>
    <AdminLayout>
        <section class="mb-8">
            <h1 class="text-4xl font-black text-heritage-ink">Question Builder</h1>
            <p class="mt-2 text-heritage-muted">Static bilingual question form. Backend saving will come later.</p>
        </section>

        <form class="section-panel grid gap-6" @submit.prevent>
            <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <label class="label" for="quiz">Select quiz</label>
                    <select id="quiz" class="field mt-2">
                        <option>Ancient Macedonia Basics</option>
                        <option>Daily Macedonian Phrases</option>
                        <option>Cyrillic Letter Match</option>
                    </select>
                </div>
                <PrimaryButton type="submit" class="w-full lg:w-auto">Save question</PrimaryButton>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="question_en">Question in English</label>
                    <textarea id="question_en" class="field mt-2 min-h-28" placeholder="What does 'добро утро' mean?"></textarea>
                </div>
                <div>
                    <label class="label" for="question_mk">Question in Macedonian</label>
                    <textarea id="question_mk" class="field mt-2 min-h-28" placeholder="Што значи 'добро утро'?"></textarea>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-[1.5rem] bg-heritage-panel p-5">
                    <h2 class="text-xl font-black text-heritage-ink">Answer options in English</h2>
                    <div class="mt-4 grid gap-3">
                        <input class="field bg-white" placeholder="A. Good night">
                        <input class="field bg-white" placeholder="B. Good morning">
                        <input class="field bg-white" placeholder="C. Thank you">
                        <input class="field bg-white" placeholder="D. Goodbye">
                    </div>
                </section>
                <section class="rounded-[1.5rem] bg-heritage-panel p-5">
                    <h2 class="text-xl font-black text-heritage-ink">Answer options in Macedonian</h2>
                    <div class="mt-4 grid gap-3">
                        <input class="field bg-white" placeholder="A. Добра ноќ">
                        <input class="field bg-white" placeholder="B. Добро утро">
                        <input class="field bg-white" placeholder="C. Благодарам">
                        <input class="field bg-white" placeholder="D. Довидување">
                    </div>
                </section>
            </div>

            <div>
                <label class="label" for="correct">Select correct answer</label>
                <select id="correct" class="field mt-2">
                    <option>B</option>
                    <option>A</option>
                    <option>C</option>
                    <option>D</option>
                </select>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="explanation_en">Explanation in English</label>
                    <textarea id="explanation_en" class="field mt-2 min-h-28" placeholder="Добро утро means Good morning."></textarea>
                </div>
                <div>
                    <label class="label" for="explanation_mk">Explanation in Macedonian</label>
                    <textarea id="explanation_mk" class="field mt-2 min-h-28" placeholder="Добро утро значи Good morning."></textarea>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
