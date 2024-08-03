<?php

namespace App\Enums;

enum WorkSiteCompletionStatusEnum: int
{
    case PENDING = 1;
    case ASSIGNED = 2;
    case STARTED = 3;
    case COMPLETED = 4;
    case CLOSED = 5;

}
