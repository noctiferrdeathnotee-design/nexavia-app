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

const filterForm = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    layanan: props.filters.layanan || '',
    tanggal_mulai: props.filters.tanggal_mulai || '',
    tanggal_akhir: props.filters.tanggal_akhir || '',
    sort: props.filters.sort || 'terbaru',
})

const rows = computed(() => props.pengiriman?.data ?? [])
const paginationLinks = computed(() => props.pengiriman?.links ?? [])

const hasActiveFilter = computed(() => {
    return (
        filterForm.search !== '' ||
        filterForm.status !== '' ||
        filterForm.layanan !== '' ||
        filterForm.tanggal_mulai !== '' ||
        filterForm.tanggal_akhir !== '' ||
        filterForm.sort !== 'terbaru'
    )
})

const cleanedFilters = computed(() => {
    const payload = {
        sort: filterForm.sort || 'terbaru',
    }

    if (filterForm.search) payload.search = filterForm.search
    if (filterForm.status) payload.status = filterForm.status
    if (filterForm.layanan) payload.layanan = filterForm.layanan
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

const resetFilters = () => {
    filterForm.search = ''
    filterForm.status = ''
    filterForm.layanan = ''
    filterForm.tanggal_mulai = ''
    filterForm.tanggal_akhir = ''
    filterForm.sort = 'terbaru'

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
        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Data Pengiriman</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Tabel pengiriman dengan filter, pencarian, dan pagination.
                    </p>
                </div>

                <Link :href="route('pengiriman.create')" class="btn-primary w-full justify-center sm:w-auto">
                    <i class="bi bi-plus-lg" />
                    Input Baru
                </Link>
            </div>

            <div class="card p-4">
                <div class="flex gap-3 overflow-x-auto">
                    <div class="min-w-[180px]">
                        <label class="form-label">Sort</label>
                        <select v-model="filterForm.sort" class="form-select" @change="applyFilters">
                            <option value="terbaru">Terbaru</option>
                            <option value="terlama">Terlama</option>
                            <option value="terlambat">Terlambat</option>
                        </select>
                    </div>

                    <div class="min-w-[180px]">
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

                    <div class="min-w-[180px]">
                        <label class="form-label">Layanan</label>
                        <select v-model="filterForm.layanan" class="form-select" @change="applyFilters">
                            <option value="">Semua</option>
                            <option value="ekonomis">Ekonomis</option>
                            <option value="reguler">Reguler</option>
                            <option value="express">Express</option>
                            <option value="same_day">Same Day</option>
                        </select>
                    </div>

                    <div class="min-w-[160px]">
                        <label class="form-label">Dari</label>
                        <input v-model="filterForm.tanggal_mulai" type="date" class="form-input" @change="applyFilters">
                    </div>

                    <div class="min-w-[160px]">
                        <label class="form-label">Sampai</label>
                        <input v-model="filterForm.tanggal_akhir" type="date" class="form-input" @change="applyFilters">
                    </div>

                    <div class="min-w-[220px]">
                        <label class="form-label">Cari</label>
                        <div class="flex gap-2">
                            <input v-model="filterForm.search" type="text" class="form-input"
                                placeholder="No resi / pengirim / penerima" @keyup.enter="applyFilters">
                            <button type="button" class="btn-secondary" @click="applyFilters">
                                <i class="bi bi-search" />
                            </button>
                        </div>
                    </div>

                    <div v-if="hasActiveFilter" class="flex min-w-[110px] items-end">
                        <button type="button" class="btn-secondary w-full justify-center" @click="resetFilters">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div v-if="rows.length" class="table-wrap">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    No Resi
                                </th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Pengirim
                                </th>
                                <th
                                    class="hidden px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:table-cell">
                                    Tujuan
                                </th>
                                <th
                                    class="hidden px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 md:table-cell">
                                    Layanan
                                </th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th
                                    class="hidden px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 lg:table-cell">
                                    Estimasi
                                </th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="item in rows" :key="item.id" :class="{ 'bg-red-50': item.is_terlambat }">
                                <td class="px-3 py-2 text-sm font-medium text-slate-700">
                                    <div class="flex flex-col gap-1">
                                        <span>{{ item.nomor_resi || '-' }}</span>
                                        <span v-if="item.is_terlambat"
                                            class="inline-flex w-fit rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-600">
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
                                    {{ formatService(item.layanan) }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <StatusBadge :status="item.status" />
                                </td>

                                <td class="hidden px-3 py-2 text-sm text-slate-600 lg:table-cell">
                                    {{ formatDate(item.estimasi_tiba) }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <Link :href="route('pengiriman.show', item.id)" class="btn-secondary btn-sm">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-4 py-8 text-center text-sm text-slate-500">
                    Belum ada data pengiriman.
                </div>

                <Pagination :links="paginationLinks" />
            </div>
        </div>
    </AppLayout>
</template>