<?php

declare(strict_types=1);

namespace WishgranterProject\DescriptivePlaylist\Tests;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use WishgranterProject\DescriptivePlaylist\Playlist;

final class PlaylistGetTest extends Base
{
    public function testGetSingleItem()
    {
        $playlist = new Playlist('tests/template.dpls');

        $item = $playlist->getItem(8);

        $this->assertEquals('Over the hills and far away', $item->title);
    }

    public function testGetMultipleItems()
    {
        $playlist = new Playlist('tests/template.dpls');

        $items = $playlist->getItems([0, 2]);

        $this->assertEquals('Lazy Day Blues', $items[0]->title);
        $this->assertEquals('Nigraj kandeloj dancas', $items[2]->title);
    }

    public function testGetNonExistingPosition()
    {
        $playlist = new Playlist('tests/template.dpls');

        $item = $playlist->getItem(50);
        $this->assertEquals(null, $item);
    }

    public function testGetBlankLine()
    {
        $playlist = new Playlist('tests/template.dpls');

        $item = $playlist->getItem(6);

        $this->assertEquals(null, $item);
    }

    public function testGetLastItem()
    {
        $playlist = new Playlist('tests/template.dpls');

        $lastItem = $playlist->getLastItem();

        $this->assertEquals('Over the hills and far away', $lastItem->title);
    }

    public function testGetItemByUuid()
    {
        $playlist = new Playlist('tests/template.dpls');

        $item = $playlist->getItemByUuid('008540f5-cf34-41ec-8b3f-9e1639695370');

        $this->assertEquals('Let\'s go sunning', $item->title);
    }
}
