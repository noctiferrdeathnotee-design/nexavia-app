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
    <!-- [UBAH KHUSUS DESKTOP] Sidebar hanya muncul di Desktop (xl:flex), di Mobile disembunyikan total -->
    <!-- Desain Glassmorphism premium: bg-white/80 backdrop-blur-xl -->
    <aside
        class="fixed inset-y-0 left-0 z-40 hidden w-[280px] flex-col border-r border-slate-100 bg-white/80 backdrop-blur-xl xl:flex shadow-[4px_0_24px_rgb(0,0,0,0.02)]">
        
        <!-- [UBAH KHUSUS DESKTOP] Header Sidebar: Logo Xaviera Raksasa -->
        <div class="flex items-center justify-center border-b border-slate-100/60 p-6">
            <img src="/images/logo-brand.png" alt="Xaviera"
                class="w-full h-auto max-h-24 object-contain drop-shadow-md">
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4">
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                Menu Utama
            </p>

            <nav class="space-y-0.5">
                <Link v-for="item in menuUtama" :key="item.href" :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
                    :class="linkClass(item)" @click="$emit('close')"
                    :style="{ transition: 'background-color 0.15s ease, color 0.15s ease' }">
                    <i :class="['text-base', item.icon]" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="my-4 border-t border-slate-200" />

            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                Laporan
            </p>

            <nav class="space-y-0.5">
                <Link v-for="item in menuLaporan" :key="item.href" :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
                    :class="linkClass(item)" @click="$emit('close')"
                    :style="{ transition: 'background-color 0.15s ease, color 0.15s ease' }">
                    <i :class="['text-base', item.icon]" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>
        </div>

        <div class="border-t border-slate-100/60 px-6 py-4">
            <p class="text-[11px] font-medium text-slate-400">© 2026 Xaviera Delivery</p>
        </div>
    </aside>
</template>