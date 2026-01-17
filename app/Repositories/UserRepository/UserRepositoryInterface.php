<?php
namespace App\Repositories\UserRepository;

use App\Repositories\RepositoryInterface;


interface UserRepositoryInterface extends RepositoryInterface
{
    public function getInfoUser();

    public function getPermission();

    public function responseWithToken($token);

    public function find($id);

    public function findByEmail($email);
}
