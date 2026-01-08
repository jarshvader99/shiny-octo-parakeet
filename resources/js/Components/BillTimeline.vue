<script setup>
import { computed } from 'vue'

const props = defineProps({
    bill: {
        type: Object,
        required: true,
    },
    events: {
        type: Array,
        default: () => [],
    },
})

// Get status icon and color
const getEventStyle = (eventType) => {
    const styles = {
        'introduced': { icon: 'M12 4v16m8-8H4', color: 'slate' },
        'committee': { icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', color: 'blue' },
        'floor': { icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', color: 'indigo' },
        'passed': { icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'emerald' },
        'failed': { icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'rose' },
        'signed': { icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'green' },
        'vetoed': { icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', color: 'red' },
    }
    return styles[eventType] || styles['introduced']
}

// Format date
const formatDate = (dateString) => {
    if (!dateString) return null
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

// Status color mapping
const statusColors = {
    'introduced': 'slate',
    'in_committee': 'blue',
    'on_floor': 'indigo',
    'passed_chamber': 'cyan',
    'passed_both': 'emerald',
    'to_president': 'amber',
    'became_law': 'green',
    'failed': 'rose',
    'vetoed': 'red',
}

const currentStatusColor = computed(() => {
    return statusColors[props.bill.status] || 'slate'
})
</script>

<template>
    <div class="mt-6 p-6 overflow-y-auto border bg-white border-stone-200 rounded-xl max-h-[560px]">
        <h3 class="mb-6 text-lg font-semibold text-slate-900">
            Bill Progress Timeline
        </h3>

        <!-- Current Status Badge -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="mb-1 text-sm text-slate-600">Current Status</div>
                <div :class="`inline-flex items-center gap-2 px-3 py-1 rounded-full bg-${currentStatusColor}-500/10 border border-${currentStatusColor}-500/20`">
                    <div :class="`w-2 h-2 rounded-full bg-${currentStatusColor}-500`"></div>
                    <span :class="`text-sm font-medium text-${currentStatusColor}-400 capitalize`">
                        {{ bill.status_label || bill.status?.replace(/_/g, ' ') || 'Unknown' }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="mb-1 text-sm text-slate-600">Last Updated</div>
                <div class="text-sm text-slate-700">
                    {{ formatDate(bill.updated_at) }}
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div v-if="events && events.length > 0" class="relative">
            <!-- Vertical line -->
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-stone-100"></div>

            <!-- Events -->
            <div class="space-y-6">
                <div
                    v-for="(event, index) in events"
                    :key="event.id || index"
                    class="relative pl-16"
                >
                    <!-- Icon -->
                    <div
                        :class="[
                            'absolute left-0 w-12 h-12 rounded-full flex items-center justify-center border-2',
                            `bg-${getEventStyle(event.event_type).color}-500/10`,
                            `border-${getEventStyle(event.event_type).color}-500/30`
                        ]"
                    >
                        <svg
                            :class="`w-6 h-6 text-${getEventStyle(event.event_type).color}-400`"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="getEventStyle(event.event_type).icon"
                            />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="p-4 rounded-lg bg-stone-100/50">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-medium text-slate-900">
                                {{ event.title || event.event_type?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Event' }}
                            </h4>
                            <span class="ml-4 text-xs text-slate-600 whitespace-nowrap">
                                {{ formatDate(event.occurred_at || event.created_at) }}
                            </span>
                        </div>
                        <p v-if="event.description" class="text-sm text-slate-600">
                            {{ event.description }}
                        </p>
                        <div v-if="event.source_url" class="mt-2">
                            <a
                                :href="event.source_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-xs text-teal-600 transition-colors hover:text-indigo-300"
                            >
                                View source →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-8 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-slate-600">No timeline events available</p>
        </div>

        <!-- Congress.gov Link -->
        <div class="pt-6 mt-6 border-t border-stone-200">
            <a
                :href="bill.congress_gov_url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-sm text-teal-600 transition-colors hover:text-indigo-300"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                View on Congress.gov
            </a>
        </div>
    </div>
</template>
