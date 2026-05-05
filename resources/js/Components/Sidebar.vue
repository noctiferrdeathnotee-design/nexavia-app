<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['close'])

const page = usePage()

const menuUtama = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'bi-speedometer2',
        match: '/dashboard',
        exact: true,
    },
    {
        label: 'Data Pengiriman',
        href: '/pengiriman',
        icon: 'bi-box-seam',
        match: '/pengiriman',
        exact: false,
    },
    {
        label: 'Tracking',
        href: '/tracking',
        icon: 'bi-geo-alt',
        match: '/tracking',
        exact: false,
    },
    {
        label: 'Cek Tarif',
        href: '/tarif',
        icon: 'bi-calculator',
        match: '/tarif',
        exact: false,
    },
]

const menuLaporan = [
    {
        label: 'Laporan',
        href: '/laporan',
        icon: 'bi-file-earmark-bar-graph',
        match: '/laporan',
        exact: false,
    },
]

const currentUrl = computed(() => page.url || '/')

const isActive = (item) => {
    if (item.exact) {
        return currentUrl.value === item.match
    }

    return currentUrl.value.startsWith(item.match)
}

const linkClass = (item) => {
    return isActive(item)
        ? 'bg-indigo-50 text-indigo-600 font-semibold border-l-2 border-indigo-500'
        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800 border-l-2 border-transparent'
}
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-60 flex-col border-r border-slate-200 bg-white transition-transform duration-200"
        :class="open ? 'translate-x-0' : '-translate-x-full xl:translate-x-0'">
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
            <div class="flex items-center gap-3">
                <img src="/images/logo-brand.png" alt="Nexavia"
                    class="h-10 w-10 rounded-xl border border-slate-200 object-contain p-1">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Nexavia</p>
                    <p class="text-[11px] text-slate-500">Manajemen Pengiriman</p>
                </div>
            </div>

            <button type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 xl:hidden"
                @click="$emit('close')">
                <i class="bi bi-x-lg" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4">
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                Menu Utama
            </p>

            <nav class="space-y-1">
                <Link v-for="item in menuUtama" :key="item.href" :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors"
                    :class="linkClass(item)" @click="$emit('close')">
                    <i :class="['text-base', item.icon]" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="my-4 border-t border-slate-200" />

            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                Laporan
            </p>

            <nav class="space-y-1">
                <Link v-for="item in menuLaporan" :key="item.href" :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors"
                    :class="linkClass(item)" @click="$emit('close')">
                    <i :class="['text-base', item.icon]" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            <p class="text-xs text-slate-400">© 2025 SoftSend</p>
        </div>
    </aside>
</template>