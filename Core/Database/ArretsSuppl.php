<?php

namespace Core\Database;

use Core\Model\Table;

class ArretsSuppl extends Table
{

    protected static $table = 'Tb_ArretsSuppl';
	
	
    public static function save($dateEntree,$avarieAgence,$gainPromo,$transfertCarte,$manutentionHorsFDR,$reglementRemiseX3,$resteFDR,$fondCaisse,$idArretsCaisses,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[avarieAgence] = ?,[gainPromo] = ? ,[transfertCarte] = ?, [manutentionHorsFDR] = ?, [reglementRemiseX3] = ?, [resteFDR] = ?, [fondCaisse] = ?, [idArretsCaisses] = ? WHERE [idArretsSuppl] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($avarieAgence),htmlentities($gainPromo),htmlentities($transfertCarte),htmlentities($manutentionHorsFDR),htmlentities($reglementRemiseX3),htmlentities($resteFDR),htmlentities($fondCaisse),htmlentities($idArretsCaisses),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[avarieAgence],[gainPromo],[transfertCarte],[manutentionHorsFDR],[reglementRemiseX3],[resteFDR],[fondCaisse],[idArretsCaisses]) VALUES (?,?,?,?,?,?,?,?,?) ';
            $param = array(htmlentities($dateEntree),htmlentities($avarieAgence),htmlentities($gainPromo),htmlentities($transfertCarte),htmlentities($manutentionHorsFDR),htmlentities($reglementRemiseX3),htmlentities($resteFDR),htmlentities($fondCaisse),htmlentities($idArretsCaisses));
            return self::query($sql, $param);
        }
    }
	
	public static function SearchByArret($idArret)
    {
		$sql = " SELECT * FROM [dbo].[". self::getTable() ."] WHERE [idArretsCaisses] = ".$idArret;
		return self::querySelect($sql);
        
    }
	
	
	

}