<?php

namespace Core\Database;

use Core\Model\Table;

class User extends Table
{

    protected static $table = 'Tb_User';

    public static function save($NomUser,$login,$password,$agence,$privilege,$id = null)
    {
        if (isset($id)) {
            $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [NomUser] = ?, [login] = ?, [password] = ?, [privilege] = ?, [idAgence] = ? WHERE [idUser] = ?';
            $param = array(htmlentities($NomUser),htmlentities($login),htmlentities($password),htmlentities($privilege),htmlentities($agence),htmlentities($id));
            return self::query($sql, $param);
        } else {
            $sql = 'INSERT INTO [dbo].[' . self::getTable() . '] ([NomUser],[login],[password],[privilege],[idAgence]) VALUES (?,?,?,?,?) ';
            $param = array(htmlentities($NomUser),htmlentities($login),htmlentities($password),htmlentities($privilege),htmlentities($agence));
            return self::query($sql, $param);
        }
    }
	
	public static function resetPassword($password,$id){
        $sql = 'UPDATE [dbo].[' . self::getTable() . '] SET [password] = ? WHERE [idUser] = ?';
        $param = array($password,$id);
        return self::querySelect($sql,$param);
    }
    
    public static function searchBylogin($login){
        $sql = "SELECT * FROM [dbo].[". self::getTable() ."] WHERE [login] = ?";
        $param = array($login);
        return self::querySelect($sql,$param);
    }

}