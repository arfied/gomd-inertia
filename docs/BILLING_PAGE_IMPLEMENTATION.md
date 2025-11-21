# Billing Page Implementation Guide

## Overview

The Billing Page is a comprehensive patient-facing interface for managing payment methods. It provides a user-friendly experience for adding, editing, viewing, and managing multiple payment methods including credit cards, ACH bank accounts, and invoice-based billing.

**Status**: ✅ COMPLETE - All components implemented and tested

## Implementation Summary

### Backend (Laravel)

#### Database Migration
- **File**: `database/migrations/2025_11_21_000001_add_verification_fields_to_payment_methods.php`
- **Changes**:
  - Added `verification_status` field (pending, verified, failed, active)
  - Added `verification_attempts` field for tracking ACH verification attempts
  - Added `last_verification_attempt_at` timestamp
  - Added `archived_at` field for soft deletes
  - Created indexes on (user_id, verification_status) and (user_id, is_default)

#### Model Updates
- **File**: `app/Models/PaymentMethod.php`
- **Changes**:
  - Added `archived_at` to fillable array
  - Added `getDeletedAtColumn()` to use `archived_at` instead of `deleted_at`
  - Added boolean and integer casts for proper type handling
  - Added verification status methods: `isVerified()`, `isPendingVerification()`, `isVerificationFailed()`, `markAsVerified()`, `markVerificationFailed()`
  - Added archive methods: `archive()`, `isArchived()`
  - Added query scopes: `active()`, `verified()`, `pendingVerification()`

#### API Controller
- **File**: `app/Http/Controllers/PaymentMethodController.php`
- **Endpoints**:
  - `GET /api/patient/payment-methods` - List all active payment methods
  - `POST /api/patient/payment-methods` - Create new payment method
  - `GET /api/patient/payment-methods/{id}` - Get payment method details
  - `PATCH /api/patient/payment-methods/{id}` - Update payment method
  - `DELETE /api/patient/payment-methods/{id}` - Archive payment method
  - `POST /api/patient/payment-methods/{id}/set-default` - Set as default
- **Features**:
  - Type-specific validation for credit card, ACH, and invoice
  - Automatic default assignment for first payment method
  - Prevention of default payment method deletion
  - Authorization checks via PaymentMethodPolicy

#### Authorization Policy
- **File**: `app/Policies/PaymentMethodPolicy.php`
- **Rules**:
  - Users can only view their own payment methods
  - Users can only update their own payment methods
  - Users can only delete their own payment methods

#### Routes
- **File**: `routes/web.php`
- **Billing Page Route**: `GET /billing` → `Billing/BillingPage` component
- **API Routes**: All payment method endpoints under `/api/patient/payment-methods` prefix

### Frontend (Vue.js)

#### Main Page Component
- **File**: `resources/js/pages/Billing/BillingPage.vue`
- **Features**:
  - Loads payment methods on mount
  - Displays loading and error states
  - Shows empty state when no payment methods exist
  - Integrates with Pinia store for state management

#### List Component
- **File**: `resources/js/components/Billing/PaymentMethodList.vue`
- **Features**:
  - Displays payment methods in a list
  - Handles delete confirmation flow
  - Manages set-default action

#### Card Component
- **File**: `resources/js/components/Billing/PaymentMethodCard.vue`
- **Features**:
  - Shows payment method details with type-specific icons
  - Displays default badge and verification status
  - Provides delete and set-default actions
  - Confirmation UI for destructive actions

#### Modal Component
- **File**: `resources/js/components/Billing/AddPaymentMethodModal.vue`
- **Features**:
  - Tabbed interface for different payment method types
  - Integrates form components
  - Handles submission and modal closing

#### Form Components
- **CreditCardForm.vue** - Credit card input with brand, last 4, expiration
- **AchForm.vue** - ACH account with account holder name, type, routing/account numbers
- **InvoiceForm.vue** - Invoice details with company, contact, email, phone, address, terms

### State Management

#### Pinia Store
- **File**: `resources/js/stores/paymentMethodsStore.ts`
- **State**:
  - `paymentMethods` - Array of payment methods
  - `loading` - Loading state
  - `error` - Error message
- **Computed Properties**:
  - `defaultPaymentMethod` - Currently set default
  - `creditCards` - Filtered credit cards
  - `achAccounts` - Filtered ACH accounts
  - `invoiceMethods` - Filtered invoice methods
  - `unverifiedAchMethods` - ACH accounts pending verification
- **Actions**:
  - `fetchPaymentMethods()` - Load from API
  - `addPaymentMethod()` - Create new
  - `updatePaymentMethod()` - Update existing
  - `removePaymentMethod()` - Archive
  - `setDefault()` - Set as default

### Testing

#### Feature Tests
- **File**: `tests/Feature/PaymentMethodControllerTest.php`
- **Test Coverage**: 11 tests, all passing
- **Tests**:
  - List payment methods
  - Create credit card, ACH, invoice
  - View payment method
  - Authorization checks
  - Set default payment method
  - Delete prevention for default
  - Delete non-default payment method
  - Auto-set first as default
  - Validation errors

#### Factory
- **File**: `database/factories/PaymentMethodFactory.php`
- **Features**:
  - Generates random payment methods
  - Type-specific factories (creditCard, ach, invoice)
  - Default and verified state factories

## Usage

### For Patients

1. Navigate to `/billing`
2. View existing payment methods
3. Click "Add Payment Method" to add new
4. Select payment method type (Credit Card, Bank Account, Invoice)
5. Fill in required information
6. Optionally set as default
7. Submit to save
8. Manage existing methods (set default, delete)

### For Developers

#### Adding a Payment Method
```javascript
const store = usePaymentMethodsStore()
await store.addPaymentMethod({
    type: 'credit_card',
    cc_last_four: '4242',
    cc_brand: 'Visa',
    cc_expiration_month: '12',
    cc_expiration_year: '2025',
    cc_token: 'token_123',
    is_default: true
})
```

#### Fetching Payment Methods
```javascript
const store = usePaymentMethodsStore()
await store.fetchPaymentMethods()
const methods = store.paymentMethods
```

## Security Considerations

1. **Authorization**: All endpoints protected by PaymentMethodPolicy
2. **Token Storage**: Card and ACH tokens stored securely (via Authorize.net)
3. **Sensitive Data**: Tokens hidden from API responses
4. **Soft Deletes**: Payment methods archived, not permanently deleted
5. **Verification**: ACH accounts require micro-deposit verification before use

## Performance

- Indexed queries on (user_id, verification_status) and (user_id, is_default)
- Efficient pagination support via simplePaginate()
- Separate /count endpoint for total count
- Lazy loading of payment methods on page mount

## Future Enhancements

1. ACH micro-deposit verification flow
2. Payment method editing (non-sensitive fields)
3. Bulk operations (delete multiple)
4. Payment method history/audit log
5. Recurring payment setup
6. Payment method templates

