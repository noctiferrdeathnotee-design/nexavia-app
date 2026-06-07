<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
// [UPDATE] Import ToggleSwitch premium Xaviera
import ToggleSwitch from '@/Components/ToggleSwitch.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const showPassword = ref(false)
const isEmailFocused = ref(false)
const isPasswordFocused = ref(false)

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const emailError = computed(() => form.errors.email || '')
const passwordError = computed(() => form.errors.password || '')

const swal = window.Swal

const validateBeforeSubmit = () => {
    if (!form.email.trim() || !form.password.trim()) {
        swal?.fire({
            icon: 'warning',
            title: 'Form belum lengkap',
            text: 'Email dan password wajib diisi.',
            customClass: {
                // [UPDATE] Warna alert disesuaikan dengan Midnight Blue Xaviera
                confirmButton: 'rounded-lg bg-[#0B132B] px-6 py-2 text-white shadow-md'
            }
        })
        return false
    }

    if (!/@gmail\.com$/i.test(form.email.trim())) {
        swal?.fire({
            icon: 'warning',
            title: 'Email tidak valid',
            text: 'Email admin harus menggunakan akun @gmail.com.',
            customClass: {
                confirmButton: 'rounded-lg bg-[#0B132B] px-6 py-2 text-white shadow-md'
            }
        })
        return false
    }

    return true
}

const submit = () => {
    if (!validateBeforeSubmit()) {
        return
    }

    form.transform((data) => ({
        ...data,
        email: data.email.trim().toLowerCase(),
    })).post(route('login.store'), {
        preserveScroll: true,
        onFinish: () => {
            form.transform((data) => data)
            form.password = ''
        },
    })
}
</script>

<template>
    <AuthLayout title="Login">
        <Head title="Login" />

        <!-- Kontainer Form (Mobile: Premium shadow & padding | Desktop: tetap aslinya) -->
        <div class="w-full rounded-[24px] bg-white p-7 shadow-[0_8px_30px_rgb(0,0,0,0.08)] sm:rounded-2xl sm:p-8 sm:shadow-xl">
            
            <!-- [UPDATE] Menampilkan Logo Xaviera Raksasa -->
            <div class="mb-8 flex flex-col items-center justify-center gap-3">
                <div class="rounded-2xl bg-slate-50 p-4 shadow-sm ring-1 ring-slate-100 flex items-center justify-center">
                    <img src="/images/logo-xaviera.jpg" alt="Xaviera" class="w-full max-w-[140px] object-contain drop-shadow-md">
                </div>
                <span class="text-[15px] font-black tracking-widest text-[#0B132B] font-['Cinzel']">XAVIERA ADMIN</span>
            </div>

            <!-- Flash Messages (Like in the image) -->
            <div v-if="page.props.flash?.success" class="mb-5 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="mb-5 rounded border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700">
                {{ page.props.flash.error }}
            </div>

            <!-- [UBAH] Header Terpusat (Tipografi Premium) -->
            <div class="mb-8 text-center">
                <p class="mb-1.5 text-[12px] font-black uppercase tracking-widest text-[#B8860B] sm:mb-1 sm:text-[11px]">
                    Admin Access
                </p>
                <h1 class="text-[26px] font-black tracking-tight text-[#0B132B] sm:text-2xl font-['Cinzel']">
                    Login Admin
                </h1>
                <p class="mt-2 text-[14px] font-medium text-slate-500 sm:mt-1 sm:text-[13px] sm:text-slate-600">
                    Gunakan akun admin untuk masuk ke dashboard.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email Field (Input Mobile Premium, Accessibility 100%, Anti-CLS) -->
                <div class="group relative">
                    <!-- Envelope Icon -->
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#B8860B] sm:left-3.5">
                        <i class="bi bi-envelope"></i>
                    </div>

                    <input id="email" v-model="form.email" type="email" autocomplete="username" aria-label="Masukan Email Admin"
                        @focus="isEmailFocused = true" @blur="isEmailFocused = false"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-11 pr-4 pb-2.5 pt-6 text-[14px] font-medium text-[#0B132B] shadow-sm transition-all duration-300 focus:border-[#B8860B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#B8860B]/20 sm:rounded-lg sm:border-slate-300 sm:bg-white sm:pl-10 sm:pb-2 sm:pt-5 sm:text-[13px] sm:focus:ring-1 sm:focus:ring-[#B8860B]"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20 sm:focus:ring-red-500/10': emailError }" />
                    
                    <label for="email"
                        class="pointer-events-none absolute left-11 z-10 origin-left transition-all duration-300 sm:left-10"
                        :class="[form.email || isEmailFocused ? 'top-1.5 text-[10px] font-bold text-[#B8860B] sm:font-semibold' : 'top-1/2 -translate-y-1/2 text-[14px] text-slate-500 sm:text-[13px]', emailError && (form.email || isEmailFocused) ? 'text-red-500' : '']"
                    >
                        Masukan Email Admin
                    </label>
                    
                    <!-- Kontainer Error Fixed Min-Height Mencegah CLS (Layout Bergeser) -->
                    <div class="min-h-[20px] mt-1">
                        <p v-show="emailError" class="flex items-center gap-1 text-[11px] font-semibold text-red-500 sm:font-medium">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ emailError }}
                        </p>
                    </div>
                </div>

                <!-- Password Field (Input Mobile Premium, Accessibility 100%, Anti-CLS) -->
                <div class="group relative">
                    <!-- Lock Icon -->
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#B8860B] sm:left-3.5">
                        <i class="bi bi-lock"></i>
                    </div>

                    <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password" aria-label="Masukkan password"
                        @focus="isPasswordFocused = true" @blur="isPasswordFocused = false"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-11 pr-10 pb-2.5 pt-6 text-[14px] font-medium text-[#0B132B] shadow-sm transition-all duration-300 focus:border-[#B8860B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#B8860B]/20 sm:rounded-lg sm:border-slate-300 sm:bg-white sm:pl-10 sm:pb-2 sm:pt-5 sm:text-[13px] sm:focus:ring-1 sm:focus:ring-[#B8860B]"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20 sm:focus:ring-red-500/10': passwordError }" />
                    
                    <label for="password"
                        class="pointer-events-none absolute left-11 z-10 origin-left transition-all duration-300 sm:left-10"
                        :class="[form.password || isPasswordFocused ? 'top-1.5 text-[10px] font-bold text-[#B8860B] sm:font-semibold' : 'top-1/2 -translate-y-1/2 text-[14px] text-slate-500 sm:text-[13px]', passwordError && (form.password || isPasswordFocused) ? 'text-red-500' : '']"
                    >
                        Masukkan password
                    </label>

                    <button type="button" aria-label="Tampilkan atau sembunyikan password"
                        class="absolute inset-y-0 right-1 flex h-full w-10 sm:w-8 items-center justify-center text-slate-400 transition-colors hover:text-[#B8860B]"
                        @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>

                    <!-- Kontainer Error Fixed Min-Height Mencegah CLS (Layout Bergeser) -->
                    <div class="min-h-[20px] mt-1">
                        <p v-show="passwordError" class="flex items-center gap-1 text-[11px] font-semibold text-red-500 sm:font-medium">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ passwordError }}
                        </p>
                    </div>
                </div>

                <!-- Options -->
                <div class="flex items-center justify-between pt-1 pb-2">
                    <!-- [UPDATE] Memakai ToggleSwitch Premium untuk menggantikan Checkbox usang -->
                    <ToggleSwitch v-model="form.remember" label="Ingat saya" />

                    <div v-if="form.processing" class="flex items-center gap-1.5 text-[12px] text-slate-500">
                        <i class="bi bi-arrow-repeat animate-spin text-sm text-[#0B132B]"></i>
                        Memproses...
                    </div>
                </div>

                <!-- [UBAH KHUSUS MOBILE & DESKTOP] Tombol Login Premium membulat -->
                <button type="submit" :disabled="form.processing"
                    class="w-full rounded-xl bg-[#0B132B] px-4 py-3 sm:py-2.5 text-[14px] sm:text-[13px] font-bold tracking-wide text-[#D4AF37] shadow-md transition-all duration-300 hover:bg-[#1C2541] hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>
    </AuthLayout>
</template>