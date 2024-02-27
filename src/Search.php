<?php

namespace WishgranterProject\DescriptivePlaylist;

use AdinanCenci\JsonLines\JsonLines;
use AdinanCenci\JsonLines\Search\Search as JsonLinesSearch;

class Search
{
    protected JsonLines $jsonLines;
    protected JsonLinesSearch $search;

    public function __construct(JsonLines $jsonLines, string $operator = 'AND')
    {
        $this->jsonLines = $jsonLines;
        $this->search = $jsonLines->search($operator);
    }

    public function find(): array
    {
        $items = [];
        $objects = $this->search->find();

        foreach ($objects as $line => $object) {
            if ($line == 0 || !$object) {
                continue;
            }
            $index = $line - 1;
            $items[ $index ] = new PlaylistItem($object);
        }

        return $items;
    }

    public function condition($property, $valueToCompare, string $operatorId = '='): self
    {
        $this->search->condition($property, $valueToCompare, $operatorId);
        return $this;
    }

    public function andConditionGroup()
    {
        return $this->search->andConditionGroup();
    }

    public function orConditionGroup()
    {
        return $this->search->orConditionGroup();
    }
}
