<?php

namespace Ava\Thumbs\Traits;

use Illuminate\Support\Facades\Storage;
use Ava\Thumbs\Http\Controllers\ThumbsController;
use Illuminate\Support\Facades\File;

trait Thumbs{

	public static function boot(){
		parent::boot();

		self::updated(function ($model){
			//очищаем тумбсы
			$model->clearThumbs();
		});
	}

	public function thumbNotWebp($field, $thumbMark, $inArray = false){
		$path = $this->thumb($field, $thumbMark, $inArray = false);
		return str_replace('.webp?', '.png?', $path);
	}

	public function placeholder($thumbMark){
		return Storage::disk('local')->url('').'_thumbs/placeholders/'.$thumbMark.'.jpg';
	}

	public function thumb($field, $thumbMark, $inArray = false){
		if(!$field || !$thumbMark) return false;

		$upd_str = '?u='.hash("crc32", $this->updated_at);

		//если это галлерея
		if(is_object($field)) return $this->galleryThumb($field, $thumbMark);

		$icon = $this->$field;
		if(!$this->$field){
			$no_ext_path = Storage::disk('local')->url('').'_thumbs/placeholders/'.$thumbMark;
			$path = $no_ext_path.'.jpg'.$upd_str;
			if($inArray){
				return [
					'path' => $path,
					'no_ext_path' => $no_ext_path,
					'ext' => 'jpg',
					'upd_str' => $upd_str
				];
			}

			return $path;
		}

		$folder = intdiv($this->id,1000) + 1;

		$ext = pathinfo($icon, PATHINFO_EXTENSION);

		$hash = ThumbsController::getHash($ext.$this->getTable().$folder.$this->id.$thumbMark);

		$no_ext_path = Storage::disk('local')->url('').'_thumbs/'.$this->getTable().'/'.$folder.'/'.$this->id.'/'.$field.'/'.$thumbMark.'/'.$hash;

		$filename = $hash.'.'.$ext;
		$path = Storage::disk('local')->url('').'_thumbs/'.$this->getTable().'/'.$folder.'/'.$this->id.'/'.$field.'/'.$thumbMark.'/'.$filename.$upd_str;

		if($inArray){
			return [
				'path' => $path,
				'no_ext_path' => $no_ext_path,
				'ext' => $ext,
				'upd_str' => $upd_str
			];
		}

		return $path;
	}

	public function clearThumbs(){
		$folder = intdiv($this->id,1000) + 1;
		return Storage::deleteDirectory('/public/_thumbs/'.$this->getTable().'/'.$folder.'/'.$this->id);
	}

	public function clearPlaceholders(){
		return Storage::deleteDirectory('/public/_thumbs/placeholders');
	}

	public function gallery($mark = 'gallery'){
		if(!$this->$mark) return false;

		$gallery = json_decode($this->$mark);

		$galleryObjArr = array();
		foreach ($gallery as $key => $src) {
			$obj = new \stdClass();
			$obj->src = $src;
			$obj->mark = $mark;
			$galleryObjArr[] = $obj;
		}

		return $galleryObjArr;
	}

	public function galleryThumb($imageObj, $thumbMark, $inArray = false){
		if(!$imageObj || !$thumbMark) return false;

		if(!$imageObj->src) return false;

		$upd_str = '?u='.hash("crc32", $this->updated_at);

		$folder = intdiv($this->id,1000) + 1;

		$icon = $imageObj->src;
		$ext = pathinfo($icon, PATHINFO_EXTENSION);

		$hash = ThumbsController::getHash($imageObj->src.$this->getTable().$folder.$this->id.$thumbMark);

		$no_ext_path = Storage::disk('local')->url('').'_thumbs/'.$this->getTable().'/'.$folder.'/'.$this->id.'/gallery/'.$imageObj->mark.'/'.$thumbMark.'/'.$hash;

		$filename = $hash.'.'.$ext;
		$path = Storage::disk('local')->url('').'_thumbs/'.$this->getTable().'/'.$folder.'/'.$this->id.'/gallery/'.$imageObj->mark.'/'.$thumbMark.'/'.$filename.$upd_str;

		if($inArray){
			return [
				'path' => $path,
				'no_ext_path' => $no_ext_path,
				'ext' => $ext,
				'upd_str' => $upd_str
			];
		}

		return $path;
	}

	public function pictureSource($field, $thumbMark){
		$data = $this->thumb($field, $thumbMark, true);

		return self::getPictureSourceHtml($data, $thumbMark);
	}

	public function pictureGallerySource($imageObj, $thumbMark){
		$data = $this->galleryThumb($imageObj, $thumbMark, true);

		return self::getPictureSourceHtml($data, $thumbMark);
	}

	public function getPictureSourceHtml($data, $thumbMark = false){
		$default_src = '';
		if($thumbMark) $default_src = 'src="'.$this->placeholder($thumbMark).'"';

		$html = '<img '.$default_src.' data-src="'.$data['no_ext_path'].'.'.$data['ext'].$data['upd_str'].'">';

		if($data['ext'] == 'webp'){
			$html = '<source srcset="'.$data['path'].'" type="image/'.$data['ext'].'">';
			$html .= '<img '.$default_src.' data-src="'.$data['no_ext_path'].'.png'.$data['upd_str'].'">';
		}

		return $html;
	}
}
