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
        <main class="page-shell grid gap-10 py-12 lg:grid-cols-2 lg:items-center">
            <section class="soft-card order-2 overflow-hidden p-6 lg:order-1 md:p-10">
                <div class="rounded-[2rem] bg-heritage-panel p-6">
                    <p class="label">Continue learning</p>
                    <h1 class="mt-3 text-3xl font-black text-heritage-ink">Welcome back!</h1>
                    <p class="mt-3 leading-7 text-heritage-muted">Please enter your details to continue your Macedonian learning journey.</p>
                </div>

                <form class="mt-8 grid gap-5" action="/login" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken">
                    <div>
                        <label class="label" for="email">Email address</label>
                        <input id="email" class="field mt-2" type="email" name="email" :value="old.email || ''" placeholder="name@example.com" autocomplete="email" required>
                        <div v-if="errors.email" class="mt-2 rounded-2xl bg-heritage-red-faint px-4 py-3 text-sm font-bold text-heritage-red">
                            <p v-for="error in errors.email" :key="error">{{ error }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <label class="label" for="password">Password</label>
                            <button class="text-sm font-bold text-heritage-red" type="button">Forgot password?</button>
                        </div>
                        <input id="password" class="field mt-2" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                        <div v-if="errors.password" class="mt-2 rounded-2xl bg-heritage-red-faint px-4 py-3 text-sm font-bold text-heritage-red">
                            <p v-for="error in errors.password" :key="error">{{ error }}</p>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-semibold text-heritage-muted">
                        <input class="h-5 w-5 rounded border-heritage-line text-heritage-red" type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <PrimaryButton type="submit" class="w-full" size="lg">Login</PrimaryButton>
                    <div class="flex items-center gap-4">
                        <div class="h-px flex-1 bg-heritage-line" />
                        <span class="text-xs font-black text-heritage-muted">OR</span>
                        <div class="h-px flex-1 bg-heritage-line" />
                    </div>
                    <button class="button-ghost flex w-full items-center justify-center gap-3 rounded-2xl px-6 py-3 text-sm font-black" type="button">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-heritage-panel text-xs font-black text-heritage-red">G</span>
                        Continue with Google
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-heritage-muted">
                    New to MakedonIQ?
                    <a href="/register" class="font-black text-heritage-red">Create an account</a>
                </p>
            </section>

            <section class="order-1 lg:order-2">
                <div class="heritage-pattern rounded-[2rem] p-6 text-white shadow-soft">
                    <div class="rounded-[1.5rem] bg-white/10 p-6 backdrop-blur">
                        <p class="text-sm font-black uppercase">Master your heritage</p>
                        <h2 class="mt-4 text-4xl font-black leading-tight">Language, history, culture, and confidence.</h2>
                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white p-5 text-heritage-ink">
                                <p class="text-3xl font-black text-heritage-red">12</p>
                                <p class="mt-1 text-sm font-semibold text-heritage-muted">Completed quizzes</p>
                            </div>
                            <div class="rounded-2xl bg-white p-5 text-heritage-ink">
                                <p class="text-3xl font-black text-heritage-gold-deep">5</p>
                                <p class="mt-1 text-sm font-semibold text-heritage-muted">Day streak</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
