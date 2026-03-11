<?php

namespace Core\Database;

use Core\Model\Table;

class Agence extends Table
{

    protected static $table = 'Tb_Agence';

    public static function save($designation,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [designation] = ? WHERE [idAgence] = ?';
            $param = array(htmlentities($designation),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([designation]) VALUES (?) ';
            $param = array(htmlentities($designation));
            return self::query($sql, $param);
        }
    }
    
    public static function numberByType($type)
    {
		$sql = " SELECT COUNT(*) FROM [dbo].[". self::getTable() ."] WHERE [type] = '".$type."'";
		return self::querySelect($sql);
        
    }

}