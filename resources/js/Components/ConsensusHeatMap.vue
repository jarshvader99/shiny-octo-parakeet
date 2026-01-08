<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
    billId: {
        type: Number,
        required: true,
    },
    geographicData: {
        type: Object,
        default: () => ({}),
    },
    title: {
        type: String,
        default: 'Regional Consensus',
    },
})

const mapContainer = ref(null)
const map = ref(null)
const geoJsonLayer = ref(null)
const geoJsonData = ref(null)
const selectedRegion = ref(null)

// Load GeoJSON and initialize map
onMounted(async () => {
    try {
        const response = await fetch('/geojson/us-states-simplified.json')
        geoJsonData.value = await response.json()

        // Wait for DOM to be ready
        await nextTick()

        // Only initialize map if we have data and container
        if (hasSufficientData.value && mapContainer.value) {
            initializeMap()
        }
    } catch (error) {
        console.error('Failed to load GeoJSON:', error)
    }
})

// Initialize Leaflet map
const initializeMap = () => {
    if (!mapContainer.value) return

    // Create map
    map.value = L.map(mapContainer.value, {
        center: [37.8, -96],
        zoom: 4,
        zoomControl: true,
        attributionControl: false,
    })

    // Add tile layer (dark theme)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
    }).addTo(map.value)

    // Add GeoJSON layer
    geoJsonLayer.value = L.geoJSON(geoJsonData.value, {
        style: styleFunction,
        onEachFeature: onEachFeature,
    }).addTo(map.value)

    // Add legend
    addLegend()
}

// Add legend to map
const addLegend = () => {
    if (!map.value) return

    const legend = L.control({ position: 'bottomright' })

    legend.onAdd = function() {
        const div = L.DomUtil.create('div', 'bg-white/95 border border-stone-300 rounded-lg p-3 text-xs')
        div.innerHTML = `
            <div class="mb-2 font-semibold text-slate-700">Legend</div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background: rgba(16, 185, 129, 0.7)"></div>
                    <span class="text-slate-600">Support</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background: rgba(239, 68, 68, 0.7)"></div>
                    <span class="text-slate-600">Oppose</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background: rgba(251, 191, 36, 0.7)"></div>
                    <span class="text-slate-600">Mixed</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-slate-600"></div>
                    <span class="text-slate-600">Insufficient data</span>
                </div>
            </div>
            <div class="pt-2 mt-2 border-t border-stone-300 text-slate-600">
                Darker = stronger consensus
            </div>
        `
        return div
    }

    legend.addTo(map.value)
}

// Get color for region based on consensus
const getRegionColor = (stateCode) => {
    const regionData = props.geographicData[stateCode]
    if (!regionData || regionData.total < 5) {
        return '#475569' // slate-600 - insufficient data
    }

    const dominantStance = regionData.dominant_stance
    const intensity = regionData.dominant_percentage / 100

    // Base colors by stance
    const colors = {
        support: { r: 16, g: 185, b: 129 },     // emerald-500
        oppose: { r: 239, g: 68, b: 68 },       // rose-500
        mixed: { r: 251, g: 191, b: 36 },       // amber-500
        undecided: { r: 120, g: 113, b: 108 },  // stone-500
        needs_more_info: { r: 99, g: 102, b: 241 }, // indigo-500
    }

    const color = colors[dominantStance] || { r: 100, g: 116, b: 139 }

    // Adjust opacity based on intensity
    const alpha = 0.3 + (intensity * 0.6) // Range: 0.3 to 0.9

    return `rgba(${color.r}, ${color.g}, ${color.b}, ${alpha})`
}

// Style for each feature
const styleFunction = (feature) => {
    const stateCode = feature.properties.STATE
    return {
        fillColor: getRegionColor(stateCode),
        weight: 2,
        opacity: 1,
        color: '#334155', // slate-700
        fillOpacity: 0.7,
    }
}

// Hover style
const highlightFeature = (e) => {
    const layer = e.target
    layer.setStyle({
        weight: 3,
        color: '#94a3b8', // slate-400
        fillOpacity: 0.9,
    })

    const stateCode = layer.feature.properties.STATE
    const regionData = props.geographicData[stateCode]
    if (regionData) {
        selectedRegion.value = {
            name: layer.feature.properties.NAME,
            ...regionData,
        }
    }
}

// Reset style
const resetHighlight = (e) => {
    if (geoJsonLayer.value) {
        geoJsonLayer.value.resetStyle(e.target)
    }
    selectedRegion.value = null
}

// Feature events
const onEachFeature = (feature, layer) => {
    layer.on({
        mouseover: highlightFeature,
        mouseout: resetHighlight,
    })
}

// Has sufficient data to display
const hasSufficientData = computed(() => {
    return Object.keys(props.geographicData).length > 0
})

// Stats summary
const statsSummary = computed(() => {
    const regions = Object.values(props.geographicData)
    if (regions.length === 0) return null

    const totalResponses = regions.reduce((sum, r) => sum + r.total, 0)
    const regionsWithData = regions.length

    return {
        totalResponses,
        regionsWithData,
        averagePerRegion: Math.round(totalResponses / regionsWithData),
    }
})
</script>

<template>
    <div class="p-6 border bg-white border-stone-200 rounded-xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ title }}
            </h3>

            <!-- Privacy Notice -->
            <div class="flex items-center gap-2 text-xs text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Aggregated data only • Min 5 responses</span>
            </div>
        </div>

        <!-- Stats Summary -->
        <div v-if="statsSummary" class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-3 text-center rounded-lg bg-stone-100/50">
                <div class="text-2xl font-bold text-slate-900">{{ statsSummary.regionsWithData }}</div>
                <div class="text-xs text-slate-600">Regions</div>
            </div>
            <div class="p-3 text-center rounded-lg bg-stone-100/50">
                <div class="text-2xl font-bold text-slate-900">{{ statsSummary.totalResponses }}</div>
                <div class="text-xs text-slate-600">Total Responses</div>
            </div>
            <div class="p-3 text-center rounded-lg bg-stone-100/50">
                <div class="text-2xl font-bold text-slate-900">{{ statsSummary.averagePerRegion }}</div>
                <div class="text-xs text-slate-600">Avg per Region</div>
            </div>
        </div>

        <!-- Map Container -->
        <div v-if="hasSufficientData && geoJsonData" class="relative overflow-hidden border rounded-lg h-96 border-stone-200">
            <div ref="mapContainer" class="w-full h-full"></div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <p class="mb-2 text-sm text-slate-600">No geographic data available</p>
            <p class="text-xs text-slate-600">Regional consensus will appear when sufficient responses are collected</p>
        </div>

        <!-- Hover Details -->
        <div v-if="selectedRegion" class="p-4 mt-4 border rounded-lg bg-stone-100 border-stone-300">
            <h4 class="mb-3 font-semibold text-slate-900">{{ selectedRegion.name }}</h4>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-slate-600">Total Responses</div>
                    <div class="text-lg font-semibold text-slate-900">{{ selectedRegion.total }}</div>
                </div>
                <div>
                    <div class="text-slate-600">Dominant View</div>
                    <div class="text-lg font-semibold capitalize" :class="{
                        'text-teal-600': selectedRegion.dominant_stance === 'needs_more_info',
                        'text-emerald-700': selectedRegion.dominant_stance === 'support',
                        'text-rose-700': selectedRegion.dominant_stance === 'oppose',
                        'text-amber-700': selectedRegion.dominant_stance === 'mixed',
                    }">
                        {{ selectedRegion.dominant_stance.replace(/_/g, ' ') }} ({{ selectedRegion.dominant_percentage }}%)
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-5 gap-2 mt-3 text-xs">
                <div class="text-center">
                    <div class="font-semibold text-emerald-700">{{ selectedRegion.support_percentage }}%</div>
                    <div class="text-slate-600">Support</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-rose-700">{{ selectedRegion.oppose_percentage }}%</div>
                    <div class="text-slate-600">Oppose</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-amber-700">{{ Math.round((selectedRegion.breakdown.mixed / selectedRegion.total) * 100) }}%</div>
                    <div class="text-slate-600">Mixed</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-stone-400">{{ Math.round((selectedRegion.breakdown.undecided / selectedRegion.total) * 100) }}%</div>
                    <div class="text-slate-600">Undecided</div>
                </div>
                <div class="text-center">
                    <div class="font-semibold text-teal-600">{{ Math.round((selectedRegion.breakdown.needs_more_info / selectedRegion.total) * 100) }}%</div>
                    <div class="text-slate-600">Need Info</div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-4 text-xs text-center text-slate-600">
            Hover over regions to see detailed breakdown • Only regions with 5+ responses shown
        </div>
    </div>
</template>
