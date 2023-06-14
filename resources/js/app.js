import { createApp } from "vue/dist/vue.esm-bundler";

import RadicadosCreate from "@/Pages/Radicados/Create.vue";

// Como no nos es posible por el momento usar un framework como Inertia.js, se
// tiene que hacer más o menos de esta forma la adición de componentes de Vue
// a las páginas.
//
// En el caso de VUE_PAGES_ROOT, hace referencia a páginas las cuales el
// componente principal único es de VueJs, y solo debería existir uno.
const VUE_PAGES_ROOT_ID = "vue-page";

const root = document.getElementById(VUE_PAGES_ROOT_ID);

if (root) {
    createApp({
        components: { RadicadosCreate },
    }).mount(root);
}
