# Changelog

All notable changes to this project will be documented in this file.

## [2.2.0] - 2023-03-25

### Added

- `Header::__unset()` and `PlaylistItem::__unset()` have been implemented.

### Fixed

- `Header::clear()` and `PlaylistItem::clear()` were not cleaning `null` 
  properties.

---

## [2.1.0] - 2023-03-11

### Added

- `Header::clear()` and `PlaylistItem::clear()` now also unset empty  
  properties ( not `0` or `'0'` ) and extract the values of one-item-long propertier.  
  This can help in saving space and in general making the objects simpler.

---

## [2.0.1] - 2023-02-02

### Fixed

- `Playlist` and `PlaylistItem` custom properties are now validated against
  a maximum o 255 characters ( string or array ).

---

## [2.0.0] - 2023-01-14

### Added

- `Playlist::getHeader()`
- `Playlist::setHeader()`

### Changed

- `PlaylistItem::setProperty()`
  `PlaylistItem::__set()`:  
  When attempting to set a property with a invalid value, an instance of 
  `\InvalidArgumentException` will be thrown instead of triggering an user 
  error as before.
- `Playlist::setItem()`: If `$item` is invalid, an instance of 
  `InvalidArgumentException` will be thrown.

### Fixed

- `Playlist::setItem()`: When adding items to an empty playlists file, the item
  would be added to the very first line ( the place reserved for the header ).
- `Playlist::getLastItem()`: calling it on an empty file runs into an infinite
  loop.
- `Playlist::getItem()`  
  `Playlist::getItems()`  
  `Playlist::deletePosition()`
  `Playlist::getLastItem()`: calling those methods on a non-existing file would
  throw exceptions, now they just return null, empty arrays or false.
