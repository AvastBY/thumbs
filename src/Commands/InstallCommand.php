<?php

namespace Ava\Thumbs\Commands;


use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use DB;
use Carbon\Carbon;

class InstallCommand extends Command
{

    protected $name = 'ava-thumbs:install';

    protected $description = 'Install the Ava Thumbs package';


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
         $this->info('Ava Thumbs installing');
        if (Schema::hasTable('thumbs')) {
            $this->info('Thumbs table already exists');
        }else{
            Schema::create('thumbs', function (Blueprint $table) {
                $table->id();
                $table->string('mark')->unique();
                $table->integer('width');
                $table->integer('height');
                $table->tinyInteger('cover');
                $table->tinyInteger('fix_canvas');
                $table->tinyInteger('upsize');
                $table->integer('quality')->default(90);
                $table->timestamps();
            });

            $this->info('Thumbs table created');
        }

        if (Schema::hasTable('data_types')) {
            if(!DB::table('data_types')->where('name', 'thumbs')->first()){
                DB::table('data_types')->insert(
                    [
                        'name' => 'thumbs',
                        'slug' => 'thumbs',
                        'display_name_singular' => 'Thumb',
                        'display_name_plural' => 'Thumbs',
                        'model_name' => 'Ava\Thumbs\Models\Thumb',
                        'generate_permissions' => 1,
                        'server_side' => 0,
                        'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                    ]
                );
            }
        }
        if (Schema::hasTable('menu_items')) {
            if(!DB::table('menu_items')->where([['menu_id', 1], ['parent_id', 5],['title', 'Thumbs']])->first()){
                DB::table('menu_items')->insert(
                    [
                        'menu_id' => 1,
                        'title' => 'Thumbs',
                        'url' => '',
                        'target' => '_self',
                        'icon_class' => 'voyager-resize-full',
                        'color' => '#000000',
                        'parent_id' => '5',
                        'order' => '50',
                        'route' => 'voyager.thumbs.index',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );
                $this->info('Menu item created');
            }
        }
        if (Schema::hasTable('permissions')) {
            $permissions = ['browse_thumbs','read_thumbs','edit_thumbs','add_thumbs','delete_thumbs'];
            if(!DB::table('permissions')->where('table_name', 'thumbs')->whereIn('key', $permissions)->count()){
                $data = [];
                foreach ($permissions as $key => $permission) {
                    $data[] = [
                        'key' => $permission,
                        'table_name' => 'thumbs',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                DB::table('permissions')->insert($data);
            }

            if (Schema::hasTable('permission_role')) {
                $permission_ids = DB::table('permissions')->where('table_name', 'thumbs')->whereIn('key', $permissions)->pluck('id')->toArray();
                if(!DB::table('permission_role')->whereIn('permission_id', $permission_ids)->count()) {
                    if ($permission_ids) {
                        $data = [];
                        foreach ($permission_ids as $key => $id) {
                            $data[] = ['permission_id' => $id, 'role_id' => 1];
                        }

                        DB::table('permission_role')->insert($data);

                        $this->info('Permissions created');
                    }
                }
            }
        }
//        $this->executeArtisanProcess('vendor:publish', [
//            '--provider' => 'Prologue\Alerts\AlertsServiceProvider',
//        ]);

        $this->info('Ava Thumbs installed');

        Artisan::call('vendor:publish --tag=ava-thumbs-routes');
        Artisan::call('cache:clear');
    }

}
