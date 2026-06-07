<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: '-- Pilih --'
    }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const search = ref('')
const wrapperRef = ref(null)

// [UBAH KHUSUS MOBILE & DESKTOP] Filter opsi berdasar teks pencarian tanpa membebani memori browser (sangat kencang)
const filteredOptions = computed(() => {
    if (!search.value) return props.options
    const q = search.value.toLowerCase()
    return props.options.filter(opt => opt.nama_kota.toLowerCase().includes(q))
})

// Mencegah error jika data belum siap
const selectedOption = computed(() => {
    return props.options.find(opt => String(opt.id) === String(props.modelValue))
})

// [UBAH KHUSUS MOBILE & DESKTOP] Fungsi memilih kota dan menutup dropdown otomatis
const selectOption = (opt) => {
    emit('update:modelValue', String(opt.id))
    isOpen.value = false
    search.value = ''
}

// Menampilkan / menyembunyikan opsi dropdown
const toggle = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        search.value = ''
    }
}

// Menutup dropdown jika user klik di luar area
const closeDropdown = (e) => {
    if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
        isOpen.value = false
    }
}

onMounted(() => document.addEventListener('click', closeDropdown))
onUnmounted(() => document.removeEventListener('click', closeDropdown))
</script>

<template>
    <div class="relative w-full" ref="wrapperRef">
        <!-- [UBAH KHUSUS MOBILE & DESKTOP] Tombol Pemicu Dropdown bergaya modern, seragam dengan input premium lainnya -->
        <div 
            @click="toggle"
            class="flex items-center justify-between w-full form-input cursor-pointer transition-colors w-full rounded-[16px] bg-slate-50/80 border-transparent min-h-[48px] sm:hover:border-indigo-400 sm:rounded-md sm:bg-white sm:border-slate-300 sm:min-h-0"
            :class="{ 'ring-2 ring-indigo-500/20 border-indigo-500 bg-white': isOpen }"
        >
            <span class="truncate font-medium tracking-tight" :class="selectedOption ? 'text-slate-800' : 'text-slate-500'">
                {{ selectedOption ? selectedOption.nama_kota : placeholder }}
            </span>
            <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
        </div>

        <!-- [UBAH KHUSUS MOBILE & DESKTOP] Menu Dropdown dengan efek Glassmorphism tipis, membatasi tinggi agar tidak merusak tata letak layar HP -->
        <transition 
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform scale-95 opacity-0 translate-y-[-10px]"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 translate-y-[-10px]"
        >
            <div v-if="isOpen" class="absolute z-50 w-full mt-2 bg-white/95 backdrop-blur-sm border border-slate-200/80 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] overflow-hidden origin-top">
                
                <!-- Kotak Pencarian Pintar -->
                <div class="p-2 border-b border-slate-100 bg-slate-50/50">
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs" />
                        <input 
                            v-model="search" 
                            type="text" 
                            class="w-full pl-8 pr-3 py-2 text-sm bg-white border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" 
                            placeholder="Ketik nama kota..."
                            @click.stop
                        >
                    </div>
                </div>

                <!-- Daftar Pilihan Kota dengan Custom Scrollbar yang Mulus -->
                <div class="max-h-56 overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-300 [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-slate-400 overscroll-contain">
                    <div v-if="filteredOptions.length === 0" class="p-4 text-sm font-medium tracking-tight text-slate-500 text-center">
                        Data kota tidak ditemukan.
                    </div>
                    <div 
                        v-for="opt in filteredOptions" 
                        :key="opt.id"
                        @click.stop="selectOption(opt)"
                        class="px-3 py-2.5 text-sm cursor-pointer transition-colors duration-150 border-b border-slate-50 last:border-0"
                        :class="{ 
                            'bg-indigo-50 text-indigo-700 font-semibold': String(opt.id) === String(modelValue), 
                            'text-slate-700 font-medium tracking-tight hover:bg-slate-50': String(opt.id) !== String(modelValue) 
                        }"
                    >
                        {{ opt.nama_kota }}
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
