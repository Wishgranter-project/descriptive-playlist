<?php

declare(strict_types=1);

namespace WishgranterProject\DescriptivePlaylist\Tests;

use WishgranterProject\DescriptivePlaylist\PlaylistItem;

final class PlaylistItemTest extends Base
{
    public function testCreateMusicFromInvalidJson()
    {
        $item = PlaylistItem::createFromJson('{"uuid":"123","title":"fuuuuuuuuck}');
        $this->assertEquals(null, $item);
    }

    public function testValideteUuid()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('uuid', 'xx', $errors));
        $this->assertEquals(['uuid should be 36 characters long'], $errors);

        $this->assertFalse($item->isValidProperty('uuid', ['xx'], $errors));
        $this->assertEquals(['uuid should be of the types: string','uuid should be 36 characters long'], $errors);

        $this->assertTrue($item->isValidProperty('uuid', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $errors));
        $this->assertEquals([], $errors);
    }

    public function testValideteTitle()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('title', null, $errors));
        $this->assertEquals(['title should be of the types: string'], $errors);

        $this->assertFalse($item->isValidProperty('title', ['x'], $errors));
        $this->assertEquals(['title should be of the types: string'], $errors);

        $this->assertFalse($item->isValidProperty('title', str_repeat('a', 260), $errors));
        $this->assertEquals(['title should not be longer than 255 characters'], $errors);

        $this->assertTrue($item->isValidProperty('title', 'foo bar', $errors));
        $this->assertEquals([], $errors);
    }

    public function testValideteArtist()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('artist', null, $errors));
        $this->assertEquals(['artist should be of the types: string, string[]'], $errors);

        $this->assertTrue($item->isValidProperty('artist', 'foo bar', $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('artist', ['foo', 'bar'], $errors));
        $this->assertEquals([], $errors);
    }

    public function testValideteFeaturing()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('featuring', null, $errors));
        $this->assertEquals(['featuring should be of the types: string, string[]'], $errors);

        $this->assertTrue($item->isValidProperty('featuring', 'foo bar', $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('featuring', ['foo', 'bar'], $errors));
        $this->assertEquals([], $errors);
    }

    public function testValideteCover()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('cover', null, $errors));
        $this->assertEquals(['cover should be of the types: string'], $errors);

        $this->assertTrue($item->isValidProperty('cover', 'foo bar', $errors));
        $this->assertEquals([], $errors);

        $this->assertFalse($item->isValidProperty('cover', ['foo', 'bar'], $errors));
        $this->assertEquals(['cover should be of the types: string'], $errors);
    }

    public function testValideteAlbum()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('album', null, $errors));
        $this->assertEquals(['album should be of the types: string'], $errors);

        $this->assertTrue($item->isValidProperty('album', 'foo bar', $errors));
        $this->assertEquals([], $errors);

        $this->assertFalse($item->isValidProperty('album', ['foo', 'bar'], $errors));
        $this->assertEquals(['album should be of the types: string'], $errors);
    }

    public function testValideteSoundtrack()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('soundtrack', null, $errors));
        $this->assertEquals(['soundtrack should be of the types: string, string[]'], $errors);

        $this->assertTrue($item->isValidProperty('soundtrack', 'foo bar', $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('soundtrack', ['foo', 'bar'], $errors));
        $this->assertEquals([], $errors);
    }

    public function testValideteGenre()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('genre', null, $errors));
        $this->assertEquals(['genre should be of the types: string, string[]'], $errors);

        $this->assertTrue($item->isValidProperty('genre', 'foo bar', $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('genre', ['foo', 'bar'], $errors));
        $this->assertEquals([], $errors);
    }

    public function testAddInvalidCustomProperty()
    {
        $item = new PlaylistItem();

        $this->assertFalse($item->isValidProperty('randomNonsense', 'foo bar', $errors));
        $this->assertEquals(['unrecognized randomNonsense property'], $errors);
    }

    public function testAddCustomProperty()
    {
        $item = new PlaylistItem();

        $this->assertTrue($item->isValidProperty('xxxValidProp', null, $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('xxxValidProp', 'aaa', $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('xxxValidProp', ['aa', 'bb'], $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('xxxValidProp', 123, $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('xxxValidProp', [11, 22], $errors));
        $this->assertEquals([], $errors);

        $this->assertTrue($item->isValidProperty('xxxValidProp', [11, 'aa'], $errors));
        $this->assertEquals([], $errors);
    }

    public function testValidPlaylistItem()
    {
        $item = new PlaylistItem();
        $item->title = 'title';
        $this->assertTrue($item->isValid());

        $item = new PlaylistItem();
        $item->album = 'album';
        $this->assertTrue($item->isValid());

        $item = new PlaylistItem();
        $item->genre = 'metal';
        $this->assertFalse($item->isValid($errors));
        $this->assertEquals(['Inform a title or an album'], $errors);
    }

    public function testSanitizeData()
    {
        $json = '{"title":["title","cant","be","array"],"cover":["neither","can","cover"],"uuid":"c6a8d990-acec-4be2-bb98-0022c3294c00"}';
        $item = PlaylistItem::createFromJson($json);

        $this->assertFalse($item->isValid());

        $item->sanitize();

        $this->assertEquals(null, $item->title);
        $this->assertEquals(null, $item->cover);
        $this->assertFalse($item->isValid());

        $item->title = 'the title';

        $this->assertTrue($item->isValid());
    }

    public function testCreateCopy()
    {
        $json = '{"title":"test title","artist":"some random","uuid":"c6a8d990-acec-4be2-bb98-0022c3294c00"}';
        $original = PlaylistItem::createFromJson($json);

        $copy = $original->createCopy();

        $this->assertEquals($copy->title, $original->title);
        $this->assertEquals($copy->artist, $original->artist);
        $this->assertNotEquals($copy->uuid, $original->uuid);
    }
}
