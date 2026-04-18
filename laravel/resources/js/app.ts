import { createApp, h, DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import { ZiggyVue } from 'ziggy-js'
import type { Config } from 'ziggy-js'
import type { SharedProps } from '@/types/models'
import '../css/app.css'

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        const ziggyConfig = (props.initialPage.props as unknown as SharedProps).ziggy as unknown as Config
        Object.assign(globalThis, { Ziggy: ziggyConfig })

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .use(ZiggyVue, ziggyConfig)
            .mount(el)
    },
})
