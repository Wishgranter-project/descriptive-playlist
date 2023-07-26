<?php 
namespace AdinanCenci\DescriptivePlaylist;

use AdinanCenci\JsonLines\JsonLines;

class Playlist 
{
    protected JsonLines $jsonLines;

    public function __construct($fileName) 
    {
        $this->jsonLines = new JsonLines($fileName);
    }

    public function __get($var) 
    {
        switch ($var) {
            case 'fileName':
                return $this->jsonLines->fileName;
                break;
            case 'lineCount':
                return $this->jsonLines->lineCount;
                break;
            case 'items':
                return new PlaylistIterator($this->jsonLines->objects);
                break;
            default:
                $header = $this->getHeader();
                return $header->{$var};
                break;
        }
    }

    public function __set($var, $value) 
    {
        if (in_array($var, ['fileName', 'lineCount', 'items'])) {
            throw new \InvalidArgumentException($var . ' is ready only.');
        }

        $header = $this->getHeader();
        $header->{$var} = $value;
        $this->setHeader($header);
    }

    /**
     * @param bool &$emptyLastLine, it returns true if the last line of the file is empty.
     * @return int
     */
    public function countLines(&$lastLineEmpty) : int
    {
        return $this->jsonLines->countLines($lastLineEmpty);
    }

    /**
     * Returns the last NON-BLANK line of the file.
     * 
     * @return int
     */
    public function getLastLine() : int
    {
        $line = $this->countLines($lastLineEmpty);
        $line -= $lastLineEmpty && $line > 0 ? 1 : 0;
        return $line;
    }

    //------------------------------------------

    /**
     * Returns a Search object.
     * 
     * @param string $operator Possible values: "AND", "OR".
     * If OR is used, only one of the following conditions must be met.
     * If AND is used, all of the conditions must be met.
     * See the Search for more information.
     * @return Search
     */
    public function search(string $operator = 'AND') : Search
    {
        return new Search($this->jsonLines, $operator);
    }

    /**
     * Returns the PlaylistItem in $position.
     * Returns null if there is nothing at that position.
     * 
     * @param int $position
     * @return PlaylistItem|null
     */
    public function getItem(int $position) : ?PlaylistItem
    {
        $items = $this->getItems([$position]);

        return $items
            ? reset($items) 
            : null;
    }

    /**
     * Based on $positions, it will return return an array of PlaylistItem.
     * objects or nulls, if there is nothing on the specified position.
     * 
     * @param int[] $positions
     * @param (PlaylistItem|null)[]
     */
    public function getItems(array $positions) : array
    {
        $items = [];
        $lines = array_map(function($index) { return $index + 1; }, $positions);

        try {
            $objects = $this->jsonLines->getObjects($lines);
        } catch(\Exception $e) {
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
     * Returns the last PlaylistItem on the playlist, null if it is empty.
     * 
     * @return PlaylistItem|null
     */
    public function getLastItem(&$index = 0) : ?PlaylistItem
    {
        $lastLine = $this->getLastLine();

        do {
            $item = $this->getItem($lastLine);
            $lastLine--;
        } while(!$item && $lastLine >= 0);

        $index = $lastLine;
        return $item;
    }

    /**
     * @param string[] $uuids
     * @return (PlaylistItem|null)[]
     */
    public function getItemsByUuid(array $uuids) : array
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
     * @param string $uuid
     * @param int &$position It will return the item's corresponding position in the playlist.
     * It will return -1 if the item is not in the playlist.
     * @return PlaylistItem|null
     */
    public function getItemByUuid(string $uuid, &$position = -1) : ?PlaylistItem
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
     * @param PlaylistItem $item
     * @param int &$position It will return the item's corresponding position in the playlist.
     * It will return -1 if the item is not in the playlist.
     * @return PlaylistItem|null
     */
    public function hasItem(PlaylistItem $item, &$position = -1) : bool
    {
        $position = -1;

        if (! $item->uuid) {
            return false;
        }

        return (bool) $this->getItemByUuid($item->uuid, $position);
    }

    //------------------------------------------

    /**
     * Updates the item and changes it from place.
     */
    public function setItem(PlaylistItem $item, ?int $newPosition = null) : bool
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
            } else if ($newPosition === null) {
                $line = $currentPosition + 1;
            } else {
                $line = $newPosition + 1;
            }

        } else {
            $line = in_array($newPosition, [null, -1])
                ? $this->jsonLines->nameLastLine(true)
                : $newPosition + 1;
        }

        $this->jsonLines->addObject($item->getData(), $line);
        return true;
    }

    //------------------------------------------

    /**
     * Deletes a PlaylistItem from the playlist, it returns false if the item 
     * is not in the playlist.
     * 
     * @param PlaylistItem $item
     * @return bool
     */
    public function deleteItem(PlaylistItem $item) : bool
    {
        if ($this->hasItem($item, $currentPosition)) {
            $this->jsonLines->deleteObject($currentPosition + 1);
            return true;
        }

        return false;
    }

    /**
     * Delete item at the specified position.
     * Returns false if there is nothing at the specified position.
     * 
     * @param int $position
     * @param PlaylistItem|null $item Return the removed item.
     * @return bool
     */
    public function deletePosition(int $position, &$item = null) : bool
    {
        if ($item = $this->getItem($position)) {
            $this->jsonLines->deleteObject($position);
            return true;
        }

        return false;
    }

    //------------------------------------------

    /**
     * @return Header
     */
    public function getHeader() : Header
    {
        try {
            $stdObj = $this->jsonLines->getObject(0);
        } catch(\Exception $e) {
            $stdObj = null;
        }

        $stdObj = $stdObj 
            ? $stdObj 
            : new \stdClass();

        return new Header($stdObj);
    }

    /**
     * @param Header $header
     * @return void
     */
    public function setHeader(Header $header) : void
    {
        if (! $header->isValid($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }

        $this->jsonLines->setObject(0, $header->getData());
    }
}
