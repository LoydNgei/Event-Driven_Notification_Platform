<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Slack = 'slack';
}
