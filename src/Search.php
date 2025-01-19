<?php

namespace WishgranterProject\DescriptivePlaylist;

use AdinanCenci\JsonLines\JsonLines;
use AdinanCenci\JsonLines\Search\Search as JsonLinesSearch;

class Search
{
    /**
     * @var AdinanCenci\JsonLines\JsonLines
     *   Object to read the json lines file.
     */
    protected JsonLines $jsonLines;

    /**
     * @var AdinanCenci\JsonLines\Search\Search
     *   Object to query the file.
     */
    protected JsonLinesSearch $search;

    /**
     * Constructor.
     *
     * @param AdinanCenci\JsonLines\JsonLines
     *   Object to read the json lines file.
     * @param string $operator
     *   The logic operator: "AND" or "OR".
     */
    public function __construct(JsonLines $jsonLines, string $operator = 'AND')
    {
        $this->jsonLines = $jsonLines;
        $this->search = $jsonLines->search($operator);
    }

    /**
     * Executes the search and returns the ordered results.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem[]
     *   The playlist items that match our criteria, indexed by their
     *   position in the file.
     */
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

    /**
     * Add a new condition to this group.
     *
     * @param string[] $propertyPath
     *   A path to extract the actual value during evaluation.
     * @param mixed $valueToCompare
     *   The value for comparison.
     * @param string $operator
     *   The operator.
     *
     * @return self
     *   Returns self to chain in other methods.
     */
    public function condition($property, $valueToCompare, string $operatorId = '='): self
    {
        $this->search->condition($property, $valueToCompare, $operatorId);
        return $this;
    }

    /**
     * Adds a new condition group ( nested inside this one ).
     *
     * @return AdinanCenci\FileEditor\Search\Condition\ConditionGroupInterface
     *   Returns the new condition group.
     */
    public function andConditionGroup()
    {
        return $this->search->andConditionGroup();
    }

    /**
     * Adds a new condition group ( nested inside this one ).
     *
     * @return AdinanCenci\FileEditor\Search\Condition\ConditionGroupInterface
     *   Returns the new condition group.
     */
    public function orConditionGroup()
    {
        return $this->search->orConditionGroup();
    }

    /**
     * Adds a new criteria to order the results by.
     *
     * @param array|string $property
     *   The property to order by.
     * @param string $direction
     *   Ascending or descending.
     *
     * @return WishgranterProject\DescriptivePlaylist\Search
     *   Returns itself.
     */
    public function orderBy(mixed $property, string $direction = 'ASC'): Search
    {
        $this->search->orderBy($property, $direction);
        return $this;
    }
}
