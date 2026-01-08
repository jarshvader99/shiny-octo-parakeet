<script setup>
</script>

<template>
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-teal-600 focus:text-white focus:rounded-lg focus:shadow-lg focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-950"
    >
        Skip to main content
    </a>
</template>

<style scoped>
/* Screen reader only class */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

/* Remove sr-only on focus */
.focus\:not-sr-only:focus {
    position: static;
    width: auto;
    height: auto;
    padding: revert;
    margin: revert;
    overflow: visible;
    clip: auto;
    white-space: normal;
}
</style>
