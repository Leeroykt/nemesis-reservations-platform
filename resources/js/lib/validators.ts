/**
 * Validation Rules Mirroring Backend
 * For frontend UX only; backend always validates.
 * Ref: 09c-FORM-VALIDATION.md
 */

// ============================================================
// TYPES
// ============================================================

interface BaseRule {
  required?: boolean;
  nullable?: boolean;
  max?: number;
  min?: number;
  email?: boolean;
  date?: boolean;
  after_or_equal?: 'today';
  integer?: boolean;
  string?: boolean;
  exists?: string;
  in?: readonly string[];
}

// ============================================================
// VALIDATION RULES
// ============================================================

export const VALIDATION_RULES: Record<string, BaseRule> = {
  guest_name: { required: true, max: 120 },
  guest_phone: { required: true, max: 40 },
  guest_email: { nullable: true, email: true, max: 160 },
  date: { required: true, date: true, after_or_equal: 'today' },
  time: { required: true },
  party_size: { required: true, integer: true, min: 1, max: 14 },
  table_id: { nullable: true, exists: 'tables,id' },
  notes: { nullable: true, string: true },
  source: { nullable: true, in: ['Website', 'Phone', 'App', 'Walk-in'] as const },
  status: { nullable: true, in: ['Upcoming', 'Confirmed', 'Completed', 'Cancelled'] as const },
} as const;

export type ValidationField = keyof typeof VALIDATION_RULES;

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get validation rules for a field.
 */
function getRules(field: ValidationField): BaseRule {
  return VALIDATION_RULES[field];
}

/**
 * Check if a value is empty (null, undefined, or empty string).
 */
function isEmpty(value: any): boolean {
  return value === undefined || value === null || value === '';
}

/**
 * Validate a single field against its rules.
 * 
 * @param field - The field name to validate
 * @param value - The value to validate
 * @returns True if valid, false otherwise
 */
export function validateField(field: ValidationField, value: any): boolean {
  const errors = getFieldErrors(field, value);
  return errors.length === 0;
}

/**
 * Get error messages for a field based on validation rules.
 * 
 * @param field - The field name to validate
 * @param value - The value to validate
 * @returns Array of error messages
 */
export function getFieldErrors(field: ValidationField, value: any): string[] {
  const rules = getRules(field);
  const errors: string[] = [];

  // Required check
  if (rules.required && isEmpty(value)) {
    const fieldName = field.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    errors.push(`${fieldName} is required.`);
    return errors;
  }

  // Skip further validation if value is empty and field is nullable
  if (rules.nullable && isEmpty(value)) {
    return errors;
  }

  // If value is empty and not required, skip further validation
  if (isEmpty(value) && !rules.required) {
    return errors;
  }

  // Max length check
  if (rules.max && typeof value === 'string' && value.length > rules.max) {
    errors.push(`This field cannot exceed ${rules.max} characters.`);
  }

  // Email validation
  if (rules.email && typeof value === 'string' && value.length > 0) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      errors.push('Please enter a valid email address.');
    }
  }

  // Date validation
  if (rules.date && typeof value === 'string' && value.length > 0) {
    const date = new Date(value);
    if (isNaN(date.getTime())) {
      errors.push('Please enter a valid date.');
    }
    if (rules.after_or_equal === 'today') {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (date < today) {
        errors.push('Date cannot be in the past.');
      }
    }
  }

  // Integer validation
  if (rules.integer && typeof value === 'number' && !Number.isInteger(value)) {
    errors.push('Value must be a whole number.');
  }

  // Min value check
  if (rules.min !== undefined && typeof value === 'number' && value < rules.min) {
    errors.push(`Value must be at least ${rules.min}.`);
  }

  // Max value check (for numbers)
  if (rules.max !== undefined && typeof value === 'number' && value > rules.max) {
    errors.push(`Value cannot exceed ${rules.max}.`);
  }

  // Enum validation
  if (rules.in && Array.isArray(rules.in) && typeof value === 'string' && value.length > 0) {
    if (!rules.in.includes(value)) {
      errors.push(`Value must be one of: ${rules.in.join(', ')}.`);
    }
  }

  return errors;
}

/**
 * Validate multiple fields at once.
 * 
 * @param fields - Object of field => value pairs
 * @returns Object of field => errors array
 */
export function validateFields(fields: Record<ValidationField, any>): Record<ValidationField, string[]> {
  const result: Record<ValidationField, string[]> = {} as Record<ValidationField, string[]>;
  
  for (const [field, value] of Object.entries(fields)) {
    result[field as ValidationField] = getFieldErrors(field as ValidationField, value);
  }
  
  return result;
}

/**
 * Check if a field is required.
 * 
 * @param field - The field name to check
 * @returns True if required, false otherwise
 */
export function isFieldRequired(field: ValidationField): boolean {
  return getRules(field).required || false;
}

/**
 * Get validation hints for a field (for UI).
 * 
 * @param field - The field name
 * @returns Object with hint information
 */
export function getFieldHints(field: ValidationField): {
  required: boolean;
  maxLength?: number;
  min?: number;
  max?: number;
  type?: string;
} {
  const rules = getRules(field);
  return {
    required: rules.required || false,
    maxLength: rules.max,
    min: rules.min,
    max: rules.max,
    type: rules.email ? 'email' : rules.date ? 'date' : rules.integer ? 'number' : 'text',
  };
}