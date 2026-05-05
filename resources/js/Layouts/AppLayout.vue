<script setup>
import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import { useFlash } from '@/composables/useFlash'
import { computed, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

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
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <div v-show="sidebarOpen" class="fixed inset-0 z-30 bg-slate-900/40 xl:hidden" @click="sidebarOpen = false" />

        <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <div class="flex min-h-screen flex-col xl:pl-60">
            <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

            <main class="flex-1 overflow-x-hidden p-4 sm:p-5 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>