<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    trends: {
        type: Array,
        required: true,
    },
    title: {
        type: String,
        default: 'Consensus Trends Over Time',
    },
})

const chartCanvas = ref(null)
const chartInstance = ref(null)
const selectedMetric = ref('support') // support, oppose, mixed

const chartData = computed(() => {
    if (!props.trends || props.trends.length === 0) {
        return null
    }

    const labels = props.trends.map(t => {
        const date = new Date(t.date)
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    })

    return {
        labels,
        datasets: [
            {
                label: 'Support',
                data: props.trends.map(t => t.percentages.support),
                borderColor: 'rgba(16, 185, 129, 1)', // emerald-500
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                hidden: selectedMetric.value !== 'all' && selectedMetric.value !== 'support',
            },
            {
                label: 'Oppose',
                data: props.trends.map(t => t.percentages.oppose),
                borderColor: 'rgba(239, 68, 68, 1)', // rose-500
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                hidden: selectedMetric.value !== 'all' && selectedMetric.value !== 'oppose',
            },
            {
                label: 'Mixed',
                data: props.trends.map(t => t.percentages.mixed),
                borderColor: 'rgba(251, 191, 36, 1)', // amber-500
                backgroundColor: 'rgba(251, 191, 36, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                hidden: selectedMetric.value !== 'all' && selectedMetric.value !== 'mixed',
            },
        ],
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
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
            callbacks: {
                label: function(context) {
                    return `${context.dataset.label}: ${context.parsed.y.toFixed(1)}%`
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            max: 100,
            grid: {
                color: 'rgba(51, 65, 85, 0.3)', // slate-700/30
            },
            ticks: {
                color: '#94a3b8', // slate-400
                callback: function(value) {
                    return value + '%'
                },
            },
        },
        x: {
            grid: {
                color: 'rgba(51, 65, 85, 0.3)', // slate-700/30
            },
            ticks: {
                color: '#94a3b8', // slate-400
                maxRotation: 45,
                minRotation: 45,
            },
        },
    },
}

const createChart = () => {
    if (!chartCanvas.value || !chartData.value) return

    // Destroy existing chart
    if (chartInstance.value) {
        chartInstance.value.destroy()
    }

    // Create new chart
    chartInstance.value = new Chart(chartCanvas.value, {
        type: 'line',
        data: chartData.value,
        options: chartOptions,
    })
}

const toggleMetric = (metric) => {
    selectedMetric.value = metric
    if (chartInstance.value) {
        chartInstance.value.data.datasets.forEach((dataset, index) => {
            if (metric === 'all') {
                dataset.hidden = false
            } else {
                dataset.hidden = dataset.label.toLowerCase() !== metric
            }
        })
        chartInstance.value.update()
    }
}

onMounted(() => {
    createChart()
})

watch(() => props.trends, () => {
    createChart()
}, { deep: true })

// Calculate trend direction
const trendDirection = computed(() => {
    if (!props.trends || props.trends.length < 2) return null

    const first = props.trends[0]
    const last = props.trends[props.trends.length - 1]

    const supportChange = last.percentages.support - first.percentages.support
    const opposeChange = last.percentages.oppose - first.percentages.oppose

    if (Math.abs(supportChange) < 2 && Math.abs(opposeChange) < 2) {
        return { direction: 'stable', text: 'Stable consensus' }
    } else if (supportChange > opposeChange) {
        return { direction: 'up', text: `Support trending up (+${supportChange.toFixed(1)}%)` }
    } else {
        return { direction: 'down', text: `Opposition trending up (+${opposeChange.toFixed(1)}%)` }
    }
})
</script>

<template>
    <div class="bg-white border border-stone-200 rounded-xl p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ title }}
            </h3>

            <!-- Metric Toggle -->
            <div class="flex items-center gap-2">
                <button
                    @click="toggleMetric('all')"
                    :class="[
                        'px-3 py-1 text-xs rounded-lg transition-all',
                        selectedMetric === 'all'
                            ? 'bg-teal-600 text-white'
                            : 'bg-stone-100 text-slate-600 hover:bg-amber-100'
                    ]"
                >
                    All
                </button>
                <button
                    @click="toggleMetric('support')"
                    :class="[
                        'px-3 py-1 text-xs rounded-lg transition-all',
                        selectedMetric === 'support'
                            ? 'bg-emerald-600 text-white'
                            : 'bg-stone-100 text-slate-600 hover:bg-amber-100'
                    ]"
                >
                    Support
                </button>
                <button
                    @click="toggleMetric('oppose')"
                    :class="[
                        'px-3 py-1 text-xs rounded-lg transition-all',
                        selectedMetric === 'oppose'
                            ? 'bg-rose-600 text-white'
                            : 'bg-stone-100 text-slate-600 hover:bg-amber-100'
                    ]"
                >
                    Oppose
                </button>
            </div>
        </div>

        <!-- Trend Indicator -->
        <div v-if="trendDirection" class="mb-4 flex items-center gap-2 text-sm">
            <svg
                v-if="trendDirection.direction === 'up'"
                class="w-5 h-5 text-emerald-700"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <svg
                v-else-if="trendDirection.direction === 'down'"
                class="w-5 h-5 text-rose-700"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
            </svg>
            <svg
                v-else
                class="w-5 h-5 text-slate-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
            </svg>
            <span :class="[
                'font-medium',
                trendDirection.direction === 'up' ? 'text-emerald-700' :
                trendDirection.direction === 'down' ? 'text-rose-700' :
                'text-slate-600'
            ]">
                {{ trendDirection.text }}
            </span>
        </div>

        <!-- Chart -->
        <div v-if="trends && trends.length > 0" class="relative h-64">
            <canvas
                ref="chartCanvas"
                role="img"
                aria-label="Line chart showing consensus trends over time"
            ></canvas>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <svg class="w-12 h-12 mx-auto text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="text-slate-600 text-sm">Not enough data to show trends</p>
            <p class="text-slate-600 text-xs mt-1">Trends appear after multiple responses over time</p>
        </div>

        <!-- Data Points Info -->
        <div v-if="trends && trends.length > 0" class="mt-4 text-xs text-slate-600 text-center">
            Showing {{ trends.length }} data {{ trends.length === 1 ? 'point' : 'points' }} from
            {{ new Date(trends[0].date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }} to
            {{ new Date(trends[trends.length - 1].date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
        </div>
    </div>
</template>
