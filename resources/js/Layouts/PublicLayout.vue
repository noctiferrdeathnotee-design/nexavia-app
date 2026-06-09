<script setup>
import { computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useFlash } from '@/composables/useFlash'

const page = usePage()

const authUser = computed(() => page.props.auth?.user || null)
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
    <div class="min-h-screen bg-slate-50 xl:bg-gradient-to-br xl:from-[#0B132B] xl:via-[#1A233A] xl:to-[#111827]">
        <!-- [UPDATE: FASE 3] Header Desktop Dark Aurora -->
        <header class="border-b border-slate-200 bg-white/95 backdrop-blur xl:border-white/10 xl:bg-[#0B132B]/95">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-3 py-2.5 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <img src="/images/logo-xaviera.jpg" alt="Nexavia"
                        class="h-9 w-9 rounded-xl border border-slate-200 object-contain drop-shadow-sm bg-white p-0.5 sm:h-10 sm:w-10">

                    <div>
                        <p class="text-sm font-semibold text-slate-800 xl:text-slate-100 transition-colors">Nexavia</p>
                        <p class="hidden text-xs text-slate-500 sm:block xl:text-slate-400">Sistem Manajemen Pengiriman</p>
                    </div>
                </div>

                <Link v-if="authUser" href="/dashboard" class="btn-secondary text-xs sm:text-sm !border-slate-200 xl:!border-white/10 xl:!bg-[#1A233A] xl:!text-slate-300 xl:hover:!text-[#D4AF37] transition-colors">
                    <i class="bi bi-speedometer2" />
                    <span class="hidden sm:inline">Dashboard</span>
                </Link>

                <Link v-else href="/login" class="btn-secondary text-xs sm:text-sm !border-slate-200 xl:!border-white/10 xl:!bg-[#1A233A] xl:!text-slate-300 xl:hover:!text-[#D4AF37] transition-colors">
                    <i class="bi bi-box-arrow-in-right" />
                    <span class="hidden sm:inline">Login Admin</span>
                </Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-3 py-4 sm:px-6 sm:py-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>