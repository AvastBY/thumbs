<?php

namespace Ava\Thumbs\Models;

use Illuminate\Database\Eloquent\Model;


class Thumb extends Model
{
    public static function boot(){
		parent::boot();

		self::updated(function ($model){
			//очищаем тумбсы
			$thumbs_path = storage_path().'/app/public/_thumbs';
			$directories = glob($thumbs_path . '/*/*/*/*/'.$model->mark , GLOB_ONLYDIR);
			if($directories){
				foreach ($directories as $key => $path) {
					$files = scandir($path);
					if($files){
						foreach ($files as $key1 => $filename) {
							if(!$filename) continue;
							if($filename == '.') continue;
							if($filename == '..') continue;
							unlink($path.'/'.$filename);
						}
					}
					rmdir($path);
				}
			}
		});
	}
}
