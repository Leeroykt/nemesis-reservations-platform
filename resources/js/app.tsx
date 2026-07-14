import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

// Vite glob is available - properly typed
const pages = import.meta.glob('./Pages/**/*.tsx')

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, pages),
  setup({ el, App, props }) {
    const root = createRoot(el)
    root.render(<App {...props} />)
  },
})