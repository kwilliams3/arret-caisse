<?php

namespace Core\Database;

use Core\Model\Table;

class ArretDouanierSageLog extends Table
{
	
    protected static $table = 'Tb_ArretsDouanierSageLog';
	

    public static function save($dateEntree,$arretInfo,$arretDouanier,$diffCaisse,$diffDouanier,$observationChef,$idUser,$versements,$observationVersements,$bordereauVersement,$MontantVerse,$idAgence,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[arretInfo] = ?,[arretDouanier] = ?,[diffCaisse] = ?,[diffDouanier] = ?,[observationChef] = ?,[idUser] = ? ,[versements] = ? ,[observationVersements] = ? ,[bordereauVersement] = ? ,[MontantVerse] = ? ,[idAgence] = ? WHERE [ArretsDouanierSageLog] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($arretInfo),htmlentities($arretDouanier),htmlentities($diffCaisse),htmlentities($diffDouanier),htmlentities($observationChef),htmlentities($idUser),htmlentities($versements),htmlentities($observationVersements),htmlentities($bordereauVersement),htmlentities($MontantVerse),htmlentities($idAgence),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[arretInfo],[arretDouanier],[diffCaisse],[diffDouanier],[observationChef],[idUser],[versements],[observationVersements],[bordereauVersement],[MontantVerse],[idAgence]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ';
            $param = array(htmlentities($dateEntree),htmlentities($arretInfo),htmlentities($arretDouanier),htmlentities($diffCaisse),htmlentities($diffDouanier),htmlentities($observationChef),htmlentities($idUser),htmlentities($versements),htmlentities($observationVersements),htmlentities($bordereauVersement),htmlentities($MontantVerse),htmlentities($idAgence));
            return self::query($sql, $param);
        }
    }
    
    public static function oldCaisseDayId($today,$id)
    {
		$sql = " SELECT * FROM [dbo].[". self::getTable() ."] WHERE [dateEntree] = '".$today."' AND [idUser] = ".$id;
		return self::querySelect($sql);
        
    }

}