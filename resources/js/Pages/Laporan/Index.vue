<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    periodeLabel: {
        type: String,
        required: true,
    },
    period: {
        type: String,
        required: true,
    },
    tab: {
        type: String,
        default: 'rekap',
    },
    mulai: {
        type: String,
        default: '',
    },
    akhir: {
        type: String,
        default: '',
    },
    rekapStatus: {
        type: Object,
        required: true,
    },
    rekapLayanan: {
        type: Object,
        required: true,
    },
    totalPengiriman: {
        type: Number,
        required: true,
    },
    totalTerkirim: {
        type: Number,
        required: true,
    },
    totalBatal: {
        type: Number,
        required: true,
    },
    totalPendapatan: {
        type: Number,
        required: true,
    },
    rataBeratTagihan: {
        type: Number,
        required: true,
    },
    onTimeCount: {
        type: Number,
        required: true,
    },
    lateCount: {
        type: Number,
        required: true,
    },
    tabelPengiriman: {
        type: Array,
        default: () => [],
    },
    breakdownLayanan: {
        type: Array,
        default: () => [],
    },
})

const customForm = reactive({
    mulai: props.mulai || '',
    akhir: props.akhir || '',
})

const search = reactive({
    keyword: '',
})

const successRate = computed(() => {
    if (!props.totalPengiriman) return 0
    return (props.totalTerkirim / props.totalPengiriman) * 100
})

const pendingCount = computed(() => Number(props.rekapStatus?.pending || 0))

const exportPdfUrl = computed(() => {
    return route('laporan.pdf', {
        period: props.period,
        mulai: props.mulai,
        akhir: props.akhir,
    })
})

const detailRows = computed(() => {
    const keyword = String(search.keyword || '').trim().toLowerCase()

    if (!keyword) {
        return props.tabelPengiriman
    }

    return props.tabelPengiriman.filter((item) => {
        return [
            item.nomor_resi,
            item.pengirim,
            item.kota_asal,
            item.tujuan,
            item.layanan,
            item.status,
        ]
            .join(' ')
            .toLowerCase()
            .includes(keyword)
    })
})

const formatCurrency = (value) => {
    return `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(Number(value || 0)))}`
}

const formatNumber = (value, digits = 0) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    }).format(Number(value || 0))
}

const formatWeight = (value) => {
    return `${formatNumber(value, 2)} kg`
}

const formatDate = (value, withTime = false) => {
    if (!value) return '-'

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
    }).format(new Date(value))
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

const applyPeriod = (period) => {
    router.get(
        route('laporan.index'),
        {
            period,
            tab: props.tab,
        },
        {
            preserveScroll: true,
            replace: true,
        }
    )
}

const applyCustomPeriod = () => {
    if (!customForm.mulai || !customForm.akhir) {
        window.Swal?.fire({
            icon: 'warning',
            title: 'Periode belum lengkap',
            text: 'Silakan isi tanggal mulai dan tanggal akhir.',
        })
        return
    }

    router.get(
        route('laporan.index'),
        {
            period: 'custom',
            mulai: customForm.mulai,
            akhir: customForm.akhir,
            tab: props.tab,
        },
        {
            preserveScroll: true,
            replace: true,
        }
    )
}

const setTab = (tab) => {
    router.get(
        route('laporan.index'),
        {
            period: props.period,
            mulai: props.mulai,
            akhir: props.akhir,
            tab,
        },
        {
            preserveScroll: true,
            replace: true,
        }
    )
}
</script>

<template>

    <Head title="Laporan" />

    <AppLayout>
        <div class="space-y-4 sm:space-y-5">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Laporan</h2>
                    <p class="text-sm text-slate-500">
                        Ringkasan performa pengiriman berdasarkan periode yang dipilih.
                    </p>
                    <p class="mt-1 text-xs font-medium text-indigo-600">
                        {{ periodeLabel }}
                    </p>
                </div>

                <a :href="exportPdfUrl" class="btn-secondary w-full justify-center sm:w-auto">
                    <i class="bi bi-file-earmark-pdf" />
                    Export PDF
                </a>
            </div>

            <!-- Period Selector (Horizontal Scroll di Mobile) -->
            <div class="card p-3 sm:p-4 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                <div class="flex w-full overflow-x-auto pb-1 gap-2 sm:gap-2 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-full shrink-0"
                        :class="{ '!border-[#1a365d] !bg-[#1a365d] !text-white shadow-md': period === 'hari_ini' }"
                        @click="applyPeriod('hari_ini')">
                        Hari Ini
                    </button>

                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-full shrink-0"
                        :class="{ '!border-[#1a365d] !bg-[#1a365d] !text-white shadow-md': period === 'minggu_ini' }"
                        @click="applyPeriod('minggu_ini')">
                        Minggu Ini
                    </button>

                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-full shrink-0"
                        :class="{ '!border-[#1a365d] !bg-[#1a365d] !text-white shadow-md': period === 'bulan_ini' }"
                        @click="applyPeriod('bulan_ini')">
                        Bulan Ini
                    </button>

                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-full shrink-0"
                        :class="{ '!border-[#1a365d] !bg-[#1a365d] !text-white shadow-md': period === 'bulan_lalu' }"
                        @click="applyPeriod('bulan_lalu')">
                        Bulan Lalu
                    </button>

                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-full shrink-0"
                        :class="{ '!border-[#1a365d] !bg-[#1a365d] !text-white shadow-md': period === 'tahun_ini' }"
                        @click="applyPeriod('tahun_ini')">
                        Tahun Ini
                    </button>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:gap-3 lg:flex lg:flex-row">
                    <div class="col-span-1 lg:w-[170px]">
                        <label class="form-label">Mulai</label>
                        <input v-model="customForm.mulai" type="date" class="form-input">
                    </div>

                    <div class="col-span-1 lg:w-[170px]">
                        <label class="form-label">Akhir</label>
                        <input v-model="customForm.akhir" type="date" class="form-input">
                    </div>

                    <div class="col-span-2 flex items-end lg:col-span-1">
                        <button type="button" class="btn-primary w-full justify-center lg:w-auto"
                            @click="applyCustomPeriod">
                            Terapkan Custom
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Selector (Horizontal Scroll di Mobile) -->
            <div class="card p-2 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] bg-slate-50 border border-slate-100">
                <div class="flex w-full overflow-x-auto gap-2 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-xl flex-1 justify-center border-0 bg-transparent"
                        :class="{ '!bg-white shadow-sm font-bold !text-[#1a365d]': tab === 'rekap', 'text-slate-500 hover:bg-slate-100': tab !== 'rekap' }"
                        @click="setTab('rekap')">
                        Rekap Umum
                    </button>

                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-xl flex-1 justify-center border-0 bg-transparent"
                        :class="{ '!bg-white shadow-sm font-bold !text-[#1a365d]': tab === 'detail', 'text-slate-500 hover:bg-slate-100': tab !== 'detail' }"
                        @click="setTab('detail')">
                        Detail Pengiriman
                    </button>

                    <button type="button" class="btn-secondary btn-sm whitespace-nowrap !rounded-xl flex-1 justify-center border-0 bg-transparent"
                        :class="{ '!bg-white shadow-sm font-bold !text-[#1a365d]': tab === 'keuangan', 'text-slate-500 hover:bg-slate-100': tab !== 'keuangan' }"
                        @click="setTab('keuangan')">
                        Keuangan
                    </button>
                </div>
            </div>

            <!-- Tab: Rekap -->
            <div v-if="tab === 'rekap'" class="space-y-4 sm:space-y-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 xl:grid-cols-3">
                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Total Pengiriman</p>
                        <p class="mt-1.5 text-xl font-bold text-slate-800 sm:mt-2 sm:text-2xl">{{
                            formatNumber(totalPengiriman) }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Terkirim</p>
                        <p class="mt-1.5 text-xl font-bold text-emerald-600 sm:mt-2 sm:text-2xl">{{
                            formatNumber(totalTerkirim) }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Batal / Gagal</p>
                        <p class="mt-1.5 text-xl font-bold text-red-500 sm:mt-2 sm:text-2xl">{{ formatNumber(totalBatal)
                            }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Sukses Rate</p>
                        <p class="mt-1.5 text-xl font-bold text-indigo-600 sm:mt-2 sm:text-2xl">{{
                            formatNumber(successRate, 2) }}%</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Tepat Waktu</p>
                        <p class="mt-1.5 text-xl font-bold text-emerald-600 sm:mt-2 sm:text-2xl">{{
                            formatNumber(onTimeCount) }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Terlambat</p>
                        <p class="mt-1.5 text-xl font-bold text-amber-500 sm:mt-2 sm:text-2xl">{{
                            formatNumber(lateCount) }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Total Pendapatan</p>
                        <p class="mt-1.5 text-lg font-bold text-slate-800 sm:mt-2 sm:text-xl">{{
                            formatCurrency(totalPendapatan) }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Rata Berat Tagihan</p>
                        <p class="mt-1.5 text-lg font-bold text-slate-800 sm:mt-2 sm:text-xl">{{
                            formatWeight(rataBeratTagihan) }}</p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Pending</p>
                        <p class="mt-1.5 text-xl font-bold text-slate-700 sm:mt-2 sm:text-2xl">{{
                            formatNumber(pendingCount) }}</p>
                    </div>
                </div>

                <div class="card p-3 sm:p-4">
                    <h3 class="text-sm font-semibold text-slate-800">Rekap Per Status</h3>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:grid-cols-4 sm:gap-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Pending</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapStatus.pending) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Diproses</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapStatus.diproses) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Dalam Perjalanan</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapStatus.dalam_perjalanan) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Tiba Kota Tujuan</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapStatus.tiba_di_kota_tujuan) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Sedang Diantar</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapStatus.sedang_diantar) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Terkirim</p>
                            <p class="mt-1 text-base font-bold text-emerald-600 sm:text-lg">{{
                                formatNumber(rekapStatus.terkirim) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Gagal</p>
                            <p class="mt-1 text-base font-bold text-red-500 sm:text-lg">{{
                                formatNumber(rekapStatus.gagal) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Dibatalkan</p>
                            <p class="mt-1 text-base font-bold text-slate-500 sm:text-lg">{{
                                formatNumber(rekapStatus.dibatalkan) }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-3 sm:p-4">
                    <h3 class="text-sm font-semibold text-slate-800">Rekap Per Layanan</h3>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:grid-cols-4 sm:gap-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Ekonomis</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapLayanan.ekonomis) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Reguler</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapLayanan.reguler) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Express</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapLayanan.express) }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 sm:p-3">
                            <p class="text-xs text-slate-500">Same Day</p>
                            <p class="mt-1 text-base font-bold text-slate-800 sm:text-lg">{{
                                formatNumber(rekapLayanan.same_day) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Detail -->
            <div v-else-if="tab === 'detail'" class="space-y-4">
                <div class="card p-3 sm:p-4">
                    <div class="max-w-sm">
                        <label class="form-label">Cari di tabel</label>
                        <input v-model="search.keyword" type="text" class="form-input"
                            placeholder="No resi / pengirim / tujuan / status">
                    </div>
                </div>

                <div class="card overflow-hidden rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    
                    <!-- Mobile Card View -->
                    <div v-if="detailRows.length" class="block sm:hidden divide-y divide-slate-100/80 bg-white">
                        <div v-for="item in detailRows" :key="'mob-'+item.id" class="p-4 flex flex-col gap-3">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">No. Resi</span>
                                    <Link :href="route('pengiriman.show', item.id)" class="text-[14px] font-bold tracking-tight text-[#1a365d]">
                                        {{ item.nomor_resi }}
                                    </Link>
                                    <span class="text-[10px] text-slate-500 mt-0.5">{{ formatDate(item.tanggal, true) }}</span>
                                </div>
                                <StatusBadge :status="item.status" />
                            </div>

                            <div class="grid grid-cols-2 gap-3 bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Rute</span>
                                    <span class="block text-[12px] font-semibold text-slate-700 truncate mt-0.5">{{ item.kota_asal || '-' }} &rarr; {{ item.tujuan || '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Layanan</span>
                                    <span class="block text-[12px] font-semibold text-[#b8860b] truncate mt-0.5">{{ formatService(item.layanan) }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-100/60 pt-2">
                                <div class="text-[11px] text-slate-500">
                                    {{ formatWeight(item.berat_tagihan) }} Tagihan
                                </div>
                                <div class="text-[13px] font-black text-indigo-600">
                                    {{ formatCurrency(item.biaya) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div v-if="detailRows.length" class="hidden sm:block table-wrap">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        No Resi</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Tanggal</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Pengirim</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Tujuan</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Layanan</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        B. Asli</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        B. Tagihan</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Status</th>
                                    <th
                                        class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Biaya</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="item in detailRows" :key="'dt-'+item.id" class="hover:bg-slate-50/50">
                                    <td class="px-3 py-2 text-sm font-bold text-[#1a365d]">
                                        <Link :href="route('pengiriman.show', item.id)" class="hover:text-[#b8860b]">
                                            {{ item.nomor_resi }}
                                        </Link>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{
                                        formatDate(item.tanggal, true) }}</td>
                                    <td class="px-3 py-2 text-sm font-medium text-slate-700">{{ item.pengirim }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">
                                        {{ item.kota_asal || '-' }} &rarr; {{ item.tujuan || '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{
                                        formatService(item.layanan) }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{
                                        formatWeight(item.berat_asli) }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{
                                        formatWeight(item.berat_tagihan) }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <StatusBadge :status="item.status" />
                                    </td>
                                    <td class="px-3 py-2 text-sm font-bold text-indigo-600">{{
                                        formatCurrency(item.biaya) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-4 py-8 text-center text-sm text-slate-500">
                        Tidak ada data detail pengiriman pada periode ini.
                    </div>
                </div>
            </div>

            <!-- Tab: Keuangan -->
            <div v-else class="space-y-4">
                <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Total Pendapatan</p>
                        <p class="mt-1.5 text-xl font-bold text-slate-800 sm:mt-2 sm:text-2xl">
                            {{ formatCurrency(totalPendapatan) }}
                        </p>
                    </div>

                    <div class="card p-3 sm:p-4">
                        <p class="text-xs text-slate-500">Rata-rata per Pengiriman</p>
                        <p class="mt-1.5 text-xl font-bold text-indigo-600 sm:mt-2 sm:text-2xl">
                            {{ formatCurrency(totalPengiriman ? totalPendapatan / totalPengiriman : 0) }}
                        </p>
                    </div>
                </div>

                <div class="card overflow-hidden rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                    <div class="border-b border-slate-100/60 bg-slate-50/50 px-4 py-3">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-[#1a365d]">Breakdown Per Layanan</h3>
                    </div>

                    <!-- Mobile View -->
                    <div class="block sm:hidden divide-y divide-slate-100/80 bg-white">
                        <div v-for="item in breakdownLayanan" :key="'mob-keu-'+item.jenis_layanan" class="p-4 flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-bold text-slate-800">{{ formatService(item.jenis_layanan) }}</span>
                                <span class="text-[11px] font-semibold text-slate-500">{{ formatNumber(item.jumlah_pengiriman) }} Paket</span>
                            </div>
                            <div class="flex flex-col gap-1 text-right">
                                <span class="text-sm font-black text-indigo-600">{{ formatCurrency(item.total_pendapatan) }}</span>
                                <span class="text-[10px] text-slate-400">Rata: {{ formatCurrency(item.rata_rata) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop View -->
                    <div class="hidden sm:block table-wrap">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Layanan</th>
                                    <th
                                        class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Jumlah</th>
                                    <th
                                        class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Total Pendapatan</th>
                                    <th
                                        class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        Rata-rata</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="item in breakdownLayanan" :key="'desk-keu-'+item.jenis_layanan">
                                    <td class="px-4 py-3 text-sm font-bold text-slate-700">{{ formatService(item.jenis_layanan) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ formatNumber(item.jumlah_pengiriman)
                                        }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-indigo-600">{{
                                        formatCurrency(item.total_pendapatan) }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{
                                        formatCurrency(item.rata_rata) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>