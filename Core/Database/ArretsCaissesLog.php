<?php

namespace Core\Database;

use Core\Model\Table;

class ArretsCaissesLog extends Table
{

    protected static $table = 'Tb_ArretsCaissesLog';
	

    public static function save($dateEntree,$arretCashCaisse,$arretOrangeCaisse,$arretMtnCaisse,$arretTpeCaisse,$arretCarteCaisse,$arretChequeCaisse,$arretVirementCaisse,$totalBonPros,$totalCaisse,$idAgence,$idUser,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [dateEntree] = ?,[arretCashCaisse] = ?,[arretOrangeCaisse] = ?,[arretMtnCaisse] = ?,[arretTpeCaisse] = ?,[arretCarteCaisse] = ?,[arretChequeCaisse] = ?,[arretVirementCaisse] = ?,[totalBonPros] = ?,[totalCaisse] = ?,[idAgence] = ? ,[idUser] = ? WHERE [idArretsCaissesLog] = ?';
            $param = array(htmlentities($dateEntree),htmlentities($arretCashCaisse),htmlentities($arretOrangeCaisse),htmlentities($arretMtnCaisse),htmlentities($arretTpeCaisse),htmlentities($arretCarteCaisse),htmlentities($arretChequeCaisse),htmlentities($arretVirementCaisse),htmlentities($totalBonPros),htmlentities($totalCaisse),htmlentities($idAgence),htmlentities($idUser),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([dateEntree],[arretCashCaisse],[arretOrangeCaisse],[arretMtnCaisse],[arretTpeCaisse],[arretCarteCaisse],[arretChequeCaisse],[arretVirementCaisse],[totalBonPros],[totalCaisse],[idAgence],[idUser]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ';
            $param = array(htmlentities($dateEntree),htmlentities($arretCashCaisse),htmlentities($arretOrangeCaisse),htmlentities($arretMtnCaisse),htmlentities($arretTpeCaisse),htmlentities($arretCarteCaisse),htmlentities($arretChequeCaisse),htmlentities($arretVirementCaisse),htmlentities($totalBonPros),htmlentities($totalCaisse),htmlentities($idAgence),htmlentities($idUser));
            return self::query($sql, $param);
        }
    }

}