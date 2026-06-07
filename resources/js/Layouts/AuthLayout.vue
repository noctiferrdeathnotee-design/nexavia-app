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
        <!-- Abstract Background Elements (Sembunyikan di mobile agar sangat ringan 100% performance, tampilkan hanya di Desktop lg:block) -->
        <div class="absolute left-0 top-0 hidden h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-900/30 blur-[120px] pointer-events-none lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-[400px] w-[400px] translate-x-1/3 translate-y-1/3 rounded-full bg-teal-900/20 blur-[100px] pointer-events-none lg:block"></div>

        <div class="relative z-10 mx-auto flex w-full items-center justify-center min-h-[500px]">
            
            <!-- [UBAH KHUSUS DESKTOP & MOBILE] Panel Login Terpusat Tanpa Ilustrasi Animasi (Performa 100% Ringan) -->
            <div class="flex w-full items-center justify-center lg:w-[450px]">
                <div class="w-full max-w-[400px] sm:max-w-[380px] animate-[slideUpFade_0.4s_ease-out]">
                    <slot />
                </div>
            </div>
            
        </div>
    </div>
</template>

<style>
/* Menghapus keyframes float animasi untuk performa ekstra */
@keyframes slideUpFade {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>