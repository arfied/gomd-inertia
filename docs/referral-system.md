# Referral System Documentation

## Overview

The Referral System is a comprehensive event-driven solution for tracking, managing, and visualizing agent referral networks in TeleMed Pro. It enables agents to generate unique referral links, track clicks and conversions, and visualize their referral network hierarchy with real-time performance metrics.

## Architecture

### Event-Driven Design

The referral system follows the event-sourcing pattern with three core domain events:

1. **ReferralLinkCreated** - Fired when an agent creates a new referral link
   - Aggregate Type: `referral_link`
   - Event Type: `referral_link.created`
   - Payload: agent_id, referral_type, referral_code, referral_token

2. **ReferralLinkClicked** - Fired when a referral link is clicked/tracked
   - Aggregate Type: `referral_link`
   - Event Type: `referral_link.clicked`
   - Payload: referral_link_id, ip_address, user_agent, referrer_url, session_id

3. **ReferralConverted** - Fired when a referral converts (patient/business enrolled)
   - Aggregate Type: `referral_link`
   - Event Type: `referral_link.converted`
   - Payload: referral_link_id, converted_entity_id, converted_entity_type

### CQRS Pattern

The system implements CQRS with separate read and write models:

**Write Model (Commands):**
- `TrackReferralClick` - Records a click on a referral link
- `RecordReferralConversion` - Records a conversion for a referral link

**Read Model (Queries):**
- `GetReferralNetworkHierarchy` - Retrieves complete referral network hierarchy
- `GetAgentReferralPerformance` - Retrieves referral performance metrics

## Data Models

### ReferralLink Model

Tracks referral links created by agents with performance metrics.

**Attributes:**
- `agent_id` - The agent who created the link
- `referral_type` - Type of referral (patient, agent, business)
- `referral_code` - Unique code for tracking (e.g., "ABC12345")
- `referral_token` - UUID token for secure tracking
- `clicks_count` - Total number of clicks on this link
- `conversions_count` - Total number of conversions
- `conversion_rate` - Calculated percentage (conversions/clicks * 100)
- `status` - Link status (active, inactive, archived)

**Methods:**
- `recordClick()` - Increment click count and update conversion rate
- `recordConversion()` - Increment conversion count and update conversion rate
- `updateConversionRate()` - Recalculate conversion rate

**Scopes:**
- `forAgent($agentId)` - Filter by agent
- `byType($type)` - Filter by referral type
- `active()` - Filter active links only

### ReferralClick Model

Tracks individual clicks on referral links for detailed analytics.

**Attributes:**
- `referral_link_id` - Reference to the referral link
- `ip_address` - IP address of the clicker
- `user_agent` - Browser/device information
- `referrer_url` - HTTP referrer URL
- `session_id` - Session identifier
- `converted` - Boolean flag if this click converted
- `converted_at` - Timestamp of conversion

**Methods:**
- `markAsConverted()` - Mark click as converted

**Scopes:**
- `forReferralLink($linkId)` - Filter by referral link
- `converted()` - Filter converted clicks
- `unconverted()` - Filter unconverted clicks

## API Endpoints

### Referral Tracking

**Track a Referral Click**
```
POST /referral/track?ref={referral_code}
```
Tracks a click on a referral link. Returns the referral link details.

**Record a Conversion**
```
POST /referral/convert
Body: {
  "referral_code": "ABC12345",
  "converted_entity_id": 123,
  "converted_entity_type": "patient"
}
```
Records a conversion for a referral link.

**Get Referral Link Details**
```
GET /referral/{referral_code}
```
Retrieves details about a specific referral link.

### Referral Network

**Get Referral Network Hierarchy**
```
GET /agent/{agentId}/referral-network/hierarchy?depth=3
```
Retrieves the complete referral network hierarchy for an agent with optional depth limit.

Response includes:
- Agent information (id, uuid, name, tier, status, email)
- Referral links count and performance metrics
- Downline agents (children) with recursive structure

**Get Referral Performance Metrics**
```
GET /agent/{agentId}/referral-network/performance?period=month
```
Retrieves referral performance metrics for an agent.

Query Parameters:
- `period` - Time period (week, month, quarter, year)

Response includes:
- Total referral links, clicks, conversions
- Overall conversion rate
- Performance breakdown by referral type

## Database Schema

### referral_links Table

```sql
CREATE TABLE referral_links (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  agent_id BIGINT NOT NULL,
  referral_type VARCHAR(50) NOT NULL,
  referral_code VARCHAR(20) UNIQUE NOT NULL,
  referral_token UUID UNIQUE NOT NULL,
  clicks_count INT DEFAULT 0,
  conversions_count INT DEFAULT 0,
  conversion_rate FLOAT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (agent_id) REFERENCES agents(id),
  INDEX (agent_id),
  INDEX (referral_type),
  INDEX (status),
  INDEX (created_at)
);
```

### referral_clicks Table

```sql
CREATE TABLE referral_clicks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  referral_link_id BIGINT NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  referrer_url TEXT,
  session_id VARCHAR(255),
  converted BOOLEAN DEFAULT FALSE,
  converted_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  FOREIGN KEY (referral_link_id) REFERENCES referral_links(id),
  INDEX (referral_link_id),
  INDEX (converted),
  INDEX (created_at)
);
```

## Testing

The referral system includes comprehensive test coverage:

**ReferralTrackingTest.php** (4 tests)
- Tracks a referral link click
- Records a referral conversion
- Calculates conversion rate correctly
- Retrieves referral link details

**ReferralNetworkTest.php** (4 tests)
- Retrieves referral network hierarchy
- Retrieves referral performance metrics
- Builds hierarchy with downline agents
- Calculates average conversion rate

Run tests:
```bash
php artisan test tests/Feature/ReferralTrackingTest.php tests/Feature/ReferralNetworkTest.php
```

## Integration with Commission System

The referral system integrates with the commission system through:

1. **Referral Link Creation** - When an agent is onboarded, referral links are created
2. **Conversion Tracking** - When a referral converts to a patient/business, commission calculations are triggered
3. **Network Hierarchy** - The referral network structure determines commission distribution

## Future Enhancements

- Vue.js dashboard component for referral network visualization
- Real-time performance metrics updates via WebSocket
- Referral link analytics with geographic and demographic breakdowns
- Automated referral link generation for different marketing channels
- Referral performance leaderboards and gamification
- Bulk referral link generation and management

