import type { Router } from 'ziggy-js'

declare global {
    function route(name: string, params?: unknown, absolute?: boolean): string
    const Ziggy: Router
}
