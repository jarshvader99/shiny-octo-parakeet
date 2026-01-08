<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

defineProps({
    guidelines: String,
});
</script>

<template>
    <Head title="Community Guidelines" />

    <div class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="pt-4 bg-gray-100 dark:bg-gray-900">
            <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 pb-12">
                <div>
                    <AuthenticationCardLogo />
                </div>

                <div 
                    class="w-full sm:max-w-3xl mt-6 p-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg prose dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-a:text-teal-600 dark:prose-a:text-teal-400" 
                    v-html="guidelines" 
                />
            </div>
        </div>
    </div>
</template>
