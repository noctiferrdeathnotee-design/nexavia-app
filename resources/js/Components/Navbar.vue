<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

defineEmits(['toggle-sidebar'])

const page = usePage()
const dropdownOpen = ref(false)
const dropdownRef = ref(null)
const currentTime = ref('')

const authUser = computed(() => page.props.auth?.user || null)

const title = computed(() => {
    if (page.props.title) {
        return page.props.title
    }

    const url = page.url || ''

    if (url.startsWith('/pengiriman')) return 'Data Pengiriman'
    if (url.startsWith('/tarif')) return 'Cek Tarif'
    if (url.startsWith('/laporan')) return 'Laporan'
    if (url.startsWith('/tracking')) return 'Tracking'
    if (url.startsWith('/dashboard')) return 'Dashboard'

    return 'SoftSend'
})

const initials = computed(() => {
    const nama = authUser.value?.nama || 'Admin'

    return nama
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((item) => item.charAt(0).toUpperCase())
        .join('')
})

let clockTimer = null

const updateClock = () => {
    const now = new Date()

    const tanggal = new Intl.DateTimeFormat('id-ID', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    }).format(now)

    const jam = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    }).format(now)

    currentTime.value = `${tanggal} · ${jam} WIB`
}

const closeDropdown = () => {
    dropdownOpen.value = false
}

const handleClickOutside = (event) => {
    if (!dropdownRef.value) {
        return
    }

    if (!dropdownRef.value.contains(event.target)) {
        closeDropdown()
    }
}

const handleKeydown = (event) => {
    if (event.key === 'Escape') {
        closeDropdown()
    }
}

const logout = async () => {
    closeDropdown()

    if (!window.Swal) {
        router.post('/logout')
        return
    }

    const result = await window.Swal.fire({
        icon: 'warning',
        title: 'Keluar dari akun?',
        text: 'Sesi admin akan diakhiri.',
        showCancelButton: true,
        confirmButtonText: 'Ya, keluar',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#CBD5E1',
    })

    if (result.isConfirmed) {
        router.post('/logout')
    }
}

onMounted(() => {
    updateClock()
    clockTimer = setInterval(updateClock, 1000)

    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
    if (clockTimer) {
        clearInterval(clockTimer)
        clockTimer = null
    }

    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex h-14 items-center justify-between gap-3 px-3 sm:h-16 sm:px-5 lg:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 xl:hidden"
                    @click="$emit('toggle-sidebar')">
                    <i class="bi bi-list text-lg" />
                </button>

                <div class="min-w-0">
                    <h1 class="truncate text-sm font-semibold text-slate-800 sm:text-base lg:text-lg">
                        {{ title }}
                    </h1>
                    <p class="hidden text-xs text-slate-500 sm:block">
                        Panel admin
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <div
                    class="hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-600 lg:block">
                    {{ currentTime }}
                </div>

                <div ref="dropdownRef" class="relative">
                    <button type="button"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5 hover:bg-slate-50 sm:px-2.5 sm:py-2"
                        @click="dropdownOpen = !dropdownOpen">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-600 sm:h-9 sm:w-9 sm:text-sm">
                            {{ initials }}
                        </div>

                        <div class="hidden text-left sm:block">
                            <p class="max-w-[140px] truncate text-sm font-medium text-slate-700">
                                {{ authUser?.nama || 'Admin' }}
                            </p>
                            <p class="max-w-[160px] truncate text-xs text-slate-500">
                                {{ authUser?.email || '-' }}
                            </p>
                        </div>

                        <i class="bi bi-chevron-down text-xs text-slate-400" />
                    </button>

                    <div v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-64 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-800">
                                {{ authUser?.nama || 'Admin' }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ authUser?.email || '-' }}
                            </p>
                        </div>

                        <div class="px-2 py-2">
                            <button type="button"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-red-500 hover:bg-red-50"
                                @click="logout">
                                <i class="bi bi-box-arrow-right" />
                                <span>Keluar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>