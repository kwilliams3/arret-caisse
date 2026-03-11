<?php

namespace Core\Database;

use Core\Model\Table;

class ArretDouanierSage extends Table
{
	
    protected static $table = 'Tb_ArretsDouanierSage';
	
    public static function save($dateEntree,$arretInfo,$arretDouanier,$diffCaisse,$diffDouanier,$observationChef,$idUser,$versements,$observationVersements,$bordereauVersement,$MontantVerse,$idAgence,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[arretInfo] = ?,[arretDouanier] = ?,[diffCaisse] = ?,[diffDouanier] = ?,[observationChef] = ?,[idUser] = ? ,[versements] = ? ,[observationVersements] = ? ,[bordereauVersement] = ? ,[MontantVerse] = ? ,[idAgence] = ? WHERE [idArretsDouanierSage] = ?';
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
	
	public static function miseJourCpta($observation,$id)
		{
			$sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [observationCpta] = ? WHERE [idArretsDouanierSage] = ?';
			$param = array(htmlentities($observation),htmlentities($id));
			return self::query($sql, $param);
			
		}
		
	public static function miseJourGestion($observation,$id)
		{
			$sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [observationGestion] = ? WHERE [idArretsDouanierSage] = ?';
			$param = array(htmlentities($observation),htmlentities($id));
			return self::query($sql, $param);
			
		}

}