<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import axios from 'axios'
import { ref } from 'vue'

const nomorResi = ref('')
const processing = ref(false)

const normalizeResi = () => {
    nomorResi.value = String(nomorResi.value || '')
        .toUpperCase()
        .replace(/\s+/g, '')
}

const submit = async () => {
    normalizeResi()

    if (!nomorResi.value) {
        window.Swal?.fire({
            icon: 'warning',
            title: 'Nomor resi kosong',
            text: 'Silakan isi nomor resi terlebih dahulu.',
        })
        return
    }

    processing.value = true

    try {
        const response = await axios.post(route('tracking.cari'), {
            nomor_resi: nomorResi.value,
        })

        const redirectUrl = response.data?.redirect_url

        if (redirectUrl) {
            router.visit(redirectUrl)
        }
    } catch (error) {
        const message =
            error?.response?.data?.message ||
            'Nomor resi tidak ditemukan.'

        window.Swal?.fire({
            icon: 'error',
            title: 'Tracking gagal',
            text: message,
        })
    } finally {
        processing.value = false
    }
}
</script>

<template>

    <Head title="Tracking" />

    <PublicLayout>
        <div class="flex min-h-[65vh] items-center justify-center sm:min-h-[70vh]">
            <div class="w-full max-w-md">
                <div class="card p-5 sm:p-6">
                    <div class="mb-5 text-center">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white sm:h-16 sm:w-16">
                            <img src="/images/logo-brand.png" alt="SoftSend" class="h-9 w-9 object-contain sm:h-11 sm:w-11">
                        </div>

                        <h1 class="text-lg font-bold text-slate-800 sm:text-xl">
                            Lacak Pengiriman
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Masukkan nomor resi untuk melihat status dan lokasi pengiriman.
                        </p>
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="form-label">Nomor Resi</label>
                            <input v-model="nomorResi" type="text" class="form-input uppercase" @input="normalizeResi">
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center" :disabled="processing"
                            :class="{ 'opacity-60 cursor-not-allowed': processing }">
                            <span v-if="processing" class="inline-flex items-center gap-2">
                                <i class="bi bi-arrow-repeat animate-spin" />
                                Memproses...
                            </span>
                            <span v-else class="inline-flex items-center gap-2">
                                <i class="bi bi-search" />
                                Lacak
                            </span>
                        </button>
                    </form>

                    <div class="mt-5 text-right">
                        <a href="/login" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                            Login Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>