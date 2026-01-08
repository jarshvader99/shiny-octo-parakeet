<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BillCard from '@/Components/BillCard.vue';
import OnboardingTour from '@/Components/OnboardingTour.vue';

defineProps({
    localBills: Array,
    nationalBills: Array,
    userDistrict: String,
});

const tourRef = ref(null)

const tourSteps = [
    {
        title: 'Welcome to Congressional Consensus',
        description: 'This platform helps you stay informed about legislation and participate in meaningful discussions about bills that affect your community.',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        list: [
            'Review bills from Congress with verified data from Congress.gov',
            'Submit your reasoned stance with substantive explanations',
            'Participate in structured discussions',
            'See geographic consensus patterns',
        ],
    },
    {
        title: 'Your Dashboard',
        description: 'Bills are prioritized based on your location. You\'ll see legislation from your representatives first, followed by national bills.',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        title: 'Submit Your Stance',
        description: 'Unlike simple voting, we ask for substantive reasoning behind your position. This creates informed consensus, not just popularity contests.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        list: [
            'Choose from 5 stance options: Support, Oppose, Mixed, Undecided, or Need More Info',
            'Provide detailed reasoning (required for quality consensus)',
            'Revise your stance as bills evolve',
            'Your position is preserved if you change your mind',
        ],
    },
    {
        title: 'Join Discussions',
        description: 'Participate in structured conversations organized by topic. Discussions are bill-centric and organized into sections for focused dialogue.',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    },
    {
        title: 'Understand Consensus',
        description: 'See how others in your region and nationwide feel about legislation through interactive visualizations and aggregate metrics.',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        list: [
            'Geographic heat maps show regional consensus',
            'Timeline charts track sentiment shifts',
            'Privacy-protected: minimum 5 responses per region',
            'All data sourced from Congress.gov',
        ],
    },
    {
        title: 'Stay Updated',
        description: 'Follow bills to get notified when they change. You can customize notifications for amendments, votes, and status changes.',
        icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    },
]
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-slate-900">
                    Your Legislative Dashboard
                </h2>
                <button
                    @click="tourRef?.resetTour()"
                    class="px-3 py-1.5 text-sm text-slate-600 hover:text-teal-600 transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Take Tour
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- User Location Info -->
                <div v-if="userDistrict" class="p-4 border rounded-lg bg-white border-stone-200">
                    <p class="text-sm text-slate-600">
                        Showing bills for <span class="font-medium text-slate-900">{{ userDistrict }}</span>
                    </p>
                </div>

                <!-- Local Bills Section -->
                <div v-if="localBills && localBills.length > 0">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Bills from Your Representatives
                        </h3>
                        <Link
                            :href="route('bills.index')"
                            class="text-sm text-teal-600 hover:text-indigo-300 transition-colors"
                        >
                            View all bills →
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <BillCard
                            v-for="bill in localBills"
                            :key="bill.id"
                            :bill="{ ...bill, is_locally_relevant: true }"
                            :show-local-badge="true"
                        />
                    </div>
                </div>

                <!-- National Bills Section -->
                <div v-if="nationalBills && nationalBills.length > 0">
                    <h3 class="mb-4 text-lg font-semibold text-slate-900">
                        Trending National Bills
                    </h3>

                    <div class="space-y-4">
                        <BillCard
                            v-for="bill in nationalBills"
                            :key="bill.id"
                            :bill="bill"
                            :show-local-badge="false"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="(!localBills || localBills.length === 0) && (!nationalBills || nationalBills.length === 0)" class="py-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mb-2 text-lg font-medium text-slate-700">No bills available yet</h3>
                    <p class="mb-4 text-sm text-slate-600">
                        Bills will appear here once they're synced from Congress.gov
                    </p>
                    <Link
                        :href="route('bills.index')"
                        class="inline-block px-4 py-2 text-sm font-medium transition-colors rounded-lg bg-teal-600 text-slate-900 hover:bg-teal-700"
                    >
                        Browse all bills
                    </Link>
                </div>
            </div>
        </div>

        <!-- Onboarding Tour -->
        <OnboardingTour
            ref="tourRef"
            tour-key="dashboard"
            :steps="tourSteps"
            :auto-start="true"
        />
    </AppLayout>
</template>
