import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import KpiCard from '@/Components/Common/KpiCard'

describe('KpiCard', () => {
    it('renders label and value', () => {
        render(<KpiCard label="Revenue" value="$1,234" icon="bi-currency-dollar" />)
        expect(screen.getByText('Revenue')).toBeDefined()
        expect(screen.getByText('$1,234')).toBeDefined()
    })

    it('shows delta when provided', () => {
        render(<KpiCard label="Bookings" value="10" delta={5.5} icon="bi-calendar" />)
        expect(screen.getByText('5.5%')).toBeDefined()
        expect(screen.getByText('Bookings')).toBeDefined()
    })

    it('shows correct tone color', () => {
        const { container } = render(<KpiCard label="Test" value="100" icon="bi-star" tone="gold" />)
        const iconDiv = container.querySelector('.kpi-icon')
        expect(iconDiv).toHaveStyle('color: var(--gold)')
    })
})