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
