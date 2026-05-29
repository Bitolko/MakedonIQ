<script setup>
import PublicLayout from '../../Components/PublicLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';

const appState = window.MakedonIQ || {};
const csrfToken = appState.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const errors = appState.errors || {};
const old = appState.old || {};
</script>

<template>
    <PublicLayout>
        <main class="page-shell grid gap-10 py-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <section class="soft-card p-6 md:p-10">
                <p class="label">Join MakedonIQ</p>
                <h1 class="mt-3 text-4xl font-black text-heritage-red">Create Account</h1>
                <p class="mt-3 text-heritage-muted">Join our community of learners and heritage keepers.</p>

                <form class="mt-8 grid gap-5" action="/register" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="label" for="name">Name</label>
                            <input id="name" class="field mt-2" type="text" name="name" :value="old.name || ''" placeholder="Nikola Petrovski" autocomplete="name" required>
                            <div v-if="errors.name" class="mt-2 rounded-2xl bg-heritage-red-faint px-4 py-3 text-sm font-bold text-heritage-red">
                                <p v-for="error in errors.name" :key="error">{{ error }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="label" for="preferred_language">Preferred language</label>
                            <select id="preferred_language" class="field mt-2" name="preferred_language" :value="old.preferred_language || 'en'">
                                <option value="en">English</option>
                                <option value="mk">Македонски</option>
                            </select>
                            <p class="mt-2 text-xs font-semibold text-heritage-muted">Used as your default quiz language where bilingual content is available.</p>
                            <div v-if="errors.preferred_language" class="mt-2 rounded-2xl bg-heritage-red-faint px-4 py-3 text-sm font-bold text-heritage-red">
                                <p v-for="error in errors.preferred_language" :key="error">{{ error }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="label" for="register_email">Email</label>
                        <input id="register_email" class="field mt-2" type="email" name="email" :value="old.email || ''" placeholder="name@example.com" autocomplete="email" required>
                        <div v-if="errors.email" class="mt-2 rounded-2xl bg-heritage-red-faint px-4 py-3 text-sm font-bold text-heritage-red">
                            <p v-for="error in errors.email" :key="error">{{ error }}</p>
                        </div>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="label" for="register_password">Password</label>
                            <input id="register_password" class="field mt-2" type="password" name="password" placeholder="Password" autocomplete="new-password" required minlength="8">
                            <div v-if="errors.password" class="mt-2 rounded-2xl bg-heritage-red-faint px-4 py-3 text-sm font-bold text-heritage-red">
                                <p v-for="error in errors.password" :key="error">{{ error }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="label" for="password_confirmation">Confirm password</label>
                            <input id="password_confirmation" class="field mt-2" type="password" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password" required minlength="8">
                        </div>
                    </div>
                    <PrimaryButton type="submit" class="w-full" size="lg">Create Account</PrimaryButton>
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
