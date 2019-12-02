<?php declare(strict_types=1);

namespace Syncer\Dto\Toggl;

use App\Models\User;
use Syncer\InvoiceNinja\Mapper;
use Syncer\Models\TogglClient;
use Syncer\Models\TogglProject;

/**
 * Class WorkspaceUser
 * @package Syncer\Dto
 *
 * @author Kath Young <kath2young@gmail.com>
 */
class WorkspaceProject
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $wid;

    /**
     * @var string
     */
    private $name;

    /** @var integer */
    private $clientId;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getWid(): int
    {
        return $this->wid;
    }

    /**
     * @param mixed $id
     */
    public function setWid(int $wid)
    {
        $this->id = $wid;
    }

    public function getClientId(){
        return $this->clientId;
    }

    public function setClientId($id){
        $this->clientId = $id;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return TogglProject
     */
    public function sync(){
        $project = TogglProject::where('toggl_id', $this->getId())->first();
        if(!$project){
            $project = new \Syncer\Models\TogglProject();
            $project->toggl_id = $this->getId();
        }
        $project->name = $this->getName();
        $project->wid = $this->getWid();
        $project->client_id = $this->clientId;
        $project->save();
        return $project;
    }
}
