<?php

namespace AvastBY\Thumbs;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;

class Scripts
{
    public static function postInstall(Event $event)
    {
        if (Schema::hasTable('data_types')) {
            if(!DB::table('data_types')->where('name', 'thumbs')->first()){
                DB::table('data_types')->insert(
                    [
                        'name' => 'thumbs',
                        'slug' => 'thumbs',
                        'display_name_singular' => 'Thumb',
                        'display_name_plural' => 'Thumbs',
                        'model_name' => 'AvastBY\Thumbs\Models\Thumb',
                        'generate_permissions' => 1,
                        'server_side' => 0,
                        'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                    ]
                );
            }

        }
        if (!Schema::hasTable('thumbs1')) {
            Schema::create('thumbs1', function (Blueprint $table) {
                $table->id();
                $table->string('mark');
                $table->integer('width')->default(0);
                $table->integer('height')->default(0);
                $table->integer('quality')->default(90);
                $table->smallInteger('fix_canvas')->default(1);
                $table->smallInteger('cover')->default(1);
                $table->smallInteger('upsize')->default(0);
                $table->string('canvas_color')->default('#ffffff');
                $table->timestamps();
            });
        }

    }

}
