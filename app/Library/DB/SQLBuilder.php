<?php
/**
 * Classe
 *
 * @version    0.1
 * @author        Jonas Thomaz de Faria <jonas.talentfour@fs.com.br>
 */

namespace App\Library\DB;

use App\Library\DB\DB;

class SQLBuilder
{

    /**
     * Nome da tabela associada a Model
     *
     * @var string
     */
    protected $table;

    /**
     * Chave primária da tabela.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $where;
    protected $field;
    protected $limit;
    protected $orderBy;
    protected $result;
    protected $itaretor;
    protected $sql;
    protected $lastSql;
    protected $error;
    protected $error_message;
    protected $bindWhere;
    protected $lastBindWhere;
    protected $app;
    /**
     * Resultado da query
     */
    protected $data;

    public function __construct($app)
    {

    }

    public static function begin_transaction()
    {
        DB::begin_transaction();
    }

    /**
     * commit
     *
     */
    public static function commit(){
        DB::commit();
    }

    /**
     * rollback
     *
     */
    public static function rollback(){
        DB::rollback();
    }

    /**
     * Dinamicamente recupera atributos da Model
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dinamicamente seta atributos na model.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }


    /**
     * Suporta método mágico
     * @param string $key Chave do banco de dados
     * @return mixed
     */
    private function getAttribute($key)
    {

        if (is_array($this->data) && isset($this->data[$key])) {
            return $this->data[$key];

        } elseif (isset($this->data->$key)) {
            return $this->data->$key;
        }
        return false;
    }


    /**
     * Suporta método mágico
     * @param string $key Chave do banco de dados
     * @return mixed
     */
    private function setAttribute($key, $value)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key] = $value;
        }
        return false;
    }

    /**
     * @param $sql
     * @return array
     */
    public function run($sql)
    {
        return $this->data = DB::query($sql);
    }

    /**
     * @return \FsVivo\Libraries\Array
     */
    public function fetch($obj = false)
    {
        if (isset($this->data)) {
            return DB::fetch($this->data, $obj);
        }
        return false;
    }

    /**
     * carrega um registro
     *
     * @param integer $key Chave primaria da tabela
     * @return void
     */
    public function load($key)
    {
        $rs = DB::query('SELECT * FROM ' . $this->table . ' WHERE ' . $this->primaryKey . '=' . $key);
        $this->data = DB::fetch($rs);
        return $this;
    }

    /*
     * @param $sql, $args
     * Bind de query
     */
    public function prepared_query($sql, $args)
    {
        $stmt = DB::prepare($sql, $args);
        if ($stmt) {
            $this->data = $stmt->get_result();
            if(!$this->data)
                return $this->prepared_query($sql, $args);

            return $this->data;
        }
        return [];
    }

    /*
     * @param $sql, $args
     * Bind de query
     */
    public function prepared_query_update($sql, $args)
    {
        return DB::prepare($sql, $args);
    }

    /* @param $sql
     * @param $args
     * @return bool|\mysqli_result
     */
    public function prepared_query_insert($sql, $args)
    {
        return $this->data = DB::prepare($sql, $args);
    }

    public function orWhere($key, $value = '', $operator = '=')
    {
        $this->where($key, $operator, $value, 'or');
        return $this;
    }

    /*********************************************************
     * Constroi o Where do select
     * @param string $key
     * @param string $operator
     * @exemples1 where([ [key, =, value], [key, >, value], ... ])
     * @exemples2 where(' key = where ')
     * @exemples3 where(key, '=', value)
     * @exemples4 where([ [key, value], [key, >, value], ... ])
     * @return $this
     */
    public function where($key, $operator = '', $value = '', $type = 'and')
    {
        $num_args = func_num_args();

        if (empty($this->where)) {
            $this->where = " where ";
        } else {
            $this->where .= " $type ";
        }
        if ($num_args == 3) {
            $this->where .= "$key $operator ? ";
            $this->bindWhere[] = $value;
        }
        if ($num_args == 2) {
            $this->where .= $key . " = ? ";
            $this->bindWhere[] = $operator;
        }
        if ($num_args == 4) {
            $this->where .= "$key $operator ? ";
            $this->bindWhere[] = $value;
        }
        if ($num_args == 1) {
            $this->bindWhere = array();
            if (is_array($key)) {
                $first = true;
                if (is_array($key[0])) {
                    foreach ($key as $key2 => $value2) {
                        if (!$first) {
                            $this->where .= " $type ";
                        }
                        if (count($value2) == 2) {
                            $this->where .= " " . $value2[0] . " = ? ";
                            $this->bindWhere[] = $value2[1];
                        }
                        if (count($value2) == 3) {
                            $this->where .= $value2[0] . " " . $value2[1] . "? ";
                            $this->bindWhere[] = $value2[2];
                        }
                        if (count($value2) == '1') {
                            $this->where .= $value2[0];
                        }
                        $first = false;
                    }
                } else {
                    if (count($key) == 2) {
                        $this->where .= " " . $key[0] . " = ? ";
                        $this->bindWhere[] = $key[1];
                    }
                    if (count($key) == 3) {
                        $this->where .= $key[0] . " " . $key[1] . " ? ";
                        $this->bindWhere[] = $key[2];
                    }
                    if (count($key) == 1) {
                        $this->where .= $key[0];
                    }
                }
            } else {
                $this->where .= $key;
            }
        }
        return $this;
    }

    /**
     * @param $fields
     */
    public function field($fields)
    {
        if (is_array($fields)) {
            $first = true;
            foreach ($fields as $key => $value) {
                if (!$first) {
                    $this->field .= " , ";
                }
                $this->field .= $value;
                $first = false;
            }
        } else {
            $this->field = $fields;
        }
        return $this;
    }

    /**
     *
     */
    public function getField()
    {
        if (empty($this->field)) {
            $this->field('*');
        }
        return $this->field;
    }

    public function find($key)
    {
        return $this->where([$this->primaryKey, $key])->get();
    }

    public function all()
    {
        return $this->get();
    }

    public function first()
    {
        $this->limit('1');
        return $this->get();
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function limit($limit, $sec = false)
    {
        if (empty($this->getLimit())) {
            $this->limit = ' limit ';
        }
        $this->limit .= $limit;
        if (!empty($sec)) {
            $this->limit .= " " . $sec;
        }

        return $this;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function orderBy($orderBy, $ordem = '')
    {
        if (strtoupper($ordem) != 'asc') {
            $ordem = 'desc';
        }

        $this->orderBy = "ORDER BY $orderBy $ordem";
        return $this;
    }

    public function next($array = false)
    {
        if (empty($this->itaretor)) {
            $this->itaretor = 0;
        }
        if (is_array($this->data) && isset($this->data[$this->itaretor])) {
            if ($array) {
                $dados = $this->data[$this->itaretor];
            } else {
                $dados = (object)$this->data[$this->itaretor];
            }
            $this->itaretor++;
            return $dados;
        } elseif (is_object($this->data) && ((string)$this->itaretor != 'object')) {
            $this->itaretor = 'object';
            return $this->data;
        } else {
            $this->itaretor = null;
            return false;
        }
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function setSql($sql)
    {
        $this->sql = $sql;
    }


    public function lastSQL()
    {
        return array('query' => $this->lastSql, 'bind' => $this->lastBindWhere);
    }

    /**
     * Executa Queries
     * @param $sql
     * @return mixed|object
     * @ex: $obj->execute('select now()')
     */
    public function execute($sql)
    {
        $this->setSql($sql);
        return $this->get();
    }

    public function getBindWhere()
    {
        return $this->bindWhere;
    }

    /**
     * @param $datas : Array[key=>value] OR string
     */
    public function update($datas, $saveMode = 'NotAGoodIdeaMate')
    {
        $setters = '';
        $bindValues = array();
        if ($saveMode != 'ForceToSave') {
            if (empty($this->where)) {
                $this->error = true;
                $this->error_message = "Error: Savemode. Tenta executar sem condição no 'where' ";
                return false;
            }
        }
        if (is_array($datas)) {
            $first = true;
            foreach ($datas as $key => $val) {
                if (!$first) {
                    $setters .= ' , ';
                }
                $setters .= $key . ' = ? ';
                $bindValues[] = $val;
                $first = false;
            }

            $array_merged = array_merge($bindValues, $this->getBindWhere());
            $this->setSql("UPDATE " . $this->table . " set " . $setters . " " . $this->where);
            $result = $this->prepared_query_update($this->getSql(), $array_merged);
            $this->cleanUpLastQuery();
            return $result;
        }
        return false;
    }

    public function getQuery()
    {
        if (empty($this->sql)) {
            return "SELECT " . $this->getField() . " FROM " . $this->table . " " . $this->where . " " . $this->getOrderBy() . " " . $this->getLimit();
        }
    }

    public function get()
    {
        if (empty($this->sql)) {
            $this->setSql($this->builderQuery());
        }

        if (!empty($this->bindWhere)) {
            $result = $this->prepared_query($this->getSql(), $this->getBindWhere());
        } else {
            $result = DB::query($this->getSql());
        }
        $this->cleanUpLastQuery();
        if ($result) {
            $this->data = DB::fetchall($result);
            if (count($this->data) < 2) {
                if (isset($this->data[0])) {
                    $this->data = (object)$this->data[0];
                }
            }
            return $this->data;
        } else {
            $this->error = true;
            $this->error_message = "Error: You have got a  MySQL sintax error";
            return false;
        }
    }

    /**
     * @return string
     */
    public function builderQuery()
    {
        return "SELECT " . $this->getField() . " FROM " . $this->table . " " . $this->where . " " . $this->getOrderBy() . " " . $this->getLimit();
    }

    /**
     * @return string
     */
    public function showQuery()
    {
        return ['qry' => $this->builderQuery(), 'value' => $this->bindWhere];
    }

    public function cleanUpLastQuery()
    {
        $this->where = '';
        $this->lastSql = $this->getSql();
        $this->lastBindWhere = DB::getBindValues();
        $this->setSql('');
        $this->bindWhere = null;
    }

}