<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    tourKey: {
        type: String,
        required: true,
    },
    steps: {
        type: Array,
        required: true,
    },
    autoStart: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['complete', 'skip'])

const currentStep = ref(0)
const isActive = ref(false)
const page = usePage()

const currentStepData = computed(() => props.steps[currentStep.value])
const isFirstStep = computed(() => currentStep.value === 0)
const isLastStep = computed(() => currentStep.value === props.steps.length - 1)
const progress = computed(() => ((currentStep.value + 1) / props.steps.length) * 100)

// Check if user has completed this tour
const tourCompleteKey = `tour_completed_${props.tourKey}`
const hasCompletedTour = () => {
    return localStorage.getItem(tourCompleteKey) === 'true'
}

// Start tour
const startTour = () => {
    if (hasCompletedTour()) return
    isActive.value = true
    currentStep.value = 0
}

// Navigate
const nextStep = () => {
    if (isLastStep.value) {
        completeTour()
    } else {
        currentStep.value++
    }
}

const previousStep = () => {
    if (!isFirstStep.value) {
        currentStep.value--
    }
}

const skipTour = () => {
    isActive.value = false
    localStorage.setItem(tourCompleteKey, 'true')
    emit('skip')
}

const completeTour = () => {
    isActive.value = false
    localStorage.setItem(tourCompleteKey, 'true')
    emit('complete')
}

const resetTour = () => {
    localStorage.removeItem(tourCompleteKey)
    startTour()
}

// Auto-start on mount if configured
onMounted(() => {
    if (props.autoStart && !hasCompletedTour()) {
        // Delay slightly to allow page to fully render
        setTimeout(() => startTour(), 500)
    }
})

// Expose methods for parent
defineExpose({
    startTour,
    resetTour,
    skipTour,
})
</script>

<template>
    <!-- Tour Overlay -->
    <Teleport to="body">
        <Transition name="tour-fade">
            <div v-if="isActive" class="fixed inset-0 z-50 overflow-hidden">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-white shadow-sm"></div>

                <!-- Tour Card -->
                <div class="relative flex items-center justify-center min-h-screen p-4">
                    <div class="relative w-full max-w-lg bg-white border border-stone-300 rounded-2xl shadow-2xl">
                        <!-- Progress Bar -->
                        <div class="h-1 bg-stone-100 rounded-t-2xl overflow-hidden">
                            <div
                                class="h-full bg-teal-500 transition-all duration-300 ease-out"
                                :style="{ width: `${progress}%` }"
                            ></div>
                        </div>

                        <!-- Content -->
                        <div class="p-8">
                            <!-- Step Indicator -->
                            <div class="flex items-center justify-between mb-6">
                                <span class="text-sm font-medium text-slate-600">
                                    Step {{ currentStep + 1 }} of {{ steps.length }}
                                </span>
                                <button
                                    @click="skipTour"
                                    class="text-sm text-slate-600 hover:text-slate-700 transition-colors"
                                >
                                    Skip tour
                                </button>
                            </div>

                            <!-- Icon -->
                            <div
                                v-if="currentStepData.icon"
                                class="w-16 h-16 mb-6 rounded-2xl bg-teal-500/10 flex items-center justify-center"
                            >
                                <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="currentStepData.icon" />
                                </svg>
                            </div>

                            <!-- Title -->
                            <h3 class="text-2xl font-semibold text-slate-900 mb-4">
                                {{ currentStepData.title }}
                            </h3>

                            <!-- Description -->
                            <p class="text-slate-700 leading-relaxed mb-8">
                                {{ currentStepData.description }}
                            </p>

                            <!-- Optional List -->
                            <ul v-if="currentStepData.list" class="space-y-3 mb-8">
                                <li
                                    v-for="(item, index) in currentStepData.list"
                                    :key="index"
                                    class="flex items-start gap-3 text-sm text-slate-600"
                                >
                                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ item }}</span>
                                </li>
                            </ul>

                            <!-- Navigation -->
                            <div class="flex items-center gap-3">
                                <button
                                    v-if="!isFirstStep"
                                    @click="previousStep"
                                    class="px-6 py-3 text-slate-700 hover:text-slate-900 hover:bg-stone-100 rounded-lg transition-all"
                                >
                                    Previous
                                </button>

                                <button
                                    @click="nextStep"
                                    class="flex-1 px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-all shadow-lg shadow-indigo-500/25"
                                >
                                    {{ isLastStep ? 'Get Started' : 'Next' }}
                                    <svg v-if="!isLastStep" class="inline-block w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Dots Navigation -->
                        <div class="flex items-center justify-center gap-2 pb-6">
                            <button
                                v-for="(step, index) in steps"
                                :key="index"
                                @click="currentStep = index"
                                class="w-2 h-2 rounded-full transition-all"
                                :class="index === currentStep
                                    ? 'bg-teal-500 w-6'
                                    : 'bg-amber-100 hover:bg-slate-600'"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.tour-fade-enter-active,
.tour-fade-leave-active {
    transition: opacity 0.3s ease;
}

.tour-fade-enter-from,
.tour-fade-leave-to {
    opacity: 0;
}
</style>
