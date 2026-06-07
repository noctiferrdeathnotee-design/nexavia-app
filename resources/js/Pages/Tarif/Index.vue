<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, reactive, ref } from 'vue'

const props = defineProps({
    kotaList: {
        type: Array,
        default: () => [],
    },
})

const form = reactive({
    kota_asal_id: '',
    kota_tujuan_id: '',
    berat_kg: '',
})

const loading = ref(false)
const errorMessage = ref('')
const tarifList = ref([])
const hasChecked = ref(false)

const originCity = computed(() => {
    return props.kotaList.find((item) => String(item.id) === String(form.kota_asal_id)) || null
})

const destinationCity = computed(() => {
    return props.kotaList.find((item) => String(item.id) === String(form.kota_tujuan_id)) || null
})

const beratPreview = computed(() => {
    const value = Number(form.berat_kg || 0)
    return value > 0 ? value.toFixed(2) : '0.00'
})

const formatCurrency = (value) => {
    return `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(Number(value || 0)))}`
}

const formatService = (value) => {
    const map = {
        ekonomis: 'Ekonomis',
        reguler: 'Reguler',
        express: 'Express',
        same_day: 'Same Day',
    }

    return map[value] || value
}

const isFastest = (item) => {
    if (!tarifList.value.length) {
        return false
    }

    const min = Math.min(...tarifList.value.map((row) => Number(row.estimasi_hari || 0)))
    return Number(item.estimasi_hari || 0) === min
}

const isCheapest = (item) => {
    if (!tarifList.value.length) {
        return false
    }

    const min = Math.min(...tarifList.value.map((row) => Number(row.total || 0)))
    return Number(item.total || 0) === min
}

const showAlert = (icon, title, text) => {
    window.Swal?.fire({
        icon,
        title,
        text,
    })
}

const validateForm = () => {
    if (!form.kota_asal_id) {
        showAlert('warning', 'Kota asal belum dipilih', 'Silakan pilih kota asal.')
        return false
    }

    if (!form.kota_tujuan_id) {
        showAlert('warning', 'Kota tujuan belum dipilih', 'Silakan pilih kota tujuan.')
        return false
    }

    if (Number(form.berat_kg || 0) < 0.1) {
        showAlert('warning', 'Berat belum valid', 'Berat minimal 0.1 kg.')
        return false
    }

    return true
}

const submit = async () => {
    if (!validateForm()) {
        return
    }

    loading.value = true
    errorMessage.value = ''
    tarifList.value = []
    hasChecked.value = true

    try {
        const response = await axios.post(route('tarif.cek'), {
            kota_asal_id: Number(form.kota_asal_id),
            kota_tujuan_id: Number(form.kota_tujuan_id),
            berat_kg: Number(form.berat_kg),
        })

        tarifList.value = Array.isArray(response.data?.tarif) ? response.data.tarif : []
        errorMessage.value = response.data?.error || ''
    } catch (error) {
        const validationErrors = error?.response?.data?.errors
        const firstValidationError = validationErrors
            ? Object.values(validationErrors)[0]?.[0]
            : null

        errorMessage.value =
            firstValidationError ||
            error?.response?.data?.message ||
            'Gagal mengambil data tarif.'
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    form.kota_asal_id = ''
    form.kota_tujuan_id = ''
    form.berat_kg = ''
    loading.value = false
    errorMessage.value = ''
    tarifList.value = []
    hasChecked.value = false
}
</script>

<template>

    <Head title="Cek Tarif" />

    <AppLayout>
        <div class="space-y-4 sm:space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Cek Tarif</h2>
                <p class="text-sm text-slate-500">
                    Cek biaya pengiriman berdasarkan kota asal, tujuan, dan berat tagihan.
                </p>
            </div>

            <div class="card p-5 sm:p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-slate-100">
                <form class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-[1fr_1fr_120px_auto] items-end" @submit.prevent="submit">
                    <div>
                        <label class="form-label text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-1.5 block">Kota Asal</label>
                        <select v-model="form.kota_asal_id" class="form-select bg-slate-50/50 border-slate-200/80 focus:border-[#1a365d] focus:ring-[#1a365d] rounded-xl text-sm font-medium py-2.5">
                            <option value="" disabled>-- Pilih Kota Asal --</option>
                            <option v-for="kota in kotaList" :key="kota.id" :value="String(kota.id)">
                                {{ kota.nama_kota }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-1.5 block">Kota Tujuan</label>
                        <select v-model="form.kota_tujuan_id" class="form-select bg-slate-50/50 border-slate-200/80 focus:border-[#1a365d] focus:ring-[#1a365d] rounded-xl text-sm font-medium py-2.5">
                            <option value="" disabled>-- Pilih Kota Tujuan --</option>
                            <option v-for="kota in kotaList" :key="kota.id" :value="String(kota.id)">
                                {{ kota.nama_kota }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-[11px] font-bold uppercase tracking-widest text-slate-500 mb-1.5 block">Berat (kg)</label>
                        <input v-model="form.berat_kg" type="number" min="0.1" step="0.1" class="form-input bg-slate-50/50 border-slate-200/80 focus:border-[#1a365d] focus:ring-[#1a365d] rounded-xl text-sm font-medium py-2.5 text-center">
                    </div>

                    <div class="flex items-center gap-2 mt-2 md:mt-0">
                        <button type="submit" class="btn-primary w-full justify-center md:w-auto h-[42px] px-6 !rounded-xl !bg-[#1a365d] shadow-[0_4px_15px_rgb(26,54,93,0.3)] transition-all active:scale-95" :disabled="loading"
                            :class="{ 'cursor-not-allowed opacity-60': loading }">
                            <span v-if="loading" class="inline-flex items-center gap-2 font-semibold">
                                <i class="bi bi-arrow-repeat animate-spin text-lg" />
                                <span class="hidden sm:inline">Memproses...</span>
                            </span>

                            <span v-else class="inline-flex items-center gap-2 font-bold tracking-wide">
                                <i class="bi bi-search" />
                                Cek Tarif
                            </span>
                        </button>

                        <button v-if="hasChecked" type="button" class="btn-secondary justify-center h-[42px] w-[42px] !p-0 !rounded-xl" @click="resetForm" title="Reset">
                            <i class="bi bi-arrow-counterclockwise text-lg"></i>
                        </button>
                    </div>
                </form>

                <div v-if="originCity && destinationCity && form.berat_kg"
                    class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 flex flex-col sm:flex-row items-center justify-center gap-3 text-sm text-indigo-900 transition-all">
                    <div class="flex items-center gap-3">
                        <span class="font-bold bg-white px-3 py-1.5 rounded-lg shadow-sm border border-indigo-50">{{ originCity.nama_kota }}</span>
                        <i class="bi bi-arrow-right text-indigo-400"></i>
                        <span class="font-bold bg-white px-3 py-1.5 rounded-lg shadow-sm border border-indigo-50">{{ destinationCity.nama_kota }}</span>
                    </div>
                    <div class="hidden sm:block h-6 w-px bg-indigo-200"></div>
                    <div class="flex items-center gap-2 font-medium bg-white px-3 py-1.5 rounded-lg shadow-sm border border-indigo-50 text-slate-600">
                        <i class="bi bi-box-seam text-indigo-400"></i>
                        {{ beratPreview }} kg
                    </div>
                </div>

                <div v-if="errorMessage"
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ errorMessage }}
                </div>
            </div>

            <div v-if="loading" class="card p-5 text-sm text-slate-500">
                Sedang mengambil tarif layanan...
            </div>

            <div v-else-if="tarifList.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="item in tarifList" :key="item.jenis_layanan" class="card relative p-5 sm:p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_15px_40px_rgb(0,0,0,0.08)] transition-all duration-300 border border-slate-100 hover:border-indigo-100 flex flex-col group bg-white">
                    
                    <div class="absolute -top-3 left-0 right-0 flex justify-center z-10" v-if="isFastest(item) || isCheapest(item)">
                        <div class="flex gap-1">
                            <span v-if="isFastest(item)" class="badge bg-amber-500 text-white font-bold tracking-widest text-[10px] px-3 py-1 shadow-md border-2 border-white rounded-full">
                                <i class="bi bi-lightning-charge-fill mr-1"></i> TERCEPAT
                            </span>
                            <span v-if="isCheapest(item)" class="badge bg-emerald-500 text-white font-bold tracking-widest text-[10px] px-3 py-1 shadow-md border-2 border-white rounded-full">
                                <i class="bi bi-tag-fill mr-1"></i> TERHEMAT
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col items-center text-center mt-2 flex-1">
                        <h3 class="text-[13px] font-black uppercase tracking-widest text-[#1a365d] mb-4">
                            {{ formatService(item.jenis_layanan) }}
                        </h3>

                        <div class="flex items-baseline justify-center gap-1 mb-6">
                            <span class="text-sm font-bold text-slate-400">Rp</span>
                            <p class="text-3xl font-black tracking-tighter text-slate-800">
                                {{ new Intl.NumberFormat('id-ID').format(Math.round(Number(item.total || 0))) }}
                            </p>
                        </div>

                        <div class="w-full bg-slate-50 rounded-xl p-3 mb-6 border border-slate-100 flex justify-around">
                            <div class="text-center">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Estimasi</span>
                                <span class="block text-sm font-black text-slate-700 mt-0.5">{{ item.estimasi_hari }} <span class="text-[10px] text-slate-500 font-medium">Hari</span></span>
                            </div>
                            <div class="w-px h-full bg-slate-200"></div>
                            <div class="text-center">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tarif/kg</span>
                                <span class="block text-sm font-black text-slate-700 mt-0.5">{{ formatCurrency(item.per_kg) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <Link :href="route('pengiriman.create', {
                            asal_id: form.kota_asal_id,
                            tujuan_id: form.kota_tujuan_id,
                            layanan: item.jenis_layanan,
                            berat: form.berat_kg,
                        })" class="btn-primary w-full justify-center !rounded-xl font-bold tracking-wide !bg-[#b8860b] hover:!bg-[#986f09] border-0 shadow-[0_4px_15px_rgb(184,134,11,0.3)] h-[44px]">
                            Pilih Layanan Ini
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else-if="hasChecked && !errorMessage" class="card p-5 text-sm text-slate-500">
                Belum ada tarif yang tersedia untuk kombinasi rute ini.
            </div>
        </div>
    </AppLayout>
</template>