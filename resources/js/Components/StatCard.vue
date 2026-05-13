<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        default: 0,
    },
    icon: {
        type: String,
        default: 'bi-bar-chart',
    },
    color: {
        type: String,
        default: 'indigo',
    },
    prefix: {
        type: String,
        default: '',
    },
})

const displayValue = ref(0)
let frameId = null

const colorClass = computed(() => {
    const colors = {
        indigo: 'bg-indigo-50 text-indigo-600',
        emerald: 'bg-emerald-50 text-emerald-600',
        amber: 'bg-amber-50 text-amber-600',
        blue: 'bg-blue-50 text-blue-600',
        red: 'bg-red-50 text-red-600',
    }

    return colors[props.color] || colors.indigo
})

const formattedValue = computed(() => {
    const numericValue = Number(displayValue.value || 0)

    if (props.prefix === 'Rp') {
        return `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(numericValue))}`
    }

    return new Intl.NumberFormat('id-ID').format(Math.round(numericValue))
})

const animateValue = (target) => {
    if (frameId) {
        cancelAnimationFrame(frameId)
        frameId = null
    }

    const start = Number(displayValue.value || 0)
    const end = Number(target || 0)
    const duration = 900
    const startTime = performance.now()

    const tick = (time) => {
        const progress = Math.min((time - startTime) / duration, 1)
        displayValue.value = start + (end - start) * progress

        if (progress < 1) {
            frameId = requestAnimationFrame(tick)
        }
    }

    frameId = requestAnimationFrame(tick)
}

watch(
    () => props.value,
    (value) => {
        animateValue(Number(value || 0))
    },
    { immediate: true }
)

onBeforeUnmount(() => {
    if (frameId) {
        cancelAnimationFrame(frameId)
        frameId = null
    }
})
</script>

<template>
    <div class="card p-3 sm:p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-medium text-slate-500 sm:text-sm">
                    {{ title }}
                </p>
                <p class="mt-1.5 text-xl font-bold text-slate-800 sm:mt-2 sm:text-2xl">
                    {{ formattedValue }}
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-xl sm:h-11 sm:w-11" :class="colorClass">
                <i :class="['text-base sm:text-lg', icon]" />
            </div>
        </div>
    </div>
</template>