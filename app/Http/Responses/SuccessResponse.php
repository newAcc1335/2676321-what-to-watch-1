<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        if ($this->data instanceof LengthAwarePaginator) {
            return $this->paginatedPayload($this->data);
        }

        return [
            'data' => $this->toArray(),
        ];
    }

    /**
     * Формирует ответ для постраничного списка
     */
    private function paginatedPayload(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'first_page_url' => $paginator->url(1),
            'last_page_url' => $paginator->url($paginator->lastPage()),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
