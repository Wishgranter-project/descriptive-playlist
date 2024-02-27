<?php

declare(strict_types=1);

namespace WishgranterProject\DescriptivePlaylist\Tests;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use WishgranterProject\DescriptivePlaylist\Playlist;

final class PlaylistDeleteTest extends Base
{
    public function testDeleteItem()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $third = $playlist->getItem(2);
        $this->assertTrue($playlist->deleteItem($third));

        $newThird = $playlist->getItem(2);
        $this->assertEquals($newThird->title, 'If I could Fly');
    }

    public function testDeletePosition()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);
        $this->assertTrue($playlist->deletePosition(2, $oldThird));
        $this->assertEquals($oldThird->title, 'Nigraj kandeloj dancas');

        $newThird = $playlist->getItem(2);
        $this->assertEquals($newThird->title, 'If I could Fly');
    }
}
