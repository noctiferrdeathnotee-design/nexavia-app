<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch, ref } from 'vue'

const showUpdateSheet = ref(false)
const showCancelSheet = ref(false)

const props = defineProps({
    pengiriman: {
        type: Object,
        required: true,
    },
})

const swal = window.Swal

const barangList = computed(() => props.pengiriman?.barang ?? [])
const trackingHistories = computed(() => props.pengiriman?.tracking_histories ?? [])

const statusLabels = {
    pending: 'Pending',
    diproses: 'Diproses',
    dalam_perjalanan: 'Dalam Perjalanan',
    tiba_di_kota_tujuan: 'Tiba di Kota Tujuan',
    sedang_diantar: 'Sedang Diantar',
    terkirim: 'Terkirim',
    gagal: 'Gagal',
    dibatalkan: 'Dibatalkan',
}

const statusTransitionOptions = {
    pending: [
        { value: 'diproses', label: 'Diproses' },
        { value: 'gagal', label: 'Gagal' },
    ],
    diproses: [
        { value: 'dalam_perjalanan', label: 'Dalam Perjalanan' },
        { value: 'gagal', label: 'Gagal' },
    ],
    dalam_perjalanan: [
        { value: 'tiba_di_kota_tujuan', label: 'Tiba di Kota Tujuan' },
        { value: 'gagal', label: 'Gagal' },
    ],
    tiba_di_kota_tujuan: [
        { value: 'sedang_diantar', label: 'Sedang Diantar' },
        { value: 'gagal', label: 'Gagal' },
    ],
    sedang_diantar: [
        { value: 'terkirim', label: 'Terkirim' },
        { value: 'gagal', label: 'Gagal' },
    ],
}

const terminalStatuses = ['terkirim', 'gagal', 'dibatalkan']

const nextStatusOptions = computed(() => {
    return statusTransitionOptions[props.pengiriman?.status] ?? []
})

const canUpdateStatus = computed(() => {
    return !terminalStatuses.includes(props.pengiriman?.status) && nextStatusOptions.value.length > 0
})

const canCancel = computed(() => {
    return !terminalStatuses.includes(props.pengiriman?.status)
})

const statusForm = useForm({
    status_baru: '',
    lokasi: props.pengiriman?.pengirim_kota?.nama_kota ?? '',
    keterangan: '',
})

const cancelForm = useForm({
    alasan_batal: '',
})

watch(
    nextStatusOptions,
    (options) => {
        if (!options.length) {
            statusForm.status_baru = ''
            return
        }

        const exists = options.some((item) => item.value === statusForm.status_baru)

        if (!exists) {
            statusForm.status_baru = options[0].value
        }
    },
    { immediate: true }
)

const formatDate = (value, withTime = false) => {
    if (!value) return '-'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        ...(withTime
            ? {
                hour: '2-digit',
                minute: '2-digit',
            }
            : {}),
    }).format(date)
}

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

    return map[value] || value || '-'
}

const formatPayment = (value) => {
    const map = {
        dibayar_pengirim: 'Dibayar Pengirim',
        dibayar_penerima: 'Dibayar Penerima',
        cod: 'COD',
    }

    return map[value] || value || '-'
}

const statusLabel = (value) => {
    return statusLabels[value] || value || '-'
}

const showAlert = async (options) => {
    if (swal) {
        return swal.fire(options)
    }

    if (options.icon === 'question' || options.showCancelButton) {
        return {
            isConfirmed: window.confirm(options.text || options.title || 'Lanjutkan?'),
        }
    }

    window.alert(options.text || options.title || 'Terjadi perubahan.')
    return { isConfirmed: true }
}

const submitStatus = async () => {
    if (!statusForm.status_baru) {
        await showAlert({
            icon: 'warning',
            title: 'Status belum dipilih',
            text: 'Silakan pilih status baru.',
        })
        return
    }

    if (!String(statusForm.lokasi || '').trim()) {
        await showAlert({
            icon: 'warning',
            title: 'Lokasi belum diisi',
            text: 'Lokasi tracking wajib diisi.',
        })
        return
    }

    if (!String(statusForm.keterangan || '').trim()) {
        await showAlert({
            icon: 'warning',
            title: 'Keterangan belum diisi',
            text: 'Keterangan tracking wajib diisi.',
        })
        return
    }

    const result = await showAlert({
        icon: 'question',
        title: 'Perbarui status?',
        text: `Status akan diubah menjadi ${statusLabel(statusForm.status_baru)}.`,
        showCancelButton: true,
        confirmButtonText: 'Ya, update',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4F46E5',
    })

    if (!result?.isConfirmed) {
        return
    }

    statusForm.post(route('pengiriman.tracking.store', props.pengiriman.id), {
        preserveScroll: true,
        onSuccess: () => {
            statusForm.reset('keterangan')
            showUpdateSheet.value = false // Tutup bottom sheet di mobile
        },
    })
}

const submitCancel = async () => {
    if (!String(cancelForm.alasan_batal || '').trim()) {
        await showAlert({
            icon: 'warning',
            title: 'Alasan belum diisi',
            text: 'Alasan pembatalan wajib diisi.',
        })
        return
    }

    const result = await showAlert({
        icon: 'warning',
        title: 'Batalkan pengiriman?',
        text: 'Pengiriman akan diubah menjadi dibatalkan.',
        showCancelButton: true,
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Kembali',
        confirmButtonColor: '#DC2626',
    })

    if (!result?.isConfirmed) {
        return
    }

    cancelForm.post(route('pengiriman.batal', props.pengiriman.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelSheet.value = false // Tutup bottom sheet di mobile
        }
    })
}
</script>

<template>

    <Head title="Detail Pengiriman" />

    <AppLayout>
        <div class="space-y-4 sm:space-y-5">
            <!-- Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm text-slate-500">Nomor Resi</p>
                    <h2 class="text-base font-semibold text-slate-800 sm:text-lg">
                        {{ pengiriman.nomor_resi || '-' }}
                    </h2>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a :href="route('resi.print', pengiriman.id)" target="_blank" rel="noopener"
                        class="btn-secondary text-xs sm:text-sm">
                        <i class="bi bi-printer" />
                        <span class="hidden sm:inline">Cetak</span> Resi
                    </a>

                    <a :href="route('resi.pdf', pengiriman.id)"
                        class="btn-secondary text-xs sm:text-sm">
                        <i class="bi bi-file-earmark-pdf" />
                        PDF
                    </a>

                    <Link :href="route('pengiriman.index')"
                        class="btn-secondary text-xs sm:text-sm">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </Link>
                </div>
            </div>

            <div class="flex flex-col gap-4 sm:gap-5 lg:grid lg:grid-cols-5 lg:items-start pb-24 lg:pb-0">
                <!-- INFO PAKET (Kolom Kiri di Desktop, Bawah di Mobile) -->
                <div class="order-2 space-y-4 sm:space-y-5 lg:order-1 lg:col-span-3">
                    <!-- Status Bar -->
                    <div class="card p-3 sm:p-4 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2">
                                <StatusBadge :status="pengiriman.status" />
                                <span
                                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold tracking-wide uppercase text-slate-500">
                                    {{ formatService(pengiriman.jenis_layanan) }}
                                </span>
                            </div>

                            <div class="text-[13px] font-medium text-slate-500">
                                Estimasi tiba:
                                <span class="font-bold text-[#0B132B]">
                                    {{ formatDate(pengiriman.estimasi_tiba) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pengirim & Penerima -->
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 xl:grid-cols-2">
                        <div class="card p-4 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-[#0B132B]">Pengirim</h3>

                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p><span class="inline-block w-16 font-semibold text-slate-400">Nama</span> <span class="font-semibold text-slate-800">{{ pengiriman.pengirim_nama || '-' }}</span></p>
                                <p><span class="inline-block w-16 font-semibold text-slate-400">HP</span> <span class="font-medium text-slate-800">{{ pengiriman.pengirim_hp || '-' }}</span></p>
                                <p><span class="block font-semibold text-slate-400 mb-1">Alamat</span> <span class="text-slate-700 whitespace-pre-line">{{ pengiriman.pengirim_alamat || '-' }}</span></p>
                                <p class="pt-2"><span class="font-semibold text-[#B8860B]">{{ pengiriman.pengirim_kota?.nama_kota || '-' }}, {{ pengiriman.pengirim_kota?.provinsi || '-' }}</span></p>
                            </div>
                        </div>

                        <div class="card p-4 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-[#0B132B]">Penerima</h3>

                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p><span class="inline-block w-16 font-semibold text-slate-400">Nama</span> <span class="font-semibold text-slate-800">{{ pengiriman.penerima_nama || '-' }}</span></p>
                                <p><span class="inline-block w-16 font-semibold text-slate-400">HP</span> <span class="font-medium text-slate-800">{{ pengiriman.penerima_hp || '-' }}</span></p>
                                <p><span class="block font-semibold text-slate-400 mb-1">Alamat</span> <span class="text-slate-700 whitespace-pre-line">{{ pengiriman.penerima_alamat || '-' }}</span></p>
                                <p class="pt-2"><span class="font-semibold text-[#B8860B]">{{ pengiriman.penerima_kota?.nama_kota || '-' }}, {{ pengiriman.penerima_kota?.provinsi || '-' }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Barang (Mobile Stacked Card View) -->
                    <div class="card overflow-hidden rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <div class="border-b border-slate-100/60 bg-slate-50/50 px-4 py-3">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-[#0B132B]">Detail Barang</h3>
                        </div>

                        <!-- Mobile View -->
                        <div v-if="barangList.length" class="block sm:hidden divide-y divide-slate-100/80">
                            <div v-for="(item, index) in barangList" :key="'mob-brg-'+item.id" class="p-4 bg-white">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-800">{{ index + 1 }}. {{ item.nama_barang || '-' }}</span>
                                    <span class="text-[11px] font-bold text-indigo-600">{{ formatWeight(item.berat_tagihan_kg) }} Tagihan</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500">
                                    <p>Asli: <span class="font-semibold text-slate-700">{{ formatWeight(item.berat_asli_kg) }}</span></p>
                                    <p>Vol: <span class="font-semibold text-slate-700">{{ formatWeight(item.berat_volumetrik_kg) }}</span></p>
                                    <p class="col-span-2">Dimensi: {{ Number(item.panjang_cm||0) }}x{{ Number(item.lebar_cm||0) }}x{{ Number(item.tinggi_cm||0) }} cm</p>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop View -->
                        <div v-if="barangList.length" class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">#</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">Nama</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">Asli</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">Vol</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-indigo-600">Tagihan</th>
                                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">Dimensi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="(item, index) in barangList" :key="'desk-brg-'+item.id">
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ item.nama_barang || '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ formatWeight(item.berat_asli_kg) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ formatWeight(item.berat_volumetrik_kg) }}</td>
                                        <td class="px-4 py-3 text-sm font-bold text-indigo-600">{{ formatWeight(item.berat_tagihan_kg) }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-500">{{ Number(item.panjang_cm||0) }}×{{ Number(item.lebar_cm||0) }}×{{ Number(item.tinggi_cm||0) }} cm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Bawah Barang -->
                        <div class="bg-slate-50/80 px-4 py-3 flex flex-wrap justify-between items-center text-sm border-t border-slate-100">
                            <span class="font-bold text-[#0B132B]">Total Tagihan:</span>
                            <span class="font-black text-indigo-600 text-base">{{ formatWeight(pengiriman.total_berat_tagihan) }}</span>
                        </div>
                        <div v-if="!barangList.length" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada data barang.</div>
                    </div>

                    <!-- Biaya -->
                    <div class="card p-4 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-[#0B132B] mb-4">Biaya & Informasi</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3 text-sm text-slate-600">
                                <div class="flex justify-between border-b border-slate-100/60 pb-2"><span>Tarif per kg</span><span class="font-semibold text-slate-800">{{ formatCurrency(pengiriman.tarif_per_kg) }}</span></div>
                                <div class="flex justify-between border-b border-slate-100/60 pb-2"><span>Ongkir</span><span class="font-semibold text-slate-800">{{ formatCurrency(pengiriman.biaya_pengiriman) }}</span></div>
                                <div class="flex justify-between border-b border-slate-100/60 pb-2"><span>Asuransi</span><span class="font-semibold text-slate-800">{{ formatCurrency(pengiriman.biaya_asuransi) }}</span></div>
                                <div class="flex justify-between border-b border-slate-100/60 pb-2"><span>Tambahan</span><span class="font-semibold text-slate-800">{{ formatCurrency(pengiriman.biaya_tambahan) }}</span></div>
                                
                                <div class="flex justify-between pt-2">
                                    <span class="font-bold text-slate-800 uppercase tracking-widest">Total Biaya</span>
                                    <span class="text-lg font-black text-indigo-600">{{ formatCurrency(pengiriman.biaya_total) }}</span>
                                </div>
                            </div>
                            <div class="space-y-3 text-[13px] text-slate-500 bg-slate-50/50 p-3 rounded-xl border border-slate-100/60">
                                <p><span class="block font-bold text-slate-400 uppercase tracking-widest text-[10px]">Metode</span> <span class="font-semibold text-[#0B132B]">{{ formatPayment(pengiriman.metode_pembayaran) }}</span></p>
                                <p><span class="block font-bold text-slate-400 uppercase tracking-widest text-[10px]">Dibuat</span> <span class="font-semibold text-slate-700">{{ formatDate(pengiriman.created_at, true) }}</span></p>
                                <p v-if="pengiriman.alasan_batal"><span class="block font-bold text-red-400 uppercase tracking-widest text-[10px]">Alasan Batal</span> <span class="font-semibold text-red-600">{{ pengiriman.alasan_batal }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN (Timeline & Bottom Sheet Action di Mobile) -->
                <div class="order-1 flex flex-col gap-4 sm:gap-5 lg:order-2 lg:col-span-2">
                    
                    <!-- Timeline (Berada Paling Atas di Mobile!) -->
                    <div class="card p-4 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] bg-gradient-to-b from-[#0B132B] to-slate-900 text-white relative overflow-hidden">
                        <!-- Dekorasi sayap Xaviera pudar -->
                        <div class="absolute -right-10 -top-10 opacity-10 blur-sm pointer-events-none w-48 h-48 bg-white rounded-full"></div>
                        <div class="relative z-10">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-[#B8860B] mb-5">Timeline Tracking</h3>

                            <div v-if="trackingHistories.length" class="space-y-4">
                                <div v-for="(item, index) in trackingHistories" :key="item.id" class="relative pl-6">
                                    <div v-if="index !== trackingHistories.length - 1" class="absolute left-1.5 top-2 h-full w-0.5 bg-slate-700/50"></div>
                                    <div class="absolute left-0 top-1.5 h-3.5 w-3.5 rounded-full border-2 border-[#0B132B] bg-[#B8860B] shadow-[0_0_8px_rgba(184,134,11,0.8)]"></div>

                                    <div class="flex flex-col gap-0.5">
                                        <p class="text-sm font-bold text-white">{{ statusLabel(item.status_baru) }}</p>
                                        <p class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase">{{ formatDate(item.created_at, true) }}</p>
                                        <p class="text-xs text-slate-300 mt-1 font-medium"><i class="bi bi-geo-alt-fill text-[#B8860B] mr-1"></i>{{ item.lokasi || '-' }}</p>
                                        <p class="text-[11px] text-slate-400 italic mt-0.5">{{ item.keterangan || '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-slate-400">Belum ada riwayat.</div>
                        </div>
                    </div>

                    <!-- MOBILE ACTION BAR (Fix di Bawah Layar, Muncul HANYA di HP) -->
                    <!-- Sengaja ditaruh di bottom-[68px] agar tidak menimpa Bottom Nav -->
                    <div class="fixed bottom-[68px] left-0 z-40 flex w-full gap-3 border-t border-slate-100 bg-white/95 px-4 py-3 shadow-[0_-10px_30px_rgb(0,0,0,0.06)] backdrop-blur-md lg:hidden">
                        <button v-if="canUpdateStatus" type="button" class="btn-primary flex-1 justify-center rounded-xl py-3 text-sm tracking-wide font-bold" @click="showUpdateSheet = true">
                            Update Status
                        </button>
                        <button v-if="canCancel" type="button" class="btn-danger flex-1 justify-center rounded-xl py-3 text-sm tracking-wide font-bold" @click="showCancelSheet = true">
                            Batalkan
                        </button>
                    </div>

                    <!-- OVERLAY UNTUK BOTTOM SHEET -->
                    <div 
                        class="fixed inset-0 z-[60] bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden"
                        :class="showUpdateSheet || showCancelSheet ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                        @click="showUpdateSheet = false; showCancelSheet = false"
                    ></div>

                    <!-- BOTTOM SHEET: UPDATE STATUS (Di Desktop menjadi Kotak Card biasa) -->
                    <div 
                        class="fixed inset-x-0 bottom-0 z-[70] rounded-t-[32px] bg-white p-5 shadow-[0_-10px_40px_rgb(0,0,0,0.1)] transition-transform duration-300 ease-out lg:static lg:block lg:translate-y-0 lg:rounded-[20px] lg:p-4 lg:shadow-[0_8px_30px_rgb(0,0,0,0.04)] lg:border lg:border-slate-100"
                        :class="showUpdateSheet ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
                    >
                        <!-- Handle tarik (Visual untuk Mobile) -->
                        <div class="mb-5 flex justify-center lg:hidden">
                            <div class="h-1.5 w-12 rounded-full bg-slate-200"></div>
                        </div>
                        
                        <h3 class="text-xs font-bold uppercase tracking-widest text-[#0B132B] mb-4">Update Status</h3>
                        
                        <div v-if="canUpdateStatus" class="space-y-4">
                            <div>
                                <label class="form-label text-xs font-bold text-slate-500 uppercase tracking-wider">Status Baru</label>
                                <select v-model="statusForm.status_baru" class="form-select bg-slate-50/50 rounded-xl h-11 border-slate-200">
                                    <option value="" disabled>-- Pilih Status --</option>
                                    <option v-for="item in nextStatusOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label text-xs font-bold text-slate-500 uppercase tracking-wider">Lokasi</label>
                                <input v-model="statusForm.lokasi" type="text" class="form-input bg-slate-50/50 rounded-xl h-11 border-slate-200">
                            </div>
                            <div>
                                <label class="form-label text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan Tambahan</label>
                                <textarea v-model="statusForm.keterangan" rows="2" class="form-input bg-slate-50/50 rounded-xl border-slate-200"></textarea>
                            </div>
                            
                            <button type="button" class="btn-primary w-full justify-center rounded-xl py-3 mt-2"
                                :disabled="statusForm.processing" @click="submitStatus">
                                <span v-if="statusForm.processing" class="inline-flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin" /> Menyimpan...</span>
                                <span v-else class="inline-flex items-center gap-2 font-bold tracking-wide"><i class="bi bi-check2-circle" /> Simpan Status Baru</span>
                            </button>
                        </div>
                        <div v-else class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm font-medium text-slate-500 text-center">
                            Pengiriman sudah berada pada status akhir (Terminal).
                        </div>
                    </div>

                    <!-- BOTTOM SHEET: PEMBATALAN (Di Desktop menjadi Kotak Card biasa) -->
                    <div 
                        v-if="canCancel"
                        class="fixed inset-x-0 bottom-0 z-[70] rounded-t-[32px] bg-white p-5 shadow-[0_-10px_40px_rgb(0,0,0,0.1)] transition-transform duration-300 ease-out lg:static lg:block lg:translate-y-0 lg:rounded-[20px] lg:p-4 lg:shadow-[0_8px_30px_rgb(0,0,0,0.04)] lg:border lg:border-slate-100"
                        :class="showCancelSheet ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
                    >
                        <div class="mb-5 flex justify-center lg:hidden"><div class="h-1.5 w-12 rounded-full bg-slate-200"></div></div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-red-600 mb-4">Batalkan Pengiriman</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="form-label text-xs font-bold text-slate-500 uppercase tracking-wider">Alasan Batal</label>
                                <textarea v-model="cancelForm.alasan_batal" rows="2" class="form-input bg-red-50/30 border-red-100 focus:border-red-300 focus:ring-red-500 rounded-xl"></textarea>
                            </div>
                            <button type="button" class="btn-danger w-full justify-center rounded-xl py-3 mt-2"
                                :disabled="cancelForm.processing" @click="submitCancel">
                                <span v-if="cancelForm.processing" class="inline-flex items-center gap-2"><i class="bi bi-arrow-repeat animate-spin" /> Membatalkan...</span>
                                <span v-else class="inline-flex items-center gap-2 font-bold tracking-wide"><i class="bi bi-x-circle" /> Konfirmasi Batal</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>