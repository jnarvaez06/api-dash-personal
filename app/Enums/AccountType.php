<?php

namespace App\Enums;

enum AccountType: string
{
    case BANK_ACCOUNT = 'bank_account';
    case CASH = 'cash';
    case DIGITAL_WALLET = 'digital_wallet';
    case CREDIT_CARD = 'credit_card';
    case INVESTMENT = 'investment';
    case OTHER = 'other';
}