# Changelog

All notable changes to this project will be documented in this file.

## [6.1.0] - 2025-01-27
### Added
- Added `Search::orderRandomly()`.

---

## [6.0.0] - 2025-01-19
### Removed
- No longer can edit the header's properties with `Playlist::__set()` or retrieve them with `Playlist::__get()`.

### Added
- Added `Playlist::getRandomItems()`.
- Added `Search::orderBy()`.

---

## [5.0.0] - 2024-02-27
### Changed
- Renamed the package to `wishgranter-project/descriptive-playlist` and namespace to `WishgranterProject\DescriptivePlaylist`.

---

## [4.0.0] - 2023-09-20
### Changed
- Renamed `Header::getData()` and `PlaylistItem::getData()` to `::getCopyOfTheData()`.
- Renamed `Header::clear()` and `PlaylistItem::clear()` to `::sanitize()` to avoid confusion with `::empty()`.

### Added
- Added `PlaylistItem::createCopy()`.

---

## [3.1.0] - 2023-08-26

### Added
- Added `Header::empty()` and `PlaylistItem::empty()` to unset the properties of the object.

---

## [3.0.0] - 2023-07-26

### Changed

- [isse 2](https://github.com/adinan-cenci/descriptive-playlist/issues/2): `Player::setItem($item, $position = null)`: If `$item` already exists in the playlist and `$position` is not informed: previously the item would have been moved to the end of the playlist, now the item will remain in its current position.

---

## [2.2.2] - 2023-07-24

### Fixed

- [isse 1](https://github.com/adinan-cenci/descriptive-playlist/issues/1): A small performance improvement in the `Player::getItemByUuid()` method.

---

## [2.2.1] - 2023-07-22

### Changed

- Just updating the dependencies, unit tests and documentation.

---

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
  This can help in saving space and in general making the objects cleaner.

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
  throw exceptions, now they just return null, empty arrays or `false`.
