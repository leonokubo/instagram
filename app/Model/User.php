<?php

/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 19/10/2018
 * Time: 20:14
 */

namespace App\Model;

class User extends Model
{
    protected $table = 'cloudVision.user';
    protected $id = 'id';

    public function getUser($user)
    {
        return parent::where('user', $user)->first();
    }

    public function insert($user)
    {
        $qry = "INSERT INTO cloudVision.user 
                (user)
                VALUES(?);";

        $insert = $this->prepared_query_insert($qry, [
                (string)$user
            ]
        );

        if($insert->affected_rows == 1)
            return true;

        return false;
    }
}