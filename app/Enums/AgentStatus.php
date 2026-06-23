<?php

namespace App\Enums;

enum AgentStatus: string
{
    case Active         = 'active';
    case Disconnected   = 'disconnected';
    case Pending        = 'pending';
    case NeverConnected = 'never_connected';
    case Master         = '000';
}
