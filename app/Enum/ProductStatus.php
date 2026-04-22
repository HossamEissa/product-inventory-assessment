<?php

namespace App\Enum;

enum ProductStatus : string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DISCONNECTED = 'disconnected';
}
