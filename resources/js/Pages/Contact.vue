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
</script>

<template>
    <PublicLayout>
        <main class="overflow-hidden">
            <section class="relative bg-heritage-bg py-10 md:py-14 lg:py-16">
                <div class="pointer-events-none absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 2px 2px, rgba(164, 0, 0, 0.12) 1px, transparent 0); background-size: 32px 32px;" />
                <div class="page-shell relative grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div class="min-w-0">
                        <AppBadge>Contact</AppBadge>
                        <h1 class="mt-5 max-w-3xl text-4xl font-black leading-tight text-heritage-ink md:text-5xl lg:text-6xl">Talk to MakedonIQ</h1>
                        <p class="mt-5 max-w-2xl text-base font-bold leading-8 text-heritage-muted md:text-lg">
                            Have a question, school idea, community suggestion, or feedback? Send us a message and help shape the future of Macedonian learning.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <PrimaryButton :href="contactMailto" size="lg">Email MakedonIQ</PrimaryButton>
                            <PrimaryButton href="/learn" variant="gold" size="lg">Explore lessons</PrimaryButton>
                        </div>
                    </div>

                    <div class="relative min-w-0">
                        <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-heritage-gold/25 blur-2xl" />
                        <div class="absolute -bottom-8 -left-5 h-32 w-32 rounded-full bg-heritage-red/15 blur-2xl" />
                        <div class="relative overflow-hidden rounded-[2rem] border border-heritage-gold/45 bg-heritage-navy p-4 text-white shadow-soft md:p-6">
                            <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/10 via-transparent to-heritage-red/25" />
                            <div class="pointer-events-none absolute inset-0 opacity-35" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.2) 1px, transparent 0); background-size: 26px 26px;" />

                            <div class="relative grid gap-4">
                                <div class="rounded-[1.5rem] border border-white/15 bg-white p-5 text-heritage-ink shadow-card">
                                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                        <div>
                                            <p class="label text-heritage-red">Community messages</p>
                                            <h2 class="mt-2 text-2xl font-black leading-tight">Families, schools, feedback, partnerships</h2>
                                        </div>
                                        <span class="inline-flex w-fit rounded-full bg-heritage-gold-faint px-4 py-2 text-sm font-black text-heritage-gold-deep shadow-card">EN/MK</span>
                                    </div>
                                    <div class="mt-5 grid gap-3">
                                        <div class="max-w-[18rem] rounded-[1.2rem] rounded-bl-sm bg-heritage-panel px-4 py-3 shadow-card">
                                            <p class="text-sm font-black text-heritage-ink">Can our family use this at home?</p>
                                        </div>
                                        <div class="ml-auto max-w-[19rem] rounded-[1.2rem] rounded-br-sm bg-heritage-gold px-4 py-3 shadow-card">
                                            <p class="text-sm font-black text-heritage-navy">Yes. Lessons and demos are built for simple practice.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <article class="rounded-[1.5rem] border border-white/15 bg-white/10 p-5">
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-black uppercase text-heritage-red">Families</span>
                                        <p class="mt-4 text-xl font-black">At-home learning questions</p>
                                        <p class="mt-2 text-sm leading-6 text-white/75">Ask about lessons, demos, and beginner-friendly practice.</p>
                                    </article>
                                    <article class="rounded-[1.5rem] border border-white/15 bg-white/10 p-5">
                                        <span class="rounded-full bg-heritage-gold px-3 py-1 text-xs font-black uppercase text-heritage-navy">Schools</span>
                                        <p class="mt-4 text-xl font-black">Community and classroom ideas</p>
                                        <p class="mt-2 text-sm leading-6 text-white/75">Share ideas for school use, teacher tools, and group learning.</p>
                                    </article>
                                </div>

                                <div class="grid gap-3 rounded-[1.5rem] border border-white/15 bg-white/10 p-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-2xl bg-white/90 px-4 py-3 text-sm font-black text-heritage-ink">Feedback</div>
                                    <div class="rounded-2xl bg-heritage-gold px-4 py-3 text-sm font-black text-heritage-navy">Content ideas</div>
                                    <div class="rounded-2xl bg-heritage-red px-4 py-3 text-sm font-black text-white">School use</div>
                                    <div class="rounded-2xl bg-white/90 px-4 py-3 text-sm font-black text-heritage-ink">Community</div>
                                </div>
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
