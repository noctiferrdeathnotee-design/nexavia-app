<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import KotaInfoBox from '@/Components/KotaInfoBox.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, ref } from 'vue'

const props = defineProps({
    kotaList: {
        type: Array,
        default: () => [],
    },
    prefill: {
        type: Object,
        default: () => ({}),
    },
})

const step = ref(1)
const loadingTarif = ref(false)
const tarifError = ref('')
const tarifList = ref([])

const stepItems = [
    { no: 1, label: 'Pengirim' },
    { no: 2, label: 'Penerima' },
    { no: 3, label: 'Barang' },
    { no: 4, label: 'Layanan' },
]

const form = useForm({
    pengirim_nama: '',
    pengirim_hp: '',
    pengirim_alamat: '',
    pengirim_kota_id: props.prefill?.asal_id ? String(props.prefill.asal_id) : '',

    penerima_nama: '',
    penerima_hp: '',
    penerima_alamat: '',
    penerima_kota_id: props.prefill?.tujuan_id ? String(props.prefill.tujuan_id) : '',

    barang: [
        {
            nama_barang: '',
            berat_asli_kg: props.prefill?.berat ? String(props.prefill.berat) : '',
            panjang_cm: '',
            lebar_cm: '',
            tinggi_cm: '',
            keterangan: '',
        },
    ],

    catatan: '',
    jenis_layanan: props.prefill?.layanan || '',
    biaya_asuransi: 0,
    biaya_tambahan: 0,
    metode_pembayaran: 'dibayar_pengirim',
})

const swal = window.Swal

const kotaPengirim = computed(() => {
    return props.kotaList.find((item) => String(item.id) === String(form.pengirim_kota_id)) || null
})

const kotaPenerima = computed(() => {
    return props.kotaList.find((item) => String(item.id) === String(form.penerima_kota_id)) || null
})

const volumetrik = (barang) => {
    const p = parseFloat(barang.panjang_cm) || 0
    const l = parseFloat(barang.lebar_cm) || 0
    const t = parseFloat(barang.tinggi_cm) || 0

    return Number(((p * l * t) / 6000).toFixed(2))
}

const tagihan = (barang) => {
    const beratAsli = parseFloat(barang.berat_asli_kg) || 0
    return Number(Math.max(beratAsli, volumetrik(barang)).toFixed(2))
}

const totalBeratAsli = computed(() => {
    return Number(
        form.barang
            .reduce((total, item) => total + (parseFloat(item.berat_asli_kg) || 0), 0)
            .toFixed(2)
    )
})

const totalBeratVolumetrik = computed(() => {
    return Number(
        form.barang
            .reduce((total, item) => total + volumetrik(item), 0)
            .toFixed(2)
    )
})

const totalBeratTagihan = computed(() => {
    return Number(
        form.barang
            .reduce((total, item) => total + tagihan(item), 0)
            .toFixed(2)
    )
})

const jumlahBarang = computed(() => form.barang.length)

const selectedTarif = computed(() => {
    return tarifList.value.find((item) => item.jenis_layanan === form.jenis_layanan) || null
})

const ongkir = computed(() => {
    return Number(selectedTarif.value?.total || 0)
})

const biayaAsuransi = computed(() => Number(form.biaya_asuransi || 0))
const biayaTambahan = computed(() => Number(form.biaya_tambahan || 0))

const totalBiaya = computed(() => {
    return Number((ongkir.value + biayaAsuransi.value + biayaTambahan.value).toFixed(0))
})

const formatCurrency = (value) => {
    return `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(Number(value || 0)))}`
}

const formatWeight = (value) => {
    return `${Number(value || 0).toFixed(2)} kg`
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

const paymentLabel = (value) => {
    const map = {
        dibayar_pengirim: 'Dibayar Pengirim',
        dibayar_penerima: 'Dibayar Penerima',
        cod: 'COD',
    }

    return map[value] || value
}

const sanitizePhone = (field) => {
    form[field] = String(form[field] || '').replace(/\D/g, '')
}

const addBarang = () => {
    form.barang.push({
        nama_barang: '',
        berat_asli_kg: '',
        panjang_cm: '',
        lebar_cm: '',
        tinggi_cm: '',
        keterangan: '',
    })
}

const removeBarang = (index) => {
    if (form.barang.length <= 1) return
    form.barang.splice(index, 1)
}

const notify = (icon, title, text) => {
    swal?.fire({
        icon,
        title,
        text,
    })
}

const validatePhone = (value) => /^(08|628|62)\d{8,11}$/.test(String(value || '').trim())

const validateStep1 = () => {
    if (!form.pengirim_nama.trim()) {
        notify('warning', 'Data belum lengkap', 'Nama pengirim wajib diisi.')
        return false
    }

    if (!validatePhone(form.pengirim_hp)) {
        notify('warning', 'No HP tidak valid', 'Nomor HP pengirim tidak valid.')
        return false
    }

    if (String(form.pengirim_alamat || '').trim().length < 15) {
        notify('warning', 'Alamat terlalu pendek', 'Alamat pengirim minimal 15 karakter.')
        return false
    }

    if (!form.pengirim_kota_id) {
        notify('warning', 'Kota belum dipilih', 'Kota asal wajib dipilih.')
        return false
    }

    return true
}

const validateStep2 = () => {
    if (!form.penerima_nama.trim()) {
        notify('warning', 'Data belum lengkap', 'Nama penerima wajib diisi.')
        return false
    }

    if (!validatePhone(form.penerima_hp)) {
        notify('warning', 'No HP tidak valid', 'Nomor HP penerima tidak valid.')
        return false
    }

    if (String(form.penerima_alamat || '').trim().length < 15) {
        notify('warning', 'Alamat terlalu pendek', 'Alamat penerima minimal 15 karakter.')
        return false
    }

    if (!form.penerima_kota_id) {
        notify('warning', 'Kota belum dipilih', 'Kota tujuan wajib dipilih.')
        return false
    }

    return true
}

const validateStep3 = () => {
    const invalidIndex = form.barang.findIndex((item) => {
        return !item.nama_barang.trim() || Number(item.berat_asli_kg || 0) <= 0
    })

    if (invalidIndex !== -1) {
        notify('warning', 'Barang belum lengkap', `Barang ke-${invalidIndex + 1} belum lengkap.`)
        return false
    }

    if (totalBeratTagihan.value <= 0) {
        notify('warning', 'Berat belum valid', 'Total berat tagihan harus lebih dari 0 kg.')
        return false
    }

    return true
}

const validateStep4 = () => {
    if (!form.jenis_layanan) {
        notify('warning', 'Layanan belum dipilih', 'Silakan pilih layanan pengiriman.')
        return false
    }

    if (!form.metode_pembayaran) {
        notify('warning', 'Pembayaran belum dipilih', 'Silakan pilih metode pembayaran.')
        return false
    }

    return true
}

const goToStep = async (target) => {
    if (target === 2 && !validateStep1()) return
    if (target === 3 && !validateStep2()) return

    if (target === 4) {
        if (!validateStep3()) return
        step.value = 4
        await fetchTarif()
        return
    }

    step.value = target
}

const isCheapest = (item) => {
    if (!tarifList.value.length) return false
    const min = Math.min(...tarifList.value.map((row) => Number(row.total || 0)))
    return Number(item.total || 0) === min
}

const isFastest = (item) => {
    if (!tarifList.value.length) return false
    const min = Math.min(...tarifList.value.map((row) => Number(row.estimasi_hari || 0)))
    return Number(item.estimasi_hari || 0) === min
}

const fetchTarif = async () => {
    tarifError.value = ''
    tarifList.value = []
    loadingTarif.value = true

    try {
        const response = await axios.post(route('tarif.cek'), {
            kota_asal_id: Number(form.pengirim_kota_id),
            kota_tujuan_id: Number(form.penerima_kota_id),
            berat_kg: Number(totalBeratTagihan.value),
        })

        tarifList.value = Array.isArray(response.data?.tarif) ? response.data.tarif : []
        tarifError.value = response.data?.error || ''

        if (form.jenis_layanan) {
            const exists = tarifList.value.some((item) => item.jenis_layanan === form.jenis_layanan)

            if (!exists) {
                form.jenis_layanan = ''
            }
        }

        if (!form.jenis_layanan && tarifList.value.length === 1) {
            form.jenis_layanan = tarifList.value[0].jenis_layanan
        }
    } catch (error) {
        const message =
            error?.response?.data?.errors
                ? Object.values(error.response.data.errors)[0]?.[0]
                : error?.response?.data?.message || 'Gagal mengambil data tarif.'

        tarifError.value = message || 'Gagal mengambil data tarif.'
    } finally {
        loadingTarif.value = false
    }
}

const submit = async () => {
    if (!validateStep4()) return

    const result = await swal?.fire({
        icon: 'question',
        title: 'Simpan pengiriman?',
        html: `
            <div style="text-align:left;font-size:14px;line-height:1.6">
                <div><b>Rute:</b> ${kotaPengirim.value?.nama_kota || '-'} → ${kotaPenerima.value?.nama_kota || '-'}</div>
                <div><b>Layanan:</b> ${formatService(form.jenis_layanan)}</div>
                <div><b>Barang:</b> ${jumlahBarang.value}</div>
                <div><b>Berat Tagihan:</b> ${formatWeight(totalBeratTagihan.value)}</div>
                <div><b>Total:</b> ${formatCurrency(totalBiaya.value)}</div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4F46E5',
    })

    if (!result?.isConfirmed) {
        return
    }

    form
        .transform((data) => ({
            ...data,
            pengirim_kota_id: Number(data.pengirim_kota_id),
            penerima_kota_id: Number(data.penerima_kota_id),
            biaya_asuransi: Number(data.biaya_asuransi || 0),
            biaya_tambahan: Number(data.biaya_tambahan || 0),
            barang: data.barang.map((item) => ({
                ...item,
                berat_asli_kg: Number(item.berat_asli_kg || 0),
                panjang_cm: Number(item.panjang_cm || 0),
                lebar_cm: Number(item.lebar_cm || 0),
                tinggi_cm: Number(item.tinggi_cm || 0),
            })),
        }))
        .post(route('pengiriman.store'), {
            preserveScroll: true,
            onError: () => {
                notify('error', 'Validasi gagal', 'Periksa kembali data pengiriman Anda.')
            },
        })
}
</script>

<template>

    <Head title="Input Pengiriman" />

    <AppLayout>
        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Input Pengiriman</h2>
                    <p class="text-sm text-slate-500">
                        Form 4 langkah untuk membuat data pengiriman baru.
                    </p>
                </div>

                <Link :href="route('pengiriman.index')" class="btn-secondary w-full justify-center sm:w-auto">
                    <i class="bi bi-arrow-left" />
                    Kembali
                </Link>
            </div>

            <div class="card p-4">
                <div class="flex items-center justify-between gap-2 overflow-x-auto">
                    <div v-for="item in stepItems" :key="item.no" class="flex min-w-0 flex-1 items-center gap-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                            :class="item.no < step
                                ? 'bg-emerald-500 text-white'
                                : item.no === step
                                    ? 'bg-indigo-500 text-white'
                                    : 'bg-slate-100 text-slate-500'">
                            <i v-if="item.no < step" class="bi bi-check-lg" />
                            <span v-else>{{ item.no }}</span>
                        </div>

                        <div class="hidden min-w-0 sm:block">
                            <p class="truncate text-sm font-medium text-slate-700">
                                {{ item.label }}
                            </p>
                        </div>

                        <div v-if="item.no !== stepItems.length" class="hidden h-px flex-1 bg-slate-200 sm:block" />
                    </div>
                </div>
            </div>

            <div v-if="Object.keys(form.errors).length"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                Ada validasi yang belum sesuai. Periksa kembali form Anda.
            </div>

            <div v-if="step === 1" class="card p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-800">Step 1 — Data Pengirim</h3>
                    <p class="text-xs text-slate-500">Isi data pengirim dan pilih kota asal.</p>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="max-w-xs">
                        <label class="form-label">Nama Pengirim</label>
                        <input v-model="form.pengirim_nama" type="text" class="form-input">
                        <p v-if="form.errors.pengirim_nama" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_nama }}
                        </p>
                    </div>

                    <div class="max-w-[180px]">
                        <label class="form-label">No HP</label>
                        <input v-model="form.pengirim_hp" type="tel" inputmode="numeric" class="form-input"
                            @input="sanitizePhone('pengirim_hp')">
                        <p v-if="form.errors.pengirim_hp" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_hp }}
                        </p>
                    </div>

                    <div class="w-full">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea v-model="form.pengirim_alamat" rows="2" class="form-input"></textarea>
                        <p v-if="form.errors.pengirim_alamat" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_alamat }}
                        </p>
                    </div>

                    <div class="max-w-xs">
                        <label class="form-label">Kota Asal</label>
                        <select v-model="form.pengirim_kota_id" class="form-select">
                            <option value="" disabled>-- Pilih --</option>
                            <option v-for="kota in kotaList" :key="kota.id" :value="String(kota.id)">
                                {{ kota.nama_kota }}
                            </option>
                        </select>
                        <p v-if="form.errors.pengirim_kota_id" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_kota_id }}
                        </p>
                    </div>

                    <div class="max-w-sm">
                        <KotaInfoBox :kota="kotaPengirim" />
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="button" class="btn-primary" @click="goToStep(2)">
                        Selanjutnya
                        <i class="bi bi-arrow-right" />
                    </button>
                </div>
            </div>

            <div v-else-if="step === 2" class="card p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-800">Step 2 — Data Penerima</h3>
                    <p class="text-xs text-slate-500">Isi data penerima dan pilih kota tujuan.</p>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="max-w-xs">
                        <label class="form-label">Nama Penerima</label>
                        <input v-model="form.penerima_nama" type="text" class="form-input">
                        <p v-if="form.errors.penerima_nama" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_nama }}
                        </p>
                    </div>

                    <div class="max-w-[180px]">
                        <label class="form-label">No HP</label>
                        <input v-model="form.penerima_hp" type="tel" inputmode="numeric" class="form-input"
                            @input="sanitizePhone('penerima_hp')">
                        <p v-if="form.errors.penerima_hp" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_hp }}
                        </p>
                    </div>

                    <div class="w-full">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea v-model="form.penerima_alamat" rows="2" class="form-input"></textarea>
                        <p v-if="form.errors.penerima_alamat" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_alamat }}
                        </p>
                    </div>

                    <div class="max-w-xs">
                        <label class="form-label">Kota Tujuan</label>
                        <select v-model="form.penerima_kota_id" class="form-select">
                            <option value="" disabled>-- Pilih --</option>
                            <option v-for="kota in kotaList" :key="kota.id" :value="String(kota.id)">
                                {{ kota.nama_kota }}
                            </option>
                        </select>
                        <p v-if="form.errors.penerima_kota_id" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_kota_id }}
                        </p>
                    </div>

                    <div class="max-w-sm">
                        <KotaInfoBox :kota="kotaPenerima" />
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-between">
                    <button type="button" class="btn-secondary justify-center" @click="step = 1">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </button>

                    <button type="button" class="btn-primary justify-center" @click="goToStep(3)">
                        Selanjutnya
                        <i class="bi bi-arrow-right" />
                    </button>
                </div>
            </div>

            <div v-else-if="step === 3" class="card p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-800">Step 3 — Detail Barang</h3>
                    <p class="text-xs text-slate-500">Isi seluruh barang dan dimensi untuk hitung volumetrik.</p>
                </div>

                <div class="space-y-4">
                    <div v-for="(item, index) in form.barang" :key="index"
                        class="rounded-xl border border-slate-200 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-semibold text-slate-700">
                                Barang #{{ index + 1 }}
                            </h4>

                            <button v-if="form.barang.length > 1" type="button" class="btn-danger btn-sm"
                                @click="removeBarang(index)">
                                <i class="bi bi-trash" />
                                Hapus
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                            <div class="max-w-xs xl:col-span-2">
                                <label class="form-label">Nama Barang</label>
                                <input v-model="item.nama_barang" type="text" class="form-input">
                                <p v-if="form.errors[`barang.${index}.nama_barang`]" class="mt-1 text-xs text-red-500">
                                    {{ form.errors[`barang.${index}.nama_barang`] }}
                                </p>
                            </div>

                            <div class="max-w-[110px]">
                                <label class="form-label">Berat Asli (kg)</label>
                                <input v-model="item.berat_asli_kg" type="number" min="0.1" step="0.1"
                                    class="form-input">
                                <p v-if="form.errors[`barang.${index}.berat_asli_kg`]"
                                    class="mt-1 text-xs text-red-500">
                                    {{ form.errors[`barang.${index}.berat_asli_kg`] }}
                                </p>
                            </div>

                            <div class="max-w-[90px]">
                                <label class="form-label">P cm</label>
                                <input v-model="item.panjang_cm" type="number" min="0" step="0.1" class="form-input">
                            </div>

                            <div class="max-w-[90px]">
                                <label class="form-label">L cm</label>
                                <input v-model="item.lebar_cm" type="number" min="0" step="0.1" class="form-input">
                            </div>

                            <div class="max-w-[90px]">
                                <label class="form-label">T cm</label>
                                <input v-model="item.tinggi_cm" type="number" min="0" step="0.1" class="form-input">
                            </div>

                            <div class="w-full max-w-sm md:col-span-2 xl:col-span-3">
                                <label class="form-label">Keterangan</label>
                                <input v-model="item.keterangan" type="text" class="form-input">
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                                <p class="text-[11px] text-blue-500">Berat Volumetrik</p>
                                <p class="font-semibold">{{ formatWeight(volumetrik(item)) }}</p>
                            </div>

                            <div
                                class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-700">
                                <p class="text-[11px] text-indigo-500">Berat Tagihan</p>
                                <p class="font-semibold">{{ formatWeight(tagihan(item)) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-start">
                        <button type="button" class="btn-secondary" @click="addBarang">
                            <i class="bi bi-plus-lg" />
                            Tambah Barang
                        </button>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                            <div>
                                <p class="text-xs text-slate-500">Jumlah Barang</p>
                                <p class="font-semibold text-slate-800">{{ jumlahBarang }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Total Berat Asli</p>
                                <p class="font-semibold text-slate-800">{{ formatWeight(totalBeratAsli) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Total Volumetrik</p>
                                <p class="font-semibold text-slate-800">{{ formatWeight(totalBeratVolumetrik) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Total Tagihan</p>
                                <p class="font-semibold text-indigo-600">{{ formatWeight(totalBeratTagihan) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full max-w-lg">
                        <label class="form-label">Catatan</label>
                        <textarea v-model="form.catatan" rows="2" class="form-input"></textarea>
                        <p v-if="form.errors.catatan" class="mt-1 text-xs text-red-500">
                            {{ form.errors.catatan }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-between">
                    <button type="button" class="btn-secondary justify-center" @click="step = 2">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </button>

                    <button type="button" class="btn-primary justify-center" @click="goToStep(4)">
                        Selanjutnya
                        <i class="bi bi-arrow-right" />
                    </button>
                </div>
            </div>

            <div v-else class="card p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-800">Step 4 — Layanan & Pembayaran</h3>
                    <p class="text-xs text-slate-500">Pilih layanan, isi biaya tambahan, lalu simpan.</p>
                </div>

                <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <div class="flex flex-col gap-1 lg:flex-row lg:flex-wrap lg:items-center lg:gap-3">
                        <span><b>Rute:</b> {{ kotaPengirim?.nama_kota || '-' }} → {{ kotaPenerima?.nama_kota || '-'
                            }}</span>
                        <span><b>Berat:</b> {{ formatWeight(totalBeratTagihan) }}</span>
                        <span><b>Barang:</b> {{ jumlahBarang }}</span>
                    </div>
                </div>

                <div v-if="loadingTarif"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                    Mengambil tarif layanan...
                </div>

                <div v-else-if="tarifError"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-600">
                    {{ tarifError }}
                </div>

                <div v-else class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <button v-for="item in tarifList" :key="item.jenis_layanan" type="button"
                            class="relative rounded-xl border p-4 text-left transition-colors" :class="item.jenis_layanan === form.jenis_layanan
                                ? 'border-2 border-indigo-500 bg-indigo-50'
                                : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-slate-50'"
                            @click="form.jenis_layanan = item.jenis_layanan">
                            <div class="mb-2 flex flex-wrap gap-1">
                                <span v-if="isFastest(item)" class="badge bg-amber-100 text-amber-700">
                                    TERCEPAT
                                </span>

                                <span v-if="isCheapest(item)" class="badge bg-emerald-100 text-emerald-700">
                                    TERHEMAT
                                </span>
                            </div>

                            <p class="text-sm font-semibold text-slate-800">
                                {{ formatService(item.jenis_layanan) }}
                            </p>
                            <p class="mt-2 text-lg font-bold text-indigo-600">
                                {{ formatCurrency(item.total) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Estimasi {{ item.estimasi_hari }} hari
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ formatCurrency(item.per_kg) }}/kg
                            </p>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <div class="mb-4 max-w-[160px]">
                                <label class="form-label">Biaya Asuransi</label>
                                <input v-model="form.biaya_asuransi" type="number" min="0" step="1000"
                                    class="form-input">
                            </div>

                            <div class="max-w-[160px]">
                                <label class="form-label">Biaya Tambahan</label>
                                <input v-model="form.biaya_tambahan" type="number" min="0" step="1000"
                                    class="form-input">
                            </div>

                            <div class="mt-5">
                                <label class="form-label">Metode Pembayaran</label>

                                <div class="space-y-2">
                                    <label
                                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">
                                        <input v-model="form.metode_pembayaran" type="radio" value="dibayar_pengirim">
                                        <span>Dibayar Pengirim</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">
                                        <input v-model="form.metode_pembayaran" type="radio" value="dibayar_penerima">
                                        <span>Dibayar Penerima</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">
                                        <input v-model="form.metode_pembayaran" type="radio" value="cod">
                                        <span>COD</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                            <h4 class="text-sm font-semibold text-slate-800">Ringkasan Biaya</h4>

                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-600">Ongkir</span>
                                    <span class="font-medium text-slate-800">{{ formatCurrency(ongkir) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-600">Asuransi</span>
                                    <span class="font-medium text-slate-800">{{ formatCurrency(biayaAsuransi) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-600">Biaya Tambahan</span>
                                    <span class="font-medium text-slate-800">{{ formatCurrency(biayaTambahan) }}</span>
                                </div>

                                <div class="my-3 border-t border-indigo-200" />

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-slate-800">Total</span>
                                    <span class="text-lg font-bold text-indigo-600">{{ formatCurrency(totalBiaya)
                                        }}</span>
                                </div>

                                <div
                                    class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs text-slate-600">
                                    <div><b>Layanan:</b> {{ form.jenis_layanan ? formatService(form.jenis_layanan) : '-'
                                        }}</div>
                                    <div><b>Pembayaran:</b> {{ paymentLabel(form.metode_pembayaran) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-between">
                    <button type="button" class="btn-secondary justify-center" @click="step = 3">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </button>

                    <button type="button" class="btn-primary justify-center"
                        :disabled="form.processing || !selectedTarif"
                        :class="{ 'opacity-60 cursor-not-allowed': form.processing || !selectedTarif }" @click="submit">
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <i class="bi bi-arrow-repeat animate-spin" />
                            Menyimpan...
                        </span>

                        <span v-else class="inline-flex items-center gap-2">
                            <i class="bi bi-check2-circle" />
                            Simpan Pengiriman
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>