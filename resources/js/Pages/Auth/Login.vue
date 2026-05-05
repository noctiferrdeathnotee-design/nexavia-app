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

        <div class="w-full rounded-2xl bg-white p-6 shadow-xl sm:p-8">
            
            <!-- Mobile Branding (Only visible on small screens since AuthLayout handles desktop) -->
            <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                <img src="/images/logo-brand.png" alt="Nexavia" class="h-8 w-8 object-contain">
                <span class="text-sm font-semibold tracking-wide text-slate-800">Nexavia Admin</span>
            </div>

            <!-- Flash Messages (Like in the image) -->
            <div v-if="page.props.flash?.success" class="mb-5 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="mb-5 rounded border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700">
                {{ page.props.flash.error }}
            </div>

            <!-- Header -->
            <div class="mb-6">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wider text-[#1E5D7B]">
                    Admin Access
                </p>
                <h1 class="text-2xl font-bold text-slate-900">
                    Login Admin
                </h1>
                <p class="mt-1 text-[13px] text-slate-600">
                    Gunakan akun admin untuk masuk ke dashboard.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email Field -->
                <div class="group relative">
                    <!-- Envelope Icon -->
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#1E5D7B]">
                        <i class="bi bi-envelope"></i>
                    </div>

                    <input id="email" v-model="form.email" type="email" autocomplete="username"
                        @focus="isEmailFocused = true" @blur="isEmailFocused = false"
                        class="block w-full rounded-lg border border-slate-300 bg-white pl-10 pr-4 pb-2 pt-5 text-[13px] font-medium text-slate-900 shadow-sm transition-all duration-300 focus:border-[#1E5D7B] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1E5D7B]"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/10': emailError }" />
                    
                    <label for="email"
                        class="pointer-events-none absolute left-10 z-10 origin-left transition-all duration-300"
                        :class="[form.email || isEmailFocused ? 'top-1.5 text-[10px] font-semibold text-[#1E5D7B]' : 'top-1/2 -translate-y-1/2 text-[13px] text-slate-500', emailError && (form.email || isEmailFocused) ? 'text-red-500' : '']"
                    >
                        Masukan Email Admin
                    </label>
                    
                    <p v-if="emailError" class="mt-1 flex items-center gap-1 text-[11px] font-medium text-red-500">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ emailError }}
                    </p>
                </div>

                <!-- Password Field -->
                <div class="group relative">
                    <!-- Lock Icon -->
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#1E5D7B]">
                        <i class="bi bi-lock"></i>
                    </div>

                    <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        @focus="isPasswordFocused = true" @blur="isPasswordFocused = false"
                        class="block w-full rounded-lg border border-slate-300 bg-white pl-10 pr-10 pb-2 pt-5 text-[13px] font-medium text-slate-900 shadow-sm transition-all duration-300 focus:border-[#1E5D7B] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1E5D7B]"
                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500/10': passwordError }" />
                    
                    <label for="password"
                        class="pointer-events-none absolute left-10 z-10 origin-left transition-all duration-300"
                        :class="[form.password || isPasswordFocused ? 'top-1.5 text-[10px] font-semibold text-[#1E5D7B]' : 'top-1/2 -translate-y-1/2 text-[13px] text-slate-500', passwordError && (form.password || isPasswordFocused) ? 'text-red-500' : '']"
                    >
                        Masukkan password
                    </label>

                    <button type="button"
                        class="absolute inset-y-0 right-1 flex h-full w-8 items-center justify-center text-slate-400 transition-colors hover:text-[#1E5D7B]"
                        @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>

                    <p v-if="passwordError" class="mt-1 flex items-center gap-1 text-[11px] font-medium text-red-500">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ passwordError }}
                    </p>
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

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing"
                    class="w-full rounded-lg bg-[#1E5D7B] px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm transition-all hover:bg-[#16465c] hover:shadow-md active:translate-y-px disabled:cursor-not-allowed disabled:opacity-70">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>
    </AuthLayout>
</template>