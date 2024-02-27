<?php

declare(strict_types=1);

namespace WishgranterProject\DescriptivePlaylist\Tests;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;
use WishgranterProject\DescriptivePlaylist\Playlist;

final class PlaylistIterateTest extends Base
{
    public function testIterating()
    {
        $playlist = new Playlist('tests/template.dpls');

        $expectedTitles = [
            0 => 'Lazy Day Blues',
            1 => 'Let\'s go sunning',
            2 => 'Nigraj kandeloj dancas',
            3 => 'If I could Fly',
            4 => 'The Bard\'s Song The Hobbit',
            5 => 'Nightfall',

            7 => 'King of fallen grace',
            8 => 'Over the hills and far away',
        ];

        foreach ($playlist->items as $lineN => $item) {
            if ($item) {
                $this->assertEquals($expectedTitles[$lineN], $item->title);
            }
        }
    }
}
