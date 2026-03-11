<?php

namespace Core\Database;

use Core\Model\Table;

class ArretControleSage extends Table
{

    protected static $table = 'Tb_ArretControleSage';

    public static function save($dateEntree,$controlePhysique,$commentaires,$idArretDouanier,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[controlePhysique] = ?,[commentaires] = ?,[idArretDouanier] = ? WHERE [idArretControleSage] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($controlePhysique),htmlentities($commentaires),htmlentities($idArretDouanier),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[controlePhysique],[commentaires],[idArretDouanier]) VALUES (?,?,?,?) ';
            $param = array(htmlentities($dateEntree),htmlentities($controlePhysique),htmlentities($commentaires),htmlentities($idArretDouanier));
            return self::query($sql, $param);
        }
    }
	
	 public static function lastInsert(){
        $sql = 'SELECT MAX([idArretControleSage]) AS last FROM [dbo].['.self::getTable().']';
        return self::querySelect($sql);
    }
	
	
	public static function SearchByArret($idArret)
    {
		$sql = " SELECT * FROM [dbo].[". self::getTable() ."] WHERE [idArretDouanier] = ".$idArret;
		return self::querySelect($sql);
        
    }
	
	public static function oldCaisseAgence($today,$idAgence)
    {
		$sql = " SELECT * FROM [dbo].[". self::getTable() ."] WHERE [dateEntree] = '".$today."' AND [idAgence] = ".$idAgence;
		return self::querySelect($sql);
        
    }
	
	public static function oldCaisseDay($today)
    {
		$sql = " SELECT * FROM [dbo].[". self::getTable() ."] WHERE [dateEntree] = '".$today."'";
		return self::querySelect($sql);
        
    }
	

}