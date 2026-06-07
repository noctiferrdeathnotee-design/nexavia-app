<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
// [UBAH KHUSUS MOBILE & DESKTOP] Tambahkan defineAsyncComponent untuk lazy loading ApexCharts agar sangat ringan (Lighthouse 100%)
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_pengiriman: 0,
            total_pendapatan: 0,
        }),
    },
    terlambat_count: {
        type: Number,
        default: 0,
    },
    chart_bulanan: {
        type: Object,
        default: () => ({
            labels: [],
            jumlah: [],
            revenue: [],
        }),
    },
    terbaru: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const userName = computed(() => page.props.auth?.user?.nama || 'Admin')
const latestItems = computed(() => (Array.isArray(props.terbaru) ? props.terbaru : []))

const numberFormatter = new Intl.NumberFormat('id-ID')
const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
})

const hasChartData = computed(() => {
    return Array.isArray(props.chart_bulanan?.jumlah)
        && props.chart_bulanan.jumlah.some((value) => Number(value) > 0)
})

const formatCurrency = (value) => {
    return `Rp ${numberFormatter.format(Math.round(Number(value || 0)))}`
}

const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return dateFormatter.format(date)
}

const formatLayanan = (value) => {
    const layananMap = {
        ekonomis: 'Ekonomis',
        reguler: 'Reguler',
        express: 'Express',
        same_day: 'Same Day',
    }

    return layananMap[value] || value || '-'
}

const openDetail = (id) => {
    if (!id) {
        return
    }

    router.get(`/pengiriman/${id}`)
}

// [UBAH KHUSUS MOBILE & DESKTOP] Lazy Load ApexCharts (Hanya di-load saat dipanggil, tidak membebani initial load)
const ApexChart = defineAsyncComponent(() => import('vue3-apexcharts'))

// [UBAH KHUSUS MOBILE & DESKTOP] Konfigurasi ApexCharts Tipe Area Premium (Smooth & Gradient)
const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        height: '100%',
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            dynamicAnimation: { enabled: true, speed: 350 }
        }
    },
    colors: ['#6366F1'], // Warna Indigo premium
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 90, 100]
        }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: Array.isArray(props.chart_bulanan?.labels) ? props.chart_bulanan.labels : [],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: { colors: '#64748B', fontSize: '11px' }
        },
        tooltip: { enabled: false }
    },
    yaxis: {
        labels: {
            style: { colors: '#64748B', fontSize: '11px' },
            formatter: (val) => val.toFixed(0)
        }
    },
    grid: {
        borderColor: '#F1F5F9',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { top: 0, right: 0, bottom: 0, left: 10 }
    },
    tooltip: {
        theme: 'light',
        y: {
            formatter: function (val, { dataPointIndex }) {
                const revenue = props.chart_bulanan?.revenue?.[dataPointIndex] ?? 0
                return `${numberFormatter.format(val)} (Rev: ${formatCurrency(revenue)})`
            }
        }
    }
}))

// [UBAH KHUSUS MOBILE & DESKTOP] Data series untuk ApexCharts
const chartSeries = computed(() => ([
    {
        name: 'Pengiriman',
        data: Array.isArray(props.chart_bulanan?.jumlah) ? props.chart_bulanan.jumlah : []
    }
]))
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="space-y-4 sm:space-y-5">

            <div v-if="terlambat_count > 0" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 sm:px-4 sm:py-3">
                <div class="flex items-start gap-2.5 sm:gap-3">
                    <div class="mt-0.5 text-red-500">
                        <i class="bi bi-exclamation-circle-fill" />
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-red-600">
                            Ada {{ terlambat_count }} pengiriman terlambat
                        </p>
                        <p class="text-xs text-red-500">
                            Estimasi tiba sudah lewat dan status belum selesai.
                        </p>
                    </div>
                </div>
            </div>

            <!-- [UBAH KHUSUS DESKTOP] Memperlebar gap grid di desktop (sm:gap-6) agar lebih elegan -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6">
                <!-- [UPDATE] Menggunakan warna premium Xaviera Blue untuk jumlah pengiriman -->
                <StatCard title="Total Pengiriman" :value="stats.total_pengiriman" icon="bi-box-seam" color="xavieraBlue" />

                <!-- [UPDATE] Menggunakan warna premium Xaviera Gold untuk pendapatan -->
                <StatCard title="Total Pendapatan" :value="stats.total_pendapatan" icon="bi-cash-stack" color="xavieraGold"
                    prefix="Rp" />
            </div>

            <!-- [UBAH KHUSUS MOBILE] Kotak Grafik: Premium glassmorphism tipis, shadow lembut, desktop tetap aslinya -->
            <div class="card overflow-hidden rounded-[20px] border border-slate-100/60 bg-white/95 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm sm:rounded-xl sm:border sm:border-slate-200 sm:bg-white sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-[15px] font-bold tracking-tight text-slate-800 sm:text-base sm:font-semibold">
                            Tren 6 Bulan Pengiriman
                        </h2>
                        <p class="text-xs text-slate-500">
                            Jumlah pengiriman per bulan dengan tooltip revenue pengiriman terkirim.
                        </p>
                    </div>
                </div>

                <div class="h-44 w-full sm:h-48 md:h-52">
                    <!-- [UBAH KHUSUS MOBILE & DESKTOP] Menggunakan ApexCharts yang sangat smooth dan ringan -->
                    <ApexChart
                        v-if="hasChartData"
                        type="area"
                        height="100%"
                        :options="chartOptions"
                        :series="chartSeries"
                    />
                </div>

                <!-- [UBAH KHUSUS MOBILE & DESKTOP] Empty State Grafik Premium Kelas SaaS -->
                <div v-if="!hasChartData"
                    class="mt-4 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200/80 bg-slate-50/50 p-6 text-center transition-all hover:bg-slate-50">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="bi bi-graph-up text-lg"></i>
                    </div>
                    <p class="text-[13px] font-semibold tracking-tight text-slate-700">Belum Ada Data Grafik</p>
                    <p class="mt-1 text-[11px] font-medium text-slate-500 max-w-[200px]">Data pengiriman 6 bulan terakhir akan muncul di sini.</p>
                </div>
            </div>

            <!-- [UBAH KHUSUS MOBILE] Tabel dikelilingi kotak glassmorphism dan overflow scroll halus tanpa scrollbar -->
            <div class="card overflow-hidden rounded-[20px] border border-slate-100/60 bg-white/95 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm sm:rounded-xl sm:border sm:border-slate-200 sm:bg-white sm:shadow-sm sm:backdrop-blur-none">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5 sm:border-slate-200 sm:px-4 sm:py-3">
                    <div>
                        <h2 class="text-[15px] font-bold tracking-tight text-slate-800 sm:text-base sm:font-semibold">
                            10 Pengiriman Terbaru
                        </h2>
                        <p class="text-[11px] text-slate-500 sm:text-xs">
                            Klik baris untuk membuka detail pengiriman.
                        </p>
                    </div>
                </div>

                <!-- [UBAH KHUSUS MOBILE] Wrapper Horizontal Scroll Tanpa Scrollbar agar elegan -->
                <div v-if="latestItems.length > 0" class="w-full overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                    <table class="min-w-full divide-y divide-slate-100 sm:divide-slate-200">
                        <!-- [UBAH KHUSUS MOBILE & DESKTOP] Kepala Tabel (Thead) bergaya kokoh dan renggang -->
                        <thead class="bg-slate-50/80 backdrop-blur-sm border-b border-slate-100">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 sm:px-4 sm:py-3.5 text-left text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 whitespace-nowrap">
                                    No Resi
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 sm:px-4 sm:py-3.5 text-left text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 whitespace-nowrap">
                                    Pengirim
                                </th>
                                <th scope="col"
                                    class="hidden px-4 py-3 sm:px-4 sm:py-3.5 text-left text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 sm:table-cell whitespace-nowrap">
                                    Kota Tujuan
                                </th>
                                <th scope="col"
                                    class="hidden px-4 py-3 sm:px-4 sm:py-3.5 text-left text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 md:table-cell whitespace-nowrap">
                                    Layanan
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 sm:px-4 sm:py-3.5 text-left text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 whitespace-nowrap">
                                    Status
                                </th>
                                <th scope="col"
                                    class="hidden px-4 py-3 sm:px-4 sm:py-3.5 text-left text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 lg:table-cell whitespace-nowrap">
                                    Estimasi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            <tr v-for="item in latestItems" :key="item.id"
                                class="cursor-pointer hover:bg-slate-50/80 transition-colors duration-200"
                                :class="{ 'bg-red-50/50 hover:bg-red-50': item.is_terlambat }" tabindex="0"
                                @click="openDetail(item.id)" @keydown.enter.prevent="openDetail(item.id)"
                                @keydown.space.prevent="openDetail(item.id)">
                                <td class="px-4 py-3 sm:py-3.5 text-[13px] font-bold tracking-tight text-slate-800 whitespace-nowrap border-b border-slate-50">
                                    <div class="flex flex-col gap-1">
                                        <span>{{ item.nomor_resi }}</span>

                                        <span v-if="item.is_terlambat"
                                            class="inline-flex w-fit items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold tracking-wide text-red-600">
                                            Terlambat
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3 sm:py-3.5 text-[13px] font-medium text-slate-600 whitespace-nowrap border-b border-slate-50">
                                    {{ item.pengirim_nama || '-' }}
                                </td>

                                <td class="hidden px-4 py-3 sm:py-3.5 text-[13px] font-medium text-slate-600 sm:table-cell whitespace-nowrap border-b border-slate-50">
                                    {{ item.tujuan_kota || '-' }}
                                </td>

                                <td class="hidden px-4 py-3 sm:py-3.5 text-[13px] font-medium text-slate-600 md:table-cell whitespace-nowrap border-b border-slate-50">
                                    {{ formatLayanan(item.layanan) }}
                                </td>

                                <td class="px-4 py-3 sm:py-3.5 text-[13px] border-b border-slate-50 whitespace-nowrap">
                                    <StatusBadge :status="item.status" />
                                </td>

                                <td class="hidden px-4 py-3 sm:py-3.5 text-[13px] font-medium text-slate-600 lg:table-cell whitespace-nowrap border-b border-slate-50">
                                    {{ formatDate(item.estimasi_tiba) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- [UBAH KHUSUS MOBILE & DESKTOP] Empty State Tabel Kelas SaaS -->
                <div v-else class="mx-4 my-6 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200/80 bg-slate-50/50 p-8 text-center">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="bi bi-inbox text-lg"></i>
                    </div>
                    <p class="text-[13px] font-semibold tracking-tight text-slate-700">Tidak Ada Pengiriman</p>
                    <p class="mt-1 text-[11px] font-medium text-slate-500">Belum ada data pengiriman terbaru saat ini.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>