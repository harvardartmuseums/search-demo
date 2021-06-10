<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BrowseService
{
    public function search($filters, $offset, $limit = 1, $orderBy = 'rank', $direction = 'asc')
    {
        $result = null;
        //Try exact match with gallery number
        if ($filters['q'] && $filters['onview']) {
            // Log::info("Inside block 1");
            $result = \HamObject::limit($limit)
            ->group($filters['group'])
            ->from($offset)
            ->gallery($filters['q'])
            ->classification($filters['classification'])
            ->technique($filters['technique'])
            ->medium($filters['medium'])
            ->place($filters['place'])
            ->worktype($filters['worktype'])
            ->culture($filters['culture'])
            ->century($filters['century'])
            ->period($filters['period'])
            ->onview($filters['onview'])
            ->keyword($filters['q'])
            ->custom($filters['custom'])
            ->sortorder($direction)
            ->sort($orderBy)
            ->findCount();
        }

        if ($filters['q'] && ! $filters['onview']) {
            // Log::info("Inside block 2");
            $result = \HamObject::limit($limit)
            ->group($filters['group'])
            ->from($offset)
            ->gallery($filters['q'])
            ->classification($filters['classification'])
            ->technique($filters['technique'])
            ->medium($filters['medium'])
            ->place($filters['place'])
            ->worktype($filters['worktype'])
            ->culture($filters['culture'])
            ->century($filters['century'])
            ->period($filters['period'])
            ->onview($filters['onview'])
            ->custom($filters['custom'])
            ->sortorder($direction)
            ->sort($orderBy)
            ->findCount();
        }

        //Try exact match with objectnumber
        if ($filters['q'] && (! $result || ! $result->info->totalrecords)) {
            // Log::info("Inside block 3");
            $result = \HamObject::limit($limit)
            ->group($filters['group'])
            ->from($offset)
            ->objectnumber($filters['q'])
            ->classification($filters['classification'])
            ->technique($filters['technique'])
            ->medium($filters['medium'])
            ->place($filters['place'])
            ->worktype($filters['worktype'])
            ->culture($filters['culture'])
            ->century($filters['century'])
            ->period($filters['period'])
            ->gallery($filters['gallery'])
            ->onview($filters['onview'])
            ->custom($filters['custom'])
            ->sortorder($direction)
            ->sort($orderBy)
            ->findCount();
        }

        if (! $result || ! $result->info->totalrecords) {
            // Log::info("Inside block 4");
            $result = \HamObject::limit($limit)
            ->group($filters['group'])
            ->from($offset)
            ->classification($filters['classification'])
            ->technique($filters['technique'])
            ->medium($filters['medium'])
            ->place($filters['place'])
            ->worktype($filters['worktype'])
            ->culture($filters['culture'])
            ->century($filters['century'])
            ->person($filters['person'])
            ->period($filters['period'])
            ->gallery($filters['gallery'])
            ->onview($filters['onview'])
            ->custom($filters['custom'])
            ->keyword($filters['q'])
            ->sortorder($direction)
            ->sort($orderBy)
            ->findCount();
        }

        return $result;
    }
}
