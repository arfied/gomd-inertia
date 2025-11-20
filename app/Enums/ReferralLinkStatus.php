<?php

namespace App\Enums;

enum ReferralLinkStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
