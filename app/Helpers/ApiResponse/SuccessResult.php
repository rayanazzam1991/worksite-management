<?php

namespace App\Helpers\ApiResponse;

class SuccessResult extends Result
{
    public function __construct(?string $message, bool $isOk = true)
    {
        parent::__construct();
        $this->isOk = $isOk;
        $this->message = $message ?? __('messages.task_completed_successfully');

    }
}
