<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

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
    })
}
</script>

<template>

    <Head title="Detail Pengiriman" />

    <AppLayout>
        <div class="space-y-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm text-slate-500">Nomor Resi</p>
                    <h2 class="text-lg font-semibold text-slate-800">
                        {{ pengiriman.nomor_resi || '-' }}
                    </h2>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a :href="route('resi.print', pengiriman.id)" target="_blank" rel="noopener" class="btn-secondary">
                        <i class="bi bi-printer" />
                        Cetak Resi
                    </a>

                    <a :href="route('resi.pdf', pengiriman.id)" class="btn-secondary">
                        <i class="bi bi-file-earmark-pdf" />
                        Download PDF
                    </a>

                    <Link :href="route('pengiriman.index')" class="btn-secondary">
                        <i class="bi bi-arrow-left" />
                        Kembali
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-5">
                <div class="space-y-5 lg:col-span-3">
                    <div class="card p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2">
                                <StatusBadge :status="pengiriman.status" />

                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                    {{ formatService(pengiriman.jenis_layanan) }}
                                </span>
                            </div>

                            <div class="text-sm text-slate-600">
                                Estimasi tiba:
                                <span class="font-medium text-slate-800">
                                    {{ formatDate(pengiriman.estimasi_tiba) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <div class="card p-4">
                            <h3 class="text-sm font-semibold text-slate-800">Pengirim</h3>

                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p>
                                    <span class="font-medium text-slate-700">Nama:</span>
                                    {{ pengiriman.pengirim_nama || '-' }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">HP:</span>
                                    {{ pengiriman.pengirim_hp || '-' }}
                                </p>

                                <p class="whitespace-pre-line break-words">
                                    <span class="font-medium text-slate-700">Alamat:</span>
                                    {{ pengiriman.pengirim_alamat || '-' }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">Kota:</span>
                                    {{ pengiriman.pengirim_kota?.nama_kota || '-' }},
                                    {{ pengiriman.pengirim_kota?.provinsi || '-' }}
                                    ({{ pengiriman.pengirim_kota?.kode_pos || '-' }})
                                </p>
                            </div>
                        </div>

                        <div class="card p-4">
                            <h3 class="text-sm font-semibold text-slate-800">Penerima</h3>

                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                <p>
                                    <span class="font-medium text-slate-700">Nama:</span>
                                    {{ pengiriman.penerima_nama || '-' }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">HP:</span>
                                    {{ pengiriman.penerima_hp || '-' }}
                                </p>

                                <p class="whitespace-pre-line break-words">
                                    <span class="font-medium text-slate-700">Alamat:</span>
                                    {{ pengiriman.penerima_alamat || '-' }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">Kota:</span>
                                    {{ pengiriman.penerima_kota?.nama_kota || '-' }},
                                    {{ pengiriman.penerima_kota?.provinsi || '-' }}
                                    ({{ pengiriman.penerima_kota?.kode_pos || '-' }})
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card overflow-hidden">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <h3 class="text-sm font-semibold text-slate-800">Detail Barang</h3>
                        </div>

                        <div class="table-wrap">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            #
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Nama
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            B. Asli
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Volumetrik
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Tagihan
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Dimensi
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Ket
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="(item, index) in barangList" :key="item.id">
                                        <td class="px-3 py-2 text-sm text-slate-600">{{ index + 1 }}</td>
                                        <td class="px-3 py-2 text-sm text-slate-700">{{ item.nama_barang || '-' }}</td>
                                        <td class="px-3 py-2 text-sm text-slate-600">{{ formatWeight(item.berat_asli_kg)
                                            }}</td>
                                        <td class="px-3 py-2 text-sm text-slate-600">{{
                                            formatWeight(item.berat_volumetrik_kg) }}</td>
                                        <td class="px-3 py-2 text-sm font-medium text-indigo-600">{{
                                            formatWeight(item.berat_tagihan_kg) }}</td>
                                        <td class="px-3 py-2 text-sm text-slate-600">
                                            {{ Number(item.panjang_cm || 0) }} × {{ Number(item.lebar_cm || 0) }} × {{
                                            Number(item.tinggi_cm || 0) }} cm
                                        </td>
                                        <td class="px-3 py-2 text-sm text-slate-600">{{ item.keterangan || '-' }}</td>
                                    </tr>

                                    <tr class="bg-slate-50">
                                        <td colspan="2" class="px-3 py-2 text-sm font-semibold text-slate-700">
                                            Total
                                        </td>
                                        <td class="px-3 py-2 text-sm font-semibold text-slate-700">
                                            {{ formatWeight(pengiriman.total_berat_asli) }}
                                        </td>
                                        <td class="px-3 py-2 text-sm font-semibold text-slate-700">
                                            {{ formatWeight(pengiriman.total_berat_volumetrik) }}
                                        </td>
                                        <td class="px-3 py-2 text-sm font-semibold text-indigo-600">
                                            {{ formatWeight(pengiriman.total_berat_tagihan) }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-slate-500">-</td>
                                        <td class="px-3 py-2 text-sm text-slate-500">
                                            {{ pengiriman.jumlah_barang || 0 }} barang
                                        </td>
                                    </tr>

                                    <tr v-if="!barangList.length">
                                        <td colspan="7" class="px-3 py-6 text-center text-sm text-slate-500">
                                            Belum ada data barang.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h3 class="text-sm font-semibold text-slate-800">Biaya Pengiriman</h3>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2 text-sm text-slate-600">
                                <div class="flex items-center justify-between gap-3">
                                    <span>Tarif per kg</span>
                                    <span class="font-medium text-slate-800">{{ formatCurrency(pengiriman.tarif_per_kg)
                                        }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span>Ongkir</span>
                                    <span class="font-medium text-slate-800">{{
                                        formatCurrency(pengiriman.biaya_pengiriman) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span>Asuransi</span>
                                    <span class="font-medium text-slate-800">{{
                                        formatCurrency(pengiriman.biaya_asuransi) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span>Tambahan</span>
                                    <span class="font-medium text-slate-800">{{
                                        formatCurrency(pengiriman.biaya_tambahan) }}</span>
                                </div>

                                <div class="border-t border-slate-200 pt-2" />

                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-800">Total</span>
                                    <span class="text-lg font-bold text-indigo-600">{{
                                        formatCurrency(pengiriman.biaya_total) }}</span>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-slate-600">
                                <p>
                                    <span class="font-medium text-slate-700">Metode pembayaran:</span>
                                    {{ formatPayment(pengiriman.metode_pembayaran) }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">Dibuat:</span>
                                    {{ formatDate(pengiriman.created_at, true) }}
                                </p>

                                <p v-if="pengiriman.tanggal_terkirim">
                                    <span class="font-medium text-slate-700">Tanggal terkirim:</span>
                                    {{ formatDate(pengiriman.tanggal_terkirim, true) }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">Admin:</span>
                                    {{ pengiriman.admin?.nama || '-' }}
                                </p>

                                <p>
                                    <span class="font-medium text-slate-700">Catatan:</span>
                                    {{ pengiriman.catatan || '-' }}
                                </p>

                                <p v-if="pengiriman.alasan_batal">
                                    <span class="font-medium text-slate-700">Alasan batal:</span>
                                    {{ pengiriman.alasan_batal }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 lg:col-span-2">
                    <div class="card p-4">
                        <h3 class="text-sm font-semibold text-slate-800">Update Status</h3>

                        <div v-if="canUpdateStatus" class="mt-4 space-y-4">
                            <div>
                                <label class="form-label">Status Baru</label>

                                <select v-model="statusForm.status_baru" class="form-select">
                                    <option value="" disabled>-- Pilih --</option>
                                    <option v-for="item in nextStatusOptions" :key="item.value" :value="item.value">
                                        {{ item.label }}
                                    </option>
                                </select>

                                <p v-if="statusForm.errors.status_baru" class="mt-1 text-xs text-red-500">
                                    {{ statusForm.errors.status_baru }}
                                </p>
                            </div>

                            <div>
                                <label class="form-label">Lokasi</label>
                                <input v-model="statusForm.lokasi" type="text" class="form-input">

                                <p v-if="statusForm.errors.lokasi" class="mt-1 text-xs text-red-500">
                                    {{ statusForm.errors.lokasi }}
                                </p>
                            </div>

                            <div>
                                <label class="form-label">Keterangan</label>
                                <textarea v-model="statusForm.keterangan" rows="3" class="form-input"></textarea>

                                <p v-if="statusForm.errors.keterangan" class="mt-1 text-xs text-red-500">
                                    {{ statusForm.errors.keterangan }}
                                </p>
                            </div>

                            <button type="button" class="btn-primary w-full justify-center"
                                :disabled="statusForm.processing"
                                :class="{ 'cursor-not-allowed opacity-60': statusForm.processing }"
                                @click="submitStatus">
                                <span v-if="statusForm.processing" class="inline-flex items-center gap-2">
                                    <i class="bi bi-arrow-repeat animate-spin" />
                                    Menyimpan...
                                </span>

                                <span v-else class="inline-flex items-center gap-2">
                                    <i class="bi bi-check2-circle" />
                                    Update Status
                                </span>
                            </button>
                        </div>

                        <div v-else
                            class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-500">
                            Pengiriman sudah berada pada status terminal dan tidak bisa diperbarui lagi.
                        </div>
                    </div>

                    <div v-if="canCancel" class="card p-4">
                        <h3 class="text-sm font-semibold text-slate-800">Batalkan Pengiriman</h3>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="form-label">Alasan Pembatalan</label>
                                <textarea v-model="cancelForm.alasan_batal" rows="3" class="form-input"></textarea>

                                <p v-if="cancelForm.errors.alasan_batal" class="mt-1 text-xs text-red-500">
                                    {{ cancelForm.errors.alasan_batal }}
                                </p>
                            </div>

                            <button type="button" class="btn-danger w-full justify-center"
                                :disabled="cancelForm.processing"
                                :class="{ 'cursor-not-allowed opacity-60': cancelForm.processing }"
                                @click="submitCancel">
                                <span v-if="cancelForm.processing" class="inline-flex items-center gap-2">
                                    <i class="bi bi-arrow-repeat animate-spin" />
                                    Memproses...
                                </span>

                                <span v-else class="inline-flex items-center gap-2">
                                    <i class="bi bi-x-circle" />
                                    Batalkan Pengiriman
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="card p-4">
                        <h3 class="text-sm font-semibold text-slate-800">Timeline Tracking</h3>

                        <div v-if="trackingHistories.length" class="mt-4 space-y-4">
                            <div v-for="(item, index) in trackingHistories" :key="item.id" class="relative pl-6">
                                <div v-if="index !== trackingHistories.length - 1"
                                    class="absolute left-2 top-1 h-full w-px bg-slate-200"></div>

                                <div
                                    class="absolute left-0 top-1.5 h-4 w-4 rounded-full border-2 border-white bg-indigo-500 shadow">
                                </div>

                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ statusLabel(item.status_baru) }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ formatDate(item.created_at, true) }}
                                        </p>
                                    </div>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ item.lokasi || '-' }}
                                        <span v-if="item.admin_nama"> · {{ item.admin_nama }}</span>
                                    </p>

                                    <p class="mt-2 text-sm text-slate-600">
                                        {{ item.keterangan || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mt-4 text-sm text-slate-500">
                            Belum ada riwayat tracking.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>