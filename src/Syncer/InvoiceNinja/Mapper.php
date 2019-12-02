<?php
namespace Syncer\InvoiceNinja;

class Mapper {

    static $clients = null;
    static $projects = null;

    /**
     * @param $togglClient
     * @return \App\Models\Client|null
     */
    public static function getClient(
        $togglClient
    ) {
        return static::getMappedObject(\App\Models\Client::class, static::getClients(), $togglClient);
    }

    /**
     * @param $togglProject
     * @return \App\Models\Project|null
     */
    public static function getProject(
        $togglProject
    ) {
        return static::getMappedObject(\App\Models\Project::class, static::getProjects(), $togglProject);
    }

    public static function getUser(
        \Syncer\Dto\Toggl\WorkspaceUser $togglUser
    ) {
        $user = static::getMappedObject(\App\Models\User::class, [], $togglUser->getId());
        if(!$user){
            $user = \App\Models\User::where('email', $togglUser->getEmail())->first();
            if($user){
                $user->toggl_id = $togglUser->getId();
                $user->save();
            }
        }
        return $user;
    }

    protected static function getMappedObject(
        $model,
        $mappingArray,
        $togglId
    ) {
        if($togglId == '') return null;
        $foundModel = $model::where('toggl_id', $togglId)->first();
        if($foundModel){
            return $foundModel;
        }

        $modelId = isset($mappingArray[$togglId]) ? $mappingArray[$togglId] : null;
        if($modelId == null){
            return null;
        }
        $foundModel = $model::find($modelId);
        if($foundModel){
            $foundModel->toggl_id = $togglId;
            $foundModel->save();
            return $foundModel;
        }else{
            return null;
        }
    }

    /**
     * @return array
     */
    protected static function getClients(){
        if(static::$clients == null){
            static::$clients = config('toggl.clients');
        }
        return static::$clients;
    }

    /**
     * @return array
     */
    protected static function getProjects(){
        if(static::$clients == null){
            static::$clients = config('toggl.projects');
        }
        return static::$clients;
    }
}