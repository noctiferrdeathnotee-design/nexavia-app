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
    <div class="min-h-screen bg-slate-50">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <img src="/images/logo-brand.png" alt="SoftSend"
                        class="h-10 w-10 rounded-xl border border-slate-200 object-contain p-1">

                    <div>
                        <p class="text-sm font-semibold text-slate-800">Nexavia</p>
                        <p class="text-xs text-slate-500">Sistem Manajemen Pengiriman</p>
                    </div>
                </div>

                <Link v-if="authUser" href="/dashboard" class="btn-secondary">
                    <i class="bi bi-speedometer2" />
                    Dashboard
                </Link>

                <Link v-else href="/login" class="btn-secondary">
                    <i class="bi bi-box-arrow-in-right" />
                    Login Admin
                </Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>