<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    bill: {
        type: Object,
        required: true,
    },
    showLocalBadge: {
        type: Boolean,
        default: true,
    },
})
</script>

<template>
    <Link
        :href="route('bills.show', bill.id)"
        class="block p-6 transition-all border bg-white border-stone-200 rounded-xl hover:border-stone-300 group"
    >
        <!-- Bill Header -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-lg bg-stone-100 text-slate-700">
                    {{ bill.identifier }}
                </span>

                <span
                    v-if="showLocalBadge && bill.is_locally_relevant"
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
        <div class="flex flex-wrap items-center text-sm gap-x-6 gap-y-2 text-slate-600">
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
                <span>{{ bill.last_action_at }}</span>
            </div>
        </div>
    </Link>
</template>
