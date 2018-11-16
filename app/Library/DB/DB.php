<?php
namespace App\Library\DB;
error_reporting(E_ERROR );

class DB{
    public  static $instance;
    private static $host;
    private static $user;
    private static $pw;
    private static $data_base;
    private static $port;
    protected static $bindValues;

    /*statement*/
    private $stmt;

    private function __construct(){
        date_default_timezone_set('America/Sao_Paulo');
    }

    public static function getInstance(){
        if(!isSet($_SERVER['conexao_db']) || $_SERVER['conexao_db'] == false) {
            self::$host = MYSQL_CONF['HOST'];
            self::$user = MYSQL_CONF['USER'];
            self::$pw = MYSQL_CONF['PASS'];
            self::$data_base = 'billing';
            self::$port = MYSQL_CONF['PORT'];
            self::connect();
        }

        return self::$instance = $_SERVER['conexao_db'];
    }

    /**
     * connect
     *
     */
    public static function connect(){
        while (!$_SERVER['conexao_db'] = mysqli_connect(self::$host, self::$user, self::$pw, self::$data_base, self::$port)) {
            sleep(1);
        }

        self::query('set innodb_lock_wait_timeout=900');
        self::query("SET time_zone='America/Sao_Paulo'");
    }

    /**
     * disconnect
     *
     */
    public static function disconnect(){
        mysqli_close(self::$instance);
        self::$instance = false;
    }

    /**
     * reconnect
     *
     */
    public static function reconnect(){
        self::disconnect();
        self::connect();
        self::getInstance();
    }

    /**
     * query
     * @param string $qry Comando a ser executado
     *
     * @return array
     *
     */
    public static function query($qry){
        if(self::$instance == false || self::$instance->errno == '2006') {
            self::getInstance();
        }
        $tries = 0;
        do{
            $tries++;
            $res = mysqli_query(self::$instance, $qry);
            $err = mysqli_error(self::$instance);
            $errno = mysqli_errno(self::$instance);
            if($errno == 2006) {
                self::reconnect();
                sleep(1);
            }
        }while($tries <= 5 && !in_array($errno, [0, 1064]));
        if (strLen(trim($err)) > 0) {
            error_log($err);
            \Logger::getLogger('error')->warn($err);
            self::disconnect();
            return false;
        }
        return $res;
    }

    /**
     * fetch
     *
     * @param resource $result O resource do resultado que está sendo avaliado.
     *
     * @return Array
     *
     */
    public static function fetch($result, $object = false)
    {
        if($object){
            return mysqli_fetch_object($result);
        }else{
            return mysqli_fetch_assoc($result);
        }
    }

    public static function fetchall($result, $associative = true)
    {
        if($associative) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return $result->fetch_all(MYSQLI_NUM);
        }
    }

    /**
     * rows
     *
     * @param resource $result O resource do resultado que está sendo avaliado.
     *
     * @return Array
     *
     */
    public static function rows($result){
        return mysqli_num_rows($result);
    }

    /**
     * affect_rows
     *
     * @return int
     *
     */
    public static function affect_rows(){
        if(self::$instance == false || self::$instance->errno == '2006') {
            self::getInstance();
        }
        return mysqli_affected_rows(self::$instance);
    }

    /**
     * escape
     *
     * @param String $w String a ser tratada
     *
     * @return String
     *
     */
    public static function escape($w) {
        if(self::$instance == false || self::$instance->errno == '2006') {
            self::getInstance();
        }
        return mysqli_real_escape_string(self::$instance, $w);
    }

    /**
     * lastID
     *
     * @return int
     *
     */
    public static function lastID() {
        return mysqli_insert_id(self::$instance);
    }

    /**
     * affectedRows
     *
     * @return int
     *
     */
    public static function affectedRows() {
        return mysqli_affected_rows(self::$instance);
    }

    /**
     * matchedRows
     *
     * @return String
     *
     */
    public static function matchedRows() {
        $tmp = explode(' ', mysqli_info(self::$instance));
        return $tmp[2];
    }

    /**
     * sanitizeValues
     *
     * @param Array $arr
     *
     */
    public static function sanitizeValues(&$arr) {
        foreach ($arr as $key => $val) {
            $arr[$key] = strip_tags($val);
        }
    }

    /**
     * escapeValues
     *
     * @param Array $arr
     *
     */
    public static function escapeValues(&$arr) {
        foreach ($arr as $key => $val) {
            $arr[$key] = escape($val);
        }
    }

    /**
     * autocommit
     *
     * @param boolean $mode Comando para ativar o auto commit.
     *
     */
    public static function autocommit($mode = false){
        if(self::$instance == false || self::$instance->errno == '2006') {
            self::getInstance();
        }
        mysqli_autocommit(self::$instance, $mode);
    }

    /**
     * begin_transaction
     *
     */
    public static function begin_transaction(){
        if(self::$instance == false || self::$instance->errno == '2006') {
            self::getInstance();
        }
        mysqli_begin_transaction(self::$instance);
    }

    /**
     * commit
     *
     */
    public static function commit(){
        mysqli_commit(self::$instance);
    }

    /**
     * rollback
     *
     */
    public static function rollback(){
        mysqli_rollback(self::$instance);
    }

    /**
     * @param $qry
     * @param $args
     * @return bool|\mysqli_result
     */
    public static function prepare($qry, $args, $reconnect = 0){
        if(self::$instance == false || self::$instance->errno == '2006') {
            self::getInstance();
        }

        $stmt = mysqli_prepare(self::$instance, $qry);
        if($stmt) {
            $params = [];
            $types  = array_reduce($args, function ($string, &$arg) use (&$params) {
                $params[] = &$arg;
                if (is_float($arg))         $string .= 'd';
                elseif (is_integer($arg))   $string .= 'i';
                elseif (is_string($arg))    $string .= 's';
                else                        $string .= 'b';
                return $string;
            }, '');
            array_unshift($params, $types);
            call_user_func_array([$stmt, "bind_param"], $params);

            self::$bindValues = $params;
            $stmt->execute();

            if($stmt->errno == '2006' && $reconnect < 10){
                $reconnect++;
                self::reconnect();
                sleep(1);
                return self::prepare($qry, $args, $reconnect++);
            }

            if($stmt->error != ""){
                \Logger::getLogger('error')->error($qry . " - " . print_r($args, true) . " - " .$stmt->error);
            }

            return $stmt;
        }else{
            if((self::$instance == false || self::$instance->errno == '2006' || self::$instance->affected_rows == -1) && $reconnect < 10) {
                $reconnect++;
                self::reconnect();
                sleep(1);
                return self::prepare($qry, $args, $reconnect);
            }
            else{
                error_log(self::$instance->error);
                \Logger::getLogger('error')->error(print_r(self::$instance, true));
                \Logger::getLogger('error')->error("[$reconnect] :: $qry");
                return false;
            }
        }
    }

    public static function getBindValues()
    {
        return self::$bindValues;
    }
}