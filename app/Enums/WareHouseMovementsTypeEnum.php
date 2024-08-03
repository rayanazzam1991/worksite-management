<?php

namespace App\Enums;

enum WareHouseMovementsTypeEnum: int
{
    case ADD_STOCK = 1;
    case DROP_STOCK = 2;
    case ADJUST_STOCK = 3;

}
