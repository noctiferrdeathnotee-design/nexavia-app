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
    <div v-if="links.length > 3" class="flex flex-wrap items-center gap-2 border-t border-slate-200 px-4 py-3">
        <component :is="link.url ? Link : 'span'" v-for="(link, index) in links" :key="index"
            :href="link.url || undefined"
            class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm transition-colors" :class="link.active
                ? 'border-indigo-500 bg-indigo-50 text-indigo-600'
                : link.url
                    ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                    : 'border-slate-100 bg-slate-50 text-slate-400'" v-html="link.label" />
    </div>
</template>