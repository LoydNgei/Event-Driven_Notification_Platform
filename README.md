# Event-Driven Notification Platform

A Laravel backend service that sends notifications through email, SMS, and Slack when events are triggered. Built to demonstrate event-driven architecture with queues and asynchronous processing.

## What It Does

You define event sources (like `order_created` or `user_signup`), create notification templates, and set up rules that link events to templates. When an external service calls the API to trigger an event, the system processes it asynchronously through queues and sends notifications based on your configured rules.

## Features

- Event-driven architecture using Laravel Events and Listeners
- Queue-based processing with automatic retries
- Multi-channel notifications (Email, SMS, Slack)
- Template system with variable interpolation
- Configurable rules with conditions
- Dashboard for monitoring and testing

## Quick Start

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
composer dev
```

Visit `http://localhost:8000/dashboard` to access the dashboard.

## System Architecture

```
┌─────────────────┐    ┌─────────────────────────────────────────────────┐
│  External API   │    │              Laravel Application                │
│    Clients      │    │                                                 │
│                 │    │  ┌─────────────┐   ┌─────────────────────────┐  │
│  POST /api/     │───▶│  │   API       │──▶│    EventTriggered       │  │
│     events      │    │  │ Controller  │   │    (Laravel Event)      │  │
│                 │    │  └─────────────┘   └───────────┬─────────────┘  │
└─────────────────┘    │                                │                │
                       │                                ▼                │
                       │                    ┌─────────────────────────┐  │
                       │                    │  ProcessEventNotifications │
                       │                    │      (Listener)          │  │
                       │                    └───────────┬─────────────┘  │
                       │                                │                │
                       │                                ▼                │
                       │                    ┌─────────────────────────┐  │
                       │                    │   SendNotificationJob   │  │
                       │                    │      (Queued Job)       │  │
                       │                    └───────────┬─────────────┘  │
                       └────────────────────────────────┼────────────────┘
                                                        │
                       ┌────────────────────────────────┼────────────────┐
                       │           Queue / Message Broker                │
                       │         (Redis / Database)                      │
                       └────────────────────────────────┼────────────────┘
                                                        │
              ┌─────────────────────────────────────────┼───────────────────┐
              │                                         │                   │
              ▼                                         ▼                   ▼
    ┌─────────────────┐                     ┌─────────────────┐   ┌─────────────────┐
    │  Email Channel  │                     │   SMS Channel   │   │  Slack Channel  │
    │  (Mailgun/SES)  │                     │ (Twilio/Vonage) │   │   (Webhook)     │
    └─────────────────┘                     └─────────────────┘   └─────────────────┘
```

### Data Flow Sequence

```
1. External service calls POST /api/events
   └── {"event": "order_created", "payload": {"order_id": 444}}

2. EventTriggerController validates and finds EventSource
   └── Dispatches EventTriggered event

3. ProcessEventNotifications listener handles the event
   ├── Finds all active rules matching the event
   ├── Filters by conditions (payload matching)
   ├── Creates NotificationLog entries (status: pending)
   └── Dispatches SendNotificationJob for each

4. Queue worker processes SendNotificationJob
   ├── Renders template with payload variables
   ├── Gets appropriate channel handler
   ├── Sends notification
   └── Updates log status (sent/failed)

5. On failure: Job retries with exponential backoff
   └── Retry intervals: 1 min → 5 min → 15 min
```

## API Endpoints

**Trigger an event:**
```bash
POST /api/events
{
  "event": "order_created",
  "payload": {
    "order_id": 444,
    "customer_email": "john@example.com",
    "total": 99.99
  }
}
```

## Configuration

Set up your notification channels in `.env`:

```env
# Queue
QUEUE_CONNECTION=database

# Email
MAIL_MAILER=log
MAIL_FROM_ADDRESS="notifications@example.com"

# Slack (optional)
SLACK_DEFAULT_WEBHOOK=https://hooks.slack.com/services/...

# Notification Settings
NOTIFICATION_MAX_ATTEMPTS=3
```

For production, configure real email providers (Mailgun, SES, SMTP) and SMS services (Twilio) in the channel service classes.

## Project Structure

```
app/
├── Events/
│   └── EventTriggered.php
├── Http/Controllers/
│   ├── Api/
│   │   ├── EventSourceController.php
│   │   ├── EventTriggerController.php
│   │   ├── NotificationLogController.php
│   │   ├── NotificationRuleController.php
│   │   └── NotificationTemplateController.php
│   └── DashboardController.php
├── Jobs/
│   └── SendNotificationJob.php
├── Listeners/
│   └── ProcessEventNotifications.php
├── Models/
│   ├── EventSource.php
│   ├── NotificationLog.php
│   ├── NotificationRule.php
│   └── NotificationTemplate.php
└── Services/
    ├── ChannelManager.php
    └── Channels/
        ├── EmailChannel.php
        ├── NotificationChannelInterface.php
        ├── SlackChannel.php
        └── SmsChannel.php
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=EventTriggerApiTest
```

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL

## License

MIT License
