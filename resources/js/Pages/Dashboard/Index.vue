<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

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
const chartCanvas = ref(null)

const userName = computed(() => page.props.auth?.user?.nama || 'Admin')
const latestItems = computed(() => (Array.isArray(props.terbaru) ? props.terbaru : []))

let chartInstance = null

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

const destroyChart = () => {
    if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
    }
}

const renderChart = async () => {
    await nextTick()

    if (!chartCanvas.value) {
        return
    }

    destroyChart()

    try {
        const { default: Chart } = await import('chart.js/auto')

        chartInstance = new Chart(chartCanvas.value, {
            type: 'bar',
            data: {
                labels: Array.isArray(props.chart_bulanan?.labels) ? props.chart_bulanan.labels : [],
                datasets: [
                    {
                        label: 'Jumlah Pengiriman',
                        data: Array.isArray(props.chart_bulanan?.jumlah) ? props.chart_bulanan.jumlah : [],
                        borderRadius: 8,
                        maxBarThickness: 34,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 700,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const index = context.dataIndex
                                const jumlah = props.chart_bulanan?.jumlah?.[index] ?? 0
                                const revenue = props.chart_bulanan?.revenue?.[index] ?? 0

                                return [
                                    `Pengiriman: ${numberFormatter.format(Number(jumlah || 0))}`,
                                    `Revenue: ${formatCurrency(revenue)}`,
                                ]
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#64748B',
                            font: {
                                size: 11,
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#64748B',
                            font: {
                                size: 11,
                            },
                        },
                        grid: {
                            color: '#E2E8F0',
                        },
                    },
                },
            },
        })
    } catch {
        destroyChart()
    }
}

onMounted(() => {
    renderChart()
})

onBeforeUnmount(() => {
    destroyChart()
})
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout>
        <div class="space-y-5">
            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                <h2 class="text-base font-semibold text-slate-800">
                    Selamat datang, {{ userName }}
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Ringkasan aktivitas pengiriman, pendapatan, tren bulanan, dan data terbaru.
                </p>
            </div>

            <div v-if="terlambat_count > 0" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                <div class="flex items-start gap-3">
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

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <StatCard title="Total Pengiriman" :value="stats.total_pengiriman" icon="bi-box-seam" color="indigo" />

                <StatCard title="Total Pendapatan" :value="stats.total_pendapatan" icon="bi-cash-stack" color="emerald"
                    prefix="Rp" />
            </div>

            <div class="card p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            Tren 6 Bulan Pengiriman
                        </h2>
                        <p class="text-xs text-slate-500">
                            Jumlah pengiriman per bulan dengan tooltip revenue pengiriman terkirim.
                        </p>
                    </div>
                </div>

                <div class="h-40 sm:h-44">
                    <canvas ref="chartCanvas" aria-label="Grafik tren pengiriman 6 bulan" />
                </div>

                <div v-if="!hasChartData"
                    class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                    Belum ada data pengiriman pada 6 bulan terakhir.
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            10 Pengiriman Terbaru
                        </h2>
                        <p class="text-xs text-slate-500">
                            Klik baris untuk membuka detail pengiriman.
                        </p>
                    </div>
                </div>

                <div v-if="latestItems.length > 0" class="table-wrap">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col"
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    No Resi
                                </th>
                                <th scope="col"
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Pengirim
                                </th>
                                <th scope="col"
                                    class="hidden px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:table-cell">
                                    Kota Tujuan
                                </th>
                                <th scope="col"
                                    class="hidden px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell">
                                    Layanan
                                </th>
                                <th scope="col"
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th scope="col"
                                    class="hidden px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell">
                                    Estimasi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="item in latestItems" :key="item.id"
                                class="cursor-pointer transition-colors hover:bg-slate-50"
                                :class="{ 'bg-red-50 hover:bg-red-50/80': item.is_terlambat }" tabindex="0"
                                @click="openDetail(item.id)" @keydown.enter.prevent="openDetail(item.id)"
                                @keydown.space.prevent="openDetail(item.id)">
                                <td class="px-3 py-2 text-sm font-medium text-slate-700">
                                    <div class="flex flex-col gap-1">
                                        <span>{{ item.nomor_resi }}</span>

                                        <span v-if="item.is_terlambat"
                                            class="inline-flex w-fit items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                            Terlambat
                                        </span>
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-sm text-slate-600">
                                    {{ item.pengirim_nama || '-' }}
                                </td>

                                <td class="hidden px-3 py-2 text-sm text-slate-600 sm:table-cell">
                                    {{ item.tujuan_kota || '-' }}
                                </td>

                                <td class="hidden px-3 py-2 text-sm text-slate-600 md:table-cell">
                                    {{ formatLayanan(item.layanan) }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <StatusBadge :status="item.status" />
                                </td>

                                <td class="hidden px-3 py-2 text-sm text-slate-600 lg:table-cell">
                                    {{ formatDate(item.estimasi_tiba) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-4 py-8 text-center text-sm text-slate-500">
                    Belum ada data pengiriman terbaru.
                </div>
            </div>
        </div>
    </AppLayout>
</template>