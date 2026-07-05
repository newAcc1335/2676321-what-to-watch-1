<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Response;

class SuccessResponse extends BaseResponse
{
    public function __construct(
        mixed $data = [],
        int $statusCode = Response::HTTP_OK,
    ) {
        parent::__construct($data, $statusCode);
    }

    /**
     * Формируем ответ согласно формату API.
     */
    #[\Override]
    protected function payload(): ?array
    {
        return [
            'data' => $this->toArray(),
        ];
    }
}
