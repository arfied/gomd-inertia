<?php

namespace App\Enums;

enum ReferralType: string
{
    case Patient = 'patient';
    case Agent = 'agent';
    case Business = 'business';
}
