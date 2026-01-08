<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

const form = useForm({
    zip_code: '',
});

const submit = () => {
    form.post(route('onboarding.zip-code.store'), {
        onSuccess: () => {
            // Redirect to dashboard handled by controller
        },
    });
};
</script>

<template>
    <Head title="Complete Your Profile" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-stone-50">
        <div>
            <AuthenticationCardLogo />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white border border-stone-200 shadow-sm overflow-hidden sm:rounded-xl">
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-slate-900 mb-2">
                    Welcome! Let's personalize your experience
                </h2>
                <p class="text-slate-600 leading-relaxed">
                    We'll use your ZIP code to show you bills from your congressional representatives and regional consensus data. Your exact location is never shared publicly.
                </p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label for="zip_code" class="block text-sm font-medium text-slate-700 mb-2">
                        ZIP Code
                    </label>
                    <input
                        id="zip_code"
                        v-model="form.zip_code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{5}"
                        maxlength="5"
                        placeholder="12345"
                        class="mt-1 block w-full px-4 py-3 bg-stone-100 border border-stone-300 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all"
                        required
                        autofocus
                    />
                    <p v-if="form.errors.zip_code" class="mt-2 text-sm text-rose-700">
                        {{ form.errors.zip_code }}
                    </p>
                    <p class="mt-2 text-xs text-slate-600">
                        U.S. ZIP codes only (5 digits)
                    </p>
                </div>

                <div class="mt-6">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">Continue to Dashboard</span>
                        <span v-else>Processing...</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 p-4 bg-stone-100/50 border border-stone-300 rounded-lg">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-xs text-slate-600 leading-relaxed">
                        <strong class="text-slate-700">Privacy Note:</strong> Your ZIP code helps us show local bills and aggregate regional consensus. We never display individual locations—only district-level statistics like "234 constituents responded."
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
