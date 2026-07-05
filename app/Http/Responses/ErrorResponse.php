<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Response;

class ErrorResponse extends BaseResponse
{
    public function __construct(
        protected ?string $message = null,
        array $errors = [],
        int $statusCode = Response::HTTP_BAD_REQUEST,
    ) {
        parent::__construct($errors, $statusCode);
    }

    /**
     * Возвращает сообщение об ошибке и список ошибок.
     */
    #[\Override]
    protected function payload(): ?array
    {
        return [
            'message' => $this->message,
            'errors' => $this->toArray(),
        ];
    }
}
