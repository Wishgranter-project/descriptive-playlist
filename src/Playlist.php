<?php

namespace WishgranterProject\DescriptivePlaylist;

use AdinanCenci\JsonLines\JsonLines;

/**
 * @property \Iterator $items
 *   Iterator object to read the playlist item by item.
 * @property string $fileName
 *   The filename.
 * @property int $lineCount
 *   The number of lines in the file.
 */
class Playlist
{
    /**
     * @var AdinanCenci\JsonLines\JsonLines
     *   Json lines object.
     */
    protected JsonLines $jsonLines;

    /**
     * Constructor.
     *
     * @param string $fileName
     *   Absolute path to the file.
     */
    public function __construct(string $fileName)
    {
        $this->jsonLines = new JsonLines($fileName);
    }

    /**
     * Return read only properties.
     *
     * @param string $propertyName
     *   Property name.
     */
    public function __get($var)
    {
        switch ($var) {
            case 'fileName':
            case 'filename':
                return $this->jsonLines->fileName;
                break;
            case 'lineCount':
                return $this->jsonLines->lineCount;
                break;
            case 'items':
                return new PlaylistIterator($this->jsonLines->objects);
                break;
            case 'header':
                return $this->getHeader();
                break;
        }
    }

    public function __set($var, $value)
    {
        if ($var == 'header') {
            $this->setHeader($value);
        }

        if (in_array($var, ['fileName', 'lineCount', 'items'])) {
            throw new \InvalidArgumentException($var . ' is ready only.');
        }
    }

    /**
     * Counts how many lines the file has.
     *
     * @param bool &$lastLineEmpty
     *   Turns true if the last line of the file is empty.
     *
     * @return int
     *   The number of lines in the file.
     */
    public function countLines(&$lastLineEmpty): int
    {
        return $this->jsonLines->countLines($lastLineEmpty);
    }

    /**
     * Returns the last NON-BLANK line of the file.
     *
     * @return int
     *   The last line.
     */
    public function getLastLine(): int
    {
        $line = $this->countLines($lastLineEmpty);
        $line -= $lastLineEmpty && $line > 0
            ? 1
            : 0;
        return $line;
    }

    /**
     * Instantiates a new search object.
     *
     * @param string $operator
     *   The operator to with wich avaliate the search conditions:
     *   "AND" or "OR".
     *
     * @return AdinanCenci\FileEditor\Search\Search
     *   The search object.
     */
    public function search(string $operator = 'AND'): Search
    {
        return new Search($this->jsonLines, $operator);
    }

    /**
     * Returns the PlaylistItem at the specified $position.
     *
     * @param int $position
     *   The position within the playlist.
     *
     * @return PlaylistItem|null
     *   The item at $position, null if there is nothing there.
     */
    public function getItem(int $position): ?PlaylistItem
    {
        $items = $this->getItems([$position]);

        return $items
            ? reset($items)
            : null;
    }

    /**
     * Retrieves the playlist at the specified $positions.
     *
     * @param int[] $positions
     *   An array of positions.
     *
     * @return (WishgranterProject\DescriptivePlaylist\PlaylistItem|null)[]
     *   The items at the specified positions.
     *
     * @throws FileDoesNotExist
     * @throws FileIsNotReadable
     */
    public function getItems(array $positions): array
    {
        $items = [];
        $lines = array_map(function ($index) {
            return $index + 1;
        }, $positions);

        try {
            $objects = $this->jsonLines->getObjects($lines);
        } catch (\Exception $e) {
            return [];
        }

        foreach ($objects as $line => $object) {
            if (! $object) {
                continue;
            }
            $index = $line - 1;
            $items[ $index ] = new PlaylistItem($object);
        }

        return $items;
    }

    /**
     * Returns the last PlaylistItem on the playlist.
     *
     * @param int &$position
     *   Turns into the position of the last item in the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem|null
     *   The last item in the playlist, null if the line is empty.
     */
    public function getLastItem(&$index = 0): ?PlaylistItem
    {
        $lastLine = $this->getLastLine();

        do {
            $item = $this->getItem($lastLine);
            $lastLine--;
        } while (!$item && $lastLine >= 0);

        $index = $lastLine;
        return $item;
    }

    /**
     * Retrieves playlist items with the specified uuids.
     *
     * @param string[] $uuids
     *   An array of unique ids.
     *
     * @return (WishgranterProject\DescriptivePlaylist\PlaylistItem|null)[]
     *   The playlist items indexed by their position in the file.
     */
    public function getItemsByUuid(array $uuids): array
    {
        $results = [];
        $aimingFor = count($uuids);

        foreach ($this->items as $position => $item) {
            if (!$item) {
                continue;
            }

            if (in_array($item->uuid, $uuids)) {
                $results[ $position ] = $item;
            }

            if (count($results) == $aimingFor) {
                break;
            }
        }

        return $results;
    }

    /**
     * Retrieves the playlist item with the specified uuid.
     *
     * @param string $uuid
     *   An unique id.
     * @param int &$position
     *   It will turn into the item's corresponding position in the playlist.
     *   Turns into -1 if the item is not in the playlist.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem|null
     */
    public function getItemByUuid(string $uuid, &$position = -1): ?PlaylistItem
    {
        $items = $this->getItemsByUuid([ $uuid ]);

        if (! $items) {
            $position = -1;
            return null;
        }

        $keys = array_keys($items);
        $position = reset($keys);

        return $items[ $position ];
    }

    /**
     * Checks if the specified item is in the playlist.
     *
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   The item we are checking.
     * @param int &$position
     *   Will turn into the item's corresponding position in the playlist.
     *   Turns into -1 if the item is not in the playlist.
     *
     * @return bool
     *   True if the item is in the playlist.
     */
    public function hasItem(PlaylistItem $item, &$position = -1): bool
    {
        $position = -1;

        if (! $item->uuid) {
            return false;
        }

        return (bool) $this->getItemByUuid($item->uuid, $position);
    }

    /**
     * Retrieves random playlist items.
     *
     * @param int $count
     *   How many lines to return.
     * @param int|null $from
     *   Limits the pool of lines available.
     * @param int|null $to
     *   Limits the pool of lines available.
     *
     * @return WishgranterProject\DescriptivePlaylist\PlaylistItem[]
     *   The items we retrieved.
     */
    public function getRandomItems(int $count, ?int $from = null, ?int $to = null): array
    {
        $from    = is_null($from) ? 1 : $from + 1;
        $objects = $this->jsonLines->getRandomObjects($count, $from, $to);
        $items   = [];

        foreach ($objects as $line => $object) {
            $items[ $line + 1 ] = new PlaylistItem($content);
        }

        return $items;
    }

    /**
     * Updates the item and changes its position within the file.
     *
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   The item to update.
     * @param int $newPosition
     *   If provided, the $item will be moved to this new position.
     *   If not, the item will be left in its current position.
     *   If the item is new to the playlist and the position is not provided,
     *   the item will be added to the end of the file.
     *
     * @return bool
     */
    public function setItem(PlaylistItem $item, ?int $newPosition = null): bool
    {
        if (! $item->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
            return false;
        }

        $alreadyExists = $this->hasItem($item, $currentPosition);
        if ($alreadyExists) {
            // Delete from current position.
            $this->jsonLines->deleteObject($currentPosition + 1);

            if ($newPosition == -1) {
                $line = $this->jsonLines->nameLastLine(true);
            } elseif ($newPosition === null) {
                $line = $currentPosition + 1;
            } else {
                $line = $newPosition + 1;
            }
        } else {
            $line = in_array($newPosition, [null, -1])
                ? $this->jsonLines->nameLastLine(true)
                : $newPosition + 1;
        }

        $this->jsonLines->addObject($item->getCopyOfTheData(), $line);
        return true;
    }

    /**
     * Deletes a PlaylistItem from the playlist.
     *
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem $item
     *   The item to be deleted.
     *
     * @return bool
     *   False if the item is not in the playlist to begin with.
     */
    public function deleteItem(PlaylistItem $item): bool
    {
        if ($this->hasItem($item, $currentPosition)) {
            $this->jsonLines->deleteObject($currentPosition + 1);
            return true;
        }

        return false;
    }

    /**
     * Deletes the playlist item at the specified position.
     *
     * @param int $position
     *   The position withing the playlist.
     * @param WishgranterProject\DescriptivePlaylist\PlaylistItem|null $item
     *   Turns into the item at $position.
     *
     * @return bool
     *   False if there is nothing at the specified position to begin with.
     */
    public function deletePosition(int $position, &$item = null): bool
    {
        if ($item = $this->getItem($position)) {
            $this->jsonLines->deleteObject($position);
            return true;
        }

        return false;
    }

    /**
     * Returns an object representing the header of the file.
     *
     * @return WishgranterProject\DescriptivePlaylist\Header
     *   The header object.
     */
    public function getHeader(): Header
    {
        try {
            $stdObj = $this->jsonLines->getObject(0);
        } catch (\Exception $e) {
            $stdObj = null;
        }

        $stdObj = $stdObj
            ? $stdObj
            : new \stdClass();

        return new Header($stdObj);
    }

    /**
     * Updates the header of the file.
     *
     * @param WishgranterProject\DescriptivePlaylist\Header $header
     *   The object representing the header of the file.
     *
     * @return void
     */
    public function setHeader(Header $header): void
    {
        if (! $header->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }

        $this->jsonLines->setObject(0, $header->getCopyOfTheData());
    }
}
