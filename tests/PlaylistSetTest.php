<?php 
declare(strict_types=1);

namespace AdinanCenci\DescriptivePlaylist\Tests;

use AdinanCenci\DescriptivePlaylist\PlaylistItem;
use AdinanCenci\DescriptivePlaylist\Playlist;

final class PlaylistSetTest extends Base
{
    public function testMoveExisitingItem() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $fifth = $playlist->getItem(5);
        $fifth->title = 'Nightfall ( updated )';
        $playlist->setItem($fifth, 0);

        $newFirstItem = $playlist->getItem(0);
        $this->assertEquals($fifth->title, $newFirstItem->title);
        $this->assertEquals($fifth->uuid, $newFirstItem->uuid);
    }

    public function testSetNewItem() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);
        $new = new PlaylistItem(new \stdClass());
        $new->title = 'Jungle Drums';
        $new->soundtrack = 'Half Life';
        $new->generateUuid();
        $playlist->setItem($new, 2);

        $third = $playlist->getItem(2);
        $this->assertEquals($third->title, $new->title);
        $this->assertEquals($third->uuid, $new->uuid);
    }
}
