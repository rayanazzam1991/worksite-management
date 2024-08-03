<?php

namespace App\Enums;

enum PaymentTypesEnum: int
{
    case CASH = 1;
    case BANK = 2;

    case CHECK = 3;

    case CARD = 4;

}
