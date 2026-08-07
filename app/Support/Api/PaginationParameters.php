<?php

namespace App\Support\Api;

use Illuminate\Http\Request;

final class PaginationParameters
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search,
        public readonly ?string $sort,
        public readonly string $direction,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $defaultPerPage = config('examflow.pagination.default_per_page', 25);
        $maxPerPage = config('examflow.pagination.max_per_page', 100);

        $perPage = min(
            max((int) $request->query('per_page', $defaultPerPage), 1),
            $maxPerPage,
        );

        $direction = strtolower((string) $request->query('direction', 'asc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return new self(
            page: max((int) $request->query('page', 1), 1),
            perPage: $perPage,
            search: $request->query('search') ? trim((string) $request->query('search')) : null,
            sort: $request->query('sort') ? trim((string) $request->query('sort')) : null,
            direction: $direction,
        );
    }
}
