<?php
/**
 * Classe
 * 
 * @version     0.1
 * @author      Jonas Thomaz de Faria <jonas.talentfour@fs.com.br>
 */ 
namespace App\Model;
use App\Library\DB\SQLBuilder;
use App\Library\Cache;

class Model extends SQLBuilder {

    public function __construct()
    {
    }

    public static function modelCache()
    {
        return new Cache();
    }

    public function all()
    {
        return parent::all();
    }

    /**
     *
     */
    public function find($key)
    {
        return parent::find($key);
    }

    /**
     *
     */
    public function alter()
    {
        //building a beautiful code
    }

    /**
     *
     */
    public function delete()
    {
        //building a beautiful code
    }

    public static function begin_transaction()
    {
        SQLBuilder::begin_transaction();
    }

    /**
     * commit
     *
     */
    public static function commit(){
        SQLBuilder::commit();
    }

    /**
     * rollback
     *
     */
    public static function rollback(){
        SQLBuilder::rollback();
    }
}