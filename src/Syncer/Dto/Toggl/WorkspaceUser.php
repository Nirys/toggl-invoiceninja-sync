<?php declare(strict_types=1);

namespace Syncer\Dto\Toggl;

use App\Models\User;
use Syncer\InvoiceNinja\Mapper;

/**
 * Class WorkspaceUser
 * @package Syncer\Dto
 *
 * @author Kath Young <kath2young@gmail.com>
 */
class WorkspaceUser
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $fullname;

    /**
     * @var string
     */
    private $imageUrl;


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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email){
        $this->email = $email;
    }

    public function getFullname(){
        return $this->fullname;
    }

    public function setFullname(string $fullname){
        $this->fullname = $fullname;
    }

    public function getImageUrl(){
        return $this->imageUrl;
    }

    public function setImageUrl(string $image_url){
        $this->imageUrl = $image_url;
    }

    public function getFirstName(){
        return preg_split("/ /", $this->fullname)[0];
    }

    public function getLastName(){
        $name = preg_split("/ /", $this->fullname);
        return $name[sizeof($name)-1];
    }

    public function sync(){
        $user = Mapper::getUser($this);
        if(!$user){
            $user = new \App\Models\User();
            $user->account_id = 1;
            $user->password = 'abc123';
            $user->username = str_replace(" ","_", strtolower($this->getFullname()));
            $user->is_admin = 0;
        }
        $user->email = $this->getEmail();
        $user->toggl_id = $this->getId();
        $user->first_name = $this->getFirstName();
        $user->last_name = $this->getLastName();
        $user->save();
        return $user;
    }
}
