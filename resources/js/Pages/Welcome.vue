<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const features = [
    {
        title: 'Informed Consensus',
        description: 'Not just voting—we measure informed public opinion. Every stance requires substantive reasoning, creating quality consensus over popularity.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        title: 'Local Relevance',
        description: 'See bills from your representatives first. Your dashboard prioritizes legislation affecting your congressional district and state.',
        icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        title: 'Verified Data',
        description: 'All bill information synced directly from Congress.gov. See the official text, status, sponsors, and timeline—no editorial spin.',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    },
    {
        title: 'Structured Discussions',
        description: 'Participate in focused conversations organized by topic: Key Questions, Arguments For/Against, and Impact Analysis.',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    },
    {
        title: 'Geographic Insights',
        description: 'Visualize how your region and nationwide constituents feel about legislation through interactive heat maps and consensus breakdowns.',
        icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
    },
    {
        title: 'Track & Follow',
        description: 'Get notified when bills you follow are amended, move through Congress, or change status. Stay informed without the noise.',
        icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    },
]
</script>

<template>
    <Head title="Congressional Consensus - Informed Public Discourse on Legislation" />

    <div class="min-h-screen bg-stone-50">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b bg-white shadow-sm border-stone-200">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Document background -->
                            <rect x="15" y="10" width="60" height="80" rx="4" fill="white" stroke="#0f172a" stroke-width="4"/>
                            <!-- Folded corner -->
                            <path d="M 75 10 L 75 25 L 60 25 L 75 10 Z" fill="#14b8a6" />
                            <path d="M 75 10 L 75 25 L 60 25" fill="none" stroke="#0f172a" stroke-width="2" stroke-linejoin="round"/>
                            <!-- Document lines -->
                            <line x1="25" y1="35" x2="50" y2="35" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                            <line x1="25" y1="45" x2="55" y2="45" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                            <line x1="25" y1="55" x2="45" y2="55" stroke="#0f172a" stroke-width="3" stroke-linecap="round"/>
                            <!-- Checkmark -->
                            <path d="M 32 65 L 45 78 L 75 48" stroke="#14b8a6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                        <span class="text-lg font-semibold text-slate-900">Congressional Consensus</span>
                    </div>
                    <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium transition-colors rounded-lg text-slate-700 hover:text-slate-900 hover:bg-stone-100"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-sm font-medium transition-colors rounded-lg text-slate-700 hover:text-slate-900 hover:bg-stone-100"
                            >
                                Sign In
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-teal-600 hover:bg-teal-500"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative px-4 pt-32 pb-20 mx-auto overflow-hidden max-w-7xl sm:px-6 lg:px-8">
            <!-- Background gradient -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50 via-stone-50 to-teal-50"></div>
            </div>

            <div class="max-w-3xl mx-auto text-center">
                <h1 class="mb-6 text-5xl font-bold tracking-tight text-slate-900 sm:text-6xl">
                    Informed Public Discourse on <span class="text-teal-600">Congressional Legislation</span>
                </h1>
                <p class="mb-8 text-xl leading-relaxed text-slate-700">
                    A civic engagement platform that measures informed consensus—not popularity—on U.S. Congressional bills. Read verified data, submit reasoned stances, and see how your community feels about legislation.
                </p>
                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="route('register')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-lg font-medium text-white transition-all rounded-lg bg-teal-600 hover:bg-teal-500 hover:scale-105"
                    >
                        Start Exploring Bills
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-lg font-medium text-white transition-all rounded-lg bg-teal-600 hover:bg-teal-500 hover:scale-105"
                    >
                        View Bills
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <a
                        href="#features"
                        class="inline-flex items-center gap-2 px-6 py-3 text-lg font-medium transition-colors border rounded-lg text-slate-700 border-stone-300 hover:bg-stone-100 hover:text-slate-900"
                    >
                        Learn More
                    </a>
                </div>
                
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="px-4 py-20 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold text-slate-900 sm:text-4xl">
                    How It Works
                </h2>
                <p class="max-w-2xl mx-auto text-lg text-slate-600">
                    Congressional Consensus is not social media. It's a tool for thinking, not reacting—designed for informed citizens who want to understand legislation.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="p-6 transition-all border bg-white border-stone-200 rounded-xl hover:border-teal-500/50"
                >
                    <div class="flex items-center justify-center w-12 h-12 mb-4 rounded-lg bg-teal-500/10">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-slate-900">{{ feature.title }}</h3>
                    <p class="text-slate-600 leading-relaxed">{{ feature.description }}</p>
                </div>
            </div>
        </div>

        <!-- How It's Different Section -->
        <div class="px-4 py-20 mx-auto bg-white/50 max-w-7xl sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <h2 class="mb-12 text-3xl font-bold text-center text-slate-900 sm:text-4xl">
                    Not Just Another Voting App
                </h2>
                <div class="space-y-8">
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-500/10">
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-slate-900">Quality Over Quantity</h3>
                            <p class="text-slate-600">Every stance requires substantive reasoning. No drive-by clicks or knee-jerk reactions. We measure informed consensus, not mob popularity.</p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-500/10">
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-slate-900">Calm, Not Combative</h3>
                            <p class="text-slate-600">No rage-bait algorithms, no infinite scroll, no dopamine hooks. This is a space for reading, thinking, and meaningful dialogue—not arguing.</p>
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-500/10">
                                <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-slate-900">Source of Truth</h3>
                            <p class="text-slate-600">All data synced directly from Congress.gov. See the actual bill text, official timeline, and real sponsors—no editorializing, no hidden agendas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="px-4 py-20 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-12 text-center bg-slate-100 border border-slate-700 rounded-2xl">
                <h2 class="mb-4 text-3xl font-bold text-slate-800 sm:text-4xl">
                    Ready to Make Your Voice Heard?
                </h2>
                <p class="max-w-2xl mx-auto mb-8 text-lg text-slate-700">
                    Join constituents across the country reviewing legislation, sharing perspectives, and measuring informed public consensus.
                </p>
                <Link
                    v-if="canRegister && !$page.props.auth.user"
                    :href="route('register')"
                    class="inline-flex items-center gap-2 px-8 py-4 text-lg font-medium text-white transition-all rounded-lg bg-teal-600 hover:bg-teal-500 hover:scale-105"
                >
                    Create Free Account
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>
                <Link
                    v-else
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-2 px-8 py-4 text-lg font-medium text-white transition-all rounded-lg bg-teal-600 hover:bg-teal-500 hover:scale-105"
                >
                    Go to Dashboard
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="px-4 py-12 border-t bg-stone-50 border-stone-200">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="text-sm text-slate-600">© 2026 Congressional Consensus</span>
                    </div>
                    <div class="text-sm text-slate-600">
                        <span>All bill data sourced from </span>
                        <a href="https://www.congress.gov" target="_blank" rel="noopener noreferrer" class="transition-colors text-teal-600 hover:text-indigo-300">
                            Congress.gov
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
