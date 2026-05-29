<script setup>
import PublicLayout from '../Components/PublicLayout.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-10 py-14 lg:grid-cols-[0.9fr_1.1fr]">
            <section>
                <AppBadge>Contact</AppBadge>
                <h1 class="mt-5 text-4xl font-black text-heritage-ink md:text-5xl">Talk to MakedonIQ</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-heritage-muted">
                    Send a message about family learning, school use, community groups, or future quiz ideas.
                </p>
                <div class="mt-6 rounded-2xl border border-heritage-gold/40 bg-heritage-gold-faint p-4 text-sm font-bold leading-6 text-heritage-gold-deep">
                    The demo contact form is a preview. For now, email the team directly and we will respond from there.
                </div>
                <div class="mt-8 grid gap-4">
                    <div class="soft-card p-5">
                        <p class="label">Email</p>
                        <p class="mt-2 font-black text-heritage-ink">makedoniq2026@gmail.com</p>
                    </div>
                    <div class="soft-card p-5">
                        <p class="label">Based in</p>
                        <p class="mt-2 font-black text-heritage-ink">Australia, for learners worldwide</p>
                    </div>
                </div>
            </section>

            <section class="section-panel">
                <div class="mb-6">
                    <p class="label">Message preview</p>
                    <h2 class="mt-2 text-2xl font-black text-heritage-ink">Tell us what you need</h2>
                    <p class="mt-2 text-sm leading-6 text-heritage-muted">These fields show the planned contact experience for the public demo.</p>
                </div>
                <form class="grid gap-5" @submit.prevent>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="label" for="contact_name">Name</label>
                            <input id="contact_name" class="field mt-2" type="text" placeholder="Your name">
                        </div>
                        <div>
                            <label class="label" for="contact_email">Email</label>
                            <input id="contact_email" class="field mt-2" type="email" placeholder="name@example.com">
                        </div>
                    </div>
                    <div>
                        <label class="label" for="reason">Reason</label>
                        <select id="reason" class="field mt-2">
                            <option>Parent</option>
                            <option>Student</option>
                            <option>School</option>
                            <option>Community Group</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="message">Message</label>
                        <textarea id="message" class="field mt-2 min-h-40" placeholder="How can we help?"></textarea>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <PrimaryButton href="mailto:makedoniq2026@gmail.com" size="lg">Email MakedonIQ</PrimaryButton>
                        <PrimaryButton type="submit" variant="soft" size="lg" disabled>Form sending soon</PrimaryButton>
                    </div>
                </form>
            </section>
        </main>
    </PublicLayout>
</template>
