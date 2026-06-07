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
        <div class="relative flex min-h-[75vh] items-center justify-center sm:min-h-[80vh] overflow-hidden">
            <!-- Background Graphic Premium -->
            <div class="absolute inset-0 z-0 bg-slate-50">
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#1a365d]/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#b8860b]/10 rounded-full blur-3xl"></div>
            </div>

            <div class="w-full max-w-xl z-10 px-4">
                <div class="card p-6 sm:p-8 rounded-[32px] shadow-[0_20px_60px_rgb(0,0,0,0.08)] border border-white bg-white/80 backdrop-blur-xl">
                    <div class="mb-8 text-center">
                        <div
                            class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#1a365d] to-[#2a5298] shadow-lg shadow-indigo-900/20 sm:h-20 sm:w-20">
                            <i class="bi bi-box-seam text-white text-3xl sm:text-4xl"></i>
                        </div>

                        <h1 class="text-2xl font-black text-[#1a365d] tracking-tight sm:text-3xl mb-2">
                            Lacak Pengiriman
                        </h1>
                        <p class="text-sm text-slate-500 font-medium">
                            Masukkan nomor resi untuk melihat status dan lokasi paket Anda.
                        </p>
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div class="relative">
                            <label class="sr-only">Nomor Resi</label>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <i class="bi bi-upc-scan text-slate-400 text-xl"></i>
                            </div>
                            <input v-model="nomorResi" type="text" class="form-input block w-full pl-12 pr-4 py-4 text-lg font-bold tracking-widest text-slate-800 uppercase bg-slate-50/50 border-slate-200 focus:border-[#1a365d] focus:ring-[#1a365d] rounded-2xl shadow-inner placeholder:text-slate-400 placeholder:font-normal placeholder:tracking-normal" placeholder="NEXA..." @input="normalizeResi">
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center h-[56px] text-lg font-bold tracking-wide !rounded-2xl !bg-[#b8860b] hover:!bg-[#986f09] shadow-[0_8px_25px_rgb(184,134,11,0.3)] transition-all active:scale-[0.98] border-0" :disabled="processing"
                            :class="{ 'opacity-70 cursor-not-allowed': processing }">
                            <span v-if="processing" class="inline-flex items-center gap-2">
                                <i class="bi bi-arrow-repeat animate-spin text-xl" />
                                Mencari...
                            </span>
                            <span v-else class="inline-flex items-center gap-2">
                                <i class="bi bi-search text-lg" />
                                Lacak Sekarang
                            </span>
                        </button>
                    </form>

                    <div class="mt-8 text-center pt-6 border-t border-slate-100">
                        <a href="/login" class="inline-flex items-center gap-2 text-sm font-bold text-[#1a365d] hover:text-[#b8860b] transition-colors">
                            <i class="bi bi-shield-lock-fill"></i>
                            Login Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>