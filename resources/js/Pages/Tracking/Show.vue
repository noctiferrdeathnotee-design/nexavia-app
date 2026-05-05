<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link } from '@inertiajs/vue3'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
    pengiriman: {
        type: Object,
        required: true,
    },
})

const mapEl = ref(null)

let map = null

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
})

const statusSteps = [
    { key: 'pending', label: 'Pending' },
    { key: 'diproses', label: 'Diproses' },
    { key: 'dalam_perjalanan', label: 'Perjalanan' },
    { key: 'tiba_di_kota_tujuan', label: 'Tiba Kota' },
    { key: 'sedang_diantar', label: 'Diantar' },
    { key: 'terkirim', label: 'Terkirim' },
]

const statusIndex = computed(() => {
    return statusSteps.findIndex((item) => item.key === props.pengiriman.status)
})

const routeText = computed(() => {
    return `${props.pengiriman.pengirim_kota?.nama_kota || '-'} → ${props.pengiriman.penerima_kota?.nama_kota || '-'}`
})

const hasValidCoords = computed(() => {
    const asal = props.pengiriman.pengirim_kota
    const tujuan = props.pengiriman.penerima_kota

    if (!asal || !tujuan) {
        return false
    }

    const asalLat = Number(asal.latitude)
    const asalLng = Number(asal.longitude)
    const tujuanLat = Number(tujuan.latitude)
    const tujuanLng = Number(tujuan.longitude)

    return Number.isFinite(asalLat)
        && Number.isFinite(asalLng)
        && Number.isFinite(tujuanLat)
        && Number.isFinite(tujuanLng)
        && !(asalLat === 0 && asalLng === 0)
        && !(tujuanLat === 0 && tujuanLng === 0)
})

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

const statusLabel = (value) => {
    const map = {
        pending: 'Pending',
        diproses: 'Diproses',
        dalam_perjalanan: 'Dalam Perjalanan',
        tiba_di_kota_tujuan: 'Tiba di Kota Tujuan',
        sedang_diantar: 'Sedang Diantar',
        terkirim: 'Terkirim',
        gagal: 'Gagal',
        dibatalkan: 'Dibatalkan',
    }

    return map[value] || value
}

const getCoords = () => {
    const asal = props.pengiriman.pengirim_kota
    const tujuan = props.pengiriman.penerima_kota

    return {
        asal: [Number(asal.latitude), Number(asal.longitude)],
        tujuan: [Number(tujuan.latitude), Number(tujuan.longitude)],
    }
}

const getProgress = () => {
    const status = props.pengiriman.status
    if (status === 'pending') return 0.05
    if (status === 'diproses') return 0.15
    if (status === 'dalam_perjalanan') return 0.50
    if (status === 'tiba_di_kota_tujuan') return 0.85
    if (status === 'sedang_diantar') return 0.95
    if (status === 'terkirim') return 1.0
    return null
}

const interpolateCoords = (from, to, progress) => {
    return [
        from[0] + (to[0] - from[0]) * progress,
        from[1] + (to[1] - from[1]) * progress,
    ]
}

const destroyMap = () => {
    if (map) {
        map.remove()
        map = null
    }
}

const setupMap = async () => {
    if (!mapEl.value || !hasValidCoords.value) {
        return
    }

    await nextTick()

    destroyMap()

    const { asal, tujuan } = getCoords()

    map = L.map(mapEl.value, {
        scrollWheelZoom: false,
        zoomControl: true,
    })

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map)

    const progress = getProgress()
    const asalText = props.pengiriman.pengirim_kota?.nama_kota || 'Kota Asal'
    const tujuanText = props.pengiriman.penerima_kota?.nama_kota || 'Kota Tujuan'

    // 1. Mark Origin
    L.marker(asal)
        .addTo(map)
        .bindPopup(`<div style="font-size:12px;text-align:center;"><b>Titik Awal</b><br>${asalText}</div>`)

    // 2. Mark Destination
    L.marker(tujuan)
        .addTo(map)
        .bindPopup(`<div style="font-size:12px;text-align:center;"><b>Titik Tujuan</b><br>${tujuanText}</div>`)

    // 3. Draw Route
    if (progress !== null && progress > 0 && progress < 1) {
        // In transit
        const currentLoc = interpolateCoords(asal, tujuan, progress)
        
        // Rute yang sudah dilewati (Solid Line)
        L.polyline([asal, currentLoc], {
            color: '#3B82F6', // Blue
            weight: 3,
        }).addTo(map)

        // Rute yang belum dilewati (Dashed Line)
        L.polyline([currentLoc, tujuan], {
            color: '#94A3B8', // Slate/Gray
            weight: 3,
            dashArray: '6, 8',
        }).addTo(map)

        // Marker posisi saat ini
        L.marker(currentLoc)
            .addTo(map)
            .bindPopup(`<div style="font-size:12px;text-align:center;"><b>Posisi Paket</b><br>${statusLabel(props.pengiriman.status)}</div>`)
            .openPopup()
            
    } else {
        // Belum dikirim atau sudah sampai
        L.polyline([asal, tujuan], {
            color: progress === 1 ? '#10B981' : '#94A3B8', // Green if done, gray if pending
            weight: 3,
            dashArray: progress === 1 ? '' : '6, 8',
        }).addTo(map)
    }

    map.fitBounds([asal, tujuan], {
        padding: [50, 50],
    })

    setTimeout(() => {
        if (map) {
            map.invalidateSize()
        }
    }, 150)
}

onMounted(() => {
    setupMap()
})

onBeforeUnmount(() => {
    destroyMap()
})
</script>

<template>

    <Head title="Detail Tracking" />

    <PublicLayout>
        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Detail Tracking</h2>
                    <p class="text-sm text-slate-500">
                        Pantau status, rute, dan riwayat pengiriman secara realtime.
                    </p>
                </div>

                <Link :href="route('tracking.index')" class="btn-secondary w-full justify-center sm:w-auto">
                    <i class="bi bi-arrow-left" />
                    Lacak Lain
                </Link>
            </div>

            <div class="card p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-xs text-slate-500">Nomor Resi</p>
                        <p class="font-semibold text-slate-800">{{ pengiriman.nomor_resi }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">Rute</p>
                        <p class="text-sm text-slate-700">{{ routeText }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">Status</p>
                        <div class="mt-1">
                            <StatusBadge :status="pengiriman.status" />
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">Estimasi Tiba</p>
                        <p class="text-sm text-slate-700">{{ formatDate(pengiriman.estimasi_tiba) }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Progress Pengiriman</h3>
                        <p class="text-xs text-slate-500">
                            Layanan: {{ formatService(pengiriman.jenis_layanan) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="flex min-w-[620px] items-start justify-between gap-2">
                        <div v-for="(item, index) in statusSteps" :key="item.key" class="flex flex-1 items-start">
                            <div class="flex w-full items-center">
                                <div class="flex flex-col items-center text-center">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-xs font-semibold"
                                        :class="index < statusIndex
                                            ? 'border-emerald-500 bg-emerald-500 text-white'
                                            : index === statusIndex
                                                ? 'border-indigo-500 bg-indigo-500 text-white'
                                                : 'border-slate-300 bg-white text-slate-400'">
                                        <i v-if="index < statusIndex" class="bi bi-check-lg" />
                                        <span v-else>{{ index + 1 }}</span>
                                    </div>

                                    <p class="mt-2 text-[11px] font-medium"
                                        :class="index <= statusIndex ? 'text-slate-700' : 'text-slate-400'">
                                        {{ item.label }}
                                    </p>
                                </div>

                                <div v-if="index !== statusSteps.length - 1" class="mt-4 h-1 flex-1 rounded-full"
                                    :class="index < statusIndex ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">Peta Perjalanan</h3>
                    <p class="text-xs text-slate-500">
                        Kota asal, kota tujuan, dan posisi paket berdasarkan status saat ini.
                    </p>
                </div>

                <div v-if="hasValidCoords" ref="mapEl"
                    class="h-[220px] w-full overflow-hidden rounded-xl border border-slate-200 sm:h-[280px]"></div>

                <div v-else
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    Koordinat kota belum lengkap sehingga peta tidak dapat ditampilkan.
                </div>
            </div>

            <div class="card p-4">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">Timeline Tracking</h3>
                    <p class="text-xs text-slate-500">
                        Riwayat terbaru tampil di bagian paling atas.
                    </p>
                </div>

                <div v-if="pengiriman.tracking_histories.length" class="space-y-4">
                    <div v-for="(item, index) in pengiriman.tracking_histories" :key="item.id" class="relative pl-6">
                        <div v-if="index !== pengiriman.tracking_histories.length - 1"
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

                            <p class="mt-1 text-xs text-slate-500">{{ item.lokasi }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ item.keterangan }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-sm text-slate-500">
                    Belum ada riwayat tracking.
                </div>
            </div>
        </div>
    </PublicLayout>
</template>