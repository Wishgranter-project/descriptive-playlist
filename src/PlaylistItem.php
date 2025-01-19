<?php

namespace WishgranterProject\DescriptivePlaylist;

use WishgranterProject\DescriptivePlaylist\Utils\Helpers;
use WishgranterProject\DescriptivePlaylist\Utils\StdClassWrapper;

class PlaylistItem extends StdClassWrapper
{
    protected array $schema = [
        'uuid' => [
            'required',
            'is:string',
            'length:36'
        ],
        'title' => [
            'is:string',
            'maxLength:255'
        ],
        'artist' => [
            'is:string|string[]',
            'maxLength:255'
        ],
        'featuring' => [
            'is:string|string[]',
            'maxLength:255'
        ],
        'cover' => [
            'is:string',
            'maxLength:255'
        ],
        'album' => [
            'is:string',
            'maxLength:255'
        ],
        'soundtrack' => [
            'is:string|string[]',
            'maxLength:255'
        ],
        'genre' => [
            'is:string|string[]',
            'maxLength:255'
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
        if (! isset($this->uuid)) {
            $this->generateUuid();
        }
    }

    /**
     * Generates an uuid for the object.
     *
     * @return string
     *   uuid string.
     */
    public function generateUuid(): string
    {
        return $this->uuid = Helpers::guidv4();
    }

    /**
     * Creates a copy of the item.
     *
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem
     *   Copy of the playlist with its on uuid.
     */
    public function createCopy(): PlaylistItem
    {
        $copy = new PlaylistItem($this->getCopyOfTheData());
        $copy->generateUuid();

        return $copy;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(&$errors = []): bool
    {
        if (empty($this->title) && empty($this->album)) {
            $errors[] = 'Inform a title or an album';
        }

        return parent::isValid($errors);
    }
}
