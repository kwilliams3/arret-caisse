<?php
/**
 * Created by PhpStorm.
 * User: su
 * Date: 7/5/2015
 * Time: 10:21 AM
 */

namespace Core\Auth;

use Core\Model\Session;
use Core\Model\Table;
use Core\Model\App;
use Core\Database\Agence;

class DBAuth {

    private $session;

    /*
     * Constructeur avec les messages d'options � personnaliser

    public  function __construct($session){
        $this->session = $session;
    }

    /*
     * retourne la session en cours

    public function getSession(){
        return $this->session;
    }

    /**
     * fonction qui permet a un utilisateur de se connecter
     * @param $username
     * @param $password
     * @return boolean
     */
    public function login($login,$password){
		$stmt = Table::querySelect('SELECT * FROM [dbo].[Tb_User] WHERE [login] = ? AND [password] = ?',array($login,$password));
		
		$user = array();
		while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
			
			$stmtAgence = Agence::searchById($result['idAgence']);
            $agence = array();
            while ($result1 = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                $agence = array(
                    "id" => $result1['idAgence'],
                    "designation" => $result1['designation'],
                );
            }
			
			$privilege = explode(" ", $result['privilege']);
			
			$user = array("idUser"=>$result['idUser'],
				"NomUser"=>$result['NomUser'],
				"login"=>$result['login'],
				"password"=>$result['password'],
				"agence"=>$result['idAgence'],
				"designation"=>$agence['designation'],
				"privilege"=>$privilege[0],
			);
		}
		
		if(!empty($user)){
			
			$session = new Session();
			$session->write('user',$user);
			
			$today = date("d-m-Y");
		    $todayBegin = date("1-m-Y");
				
			return "connecte";
			
		}else{
			return "Identifiant ou mot de passe incorrect";
		}
    }
	



    public function  signOut(){
        unset($_SESSION['user']);
        session_destroy();
        //$this->session->write('succes','Vous etes deconnecte avec succes');
        return true;
    }


}