<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
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
                confirmButton: 'rounded-lg bg-[#1E5D7B] px-6 py-2 text-white shadow-md'
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
                confirmButton: 'rounded-lg bg-[#1E5D7B] px-6 py-2 text-white shadow-md'
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
            
            <!-- Mobile Branding (Dioptimalkan untuk mobile: tampilan premium dan spacing presisi) -->
            <div class="mb-10 flex flex-col items-center justify-center gap-3 lg:hidden">
                <div class="rounded-2xl bg-slate-50 p-3 shadow-sm ring-1 ring-slate-100">
                    <img src="/images/logo-brand.png" alt="Nexavia" class="h-10 w-10 object-contain drop-shadow-sm">
                </div>
                <span class="text-[15px] font-bold tracking-wide text-slate-800">Nexavia Admin</span>
            </div>

            <!-- Flash Messages (Like in the image) -->
            <div v-if="page.props.flash?.success" class="mb-5 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="mb-5 rounded border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700">
                {{ page.props.flash.error }}
            </div>

            <!-- Header (Dioptimalkan typography untuk mobile, desktop dipertahankan text-left aslinya) -->
            <div class="mb-8 text-center sm:mb-6 sm:text-left">
                <p class="mb-1.5 text-[12px] font-bold uppercase tracking-widest text-[#1E5D7B] sm:mb-1 sm:text-[11px] sm:font-semibold sm:tracking-wider">
                    Admin Access
                </p>
                <h1 class="text-[26px] font-extrabold tracking-tight text-slate-900 sm:text-2xl sm:font-bold">
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
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#1E5D7B] sm:left-3.5">
                        <i class="bi bi-envelope"></i>
                    </div>

                    <input id="email" v-model="form.email" type="email" autocomplete="username" aria-label="Masukan Email Admin"
                        @focus="isEmailFocused = true" @blur="isEmailFocused = false"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-11 pr-4 pb-2.5 pt-6 text-[14px] font-medium text-slate-900 shadow-sm transition-all duration-300 focus:border-[#1E5D7B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E5D7B]/20 sm:rounded-lg sm:border-slate-300 sm:bg-white sm:pl-10 sm:pb-2 sm:pt-5 sm:text-[13px] sm:focus:ring-1 sm:focus:ring-[#1E5D7B]"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20 sm:focus:ring-red-500/10': emailError }" />
                    
                    <label for="email"
                        class="pointer-events-none absolute left-11 z-10 origin-left transition-all duration-300 sm:left-10"
                        :class="[form.email || isEmailFocused ? 'top-1.5 text-[10px] font-bold text-[#1E5D7B] sm:font-semibold' : 'top-1/2 -translate-y-1/2 text-[14px] text-slate-500 sm:text-[13px]', emailError && (form.email || isEmailFocused) ? 'text-red-500' : '']"
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
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#1E5D7B] sm:left-3.5">
                        <i class="bi bi-lock"></i>
                    </div>

                    <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password" aria-label="Masukkan password"
                        @focus="isPasswordFocused = true" @blur="isPasswordFocused = false"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-11 pr-10 pb-2.5 pt-6 text-[14px] font-medium text-slate-900 shadow-sm transition-all duration-300 focus:border-[#1E5D7B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E5D7B]/20 sm:rounded-lg sm:border-slate-300 sm:bg-white sm:pl-10 sm:pb-2 sm:pt-5 sm:text-[13px] sm:focus:ring-1 sm:focus:ring-[#1E5D7B]"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/20 sm:focus:ring-red-500/10': passwordError }" />
                    
                    <label for="password"
                        class="pointer-events-none absolute left-11 z-10 origin-left transition-all duration-300 sm:left-10"
                        :class="[form.password || isPasswordFocused ? 'top-1.5 text-[10px] font-bold text-[#1E5D7B] sm:font-semibold' : 'top-1/2 -translate-y-1/2 text-[14px] text-slate-500 sm:text-[13px]', passwordError && (form.password || isPasswordFocused) ? 'text-red-500' : '']"
                    >
                        Masukkan password
                    </label>

                    <button type="button" aria-label="Tampilkan atau sembunyikan password"
                        class="absolute inset-y-0 right-1 flex h-full w-10 sm:w-8 items-center justify-center text-slate-400 transition-colors hover:text-[#1E5D7B]"
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
                    <label class="group flex cursor-pointer items-center gap-2">
                        <div class="relative flex items-center justify-center">
                            <input v-model="form.remember" type="checkbox"
                                class="peer h-3.5 w-3.5 cursor-pointer appearance-none rounded-sm border border-slate-300 bg-white transition-all checked:border-[#1E5D7B] checked:bg-[#1E5D7B] hover:border-[#1E5D7B] focus:outline-none focus:ring-2 focus:ring-[#1E5D7B]/20">
                            <i class="bi bi-check pointer-events-none absolute text-[10px] text-white opacity-0 transition-opacity peer-checked:opacity-100"></i>
                        </div>
                        <span class="text-[13px] text-slate-600 transition-colors group-hover:text-slate-900">Ingat saya</span>
                    </label>

                    <div v-if="form.processing" class="flex items-center gap-1.5 text-[12px] text-slate-500">
                        <i class="bi bi-arrow-repeat animate-spin text-sm text-[#1E5D7B]"></i>
                        Memproses...
                    </div>
                </div>

                <!-- Submit Button (Ditingkatkan responsivitas dan efek hover mobile) -->
                <button type="submit" :disabled="form.processing"
                    class="w-full rounded-xl sm:rounded-lg bg-[#1E5D7B] px-4 py-3 sm:py-2.5 text-[14px] sm:text-[13px] font-bold sm:font-semibold tracking-wide text-white shadow-md sm:shadow-sm transition-all duration-300 hover:bg-[#16465c] hover:shadow-lg sm:hover:shadow-md active:scale-[0.98] sm:active:scale-100 sm:active:translate-y-px disabled:cursor-not-allowed disabled:opacity-70 disabled:active:scale-100">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>
    </AuthLayout>
</template>