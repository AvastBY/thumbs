<?php

namespace Avast\Thumbs\Commands;


use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use DB;
use Carbon\Carbon;

class InstallCommand extends Command
{

    protected $name = 'avast-thumbs:install';

    protected $description = 'Install the Avast Thumbs package';


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
         $this->info('Avast Thumbs installing');
        if (Schema::hasTable('thumbs')) {
            $this->info('Thumbs table already exists');
        }else{
            Schema::create('thumbs', function (Blueprint $table) {
                $table->id();
                $table->string('mark')->unique();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->tinyInteger('cover')->nullable();
                $table->tinyInteger('fix_canvas')->nullable();
                $table->tinyInteger('upsize')->nullable();
                $table->integer('quality')->nullable()->default(90);
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
                        'model_name' => 'Avast\Thumbs\Models\Thumb',
                        'generate_permissions' => 1,
                        'server_side' => 0,
                        'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                    ]
                );
            }
            $data_type = DB::table('data_types')->where('name', 'thumbs')->first();
            if ($data_type && Schema::hasTable('data_rows')) {
                if(!DB::table('data_rows')->where('data_type_id', $data_type->id)->first()){
                    $data = [
                        ['data_type_id' => $data_type->id, 'field' => 'id', 'type' => 'text', 'display_name' => 'id', 'required' => 1, 'browse' => 0, 'read' => 0, 'edit' => 0, 'add' => 0, 'delete' => 0, 'details' => '{}', 'order' => 1],
                        ['data_type_id' => $data_type->id, 'field' => 'mark', 'type' => 'text', 'display_name' => 'Mark', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 2],
                        ['data_type_id' => $data_type->id, 'field' => 'width', 'type' => 'number', 'display_name' => 'Width', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 3],
                        ['data_type_id' => $data_type->id, 'field' => 'height', 'type' => 'number', 'display_name' => 'Height', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 4],
                        ['data_type_id' => $data_type->id, 'field' => 'cover', 'type' => 'checkbox', 'display_name' => 'Cover', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 5],
                        ['data_type_id' => $data_type->id, 'field' => 'fix_canvas', 'type' => 'checkbox', 'display_name' => 'Fix Canvas', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 6],
                        ['data_type_id' => $data_type->id, 'field' => 'upsize', 'type' => 'checkbox', 'display_name' => 'Upsize', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 7],
                        ['data_type_id' => $data_type->id, 'field' => 'quality', 'type' => 'number', 'display_name' => 'Quality', 'required' => 0, 'browse' => 1, 'read' => 1, 'edit' => 1, 'add' => 1, 'delete' => 1, 'details' => '{}', 'order' => 8],
                        ['data_type_id' => $data_type->id, 'field' => 'created_at', 'type' => 'timestamp', 'display_name' => 'Created At', 'required' => 0, 'browse' => 0, 'read' => 0, 'edit' => 0, 'add' => 0, 'delete' => 0, 'details' => '{}', 'order' => 9],
                        ['data_type_id' => $data_type->id, 'field' => 'updated_at', 'type' => 'timestamp', 'display_name' => 'Updated At', 'required' => 0, 'browse' => 0, 'read' => 0, 'edit' => 0, 'add' => 0, 'delete' => 0, 'details' => '{}', 'order' => 10],
                    ];

                    DB::table('data_rows')->insert($data);
                }
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

        $this->info('Avast Thumbs installed');

        Artisan::call('vendor:publish --tag=ava-thumbs-routes');
        Artisan::call('cache:clear');
    }

}
