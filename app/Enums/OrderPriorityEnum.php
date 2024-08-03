<?php

namespace App\Enums;

enum OrderPriorityEnum: int
{
    case LOW = 1;
    case NORMAL = 2;
    case HIGH = 3;
    case URGENT = 4;

}
