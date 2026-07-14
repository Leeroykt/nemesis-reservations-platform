import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

// @ts-ignore - Vite glob is available
const pages = import.meta.glob('./Pages/**/*.tsx')

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, pages),
  setup({ el, App, props }) {
    const root = createRoot(el)
    root.render(<App {...props} />)
  },
})