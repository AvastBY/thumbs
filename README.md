## Install

`composer require avast/thumbs`

`php artisan avast-thumbs:install`

## Routes
`require __DIR__.'/thumbs.php';`

## Model trait
`use \Avast\Thumbs\Traits\Thumbs;`

## Usage

Create thumb via admin Tools->Thumbs

Then use in blade {{ $model->thumb('`[image attribute]`', '`[thumb mark]`') }}

For multiple images
```blade
@if($it->gallery('gallery'))
  @foreach($it->gallery('gallery') as $image)
    <img src="{{ $it->galleryThumb($image, 'big') }}">
  @endforeach
@endif
```
