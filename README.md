# Event-Driven Notification Platform

A Laravel-based backend service that sends notifications (email, SMS, Slack) triggered by user-defined events. Built to demonstrate event-driven architecture, queues, and microservice thinking.

## 🎯 Goal

Show system design maturity with:
- Event-driven architecture using Laravel Events + Listeners
- Queue-based asynchronous processing with retries
- Multi-channel notification delivery
- Clean, testable API-first design

## ⚙️ Core Features

- **User-defined event sources** (e.g., `order_created`, `user_signup`)
- **Event trigger API endpoint** for external services
- **Multi-channel notifications**: Email, SMS, Slack
- **Template system** with variable interpolation (`{{order_id}}`)
- **Configurable rules** per event with conditions
- **Automatic retries** with exponential backoff
- **Dashboard UI** for monitoring and testing

---

## 🏗️ System Architecture

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

---

## 📊 Database Schema

```
┌──────────────────┐       ┌────────────────────────┐       ┌──────────────────────┐
│   event_sources  │       │   notification_rules   │       │notification_templates│
├──────────────────┤       ├────────────────────────┤       ├──────────────────────┤
│ id               │◀──────│ event_source_id        │       │ id                   │
│ user_id (FK)     │       │ notification_template_id│◀─────│ user_id (FK)         │
│ name (unique)    │       │ channel                │       │ name                 │
│ description      │       │ conditions (JSON)      │       │ channel              │
│ schema (JSON)    │       │ recipient_email        │       │ subject              │
│ is_active        │       │ recipient_phone        │       │ body                 │
│ timestamps       │       │ recipient_slack_webhook│       │ timestamps           │
└──────────────────┘       │ recipient_field        │       └──────────────────────┘
                           │ is_active              │
                           │ timestamps             │
                           └────────────┬───────────┘
                                        │
                                        ▼
                           ┌────────────────────────┐
                           │   notification_logs    │
                           ├────────────────────────┤
                           │ id                     │
                           │ event_source_id        │
                           │ notification_rule_id   │
                           │ channel                │
                           │ payload (JSON)         │
                           │ recipient              │
                           │ status (enum)          │
                           │ error_message          │
                           │ attempts               │
                           │ sent_at                │
                           │ timestamps             │
                           └────────────────────────┘
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm (optional, for frontend assets)
- SQLite (default) or MySQL

### Installation

```bash
# Clone and install
git clone <repository-url>
cd Event-Driven_Notification_Platform
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server (app + queue worker + logs)
composer dev
```

Visit `http://localhost:8000/dashboard` to see the demo UI.

### Docker Setup

```bash
# Start all services (app, queue worker, Redis)
docker-compose up -d

# View logs
docker-compose logs -f app queue
```

Access the app at `http://localhost:8080`

---

## 📡 API Reference

### Event Sources

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/event-sources` | List all event sources |
| POST | `/api/event-sources` | Create event source |
| GET | `/api/event-sources/{id}` | Get event source |
| PUT | `/api/event-sources/{id}` | Update event source |
| DELETE | `/api/event-sources/{id}` | Delete event source |

```bash
# Create an event source
curl -X POST http://localhost:8000/api/event-sources \
  -H "Content-Type: application/json" \
  -d '{"name": "order_created", "description": "When an order is placed"}'
```

### Trigger Events

```bash
# POST /api/events
curl -X POST http://localhost:8000/api/events \
  -H "Content-Type: application/json" \
  -d '{
    "event": "order_created",
    "payload": {
      "order_id": 444,
      "customer_email": "john@example.com",
      "total": 99.99
    }
  }'
```

Response:
```json
{
  "message": "Event triggered successfully.",
  "event": "order_created",
  "payload": {"order_id": 444, "customer_email": "john@example.com", "total": 99.99}
}
```

### Notification Templates

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notification-templates` | List templates |
| POST | `/api/notification-templates` | Create template |
| GET | `/api/notification-templates/{id}` | Get template |
| PUT | `/api/notification-templates/{id}` | Update template |
| DELETE | `/api/notification-templates/{id}` | Delete template |

```bash
# Create an email template
curl -X POST http://localhost:8000/api/notification-templates \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Order Confirmation",
    "channel": "email",
    "subject": "Order #{{order_id}} Confirmed",
    "body": "Thank you for your order! Your order #{{order_id}} for ${{total}} has been confirmed."
  }'
```

### Notification Rules

```bash
# Create a rule linking event to template
curl -X POST http://localhost:8000/api/notification-rules \
  -H "Content-Type: application/json" \
  -d '{
    "event_source_id": 1,
    "notification_template_id": 1,
    "channel": "email",
    "recipient_field": "customer_email",
    "is_active": true
  }'
```

### Notification Logs

```bash
# View notification history
curl "http://localhost:8000/api/notification-logs?status=sent"

# Get specific log details
curl http://localhost:8000/api/notification-logs/1
```

---

## 📁 Project Structure

```
app/
├── Events/
│   └── EventTriggered.php          # Laravel event class
├── Http/Controllers/
│   ├── Api/
│   │   ├── EventSourceController.php
│   │   ├── EventTriggerController.php
│   │   ├── NotificationLogController.php
│   │   ├── NotificationRuleController.php
│   │   └── NotificationTemplateController.php
│   └── DashboardController.php
├── Jobs/
│   └── SendNotificationJob.php     # Queued job with retries
├── Listeners/
│   └── ProcessEventNotifications.php
├── Models/
│   ├── EventSource.php
│   ├── NotificationLog.php
│   ├── NotificationRule.php
│   └── NotificationTemplate.php
└── Services/
    ├── ChannelManager.php          # Channel factory
    └── Channels/
        ├── EmailChannel.php
        ├── NotificationChannelInterface.php
        ├── SlackChannel.php
        └── SmsChannel.php
```

---

## ⚙️ Configuration

### Environment Variables

```env
# Queue Configuration
QUEUE_CONNECTION=database   # Use 'redis' in production

# Email (using log driver for demo)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="notifications@example.com"

# Slack (optional)
SLACK_DEFAULT_WEBHOOK=https://hooks.slack.com/services/...

# Notification Settings
NOTIFICATION_MAX_ATTEMPTS=3
NOTIFICATION_EMAIL_ENABLED=true
NOTIFICATION_SMS_ENABLED=true
NOTIFICATION_SLACK_ENABLED=true
```

### Channel Setup

**Email**: Configure `MAIL_MAILER` to `mailgun`, `ses`, or `smtp` for real emails.

**SMS**: Integrate Twilio in `app/Services/Channels/SmsChannel.php`:
```php
// Uncomment and configure Twilio
$twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
$twilio->messages->create($recipient, ['from' => config('services.twilio.from'), 'body' => $content]);
```

**Slack**: Use webhook URLs in notification rules or set a default in `.env`.

---

## 🐳 Docker & Deployment

### Docker Compose Services

| Service | Port | Description |
|---------|------|-------------|
| app | 8080 | Laravel application (Nginx + PHP-FPM) |
| queue | - | Queue worker processing jobs |
| redis | 6379 | Message broker |
| redis-commander | 8081 | Redis web UI (dev profile only) |

### Production with Redis

```bash
# Start with Redis Commander for debugging
docker-compose --profile dev up -d
```

### AWS ECS Deployment

1. **Build and push image**:
   ```bash
   docker build -t notification-platform .
   docker tag notification-platform:latest <account>.dkr.ecr.<region>.amazonaws.com/notification-platform:latest
   docker push <account>.dkr.ecr.<region>.amazonaws.com/notification-platform:latest
   ```

2. **ECS Task Definition**: Create two services:
   - **Web Service**: Run the app container
   - **Worker Service**: Override command to `php artisan queue:work`

3. **Services**:
   - **Redis**: Use ElastiCache
   - **Database**: Use RDS MySQL or Aurora
   - **Email**: Use Amazon SES

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=EventTriggerApiTest
```

### Manual Testing

```bash
# 1. Create event source
curl -X POST http://localhost:8000/api/event-sources \
  -H "Content-Type: application/json" \
  -d '{"name": "test_event", "description": "Test"}'

# 2. Create template
curl -X POST http://localhost:8000/api/notification-templates \
  -H "Content-Type: application/json" \
  -d '{"name": "Test", "channel": "email", "subject": "Test {{id}}", "body": "Message: {{id}}"}'

# 3. Create rule
curl -X POST http://localhost:8000/api/notification-rules \
  -H "Content-Type: application/json" \
  -d '{"event_source_id": 1, "notification_template_id": 1, "channel": "email", "recipient_email": "test@example.com"}'

# 4. Trigger event
curl -X POST http://localhost:8000/api/events \
  -H "Content-Type: application/json" \
  -d '{"event": "test_event", "payload": {"id": 123}}'

# 5. Check logs
curl http://localhost:8000/api/notification-logs
```

---

## 🔮 Future Enhancements

- [ ] **WebSocket real-time updates** for dashboard
- [ ] **DynamoDB** for event logs (multi-database demo)
- [ ] **S3 template storage** for large templates
- [ ] **Rate limiting** per channel/user
- [ ] **Authentication** with Laravel Sanctum
- [ ] **Webhook callbacks** for notification status

---

## 📄 License

MIT License - see LICENSE file for details.
