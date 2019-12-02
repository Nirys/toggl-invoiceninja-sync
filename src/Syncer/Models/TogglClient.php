<?php
namespace Syncer\Models;

class TogglClient extends \App\Models\EntityModel {

    public $timestamps = false;
    protected $table = 'toggl_clients';

    public static function getSelectOptions(){
        return TogglClient::query()->get();
    }

}