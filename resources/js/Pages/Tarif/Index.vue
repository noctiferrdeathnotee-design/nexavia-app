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

            <div class="card p-3 sm:p-4">
                <form class="grid grid-cols-2 gap-2 sm:gap-3 xl:grid-cols-[1fr_1fr_140px_auto]" @submit.prevent="submit">
                    <div class="col-span-1">
                        <label class="form-label">Kota Asal</label>
                        <select v-model="form.kota_asal_id" class="form-select">
                            <option value="" disabled>-- Pilih --</option>
                            <option v-for="kota in kotaList" :key="kota.id" :value="String(kota.id)">
                                {{ kota.nama_kota }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-1">
                        <label class="form-label">Kota Tujuan</label>
                        <select v-model="form.kota_tujuan_id" class="form-select">
                            <option value="" disabled>-- Pilih --</option>
                            <option v-for="kota in kotaList" :key="kota.id" :value="String(kota.id)">
                                {{ kota.nama_kota }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-1">
                        <label class="form-label">Berat (kg)</label>
                        <input v-model="form.berat_kg" type="number" min="0.1" step="0.1" class="form-input">
                    </div>

                    <div class="col-span-1 flex items-end gap-2">
                        <button type="submit" class="btn-primary w-full justify-center xl:w-auto" :disabled="loading"
                            :class="{ 'cursor-not-allowed opacity-60': loading }">
                            <span v-if="loading" class="inline-flex items-center gap-2">
                                <i class="bi bi-arrow-repeat animate-spin" />
                                Cek...
                            </span>

                            <span v-else class="inline-flex items-center gap-2">
                                <i class="bi bi-search" />
                                Cek
                            </span>
                        </button>

                        <button v-if="hasChecked" type="button" class="btn-secondary justify-center" @click="resetForm">
                            Reset
                        </button>
                    </div>
                </form>

                <div v-if="originCity && destinationCity && form.berat_kg"
                    class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                    <span class="font-medium text-slate-700">{{ originCity.nama_kota }}</span>
                    →
                    <span class="font-medium text-slate-700">{{ destinationCity.nama_kota }}</span>
                    · Berat:
                    <span class="font-medium text-slate-700">{{ beratPreview }} kg</span>
                </div>

                <div v-if="errorMessage"
                    class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-600">
                    {{ errorMessage }}
                </div>
            </div>

            <div v-if="loading" class="card p-5 text-sm text-slate-500">
                Sedang mengambil tarif layanan...
            </div>

            <div v-else-if="tarifList.length" class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
                <div v-for="item in tarifList" :key="item.jenis_layanan" class="card p-4">
                    <div class="mb-3 flex flex-wrap gap-1">
                        <span v-if="isFastest(item)" class="badge bg-amber-100 text-amber-700">
                            TERCEPAT
                        </span>

                        <span v-if="isCheapest(item)" class="badge bg-emerald-100 text-emerald-700">
                            TERHEMAT
                        </span>
                    </div>

                    <h3 class="text-base font-semibold text-slate-800">
                        {{ formatService(item.jenis_layanan) }}
                    </h3>

                    <p class="mt-3 text-xl font-bold text-indigo-600 sm:text-2xl">
                        {{ formatCurrency(item.total) }}
                    </p>

                    <div class="mt-3 space-y-1 text-sm text-slate-500">
                        <p>Estimasi: {{ item.estimasi_hari }} hari</p>
                        <p>Tarif: {{ formatCurrency(item.per_kg) }}/kg</p>
                    </div>

                    <div class="mt-4">
                        <Link :href="route('pengiriman.create', {
                            asal_id: form.kota_asal_id,
                            tujuan_id: form.kota_tujuan_id,
                            layanan: item.jenis_layanan,
                            berat: form.berat_kg,
                        })" class="btn-primary w-full justify-center">
                            Pilih Ini
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