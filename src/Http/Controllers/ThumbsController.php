<?php

namespace Avast\Thumbs\Http\Controllers;

use Illuminate\Http\Request;
use Avast\Thumbs\Models\Thumb;
use DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

use File;

class ThumbsController extends Controller
{

	const SALT = 'A45Scj1381h13ba';

	//тумбса иконки
	public function generateThumb($table, $folder, $id, $field, $mark, $filename, $ext){
		if(file_exists(url()->current())) return response()->file(url()->current());

		if(!$table || !$folder || !$id || !$mark || !$filename || !$ext) abort(404);

		$webpHash =  self::getHash('webp'.$table.$folder.$id.$mark);
		$hash =  self::getHash($ext.$table.$folder.$id.$mark);

		if($hash != $filename && $webpHash != $filename) return abort(404);
		if($webpHash == $filename && $ext != 'webp') $ext = 'png';

		$thumbModel = Thumb::where('mark', $mark)->first();
		if(!$thumbModel) return abort(404);

		$img_path = self::getFieldValue($id, $table, $field);

		return self::createThumbnail($thumbModel, $img_path, $table, $folder, $id, $field, $mark, $filename, $ext);
	}

	//тумбса галлереи
	public static function generateGalleryThumb($table, $folder, $id, $field, $mark, $filename, $ext){
		if(file_exists(url()->current())) return response()->file(url()->current());
		if(!$table || !$folder || !$id || !$mark || !$filename || !$ext) return abort(404);

		$thumbModel = Thumb::where('mark', $mark)->first();
		if(!$thumbModel) return abort(404);

		$gallery = self::getFieldValue($id, $table, $field);

		$gallery = json_decode($gallery);
		if(!$gallery) return abort(404);

		$img_path = false;
		foreach ($gallery as $key => $it) {
			$src = is_string($it) ? $it : $it->src;
			
			$hash = ThumbsController::getHash($src.$table.$folder.$id.$mark);

			if($hash != $filename) continue;
			$img_path = $src;
			break;
		}

		if(!$img_path) return abort(404);
		return self::createThumbnail($thumbModel, $img_path, $table, $folder, $id, $field, $mark, $filename, $ext, true);
	}

	public static function getFieldValue($id, $table, $field){
		$model_name = Str::studly(Str::singular($table));
		if($model_name && class_exists($model_class = config('voyager.models.namespace').$model_name ?? 'App\\Models\\'.$model_name)){
			$model = $model_class::where('id', (int) $id)->first();
			if(!$model) return abort(404);
			
			if(!empty($model->data) && $model->data->$field){
				$value = $model->data->$field;
			}else{
				$value = $model->$field;
			}
		}else{
			$value = DB::table($table)->where('id', (int) $id)->pluck($field)->first();
		}

		return $value ?? abort(404);
	}

	public static function createThumbnail($thumbModel, $img_path, $table, $folder, $id, $field, $mark, $filename, $ext, $is_gallery = false){
		if(!file_exists(public_path().'/storage/'.$img_path)) abort(404);

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
				if($image->width() > $tW || $image->height() > $tH){
					$thumbnail->resize(round($k * $image->width()), round($k * $image->height()));
				}
			}
		}

		if($thumbModel->blur > 0) $thumbnail->blur($thumbModel->blur <= 100 ? $thumbModel->blur : 100);

		if($ext == 'jpeg') $ext = 'jpg';
		if($is_gallery){
			$path = 'public/_thumbs/'.$table.'/'.$folder.'/'.$id.'/gallery/'.$field.'/'.$mark.'/'.$filename.'.'.$ext;
		}else{
			$path = 'public/_thumbs/'.$table.'/'.$folder.'/'.$id.'/'.$field.'/'.$mark.'/'.$filename.'.'.$ext;
		}

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

		if (!$mark || !$ext) return abort(404);

		$thumbModel = Thumb::where('mark', $mark)->first();

		if (!$thumbModel) return abort(404);

		$tW = ($thumbModel->width) ? $thumbModel->width : null;
		$tH = ($thumbModel->height) ? $thumbModel->height : null;

		if(!$tW && !$tH) return abort(404);

		if(!$tH) $tH = $tW;
		if(!$tW) $tW = $tH;

		$image = Image::canvas($tW, $tH,'#eee');

		$path = 'public/_thumbs/placeholders/'.$mark.'.jpg';
		Storage::put($path, (string) $image->encode());

		return $image->response();
	}


}
