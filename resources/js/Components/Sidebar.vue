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
    // [UPDATE] Sidebar menggunakan warna dasar gelap (xaviera-blue), efek hover/aktif menggunakan warna xaviera-gold
    return isActive(item)
        ? 'bg-[#1C2541]/80 text-[#D4AF37] font-semibold border-l-4 border-[#B8860B] drop-shadow-md'
        : 'text-slate-300 hover:bg-[#1C2541]/40 hover:text-white border-l-4 border-transparent'
}
</script>

<template>
    <!-- [UBAH KHUSUS DESKTOP] Sidebar hanya muncul di Desktop (xl:flex), di Mobile disembunyikan total -->
    <!-- [UPDATE] Sidebar Kelas Atas Xaviera: Menggunakan background Midnight Blue (#0B132B) dengan sedikit efek glass -->
    <aside
        class="fixed inset-y-0 left-0 z-40 hidden w-[280px] flex-col border-r border-[#1C2541] bg-[#0B132B]/95 backdrop-blur-xl xl:flex shadow-[4px_0_24px_rgb(0,0,0,0.2)]">
        
        <!-- [UPDATE] Header Sidebar: Logo Xaviera Raksasa. Tinggi p-8 agar proporsional -->
        <div class="flex items-center justify-center border-b border-[#1C2541] p-8">
            <img src="/images/logo-xaviera.jpg" alt="Xaviera"
                class="w-full h-auto max-h-32 object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,0.5)]">
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4 [&::-webkit-scrollbar]:w-1 [&::-webkit-scrollbar-thumb]:bg-[#1C2541]">
            <!-- [UPDATE] Teks kategori menu berwarna gold/slate-400 gelap -->
            <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-widest text-[#B8860B]/70">
                Menu Utama
            </p>

            <nav class="space-y-1">
                <Link v-for="item in menuUtama" :key="item.href" :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm tracking-wide"
                    :class="linkClass(item)" @click="$emit('close')"
                    :style="{ transition: 'background-color 0.2s ease, color 0.2s ease, transform 0.2s ease' }">
                    <i :class="['text-lg', item.icon]" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="my-5 border-t border-[#1C2541]" />

            <p class="mb-2 px-3 text-[11px] font-black uppercase tracking-widest text-[#B8860B]/70">
                Laporan
            </p>

            <nav class="space-y-1">
                <Link v-for="item in menuLaporan" :key="item.href" :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm tracking-wide"
                    :class="linkClass(item)" @click="$emit('close')"
                    :style="{ transition: 'background-color 0.2s ease, color 0.2s ease, transform 0.2s ease' }">
                    <i :class="['text-lg', item.icon]" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>
        </div>

        <div class="border-t border-[#1C2541] px-6 py-4 bg-[#080d1e]">
            <p class="text-[11px] font-medium text-slate-500 tracking-wider">© 2026 XAVIERA DELIVERY</p>
        </div>
    </aside>
</template>