<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import KotaInfoBox from '@/Components/KotaInfoBox.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue' // [UBAH KHUSUS MOBILE & DESKTOP] Impor Dropdown Pencarian Pintar
import ToggleSwitch from '@/Components/ToggleSwitch.vue' // [UPDATE: FASE 3]
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

// [UPDATE: FASE 3] State reaktif untuk form interaktif
const useAsuransi = ref(false)
const useBiayaTambahan = ref(false)
const hasCatatan = ref(false)

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
            hasKeterangan: false, // [UPDATE: FASE 3] Kontrol visibility keterangan per barang
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
        hasKeterangan: false, // [UPDATE: FASE 3]
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
        <!-- [UBAH KHUSUS MOBILE] Padding bawah ekstra agar konten tidak tertutup oleh tombol sticky -->
        <div class="space-y-4 sm:space-y-5 pb-28 sm:pb-0">
            <!-- [UPDATE: FASE 7 ULTRA-PREMIUM NATIVE FORM] Header terpisah -->
            <!-- DESKTOP HEADER (100% Tidak Disentuh) -->
            <div class="hidden sm:flex flex-row items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Input Pengiriman</h2>
                    <p class="text-sm text-slate-500">
                        Form 4 langkah untuk membuat data pengiriman baru.
                    </p>
                </div>
                <Link :href="route('pengiriman.index')" class="btn-secondary w-auto">
                    <i class="bi bi-arrow-left" />
                    Kembali
                </Link>
            </div>

            <!-- MOBILE HEADER (Native App Feel) -->
            <div class="flex sm:hidden items-center justify-between mb-2">
                <Link :href="route('pengiriman.index')" class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-[0_2px_10px_rgba(0,0,0,0.03)] border border-slate-100 transition-transform active:scale-95">
                    <i class="bi bi-arrow-left text-lg" />
                </Link>
                <h2 class="text-[17px] font-bold text-[#0B132B] tracking-tight">Input Pengiriman</h2>
                <div class="w-10"></div> <!-- Spacer -->
            </div>

            <!-- [UPDATE: FASE 7] Liquid Progress Bar (Tanpa Card Background di Mobile) -->
            <div class="px-2 py-1 sm:card sm:p-4">
                <div class="flex items-center justify-between gap-1.5 overflow-x-auto sm:gap-2">
                    <div v-for="item in stepItems" :key="item.no" class="flex min-w-0 flex-1 items-center gap-2">
                        
                        <!-- Lingkaran Desktop (Tidak Disentuh) -->
                        <div class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold sm:flex"
                            :class="item.no < step ? 'bg-emerald-500 text-white' : item.no === step ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500'">
                            <i v-if="item.no < step" class="bi bi-check-lg" />
                            <span v-else>{{ item.no }}</span>
                        </div>

                        <!-- Lingkaran Mobile (Premium Xaviera Gold) -->
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold shadow-sm transition-all duration-300 sm:hidden"
                            :class="item.no < step ? 'bg-[#0B132B] text-[#D4AF37]' : item.no === step ? 'bg-gradient-to-br from-[#D4AF37] to-[#B8860B] text-white ring-4 ring-[#D4AF37]/20 shadow-[0_4px_15px_rgba(212,175,55,0.4)]' : 'bg-white text-slate-400 border border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)]'">
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

            <div v-if="step === 1" class="p-4 bg-white/60 backdrop-blur-md border border-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:card sm:rounded-xl sm:bg-white sm:border-slate-200 sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-[#0B132B] sm:text-slate-800">Step 1 — Data Pengirim</h3>
                    <p class="text-xs text-slate-500">Isi data pengirim dan pilih kota asal.</p>
                </div>

                <!-- [UPDATE: FASE 2] Horizontal Layout untuk Desktop (grid-cols-4) -->
                <div class="grid grid-cols-1 gap-4 sm:gap-4 xl:grid-cols-4 xl:gap-5">
                    <div class="w-full xl:col-span-2">
                        <label class="form-label">Nama Pengirim</label>
                        <input v-model="form.pengirim_nama" type="text" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
                        <p v-if="form.errors.pengirim_nama" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_nama }}
                        </p>
                    </div>

                    <div class="w-full xl:col-span-2">
                        <label class="form-label">No HP</label>
                        <input v-model="form.pengirim_hp" type="tel" inputmode="numeric" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent"
                            @input="sanitizePhone('pengirim_hp')">
                        <p v-if="form.errors.pengirim_hp" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_hp }}
                        </p>
                    </div>

                    <div class="w-full xl:col-span-4">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea v-model="form.pengirim_alamat" rows="2" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] py-3 focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent"></textarea>
                        <p v-if="form.errors.pengirim_alamat" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_alamat }}
                        </p>
                    </div>

                    <div class="w-full relative z-30 xl:col-span-2">
                        <label class="form-label font-medium tracking-tight">Kota Asal</label>
                        <SearchableSelect 
                            v-model="form.pengirim_kota_id"
                            :options="kotaList"
                            placeholder="Ketik & Pilih Kota Asal"
                        />
                        <p v-if="form.errors.pengirim_kota_id" class="mt-1 text-xs text-red-500">
                            {{ form.errors.pengirim_kota_id }}
                        </p>
                    </div>

                    <div class="w-full sm:max-w-sm xl:col-span-2">
                        <KotaInfoBox :kota="kotaPengirim" />
                    </div>
                </div>

                <!-- [UPDATE: FASE 7.1] The Royal Action Bar (Static Flow di Mobile agar tidak bentrok / menutupi dropdown) -->
                <div class="mt-6 flex flex-col gap-3 sm:mt-5 sm:flex-row sm:justify-end">
                    <!-- Desktop -->
                    <button type="button" class="hidden sm:inline-flex btn-primary w-auto rounded-lg" @click="goToStep(2)">
                        Selanjutnya
                        <i class="bi bi-arrow-right" />
                    </button>
                    <!-- Mobile -->
                    <button type="button" class="flex sm:hidden w-full items-center justify-center gap-2 rounded-[16px] bg-[#0B132B] px-4 py-3.5 text-[15px] font-bold tracking-wide text-[#D4AF37] shadow-[0_8px_20px_rgba(11,19,43,0.2)] transition-transform hover:scale-[0.98] active:scale-95" @click="goToStep(2)">
                        Selanjutnya
                        <i class="bi bi-arrow-right font-bold text-lg" />
                    </button>
                </div>
            </div>

            <div v-else-if="step === 2" class="p-4 bg-white/60 backdrop-blur-md border border-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:card sm:rounded-xl sm:bg-white sm:border-slate-200 sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-[#0B132B] sm:text-slate-800">Step 2 — Data Penerima</h3>
                    <p class="text-xs text-slate-500">Isi data penerima dan pilih kota tujuan.</p>
                </div>

                <!-- [UPDATE: FASE 2] Horizontal Layout untuk Desktop (grid-cols-4) -->
                <div class="grid grid-cols-1 gap-4 sm:gap-4 xl:grid-cols-4 xl:gap-5">
                    <div class="w-full xl:col-span-2">
                        <label class="form-label">Nama Penerima</label>
                        <input v-model="form.penerima_nama" type="text" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
                        <p v-if="form.errors.penerima_nama" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_nama }}
                        </p>
                    </div>

                    <div class="w-full xl:col-span-2">
                        <label class="form-label">No HP</label>
                        <input v-model="form.penerima_hp" type="tel" inputmode="numeric" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent"
                            @input="sanitizePhone('penerima_hp')">
                        <p v-if="form.errors.penerima_hp" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_hp }}
                        </p>
                    </div>

                    <div class="w-full xl:col-span-4">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea v-model="form.penerima_alamat" rows="2" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] py-3 focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent"></textarea>
                        <p v-if="form.errors.penerima_alamat" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_alamat }}
                        </p>
                    </div>

                    <div class="w-full relative z-30 xl:col-span-2">
                        <label class="form-label font-medium tracking-tight">Kota Tujuan</label>
                        <SearchableSelect 
                            v-model="form.penerima_kota_id"
                            :options="kotaList"
                            placeholder="Ketik & Pilih Kota Tujuan"
                        />
                        <p v-if="form.errors.penerima_kota_id" class="mt-1 text-xs text-red-500">
                            {{ form.errors.penerima_kota_id }}
                        </p>
                    </div>

                    <div class="w-full sm:max-w-sm xl:col-span-2">
                        <KotaInfoBox :kota="kotaPenerima" />
                    </div>
                </div>

                <!-- [UPDATE: FASE 7.1] The Royal Action Bar (Static Flow di Mobile) -->
                <div class="mt-6 flex flex-col gap-3 sm:mt-5 sm:flex-row sm:justify-between">
                    <!-- Desktop -->
                    <button type="button" class="hidden sm:inline-flex btn-secondary w-auto justify-center rounded-lg" @click="step = 1">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </button>
                    <button type="button" class="hidden sm:inline-flex btn-primary w-auto justify-center rounded-lg" @click="goToStep(3)">
                        Selanjutnya
                        <i class="bi bi-arrow-right" />
                    </button>

                    <!-- Mobile -->
                    <div class="flex sm:hidden gap-3 w-full">
                        <button type="button" class="flex-1 items-center justify-center gap-2 rounded-[16px] bg-slate-100 px-4 py-3.5 text-[15px] font-bold tracking-wide text-slate-600 transition-transform active:scale-95" @click="step = 1">
                            Kembali
                        </button>
                        <button type="button" class="flex-[2] items-center justify-center gap-2 rounded-[16px] bg-[#0B132B] px-4 py-3.5 text-[15px] font-bold tracking-wide text-[#D4AF37] shadow-[0_8px_20px_rgba(11,19,43,0.2)] transition-transform hover:scale-[0.98] active:scale-95" @click="goToStep(3)">
                            Selanjutnya
                            <i class="bi bi-arrow-right font-bold text-lg" />
                        </button>
                    </div>
                </div>
            </div>

            <div v-else-if="step === 3" class="p-4 bg-white/60 backdrop-blur-md border border-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:card sm:rounded-xl sm:bg-white sm:border-slate-200 sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-[#0B132B] sm:text-slate-800">Step 3 — Detail Barang</h3>
                    <p class="text-xs text-slate-500">Isi seluruh barang dan dimensi untuk hitung volumetrik.</p>
                </div>

                <div class="space-y-4">
                    <!-- [UPDATE: FASE 8] Premium Glass Card untuk item barang di Mobile. Di desktop kembali ke border-slate-200 biasa -->
                    <div v-for="(item, index) in form.barang" :key="index"
                        class="bg-white/80 backdrop-blur-md border border-white rounded-[20px] shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-4 sm:bg-transparent sm:border-slate-200 sm:rounded-xl sm:shadow-none sm:p-4">
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

                        <!-- [UPDATE: FASE 8] Smart Grid. P,L,T digabung ke dalam 1 sub-grid (grid-cols-3) khusus di mobile agar hemat tinggi layar -->
                        <div class="grid grid-cols-3 gap-3 sm:gap-4 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-6">
                            <div class="col-span-3 w-full sm:col-span-2 sm:max-w-xs xl:col-span-2">
                                <label class="form-label">Nama Barang</label>
                                <input v-model="item.nama_barang" type="text" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
                                <p v-if="form.errors[`barang.${index}.nama_barang`]" class="mt-1 text-xs text-red-500">
                                    {{ form.errors[`barang.${index}.nama_barang`] }}
                                </p>
                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                <label class="form-label">Berat Asli (kg)</label>
                                <input v-model="item.berat_asli_kg" type="number" min="0.1" step="0.1"
                                    class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
                                <p v-if="form.errors[`barang.${index}.berat_asli_kg`]"
                                    class="mt-1 text-xs text-red-500">
                                    {{ form.errors[`barang.${index}.berat_asli_kg`] }}
                                </p>
                            </div>

                            <div class="col-span-1">
                                <label class="form-label">P cm</label>
                                <input v-model="item.panjang_cm" type="number" min="0" step="0.1" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 text-center sm:text-left xl:bg-transparent">
                            </div>

                            <div class="col-span-1">
                                <label class="form-label">L cm</label>
                                <input v-model="item.lebar_cm" type="number" min="0" step="0.1" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 text-center sm:text-left xl:bg-transparent">
                            </div>

                            <div class="col-span-1">
                                <label class="form-label">T cm</label>
                                <input v-model="item.tinggi_cm" type="number" min="0" step="0.1" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 text-center sm:text-left xl:bg-transparent">
                            </div>

                            <!-- [UPDATE: FASE 3] Checkbox Opsional Keterangan per barang -->
                            <div class="col-span-3 w-full sm:col-span-2 xl:col-span-6 flex items-center gap-2 mt-1 mb-1">
                                <input type="checkbox" v-model="item.hasKeterangan" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 xl:border-white/20 xl:bg-[#1A233A]">
                                <label class="text-sm font-medium text-slate-600 xl:text-slate-400 cursor-pointer select-none" @click="item.hasKeterangan = !item.hasKeterangan">
                                    Tambah Keterangan Barang
                                </label>
                            </div>

                            <div v-show="item.hasKeterangan" class="col-span-3 w-full sm:col-span-2 sm:max-w-sm xl:col-span-6 transition-all duration-300">
                                <label class="form-label">Isi Keterangan</label>
                                <input v-model="item.keterangan" type="text" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
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

                    <!-- [UPDATE: FASE 8] Tombol Tambah Barang bergaya Dashed Gold di Mobile, kembali normal di Desktop -->
                    <div class="flex justify-start">
                        <!-- Desktop -->
                        <button type="button" class="hidden sm:inline-flex btn-secondary" @click="addBarang">
                            <i class="bi bi-plus-lg" />
                            Tambah Barang
                        </button>
                        <!-- Mobile -->
                        <button type="button" class="flex sm:hidden w-full items-center justify-center gap-2 rounded-[16px] border-2 border-dashed border-[#D4AF37]/60 px-4 py-3.5 text-[14px] font-bold tracking-wide text-[#B8860B] transition-transform hover:bg-[#D4AF37]/5 active:scale-95" @click="addBarang">
                            <i class="bi bi-plus-lg text-lg" />
                            Tambah Barang Lainnya
                        </button>
                    </div>

                    <!-- [UPDATE: FASE 8] Mini Dashboard Midnight Blue di Mobile, kembali normal di Desktop -->
                    <div class="rounded-[24px] bg-[#0B132B] p-4 shadow-[0_8px_30px_rgba(11,19,43,0.15)] sm:rounded-xl sm:border sm:border-slate-200 sm:bg-slate-50 sm:p-4 sm:shadow-none">
                        <div class="grid grid-cols-2 gap-4 text-sm sm:gap-3 sm:grid-cols-4">
                            <div>
                                <p class="text-xs text-slate-400 sm:text-slate-500">Jumlah Barang</p>
                                <p class="font-semibold text-white sm:text-slate-800">{{ jumlahBarang }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 sm:text-slate-500">Total Berat Asli</p>
                                <p class="font-semibold text-white sm:text-slate-800">{{ formatWeight(totalBeratAsli) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 sm:text-slate-500">Total Volumetrik</p>
                                <p class="font-semibold text-white sm:text-slate-800">{{ formatWeight(totalBeratVolumetrik) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 sm:text-slate-500">Total Tagihan</p>
                                <p class="font-bold text-[#D4AF37] sm:font-semibold sm:text-indigo-600">{{ formatWeight(totalBeratTagihan) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- [UPDATE: FASE 3] Catatan Opsional dengan Checkbox -->
                    <div class="w-full max-w-lg mt-6">
                        <div class="flex items-center gap-2 mb-3">
                            <input type="checkbox" v-model="hasCatatan" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 xl:border-white/20 xl:bg-[#1A233A]">
                            <label class="text-sm font-semibold text-slate-700 xl:text-slate-300 cursor-pointer select-none" @click="hasCatatan = !hasCatatan">
                                Tambah Catatan Pengiriman (Opsional)
                            </label>
                        </div>
                        <div v-show="hasCatatan" class="transition-all duration-300">
                            <label class="form-label">Isi Catatan</label>
                            <textarea v-model="form.catatan" rows="2" class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] py-3 focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent"></textarea>
                            <p v-if="form.errors.catatan" class="mt-1 text-xs text-red-500">
                                {{ form.errors.catatan }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- [UPDATE: FASE 7.1] The Royal Action Bar (Static Flow di Mobile) -->
                <div class="mt-6 flex flex-col gap-3 sm:mt-5 sm:flex-row sm:justify-between">
                    <!-- Desktop -->
                    <button type="button" class="hidden sm:inline-flex btn-secondary w-auto justify-center rounded-lg" @click="step = 2">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </button>
                    <button type="button" class="hidden sm:inline-flex btn-primary w-auto justify-center rounded-lg" @click="goToStep(4)">
                        Selanjutnya
                        <i class="bi bi-arrow-right" />
                    </button>

                    <!-- Mobile -->
                    <div class="flex sm:hidden gap-3 w-full">
                        <button type="button" class="flex-1 items-center justify-center gap-2 rounded-[16px] bg-slate-100 px-4 py-3.5 text-[15px] font-bold tracking-wide text-slate-600 transition-transform active:scale-95" @click="step = 2">
                            Kembali
                        </button>
                        <button type="button" class="flex-[2] items-center justify-center gap-2 rounded-[16px] bg-[#0B132B] px-4 py-3.5 text-[15px] font-bold tracking-wide text-[#D4AF37] shadow-[0_8px_20px_rgba(11,19,43,0.2)] transition-transform hover:scale-[0.98] active:scale-95" @click="goToStep(4)">
                            Selanjutnya
                            <i class="bi bi-arrow-right font-bold text-lg" />
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="p-4 bg-white/60 backdrop-blur-md border border-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:card sm:rounded-xl sm:bg-white sm:border-slate-200 sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-[#0B132B] sm:text-slate-800">Step 4 — Layanan & Pembayaran</h3>
                    <p class="text-xs text-slate-500">Pilih layanan, isi biaya tambahan, lalu simpan.</p>
                </div>

                <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600 sm:p-4">
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
                            class="relative rounded-xl border p-3 text-left sm:p-4" :class="item.jenis_layanan === form.jenis_layanan
                                ? 'border-2 border-indigo-500 bg-indigo-50'
                                : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-slate-50'"
                            :style="{ transition: 'border-color 0.2s ease, background-color 0.2s ease' }"
                            @click="form.jenis_layanan = item.jenis_layanan">
                            <div class="mb-2 flex flex-wrap gap-1">
                                <span v-if="isFastest(item)" class="badge bg-amber-100 text-amber-700 xl:bg-amber-900/40 xl:text-amber-400">
                                    TERCEPAT
                                </span>

                                <span v-if="isCheapest(item)" class="badge bg-emerald-100 text-emerald-700 xl:bg-emerald-900/40 xl:text-emerald-400">
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

                    <div class="grid grid-cols-1 gap-3 sm:gap-4 lg:grid-cols-2">
                        <div>
                            <!-- [UPDATE: FASE 3] Biaya Asuransi dengan Toggle Switch -->
                            <div class="mb-5 w-full sm:max-w-xs xl:max-w-sm">
                                <ToggleSwitch v-model="useAsuransi" label="Gunakan Asuransi Pengiriman" class="mb-3 xl:text-slate-300" />
                                <div v-show="useAsuransi" class="transition-all duration-300">
                                    <label class="form-label">Nominal Asuransi</label>
                                    <input v-model="form.biaya_asuransi" type="number" min="0" step="1000"
                                        class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
                                </div>
                            </div>

                            <!-- [UPDATE: FASE 3] Biaya Tambahan dengan Toggle Switch -->
                            <div class="mb-5 w-full sm:max-w-xs xl:max-w-sm">
                                <ToggleSwitch v-model="useBiayaTambahan" label="Ada Biaya Tambahan" class="mb-3 xl:text-slate-300" />
                                <div v-show="useBiayaTambahan" class="transition-all duration-300">
                                    <label class="form-label">Nominal Biaya Tambahan</label>
                                    <input v-model="form.biaya_tambahan" type="number" min="0" step="1000"
                                        class="form-input w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] focus:bg-white sm:rounded-lg sm:bg-white sm:border-slate-300 sm:min-h-0 xl:bg-transparent">
                                </div>
                            </div>

                            <div class="mt-5">
                                <label class="form-label">Metode Pembayaran</label>

                                <div class="space-y-2">
                                    <label
                                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 xl:border-white/10 xl:text-slate-300 xl:bg-[#1A233A]">
                                        <input v-model="form.metode_pembayaran" type="radio" value="dibayar_pengirim" class="xl:bg-[#0B132B]">
                                        <span>Dibayar Pengirim</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 xl:border-white/10 xl:text-slate-300 xl:bg-[#1A233A]">
                                        <input v-model="form.metode_pembayaran" type="radio" value="dibayar_penerima" class="xl:bg-[#0B132B]">
                                        <span>Dibayar Penerima</span>
                                    </label>

                                    <label
                                        class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 xl:border-white/10 xl:text-slate-300 xl:bg-[#1A233A]">
                                        <input v-model="form.metode_pembayaran" type="radio" value="cod" class="xl:bg-[#0B132B]">
                                        <span>COD</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-3 sm:p-4 xl:border-[#D4AF37]/30 xl:bg-[#0B132B]/80 xl:backdrop-blur-md">
                            <h4 class="text-sm font-semibold text-slate-800 xl:text-[#D4AF37]">Ringkasan Biaya</h4>

                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-600 xl:text-slate-300">Ongkir</span>
                                    <span class="font-medium text-slate-800 xl:text-white">{{ formatCurrency(ongkir) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3" v-if="useAsuransi">
                                    <span class="text-slate-600 xl:text-slate-300">Asuransi</span>
                                    <span class="font-medium text-slate-800 xl:text-white">{{ formatCurrency(biayaAsuransi) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3" v-if="useBiayaTambahan">
                                    <span class="text-slate-600 xl:text-slate-300">Biaya Tambahan</span>
                                    <span class="font-medium text-slate-800 xl:text-white">{{ formatCurrency(biayaTambahan) }}</span>
                                </div>

                                <div class="my-3 border-t border-indigo-200 xl:border-white/10" />

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-semibold text-slate-800 xl:text-slate-200">Total</span>
                                    <span class="text-lg font-bold text-indigo-600 xl:text-[#D4AF37]">{{ formatCurrency(totalBiaya)
                                        }}</span>
                                </div>

                                <div
                                    class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs text-slate-600 xl:border-white/10 xl:bg-[#1A233A] xl:text-slate-400 mt-2">
                                    <div><b class="xl:text-slate-300">Layanan:</b> {{ form.jenis_layanan ? formatService(form.jenis_layanan) : '-'
                                        }}</div>
                                    <div><b class="xl:text-slate-300">Pembayaran:</b> {{ paymentLabel(form.metode_pembayaran) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- [UPDATE: FASE 7.1] The Royal Action Bar (Static Flow di Mobile untuk Simpan Data) -->
                <div class="mt-6 flex flex-col gap-3 sm:mt-5 sm:flex-row sm:justify-between">
                    <!-- Desktop -->
                    <button type="button" class="hidden sm:inline-flex btn-secondary w-auto justify-center rounded-lg" @click="step = 3">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </button>
                    <button type="button" class="hidden sm:inline-flex btn-primary w-auto justify-center rounded-lg"
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

                    <!-- Mobile -->
                    <div class="flex sm:hidden gap-3 w-full">
                        <button type="button" class="flex-1 items-center justify-center gap-2 rounded-[16px] bg-slate-100 px-4 py-3.5 text-[15px] font-bold tracking-wide text-slate-600 transition-transform active:scale-95" @click="step = 3">
                            Kembali
                        </button>
                        <button type="button" class="flex-[2] items-center justify-center gap-2 rounded-[16px] bg-[#0B132B] px-4 py-3.5 text-[15px] font-bold tracking-wide text-[#D4AF37] shadow-[0_8px_20px_rgba(11,19,43,0.2)] transition-transform hover:scale-[0.98] active:scale-95"
                            :disabled="form.processing || !selectedTarif"
                            :class="{ 'opacity-60 cursor-not-allowed': form.processing || !selectedTarif }" @click="submit">
                            <span v-if="form.processing" class="inline-flex items-center gap-2">
                                <i class="bi bi-arrow-repeat animate-spin text-lg" />
                                Tunggu...
                            </span>
                            <span v-else class="inline-flex items-center gap-2">
                                Simpan Data
                                <i class="bi bi-check2-circle font-bold text-lg" />
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>