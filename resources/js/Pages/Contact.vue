<script setup>
import PublicLayout from '../Components/PublicLayout.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import { currentUser } from '../api/makedoniq';

const contactEmail = 'makedoniq2026@gmail.com';
const contactMailto = `mailto:${contactEmail}?subject=MakedonIQ%20contact`;
const feedbackMailto = `mailto:${contactEmail}?subject=MakedonIQ%20feedback`;
const user = currentUser();
const isGuest = !user;

const contactOptions = [
    {
        title: 'Email',
        value: contactEmail,
        detail: 'Best way to reach MakedonIQ while the public contact form is being prepared.',
        href: contactMailto,
        cta: 'Email MakedonIQ',
        icon: 'MAIL',
        tone: 'bg-heritage-red text-white',
    },
    {
        title: 'Based in',
        value: 'Australia, for learners worldwide',
        detail: 'Built for Macedonian families, learners, and communities wherever they are.',
        icon: 'AUS',
        tone: 'bg-heritage-gold text-heritage-navy',
    },
    {
        title: 'Best for',
        value: 'Families, Macedonian schools, community groups, and beginner learners',
        detail: 'Useful for at-home practice, school ideas, community feedback, and future lesson planning.',
        icon: 'GROUP',
        tone: 'bg-heritage-navy text-white',
    },
    {
        title: 'Response time',
        value: 'We will reply when available. This is currently an early demo project.',
        detail: 'Direct email is available now. Form sending will be added later.',
        icon: 'NOTE',
        tone: 'bg-heritage-panel text-heritage-red',
    },
];

const contactReasons = [
    'Parent / Family',
    'Macedonian school',
    'Community group',
    'Feedback',
    'Partnership',
    'Other',
];

const useCases = [
    {
        title: 'Family learning',
        detail: 'Questions about using MakedonIQ at home.',
        icon: 'HOME',
    },
    {
        title: 'School use',
        detail: 'Ideas for Macedonian school lessons, quizzes, and teacher tools.',
        icon: 'CLASS',
    },
    {
        title: 'Content ideas',
        detail: 'Suggestions for new lessons, songs, pictures, map questions, or cultural topics.',
        icon: 'IDEA',
    },
    {
        title: 'Feedback',
        detail: 'Tell us what is confusing, missing, or could be improved.',
        icon: 'CHAT',
    },
];

const heroTrustChips = [
    'Families',
    'Schools',
    'Feedback',
];

const heroContactRows = [
    {
        title: 'Families',
        detail: 'Home learning, lessons, and demo questions.',
        icon: 'home',
        tone: 'bg-heritage-red-faint text-heritage-red',
    },
    {
        title: 'Schools',
        detail: 'Classroom ideas and future teacher tools.',
        icon: 'book',
        tone: 'bg-heritage-gold-faint text-heritage-gold-deep',
    },
    {
        title: 'Feedback',
        detail: 'Suggestions for quizzes, maps, songs, and improvements.',
        icon: 'chat',
        tone: 'bg-heritage-navy-soft text-heritage-navy',
    },
];
</script>

<template>
    <PublicLayout>
        <main class="overflow-hidden">
            <section class="relative bg-heritage-bg py-10 md:py-14 lg:py-16">
                <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.12) 1px, transparent 0); background-size: 32px 32px;" />
                <div class="page-shell relative grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div class="min-w-0 py-2">
                        <AppBadge>Contact</AppBadge>
                        <h1 class="mt-5 max-w-3xl text-4xl font-black leading-tight text-heritage-ink md:text-5xl">Talk to MakedonIQ</h1>
                        <p class="mt-5 max-w-2xl text-base font-bold leading-8 text-heritage-muted md:text-lg">
                            Have a question, school idea, community suggestion, or feedback? Send us a message and help shape the future of Macedonian learning.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <PrimaryButton :href="contactMailto" size="lg">Email MakedonIQ</PrimaryButton>
                            <PrimaryButton href="/learn" variant="gold" size="lg">Explore lessons</PrimaryButton>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <span v-for="chip in heroTrustChips" :key="chip" class="rounded-full border border-heritage-gold/35 bg-white/80 px-3 py-1 text-xs font-black uppercase text-heritage-muted">
                                {{ chip }}
                            </span>
                        </div>
                    </div>

                    <div class="relative min-w-0">
                        <div class="relative overflow-hidden rounded-[2rem] border border-heritage-gold/35 bg-white/95 p-6 shadow-soft md:p-8">
                            <div class="pointer-events-none absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.10) 1px, transparent 0); background-size: 30px 30px;" />

                            <div class="relative">
                                <AppBadge variant="gold">Contact options</AppBadge>
                                <h2 class="mt-4 text-3xl font-black leading-tight text-heritage-ink">How we can help</h2>
                                <p class="mt-3 max-w-xl text-sm font-bold leading-7 text-heritage-muted md:text-base">
                                    Email us about family learning, school use, content ideas, or feedback.
                                </p>

                                <div class="mt-7 divide-y divide-heritage-line/60">
                                    <article
                                        v-for="row in heroContactRows"
                                        :key="row.title"
                                        class="group flex items-start gap-4 py-4 first:pt-0 last:pb-0"
                                    >
                                        <span :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl shadow-inner transition group-hover:scale-105', row.tone]">
                                            <span v-if="row.icon === 'home'" class="relative h-6 w-6">
                                                <span class="absolute left-1 top-3 h-3.5 w-4 rounded-b bg-current opacity-70" />
                                                <span class="absolute left-0 top-2 h-3 w-6 rotate-45 rounded-sm bg-current opacity-35" />
                                                <span class="absolute left-2.5 top-4 h-2.5 w-1.5 rounded-t bg-white/80" />
                                            </span>
                                            <span v-else-if="row.icon === 'book'" class="relative h-6 w-6">
                                                <span class="absolute left-1 top-1 h-5 w-4 rounded-lg bg-current opacity-35" />
                                                <span class="absolute right-1 top-1 h-5 w-4 rounded-lg bg-current opacity-65" />
                                                <span class="absolute left-3 top-2 h-4 w-px bg-white/80" />
                                            </span>
                                            <span v-else class="relative h-6 w-6">
                                                <span class="absolute inset-x-0 top-1 h-4 rounded-2xl bg-current opacity-55" />
                                                <span class="absolute bottom-0 left-3 h-3 w-3 rotate-45 rounded-sm bg-current opacity-55" />
                                                <span class="absolute left-2 top-3 h-1 w-2 rounded-full bg-white/80" />
                                                <span class="absolute right-2 top-3 h-1 w-2 rounded-full bg-white/80" />
                                            </span>
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="text-lg font-black text-heritage-ink">{{ row.title }}</h3>
                                            <p class="mt-1 text-sm font-bold leading-6 text-heritage-muted">{{ row.detail }}</p>
                                        </div>
                                    </article>
                                </div>

                                <p class="mt-7 border-t border-heritage-line/60 pt-4 text-sm font-bold leading-6 text-heritage-muted">
                                    Ready to share an idea?
                                    <a :href="contactMailto" class="font-black text-heritage-red transition hover:text-heritage-red-dark">Email MakedonIQ</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-shell grid gap-8 py-14 lg:grid-cols-[0.92fr_1.08fr] lg:items-start">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <article
                        v-for="option in contactOptions"
                        :key="option.title"
                        class="rounded-[1.5rem] border border-heritage-line/70 bg-white p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-gold/70 hover:shadow-soft"
                    >
                        <div class="flex items-start gap-4">
                            <span :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-xs font-black shadow-card', option.tone]">{{ option.icon }}</span>
                            <div class="min-w-0">
                                <p class="label">{{ option.title }}</p>
                                <p class="mt-2 break-words text-lg font-black leading-7 text-heritage-ink">{{ option.value }}</p>
                                <p class="mt-2 text-sm font-bold leading-6 text-heritage-muted">{{ option.detail }}</p>
                            </div>
                        </div>
                        <PrimaryButton v-if="option.href" :href="option.href" class="mt-5 w-full sm:w-auto" size="sm">{{ option.cta }}</PrimaryButton>
                    </article>
                </div>

                <section class="rounded-[2rem] border border-heritage-gold/45 bg-white p-5 shadow-soft md:p-8">
                    <div class="mb-6">
                        <AppBadge variant="red">Message preview</AppBadge>
                        <h2 class="mt-4 text-3xl font-black leading-tight text-heritage-ink">Tell us what you need</h2>
                        <p class="mt-3 text-sm font-bold leading-6 text-heritage-muted">
                            The form is being prepared. For now, email us directly and include your name, reason, and message.
                        </p>
                    </div>
                    <form class="grid gap-5" @submit.prevent>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="label" for="contact_name">Name</label>
                                <input id="contact_name" class="field mt-2 bg-white" type="text" placeholder="Your name">
                            </div>
                            <div>
                                <label class="label" for="contact_email">Email</label>
                                <input id="contact_email" class="field mt-2 bg-white" type="email" placeholder="name@example.com">
                            </div>
                        </div>
                        <div>
                            <label class="label" for="reason">Reason</label>
                            <select id="reason" class="field mt-2 bg-white">
                                <option v-for="reason in contactReasons" :key="reason">{{ reason }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="message">Message</label>
                            <textarea id="message" class="field mt-2 min-h-40 bg-white" placeholder="How can we help?"></textarea>
                        </div>
                        <div class="rounded-2xl border border-heritage-gold/35 bg-heritage-gold-faint px-4 py-3">
                            <p class="text-sm font-black leading-6 text-heritage-gold-deep">Direct email is available now. Form sending will be added later.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <PrimaryButton :href="contactMailto" size="lg">Email MakedonIQ</PrimaryButton>
                            <PrimaryButton type="submit" variant="soft" size="lg" disabled>Form sending soon</PrimaryButton>
                        </div>
                    </form>
                </section>
            </section>

            <section class="bg-white py-14 md:py-16">
                <div class="page-shell">
                    <div class="max-w-3xl">
                        <AppBadge variant="gold">Contact topics</AppBadge>
                        <h2 class="mt-4 text-3xl font-black text-heritage-ink md:text-4xl">What you can contact us about</h2>
                        <p class="mt-3 leading-7 text-heritage-muted">Send ideas, questions, and feedback that can help make MakedonIQ more useful for real learners and community groups.</p>
                    </div>
                    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <article
                            v-for="item in useCases"
                            :key="item.title"
                            class="rounded-[1.5rem] border border-heritage-line/70 bg-heritage-panel p-5 shadow-card transition hover:-translate-y-1 hover:border-heritage-red/25 hover:bg-white hover:shadow-soft"
                        >
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black uppercase text-heritage-red shadow-card">{{ item.icon }}</span>
                            <h3 class="mt-5 text-xl font-black text-heritage-ink">{{ item.title }}</h3>
                            <p class="mt-3 text-sm font-bold leading-6 text-heritage-muted">{{ item.detail }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="page-shell py-14 md:py-16">
                <div class="relative overflow-hidden rounded-[2rem] bg-heritage-red p-6 text-white shadow-soft md:p-10">
                    <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/10 via-transparent to-heritage-navy/30" />
                    <div class="relative grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <AppBadge variant="gold">{{ isGuest ? 'Community feedback' : 'Keep learning' }}</AppBadge>
                            <h2 class="mt-4 text-3xl font-black leading-tight md:text-4xl">
                                {{ isGuest ? 'Help build better Macedonian learning' : 'Continue your learning journey' }}
                            </h2>
                            <p class="mt-3 max-w-3xl leading-7 text-white/85">
                                {{ isGuest ? 'MakedonIQ is growing step by step. Your feedback can help shape lessons, quizzes, folklore content, map challenges, and future school features.' : 'Keep exploring lessons, practise with quizzes, and review your progress as MakedonIQ continues to grow.' }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                            <template v-if="isGuest">
                                <PrimaryButton :href="feedbackMailto" variant="gold" size="lg">Email feedback</PrimaryButton>
                                <PrimaryButton href="/quizzes" variant="white" size="lg">Try demo quizzes</PrimaryButton>
                            </template>
                            <template v-else>
                                <PrimaryButton href="/learn" variant="gold" size="lg">Continue learning</PrimaryButton>
                                <PrimaryButton href="/progress" variant="white" size="lg">View progress</PrimaryButton>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
