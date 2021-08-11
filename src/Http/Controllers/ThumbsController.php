<?php

namespace Ava\Thumbs\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thumb;
use DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use File;

class ThumbsController extends Controller
{

	const SALT = 'A45Scj1381h13ba';

	//тумбса иконки
	public function generateThumb($table, $folder, $id, $field, $mark, $filename, $ext){
		if(file_exists(url()->current())) return response()->file(url()->current());

		if(!$table || !$folder || !$id || !$mark || !$filename || !$ext) return false;

       $webpHash =  self::getHash('webp'.$table.$folder.$id.$mark);
       $hash =  self::getHash($ext.$table.$folder.$id.$mark);
       if($hash != $filename && $webpHash != $filename) return false;

		if($webpHash == $filename && $ext != 'webp'){
			$ext = 'png';
		}

		$thumbModel = Thumb::where('mark', $mark)->first();
		if(!$thumbModel) return false;


		$model_name = Str::studly(Str::singular($table));
		if($model_name && class_exists('App\\Models\\'.$model_name)){
			$model_class = 'App\\Models\\'.$model_name;
			$model = $model_class::where('id', (int) $id)->first();
			if(!$model) return false;
			$img_path = $model->$field;
			if(!$img_path) return false;
		}else{
			$img_path = DB::table($table)->where('id', (int) $id)->pluck($field)->first();
			if(!$img_path) return false;
		}

		return self::createThumbnail($thumbModel, $img_path, $table, $folder, $id, $field, $mark, $filename, $ext);
	}

	//тумбса галлереи
	public static function generateGalleryThumb($table, $folder, $id, $field, $mark, $filename, $ext){
        if(file_exists(url()->current())) return response()->file(url()->current());
		if(!$table || !$folder || !$id || !$mark || !$filename || !$ext) return false;

		$thumbModel = Thumb::where('mark', $mark)->first();
		if(!$thumbModel) return false;

		$model_name = Str::studly(Str::singular($table));
		if($model_name && class_exists('App\\Models\\'.$model_name)){
			$model_class = 'App\\Models\\'.$model_name;
			$model = $model_class::where('id', (int) $id)->first();
			if(!$model) return false;
			$gallery = $model->$field;
		}else{
			$gallery = DB::table($table)->where('id', (int) $id)->pluck($field)->first();
		}

		if(!$gallery) return false;
		$gallery = json_decode($gallery);
		if(!$gallery) return false;
		$img_path = false;
        foreach ($gallery as $key => $src) {
            $hash = ThumbsController::getHash($src.$table.$folder.$id.$mark);
            if($hash != $filename) continue;
            $img_path = $src;
            break;
		}
        if(!$img_path) return false;

		return self::createThumbnail($thumbModel, $img_path, $table, $folder, $id, $field, $mark, $filename, $ext);
    }

    public static function createThumbnail($thumbModel, $img_path, $table, $folder, $id, $field, $mark, $filename, $ext){
	    if(!file_exists(public_path().'/storage/'.$img_path)) return false;

		$image = Image::make(File::get(public_path().'/storage/'.$img_path));
		$thumbnail = Image::make(clone $image);

		$tW = ($thumbModel->width) ? $thumbModel->width : null;
		$tH = ($thumbModel->height) ? $thumbModel->height : null;
		if($tW &&!$tH) $tH = (int) ($image->height()/($image->width()/$tW));
		if($tH &&!$tW) $tW = (int) ($image->width()/($image->height()/$tH));

		if($thumbModel->cover){
			$thumbnail = $thumbnail->fit($tW, $tH);
		}elseif($thumbModel->fix_canvas){
			$kW = $tW/$image->width();
			$kH = $tH/$image->height();
			$k = $kW;
			if($kH < $kW) $k = $kH;

			if($thumbModel->upsize){
				$thumbnail->resize(round($k * $image->width()), round($k * $image->height()));
			}else{
				if($image->width() > $tW && $image->height() > $tH){
					$thumbnail->resize(round($k * $image->width()), round($k * $image->height()));
				}
			}

			if ($thumbModel->canvas_color) {
				$thumbnail = $thumbnail->resizeCanvas($tW, $tH, 'center', false, $thumbModel->canvas_color);
			}else {
				$thumbnail = $thumbnail->resizeCanvas($tW, $tH);
			}
		}else{
			$kW = $tW/$image->width();
			$kH = $tH/$image->height();
			$k = $kW;
			if($kH < $kW) $k = $kH;

			if($thumbModel->upsize){
				$thumbnail->resize(round($k * $image->width()), round($k * $image->height()));
			}else{
				if($image->width() > $tW && $image->height() > $tH){
					$thumbnail->resize(round($k * $image->width()), round($k * $image->height()));
				}
			}
		}


		if($ext == 'jpeg') $ext = 'jpg';
		$path = 'public/_thumbs/'.$table.'/'.$folder.'/'.$id.'/'.$field.'/'.$mark.'/'.$filename.'.'.$ext;
		$file = $thumbnail->encode($ext, ($thumbnail->quality ?? 90))->encoded;
		Storage::put($path, $file);

		return $thumbnail->response();
    }

	public static function getHash($str){
		$hash = crypt(md5($str), env('THUMBS_SALT') ?? ThumbsController::SALT);
		$hash = str_replace(array('.','/',',','?',''), 'x', $hash);

		return $hash;
	}

	public function generatePlaceholder($mark, $ext){

		if (file_exists(url()->current())) return url()->current();

		if (!$mark || !$ext) return false;

		$thumbModel = Thumb::where('mark', $mark)->first();

		if (!$thumbModel) return false;

		$tW = ($thumbModel->width) ? $thumbModel->width : null;
		$tH = ($thumbModel->height) ? $thumbModel->height : null;

		if(!$tW && !$tH) return false;

		if(!$tH) $tH = $tW;
		if(!$tW) $tW = $tH;

		$image = Image::canvas($tW, $tH,'#eee');

		$path = 'public/_thumbs/placeholders/'.$mark.'.jpg';
		Storage::put($path, (string) $image->encode());

		return $image->response();
	}


}
