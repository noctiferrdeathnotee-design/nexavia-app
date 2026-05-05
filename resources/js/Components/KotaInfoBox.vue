<script setup>
defineProps({
    kota: {
        type: Object,
        default: null,
    },
})
</script>

<template>
    <div v-if="kota" class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2">
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
                <p class="text-[10px] text-slate-400">Provinsi</p>
                <p class="font-medium text-slate-700">{{ kota.provinsi }}</p>
            </div>

            <div>
                <p class="text-[10px] text-slate-400">Kode Pos</p>
                <p class="font-medium text-slate-700">{{ kota.kode_pos }}</p>
            </div>
        </div>
    </div>
</template>