<?php

namespace Core\Controller;

use Core\Model\App;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Database\User;
use Core\Database\Agence;
use Core\Database\ArretsCaisses;
use Core\Database\ArretsCaissesSage;
use Core\Database\ArretsCaissesLD;
use Core\Model\Session;
use Core\Model\Sms;

class HomeController extends AppController{

    public function index(){
		
			
            $this->renderLogin('home.index');

    }
	
	public  function loginUser(){
        header('content-type: application/json');
        $return = [];
        if(isset($_POST['login']) && isset($_POST['password'])){
			//var_dump('Test 1');
            $login = $_POST['login'];
            $password = $_POST['password']; 
			//var_dump('Test 2');
            if(!empty($login) && !empty($password)){
				/*var_dump('Test 3');	  
				var_dump($login);
				var_dump($password);*/
                $conMessage = App::getDBAuth()->login($login,$password);
					$user = array();
					$session = Session::getInstance();
				//var_dump('Test 4');	
				if(strcmp($conMessage, "connecte") == 0){ 
					$user = $_SESSION['user'];
					//Partie user interne
					
						$lastUrl = 'index.php?p=home.accuiel';
						$return = array("statuts" => 0,"direct"=>$lastUrl, "mes"=> "Vous etes bien connecte");
					/*var_dump('Test 5');
					die();*/
				}else{
					
					$message = 'Une erreur est survenue, réessayer';
					$return = array("statuts"=> 1,"mes" => $conMessage);
					
					}
            }else{
                $message = 'Renseigner tous les champs obligatoires';
                $return = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = 'Une erreur est survenue, réessayer';
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
    }
	
	
	public function accuiel(){
		// la date du jour, et le veille
		
		$date = date('Y-m-d');
		$today = date("Y-m-d h:m:s");
		$hier = date('Y-m-d H:i:s', ( time() - 86400) );
		
		// Traitement des agences sage x3
		
		$stmtNbreAgenceSageX3 = Agence::numberByType('Agence');
        $nbreAgenceX3 = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreAgenceSageX3, SQLSRV_FETCH_ASSOC)) {
            $nbreAgenceX3 = $result[''];
        }
		// Recuperation pour la veille 
		$stmtNbreArretsSageX3Hier = ArretsCaisses::numberByDay($hier);
        $nbreArretsX3Hier = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreArretsSageX3Hier, SQLSRV_FETCH_ASSOC)) {
            $nbreArretsX3Hier = $result[''];
        }
		// Recuperation pour la today  
		$stmtNbreArretsSageX3Today = ArretsCaisses::numberByDay($today);
        $nbreArretsX3Today = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreArretsSageX3Today, SQLSRV_FETCH_ASSOC)) {
            $nbreArretsX3Today = $result[''];
        }
		
		// Traitement des agences sage 100
		
		$stmtNbreAgenceSage100 = Agence::numberByType('AgenceSage');
        $nbreAgenceSage = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreAgenceSage100, SQLSRV_FETCH_ASSOC)) {
            $nbreAgenceSage = $result[''];
        }
		// Recuperation pour la veille 
		$stmtNbreArretsSage100Hier = ArretsCaissesSage::numberByDay($hier);
        $nbreArrets100Hier = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreArretsSage100Hier, SQLSRV_FETCH_ASSOC)) {
            $nbreArrets100Hier = $result[''];
        }
		// Recuperation pour la today  
		$stmtNbreArretsSage100Today = ArretsCaissesSage::numberByDay($today);
        $nbreArrets100Today = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreArretsSage100Today, SQLSRV_FETCH_ASSOC)) {
            $nbreArrets100Today = $result[''];
        }
		
		// Traitement des agences LD
		
		$stmtNbreAgenceLD = Agence::numberByType('LD');
        $nbreAgenceLD = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreAgenceLD, SQLSRV_FETCH_ASSOC)) {
            $nbreAgenceLD = $result[''];
        }
		// Recuperation pour la veille 
		$stmtNbreArretsLDHier = ArretsCaissesLD::numberByDay($hier);
        $nbreArretsLDHier = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreArretsLDHier, SQLSRV_FETCH_ASSOC)) {
            $nbreArretsLDHier = $result[''];
        }
		// Recuperation pour la today  
		$stmtNbreArretsLDToday = ArretsCaissesLD::numberByDay($today);
        $nbreArretsLDToday = 0;
		
        while ($result = sqlsrv_fetch_array($stmtNbreArretsLDToday, SQLSRV_FETCH_ASSOC)) {
            $nbreArretsLDToday = $result[''];
        }
		
		
		
        $this->render('home.accuiel',compact('ArretsCaisses','nbreAgenceLD','nbreArretsLDHier','nbreArretsLDToday','nbreAgenceSage','nbreArrets100Hier','nbreArrets100Today','nbreAgenceX3','nbreArretsX3Hier','nbreArretsX3Today'));
			
    }
	
	public function resetPass(){
		
		header('content-type: application/json');
        $result = [];

        if(isset($_POST['oldPassword']) && !empty($_POST['oldPassword']) && isset($_POST['newPassword']) && !empty($_POST['newPassword']) 
			&& isset($_POST['confirmPassword']) && !empty($_POST['confirmPassword'])) {

            $oldPassword = $_POST['oldPassword'];
            $newPassword = $_POST['newPassword'];
            $confirmPassword = $_POST['confirmPassword'];
				
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
            if(!empty($user)){
				
				$ancien = explode(" ",$user['password']);
				$ancienSaisi = $oldPassword;
				
				
				if(strcmp($ancien[0],$ancienSaisi) == 0){
					
					if(strcmp($newPassword,$confirmPassword) == 0){
						
						$addUpdate = User::resetPassword($newPassword,$user['idUser']);

						if ($addUpdate == TRUE) {
							$message = "La reinitialisation a ete bien effectue";
							$result = array("statuts" => 0, "mes" => $message);
						} else {
							$message = "Erreur lors de l'enregistrement";
							$result = array("statuts" => 1, "mes" => $message);
						}
						
					}else{
						$message = "Les mot de passe ne sont pas identiques";
						$result = array("statuts" => 1, "mes" => $message);
					}
				}else{
						$message = "Ancien mot de passe est incorrect";
						$result = array("statuts" => 1, "mes" => $message);
					}
            }else {
                $message = "Une erreur est survenue, reessayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, reessayez plus tard !!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);
    }
	
	
	public function logout(){
        header('content-type: application/json');
        $result = " ";
        $session = Session::getInstance();
        if(isset($_SESSION['user'])){
            if(App::getDBAuth()->signOut() == true){
                $lastUrl = 'index.php?p=home.index';
                $result = array("statuts" => 0, "direct"=>$lastUrl, "mes" => "Vous etes deconnete");
            }else{
                $result = array("statuts" => 0, "mes" => "Une erreur est survenue");
            }
        }
        echo json_encode($result);
    }
	
}

?>