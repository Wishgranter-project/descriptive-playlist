<?php

declare(strict_types=1);

namespace WishgranterProject\DescriptivePlaylist\Tests;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use WishgranterProject\DescriptivePlaylist\Playlist;

final class PlaylistHeaderTest extends Base
{
    public function testSetHeaderTitle()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file, '');

        $playlist = new Playlist($file);
        $header = $playlist->getHeader();
        $header->title = 'Playlist title';
        $playlist->setHeader($header);

        $anotherPlaylist = new Playlist($file);
        $this->assertEquals('Playlist title', $playlist->header->title);
    }

    public function testSetHeaderDescription()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file, '');

        $playlist = new Playlist($file);
        $header = $playlist->getHeader();
        $header->description = 'Playlist description';
        $playlist->setHeader($header);

        $anotherPlaylist = new Playlist($file);
        $this->assertEquals('Playlist description', $playlist->header->description);
    }

    public function testReadHeader()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);

        $this->assertEquals('Template for tests', $playlist->header->title);
        $this->assertEquals('test description', $playlist->header->description);
    }

    public function testSetCustomProperty()
    {
        $file = 'tests/files/' . __FUNCTION__ . '.dpls';
        $this->resetTest($file);

        $playlist = new Playlist($file);
        $header = $playlist->getHeader();
        $header->xxxCustomProperty = 'test';
        $playlist->setHeader($header);

        $anotherPlaylist = new Playlist($file);

        $this->assertEquals('test', $anotherPlaylist->header->xxxCustomProperty);
    }
}
