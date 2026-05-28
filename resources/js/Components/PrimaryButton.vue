<script setup>
import { computed } from 'vue';

const props = defineProps({
    href: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'red',
    },
    type: {
        type: String,
        default: 'button',
    },
    size: {
        type: String,
        default: 'md',
    },
});

const variantClasses = {
    red: 'pressable-red',
    gold: 'pressable-gold',
    ghost: 'button-ghost',
    soft: 'button-soft',
    white: 'bg-white text-heritage-red shadow-card border border-white/70 hover:bg-heritage-red-faint',
};

const sizeClasses = {
    sm: 'px-4 py-2 text-xs rounded-xl',
    md: 'px-6 py-3 text-sm rounded-2xl',
    lg: 'px-7 py-4 text-base rounded-2xl',
};

const classes = computed(() => [
    'inline-flex items-center justify-center gap-2 font-black transition',
    sizeClasses[props.size] || sizeClasses.md,
    variantClasses[props.variant] || variantClasses.red,
]);
</script>

<template>
    <a v-if="href" :href="href" :class="classes">
        <slot />
    </a>
    <button v-else :type="type" :class="classes">
        <slot />
    </button>
</template>
