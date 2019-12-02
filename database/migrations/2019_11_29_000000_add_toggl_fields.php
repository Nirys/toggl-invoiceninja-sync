<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTogglFields extends Migration
{
    protected $tables = ['users','tasks','clients','projects'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach($this->tables as $table){
            $this->addTogglField($table);
        }

        Schema::create('toggl_clients', function (Blueprint $blueprint){
            $blueprint->bigIncrements('id');
            $blueprint->string('toggl_id');
            $blueprint->bigInteger('wid');
            $blueprint->string('name');
            $blueprint->index(['toggl_id']);
        });

        Schema::create('toggl_projects', function (Blueprint $blueprint){
            $blueprint->bigIncrements('id');
            $blueprint->string('name');
            $blueprint->string('toggl_id');
            $blueprint->bigInteger('client_id')->nullable();
            $blueprint->bigInteger('wid');
            $blueprint->index(['toggl_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach($this->tables as $table){
            $this->removeTogglField($table);
        }

        Schema::dropIfExists('toggl_clients');
        Schema::dropIfExists('toggl_projects');
    }

    protected function removeTogglField($fromTable){
        Schema::table($fromTable, function (Blueprint $blueprint){
            $blueprint->dropColumn('toggl_id');
        });
    }

    protected function addTogglField($toTable){
        Schema::table($toTable, function (Blueprint $blueprint){
            $blueprint->string('toggl_id', 50)->nullable();
            $blueprint->index(['toggl_id']);
        });
    }
}
