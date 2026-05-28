<script setup>
import AppNavbar from './AppNavbar.vue';
import AppFooter from './AppFooter.vue';
</script>

<template>
    <div class="min-h-screen bg-heritage-bg">
        <AppNavbar variant="public" />
        <slot />
        <AppFooter />
    </div>
</template>
