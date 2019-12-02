<?php
namespace Syncer\Models;

class TogglProject extends \App\Models\EntityModel {

    public $timestamps = false;
    protected $table = 'toggl_projects';

    public static function getSelectOptions(){
        return TogglProject::query()->get();
    }
}