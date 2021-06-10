require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import { App as InertiaApp, plugin as InertiaPlugin } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import mitt from 'mitt'
import { VueMasonryPlugin } from "vue-masonry/src/masonry-vue3.plugin";

const el = document.getElementById('app');
const emitter = mitt()

const app = createApp({
    render: () =>
        h(InertiaApp, {
            initialPage: JSON.parse(el.dataset.page),
            resolveComponent: (name) => require(`./Pages/${name}`).default,
        }),
})

app.config.globalProperties.emitter = emitter

app.mixin({ methods: { route } })
    .use(InertiaPlugin)
    .use(VueMasonryPlugin)
    .mount(el);

InertiaProgress.init({ color: '#4B5563' });
