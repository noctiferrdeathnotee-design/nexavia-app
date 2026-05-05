<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        default: 'pending',
    },
})

const config = computed(() => {
    const items = {
        pending: {
            label: 'Pending',
            className: 'bg-slate-100 text-slate-600',
        },
        diproses: {
            label: 'Diproses',
            className: 'bg-amber-100 text-amber-700',
        },
        dalam_perjalanan: {
            label: 'Perjalanan',
            className: 'bg-blue-100 text-blue-700',
        },
        tiba_di_kota_tujuan: {
            label: 'Tiba Kota',
            className: 'bg-indigo-100 text-indigo-700',
        },
        sedang_diantar: {
            label: 'Diantar',
            className: 'bg-purple-100 text-purple-700',
        },
        terkirim: {
            label: 'Terkirim',
            className: 'bg-emerald-100 text-emerald-700',
        },
        gagal: {
            label: 'Gagal',
            className: 'bg-red-100 text-red-600',
        },
        dibatalkan: {
            label: 'Dibatalkan',
            className: 'bg-slate-100 text-slate-400',
        },
    }

    return items[props.status] || items.pending
})
</script>

<template>
    <span class="badge" :class="config.className">
        {{ config.label }}
    </span>
</template>