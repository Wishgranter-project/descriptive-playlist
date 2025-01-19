<?php

declare(strict_types=1);

namespace WishgranterProject\DescriptivePlaylist\Tests;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use WishgranterProject\DescriptivePlaylist\Playlist;

final class PlaylistSearchTest extends Base
{
    public function testGetMultipleItemsByUuid()
    {
        $playlist = new Playlist('tests/template.dpls');

        $items = $playlist->getItemsByUuid([
            'ff81ea29-faa3-4523-8ddf-0da011a2a486',
            '3bf9561a-a198-4ad7-9718-856a22a77e83'
        ]);

        $this->assertEquals('Lazy Day Blues', $items[0]->title);
        $this->assertEquals('Nigraj kandeloj dancas', $items[2]->title);
    }

    public function testGetSingleItemByUuid()
    {
        $playlist = new Playlist('tests/template.dpls');

        $item = $playlist->getItemByUuid('0aa35c66-1037-4020-bfb5-735750093474');

        $this->assertEquals('Over the hills and far away', $item->title);
    }

    public function testSearchItemsByArtist()
    {
        $playlist = new Playlist('tests/template.dpls');

        $search = $playlist->search();
        $search->condition('artist', 'Blind Guardian', '=');
        $items = $search->find();

        $this->assertEquals('The Bard\'s Song The Hobbit', $items[4]->title);
        $this->assertEquals('Nightfall', $items[5]->title);
    }

    public function testSearchItemsByImcompleteTerm()
    {
        $playlist = new Playlist('tests/template.dpls');

        $search = $playlist->search();
        $search->condition('title', ['Blues', 'kandeloj'], 'LIKE');
        $items = $search->find();

        $this->assertEquals('Lazy Day Blues', $items[0]->title);
        $this->assertEquals('Nigraj kandeloj dancas', $items[2]->title);
    }

    public function testSearchOrderResults()
    {
        $playlist = new Playlist('tests/template.dpls');
        $search = $playlist->search();
        $search->orderBy('title', 'ASC');
        $items = $search->find();

        $first = reset($items);
        $last = end($items);

        $this->assertEquals('If I could Fly', $first->title);
        $this->assertEquals('The Bard\'s Song The Hobbit', $last->title);
    }
}
