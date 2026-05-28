<script setup>
import AdminLayout from '../../Components/AdminLayout.vue';
import StatCard from '../../Components/StatCard.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { adminActivity, adminQuizzes } from '../../data/makedoniq';

const stats = [
    { label: 'Users', value: '1,284', detail: '+42 this month', icon: 'U', tone: 'red' },
    { label: 'Quizzes', value: '52', detail: '38 published', icon: 'Q', tone: 'gold' },
    { label: 'Questions', value: '640', detail: 'EN and MK prompts', icon: '?', tone: 'navy' },
    { label: 'Completed attempts', value: '8,910', detail: '+312 this week', icon: '✓', tone: 'red' },
];
</script>

<template>
    <AdminLayout>
        <section class="mb-8 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <h1 class="text-4xl font-black text-heritage-ink">Dashboard Overview</h1>
                <p class="mt-2 text-heritage-muted">Static admin UI preview for managing the future quiz system.</p>
            </div>
            <PrimaryButton href="/admin/quizzes">Add Quiz</PrimaryButton>
        </section>

        <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <StatCard v-for="stat in stats" :key="stat.label" :stat="stat" />
        </section>

        <section class="mt-10 grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
            <article class="soft-card p-6">
                <h2 class="text-2xl font-black text-heritage-ink">Quick actions</h2>
                <div class="mt-5 grid gap-3">
                    <a href="/admin/quizzes" class="rounded-2xl bg-heritage-red px-5 py-4 font-black text-white">Add Quiz</a>
                    <a href="#" class="rounded-2xl bg-heritage-panel px-5 py-4 font-black text-heritage-muted">Add Category</a>
                    <a href="/admin/questions" class="rounded-2xl bg-heritage-gold-soft px-5 py-4 font-black text-heritage-gold-deep">Add Question</a>
                </div>
                <h2 class="mt-8 text-2xl font-black text-heritage-ink">Recent activity</h2>
                <div class="mt-5 grid gap-3">
                    <p v-for="activity in adminActivity" :key="activity" class="rounded-2xl bg-heritage-panel p-4 text-sm font-semibold text-heritage-muted">{{ activity }}</p>
                </div>
            </article>

            <article class="soft-card overflow-hidden">
                <div class="border-b border-heritage-line/50 p-6">
                    <h2 class="text-2xl font-black text-heritage-ink">Table preview</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left">
                        <thead class="bg-heritage-panel text-sm text-heritage-muted">
                            <tr>
                                <th class="px-6 py-4 font-black">Quiz</th>
                                <th class="px-6 py-4 font-black">Category</th>
                                <th class="px-6 py-4 font-black">Questions</th>
                                <th class="px-6 py-4 font-black">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="quiz in adminQuizzes.slice(0, 4)" :key="quiz.title" class="border-t border-heritage-line/40">
                                <td class="px-6 py-4 font-bold text-heritage-ink">{{ quiz.title }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ quiz.category }}</td>
                                <td class="px-6 py-4 text-heritage-muted">{{ quiz.questions }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-heritage-panel px-3 py-1 text-xs font-black text-heritage-muted">{{ quiz.status }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </AdminLayout>
</template>
