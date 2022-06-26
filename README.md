# Laravel Sortable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hippieua/laravel-sortable)](https://packagist.org/packages/hippieua/laravel-sortable)
[![Total Downloads](https://img.shields.io/packagist/dt/hippieua/laravel-sortable)](https://packagist.org/packages/hippieua/laravel-sortable)

This package allows you to sort order of your Laravel model records with easy methods: 
```php
$model->moveUp();
$model->moveDown();
$model->move('up');
$model->move('down');
```
## Installation

To install the most recent version, run the following command.

```bash
composer require hippieua/laravel-sortable
```

## Setup
Your model should contain sortable field. You can add new or use existing one (don't use default id field)

```php
Schema::table('comments', function (Blueprint $table) {
    $table->unsignedInteger('order')->default(1);
});
```

Update you model, use Sortable trait and fill `$sortable_field` property.

```php
    use Hippie\Sortable\Sortable;
    
    class Comment extends Model
    {
        use Sortable;
        
        protected string $sortable_field = 'order';
    }
```

If your model have BelongsTo relationship, and you want to move records up and down within your relation items fill `$sortable_relation` property.
 
```php
use Hippie\Sortable\Sortable;

class Comment extends Model
{
    use Sortable;
    
    protected string $sortable_field = 'order';
    protected string $sortable_relation = 'post';
    
    protected $fillable = [
        'order',
    ];
    
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
```

```php
class Post extends Model
{
    public function comments(): BelongsTo
    {
        return $this->hasMany(Comment::class)->orderBy('order'); 
    }    
}
```

In case you don't want to calculate your sortable field value on creating new item manually add `static::created` to your sortable model
```php
protected static function booted()
{
    static::created(function ($model) {
        $model->updateSortOrderOnCreate();
    });
}
```

## Usage
### Add new item
``` php
$comment = $post->comments()->create([
    'text' => $request->text,
])
```
`$comment->order`  value will be set to `$post->comments->count() + 1`

### Sort existing items
``` php
$post->comments()->first()->moveDown()
```

will set `order` field value for the first comment to 2 and set `order` field value for second item to 1;

### Available methods
 ``` php
$comment->moveUp();
$comment->moveDown();
$comment->move('up');
$comment->move('down');
```

## Info
Tested on Laravel 8 and 9 versions

## Credits
- [Vitalii Gura](https://github.com/hippieua)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.