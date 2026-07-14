import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import StatusBadge from '@/Components/Common/StatusBadge'

describe('StatusBadge', () => {
  it('renders confirmed status', () => {
    render(<StatusBadge status="Confirmed" />)
    const badge = screen.getByText('Confirmed')
    expect(badge).toBeDefined()
    expect(badge.className).toContain('confirmed')
  })

  it('renders upcoming status', () => {
    render(<StatusBadge status="Upcoming" />)
    const badge = screen.getByText('Upcoming')
    expect(badge).toBeDefined()
    expect(badge.className).toContain('upcoming')
  })

  it('renders cancelled status', () => {
    render(<StatusBadge status="Cancelled" />)
    const badge = screen.getByText('Cancelled')
    expect(badge).toBeDefined()
    expect(badge.className).toContain('cancelled')
  })

  it('renders completed status', () => {
    render(<StatusBadge status="Completed" />)
    const badge = screen.getByText('Completed')
    expect(badge).toBeDefined()
    expect(badge.className).toContain('completed')
  })
})