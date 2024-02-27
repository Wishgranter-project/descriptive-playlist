<?php

namespace WishgranterProject\DescriptivePlaylist;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use AdinanCenci\JsonLines\JsonLinesIterator;

class PlaylistIterator implements \Iterator
{
    protected JsonLinesIterator $objects;
    protected int $currentLine = 0;

    public function __construct(JsonLinesIterator $objects)
    {
        $this->objects = $objects;
    }

    public function current()
    {
        $object = $this->objects->current();
        return $object instanceof \stdClass
            ? new PlaylistItem($object)
            : null;
    }

    public function key()
    {
        return $this->currentLine;
    }

    public function next(): void
    {
        $this->currentLine++;
        $object = $this->objects->next();
    }

    public function rewind(): void
    {
        $this->currentLine = 0;
        $this->objects->rewind();
        $this->objects->next();
    }

    public function valid(): bool
    {
        return $this->objects->valid();
    }
}
