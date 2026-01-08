<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import CommentForm from './CommentForm.vue'

const props = defineProps({
    discussion: {
        type: Object,
        required: true,
    },
    billId: {
        type: Number,
        required: true,
    },
    comments: {
        type: Array,
        default: () => [],
    },
})

const replyingTo = ref(null)

const toggleReply = (commentId) => {
    replyingTo.value = replyingTo.value === commentId ? null : commentId
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffMs = now - date
    const diffMins = Math.floor(diffMs / 60000)

    if (diffMins < 1) return 'Just now'
    if (diffMins < 60) return `${diffMins}m ago`

    const diffHours = Math.floor(diffMins / 60)
    if (diffHours < 24) return `${diffHours}h ago`

    const diffDays = Math.floor(diffHours / 24)
    if (diffDays < 7) return `${diffDays}d ago`

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const markHelpful = (commentId) => {
    router.post(route('comments.helpful', commentId), {}, {
        preserveScroll: true,
    })
}

const flagComment = (commentId) => {
    if (!confirm('Flag this comment for moderation review?')) {
        return
    }

    router.post(route('comments.flag', commentId), {}, {
        preserveScroll: true,
    })
}

const deleteComment = (commentId) => {
    if (!confirm('Delete this comment? This action can be undone.')) {
        return
    }

    router.delete(route('comments.destroy', commentId), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="space-y-6">
        <!-- Comment List -->
        <div v-if="comments && comments.length > 0" class="space-y-4">
            <div v-for="comment in comments" :key="comment.id" class="bg-white border border-stone-200 rounded-lg p-4">
                <!-- Comment Header -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-slate-700 text-sm font-medium">
                            {{ comment.user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-medium text-slate-200">{{ comment.user.name }}</div>
                            <div class="text-xs text-slate-600">
                                {{ formatDate(comment.created_at) }}
                                <span v-if="comment.updated_at !== comment.created_at" class="ml-1">(edited)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Outdated Bill Warning -->
                    <div v-if="comment.is_outdated" class="text-xs text-amber-700">
                        ⚠️ Bill amended
                    </div>
                </div>

                <!-- Comment Content -->
                <div class="text-slate-700 leading-relaxed mb-4 pl-11">
                    {{ comment.content }}
                </div>

                <!-- Comment Actions -->
                <div class="flex items-center gap-4 pl-11 text-sm">
                    <button
                        @click="toggleReply(comment.id)"
                        class="text-slate-600 hover:text-teal-600 transition-colors"
                    >
                        Reply
                    </button>

                    <button
                        @click="markHelpful(comment.id)"
                        class="flex items-center gap-1 text-slate-600 hover:text-emerald-700 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                        </svg>
                        <span>Helpful ({{ comment.helpful_count }})</span>
                    </button>

                    <button
                        @click="flagComment(comment.id)"
                        class="text-slate-600 hover:text-rose-700 transition-colors"
                    >
                        Flag
                    </button>

                    <button
                        v-if="$page.props.auth?.user?.id === comment.user_id"
                        @click="deleteComment(comment.id)"
                        class="text-slate-600 hover:text-rose-700 transition-colors ml-auto"
                    >
                        Delete
                    </button>
                </div>

                <!-- Reply Form -->
                <div v-if="replyingTo === comment.id" class="mt-4 pl-11">
                    <CommentForm
                        :discussion-id="discussion.id"
                        :bill-id="billId"
                        :parent-id="comment.id"
                        placeholder="Write your reply..."
                        @submitted="toggleReply(comment.id)"
                        @cancelled="toggleReply(comment.id)"
                    />
                </div>

                <!-- Nested Replies -->
                <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 pl-11 space-y-4">
                    <div v-for="reply in comment.replies" :key="reply.id" class="bg-stone-100/50 border border-stone-300 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-slate-700 text-xs font-medium">
                                    {{ reply.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-slate-200">{{ reply.user.name }}</div>
                                    <div class="text-xs text-slate-600">{{ formatDate(reply.created_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-sm text-slate-700 leading-relaxed mb-3 pl-9">
                            {{ reply.content }}
                        </div>

                        <div class="flex items-center gap-4 pl-9 text-xs">
                            <button
                                @click="markHelpful(reply.id)"
                                class="flex items-center gap-1 text-slate-600 hover:text-emerald-700 transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                </svg>
                                <span>{{ reply.helpful_count }}</span>
                            </button>

                            <button
                                @click="flagComment(reply.id)"
                                class="text-slate-600 hover:text-rose-700 transition-colors"
                            >
                                Flag
                            </button>

                            <button
                                v-if="$page.props.auth?.user?.id === reply.user_id"
                                @click="deleteComment(reply.id)"
                                class="text-slate-600 hover:text-rose-700 transition-colors ml-auto"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="text-slate-600 text-sm">No comments yet. Be the first to share your thoughts.</p>
        </div>

        <!-- Top-Level Comment Form -->
        <div class="pt-6 border-t border-stone-200">
            <h4 class="text-sm font-medium text-slate-700 mb-3">Add a Comment</h4>
            <CommentForm
                :discussion-id="discussion.id"
                :bill-id="billId"
                placeholder="Share your thoughts on this bill..."
            />
        </div>
    </div>
</template>
