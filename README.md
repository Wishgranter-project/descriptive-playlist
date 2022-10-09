# Descriptive playlist

## Instantiating

```php
use AdinanCenci\DescriptivePlaylist\Playlist;
$playlist = new Playlist('my-file.dpls');
```

<br><br>

## Retrieving items

By position:

```php
$item = $playlist->getItem(5);
// or retrieve multiples at once
$items = $playlist->getItems([5,7]);
```

<br><br>

By uuid:

```php
$item = $playlist->getItemByUuid('ff81ea29-faa3-4523-8ddf-0da011a2a486');
// or retrieve multiples at once
$items = $playlist->getItemsByUuid(['ff81ea29-faa3-4523-8ddf-0da011a2a486','uuid":"008540f5-cf34-41ec-8b3f-9e1639695370']);
```

<br><br>

## The PlaylistItem object

The object support several properties.

A valid object must contain an uuid ( it generates one automatically ) and either an title or album properties.

<br><br>

## Adding new items

```php
use AdinanCenci\DescriptivePlaylist\PlaylistItem;
$music = new PlaylistItem();
$music->title = 'Nightfall';
$music->artist = 'Blind Guardian';
$playlist->setItem($music); // adds to the end of the file
```

You may inform the position for the new item.

```php
$playlist->setItem($music, 10); // add to the 10th position
```

<br><br>

## Updating existing items

```php
$item = $playlist->getItem(2);
$item->featuring = 'Ali Edwards';
$item->setItem($item); // Moves the item to the end of the file
$item->setItem($item, 2); // Leaves the item in its original position
$item->setItem($item, 4); // Moves the item to the 4th position
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

## Licence

MIT
