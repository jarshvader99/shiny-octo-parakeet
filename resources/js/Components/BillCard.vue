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
                <span class="font-medium text-slate-600">Sponsor:</span>
                <span>{{ bill.sponsor.name }}</span>
                <span v-if="bill.sponsor.party" class="text-slate-600">
                    ({{ bill.sponsor.party }})
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span class="font-medium text-slate-600">Chamber:</span>
                <span class="capitalize">{{ bill.chamber }}</span>
            </div>

            <div v-if="bill.last_action_at" class="flex items-center gap-2">
                <span class="font-medium text-slate-600">Last Action:</span>
                <span>{{ bill.last_action_at }}</span>
            </div>
        </div>
    </Link>
</template>
