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
            { label: 'Users', href: '#' },
            { label: 'Results', href: '#' },
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
    <header class="sticky top-0 z-50 border-b border-heritage-line/40 bg-white/95 backdrop-blur">
        <div class="page-shell">
            <div class="flex min-h-20 items-center justify-between gap-4">
                <a :href="variant === 'admin' ? '/admin' : '/'" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-heritage-red text-lg font-black text-white shadow-[0_4px_0_0_#760000]">IQ</span>
                    <span class="text-2xl font-black text-heritage-red">
                        {{ variant === 'admin' ? 'MakedonIQ Admin' : 'MakedonIQ' }}
                    </span>
                </a>

                <nav class="hidden items-center gap-7 md:flex">
                    <a
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'text-sm font-bold transition',
                            isActive(item.href) ? 'text-heritage-red' : 'text-heritage-muted hover:text-heritage-red',
                        ]"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div v-if="variant === 'public'" class="hidden items-center gap-3 md:flex">
                    <button class="rounded-full bg-heritage-panel px-3 py-2 text-xs font-black text-heritage-muted">EN / MK</button>
                    <a href="/login" class="text-sm font-black text-heritage-muted hover:text-heritage-red">Login</a>
                    <PrimaryButton href="/register">Start Learning</PrimaryButton>
                </div>

                <button class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-black text-heritage-red md:hidden" type="button" @click="open = !open">
                    Menu
                </button>
            </div>

            <div v-if="open" class="grid gap-3 border-t border-heritage-line/40 py-4 md:hidden">
                <a
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-bold text-heritage-muted"
                >
                    {{ item.label }}
                </a>
                <template v-if="variant === 'public'">
                    <a href="/login" class="rounded-2xl bg-heritage-panel px-4 py-3 text-sm font-bold text-heritage-muted">Login</a>
                    <a href="/register" class="rounded-2xl bg-heritage-red px-4 py-3 text-center text-sm font-black text-white">Start Learning</a>
                </template>
            </div>
        </div>
    </header>
</template>
