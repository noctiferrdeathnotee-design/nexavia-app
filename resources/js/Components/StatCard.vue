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
        // [UPDATE] Warna premium Xaviera untuk Statistik
        xavieraBlue: 'bg-[#1C2541] text-white shadow-md border border-[#0B132B]',
        xavieraGold: 'bg-gradient-to-br from-[#D4AF37] to-[#B8860B] text-white shadow-lg border border-[#B8860B]',
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
    // [UBAH KHUSUS MOBILE & DESKTOP] Efek animasi angka yang sangat halus dan pelan (easeOutExpo)
    const duration = 2500 // Diperlambat menjadi 2.5 detik untuk efek premium
    const startTime = performance.now()

    const tick = (time) => {
        const progress = Math.min((time - startTime) / duration, 1)
        
        // Rumus Easing Out Expo: Berputar cepat di awal, sangat pelan di akhir
        const easeOutExpo = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress)
        
        displayValue.value = start + (end - start) * easeOutExpo

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
    <!-- [UBAH KHUSUS MOBILE] Kotak Grid: Premium glassmorphism tipis, shadow lembut, desktop tetap aslinya -->
    <div class="card relative overflow-hidden rounded-[20px] border border-slate-100/60 bg-white/95 p-4.5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm sm:rounded-xl sm:border sm:border-slate-200 sm:bg-white sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
        
        <!-- Efek cahaya tipis di dalam kotak khusus mobile untuk kesan mewah -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none lg:hidden"></div>

        <div class="relative z-10 flex items-start justify-between gap-3">
            <div>
                <!-- [UPDATE: FASE 2 TIPOGRAFI MOBILE]
                     Perbaikan: Label menggunakan text-[10px] di HP agar tidak kebesaran. -->
                <p class="text-[10px] font-black uppercase tracking-widest text-[#B8860B] sm:text-xs sm:font-bold">
                    {{ title }}
                </p>
                <!-- [UPDATE: FASE 2 TIPOGRAFI MOBILE]
                     Perbaikan: Angka nominal dikecilkan di HP menjadi text-[20px] dari sebelumnya text-[26px] 
                     yang memakan terlalu banyak memori vertikal, desktop dipertahankan. -->
                <p class="mt-1 text-[20px] font-black tracking-tight text-[#0B132B] sm:mt-2 sm:text-2xl font-['Cinzel']">
                    {{ formattedValue }}
                </p>
            </div>

            <!-- Icon Container -->
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl shadow-sm sm:h-11 sm:w-11 sm:rounded-xl sm:shadow-none" :class="colorClass">
                <i :class="['text-[18px] sm:text-lg', icon]" />
            </div>
        </div>
    </div>
</template>