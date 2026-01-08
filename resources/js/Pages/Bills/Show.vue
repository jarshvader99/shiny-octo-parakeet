<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StanceForm from '@/Components/StanceForm.vue'
import StanceDisplay from '@/Components/StanceDisplay.vue'
import DiscussionThread from '@/Components/DiscussionThread.vue'
import ConsensusChart from '@/Components/ConsensusChart.vue'
import BillTimeline from '@/Components/BillTimeline.vue'
import ConsensusTrend from '@/Components/ConsensusTrend.vue'
import ConsensusHeatMap from '@/Components/ConsensusHeatMap.vue'
import BillFollowButton from '@/Components/BillFollowButton.vue'

const props = defineProps({
    bill: Object,
})

const showStanceForm = ref(false)
const activeDiscussionTab = ref('key_questions')

const toggleStanceForm = () => {
    showStanceForm.value = !showStanceForm.value
}

const handleStanceSubmitted = () => {
    showStanceForm.value = false
}

const discussionSections = [
    { key: 'key_questions', label: 'Key Questions', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'arguments_for', label: 'Arguments For', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'arguments_against', label: 'Arguments Against', icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'impact_analysis', label: 'Impact Analysis', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { key: 'general', label: 'General Discussion', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' },
]

const getDiscussion = (section) => {
    return props.bill.discussions?.[section] || null
}

const getOrCreateDiscussion = async (section) => {
    if (!getDiscussion(section)) {
        // Create discussion and reload page data
        router.get(route('bills.discussions.get', { bill: props.bill.id, section }), {}, {
            preserveState: true,
            preserveScroll: true,
            only: ['bill'],
        })
    }
}

// Format date to relative time or absolute
const formatDate = (dateString) => {
    if (!dateString) return null
    const date = new Date(dateString)
    const now = new Date()
    const diffMs = now - date
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))

    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return 'Yesterday'
    if (diffDays < 7) return `${diffDays} days ago`
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`

    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

// Format hours to human-readable time
const formatHoursAgo = (hours) => {
    if (hours === null || hours === undefined) return 'never'

    const minutes = hours * 60
    const seconds = minutes * 60

    if (seconds < 60) return 'just now'
    if (minutes < 1) return 'less than a minute ago'
    if (minutes < 60) return `${Math.floor(minutes)} ${Math.floor(minutes) === 1 ? 'minute' : 'minutes'} ago`
    if (hours < 24) return `${Math.floor(hours)} ${Math.floor(hours) === 1 ? 'hour' : 'hours'} ago`

    const days = Math.floor(hours / 24)
    if (days < 7) return `${days} ${days === 1 ? 'day' : 'days'} ago`

    const weeks = Math.floor(days / 7)
    if (weeks < 4) return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`

    const months = Math.floor(days / 30)
    return `${months} ${months === 1 ? 'month' : 'months'} ago`
}

// Get status badge color
const getStatusColor = (status) => {
    const colors = {
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
    return colors[status] || 'slate'
}

const statusColor = getStatusColor(props.bill.status)
</script>

<template>
    <AppLayout :title="bill.title">
        <Head :title="bill.title" />

        <div class="py-12">
            <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
                <!-- Back Link -->
                <div class="my-8">
                    <Link
                        :href="route('bills.index')"
                        class="inline-flex items-center gap-2 transition-colors text-slate-600 hover:text-teal-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to all bills
                    </Link>
                </div>

                <!-- Bill Header -->
                <div class="p-8 mb-6 border bg-white border-stone-200 rounded-xl">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-4 py-2 text-lg font-medium rounded-lg bg-stone-100 text-slate-900">
                                {{ bill.identifier }}
                            </span>

                            <span
                                v-if="bill.is_locally_relevant"
                                class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md bg-emerald-500/10 text-emerald-700"
                            >
                                Local to your district
                            </span>
                        </div>

                        <span :class="[
                            'inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium',
                            `bg-${statusColor}-500/10 text-${statusColor}-400`
                        ]">
                            {{ bill.status_display }}
                        </span>
                    </div>

                    <h1 class="mb-4 text-3xl font-semibold leading-relaxed text-slate-900">
                        {{ bill.title }}
                    </h1>

                    <div v-if="bill.summary" v-html="bill.summary" class="mb-6 text-lg leading-relaxed text-slate-700 [&_p]:mb-4 [&_p:last-child]:mb-0">
                    </div>

                    <!-- Meta Information -->
                    <div class="grid grid-cols-1 gap-4 pt-6 border-t md:grid-cols-2 border-stone-200">
                        <div>
                            <span class="block mb-1 text-sm text-slate-600">Congress</span>
                            <span class="text-slate-900">{{ bill.congress_number }}th Congress</span>
                        </div>
                        <div>
                            <span class="block mb-1 text-sm text-slate-600">Chamber</span>
                            <span class="capitalize text-slate-900">{{ bill.chamber }}</span>
                        </div>
                        <div v-if="bill.introduced_at">
                            <span class="block mb-1 text-sm text-slate-600">Introduced</span>
                            <span class="text-slate-900">{{ formatDate(bill.introduced_at) }}</span>
                        </div>
                        <div v-if="bill.last_action_at">
                            <span class="block mb-1 text-sm text-slate-600">Last Action</span>
                            <span class="text-slate-900">{{ formatDate(bill.last_action_at) }}</span>
                        </div>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="flex items-center justify-between pt-6 mt-6 text-sm border-t border-stone-200 text-slate-600">
                        <div class="flex items-center gap-4">
                            <span>Data synced {{ formatDate(bill.last_synced_at) }}</span>
                            <BillFollowButton
                                :bill-id="bill.id"
                                :is-following="bill.is_following"
                                :follower-count="bill.follower_count"
                            />
                        </div>
                        <a
                            v-if="bill.congress_gov_url"
                            :href="bill.congress_gov_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-teal-600 transition-colors hover:text-indigo-300"
                        >
                            View on Congress.gov
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- User Stance Section -->
                <div v-if="bill.user_stance && !showStanceForm" class="mb-6">
                    <StanceDisplay
                        :stance="bill.user_stance"
                        :bill-id="bill.id"
                        :show-outdated-warning="bill.user_stance.is_outdated"
                        @edit="toggleStanceForm"
                    />
                </div>

                <!-- Stance Form -->
                <div v-if="showStanceForm || (!bill.user_stance && !showStanceForm)" class="mb-6">
                    <StanceForm
                        v-if="showStanceForm || !bill.user_stance"
                        :bill-id="bill.id"
                        :existing-stance="bill.user_stance"
                        @submitted="handleStanceSubmitted"
                        @cancelled="toggleStanceForm"
                    />


                </div>

                <!-- Consensus Metrics -->
                <div v-if="bill.consensus && bill.consensus.total > 0" class="p-6 mb-6 border bg-white border-stone-200 rounded-xl">
                    <h2 class="mb-4 text-xl font-semibold text-slate-900">
                        Public Consensus
                    </h2>

                    <p class="mb-6 text-sm text-slate-600">
                        Based on {{ bill.consensus.total }} {{ bill.consensus.total === 1 ? 'stance' : 'stances' }} from verified constituents
                    </p>

                    <div class="space-y-3">
                        <div v-if="bill.consensus.breakdown.support > 0" class="flex items-center gap-4">
                            <div class="w-24 text-sm text-slate-700">Support</div>
                            <div class="flex-1 h-8 overflow-hidden rounded-lg bg-stone-100">
                                <div
                                    class="flex items-center justify-end h-full px-3 text-sm font-medium bg-emerald-500/60 text-slate-900"
                                    :style="{ width: bill.consensus.percentages.support + '%' }"
                                >
                                    {{ bill.consensus.percentages.support }}%
                                </div>
                            </div>
                            <div class="w-16 text-sm text-right text-slate-600">
                                {{ bill.consensus.breakdown.support }}
                            </div>
                        </div>

                        <div v-if="bill.consensus.breakdown.oppose > 0" class="flex items-center gap-4">
                            <div class="w-24 text-sm text-slate-700">Oppose</div>
                            <div class="flex-1 h-8 overflow-hidden rounded-lg bg-stone-100">
                                <div
                                    class="flex items-center justify-end h-full px-3 text-sm font-medium bg-rose-500/60 text-slate-900"
                                    :style="{ width: bill.consensus.percentages.oppose + '%' }"
                                >
                                    {{ bill.consensus.percentages.oppose }}%
                                </div>
                            </div>
                            <div class="w-16 text-sm text-right text-slate-600">
                                {{ bill.consensus.breakdown.oppose }}
                            </div>
                        </div>

                        <div v-if="bill.consensus.breakdown.mixed > 0" class="flex items-center gap-4">
                            <div class="w-24 text-sm text-slate-700">Mixed</div>
                            <div class="flex-1 h-8 overflow-hidden rounded-lg bg-stone-100">
                                <div
                                    class="flex items-center justify-end h-full px-3 text-sm font-medium bg-amber-500/60 text-slate-900"
                                    :style="{ width: bill.consensus.percentages.mixed + '%' }"
                                >
                                    {{ bill.consensus.percentages.mixed }}%
                                </div>
                            </div>
                            <div class="w-16 text-sm text-right text-slate-600">
                                {{ bill.consensus.breakdown.mixed }}
                            </div>
                        </div>

                        <div v-if="bill.consensus.breakdown.undecided > 0" class="flex items-center gap-4">
                            <div class="w-24 text-sm text-slate-700">Undecided</div>
                            <div class="flex-1 h-8 overflow-hidden rounded-lg bg-stone-100">
                                <div
                                    class="flex items-center justify-end h-full px-3 text-sm font-medium bg-slate-500/60 text-slate-900"
                                    :style="{ width: bill.consensus.percentages.undecided + '%' }"
                                >
                                    {{ bill.consensus.percentages.undecided }}%
                                </div>
                            </div>
                            <div class="w-16 text-sm text-right text-slate-600">
                                {{ bill.consensus.breakdown.undecided }}
                            </div>
                        </div>

                        <div v-if="bill.consensus.breakdown.needs_more_info > 0" class="flex items-center gap-4">
                            <div class="w-24 text-sm text-slate-700">Need Info</div>
                            <div class="flex-1 h-8 overflow-hidden rounded-lg bg-stone-100">
                                <div
                                    class="flex items-center justify-end h-full px-3 text-sm font-medium bg-blue-500/60 text-slate-900"
                                    :style="{ width: bill.consensus.percentages.needs_more_info + '%' }"
                                >
                                    {{ bill.consensus.percentages.needs_more_info }}%
                                </div>
                            </div>
                            <div class="w-16 text-sm text-right text-slate-600">
                                {{ bill.consensus.breakdown.needs_more_info }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sponsor & Cosponsors -->
                <div class="p-6 mb-6 border bg-white border-stone-200 rounded-xl">
                    <h2 class="mb-4 text-xl font-semibold text-slate-900">
                        Sponsors
                    </h2>

                    <div v-if="bill.sponsor" class="mb-4">
                        <span class="block mb-2 text-sm text-slate-600">Primary Sponsor</span>
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-slate-900">{{ bill.sponsor.name }}</span>
                            <span v-if="bill.sponsor.party" class="text-slate-600">
                                {{ bill.sponsor.party }}
                            </span>
                            <span v-if="bill.sponsor.state" class="text-slate-600">
                                {{ bill.sponsor.state }}<span v-if="bill.sponsor.district">-{{ bill.sponsor.district }}</span>
                            </span>
                        </div>
                    </div>

                    <div v-if="bill.cosponsors && bill.cosponsors.length > 0" class="pt-4 border-t border-stone-200">
                        <span class="block mb-3 text-sm text-slate-600">
                            Cosponsors ({{ bill.cosponsors.length }})
                        </span>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div
                                v-for="cosponsor in bill.cosponsors.slice(0, 10)"
                                :key="cosponsor.id"
                                class="flex items-center gap-2 text-sm"
                            >
                                <span class="text-slate-700">{{ cosponsor.name }}</span>
                                <span class="text-slate-600">
                                    ({{ cosponsor.party }}-{{ cosponsor.state }})
                                </span>
                            </div>
                        </div>
                        <div v-if="bill.cosponsors.length > 10" class="mt-3 text-sm text-slate-600">
                            + {{ bill.cosponsors.length - 10 }} more cosponsors
                        </div>
                    </div>
                </div>

                <!-- Committees -->
                <div v-if="bill.committees && bill.committees.length > 0" class="p-6 mb-6 border bg-white border-stone-200 rounded-xl">
                    <h2 class="mb-4 text-xl font-semibold text-slate-900">
                        Committees
                    </h2>
                    <div class="space-y-2">
                        <div
                            v-for="committee in bill.committees"
                            :key="committee.id"
                            class="flex items-center gap-3 text-slate-700"
                        >
                            <svg class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ committee.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Events Timeline -->
                <div v-if="bill.events && bill.events.length > 0" class="p-6 mb-6 border bg-white border-stone-200 rounded-xl">
                    <h2 class="mb-6 text-xl font-semibold text-slate-900">
                        Legislative Timeline
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="event in bill.events"
                            :key="event.id"
                            class="flex gap-4"
                        >
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 bg-teal-500 rounded-full"></div>
                                <div class="w-px h-full mt-2 bg-stone-100"></div>
                            </div>
                            <div class="flex-1 pb-6">
                                <div class="mb-1 text-sm text-slate-600">
                                    {{ formatDate(event.occurred_at) }}
                                </div>
                                <div class="mb-1 font-medium capitalize text-slate-900">
                                    {{ event.event_type ? event.event_type.replace('_', ' ') : 'Event' }}
                                </div>
                                <div v-if="event.description" class="text-sm text-slate-700">
                                    {{ event.description }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Latest Version -->
                <div v-if="bill.latest_version" class="p-6 border bg-white border-stone-200 rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-slate-900">
                            Bill Text
                        </h2>
                        <span class="text-sm text-slate-600">
                            Version: {{ bill.latest_version.version_code }}
                        </span>
                    </div>

                    <div v-if="bill.latest_version.full_text" class="prose prose-invert max-w-none">
                        <div class="font-serif leading-relaxed whitespace-pre-wrap text-slate-700">
                            {{ bill.latest_version.full_text }}
                        </div>
                    </div>

                    <div v-else class="py-8 text-center">
                        <p class="mb-4 text-slate-600">
                            Full text not yet available in our database
                        </p>
                        <a
                            v-if="bill.congress_gov_url"
                            :href="bill.congress_gov_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 text-white transition-colors bg-teal-600 rounded-lg hover:bg-teal-500"
                        >
                            Read on Congress.gov
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Consensus Metrics & Visualizations -->
                <div v-if="bill.consensus_metrics" class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
                    <!-- Consensus Chart -->
                    <ConsensusChart
                        :metrics="bill.consensus_metrics"
                        title="Public Consensus"
                    />

                    <!-- Bill Timeline -->
                    <BillTimeline
                        :bill="bill"
                        :events="bill.events || []"
                    />
                </div>

                <!-- Consensus Trend -->
                <div v-if="bill.consensus_metrics && bill.consensus_metrics.trends && bill.consensus_metrics.trends.length > 1" class="mb-6">
                    <ConsensusTrend
                        :trends="bill.consensus_metrics.trends"
                    />
                </div>

                <!-- Geographic Heat Map -->
                <div class="mb-6">
                    <ConsensusHeatMap
                        v-if="bill.geographic_data && Object.keys(bill.geographic_data).length > 0"
                        :bill-id="bill.id"
                        :geographic-data="bill.geographic_data"
                    />
                    <div v-else class="p-6 border bg-white border-stone-200 rounded-xl">
                        <h3 class="mb-4 text-lg font-semibold text-slate-900">
                            Regional Consensus
                        </h3>
                        <div class="py-12 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <p class="mb-2 text-sm text-slate-600">Not enough geographic data yet</p>
                            <p class="text-xs text-slate-600">Regional consensus map will appear when at least 5 responses are collected per region</p>
                        </div>
                    </div>
                </div>

                <!-- Data Freshness Indicator -->
                <div v-if="bill.consensus_metrics && bill.consensus_metrics.freshness" class="p-4 mb-6 border bg-white border-stone-200 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'w-3 h-3 rounded-full',
                                bill.consensus_metrics.freshness.is_stale ? 'bg-amber-500' : 'bg-emerald-500'
                            ]"></div>
                            <div>
                                <div class="text-sm font-medium text-slate-900">
                                    {{ bill.consensus_metrics.freshness.is_stale ? 'Data may be outdated' : 'Data is current' }}
                                </div>
                                <div class="text-xs text-slate-600">
                                    Last stance submitted {{ formatHoursAgo(bill.consensus_metrics.freshness.hours_since_last) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-slate-600">
                            {{ bill.consensus_metrics.geographic.unique_zip_codes }} unique locations
                        </div>
                    </div>
                </div>

                <!-- Discussion Tabs -->
                <div class="p-6 border bg-white border-stone-200 rounded-xl">
                    <h2 class="mb-6 text-xl font-semibold text-slate-900">
                        Discussion
                    </h2>

                    <!-- Tab Navigation -->
                    <div class="flex items-center gap-2 mb-6 overflow-x-auto">
                        <button
                            v-for="section in discussionSections"
                            :key="section.key"
                            @click="activeDiscussionTab = section.key; getOrCreateDiscussion(section.key)"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                                activeDiscussionTab === section.key
                                    ? 'bg-teal-600 text-white'
                                    : 'bg-stone-100 text-slate-700 hover:bg-amber-100'
                            ]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="section.icon" />
                            </svg>
                            <span>{{ section.label }}</span>
                            <span
                                v-if="getDiscussion(section.key)?.comment_count > 0"
                                class="px-2 py-0.5 rounded-full bg-amber-100 text-xs"
                            >
                                {{ getDiscussion(section.key).comment_count }}
                            </span>
                        </button>
                    </div>

                    <!-- Discussion Content -->
                    <DiscussionThread
                        v-if="getDiscussion(activeDiscussionTab)"
                        :key="activeDiscussionTab"
                        :discussion="getDiscussion(activeDiscussionTab)"
                        :bill-id="bill.id"
                        :comments="getDiscussion(activeDiscussionTab).comments || []"
                    />

                    <!-- Create Discussion Prompt -->
                    <div v-else class="py-12 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="mb-4 text-sm text-slate-600">No discussion in this section yet.</p>
                        <button
                            @click="getOrCreateDiscussion(activeDiscussionTab)"
                            class="px-4 py-2 text-sm font-medium text-white transition-colors bg-teal-600 rounded-lg hover:bg-teal-500"
                        >
                            Start Discussion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
