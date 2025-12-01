<?php
/******************************************************************
# Wallet--- Wallet                                                *
# ----------------------------------------------------------------*
# author    Webkul                                                *
# copyright Copyright (C) 2010 webkul.com. All Rights Reserved.   *
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL     *
# Websites: http://webkul.com                                     *
*******************************************************************
*/


namespace WalletSystemModel;

use Tygh\Registry;
use Exception;

include_once(Registry::get("config.dir.addons").'/wallet_system/config.php');


Class BaseModel {
    
    private $data;

    private $logId;
    
    protected $auth;

    protected $cart;
    
    public function __construct() {
        $this->logId = TIME;
        $this->auth = $_SESSION['auth'];
        $this->cart = $_SESSION['cart'];
        // Here base model is loaded
    }

    /**
     * 
     * @param string $varName
     * 
     * @return string|array|object
     */
    public function __get($varName){       
        
        if (!array_key_exists($varName,$this->data)){
            throw new Exception('.....');
        }
        else return $this->data[$varName];
    }

    /**
     * 
     * 
     * @return void
     */
    public function __set($varName,$value){
        $this->data[$varName] = $value;
    }

    /**
     * 
     * @param string
     * @param string|array
     * @param array
     * @param string
     * 
     * @return string|array|object|void
     */
    public function select($table,$selection,$where,$func) {
        $selection = (is_array($selection)) ? implode(",", $selection) : $selection;
        $query = "SELECT $selection FROM ?:$table";
        if(!empty($where)){
            $query .= " WHERE 1 ";
            if(is_array($where)){
                foreach($where as $column => $values) {
                    if(is_array($values)){
                        if($this->checkWhereClause($values[1])) {
                            $value = "('" . implode ( "', '", $values[0] ) . "')";
                        } else {
                            $value = $this->checkIsString($values[0]);
                        }
                        $constraint = $values[1];
                    } else {
                        $value = $this->checkIsString($values);
                        $constraint = '=';
                    }
                    $query .= "AND $column $constraint $value ";
                }
            }else{
                $query .= " $where";
            }
        }
        try {            
            $result = $func($query);
            return $result;
        } catch (Exception $e) {            
            $this->writeLog(MYSQL_ERROR_LOG,$e->getMessage());
        }
    }


    /**
     * 
     * @param string
     * @param string|array
     * @param string|array
     * @param string
     * @param string
     * @param array|string|void
     * @param array|string|void
     * @param array|string|void
     * 
     * @return string|array|object|void
     */
    public function mtSelect($table, $selection, $where, $func, $join='', $orderBy='', $limit='', $offset='') {
        $selection = (is_array($selection)) ? implode(",", $selection) : $selection;
        $query = "SELECT $selection FROM ?:$table";


        if(!empty($join)){
            $query .= " $join ";
        }


        if(!empty($where)){
            $query .= " WHERE 1 ";

            if(is_array($where)){
                foreach($where as $column => $values) {
                    if(is_array($values)){
                        if($this->checkWhereClause($values[1])) {
                            $value = "('" . implode ( "', '", $values[0] ) . "')";
                        } else {
                            $value = $this->checkIsString($values[0]);
                        }
                        $constraint = $values[1];
                    } else {
                        $value = $this->checkIsString($values);
                        $constraint = '=';
                    }
                    $query .= "AND `$column` $constraint $value ";
                }
            }else{
                $query .= "AND $where ";
            }
        }
        
        if(!empty($orderBy)){
            $order = is_array($orderBy) ? $orderBy[0] : $orderBy;
            $query .= "ORDER BY $order";
            if(is_array($orderBy) && isset($orderBy[1])) {
                $query .= " $orderBy[1]";
            }
        }
        if(!empty($limit)){
            $data = (!empty($offset)) ? $offset.', '.$limit : $limit;
            $query .= " LIMIT $data";
        }
        try {
            $result = $func($query);
            return $result;
        } catch (Exception $e) {
            $this->writeLog(MYSQL_ERROR_LOG, $e->getMessage());
        }
    }





    /**
     * 
     * @param string
     * @param array
     * 
     * @return void
     */
    public function insert($table,$params){
        
        $query = "INSERT INTO ?:$table ?e";

        try {
            return db_query($query,$params);
        } catch (Exception $e) {
            
            $this->writeLog(MYSQL_ERROR_LOG,$e->getMessage());
        }
        
    }

    /**
     * 
     * @param string
     * @param array
     * 
     * @return void
     */
    public function replace($table,$params){
        
        $query = "REPLACE INTO ?:$table ?e";
        try {
            db_query($query,$params);
        } catch (Exception $e) {
            $this->writeLog(MYSQL_ERROR_LOG,$e->getMessage());
        }
        
    }

    /**
     * 
     * @param string
     * @param array
     * @param array
     * 
     * @return void
     */
    public function update($table,$params,$where=array()){
        $query = "UPDATE ?:$table SET ?u";
        if(!empty($where)){
            $query .= " WHERE ";
            $condition = ' AND ';

            $extraWhere = true;
            $lastIndex = 0; // check last index
            $totalCondition = count($where); // check total where condition.
            
            if(count($where) > 1 ) {
                $extraWhere = false;
            }

            foreach($where as $column => $value) {
                if(is_array($value)){
                    $value = $this->checkIsString($value[0]);
                    $constraint = $value[1];
                } else {
                    $value = $this->checkIsString($value);
                    $constraint = '=';
                }

                // this will work for single where condition;
                if($extraWhere) {

                    $query .= "$column $constraint $value ";

                } else {
                    // this will work for multiple where condition
                    if(++$lastIndex === $totalCondition) {
                        $query .= "$column $constraint $value";
                    } else {
                        $query .= "$column $constraint $value $condition";
                    }
                    
                }
            }
        }

        try {
            db_query($query,$params);
        } catch (Exception $e) {
            $this->writeLog(MYSQL_ERROR_LOG,$e->getMessage());
        }
        
    }

    /**
     * 
     * @param string
     * @param array
     * 
     * @return void
     */
    public function delete($table,$where=array()){
        $query = "DELETE FROM ?:$table";
        if(!empty($where)){
            $query .= " WHERE ";
            foreach($where as $column => $value) {
                if(is_array($value)){
                    $value = $this->checkIsString($value[0]);
                    $constraint = $value[1];
                } else {
                    $value = $this->checkIsString($value);
                    $constraint = '=';
                }
                $query .= "$column $constraint $value ";
            }

            try {
                db_query($query);
            } catch (Exception $e) {                
                $this->writeLog(MYSQL_ERROR_LOG,$e->getMessage());
            }
        }
    }

    public function deleteMoreCondition($table,$where = array()) {        
        $query = "DELETE FROM ?:$table";
        if(!empty($where)){
            $query .= " WHERE ";
            $condition = ' AND ';

            $extraWhere = true;
            $lastIndex = 0; // check last index
            $totalCondition = count($where); // check total where condition.
            
            if(count($where) > 1 ) {
                $extraWhere = false;
            }

            foreach($where as $column => $values) {
                if(is_array($values)){
                    if($this->checkWhereClause($values[1])) {
                        $value = "('" . implode ( "', '", $values[0] ) . "')";
                    } else {
                        $value = $this->checkIsString($values[0]);
                    }
                    $constraint = $values[1];
                } else {
                    $value = $this->checkIsString($values);
                    $constraint = '=';
                }

                // this will work for single where condition;
                if($extraWhere) {

                    $query .= "$column $constraint $value ";

                } else {
                    // this will work for multiple where condition
                    if(++$lastIndex === $totalCondition) {
                        $query .= "$column $constraint $value";
                    } else {
                        $query .= "$column $constraint $value $condition";
                    }
                    
                }
            }
        }

        
        try {
            return db_query($query);
        } catch (Exception $e) {
        
            $this->writeLog(MYSQL_ERROR_LOG,$e->getMessage());
        }
    }

    /**
     * @param string
     * 
     * @return string
     */
    private function checkIsString($value) {
        return (is_string($value)) ? "\"$value\"" : $value;
    }

    /**
     * @param string
     * @param array
     * 
     * @return string|array|void
     */
    public function writeLog($file,$contents) {
        $file = fopen(MYSQL_ERROR_LOG, "r");
        $contents = $this->logId." ".date("Y-m-d h:i:s",TIME)." ".$contents."\n";
        fwrite($file, $contents);
        fclose($file);
    }

    /**
     * Check Where clause exist or not.
     * 
     * @param string
     * 
     * @return bool
     */
    private function checkWhereClause($value) {
        $allowedClause = array('NOT IN','IN');
        return in_array($value,$allowedClause);
    }
}
?>
