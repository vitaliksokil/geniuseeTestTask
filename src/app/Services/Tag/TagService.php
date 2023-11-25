<?php

namespace App\Services\Tag;

use App\Repositories\MongoDB\Tag\TagRepositoryInterface;
use App\Services\BaseService;

class TagService extends BaseService implements TagServiceInterface
{

    public function __construct(private readonly TagRepositoryInterface $repository){}

    public function search(array $params)
    {
        $this->validate($params, [
            'title'         => 'nullable|string',
            'exclude_ids'   => 'nullable|string'
        ]);

        return $this->repository->search($params);

    }
}
