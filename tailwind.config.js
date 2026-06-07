import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            colors: {
                // [UPDATE] Definisi warna premium Xaviera sesuai Blueprint
                // xaviera-blue: Biru gelap mewah (Midnight Blue) dari warna bola dunia logo
                // xaviera-gold: Emas kerajaan (Royal Gold) dari ukiran sayap logo
                xaviera: {
                    blue: "#0B132B",
                    blueLight: "#1C2541",
                    gold: "#B8860B",
                    goldLight: "#D4AF37",
                },
                primary: {
                    50: "#EEF2FF",
                    100: "#E0E7FF",
                    500: "#6366F1",
                    600: "#4F46E5",
                    700: "#4338CA",
                },
            },
            fontFamily: {
                // [UPDATE] Menggunakan font premium: Plus Jakarta Sans untuk teks umum (HD, tajam)
                // dan Cinzel untuk teks judul/brand agar serasi dengan ornamen logo steampunk.
                sans: ["'Plus Jakarta Sans'", ...defaultTheme.fontFamily.sans],
                cinzel: ["'Cinzel'", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
