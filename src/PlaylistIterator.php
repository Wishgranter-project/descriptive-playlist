<?php

namespace WishgranterProject\DescriptivePlaylist;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use AdinanCenci\JsonLines\JsonLinesIterator;

class PlaylistIterator implements \Iterator
{
    /**
     * @var AdinanCenci\JsonLines\JsonLinesIterator
     *   Object to read the json lines file.
     */
    protected JsonLinesIterator $objects;

    /**
     * @var int
     *   To keep track as we progress during the iteration.
     */
    protected int $currentLine = 0;

    /**
     * @param AdinanCenci\JsonLines\JsonLinesIterator $objects
     *   Object to read the json lines file.
     */
    public function __construct(JsonLinesIterator $objects)
    {
        $this->objects = $objects;
    }

    /**
     * Iterator::current().
     */
    public function current()
    {
        $object = $this->objects->current();
        return $object instanceof \stdClass
            ? new PlaylistItem($object)
            : null;
    }

    /**
     * Iterator::key().
     */
    public function key()
    {
        return $this->currentLine;
    }

    /**
     * Iterator::next().
     */
    public function next(): void
    {
        $this->currentLine++;
        $object = $this->objects->next();
    }

    /**
     * Iterator::rewind().
     */
    public function rewind(): void
    {
        $this->currentLine = 0;
        $this->objects->rewind();
        $this->objects->next();
    }

    /**
     * Iterator::valid().
     */
    public function valid(): bool
    {
        return $this->objects->valid();
    }
}
