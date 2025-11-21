# Billing Page Design & Architecture

## Overview
Comprehensive patient-facing billing page for managing payment methods with support for credit cards, ACH accounts, and invoice payment methods.

## Route & Access
- **Route**: `/billing` (patient authenticated)
- **Page Title**: "Billing & Payment Methods"
- **Access**: Patients only (authenticated users with patient role)
- **Layout**: Dashboard-style card layout with clear sections

## Database Schema Changes

### New Fields for `payment_methods` Table
```sql
ALTER TABLE payment_methods ADD COLUMN verification_status VARCHAR(50) DEFAULT 'pending';
ALTER TABLE payment_methods ADD COLUMN verification_attempts INT DEFAULT 0;
ALTER TABLE payment_methods ADD COLUMN last_verification_attempt_at TIMESTAMP NULL;
ALTER TABLE payment_methods ADD COLUMN archived_at TIMESTAMP NULL;
```

### Verification Status Values
- `pending` - Awaiting verification (for ACH)
- `verified` - Successfully verified
- `failed` - Verification failed
- `active` - Ready for use (credit card, invoice)

## API Endpoints

### Payment Methods CRUD
- `GET /api/patient/payment-methods` - List all payment methods
- `POST /api/patient/payment-methods` - Create new payment method
- `GET /api/patient/payment-methods/{id}` - Get payment method details
- `PATCH /api/patient/payment-methods/{id}` - Update payment method
- `DELETE /api/patient/payment-methods/{id}` - Remove payment method
- `POST /api/patient/payment-methods/{id}/set-default` - Set as default
- `POST /api/patient/payment-methods/{id}/verify-ach` - Verify ACH with micro-deposits

## Frontend Components

### Main Components
1. **BillingPage.vue** - Main page container
2. **PaymentMethodList.vue** - List/grid of payment methods
3. **PaymentMethodCard.vue** - Individual payment method display
4. **AddPaymentMethodModal.vue** - Modal with tabs for different types

### Form Components
5. **CreditCardForm.vue** - Credit card input form
6. **AchForm.vue** - ACH account input form
7. **InvoiceForm.vue** - Invoice details form
8. **VerificationFlow.vue** - ACH verification components

## State Management (Pinia)
- `paymentMethodsStore` - Manage payment methods state
- Actions: `fetchPaymentMethods`, `addPaymentMethod`, `updatePaymentMethod`, `removePaymentMethod`, `setDefault`
- Getters: `defaultPaymentMethod`, `paymentMethodsByType`, `unverifiedAchMethods`

## Validation Rules

### Credit Card
- Card number: Valid Luhn algorithm
- Expiration: Not expired
- CVV: 3-4 digits
- Cardholder name: Required

### ACH
- Routing number: Valid 9-digit number
- Account number: 4-17 digits
- Account type: checking or savings
- Account holder name: Required

### Invoice
- Company name: Required
- Email: Valid email format
- Contact name: Required
- Phone: Valid phone format

## Security Considerations
- No raw card data stored (tokenization via Authorize.net)
- CSRF protection on all forms
- Authorization checks on all endpoints
- Rate limiting on payment method operations
- Soft delete for payment methods (archived_at)
- Hidden sensitive fields in API responses

## User Experience Features
- Loading states with spinners
- Success/error toast notifications
- Empty state when no payment methods
- Responsive design (mobile, tablet, desktop)
- Keyboard navigation support
- Confirmation dialogs for destructive actions
- Real-time form validation
- Clear error messages

## Testing Strategy
- Unit tests for validation logic
- Feature tests for API endpoints
- Component tests for Vue components
- Browser tests for responsive design
- E2E tests for complete workflows

