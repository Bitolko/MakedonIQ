<script setup>
defineProps({
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
});
</script>

<template>
    <a
        v-if="href"
        :href="href"
        :class="[
            'inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-black',
            variant === 'gold' ? 'pressable-gold' : 'pressable-red',
        ]"
    >
        <slot />
    </a>
    <button
        v-else
        :type="type"
        :class="[
            'inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-black',
            variant === 'gold' ? 'pressable-gold' : 'pressable-red',
        ]"
    >
        <slot />
    </button>
</template>
