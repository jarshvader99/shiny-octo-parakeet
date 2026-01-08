<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
    stance: {
        type: Object,
        required: true,
    },
    billId: {
        type: Number,
        required: true,
    },
    showOutdatedWarning: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['edit'])

const stanceColors = {
    support: 'emerald',
    oppose: 'rose',
    mixed: 'amber',
    undecided: 'slate',
    needs_more_info: 'blue',
}

const color = stanceColors[props.stance.stance] || 'slate'

const editStance = () => {
    emit('edit')
}

const deleteStance = () => {
    if (!confirm('Are you sure you want to remove your stance? This action can be undone.')) {
        return
    }

    router.delete(route('bills.stances.destroy', props.billId), {
        preserveScroll: true,
    })
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>

<template>
    <div class="bg-white border border-stone-200 rounded-xl p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div :class="[
                    'px-4 py-2 rounded-lg font-medium',
                    `bg-${color}-500/10 text-${color}-400`
                ]">
                    {{ stance.stance_label }}
                </div>

                <div v-if="stance.revision > 1" class="text-xs text-slate-600">
                    Revision {{ stance.revision }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="editStance"
                    class="px-3 py-1.5 text-sm bg-stone-100 hover:bg-amber-100 text-slate-700 rounded-lg transition-colors"
                >
                    Edit
                </button>
                <button
                    @click="deleteStance"
                    class="px-3 py-1.5 text-sm bg-stone-100 hover:bg-rose-900/20 text-slate-600 hover:text-rose-700 rounded-lg transition-colors"
                >
                    Remove
                </button>
            </div>
        </div>

        <!-- Outdated Warning -->
        <div v-if="showOutdatedWarning" class="mb-4 p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-700 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm text-amber-300">
                    <strong>Bill has changed:</strong> This legislation has been amended since you submitted your stance.
                    <button @click="editStance" class="underline hover:no-underline ml-1">
                        Review and update your position
                    </button>
                </div>
            </div>
        </div>

        <!-- Reason -->
        <div class="mb-4">
            <h4 class="text-sm font-medium text-slate-700 mb-2">Your Reasoning:</h4>
            <div class="text-slate-600 leading-relaxed whitespace-pre-wrap font-serif">
                {{ stance.reason }}
            </div>
        </div>

        <!-- Metadata -->
        <div class="pt-4 border-t border-stone-200 flex items-center justify-between text-sm text-slate-600">
            <div>
                Submitted {{ formatDate(stance.created_at) }}
                <span v-if="stance.congressional_district" class="ml-2">
                    from {{ stance.congressional_district }}
                </span>
            </div>

            <div v-if="stance.updated_at !== stance.created_at" class="text-xs">
                Updated {{ formatDate(stance.updated_at) }}
            </div>
        </div>
    </div>
</template>
