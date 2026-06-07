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

// Warna berbeda untuk setiap segment rute (seperti referensi maps)
const segmentColors = [
    '#16A34A', // hijau — pickup/awal
    '#2563EB', // biru — perjalanan
    '#7C3AED', // ungu — transit
    '#DC2626', // merah — mendekati tujuan
    '#EA580C', // oranye — sedang diantar
    '#059669', // emerald — terkirim
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
    const labels = {
        ekonomis: 'Ekonomis',
        reguler: 'Reguler',
        express: 'Express',
        same_day: 'Same Day',
    }

    return labels[value] || value
}

const statusLabel = (value) => {
    const labels = {
        pending: 'Pending',
        diproses: 'Diproses',
        dalam_perjalanan: 'Dalam Perjalanan',
        tiba_di_kota_tujuan: 'Tiba di Kota Tujuan',
        sedang_diantar: 'Sedang Diantar',
        terkirim: 'Terkirim',
        gagal: 'Gagal',
        dibatalkan: 'Dibatalkan',
    }

    return labels[value] || value
}

const getCoords = () => {
    const asal = props.pengiriman.pengirim_kota
    const tujuan = props.pengiriman.penerima_kota

    return {
        asal: [Number(asal.latitude), Number(asal.longitude)],
        tujuan: [Number(tujuan.latitude), Number(tujuan.longitude)],
    }
}

/**
 * Buat waypoints (titik-titik transit) di antara asal dan tujuan
 * berdasarkan tracking history yang sudah dilalui
 */
const buildWaypoints = (from, to) => {
    const points = [from]
    const totalSteps = statusSteps.length

    // Buat titik-titik intermediate berdasarkan jumlah step
    for (let i = 1; i < totalSteps - 1; i++) {
        const t = i / (totalSteps - 1)

        // Variasi posisi dengan offset lateral agar terlihat seperti rute nyata
        const latDiff = to[0] - from[0]
        const lngDiff = to[1] - from[1]
        const dist = Math.sqrt(latDiff * latDiff + lngDiff * lngDiff)

        // Offset perpendicular untuk membuat jalur tidak lurus
        const perpScale = dist * 0.08 * Math.sin(t * Math.PI)
        const angle = Math.atan2(latDiff, lngDiff) + Math.PI / 2
        const offsetLat = Math.cos(angle) * perpScale * (i % 2 === 0 ? 1 : -1)
        const offsetLng = Math.sin(angle) * perpScale * (i % 2 === 0 ? 1 : -1)

        const lat = from[0] + latDiff * t + offsetLat
        const lng = from[1] + lngDiff * t + offsetLng

        points.push([lat, lng])
    }

    points.push(to)
    return points
}

/**
 * Buat smooth curve antara 2 titik dengan Bezier
 */
const smoothSegment = (p1, p2, segments = 15) => {
    const points = []
    const latDiff = p2[0] - p1[0]
    const lngDiff = p2[1] - p1[1]
    const dist = Math.sqrt(latDiff * latDiff + lngDiff * lngDiff)

    // Offset control point untuk lengkungan halus
    const offset = dist * 0.2
    const midLat = (p1[0] + p2[0]) / 2 + (lngDiff > 0 ? offset * 0.3 : -offset * 0.3)
    const midLng = (p1[1] + p2[1]) / 2 + (latDiff > 0 ? -offset * 0.3 : offset * 0.3)

    for (let i = 0; i <= segments; i++) {
        const t = i / segments
        const oneMinusT = 1 - t

        // Quadratic Bezier
        const lat = oneMinusT * oneMinusT * p1[0] + 2 * oneMinusT * t * midLat + t * t * p2[0]
        const lng = oneMinusT * oneMinusT * p1[1] + 2 * oneMinusT * t * midLng + t * t * p2[1]
        points.push([lat, lng])
    }

    return points
}

/**
 * Buat numbered circle marker (seperti di referensi foto)
 */
const createNumberedIcon = (number, color, size = 24) => {
    return L.divIcon({
        html: `<div style="
            background:${color};
            width:${size}px;height:${size}px;
            border-radius:50%;
            border:2px solid white;
            box-shadow:0 2px 6px rgba(0,0,0,0.35);
            display:flex;align-items:center;justify-content:center;
            font-size:${size > 24 ? 13 : 11}px;color:white;font-weight:700;
            font-family:Inter,system-ui,sans-serif;
        ">${number}</div>`,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
        popupAnchor: [0, -(size / 2 + 4)],
    })
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

    // Google Maps-style tile dari CartoDB — mirip tampilan Google Maps
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map)

    const currentIdx = statusIndex.value
    const asalText = props.pengiriman.pengirim_kota?.nama_kota || 'Asal'
    const tujuanText = props.pengiriman.penerima_kota?.nama_kota || 'Tujuan'

    // Buat waypoints di antara asal dan tujuan
    const waypoints = buildWaypoints(asal, tujuan)

    // Gambar setiap segment dengan warna berbeda
    for (let i = 0; i < waypoints.length - 1; i++) {
        const segmentPoints = smoothSegment(waypoints[i], waypoints[i + 1])
        const color = segmentColors[i % segmentColors.length]
        const isPassed = i < currentIdx
        const isCurrent = i === currentIdx
        const isFuture = i > currentIdx

        L.polyline(segmentPoints, {
            color: isFuture ? '#CBD5E1' : color,
            weight: isPassed || isCurrent ? 5 : 3,
            opacity: isFuture ? 0.4 : 0.85,
            dashArray: isFuture ? '8, 12' : null,
            lineCap: 'round',
            lineJoin: 'round',
        }).addTo(map)
    }

    // Gambar numbered waypoint markers
    waypoints.forEach((point, idx) => {
        const isFirst = idx === 0
        const isLast = idx === waypoints.length - 1
        const isPassed = idx <= currentIdx
        const isCurrent = idx === currentIdx

        let color = '#94A3B8' // abu-abu (belum dilewati)
        let size = 22

        if (isFirst) {
            color = '#16A34A' // hijau
            size = 28
        } else if (isLast) {
            color = '#DC2626' // merah
            size = 28
        } else if (isCurrent) {
            color = '#6366F1' // indigo (posisi saat ini)
            size = 28
        } else if (isPassed) {
            color = segmentColors[idx % segmentColors.length]
            size = 24
        }

        const label = isFirst ? 'A' : isLast ? 'B' : String(idx)
        const marker = L.marker(point, { icon: createNumberedIcon(label, color, size) }).addTo(map)

        // Popup info
        let popupText = ''
        if (isFirst) {
            popupText = `<b>Kota Asal</b><br>${asalText}`
        } else if (isLast) {
            popupText = `<b>Kota Tujuan</b><br>${tujuanText}`
        } else if (isCurrent) {
            popupText = `<b>Posisi Saat Ini</b><br>${statusLabel(props.pengiriman.status)}`
        } else if (isPassed) {
            popupText = `<b>Transit ${idx}</b><br>${statusSteps[idx]?.label || ''}`
        } else {
            popupText = `<b>Rute ${idx}</b><br>${statusSteps[idx]?.label || 'Belum dilewati'}`
        }

        marker.bindPopup(`<div style="font-size:12px;text-align:center;">${popupText}</div>`)

        // Buka popup posisi saat ini otomatis
        if (isCurrent && !isFirst && !isLast) {
            marker.openPopup()
        }
    })

    // Fit bounds
    map.fitBounds([asal, tujuan], {
        padding: [50, 50],
    })

    setTimeout(() => {
        if (map) map.invalidateSize()
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
        <div class="space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white/50 p-4 rounded-[20px] backdrop-blur-md border border-slate-100 shadow-sm">
                <div>
                    <h2 class="text-xl font-black text-[#1a365d] tracking-tight">Detail Tracking</h2>
                    <p class="text-sm text-slate-500 font-medium">
                        Pantau status, rute, dan riwayat pengiriman secara realtime.
                    </p>
                </div>

                <Link :href="route('tracking.index')" class="btn-secondary w-full justify-center sm:w-auto !rounded-xl h-[42px] px-5 bg-white shadow-sm hover:shadow-md transition-shadow">
                    <i class="bi bi-arrow-left" />
                    Lacak Lain
                </Link>
            </div>

            <!-- Info Pengiriman -->
            <div class="card p-5 sm:p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 bg-gradient-to-br from-white to-slate-50">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-4">
                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Nomor Resi</p>
                        <p class="mt-1 text-[15px] font-black text-[#1a365d] break-all">{{ pengiriman.nomor_resi }}</p>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Rute</p>
                        <p class="mt-1 text-[13px] font-semibold text-slate-700 truncate">{{ routeText }}</p>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</p>
                        <div class="mt-1">
                            <StatusBadge :status="pengiriman.status" />
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Estimasi Tiba</p>
                        <p class="mt-1 text-[13px] font-semibold text-slate-700">{{ formatDate(pengiriman.estimasi_tiba) }}</p>
                    </div>
                </div>
            </div>

            <!-- Progress Stepper -->
            <div class="card p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Progress Pengiriman</h3>
                        <p class="text-xs text-slate-500">
                            Layanan: {{ formatService(pengiriman.jenis_layanan) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto pb-1">
                    <div class="flex min-w-[560px] items-start justify-between gap-1">
                        <div v-for="(item, index) in statusSteps" :key="item.key" class="flex flex-1 items-start">
                            <div class="flex w-full items-center">
                                <div class="flex flex-col items-center text-center">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-semibold sm:h-9 sm:w-9"
                                        :class="index < statusIndex
                                            ? 'border-emerald-500 bg-emerald-500 text-white'
                                            : index === statusIndex
                                                ? 'border-indigo-500 bg-indigo-500 text-white'
                                                : 'border-slate-300 bg-white text-slate-400'"
                                        :style="index < statusIndex
                                            ? { borderColor: segmentColors[index], backgroundColor: segmentColors[index] }
                                            : {}">
                                        <i v-if="index < statusIndex" class="bi bi-check-lg" />
                                        <span v-else>{{ index + 1 }}</span>
                                    </div>

                                    <p class="mt-1.5 text-[10px] font-medium leading-tight sm:text-[11px]"
                                        :class="index <= statusIndex ? 'text-slate-700' : 'text-slate-400'">
                                        {{ item.label }}
                                    </p>
                                </div>

                                <div v-if="index !== statusSteps.length - 1" class="mt-3.5 h-1 flex-1 rounded-full sm:mt-4"
                                    :style="index < statusIndex
                                        ? { backgroundColor: segmentColors[index] }
                                        : { backgroundColor: '#E2E8F0' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peta Perjalanan -->
            <div class="card p-4">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">Peta Perjalanan</h3>
                    <p class="text-xs text-slate-500">
                        Rute pengiriman dengan jalur berwarna berbeda per segment.
                    </p>
                </div>

                <!-- Legend warna -->
                <div class="mb-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-block h-2.5 w-2.5 rounded-full" style="background:#16A34A"></span>
                        Asal
                    </span>
                    <span v-for="(step, idx) in statusSteps.slice(1, -1)" :key="step.key"
                        class="inline-flex items-center gap-1">
                        <span class="inline-block h-2.5 w-2.5 rounded-full"
                            :style="{ background: segmentColors[idx + 1] }"></span>
                        {{ step.label }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-block h-2.5 w-2.5 rounded-full" style="background:#DC2626"></span>
                        Tujuan
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-block h-4 w-4 border-t-2 border-dashed border-slate-300"></span>
                        Belum Dilewati
                    </span>
                </div>

                <div v-if="hasValidCoords" ref="mapEl"
                    class="h-[260px] w-full overflow-hidden rounded-[20px] border-4 border-white shadow-lg sm:h-[340px] md:h-[400px]"></div>

                <div v-else
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    Koordinat kota belum lengkap sehingga peta tidak dapat ditampilkan.
                </div>
            </div>

            <!-- Timeline Tracking -->
            <div class="card p-5 sm:p-6 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-[14px] font-black uppercase tracking-widest text-[#1a365d]">Timeline Tracking</h3>
                        <p class="text-xs text-slate-500 mt-1">Riwayat perjalanan terbaru di urutan atas.</p>
                    </div>
                </div>

                <div v-if="pengiriman.tracking_histories.length" class="space-y-4 sm:space-y-5 pl-2">
                    <div v-for="(item, index) in pengiriman.tracking_histories" :key="item.id" class="relative pl-8 sm:pl-10">
                        <div v-if="index !== pengiriman.tracking_histories.length - 1"
                            class="absolute left-3 sm:left-[17px] top-6 h-full w-[2px] bg-slate-100/80"></div>

                        <div
                            class="absolute left-0 sm:left-2 top-2 h-6 w-6 rounded-full border-4 border-white shadow-sm flex items-center justify-center z-10"
                            :style="{ backgroundColor: segmentColors[index % segmentColors.length] }">
                            <div class="h-2 w-2 rounded-full bg-white"></div>
                        </div>

                        <div class="rounded-2xl border border-slate-100/50 bg-slate-50/50 p-4 transition-all hover:bg-white hover:shadow-md hover:border-slate-200">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between mb-2">
                                <p class="text-[13px] font-black tracking-wide" :style="{ color: segmentColors[index % segmentColors.length] }">
                                    {{ statusLabel(item.status_baru) }}
                                </p>
                                <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-md shadow-sm border border-slate-100">
                                    <i class="bi bi-clock text-[10px] text-slate-400"></i>
                                    <p class="text-[11px] font-bold text-slate-500">
                                        {{ formatDate(item.created_at, true) }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-[12px] font-semibold text-slate-700 flex items-start gap-1.5 mt-1">
                                <i class="bi bi-geo-alt-fill text-slate-400 mt-0.5"></i>
                                {{ item.lokasi }}
                            </p>
                            <p class="mt-2 text-[13px] text-slate-600 bg-white p-3 rounded-xl border border-slate-100 leading-relaxed">{{ item.keterangan }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm font-medium text-slate-500">
                    Belum ada riwayat tracking untuk resi ini.
                </div>
            </div>
        </div>
    </PublicLayout>
</template>