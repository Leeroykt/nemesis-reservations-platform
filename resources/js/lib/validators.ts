/**
 * Validation Rules Mirroring Backend
 * For frontend UX only; backend always validates.
 * Ref: 09c-FORM-VALIDATION.md
 */

export const VALIDATION_RULES = {
  guest_name: { required: true, max: 120 },
  guest_phone: { required: true, max: 40 },
  guest_email: { nullable: true, email: true, max: 160 },
  date: { required: true, date: true, after_or_equal: 'today' },
  time: { required: true, time: true },
  party_size: { required: true, integer: true, min: 1, max: 14 }, // default max from restaurant rules
  table_id: { nullable: true, exists: 'tables,id' },
  notes: { nullable: true, string: true },
  source: { nullable: true, in: ['Website', 'Phone', 'App', 'Walk-in'] },
  status: { nullable: true, in: ['Upcoming', 'Confirmed', 'Completed', 'Cancelled'] },
};