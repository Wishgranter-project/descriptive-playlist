# Changelog
All notable changes to this project will be documented in this file.


## [2.0.0] - 2023-01-14

## Added
- `Playlist::getHeader()`
- `Playlist::setHeader()`

## Changed
- `PlaylistItem::setProperty()`
  `PlaylistItem::__set()`:  
  When attempting to set a property with a invalid value, an instance of 
  `\InvalidArgumentException` will be thrown instead of triggering an user 
  error as before.
- `Playlist::setItem()`: If `$item` is invalid, an instance of 
  `InvalidArgumentException` will be thrown.

## Fixed
- `Playlist::setItem()`: When adding items to an empty playlists file, the item
  would be added to the very first line ( the place reserved for the header ).
- `Playlist::getLastItem()`: calling it on an empty file runs into an infinite
  loop.
- `Playlist::getItem()`  
  `Playlist::getItems()`  
  `Playlist::deletePosition()`
  `Playlist::getLastItem()`: calling those methods on a non-existing file would
  throw exceptions, now they just return null, empty arrays or false.
