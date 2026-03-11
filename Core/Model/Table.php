<?php

namespace Core\Model;

use Core\Model\App;

class Table{

    protected static $table;
    protected static $test;

    public static function getTable(){
        if(static::$table === null) {
            $class_name = explode('\\', get_called_class());
            static::$table = end($class_name);
        }
        return static::$table;
    }

    public static function allOther(){
        return App::getDB()->query("SELECT * FROM [dbo].[".static::$table."]",NULL,true);
    }

    public static function all(){
        static::$test = explode('_', static::$table);
        return App::getDB()->query("SELECT * FROM [dbo].[".static::$table."] ORDER BY [id".static::$test[1]."] DESC");
    }

    protected  static function selectString(){
        return 'SELECT * FROM [dbo].['.static::getTable().']';
    }
	
	public static function querySelect($statement, $attributes = null,$sage = false, $adresseIP = null, $baseDonnees = null, $serveur = null)
    {
        return App::getDB()->query($statement,$attributes,$sage,$adresseIP, $baseDonnees,$serveur);

    }

    public static function queryCount($statement,$sage = false)
    {
        return App::getDB()->queryCount($statement,$sage);

    }

    public static function query($statement, $attributes = null)
    {
        return App::getDB()->Prepare($statement,$attributes);
    }

    public static function delete($id){
        static::$test = explode('_', static::$table);
        $sql = "DELETE FROM [dbo].[". self::getTable() ."] WHERE [id".static::$test[1]."] = ?";
        $param = array(&$id);
        return self::query($sql,$param);
    }

    public static function searchById($id){
        static::$test = explode('_', static::$table);
        $sql = "SELECT * FROM [dbo].[". self::getTable() ."] WHERE [id".static::$test[1]."] = ?";
        $param = array(&$id);
        return self::querySelect($sql,$param);
    }


}

?>