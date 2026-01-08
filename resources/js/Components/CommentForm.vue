<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    discussionId: {
        type: Number,
        required: true,
    },
    billId: {
        type: Number,
        required: true,
    },
    parentId: {
        type: Number,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Share your thoughts...',
    },
})

const emit = defineEmits(['submitted', 'cancelled'])

const form = useForm({
    content: '',
    parent_id: props.parentId,
})

const characterCount = ref(0)
const minCharacters = 10
const maxCharacters = 5000

const updateCharacterCount = () => {
    characterCount.value = form.content.length
}

const submit = () => {
    form.post(route('bills.discussions.comments.store', {
        bill: props.billId,
        discussion: props.discussionId,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            characterCount.value = 0
            emit('submitted')
        },
    })
}

const cancel = () => {
    form.reset()
    characterCount.value = 0
    emit('cancelled')
}
</script>

<template>
    <div class="bg-stone-100/50 border border-stone-300 rounded-lg p-4">
        <form @submit.prevent="submit" class="space-y-3">
            <textarea
                v-model="form.content"
                rows="4"
                :maxlength="maxCharacters"
                class="w-full px-4 py-3 bg-stone-100 border border-stone-300 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                :placeholder="placeholder"
                @input="updateCharacterCount"
                required
            />

            <div class="flex items-center justify-between">
                <div
                    :class="[
                        'text-xs',
                        characterCount < minCharacters ? 'text-amber-700' : 'text-slate-600'
                    ]"
                >
                    {{ characterCount }} / {{ maxCharacters }}
                    <span v-if="characterCount < minCharacters">
                        ({{ minCharacters - characterCount }} more needed)
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="parentId"
                        type="button"
                        @click="cancel"
                        class="px-4 py-2 text-sm bg-amber-100 hover:bg-slate-600 text-slate-700 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing || characterCount < minCharacters"
                        class="px-4 py-2 text-sm bg-teal-600 hover:bg-teal-500 disabled:bg-amber-100 disabled:text-slate-600 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
                    >
                        {{ form.processing ? 'Posting...' : 'Post Comment' }}
                    </button>
                </div>
            </div>

            <div v-if="form.errors.content" class="text-sm text-rose-700">
                {{ form.errors.content }}
            </div>
        </form>
    </div>
</template>
