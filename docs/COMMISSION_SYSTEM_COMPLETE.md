# Commission System - Complete Documentation

## Overview

The commission system uses a hierarchical differential rate structure where commissions cascade upward through the referral chain using **difference-based allocation**. Each role in the hierarchy receives a different commission rate based on their position.

## Hierarchy & Commission Rates

### Tier Hierarchy (Lowest to Highest)
1. **LOA** (Licensed Only Agent) - Lowest tier, commissions tracked through parent agent
2. **ASSOCIATE** - 20% (monthly) / 14% (biannual/annual)
3. **AGENT** - 30% (monthly) / 15% (biannual/annual)
4. **MGA** (Managing General Agent) - 40% (monthly) / 20% (biannual/annual)
5. **SVG** (Sales Vice Group) - 45% (monthly) / 23% (biannual/annual)
6. **FMO** (Field Marketing Organization) - 50% (monthly) / 25% (biannual/annual)
7. **SFMO** (Super Field Marketing Organization) - 55% (monthly) / 27.5% (biannual/annual)

### Tier-Based Commission Caps
- SFMO: 55% (highest tier cap)
- FMO: 50%
- SVG: 45%
- MGA: 40%
- AGENT: 30%
- ASSOCIATE: 20%

## Difference-Based Allocation Algorithm

Commissions are distributed using **difference-based allocation** where each agent receives the difference between their tier rate and the previous tier's rate.

### Example: FMO → SVG → MGA → Agent → Patient ($100/month)

**Processing Order:** Agent (lowest) → MGA → SVG → FMO (highest)

```
Agent (30%):  30% - 0%   = 30% → $30.00
MGA (40%):    40% - 30%  = 10% → $10.00
SVG (45%):    45% - 40%  = 5%  → $5.00
FMO (50%):    50% - 45%  = 5%  → $5.00
─────────────────────────────────────
Total:                     50% → $50.00 (respects FMO cap)
```

### Key Features
- Processes agents from lowest to highest tier
- Each agent gets the difference between their rate and the previous tier
- Respects the maximum cap based on highest tier in chain
- LOA agents are completely filtered out (not included in calculations)
- All eligible agents in the chain receive commissions

## Commission Lifecycle

1. **Pending** - Created immediately after successful payment
2. **Paid** - Marked when included in a payout to the agent
3. **Cancelled** - Can be cancelled if subscription/transaction is cancelled

## Core Components

### CommissionCalculationEngine
**Location:** `app/Domain/Commission/CommissionCalculationEngine.php`

Implements the difference-based allocation algorithm:
- `calculateCommissionCascade()` - Main calculation method
- `filterEligibleAgents()` - Removes LOA agents
- `getHighestTier()` - Determines cap based on highest tier
- `distributeCommissionsByChain()` - Applies difference-based allocation

### ReferralChainBuilder
**Location:** `app/Application/Commission/ReferralChainBuilder.php`

Automatically traces referral chains from patients upward:
- Starts with patient's direct agent referrer
- If no direct referrer, checks for LOA referrer and gets their agent
- Skips LOA agents entirely
- Prevents infinite loops with circular reference detection

### CommissionCalculationService
**Location:** `app/Application/Commission/CommissionCalculationService.php`

High-level orchestration service combining chain building and calculation:
- `calculateForUser()` - Main entry point for commission calculation
- `calculateWithChain()` - Explicit chain calculation
- `getReferralData()` - Get chain without calculating

## LOA (Licensed Only Agent) Handling

- LOA users are the lowest tier, positioned below agents
- LOA users do NOT receive commissions directly
- Commissions earned by LOA users are tracked through their parent agent
- LOA users are completely filtered out from commission calculations
- The system traces: Patient → LOA → LOA's Agent → Upline

## Usage Example

```php
$service = new CommissionCalculationService();
$commissions = $service->calculateForUser($user, 100.00, 'monthly');

// Returns:
// [
//   ['agent_id' => 1, 'tier' => 'agent', 'rate' => 30.0, 'amount' => 30.00, ...],
//   ['agent_id' => 2, 'tier' => 'mga', 'rate' => 10.0, 'amount' => 10.00, ...],
//   ['agent_id' => 3, 'tier' => 'svg', 'rate' => 5.0, 'amount' => 5.00, ...],
//   ['agent_id' => 4, 'tier' => 'fmo', 'rate' => 5.0, 'amount' => 5.00, ...],
// ]
```

## Testing

All commission functionality is thoroughly tested:
- **CommissionCalculationEngineTest** - 14 tests for core algorithm
- **CommissionCalculationServiceTest** - 7 tests for service layer
- **CommissionEdgeCasesTest** - 10 tests for edge cases
- **CommissionScenarioTest** - 5 tests for real-world scenarios
- **ReferralChainBuilderTest** - 8 tests for chain building
- **CommissionAggregateTest** - 4 tests for domain aggregate

**Total: 48 tests with 192 assertions - All passing ✅**

