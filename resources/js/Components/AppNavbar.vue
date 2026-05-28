<script setup>
import { computed, ref } from 'vue';
import PrimaryButton from './PrimaryButton.vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'public',
    },
});

const open = ref(false);
const currentPath = window.location.pathname;

const navItems = computed(() => {
    if (props.variant === 'admin') {
        return [
            { label: 'Admin Dashboard', href: '/admin' },
            { label: 'Quizzes', href: '/admin/quizzes' },
            { label: 'Questions', href: '/admin/questions' },
            { label: 'Users', href: '/admin', soon: true },
            { label: 'Results', href: '/admin', soon: true },
        ];
    }

    if (props.variant === 'dashboard') {
        return [
            { label: 'Dashboard', href: '/dashboard' },
            { label: 'Quizzes', href: '/quizzes' },
            { label: 'Progress', href: '/progress' },
            { label: 'Logout', href: '/' },
        ];
    }

    return [
        { label: 'Home', href: '/' },
        { label: 'Quizzes', href: '/quizzes' },
        { label: 'About', href: '/about' },
        { label: 'Contact', href: '/contact' },
    ];
});

const isActive = (href) => href !== '#' && (currentPath === href || (href !== '/' && currentPath.startsWith(href)));
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-heritage-line/40 bg-white/95 shadow-[0_1px_18px_rgba(78,77,100,0.05)] backdrop-blur">
        <div class="page-shell">
            <div class="flex min-h-20 items-center justify-between gap-3">
                <a :href="variant === 'admin' ? '/admin' : '/'" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-heritage-red text-base font-black text-white shadow-[0_4px_0_0_#760000]">IQ</span>
                    <span class="min-w-0">
                        <span class="block truncate text-xl font-black text-heritage-red sm:text-2xl">
                            {{ variant === 'admin' ? 'MakedonIQ Admin' : 'MakedonIQ' }}
                        </span>
                        <span v-if="variant === 'public'" class="hidden text-xs font-bold text-heritage-muted sm:block">Learn Macedonian through fun quizzes</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 rounded-full bg-heritage-panel p-1 md:flex">
                    <a
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'rounded-full px-4 py-2 text-sm font-black transition',
                            item.soon ? 'text-heritage-muted/70 hover:bg-white hover:text-heritage-muted' : '',
                            !item.soon && isActive(item.href) ? 'bg-white text-heritage-red shadow-card' : '',
                            !item.soon && !isActive(item.href) ? 'text-heritage-muted hover:bg-white hover:text-heritage-red' : '',
                        ]"
                    >
                        {{ item.label }}
                        <span v-if="item.soon" class="ml-1 text-xs font-black text-heritage-red">Soon</span>
                    </a>
                </nav>

                <div v-if="variant === 'public'" class="hidden items-center gap-3 md:flex">
                    <button class="rounded-full border border-heritage-line bg-white px-3 py-2 text-xs font-black text-heritage-muted shadow-card">EN / MK</button>
                    <a href="/login" class="rounded-full px-4 py-2 text-sm font-black text-heritage-muted hover:bg-heritage-panel hover:text-heritage-red">Login</a>
                    <PrimaryButton href="/register" size="sm">Start Learning</PrimaryButton>
                </div>

                <div v-else-if="variant === 'dashboard'" class="hidden items-center gap-3 md:flex">
                    <span class="rounded-full bg-heritage-gold-faint px-3 py-2 text-xs font-black text-heritage-gold-deep">5 day streak</span>
                    <span class="rounded-full bg-heritage-panel px-3 py-2 text-xs font-black text-heritage-muted">Stefan</span>
                </div>

                <div v-else class="hidden items-center gap-3 md:flex">
                    <span class="rounded-full bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-800">System online</span>
                </div>

                <button class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-red md:hidden" type="button" :aria-expanded="open" @click="open = !open">
                    {{ open ? 'Close' : 'Menu' }}
                </button>
            </div>

            <div v-if="open" class="grid gap-3 border-t border-heritage-line/40 py-4 md:hidden">
                <div v-if="variant === 'public'" class="grid grid-cols-2 gap-3">
                    <button class="rounded-2xl bg-white px-4 py-3 text-sm font-black text-heritage-muted shadow-card">EN / MK</button>
                    <a href="/login" class="rounded-2xl bg-white px-4 py-3 text-center text-sm font-black text-heritage-red shadow-card">Login</a>
                </div>
                <a
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'rounded-2xl px-4 py-3 text-sm font-black',
                        isActive(item.href) && !item.soon ? 'bg-heritage-red text-white' : 'bg-heritage-panel text-heritage-muted',
                    ]"
                >
                    {{ item.label }}
                    <span v-if="item.soon" class="ml-2 text-xs text-heritage-red">Coming soon</span>
                </a>
                <template v-if="variant === 'public'">
                    <a href="/register" class="rounded-2xl bg-heritage-red px-4 py-3 text-center text-sm font-black text-white">Start Learning</a>
                </template>
            </div>
        </div>
    </header>
</template>
