<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: '',
    },
})

defineEmits(['update:modelValue'])
</script>

<template>
    <label class="flex cursor-pointer items-center gap-3">
        <div class="relative flex items-center">
            <input 
                type="checkbox" 
                class="peer sr-only" 
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
            >
            <!-- Latar belakang kapsul (Pudar -> Xaviera Blue) -->
            <div class="h-6 w-11 rounded-full bg-slate-200 transition-colors duration-300 ease-in-out peer-checked:bg-[#1a365d] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#b8860b]/50"></div>
            <!-- Knob / Lingkaran peluncur -->
            <div class="absolute left-[2px] top-[2px] h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-300 ease-in-out peer-checked:translate-x-5 peer-checked:border-white"></div>
        </div>
        <span v-if="label" class="text-sm font-semibold text-slate-700 select-none">{{ label }}</span>
    </label>
</template>
