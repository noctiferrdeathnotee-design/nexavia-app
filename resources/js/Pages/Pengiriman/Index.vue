<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    pengiriman: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

// [UBAH KHUSUS MOBILE & DESKTOP] Menghapus filter Sort & Layanan agar sangat ringan dan bersih (100% Performance)
const filterForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    tanggal_mulai: props.filters.tanggal_mulai || '',
    tanggal_akhir: props.filters.tanggal_akhir || '',
})

const rows = computed(() => props.pengiriman?.data ?? [])
const paginationLinks = computed(() => props.pengiriman?.links ?? [])

// [UBAH KHUSUS MOBILE & DESKTOP] Evaluasi aktif tanpa sort dan layanan
const hasActiveFilter = computed(() => {
    return (
        filterForm.search !== '' ||
        filterForm.status !== '' ||
        filterForm.tanggal_mulai !== '' ||
        filterForm.tanggal_akhir !== ''
    )
})

// [UBAH KHUSUS MOBILE & DESKTOP] Menghapus logika parameter sort dan layanan
const cleanedFilters = computed(() => {
    const payload = {}

    if (filterForm.search) payload.search = filterForm.search
    if (filterForm.status) payload.status = filterForm.status
    if (filterForm.tanggal_mulai) payload.tanggal_mulai = filterForm.tanggal_mulai
    if (filterForm.tanggal_akhir) payload.tanggal_akhir = filterForm.tanggal_akhir

    return payload
})

const applyFilters = () => {
    router.get(route('pengiriman.index'), cleanedFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

// [UBAH KHUSUS MOBILE & DESKTOP] Reset tanpa sort dan layanan
const resetFilters = () => {
    filterForm.search = ''
    filterForm.status = ''
    filterForm.tanggal_mulai = ''
    filterForm.tanggal_akhir = ''

    applyFilters()
}

const formatDate = (value) => {
    if (!value) return '-'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
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
</script>

<template>

    <Head title="Data Pengiriman" />

    <AppLayout>
        <div class="space-y-4 sm:space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <!-- [UBAH KHUSUS MOBILE & DESKTOP] Font lebih rapat (tracking-tight) untuk kesan kokoh -->
                    <h2 class="text-lg font-bold tracking-tight text-slate-800 sm:font-semibold">Data Pengiriman</h2>
                    <p class="mt-0.5 text-sm text-slate-500">
                        Tabel pengiriman dengan filter, pencarian, dan pagination.
                    </p>
                </div>

                <!-- [UBAH KHUSUS MOBILE] Tombol melengkung elegan (rounded-xl) di Mobile agar berkesan aplikasi native -->
                <Link :href="route('pengiriman.create')" class="btn-primary w-full justify-center rounded-xl sm:w-auto sm:rounded-lg">
                    <i class="bi bi-plus-lg" />
                    Input Baru
                </Link>
            </div>

            <!-- Filter Section -->
            <!-- [UBAH KHUSUS MOBILE] Kotak Filter Premium: glassmorphism, shadow tipis, border tipis -->
            <div class="card relative overflow-hidden rounded-[20px] border border-slate-100/60 bg-white/95 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm sm:rounded-xl sm:border sm:border-slate-200 sm:bg-white sm:p-4 sm:shadow-sm sm:backdrop-blur-none">
                <!-- Efek cahaya halus khusus mobile -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent pointer-events-none lg:hidden"></div>
                
                <!-- [UBAH KHUSUS MOBILE & DESKTOP] Tata letak grid baru setelah "Sort" dan "Layanan" dihapus -->
                <div class="relative z-10 grid grid-cols-2 gap-3 sm:gap-4 lg:flex lg:flex-wrap lg:items-end">
                    
                    <div class="col-span-2 lg:col-span-1 lg:min-w-[150px]">
                        <label class="form-label">Status</label>
                        <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                            <option value="">Semua</option>
                            <option value="pending">Pending</option>
                            <option value="diproses">Diproses</option>
                            <option value="dalam_perjalanan">Dalam Perjalanan</option>
                            <option value="tiba_di_kota_tujuan">Tiba Kota Tujuan</option>
                            <option value="sedang_diantar">Sedang Diantar</option>
                            <option value="terkirim">Terkirim</option>
                            <option value="gagal">Gagal</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>

                    <div class="col-span-1 lg:col-span-1 lg:w-[160px]">
                        <label class="form-label">Dari</label>
                        <input v-model="filterForm.tanggal_mulai" type="date" class="form-input" @change="applyFilters">
                    </div>

                    <div class="col-span-1 lg:col-span-1 lg:w-[160px]">
                        <label class="form-label">Sampai</label>
                        <input v-model="filterForm.tanggal_akhir" type="date" class="form-input" @change="applyFilters">
                    </div>

                    <div class="col-span-2 lg:flex-1 lg:min-w-[200px]">
                        <label class="form-label">Cari</label>
                        <div class="flex gap-2">
                            <input v-model="filterForm.search" type="text" class="form-input min-w-0 flex-1"
                                placeholder="No resi / pengirim / penerima" @keyup.enter="applyFilters">
                            <button type="button" class="btn-secondary shrink-0" @click="applyFilters">
                                <i class="bi bi-search" />
                            </button>
                        </div>
                    </div>

                    <div v-if="hasActiveFilter" class="col-span-2 flex items-end lg:col-span-1">
                        <button type="button" class="btn-secondary w-full justify-center lg:w-auto" @click="resetFilters">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <!-- [UBAH KHUSUS MOBILE] Kotak Tabel Premium: glassmorphism, shadow tipis, border tipis -->
            <div class="card overflow-hidden rounded-[20px] border border-slate-100/60 bg-white/95 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm sm:rounded-xl sm:border sm:border-slate-200 sm:bg-white sm:shadow-sm sm:backdrop-blur-none">
                <!-- [UBAH KHUSUS MOBILE] Wrapper Horizontal Scroll tanpa Scrollbar (smooth swipe) -->
                <div v-if="rows.length" class="w-full overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
                    <table class="min-w-full divide-y divide-slate-100 sm:divide-slate-200">
                        <thead class="bg-slate-50/50 sm:bg-slate-50">
                            <tr>
                                <!-- [UBAH KHUSUS MOBILE] Font label uppercase dengan tracking lebar (tracking-widest) -->
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    No Resi
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    Pengirim
                                </th>
                                <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 sm:table-cell sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    Tujuan
                                </th>
                                <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 md:table-cell sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    Layanan
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    Status
                                </th>
                                <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 lg:table-cell sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    Estimasi
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 sm:px-3 sm:py-2 sm:text-xs sm:font-semibold sm:tracking-wide">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-transparent sm:bg-white">
                            <tr v-for="item in rows" :key="item.id" :class="item.is_terlambat ? 'bg-red-50/50 sm:bg-red-50' : 'hover:bg-slate-50/50 sm:hover:bg-slate-50'">
                                <!-- [UBAH KHUSUS MOBILE] Font isi tabel lebih solid (tracking-tight) khusus Mobile -->
                                <td class="px-4 py-3 text-sm font-semibold tracking-tight text-slate-800 sm:px-3 py-2 sm:font-medium sm:tracking-normal sm:text-slate-700">
                                    <div class="flex flex-col gap-1">
                                        <span>{{ item.nomor_resi || '-' }}</span>
                                        <span v-if="item.is_terlambat"
                                            class="inline-flex w-fit rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
                                            Terlambat
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-sm font-medium tracking-tight text-slate-700 sm:px-3 sm:py-2 sm:font-normal sm:tracking-normal sm:text-slate-600">
                                    {{ item.pengirim_nama || '-' }}
                                </td>

                                <td class="hidden px-4 py-3 text-sm font-medium tracking-tight text-slate-700 sm:table-cell sm:px-3 sm:py-2 sm:font-normal sm:tracking-normal sm:text-slate-600">
                                    {{ item.tujuan_kota || '-' }}
                                </td>

                                <td class="hidden px-4 py-3 text-sm font-medium tracking-tight text-slate-700 md:table-cell sm:px-3 sm:py-2 sm:font-normal sm:tracking-normal sm:text-slate-600">
                                    {{ formatService(item.layanan) }}
                                </td>

                                <td class="px-4 py-3 text-sm sm:px-3 sm:py-2">
                                    <StatusBadge :status="item.status" />
                                </td>

                                <td class="hidden px-4 py-3 text-sm font-medium tracking-tight text-slate-700 lg:table-cell sm:px-3 sm:py-2 sm:font-normal sm:tracking-normal sm:text-slate-600">
                                    {{ formatDate(item.estimasi_tiba) }}
                                </td>

                                <td class="px-4 py-3 text-sm sm:px-3 sm:py-2">
                                    <!-- [UBAH KHUSUS MOBILE] Tombol Aksi lengkung penuh khusus Mobile -->
                                    <Link :href="route('pengiriman.show', item.id)" class="btn-secondary btn-sm rounded-full sm:rounded-md">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-4 py-10 text-center text-sm font-medium tracking-tight text-slate-500 sm:py-8 sm:font-normal sm:tracking-normal">
                    Belum ada data pengiriman.
                </div>

                <Pagination :links="paginationLinks" />
            </div>
        </div>
    </AppLayout>
</template>