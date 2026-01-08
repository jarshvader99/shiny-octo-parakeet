<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    billId: {
        type: Number,
        required: true,
    },
    isFollowing: {
        type: Boolean,
        default: false,
    },
    followerCount: {
        type: Number,
        default: 0,
    },
})

const followForm = useForm({})

const toggleFollow = () => {
    if (props.isFollowing) {
        followForm.delete(route('bills.unfollow', props.billId), {
            preserveScroll: true,
        })
    } else {
        followForm.post(route('bills.follow', props.billId), {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <button
        @click="toggleFollow"
        :disabled="followForm.processing"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-all rounded-lg"
        :class="isFollowing
            ? 'bg-stone-100 text-slate-700 hover:bg-amber-100 border border-stone-300'
            : 'bg-teal-600 text-white hover:bg-teal-700'"
    >
        <svg
            v-if="isFollowing"
            class="w-4 h-4"
            fill="currentColor"
            viewBox="0 0 20 20"
        >
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        <svg
            v-else
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>

        <span v-if="isFollowing">Following</span>
        <span v-else>Follow Bill</span>

        <span v-if="followerCount > 0" class="ml-1 text-xs opacity-75">
            ({{ followerCount }})
        </span>
    </button>
</template>
