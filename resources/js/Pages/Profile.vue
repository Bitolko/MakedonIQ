<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '../Components/DashboardLayout.vue';
import PrimaryButton from '../Components/PrimaryButton.vue';
import AppBadge from '../Components/AppBadge.vue';
import { getMe, updatePassword, updateProfile } from '../api/makedoniq';

const loading = ref(true);
const profileSaving = ref(false);
const passwordSaving = ref(false);
const error = ref('');
const profileSuccess = ref('');
const passwordSuccess = ref('');
const user = ref(null);
const profileErrors = ref({});
const passwordErrors = ref({});

const profileForm = ref({
    name: '',
    email: '',
    preferred_language: 'en',
});

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const preferredLanguageLabel = computed(() => (
    user.value?.preferred_language === 'mk' ? 'Македонски' : 'English'
));

onMounted(async () => {
    try {
        const response = await getMe();
        setUser(response.data);
    } catch (caughtError) {
        error.value = caughtError.message || 'Unable to load your profile.';
    } finally {
        loading.value = false;
    }
});

async function saveProfile() {
    profileSaving.value = true;
    error.value = '';
    profileSuccess.value = '';
    profileErrors.value = {};

    try {
        const response = await updateProfile(profileForm.value);
        setUser(response.data);
        syncAuthPayload(response.data);
        profileSuccess.value = response.message || 'Profile updated.';
    } catch (caughtError) {
        profileErrors.value = caughtError.payload?.errors || {};
        error.value = caughtError.message || 'Unable to update profile.';
    } finally {
        profileSaving.value = false;
    }
}

async function savePassword() {
    passwordSaving.value = true;
    error.value = '';
    passwordSuccess.value = '';
    passwordErrors.value = {};

    try {
        const response = await updatePassword(passwordForm.value);
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };
        passwordSuccess.value = response.message || 'Password updated.';
    } catch (caughtError) {
        passwordErrors.value = caughtError.payload?.errors || {};
        error.value = caughtError.message || 'Unable to update password.';
    } finally {
        passwordSaving.value = false;
    }
}

function setUser(payload) {
    user.value = payload;
    profileForm.value = {
        name: payload.name || '',
        email: payload.email || '',
        preferred_language: payload.preferred_language || 'en',
    };
}

function syncAuthPayload(payload) {
    if (!window.MakedonIQ?.auth) {
        return;
    }

    window.MakedonIQ.auth.user = {
        ...(window.MakedonIQ.auth.user || {}),
        id: payload.id,
        name: payload.name,
        email: payload.email,
        preferred_language: payload.preferred_language,
        is_admin: payload.is_admin,
    };
    window.dispatchEvent(new CustomEvent('makedoniq:auth-user-updated', {
        detail: {
            user: window.MakedonIQ.auth.user,
        },
    }));
}

function fieldError(errors, field) {
    return errors?.[field]?.[0] || '';
}

function formatDate(value) {
    if (!value) {
        return 'Recently';
    }

    return new Intl.DateTimeFormat('en-AU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}
</script>

<template>
    <DashboardLayout>
        <section class="mb-8">
            <AppBadge variant="gold">Profile</AppBadge>
            <h1 class="mt-4 text-4xl font-black text-heritage-ink">Profile Settings</h1>
            <p class="mt-2 max-w-3xl text-heritage-muted">
                Keep your learner details current and choose your default quiz language.
            </p>
        </section>

        <article v-if="loading" class="section-panel">
            <AppBadge variant="navy">Loading</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Loading your profile</h2>
            <p class="mt-2 text-heritage-muted">Fetching your account settings.</p>
        </article>

        <article v-else-if="error && !user" class="section-panel">
            <AppBadge variant="red">Profile unavailable</AppBadge>
            <h2 class="mt-4 text-2xl font-black text-heritage-ink">Unable to load profile</h2>
            <p class="mt-2 text-heritage-muted">{{ error }}</p>
        </article>

        <template v-else>
            <div v-if="error" class="mb-5 rounded-2xl border border-heritage-red/20 bg-heritage-red-faint px-5 py-4 text-sm font-bold text-heritage-red-dark">
                {{ error }}
            </div>

            <section class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <aside class="soft-card p-6 md:p-8">
                    <div class="heritage-pattern rounded-[2rem] p-6 text-white">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-xl font-black text-heritage-red">
                            {{ user.name?.slice(0, 2).toUpperCase() || 'IQ' }}
                        </div>
                        <h2 class="mt-5 text-3xl font-black">{{ user.name }}</h2>
                        <p class="mt-2 font-semibold text-white/80">{{ user.email }}</p>
                    </div>

                    <div class="mt-6 grid gap-3">
                        <div class="rounded-2xl bg-heritage-panel p-4">
                            <p class="label">Preferred language</p>
                            <p class="mt-2 text-xl font-black text-heritage-ink">{{ preferredLanguageLabel }}</p>
                        </div>
                        <div class="rounded-2xl bg-heritage-panel p-4">
                            <p class="label">Member since</p>
                            <p class="mt-2 text-xl font-black text-heritage-ink">{{ formatDate(user.created_at) }}</p>
                        </div>
                        <div v-if="user.is_admin" class="rounded-2xl bg-heritage-gold-faint p-4">
                            <p class="label">Access</p>
                            <p class="mt-2 text-xl font-black text-heritage-gold-deep">Admin</p>
                        </div>
                    </div>
                </aside>

                <div class="grid gap-6">
                    <form class="section-panel grid gap-5" @submit.prevent="saveProfile">
                        <div>
                            <h2 class="text-2xl font-black text-heritage-ink">Profile details</h2>
                            <p class="mt-1 text-sm text-heritage-muted">Your admin status cannot be changed here.</p>
                        </div>

                        <div v-if="profileSuccess" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                            {{ profileSuccess }}
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="label">Name</span>
                                <input v-model="profileForm.name" class="field mt-2" type="text">
                                <span v-if="fieldError(profileErrors, 'name')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(profileErrors, 'name') }}</span>
                            </label>
                            <label class="block">
                                <span class="label">Email</span>
                                <input v-model="profileForm.email" class="field mt-2" type="email">
                                <span v-if="fieldError(profileErrors, 'email')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(profileErrors, 'email') }}</span>
                            </label>
                        </div>

                        <label class="block">
                            <span class="label">Preferred language</span>
                            <select v-model="profileForm.preferred_language" class="field mt-2 max-w-sm">
                                <option value="en">English</option>
                                <option value="mk">Македонски</option>
                            </select>
                            <span v-if="fieldError(profileErrors, 'preferred_language')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(profileErrors, 'preferred_language') }}</span>
                        </label>

                        <div class="rounded-2xl bg-heritage-panel p-4 text-sm font-semibold leading-7 text-heritage-muted">
                            Preferred language controls the default quiz display mode where bilingual content is available. You can still toggle language on quiz pages.
                        </div>

                        <PrimaryButton class="w-full sm:w-auto" type="submit" :disabled="profileSaving">
                            {{ profileSaving ? 'Saving...' : 'Save changes' }}
                        </PrimaryButton>
                    </form>

                    <form class="section-panel grid gap-5" @submit.prevent="savePassword">
                        <div>
                            <h2 class="text-2xl font-black text-heritage-ink">Update password</h2>
                            <p class="mt-1 text-sm text-heritage-muted">Enter your current password before choosing a new one.</p>
                        </div>

                        <div v-if="passwordSuccess" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                            {{ passwordSuccess }}
                        </div>

                        <label class="block">
                            <span class="label">Current password</span>
                            <input v-model="passwordForm.current_password" class="field mt-2" type="password" autocomplete="current-password">
                            <span v-if="fieldError(passwordErrors, 'current_password')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(passwordErrors, 'current_password') }}</span>
                        </label>

                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="label">New password</span>
                                <input v-model="passwordForm.password" class="field mt-2" type="password" autocomplete="new-password">
                                <span v-if="fieldError(passwordErrors, 'password')" class="mt-2 block text-xs font-bold text-heritage-red">{{ fieldError(passwordErrors, 'password') }}</span>
                            </label>
                            <label class="block">
                                <span class="label">Confirm new password</span>
                                <input v-model="passwordForm.password_confirmation" class="field mt-2" type="password" autocomplete="new-password">
                            </label>
                        </div>

                        <PrimaryButton class="w-full sm:w-auto" type="submit" :disabled="passwordSaving">
                            {{ passwordSaving ? 'Updating...' : 'Update password' }}
                        </PrimaryButton>
                    </form>
                </div>
            </section>
        </template>
    </DashboardLayout>
</template>
