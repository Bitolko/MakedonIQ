<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-10 py-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <section class="soft-card p-6 md:p-10">
                <p class="label">Join MakedonIQ</p>
                <h1 class="mt-3 text-4xl font-black text-heritage-red">Create Account</h1>
                <p class="mt-3 text-heritage-muted">Join our community of learners and heritage keepers.</p>

                <form class="mt-8 grid gap-5" @submit.prevent>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="label" for="name">Name</label>
                            <input id="name" class="field mt-2" type="text" placeholder="Nikola Petrovski" autocomplete="name">
                        </div>
                        <div>
                            <label class="label" for="preferred_language">Preferred language</label>
                            <select id="preferred_language" class="field mt-2">
                                <option>English</option>
                                <option>Македонски</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="label" for="register_email">Email</label>
                        <input id="register_email" class="field mt-2" type="email" placeholder="name@example.com" autocomplete="email">
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="label" for="register_password">Password</label>
                            <input id="register_password" class="field mt-2" type="password" placeholder="••••••••" autocomplete="new-password">
                        </div>
                        <div>
                            <label class="label" for="password_confirmation">Confirm password</label>
                            <input id="password_confirmation" class="field mt-2" type="password" placeholder="••••••••" autocomplete="new-password">
                        </div>
                    </div>
                    <PrimaryButton type="submit" class="w-full">Create Account</PrimaryButton>
                </form>

                <p class="mt-8 text-center text-sm text-heritage-muted">
                    Already have an account?
                    <a href="/login" class="font-black text-heritage-red">Login</a>
                </p>
            </section>

            <aside class="heritage-pattern rounded-[2rem] p-6 text-white shadow-soft">
                <div class="rounded-[1.5rem] bg-white p-6 text-heritage-ink">
                    <p class="label">Learning profile</p>
                    <h2 class="mt-3 text-3xl font-black text-heritage-ink">Built for families, schools, and community groups.</h2>
                    <div class="mt-6 grid gap-3">
                        <div class="rounded-2xl bg-heritage-panel p-4 font-bold text-heritage-muted">Bilingual quiz prompts</div>
                        <div class="rounded-2xl bg-heritage-panel p-4 font-bold text-heritage-muted">Progress and achievements</div>
                        <div class="rounded-2xl bg-heritage-panel p-4 font-bold text-heritage-muted">Culture-first learning topics</div>
                    </div>
                </div>
            </aside>
        </main>
    </PublicLayout>
</template>
