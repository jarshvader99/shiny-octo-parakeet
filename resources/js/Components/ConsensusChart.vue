<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        default: 'Consensus Breakdown',
    },
    showEngaged: {
        type: Boolean,
        default: false,
    },
})

const chartCanvas = ref(null)
const chartInstance = ref(null)
const activeView = ref(props.showEngaged ? 'engaged' : 'raw')

const currentMetrics = computed(() => {
    return props.metrics[activeView.value] || props.metrics.raw
})

const chartData = computed(() => {
    const percentages = currentMetrics.value.percentages

    return {
        labels: ['Support', 'Oppose', 'Mixed', 'Undecided', 'Needs More Info'],
        datasets: [{
            data: [
                percentages.support,
                percentages.oppose,
                percentages.mixed,
                percentages.undecided,
                percentages.needs_more_info,
            ],
            backgroundColor: [
                'rgba(16, 185, 129, 0.6)',  // emerald-500/60 - support
                'rgba(239, 68, 68, 0.6)',   // rose-500/60 - oppose
                'rgba(251, 191, 36, 0.6)',  // amber-500/60 - mixed
                'rgba(120, 113, 108, 0.6)', // stone-500/60 - undecided
                'rgba(99, 102, 241, 0.6)',  // indigo-500/60 - needs more info
            ],
            borderColor: [
                'rgba(16, 185, 129, 1)',
                'rgba(239, 68, 68, 1)',
                'rgba(251, 191, 36, 1)',
                'rgba(120, 113, 108, 1)',
                'rgba(99, 102, 241, 1)',
            ],
            borderWidth: 2,
        }],
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#cbd5e1', // slate-300
                padding: 15,
                font: {
                    size: 12,
                },
                usePointStyle: true,
            },
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.95)', // slate-900/95
            titleColor: '#f1f5f9', // slate-100
            bodyColor: '#cbd5e1', // slate-300
            borderColor: '#334155', // slate-700
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
                label: function(context) {
                    const label = context.label || ''
                    const value = context.parsed || 0
                    const count = currentMetrics.value.breakdown[
                        label.toLowerCase().replace(/ /g, '_')
                    ] || 0
                    return `${label}: ${value}% (${count} votes)`
                },
            },
        },
    },
}

const createChart = () => {
    if (!chartCanvas.value) return

    // Destroy existing chart
    if (chartInstance.value) {
        chartInstance.value.destroy()
    }

    // Create new chart
    chartInstance.value = new Chart(chartCanvas.value, {
        type: 'doughnut',
        data: chartData.value,
        options: chartOptions,
    })
}

onMounted(() => {
    createChart()
})

watch([chartData, activeView], () => {
    createChart()
})
</script>

<template>
    <div class="p-6 mt-6 border bg-white border-stone-200 rounded-xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ title }}
            </h3>

            <!-- View Toggle -->
            <div v-if="metrics.engaged && metrics.engaged.total > 0" class="flex items-center gap-2">
                <button
                    @click="activeView = 'raw'"
                    :class="[
                        'px-3 py-1 text-sm rounded-lg transition-all',
                        activeView === 'raw'
                            ? 'bg-teal-600 text-white'
                            : 'bg-stone-100 text-slate-600 hover:bg-amber-100'
                    ]"
                >
                    All Votes
                </button>
                <button
                    @click="activeView = 'engaged'"
                    :class="[
                        'px-3 py-1 text-sm rounded-lg transition-all',
                        activeView === 'engaged'
                            ? 'bg-teal-600 text-white'
                            : 'bg-stone-100 text-slate-600 hover:bg-amber-100'
                    ]"
                >
                    Engaged Only
                </button>
            </div>
        </div>

        <!-- Consensus Score -->
        <div class="mb-6 text-center">
            <div class="mb-1 text-3xl font-bold text-slate-900">
                {{ currentMetrics.score }}
                <span class="text-lg text-slate-600">/100</span>
            </div>
            <div class="text-sm text-slate-600">
                Consensus Strength
            </div>
            <div class="mt-1 text-xs text-slate-600">
                Based on {{ currentMetrics.total.toLocaleString() }}
                {{ activeView === 'engaged' ? 'engaged' : '' }}
                {{ currentMetrics.total === 1 ? 'response' : 'responses' }}
            </div>
        </div>

        <!-- Chart -->
        <div class="relative h-64 mb-4">
            <canvas
                ref="chartCanvas"
                role="img"
                :aria-label="`Doughnut chart showing consensus breakdown: ${currentMetrics.percentages.support}% support, ${currentMetrics.percentages.oppose}% oppose, ${currentMetrics.percentages.mixed}% mixed, ${currentMetrics.percentages.undecided}% undecided, ${currentMetrics.percentages.needs_more_info}% needs more info`"
            ></canvas>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-3 mt-6">
            <div class="p-3 rounded-lg bg-stone-100/50">
                <div class="text-2xl font-bold text-emerald-700">
                    {{ currentMetrics.percentages.support }}%
                </div>
                <div class="text-xs text-slate-600">Support</div>
            </div>
            <div class="p-3 rounded-lg bg-stone-100/50">
                <div class="text-2xl font-bold text-rose-700">
                    {{ currentMetrics.percentages.oppose }}%
                </div>
                <div class="text-xs text-slate-600">Oppose</div>
            </div>
        </div>

        <!-- Data Quality Indicator -->
        <div v-if="activeView === 'engaged' && metrics.engaged.total > 0" class="mt-4 text-xs text-center text-slate-600">
            Engaged responses: detailed reasoning (100+ characters)
        </div>
    </div>
</template>
