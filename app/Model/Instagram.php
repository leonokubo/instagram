<?php
/**
 * Created by PhpStorm.
 * User: leon.okubo
 * Date: 26/10/2018
 * Time: 21:01
 */

namespace App\Model;


class Instagram extends Model
{
    protected $table = 'cloudVision.instagram';
    protected $id = 'id';

    public function getUser($user)
    {
        return parent::where('user', $user)->first();
    }

    public function insert($user)
    {
        $qry = "INSERT INTO cloudVision.instagram
        (user, lastId, maxId) 
        VALUES
        (?,null,null) on duplicate key update modify_date = current_timestamp;";

        $insert = $this->prepared_query_insert($qry, [
                (int)$user
            ]
        );

        if($insert->affected_rows == 1)
            return true;

        return false;
    }

    public function setLastUpdate($user, $lastId, $maxId)
    {
        $update = "UPDATE cloudVision.instagram SET lastId = ?, maxId = ?
                   WHERE user = ?;";

        return $this->prepared_query_update($update, [(int)$lastId, (string)$maxId, (int)$user]);
    }
}