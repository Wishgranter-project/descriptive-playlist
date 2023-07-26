<?php 
declare(strict_types=1);

namespace AdinanCenci\DescriptivePlaylist\Tests;

use AdinanCenci\DescriptivePlaylist\PlaylistItem;
use AdinanCenci\DescriptivePlaylist\Playlist;

final class PlaylistSetTest extends Base
{
    public function testUpdateExistingItem() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $fifth = $playlist->getItem(5);
        $fifth->title = 'Nightfall ( updated )';
        $playlist->setItem($fifth);

        $updatedItem = $playlist->getItem(5);
        $this->assertEquals('Nightfall ( updated )', $updatedItem->title);
    }

    public function testMovingExisitingItem() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $fifth = $playlist->getItem(5);
        $fifth->title = 'Nightfall ( updated )';
        $playlist->setItem($fifth, 0);

        $newFirstItem = $playlist->getItem(0);
        $this->assertEquals('Nightfall ( updated )', $newFirstItem->title);
    }

    public function testSetNewItem() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $new = new PlaylistItem();
        $new->title = 'Jungle Drums';
        $new->soundtrack = 'Half Life';

        $playlist->setItem($new);

        $last = $playlist->getItem(9);
        $this->assertEquals('Jungle Drums', $last->title);
    }

    public function testSetNewItemAndInformItsPosition() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $new = new PlaylistItem();
        $new->title = 'Jungle Drums';
        $new->soundtrack = 'Half Life';

        $playlist->setItem($new, 2);

        $third = $playlist->getItem(2);
        $this->assertEquals('Jungle Drums', $new->title);
    }
}
