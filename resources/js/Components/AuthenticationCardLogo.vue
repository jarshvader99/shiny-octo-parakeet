<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'">
        <svg class="size-16" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Document background -->
            <rect x="15" y="10" width="60" height="80" rx="4" fill="white" stroke="#334155" stroke-width="4"/>
            <!-- Folded corner -->
            <path d="M 75 10 L 75 25 L 60 25 L 75 10 Z" fill="#6366f1" />
            <path d="M 75 10 L 75 25 L 60 25" fill="none" stroke="#334155" stroke-width="2" stroke-linejoin="round"/>
            <!-- Document lines -->
            <line x1="25" y1="35" x2="50" y2="35" stroke="#334155" stroke-width="3" stroke-linecap="round"/>
            <line x1="25" y1="45" x2="55" y2="45" stroke="#334155" stroke-width="3" stroke-linecap="round"/>
            <line x1="25" y1="55" x2="45" y2="55" stroke="#334155" stroke-width="3" stroke-linecap="round"/>
            <!-- Checkmark -->
            <path d="M 32 65 L 45 78 L 75 48" stroke="#6366f1" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
    </Link>
</template>
