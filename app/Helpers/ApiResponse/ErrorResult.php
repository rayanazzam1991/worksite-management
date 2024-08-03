<?php

namespace App\Helpers\ApiResponse;

class ErrorResult extends Result
{
    public function __construct(?string $message, int $code = 500, bool $isOk = false)
    {
        parent::__construct();
        $this->isOk = $isOk;
        $this->message = $message ?? __('messages.task_does_not_complete_successfully');
        $this->code = $code;

    }
}
