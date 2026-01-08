<script setup>
import { computed } from 'vue'

const props = defineProps({
    lastSynced: {
        type: String,
        required: true,
    },
    source: {
        type: String,
        default: 'Congress.gov',
    },
    sourceUrl: {
        type: String,
        default: null,
    },
    isStale: {
        type: Boolean,
        default: false,
    },
})

const formatSyncTime = computed(() => {
    const date = new Date(props.lastSynced)
    const now = new Date()
    const diffMs = now - date
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
    const diffDays = Math.floor(diffHours / 24)

    if (diffHours < 1) return 'less than an hour ago'
    if (diffHours === 1) return '1 hour ago'
    if (diffHours < 24) return `${diffHours} hours ago'`
    if (diffDays === 1) return '1 day ago'
    return `${diffDays} days ago`
})

const staleWarning = computed(() => {
    if (!props.isStale) return null
    return 'This data may be outdated. Re-sync in progress.'
})
</script>

<template>
    <div class="flex items-center gap-4 text-xs text-slate-600">
        <!-- Sync Status -->
        <div class="flex items-center gap-2">
            <svg
                class="w-3.5 h-3.5"
                :class="isStale ? 'text-amber-500' : 'text-emerald-500'"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>
                <span class="font-medium">Last updated:</span> {{ formatSyncTime }}
            </span>
        </div>

        <!-- Source Attribution -->
        <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>
                <span class="font-medium">Source:</span>
                <a
                    v-if="sourceUrl"
                    :href="sourceUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-teal-600 hover:text-indigo-300 transition-colors underline ml-1"
                >
                    {{ source }}
                </a>
                <span v-else class="ml-1">{{ source }}</span>
            </span>
        </div>

        <!-- Stale Warning -->
        <div v-if="staleWarning" class="flex items-center gap-2 text-amber-500">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>{{ staleWarning }}</span>
        </div>
    </div>
</template>
