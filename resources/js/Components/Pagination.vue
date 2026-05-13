<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-1.5 border-t border-slate-200 px-3 py-3 sm:gap-2 sm:px-4">
        <component :is="link.url ? Link : 'span'" v-for="(link, index) in links" :key="index"
            :href="link.url || undefined"
            class="inline-flex items-center rounded-lg border px-2.5 py-1.5 text-xs sm:px-3 sm:text-sm" :class="link.active
                ? 'border-indigo-500 bg-indigo-50 text-indigo-600'
                : link.url
                    ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                    : 'border-slate-100 bg-slate-50 text-slate-400'"
            :style="{ transition: 'background-color 0.15s ease, border-color 0.15s ease' }"
            v-html="link.label" />
    </div>
</template>