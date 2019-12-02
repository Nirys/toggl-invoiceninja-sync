<?php declare(strict_types=1);

namespace Syncer\Dto\Toggl;

use App\Models\User;
use Syncer\InvoiceNinja\Mapper;
use Syncer\Models\TogglClient;

/**
 * Class WorkspaceUser
 * @package Syncer\Dto
 *
 * @author Kath Young <kath2young@gmail.com>
 */
class WorkspaceClient
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
     * @return TogglClient
     */
    public function sync(){
        $client = TogglClient::where('toggl_id', $this->getId())->first();
        if(!$client){
            $client = new \Syncer\Models\TogglClient();
            $client->toggl_id = $this->getId();
        }
        $client->name = $this->getName();
        $client->wid = $this->getWid();
        $client->save();
        return $client;
    }
}
