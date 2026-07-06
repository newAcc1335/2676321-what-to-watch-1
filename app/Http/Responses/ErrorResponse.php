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
        $payload = ['message' => $this->message];

        $errors = $this->toArray();

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return $payload;
    }
}
