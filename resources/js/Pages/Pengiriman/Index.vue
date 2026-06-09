<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import ToggleSwitch from '@/Components/ToggleSwitch.vue'

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
    only_late: props.filters.only_late === 'true' || props.filters.only_late === true || false,
})

// [UPDATE: FASE 6 PREMIUM MOBILE DISPATCH] State untuk Buka-Tutup Filter di HP
const showMobileFilter = ref(false)

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
    if (filterForm.only_late) payload.only_late = filterForm.only_late

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
    filterForm.only_late = false

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

                <!-- [UPDATE: FASE 6 PREMIUM MOBILE DISPATCH] The Royal Button Khusus Mobile & Tombol Standar Desktop -->
                <!-- Desktop (100% Tidak Disentuh) -->
                <Link :href="route('pengiriman.create')" class="hidden sm:inline-flex btn-primary w-auto rounded-lg">
                    <i class="bi bi-plus-lg" />
                    Input Baru
                </Link>
                <!-- Mobile (Premium Xaviera Gold & Midnight Blue) -->
                <Link :href="route('pengiriman.create')" class="flex sm:hidden w-full items-center justify-center gap-2 rounded-[16px] bg-[#0B132B] px-4 py-3.5 text-sm font-bold tracking-wide text-[#D4AF37] shadow-[0_8px_20px_rgba(11,19,43,0.3)] transition-transform hover:scale-[0.98] active:scale-95">
                    <i class="bi bi-plus-circle-fill text-base" />
                    Input Baru
                </Link>
            </div>

            <!-- Filter Section -->
            
            <!-- [UPDATE: FASE 6 PREMIUM MOBILE DISPATCH] COLLAPSIBLE SMART FILTER KHUSUS MOBILE -->
            <div class="block sm:hidden space-y-3">
                <!-- Smart Pill Search Bar (Selalu Terlihat) -->
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input v-model="filterForm.search" type="text" 
                               class="w-full rounded-full border border-slate-200 bg-white py-3 pl-10 pr-4 text-[13px] text-slate-800 shadow-[0_2px_10px_rgb(0,0,0,0.02)] focus:border-[#B8860B] focus:ring-[#B8860B]/20 transition-shadow"
                               placeholder="Cari resi / pengirim..." @keyup.enter="applyFilters">
                    </div>
                    <button type="button" @click="showMobileFilter = !showMobileFilter" 
                            class="flex shrink-0 items-center justify-center rounded-full bg-white px-4 text-slate-600 shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-slate-200 transition-colors"
                            :class="showMobileFilter ? 'bg-slate-100 ring-2 ring-slate-200' : ''">
                        <i class="bi bi-sliders text-lg"></i>
                    </button>
                </div>

                <!-- Expanded Filters (Collapsible via v-show untuk LCP tercepat) -->
                <div v-show="showMobileFilter" class="rounded-[20px] border border-slate-100 bg-white/95 p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-md transition-all duration-300 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Status</label>
                            <select v-model="filterForm.status" class="w-full rounded-xl border-slate-100 bg-slate-50 text-[13px] text-slate-700 focus:border-[#B8860B] focus:ring-[#B8860B]/20" @change="applyFilters">
                                <option value="">Semua Status</option>
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
                        <div class="col-span-1">
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Dari</label>
                            <input v-model="filterForm.tanggal_mulai" type="date" class="w-full rounded-xl border-slate-100 bg-slate-50 px-3 py-2 text-[13px] text-slate-700 focus:border-[#B8860B] focus:ring-[#B8860B]/20" @change="applyFilters">
                        </div>
                        <div class="col-span-1">
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-slate-400">Sampai</label>
                            <input v-model="filterForm.tanggal_akhir" type="date" class="w-full rounded-xl border-slate-100 bg-slate-50 px-3 py-2 text-[13px] text-slate-700 focus:border-[#B8860B] focus:ring-[#B8860B]/20" @change="applyFilters">
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-2.5">
                        <ToggleSwitch v-model="filterForm.only_late" label="Hanya Terlambat" @change="applyFilters" />
                    </div>

                    <div class="pt-2">
                        <button v-if="hasActiveFilter || filterForm.only_late" type="button" class="w-full rounded-xl bg-red-50 py-3 text-[13px] font-bold text-red-600 transition-colors hover:bg-red-100 border border-red-100" @click="resetFilters">
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- FILTER DESKTOP (100% Tidak Disentuh, Hanya Disembunyikan di Mobile) -->
            <div class="hidden sm:block card relative overflow-hidden p-4 rounded-xl border border-slate-200 bg-white shadow-sm xl:bg-[#1A233A] xl:border-white/10 xl:shadow-lg">
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

                    <!-- [UBAH KHUSUS MOBILE & DESKTOP] Premium Toggle Switch untuk Filter 'Hanya Terlambat' -->
                    <div class="col-span-2 flex items-center justify-between lg:col-span-1 p-2 bg-slate-50/50 rounded-lg border border-slate-100/50 xl:bg-white/5 xl:border-white/10 xl:backdrop-blur-sm">
                        <ToggleSwitch v-model="filterForm.only_late" label="Hanya Terlambat" @change="applyFilters" />
                    </div>

                    <div v-if="hasActiveFilter || filterForm.only_late" class="col-span-2 flex items-end lg:col-span-1">
                        <button type="button" class="btn-secondary w-full justify-center lg:w-auto" @click="resetFilters">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <!-- [UBAH KHUSUS MOBILE] Kotak Tabel Premium: glassmorphism, shadow tipis, border tipis -->
            <div class="card overflow-hidden rounded-[20px] border border-slate-100/60 bg-white/95 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm sm:rounded-xl sm:border sm:border-slate-200 sm:bg-white sm:shadow-sm sm:backdrop-blur-none xl:bg-[#1A233A] xl:border-white/10 xl:shadow-lg">
                
                <!-- [UPDATE: FASE 2 STACKED CARDS KHUSUS MOBILE]
                     Perbaikan: Padding dikurangi dari p-4 menjadi p-3.5 agar tidak memakan layar.
                     Tipografi dikompresi (text-[13px] & text-[12px]) agar rapi di HP kecil. -->
                <div v-if="rows.length" class="block sm:hidden divide-y divide-slate-100/80">
                    <div v-for="item in rows" :key="'mob-'+item.id" class="p-3.5 flex flex-col gap-2.5" :class="item.is_terlambat ? 'bg-red-50/30' : 'bg-transparent'">
                        <!-- Resi & Status -->
                        <div class="flex items-start justify-between">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">No. Resi</span>
                                <span class="text-[13px] font-bold tracking-tight text-[#0B132B]">{{ item.nomor_resi || '-' }}</span>
                            </div>
                            <StatusBadge :status="item.status" />
                        </div>

                        <!-- Pengirim & Tujuan -->
                        <div class="grid grid-cols-2 gap-2.5 bg-slate-50/50 p-2.5 rounded-[14px] border border-slate-100">
                            <div>
                                <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Pengirim</span>
                                <span class="block text-[12px] font-semibold text-slate-700 truncate mt-0.5">{{ item.pengirim_nama || '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">Tujuan</span>
                                <span class="block text-[12px] font-semibold text-slate-700 truncate mt-0.5">{{ item.tujuan_kota || '-' }}</span>
                            </div>
                        </div>

                        <!-- Layanan, Estimasi & Aksi -->
                        <div class="flex items-center justify-between mt-0.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-[#b8860b]/10 px-2 py-1 text-[11px] font-semibold text-[#b8860b]">
                                    {{ formatService(item.layanan) }}
                                </span>
                                <span v-if="item.is_terlambat" class="inline-flex rounded-md bg-red-100 px-2 py-1 text-[11px] font-bold text-red-600">
                                    Telat
                                </span>
                            </div>
                            <Link :href="route('pengiriman.show', item.id)" class="btn-secondary btn-sm !rounded-full px-4 border-slate-200 shadow-sm text-[#0B132B] hover:bg-slate-50">
                                Detail
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="rows.length" class="hidden sm:block w-full overflow-x-auto">
                    <!-- [UPDATE: FASE 3] Tabel menggunakan class global .table-xaviera dari app.css untuk konsistensi -->
                    <table class="table-xaviera">
                        <thead>
                            <tr>
                                <th>No Resi</th>
                                <th>Pengirim</th>
                                <th>Tujuan</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th>Estimasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in rows" :key="item.id" :class="{ 'is-late': item.is_terlambat }">
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="font-bold">{{ item.nomor_resi || '-' }}</span>
                                        <span v-if="item.is_terlambat"
                                            class="inline-flex w-fit rounded-md bg-red-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-red-600 xl:bg-red-900/40 xl:text-red-400">
                                            TERLAMBAT
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="font-bold">{{ item.pengirim_nama || '-' }}</span>
                                </td>

                                <td>
                                    {{ item.tujuan_kota || '-' }}
                                </td>

                                <td>
                                    <span class="inline-flex items-center rounded-md bg-[#b8860b]/10 px-2.5 py-1 text-xs font-semibold text-[#b8860b] xl:bg-[#D4AF37]/20 xl:text-[#D4AF37]">
                                        {{ formatService(item.layanan) }}
                                    </span>
                                </td>

                                <td>
                                    <StatusBadge :status="item.status" />
                                </td>

                                <td>
                                    {{ formatDate(item.estimasi_tiba) }}
                                </td>

                                <td>
                                    <Link :href="route('pengiriman.show', item.id)"
                                        class="btn-secondary btn-sm !rounded-md xl:hover:bg-[#0B132B]">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!rows.length" class="flex flex-col items-center justify-center p-8 text-center sm:p-12">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 xl:bg-[#0B132B]">
                        <i class="bi bi-inbox text-2xl text-slate-400 xl:text-slate-500" />
                    </div>
                    <h3 class="mb-1 font-semibold text-slate-800 xl:text-slate-200">Belum ada data</h3>
                    <p class="text-sm text-slate-500 xl:text-slate-400">Data pengiriman akan muncul di sini</p>
                </div>

                <Pagination :links="paginationLinks" />
            </div>
        </div>
    </AppLayout>
</template>