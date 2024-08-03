<?php

namespace App\Enums;

enum WareHouseItemStatusEnum: int
{
    case IN_STOCK = 1;
    case LOW_STOCK = 2;
    case OFF_STOCK = 3;

}
