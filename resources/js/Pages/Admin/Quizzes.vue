<script setup>
import AdminLayout from '../../Components/AdminLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { adminQuizzes } from '../../data/makedoniq';
</script>

<template>
    <AdminLayout>
        <section class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <h1 class="text-4xl font-black text-heritage-ink">Admin Quizzes</h1>
                <p class="mt-2 text-heritage-muted">Static quiz management table for the future admin workflow.</p>
            </div>
            <PrimaryButton>Add quiz</PrimaryButton>
        </section>

        <section class="mb-5 grid gap-3 rounded-[1.5rem] bg-white p-4 shadow-card md:grid-cols-[1fr_auto_auto]">
            <input class="field" placeholder="Search quizzes..." type="search">
            <select class="field md:w-44">
                <option>All categories</option>
                <option>History</option>
                <option>Language</option>
                <option>Alphabet</option>
            </select>
            <select class="field md:w-40">
                <option>All status</option>
                <option>Published</option>
                <option>Draft</option>
            </select>
        </section>

        <section class="table-shell">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left">
                    <thead class="table-heading">
                        <tr>
                            <th class="px-6 py-4 font-black">Quiz title</th>
                            <th class="px-6 py-4 font-black">Category</th>
                            <th class="px-6 py-4 font-black">Difficulty</th>
                            <th class="px-6 py-4 font-black">Questions</th>
                            <th class="px-6 py-4 font-black">Status</th>
                            <th class="px-6 py-4 font-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="quiz in adminQuizzes" :key="quiz.title" class="border-t border-heritage-line/40">
                            <td class="px-6 py-4 font-bold text-heritage-ink">{{ quiz.title }}</td>
                            <td class="px-6 py-4 text-heritage-muted">{{ quiz.category }}</td>
                            <td class="px-6 py-4 text-heritage-muted">{{ quiz.difficulty }}</td>
                            <td class="px-6 py-4 text-heritage-muted">{{ quiz.questions }}</td>
                            <td class="px-6 py-4">
                                <span :class="['rounded-full px-3 py-1 text-xs font-black', quiz.status === 'Published' ? 'bg-emerald-50 text-emerald-800' : 'bg-heritage-panel text-heritage-muted']">{{ quiz.status }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button class="button-soft rounded-xl px-3 py-2 text-xs font-black" type="button">Edit</button>
                                    <button class="rounded-xl bg-heritage-red-faint px-3 py-2 text-xs font-black text-heritage-red hover:bg-heritage-red-soft" type="button">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>
