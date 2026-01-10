<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    bills: Object, // Paginated collection
    filters: Object, // Current search/filter state
})

// Local filter state
const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const chamber = ref(props.filters.chamber || '')
const sort = ref(props.filters.sort || 'last_action')

// Apply filters
const applyFilters = () => {
    router.get(route('bills.index'), {
        search: search.value,
        status: status.value,
        chamber: chamber.value,
        sort: sort.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Clear all filters
const clearFilters = () => {
    search.value = ''
    status.value = ''
    chamber.value = ''
    sort.value = 'last_action'
    applyFilters()
}

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return search.value || status.value || chamber.value
})

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
</script>

<template>
    <AppLayout title="All Bills">
        <Head title="All Bills" />

        <div class="py-12">
            <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
                <!-- Header -->
                <div class="my-8">
                    <h1 class="text-3xl font-semibold text-slate-900">
                        All Bills
                    </h1>
                    <p class="mt-2 text-slate-600">
                        Browse and search all Congressional legislation
                    </p>
                </div>

                <!-- Search and Filters -->
                <div class="p-6 mb-6 border bg-white border-stone-200 rounded-xl">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Search Input -->
                        <div class="md:col-span-2">
                            <label for="search" class="block mb-2 text-sm font-medium text-slate-700">
                                Search Bills
                            </label>
                            <input
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Search by title, summary, or bill number..."
                                class="w-full px-4 py-2 transition-all border rounded-lg bg-stone-100 border-stone-300 text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-amber-600 focus:border-transparent"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block mb-2 text-sm font-medium text-slate-700">
                                Status
                            </label>
                            <select
                                id="status"
                                v-model="status"
                                class="w-full px-4 py-2 transition-all border rounded-lg bg-stone-100 border-stone-300 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option value="introduced">Introduced</option>
                                <option value="in_committee">In Committee</option>
                                <option value="on_floor">On Floor</option>
                                <option value="passed_chamber">Passed Chamber</option>
                                <option value="passed_both">Passed Both</option>
                                <option value="to_president">To President</option>
                                <option value="became_law">Became Law</option>
                                <option value="failed">Failed</option>
                                <option value="vetoed">Vetoed</option>
                            </select>
                        </div>

                        <!-- Chamber Filter -->
                        <div>
                            <label for="chamber" class="block mb-2 text-sm font-medium text-slate-700">
                                Chamber
                            </label>
                            <select
                                id="chamber"
                                v-model="chamber"
                                class="w-full px-4 py-2 transition-all border rounded-lg bg-stone-100 border-stone-300 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                @change="applyFilters"
                            >
                                <option value="">Both Chambers</option>
                                <option value="house">House</option>
                                <option value="senate">Senate</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="mt-4">
                        <label for="sort" class="block mb-2 text-sm font-medium text-slate-700">
                            Sort By
                        </label>
                        <select
                            id="sort"
                            v-model="sort"
                            class="w-full px-4 py-2 transition-all border rounded-lg bg-stone-100 border-stone-300 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:border-transparent md:w-auto"
                            @change="applyFilters"
                        >
                            <option value="last_action">Latest Action (Newest First)</option>
                            <option value="last_action_asc">Latest Action (Oldest First)</option>
                            <option value="introduced">Recently Introduced</option>
                            <option value="introduced_asc">Oldest Introduced</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-center justify-between mt-4">
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="text-sm transition-colors text-slate-600 hover:text-slate-700"
                        >
                            Clear all filters
                        </button>
                        <div v-else />

                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-white transition-colors bg-teal-600 rounded-lg hover:bg-teal-500"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="mb-4 text-sm text-slate-600">
                    Showing {{ bills.from || 0 }}-{{ bills.to || 0 }} of {{ bills.total || 0 }} bills
                </div>

                <!-- Bills List -->
                <div v-if="bills.data && bills.data.length > 0" class="space-y-4">
                    <Link
                        v-for="bill in bills.data"
                        :key="bill.id"
                        :href="route('bills.show', bill.id)"
                        class="block p-6 transition-all border bg-white border-stone-200 rounded-xl hover:border-stone-300 group"
                    >
                        <!-- Bill Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span  class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-lg bg-stone-100 text-slate-700">
                                    {{ bill.identifier }}
                                </span>

                                <span
                                    v-if="bill.is_locally_relevant"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-emerald-500/10 text-emerald-700"
                                >
                                    Local
                                </span>
                            </div>

                            <span class="text-xs capitalize text-slate-600">
                                {{ bill.status_display }}
                            </span>
                        </div>

                        <!-- Bill Title -->
                        <h3 class="mb-2 text-xl font-semibold transition-colors text-slate-900 group-hover:text-teal-600">
                            {{ bill.title }}
                        </h3>

                        <!-- Bill Summary -->
                        <p v-if="bill.summary" v-html="bill.summary" class="mb-4 text-slate-600 line-clamp-2">

                        </p>

                        <!-- Bill Meta -->
                        <div class="flex flex-wrap items-center gap-6 text-sm text-slate-600">
                            <div v-if="bill.sponsor" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium text-slate-600">{{ bill.sponsor.name }}</span>
                                <span v-if="bill.sponsor.party" class="text-slate-500">
                                    ({{ bill.sponsor.party }})
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="capitalize">{{ bill.chamber }}</span>
                            </div>

                            <div v-if="bill.stances_count !== undefined" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                                <span>{{ bill.stances_count }} {{ bill.stances_count === 1 ? 'stance' : 'stances' }}</span>
                            </div>

                            <div v-if="bill.followers_count !== undefined" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                                <span>{{ bill.followers_count }} {{ bill.followers_count === 1 ? 'follower' : 'followers' }}</span>
                            </div>

                            <div v-if="bill.comments_count !== undefined" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ bill.comments_count }} {{ bill.comments_count === 1 ? 'comment' : 'comments' }}</span>
                            </div>

                            <div v-if="bill.last_action_at" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ formatDate(bill.last_action_at) }}</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center border bg-white border-stone-200 rounded-xl">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mb-2 text-lg font-semibold text-slate-700">
                            No bills found
                        </h3>
                        <p class="mb-6 text-slate-600">
                            Try adjusting your search or filter criteria
                        </p>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="px-4 py-2 transition-colors rounded-lg bg-stone-100 hover:bg-amber-100 text-slate-900"
                        >
                            Clear all filters
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="bills.data && bills.data.length > 0" class="flex items-center justify-between mt-8">
                    <div class="flex items-center gap-2">
                        <template v-for="link in bills.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-all',
                                    link.active
                                        ? 'bg-teal-600 text-white'
                                        : 'bg-stone-100 text-slate-700 hover:bg-amber-100'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'px-4 py-2 rounded-lg bg-white text-slate-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
