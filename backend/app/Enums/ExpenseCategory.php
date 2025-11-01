<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case WATER = 'eau';
    case ELECTRICITY = 'electricite';
    case MAINTENANCE = 'entretien';
    case OTHER = 'autre';
}
