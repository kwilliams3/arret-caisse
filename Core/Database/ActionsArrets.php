<?php

namespace Core\Database;

use Core\Model\Table;

class ActionsArrets extends Table
{

    protected static $table = 'Tb_ActionsArrets';
	
    public static function save($dateEntree,$designation,$delai,$pilotes,$idArretControle,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[designation] = ?,[delai] = ? ,[pilotes] = ?, [idArretControle] = ? WHERE [idActionsArrets] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($designation),htmlentities($delai),htmlentities($pilotes),htmlentities($idArretControle),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[designation],[delai],[pilotes],[idArretControle]) VALUES (?,?,?,?,?) ';
            $param = array(htmlentities($dateEntree),htmlentities($designation),htmlentities($delai),htmlentities($pilotes),htmlentities($idArretControle));
            return self::query($sql, $param);
        }
    }
	
	public static function SearchByArret($idArret)
    {
		$sql = " SELECT * FROM [dbo].[". self::getTable() ."] WHERE [idArretControle] = ".$idArret;
		return self::querySelect($sql);
        
    }
	

}