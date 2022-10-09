<?php 
declare(strict_types=1);

namespace AdinanCenci\DescriptivePlaylist\Tests;

use AdinanCenci\DescriptivePlaylist\PlaylistItem;
use AdinanCenci\DescriptivePlaylist\Playlist;

final class PlaylistHeaderTest extends Base
{
    public function testSetHeaderTitle() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file, '');

        $playlist = new Playlist($file);
        $playlist->title = 'Playlist title';

        $anotherPlaylist = new Playlist($file);
        $this->assertEquals('Playlist title', $playlist->title);
    }

    public function testSetHeaderDescription() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file, '');

        $playlist = new Playlist($file);
        $playlist->description = 'Playlist description';

        $anotherPlaylist = new Playlist($file);
        $this->assertEquals('Playlist description', $playlist->description);
    }

    public function testReadHeader() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $this->assertEquals('Template for tests', $playlist->title);
        $this->assertEquals('test description', $playlist->description);
    }

    public function testSetCustomProperty() 
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);
        $playlist->xxxCustomProperty = 'test';

        $anotherPlaylist = new Playlist($file);

        $this->assertEquals('test', $anotherPlaylist->xxxCustomProperty);
    }
}
