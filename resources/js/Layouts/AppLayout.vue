<script setup>
import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import { useFlash } from '@/composables/useFlash'
import { computed, ref, watch } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage()
const sidebarOpen = ref(false)

const flashSuccess = computed(() => page.props.flash?.success || '')
const flashError = computed(() => page.props.flash?.error || '')

const { showSuccess, showError } = useFlash(page)

watch(
    () => page.url,
    () => {
        sidebarOpen.value = false
    }
)

watch(flashSuccess, (value) => {
    showSuccess(value)
})

watch(flashError, (value) => {
    showError(value)
})

const currentUrl = computed(() => page.url || '/')

const isMobileNavActive = (match, exact) => {
    if (exact) {
        return currentUrl.value === match
    }
    return currentUrl.value.startsWith(match)
}
</script>

<template>
    <!-- [UBAH KHUSUS MOBILE & DESKTOP] Tambahan pb-20 untuk memberi ruang bagi Bottom Navigation Bar di Mobile -->
    <div class="min-h-screen bg-slate-50 font-['Plus_Jakarta_Sans'] pb-20 xl:pb-0">
        <!-- Sidebar dimatikan sepenuhnya di Mobile Android (Overlay Background Dihapus untuk membersihkan DOM) -->
        
        <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <div class="flex min-h-screen flex-col xl:pl-[280px]"> <!-- Lebar sidebar desktop diperbesar untuk logo Xaviera -->
            
            <!-- [UPDATE: FASE 4 PERBAIKAN BUG VISUAL MOBILE]
                 Fungsi: Header ini HANYA muncul di Mobile (Layar HP) dan disembunyikan di Desktop.
                 Perbaikan: Menghapus 'mix-blend-multiply' dari class img.
                 Alasan: Gambar logo-xaviera.jpg yang diupload user memiliki latar belakang kotak-kotak bawaan gambar (fake transparent). 
                 Mix-blend-multiply malah mengubah warna kotak-kotak itu menjadi gelap/hitam. 
                 Saran: Tetap dianjurkan user mengganti dengan PNG transparan asli agar sempurna. -->
            <div class="sticky top-0 z-30 flex h-20 w-full items-center justify-center border-b border-slate-100 bg-white/95 backdrop-blur-md xl:hidden shadow-sm">
                <img src="/images/logo-xaviera.jpg" alt="Xaviera" class="h-16 w-auto max-w-[240px] object-contain drop-shadow-sm rounded-lg bg-white">
            </div>

            <!-- Navbar Desktop (Navbar.vue), di Mobile disembunyikan sepenuhnya -->
            <div class="hidden xl:block">
                <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
            </div>

            <main class="flex-1 overflow-x-hidden p-3 sm:p-5 lg:p-6">
                <slot />
            </main>
        </div>

        <!-- [UPDATE: FASE 5 THE GOLDEN 5 BOTTOM NAVIGATION] 
             Fungsi: Memuat semua 5 menu Sidebar ke dalam Bottom Navigation di Mobile tanpa merusak UI.
             Perbaikan UI/UX: 
             1. Metode "Flex-1 Fluidity": Kelas 'w-16' dihapus dan diganti 'flex-1' agar ke-5 menu membelah layar rata (20% per menu).
             2. Ikon distabilkan di text-[22px] agar ada ruang bernapas untuk 5 tombol.
             3. Menu "Tracking" ditambahkan di tengah dengan alias "Lacak" agar teks tidak terpotong. -->
        <nav class="fixed bottom-0 left-0 z-50 flex h-[68px] w-full items-center justify-around border-t border-slate-200/60 bg-white/95 px-1 pb-safe backdrop-blur-xl xl:hidden shadow-[0_-8px_30px_rgb(0,0,0,0.04)]">
            
            <!-- 1. Beranda (Icon Aktif: Xaviera Gold) -->
            <Link href="/dashboard" class="group relative flex flex-col items-center justify-center flex-1 gap-1" :class="isMobileNavActive('/dashboard', true) ? 'text-[#B8860B]' : 'text-slate-400 hover:text-slate-600'">
                <i class="bi bi-speedometer2 text-[22px] transition-transform duration-300" :class="{ 'scale-110 drop-shadow-sm': isMobileNavActive('/dashboard', true) }" />
                <span class="text-[10px] font-semibold tracking-wide" :class="{ 'opacity-100': isMobileNavActive('/dashboard', true), 'opacity-80': !isMobileNavActive('/dashboard', true) }">Beranda</span>
                <div v-if="isMobileNavActive('/dashboard', true)" class="absolute -bottom-2 h-1 w-1 rounded-full bg-[#B8860B] shadow-[0_0_8px_rgba(184,134,11,0.6)]"></div>
            </Link>

            <!-- 2. Paket / Pengiriman (Icon Aktif: Xaviera Blue) -->
            <Link href="/pengiriman" class="group relative flex flex-col items-center justify-center flex-1 gap-1" :class="isMobileNavActive('/pengiriman', false) ? 'text-[#0B132B]' : 'text-slate-400 hover:text-slate-600'">
                <i class="bi bi-box-seam text-[22px] transition-transform duration-300" :class="{ 'scale-110 drop-shadow-sm': isMobileNavActive('/pengiriman', false) }" />
                <span class="text-[10px] font-semibold tracking-wide" :class="{ 'opacity-100': isMobileNavActive('/pengiriman', false), 'opacity-80': !isMobileNavActive('/pengiriman', false) }">Paket</span>
                <div v-if="isMobileNavActive('/pengiriman', false)" class="absolute -bottom-2 h-1 w-1 rounded-full bg-[#0B132B] shadow-[0_0_8px_rgba(11,19,43,0.6)]"></div>
            </Link>

            <!-- 3. Lacak / Tracking (Icon Aktif: Xaviera Gold) -->
            <Link href="/tracking" class="group relative flex flex-col items-center justify-center flex-1 gap-1" :class="isMobileNavActive('/tracking', false) ? 'text-[#B8860B]' : 'text-slate-400 hover:text-slate-600'">
                <i class="bi bi-geo-alt text-[22px] transition-transform duration-300" :class="{ 'scale-110 drop-shadow-sm': isMobileNavActive('/tracking', false) }" />
                <span class="text-[10px] font-semibold tracking-wide" :class="{ 'opacity-100': isMobileNavActive('/tracking', false), 'opacity-80': !isMobileNavActive('/tracking', false) }">Lacak</span>
                <div v-if="isMobileNavActive('/tracking', false)" class="absolute -bottom-2 h-1 w-1 rounded-full bg-[#B8860B] shadow-[0_0_8px_rgba(184,134,11,0.6)]"></div>
            </Link>

            <!-- 4. Tarif (Icon Aktif: Xaviera Blue) -->
            <Link href="/tarif" class="group relative flex flex-col items-center justify-center flex-1 gap-1" :class="isMobileNavActive('/tarif', false) ? 'text-[#0B132B]' : 'text-slate-400 hover:text-slate-600'">
                <i class="bi bi-calculator text-[22px] transition-transform duration-300" :class="{ 'scale-110 drop-shadow-sm': isMobileNavActive('/tarif', false) }" />
                <span class="text-[10px] font-semibold tracking-wide" :class="{ 'opacity-100': isMobileNavActive('/tarif', false), 'opacity-80': !isMobileNavActive('/tarif', false) }">Tarif</span>
                <div v-if="isMobileNavActive('/tarif', false)" class="absolute -bottom-2 h-1 w-1 rounded-full bg-[#0B132B] shadow-[0_0_8px_rgba(11,19,43,0.6)]"></div>
            </Link>

            <!-- 5. Laporan (Icon Aktif: Xaviera Gold) -->
            <Link href="/laporan" class="group relative flex flex-col items-center justify-center flex-1 gap-1" :class="isMobileNavActive('/laporan', false) ? 'text-[#B8860B]' : 'text-slate-400 hover:text-slate-600'">
                <i class="bi bi-file-earmark-bar-graph text-[22px] transition-transform duration-300" :class="{ 'scale-110 drop-shadow-sm': isMobileNavActive('/laporan', false) }" />
                <span class="text-[10px] font-semibold tracking-wide" :class="{ 'opacity-100': isMobileNavActive('/laporan', false), 'opacity-80': !isMobileNavActive('/laporan', false) }">Laporan</span>
                <div v-if="isMobileNavActive('/laporan', false)" class="absolute -bottom-2 h-1 w-1 rounded-full bg-[#B8860B] shadow-[0_0_8px_rgba(184,134,11,0.6)]"></div>
            </Link>
        </nav>
    </div>
</template>