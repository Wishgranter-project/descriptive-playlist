# Descriptive playlist

A library to read and edit [Descriptive playlist files](https://github.com/wishgranter-project/descriptive-playlist-definition).

## Instantiating

```php
use WishgranterProject\DescriptivePlaylist\Playlist;
$playlist = new Playlist('path-to/my-file.dpls');
```

<br><br>

### Playlist properties

```php
$playlist->fileName; // self-explanatory ( read only )
$playlist->lineCount; // The number of lines of the file ( read only )
$playlist->items; // An iterator object to loop through the file.
```

<br><br>

## The header object

```php
// Retrieving
$header = playlist->getHeader();

$header->title = 'my playlist title';
$header->description = 'description';
$header->xxxCustomProperty = 'custom value';

// Save changes.
$playlist->setHeader($header);
```

<br><br>

## Retrieving items

```php
// By position:
$item = $playlist->getItem(5);
// retrieve multiples at once:
$items = $playlist->getItems([5,7]);

// By uuid ( $position will be set with the item's position in the playlist ):
$item = $playlist->getItemByUuid('ff81ea29-faa3-4523-8ddf-0da011a2a486', $position);
// or retrieve multiples at once ( they will be indexed by their position in the playlist )
$items = $playlist->getItemsByUuid(['ff81ea29-faa3-4523-8ddf-0da011a2a486', '008540f5-cf34-41ec-8b3f-9e1639695370']);


```

<br><br>

## The PlaylistItem object

The object support several properties.

A valid object must contain an `uuid` ( it generates one automatically ) and either an title or album properties ( [see specifications](https://github.com/wishgranter-project/descriptive-playlist-definition/blob/master/technical-specifications.md) ).

<br><br>

## Adding new items

```php
use WishgranterProject\DescriptivePlaylist\PlaylistItem;

$music = new PlaylistItem();
$music->title = 'Nightfall';
$music->artist = 'Blind Guardian';
$playlist->setItem($music); // adds to the end of the file.

// Alternatively you may inform the position for the new item.
$playlist->setItem($music, 10);
```

<br><br>

## Updating existing items

```php
$item = $playlist->getItem(2);
$item->featuring = 'Ali Edwards';
$item->setItem($item); // Leaves the item in its original position.
$item->setItem($item, 4); // Move the item to the 5th position.
$item->setItem($item, -1); // Move the item to the end of the playlist.
```

<br><br>

## Removing items

```php
$item = $playlist->getItem(10);
$playlist->delete($item);

// Or delete an item based on its position.
$playlist->deletePosition(10); // Delete the item in the 10th position.
```

<br><br>

## Searching

The library also provides a way to query the file.  
Instantiate a new `Search` object, give it conditions and call the `::find()` method, 
it will return an array of matching objects indexed by their line in the file.

```php
$search = $file->search();
$search->condition("property name", 'value to compare', 'operator');
$results = $search->find();
```

<br><br>

**Is null operator**

```php
$search->condition('xxxRating', null, 'IS NULL');
// Will match entries where the "xxxRating" property equals null or is 
// not defined.
```

<br><br>

**Equals operator**

```php
$search->condition('title', 'Iron man', '=');
// Will match entries where the "title" property equals "Iron man" 
// ( case insensitive ).
```

<br><br>

**In operator**

```php
$search->condition('artist', ['Queen', ' Iron Maiden'], 'IN');
// Will match entries where the "artist" property equals to either 
// "Queen" or "Iron Maiden" ( case insensitive ).
```

<br><br>

**Like operator**

```php
$search->condition('title', 'fire', 'LIKE');
// Will match entries where the "title" property contains the word "fire"
// e.g: "Hearts on fire", "Through The fire and flames", "Temple of fire"
// etc ( case insensitive ).

$search->condition('title', ['sons', 'babylon'], 'LIKE');
// It also accept arrays. This will match match 
// "Sons of Riddlemark", "Disciples of Babylon", etc.
```

<br><br>

**Number comparison operators**

It also supports "less than", "greater than", "less than or equal", "greater than or equal" and "between".

```php
$search
  ->condition('xxxRating', 10, '<')
  ->condition('xxxYear', 1990, '>')
  ->condition('xxxAge', 60, '<=')
  ->condition('xxxAge', 18, '>=')
  ->condition('xxxAge', [10, 50], 'BETWEEN');
```

<br><br>

### Negating operators

You may also negate the operators.

```php
$search
  ->condition('title', 'Iliad', '!=') // Different to ( case insensitive ).
  ->condition('title', ['Iliad', ' Odyssey'], 'NOT IN') // case insensitive.
  ->condition('xxxRating', [5, 7], 'NOT BETWEEN')
  ->condition('title', ['foo', 'bar'], 'UNLIKE');
```

<br><br>

### Multiple conditions

You may add multiple conditions to a search.
By default all of the conditions must be met.

```php
$search = $file->search();
$search
  ->condition('artist', 'Iron Maiden', '=')
  ->condition('xxxRelease', 2000, '<');
$results = $search->find();
// Will match entries for Iron Maiden from before the yar 2000.
```

But you can make it so that only one needs to be met.

```php
$search = $file->search('OR');
$search
  ->condition('artist', 'Blind Guardian', '=')
  ->condition('artist', 'Demons & Wizards', '=');
$results = $search->find();
// Will match entries for both Blind Guardian and Demons & Wizards.
```

<br><br>

### Conditions groups

You may also group conditons to create complex queries.

```php
$search = $file->search('OR');

$search->andConditionGroup()
  ->condition('artist', 'Angra', '=')
  ->condition('xxxRelease', 2010, '<');

$search->andConditionGroup()
  ->condition('artist', 'Almah', '=')
  ->condition('release', 2013, '>');

$results = $search->find();
// Will match entries for Angra from before 2010 OR
// entries for Almah from after 2013
```

<br><br>

### Order results

```php
$search = $file->search();
$search->orderBy('title', 'ASC');
```

## Licence

MIT
