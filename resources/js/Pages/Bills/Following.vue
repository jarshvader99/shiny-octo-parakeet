<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    followedBills: Array,
})

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
</script>

<template>
    <AppLayout title="Following">
        <Head title="Bills You're Following" />

        <div class="py-12">
            <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
                <!-- Header -->
                <div class="mt-8 mb-8">
                    <h1 class="text-3xl font-semibold text-slate-900">
                        Bills You're Following
                    </h1>
                    <p class="mt-2 text-slate-600">
                        Stay updated on legislation that matters to you
                    </p>
                </div>

                <!-- Following Feed -->
                <div v-if="followedBills.length > 0" class="space-y-4">
                    <Link
                        v-for="bill in followedBills"
                        :key="bill.id"
                        :href="route('bills.show', bill.id)"
                        class="block p-6 transition-all border bg-white border-stone-200 rounded-xl hover:border-stone-300 group"
                    >
                        <!-- Bill Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-lg bg-stone-100 text-slate-700">
                                    {{ bill.identifier }}
                                </span>

                                <span :class="[
                                    'inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium',
                                    `bg-${getStatusColor(bill.status)}-500/10 text-${getStatusColor(bill.status)}-400`
                                ]">
                                    {{ bill.status_display }}
                                </span>
                            </div>

                            <div class="text-xs text-slate-600">
                                Following since {{ bill.followed_at }}
                            </div>
                        </div>

                        <!-- Bill Title -->
                        <h3 class="mb-2 text-xl font-semibold transition-colors text-slate-900 group-hover:text-teal-600">
                            {{ bill.title }}
                        </h3>

                        <!-- Bill Summary -->
                        <p v-if="bill.summary" v-html="bill.summary" class="mb-4 text-slate-600 line-clamp-2">
                        </p>

                        <!-- Last Action -->
                        <div v-if="bill.last_action_text" class="p-3 mb-4 border border-l-4 rounded-lg bg-stone-100/50 border-stone-300 border-l-indigo-500">
                            <div class="text-xs font-medium text-slate-600">
                                Last Action • {{ bill.last_action_at }}
                            </div>
                            <div class="mt-1 text-sm text-slate-700">
                                {{ bill.last_action_text }}
                            </div>
                        </div>

                        <!-- Bill Meta -->
                        <div class="flex flex-wrap items-center gap-6 text-sm text-slate-600">
                            <div v-if="bill.sponsor" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium text-slate-600">{{ bill.sponsor.name }}</span>
                                <span v-if="bill.sponsor.party" class="text-slate-600">
                                    ({{ bill.sponsor.party }})
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                                <span>{{ bill.stances_count }} {{ bill.stances_count === 1 ? 'stance' : 'stances' }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ bill.followers_count }} {{ bill.followers_count === 1 ? 'follower' : 'followers' }}</span>
                            </div>
                        </div>

                        <!-- Notification Badges -->
                        <div v-if="bill.notification_preferences" class="flex flex-wrap gap-2 pt-4 mt-4 border-t border-stone-200">
                            <span class="text-xs text-slate-600">Notifications:</span>
                            <span
                                v-if="bill.notification_preferences.notify_on_amendment"
                                class="inline-flex items-center px-2 py-1 text-xs text-teal-600 rounded-md bg-teal-500/10"
                            >
                                Amendments
                            </span>
                            <span
                                v-if="bill.notification_preferences.notify_on_vote"
                                class="inline-flex items-center px-2 py-1 text-xs text-teal-600 rounded-md bg-teal-500/10"
                            >
                                Votes
                            </span>
                            <span
                                v-if="bill.notification_preferences.notify_on_status_change"
                                class="inline-flex items-center px-2 py-1 text-xs text-teal-600 rounded-md bg-teal-500/10"
                            >
                                Status Changes
                            </span>
                            <span
                                v-if="bill.notification_preferences.notify_on_new_discussion"
                                class="inline-flex items-center px-2 py-1 text-xs text-teal-600 rounded-md bg-teal-500/10"
                            >
                                Discussions
                            </span>
                            <span
                                v-if="!bill.notification_preferences.notify_on_amendment && !bill.notification_preferences.notify_on_vote && !bill.notification_preferences.notify_on_status_change && !bill.notification_preferences.notify_on_new_discussion"
                                class="text-xs text-slate-600"
                            >
                                None enabled
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center border bg-white border-stone-200 rounded-xl">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        <h3 class="mb-2 text-lg font-semibold text-slate-700">
                            No bills followed yet
                        </h3>
                        <p class="mb-6 text-slate-600">
                            Start following bills to get updates and track legislation that matters to you
                        </p>
                        <Link
                            :href="route('bills.index')"
                            class="inline-flex items-center gap-2 px-6 py-3 text-white transition-colors bg-teal-600 rounded-lg hover:bg-teal-500"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Browse All Bills
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
