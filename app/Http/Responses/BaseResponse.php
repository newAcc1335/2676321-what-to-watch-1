<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseResponse implements Responsable
{
    public function __construct(
        protected mixed $data = [],
        public int $statusCode = Response::HTTP_OK,
    ) {
    }

    /**
     * Формирует JSON-ответ для клиента.
     */
    #[\Override]
    public function toResponse($request): JsonResponse
    {
        return response()->json($this->payload(), $this->statusCode);
    }

    /**
     * Формирует содержимое ответа.
     */
    abstract protected function payload(): ?array;

    /**
     * Преобразует данные к массиву.
     */
    protected function toArray(): array
    {
        if ($this->data instanceof Arrayable) {
            return $this->data->toArray();
        }

        if (is_array($this->data)) {
            return $this->data;
        }

        return [];
    }
}
