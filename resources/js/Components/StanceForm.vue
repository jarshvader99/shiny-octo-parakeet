<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    billId: {
        type: Number,
        required: true,
    },
    existingStance: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['submitted', 'cancelled'])

const form = useForm({
    stance: props.existingStance?.stance || '',
    reason: props.existingStance?.reason || '',
})

const stanceOptions = [
    {
        value: 'support',
        label: 'Support',
        description: 'I support this legislation',
        color: 'emerald',
    },
    {
        value: 'oppose',
        label: 'Oppose',
        description: 'I oppose this legislation',
        color: 'rose',
    },
    {
        value: 'mixed',
        label: 'Mixed Feelings',
        description: 'I see both pros and cons',
        color: 'amber',
    },
    {
        value: 'undecided',
        label: 'Undecided',
        description: 'I need to think more about this',
        color: 'slate',
    },
    {
        value: 'needs_more_info',
        label: 'Need More Info',
        description: 'I need additional information',
        color: 'blue',
    },
]

const characterCount = ref(form.reason?.length || 0)
const minCharacters = 50
const maxCharacters = 5000

const updateCharacterCount = () => {
    characterCount.value = form.reason.length
}

const submit = () => {
    form.post(route('bills.stances.store', props.billId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('submitted')
        },
    })
}

const cancel = () => {
    form.reset()
    emit('cancelled')
}

// Get explicit Tailwind classes for selected stance (avoids dynamic class purging issues)
const getStanceClasses = (color) => {
    const classMap = {
        emerald: 'border-emerald-500/60 bg-emerald-500/5',
        rose: 'border-rose-500/60 bg-rose-500/5',
        amber: 'border-amber-500/60 bg-amber-500/5',
        slate: 'border-slate-500/60 bg-slate-500/5',
        blue: 'border-blue-500/60 bg-blue-500/5',
    }
    return classMap[color] || 'border-slate-500/60 bg-slate-500/5'
}
</script>

<template>
    <div class="bg-white border border-stone-200 rounded-xl p-6">
        <h3 class="text-xl font-semibold text-slate-900 mb-4">
            {{ existingStance ? 'Update Your Stance' : 'Take a Stance' }}
        </h3>

        <p class="text-slate-600 text-sm mb-6">
            Share your informed perspective on this legislation. Your stance will be part of the civic consensus.
        </p>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Stance Selection -->
            <fieldset>
                <legend class="block text-sm font-medium text-slate-700 mb-3">
                    Your Position
                </legend>
                <div class="space-y-3">
                    <label
                        v-for="option in stanceOptions"
                        :key="option.value"
                        :class="[
                            'flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all',
                            form.stance === option.value
                                ? getStanceClasses(option.color)
                                : 'border-stone-300 hover:border-amber-300'
                        ]"
                    >
                        <input
                            v-model="form.stance"
                            type="radio"
                            :value="option.value"
                            :aria-describedby="`stance-${option.value}-desc`"
                            class="mt-1 mr-3 text-teal-600 focus:ring-amber-500"
                            required
                        />
                        <div class="flex-1">
                            <div class="font-medium text-slate-200">{{ option.label }}</div>
                            <div :id="`stance-${option.value}-desc`" class="text-sm text-slate-600">{{ option.description }}</div>
                        </div>
                    </label>
                </div>
                <div v-if="form.errors.stance" class="mt-2 text-sm text-rose-700" role="alert">
                    {{ form.errors.stance }}
                </div>
            </fieldset>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-sm font-medium text-slate-700 mb-2">
                    Your Reasoning <span class="text-rose-700">*</span>
                </label>
                <p class="text-xs text-slate-600 mb-3">
                    Provide a thoughtful explanation for your position. This helps build informed consensus.
                </p>
                <textarea
                    id="reason"
                    v-model="form.reason"
                    rows="6"
                    :maxlength="maxCharacters"
                    class="w-full px-4 py-3 bg-stone-100 border border-stone-300 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all font-serif"
                    placeholder="Explain your reasoning in detail... (minimum 50 characters)"
                    @input="updateCharacterCount"
                    :aria-describedby="form.errors.reason ? 'reason-error' : 'reason-help'"
                    :aria-invalid="form.errors.reason ? 'true' : 'false'"
                    required
                />

                <!-- Screen reader announcement for character count -->
                <div class="sr-only" aria-live="polite" aria-atomic="true">
                    {{ characterCount }} characters entered,
                    <span v-if="characterCount < minCharacters">
                        {{ minCharacters - characterCount }} more characters needed to meet minimum
                    </span>
                    <span v-else>
                        minimum requirement met
                    </span>
                </div>

                <div class="mt-2 flex items-center justify-between text-xs">
                    <div
                        v-if="form.errors.reason"
                        id="reason-error"
                        class="text-rose-700"
                        role="alert"
                    >
                        {{ form.errors.reason }}
                    </div>
                    <div v-else />
                    <div
                        :class="[
                            characterCount < minCharacters ? 'text-amber-700' : 'text-slate-600'
                        ]"
                        aria-hidden="true"
                    >
                        {{ characterCount }} / {{ maxCharacters }}
                        <span v-if="characterCount < minCharacters">
                            ({{ minCharacters - characterCount }} more needed)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Privacy Notice -->
            <div class="p-4 bg-stone-100/50 border border-stone-300 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-teal-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-slate-600">
                        <p class="mb-2">
                            Your stance will be aggregated geographically by ZIP code/district for consensus visualization.
                            Your name will not be publicly associated with your stance.
                        </p>
                        <p>
                            You can revise your stance at any time. Previous versions are preserved for your own records.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing || characterCount < minCharacters"
                    class="px-6 py-3 bg-teal-600 hover:bg-teal-500 disabled:bg-amber-100 disabled:text-slate-600 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
                >
                    {{ form.processing ? 'Submitting...' : (existingStance ? 'Update Stance' : 'Submit Stance') }}
                </button>
                <button
                    type="button"
                    @click="cancel"
                    class="px-6 py-3 bg-stone-100 hover:bg-amber-100 text-slate-700 font-medium rounded-lg transition-colors"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>
