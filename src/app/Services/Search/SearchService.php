<?php

namespace App\Services\Search;

use App\Models\User\User;
use App\Repositories\MongoDB\Search\SearchRepositoryInterface;
use App\Services\BaseService;

class SearchService extends BaseService implements SearchServiceInterface
{

    public function __construct(private readonly SearchRepositoryInterface $searchRepository){}

    public function searchList(array $data)
    {

        $this->validate($data, [
            'search'    => '',
//            'type'      => 'in:' . User::TAGS_SEARCH .',' . User::NAME_SEARCH . ',' . User::NICKNAME_SEARCH,
        ]);

//        if (!isset($data['type'])) $data['type'] = User::NAME_SEARCH;

        return $this->searchRepository->searchList($data);

    }

    public function searchOnMap(array $data)
    {
        $this->validate($data, [
            'search'    => '',
//            'type'      => 'in:' . User::TAGS_SEARCH .',' . User::NAME_SEARCH . ',' . User::NICKNAME_SEARCH,

            'coordinates'          => 'required|array|min:1|max:1',
            'coordinates.*'        => 'required|array|min:4|max:5',
        ]);

        return $this->searchRepository->searchOnMap($data);
    }
}
