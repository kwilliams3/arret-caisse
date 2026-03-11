<?php

namespace Core\Database;

use Core\Model\Table;

class ArretsCaissesLDLog extends Table
{

    protected static $table = 'Tb_ArretsCaissesLDLog';
	  
    public static function save($dateEntree,$arretCashCaisse,$arretOrangeCaisse,$arretMtnCaisse,$arretTpeCaisse,$arretCarteCaisse,$arretChequeCaisse,$arretComplementCaisse,$arretVirementCaisse,$arretInfo,$versements,$observationVersements,$MontantVerse,$bordereauVersement,$totalCaisse,$idAgence,$idUser,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[arretCashCaisse] = ?,[arretOrangeCaisse] = ?,[arretMtnCaisse] = ?,[arretTpeCaisse] = ?,[arretCarteCaisse] = ?,[arretChequeCaisse] = ?,[arretComplementCaisse] = ?,[arretVirementCaisse] = ?,[arretInfo] = ?,[versements] = ?,[observationVersements] = ?,[MontantVerse] = ?,[bordereauVersement] = ?,[totalCaisse] = ?,[idAgence] = ? ,[idUser] = ? WHERE [idArretsCaissesLDLog] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($arretCashCaisse),htmlentities($arretOrangeCaisse),htmlentities($arretMtnCaisse),htmlentities($arretTpeCaisse),htmlentities($arretCarteCaisse),htmlentities($arretChequeCaisse),htmlentities($arretComplementCaisse),htmlentities($arretVirementCaisse),htmlentities($arretInfo),htmlentities($versements),htmlentities($observationVersements),htmlentities($MontantVerse),htmlentities($bordereauVersement),htmlentities($totalCaisse),htmlentities($idAgence),htmlentities($idUser),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[arretCashCaisse],[arretOrangeCaisse],[arretMtnCaisse],[arretTpeCaisse],[arretCarteCaisse],[arretChequeCaisse],[arretComplementCaisse],[arretVirementCaisse],[arretInfo],[versements],[observationVersements],[MontantVerse],[bordereauVersement],[totalCaisse],[idAgence],[idUser]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ';
			 $param = array(htmlentities($dateEntree),htmlentities($arretCashCaisse),htmlentities($arretOrangeCaisse),htmlentities($arretMtnCaisse),htmlentities($arretTpeCaisse),htmlentities($arretCarteCaisse),htmlentities($arretChequeCaisse),htmlentities($arretComplementCaisse),htmlentities($arretVirementCaisse),htmlentities($arretInfo),htmlentities($versements),htmlentities($observationVersements),htmlentities($MontantVerse),htmlentities($bordereauVersement),htmlentities($totalCaisse),htmlentities($idAgence),htmlentities($idUser));
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
	

}