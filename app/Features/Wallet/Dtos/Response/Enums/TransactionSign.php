<?php

namespace App\Features\Wallet\Dtos\Response\Enums;

enum TransactionSign: string
{
    case Plus = '+';
    case Minus = '-';
}
