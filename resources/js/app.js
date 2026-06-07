import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const appName = "xaviera";

createInertiaApp({
    // [UBAH KHUSUS DESKTOP & MOBILE] Title tab Chrome dipaksa tunggal "xaviera" tanpa embel-embel nama halaman
    title: () => "xaviera",

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),

    setup({ el, App, props, plugin }) {
        return createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },

    progress: {
        color: "#6366F1",
    },
});
