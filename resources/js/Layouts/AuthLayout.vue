<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { useFlash } from '@/composables/useFlash'

defineProps({
    title: {
        type: String,
        default: 'Login',
    },
})

const page = usePage()

const flashSuccess = computed(() => page.props.flash?.success || '')
const flashError = computed(() => page.props.flash?.error || '')

const { showSuccess, showError } = useFlash(page)

watch(flashSuccess, (value) => {
    showSuccess(value)
})

watch(flashError, (value) => {
    showError(value)
})
</script>

<template>
    <Head :title="title" />

    <div class="relative flex min-h-screen items-center justify-center bg-[#0F172A] p-4 sm:p-6 lg:p-8 overflow-hidden font-sans">
        <!-- Abstract Background Elements -->
        <div class="absolute left-0 top-0 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-900/30 blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 h-[400px] w-[400px] translate-x-1/3 translate-y-1/3 rounded-full bg-teal-900/20 blur-[100px] pointer-events-none"></div>

        <div class="relative z-10 mx-auto flex w-full max-w-[1100px] flex-col gap-6 lg:flex-row lg:items-stretch lg:gap-8 lg:min-h-[600px]">
            
            <!-- Left Panel: Glass Card (Illustration & Text) -->
            <div class="group relative hidden flex-1 flex-col justify-center overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.03] p-8 shadow-2xl backdrop-blur-md lg:flex lg:p-12">
                <!-- Inner glow -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent pointer-events-none"></div>
                
                <div class="relative z-10 mb-10 flex items-center gap-3">
                    <img src="/images/logo-brand.png" alt="Nexavia" class="h-7 w-7 object-contain drop-shadow-md opacity-90">
                    <span class="text-sm font-semibold tracking-wide text-slate-200">Nexavia Admin</span>
                </div>

                <div class="relative z-10 max-w-xl">
                    <h1 class="text-[2.5rem] font-bold leading-tight tracking-tight text-white mb-4">
                        Dashboard Operasional<br>
                        <span class="text-teal-400">Pengiriman Barang</span>
                    </h1>
                    <p class="mb-10 max-w-md text-sm leading-relaxed text-slate-300">
                        Masuk ke panel admin untuk memantau pengiriman, stok gudang,
                        tracking paket, laporan, dan pengaturan sistem Nexavia.
                    </p>

                    <!-- Metric Boxes -->
                    <div class="flex flex-wrap gap-3 mb-4">
                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 backdrop-blur-md">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Monitoring</p>
                            <p class="text-base font-bold text-white">24/7</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 backdrop-blur-md">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Tracking</p>
                            <p class="text-base font-bold text-white">Real-Time</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 backdrop-blur-md">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Reports</p>
                            <p class="text-base font-bold text-white">Exportable</p>
                        </div>
                    </div>
                </div>

                <!-- Logistics Illustration SVG -->
                <div class="absolute bottom-4 right-4 z-0 w-[55%] opacity-90 lg:bottom-8 lg:right-8 animate-[float_6s_ease-in-out_infinite]">
                    <!-- Clean SVG Illustration of Logistics (Box & Map Pin) -->
                    <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
                        <!-- Base Platform -->
                        <ellipse cx="200" cy="250" rx="140" ry="30" fill="#1E293B" opacity="0.6" />
                        
                        <!-- Abstract Warehouse -->
                        <path d="M120 170 L180 140 L280 140 L320 180 L320 230 L120 230 Z" fill="#1E293B" opacity="0.9" />
                        <path d="M180 140 L280 140 L280 230 L180 230 Z" fill="#0F172A" opacity="0.7" />
                        <rect x="200" y="190" width="20" height="40" fill="#2DD4BF" opacity="0.2" />
                        <rect x="240" y="190" width="20" height="40" fill="#2DD4BF" opacity="0.2" />

                        <!-- Delivery Box -->
                        <path d="M100 190 L160 160 L220 190 L160 220 Z" fill="#F8FAFC" />
                        <path d="M100 190 L160 220 L160 270 L100 240 Z" fill="#CBD5E1" />
                        <path d="M220 190 L160 220 L160 270 L220 240 Z" fill="#94A3B8" />
                        <path d="M140 170 L180 190 L170 195 L130 175 Z" fill="#2DD4BF" opacity="0.8" /> <!-- Tape -->

                        <!-- Map Pin -->
                        <path d="M250 100 C250 77.9 232.1 60 210 60 C187.9 60 170 77.9 170 100 C170 122.1 210 180 210 180 C210 180 250 122.1 250 100 Z" fill="#F43F5E" class="animate-[bounce_3s_ease-in-out_infinite]" />
                        <circle cx="210" cy="100" r="14" fill="white" class="animate-[bounce_3s_ease-in-out_infinite]" />
                        
                        <!-- Mini Truck (Abstract) -->
                        <path d="M260 220 L300 220 L315 235 L315 250 L260 250 Z" fill="#2DD4BF" />
                        <rect x="260" y="225" width="25" height="25" fill="#0F766E" />
                        <circle cx="275" cy="255" r="7" fill="#0F172A" />
                        <circle cx="305" cy="255" r="7" fill="#0F172A" />
                    </svg>
                </div>
            </div>

            <!-- Right Panel: Login Form -->
            <div class="flex w-full items-center justify-center lg:w-[400px] lg:flex-none">
                <div class="w-full max-w-[380px] animate-[slideUpFade_0.5s_ease-out]">
                    <slot />
                </div>
            </div>
            
        </div>
    </div>
</template>

<style>
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
    100% { transform: translateY(0px); }
}
@keyframes slideUpFade {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>