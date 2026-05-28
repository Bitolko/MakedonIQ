<script setup>
import AppNavbar from './AppNavbar.vue';
</script>

<template>
    <div class="min-h-screen bg-heritage-bg">
        <AppNavbar variant="admin" />
        <main class="page-shell py-8 md:py-12">
            <slot />
        </main>
    </div>
</template>
