<?php


namespace App\Support\Enums;

enum TenantStatus: string
{
    case Active    = 'active';
    case Trial     = 'trial';
    case Suspended = 'suspended';
}
