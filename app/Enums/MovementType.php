<?php

namespace App\Enums;

enum MovementType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case INVESTMENT = 'investment';
}