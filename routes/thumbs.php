<?php
use Avast\Thumbs\Http\Controllers\ThumbsController;

Route::get(Storage::disk('local')->url('').'_thumbs/{table}/{dir}/{id}/{field}/{mark}/{filename}.{ext}',[ThumbsController::class, 'generateThumb']);
Route::get(Storage::disk('local')->url('').'_thumbs/{table}/{dir}/{id}/gallery/{field}/{mark}/{filename}.{ext}',[ThumbsController::class, 'generateGalleryThumb']);
Route::get(Storage::disk('local')->url('').'_thumbs/placeholders/{mark}.{ext}',[ThumbsController::class, 'generatePlaceholder']);

