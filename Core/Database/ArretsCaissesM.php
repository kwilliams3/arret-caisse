<?php

namespace Core\Database;

use Core\Model\Table;

class ArretsCaissesM extends Table
{

    protected static $table = 'Tb_ArretsCaissesM';
	
    public static function save($dateEntree,$arretCashCaisse,$arretOrangeCaisse,$arretMtnCaisse,$arretVersementCaisse,$arretEntreeFictifCaisse,$arretDepensesCaisse,$arretTpeCaisse,$arretCarteCaisse,$arretChequeCaisse,$arretComplementCaisse,$arretVirementCaisse,$versementProspecteur,$avarieAgence,$gainPromo,$transfertCarte,$bonAchat,$reglementRemiseX3,$arretManutention,$arretOpDirection,$arretTaxi,$totalCaisse,$idAgence,$idUser,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[arretCashCaisse] = ?,[arretOrangeCaisse] = ?,[arretMtnCaisse] = ?,[arretVersementCaisse] = ?,[arretEntreeFictifCaisse] = ?,[arretDepensesCaisse] = ?,[arretTpeCaisse] = ?,[arretCarteCaisse] = ?,[arretChequeCaisse] = ?,[arretVirementCaisse] = ?,[arretComplementCaisse] = ?,[versementProspecteur] = ?,[avarieAgence] = ?,[gainPromo] = ?,[transfertCarte] = ?,[bonAchat] = ?,[reglementRemiseX3] = ?,[arretManutention] = ?,[arretOpDirection] = ?,[arretTaxi] = ?,[totalCaisse] = ?,[idAgence] = ? ,[idUser] = ? WHERE [idArretsCaissesM] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($arretCashCaisse),htmlentities($arretOrangeCaisse),htmlentities($arretMtnCaisse),htmlentities($arretVersementCaisse),htmlentities($arretEntreeFictifCaisse),htmlentities($arretDepensesCaisse),htmlentities($arretTpeCaisse),htmlentities($arretCarteCaisse),htmlentities($arretChequeCaisse),htmlentities($arretVirementCaisse),htmlentities($arretComplementCaisse),htmlentities($versementProspecteur),htmlentities($avarieAgence),htmlentities($gainPromo),htmlentities($transfertCarte),htmlentities($bonAchat),htmlentities($reglementRemiseX3),htmlentities($arretManutention),htmlentities($arretOpDirection),htmlentities($arretTaxi),htmlentities($totalCaisse),htmlentities($idAgence),htmlentities($idUser),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[arretCashCaisse],[arretOrangeCaisse],[arretMtnCaisse],[arretVersementCaisse],[arretEntreeFictifCaisse],[arretDepensesCaisse],[arretTpeCaisse],[arretCarteCaisse],[arretChequeCaisse],[arretVirementCaisse],[arretComplementCaisse],[versementProspecteur],[avarieAgence],[gainPromo],[transfertCarte],[bonAchat],[reglementRemiseX3],[arretManutention],[arretOpDirection],[arretTaxi],[totalCaisse],[idAgence],[idUser]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ';
            $param = array(htmlentities($dateEntree),htmlentities($arretCashCaisse),htmlentities($arretOrangeCaisse),htmlentities($arretMtnCaisse),htmlentities($arretVersementCaisse),htmlentities($arretEntreeFictifCaisse),htmlentities($arretDepensesCaisse),htmlentities($arretTpeCaisse),htmlentities($arretCarteCaisse),htmlentities($arretChequeCaisse),htmlentities($arretVirementCaisse),htmlentities($arretComplementCaisse),htmlentities($versementProspecteur),htmlentities($avarieAgence),htmlentities($gainPromo),htmlentities($transfertCarte),htmlentities($bonAchat),htmlentities($reglementRemiseX3),htmlentities($arretManutention),htmlentities($arretOpDirection),htmlentities($arretTaxi),htmlentities($totalCaisse),htmlentities($idAgence),htmlentities($idUser));
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