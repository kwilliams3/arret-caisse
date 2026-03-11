<?php

namespace Core\Controller;

use Core\Model\App;
use Core\Database\ArretsCaisses;
use Core\Database\ArretsCaissesSage;
use Core\Database\ArretsCaissesSageLog;
use Core\Database\ArretsSuppl;
use Core\Database\ArretsCaissesM;
use Core\Database\ArretsCaissesLog;
use Core\Database\Agence;
use Core\Database\User;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;
use Core\Model\Sms;

class ArretsCaissesController extends AppController{

    public function index(){
		
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
				
				// $stmtAgenceUser = Agence::searchById($user['agence']);
				// $agenceUser = array();
				// while ($result3= sqlsrv_fetch_array($stmtAgenceUser, SQLSRV_FETCH_ASSOC)) {
					// $agenceUser = array(
						// "id" => $result3['idAgence'],
						// "designation" => $result3['designation'],
					// );
				// }
					
				
			$stmtArretsCaisses = ArretsCaisses::all();
			$arretsCaisses = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($result['idAgence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
				$privilege = explode(" ", $user['privilege']);
				
				$privileges = 'Agence,Caissiere';
				if(in_array($privilege[0] ,explode(',',$privileges))) {
					
					if($user['agence'] == $result['idAgence']){
						
						$pers =  array("idArretsCaisses"=>$result['idArretsCaisses'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"totalBonPros"=>$result['totalBonPros'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
						
						$arretsCaisses[] = $pers;
					}
					
					
				 }else{
					 
					$pers =  array("idArretsCaisses"=>$result['idArretsCaisses'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"totalBonPros"=>$result['totalBonPros'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
					
					$arretsCaisses[] = $pers;
					 
				 }
				
			}
			

        $this->render('arretsCaisses.index',compact('arretsCaisses'));
            

    }
	
	
	public function ajoutArretsCaisse(){
		
		header('content-type: application/json');
        $result = [];

        if(isset($_POST['arretCash']) && (!empty($_POST['arretCash']) || $_POST['arretCash'] == 0) 
			&& isset($_POST['arretOrangeMobile']) && (!empty($_POST['arretOrangeMobile']) || $_POST['arretOrangeMobile'] == 0)
			&& (!empty($_POST['arretCarte']) || $_POST['arretCarte'] == 0) && isset($_POST['arretCarte']) 
			&& (!empty($_POST['arretCheque']) || $_POST['arretCheque'] == 0) && isset($_POST['arretCheque'])
			&& (!empty($_POST['arretMtnMobile']) || $_POST['arretMtnMobile'] == 0) && isset($_POST['arretMtnMobile'])
			&& (!empty($_POST['arretTpeMobile']) || $_POST['arretTpeMobile'] == 0) && isset($_POST['arretTpeMobile'])
			&& (!empty($_POST['arretVirement']) || $_POST['arretVirement'] == 0) && isset($_POST['arretVirement']) 
			&& (!empty($_POST['totalBonPros']) || $_POST['totalBonPros'] == 0) && isset($_POST['totalBonPros'])) {
			
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
				$date = date('Y-m-d');
				$today = date("Y-m-d h:m:s");

				$arretCash = $_POST['arretCash'];
				$arretTpeMobile = $_POST['arretTpeMobile'];
				$arretMtnMobile = $_POST['arretMtnMobile'];
				$arretOrangeMobile = $_POST['arretOrangeMobile'];
				$arretCarte = $_POST['arretCarte'];
				$arretCheque = $_POST['arretCheque'];
				$arretVirement = $_POST['arretVirement'];
				$totalBonPros = $_POST['totalBonPros'];
				
				$totalCaisse = $arretCash + $arretOrangeMobile + $arretMtnMobile + $arretTpeMobile + $arretCarte + $arretCheque + $arretVirement - $totalBonPros;
			
				$stmt = ArretsCaisses::oldCaisseDayId($today,$user['idUser']);
				$arretCaisseOld = array();
				while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
					
					$arretCaisseOld = array("idArretsCaisses"=>$result['idArretsCaisses'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							);
				}
				
            if(!empty($user)){
				
				if(empty($arretCaisseOld)){
															
													///	save($dateEntree,$arretCashCaisse,$arretOrangeCaisse,$arretMtnCaisse,$arretTpeCaisse,$arretCarteCaisse,$arretChequeCaisse,$arretVirementCaisse,$totalBonPros,$totalCaisse,$idAgence,$idUser,$id = null)	
					$addUpdateLog = ArretsCaissesLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser']);
				
					$addUpdate = ArretsCaisses::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser']);

					if ($addUpdate == TRUE) {
						$message = "L'enregistrement a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}else{
										  
					$addUpdateLog = ArretsCaissesLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser']);
					
					$addUpdate = ArretsCaisses::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaisses']);

					if ($addUpdate == TRUE) {
						$message = "L'enregistrement a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}
				
            }else {
                $message = "Une erreur est survenue, reessayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, reessayez plus tard !!!!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);
    }
	
	public function arretsMaj(){
		
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
				
				// $stmtAgenceUser = Agence::searchById($user['agence']);
				// $agenceUser = array();
				// while ($result3= sqlsrv_fetch_array($stmtAgenceUser, SQLSRV_FETCH_ASSOC)) {
					// $agenceUser = array(
						// "id" => $result3['idAgence'],
						// "designation" => $result3['designation'],
					// );
				// }
					
				
			$stmtArretsCaisses = ArretsCaissesM::all();
			$arretsCaisses = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($result['idAgence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
				$privilege = explode(" ", $user['privilege']);
				
				$privileges = 'Agence,Caissiere';
				if(in_array($privilege[0] ,explode(',',$privileges))) {
					
					if($user['agence'] == $result['idAgence']){
						
						$pers =  array("idArretsCaissesM"=>$result['idArretsCaissesM'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretVersementCaisse"=>$result['arretVersementCaisse'],
							"arretEntreeFictifCaisse"=>$result['arretEntreeFictifCaisse'],
							"arretDepensesCaisse"=>$result['arretDepensesCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"versementPros"=>$result['versementProspecteur'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
						
						$arretsCaisses[] = $pers;
					}
					
					
				 }else{
					 
					$pers =  array("idArretsCaissesM"=>$result['idArretsCaissesM'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretVersementCaisse"=>$result['arretVersementCaisse'],
							"arretEntreeFictifCaisse"=>$result['arretEntreeFictifCaisse'],
							"arretDepensesCaisse"=>$result['arretDepensesCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"versementPros"=>$result['versementProspecteur'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
					
					$arretsCaisses[] = $pers;
					 
				 }
				 
			}

        $this->render('arretsCaisses.arretsMaj',compact('arretsCaisses'));
            

    }
	
	
	public function ajoutArretsCaisseM(){
		
		header('content-type: application/json');
        $result = [];

        if(isset($_POST['arretCash']) && (!empty($_POST['arretCash']) || $_POST['arretCash'] == 0) 
			&& isset($_POST['arretOrangeMobile']) && (!empty($_POST['arretOrangeMobile']) || $_POST['arretOrangeMobile'] == 0)
			&& (!empty($_POST['arretCarte']) || $_POST['arretCarte'] == 0) && isset($_POST['arretCarte']) 
			&& (!empty($_POST['arretCheque']) || $_POST['arretCheque'] == 0) && isset($_POST['arretCheque'])
			&& (!empty($_POST['arretMtnMobile']) || $_POST['arretMtnMobile'] == 0) && isset($_POST['arretMtnMobile'])
			&& (!empty($_POST['arretTpeMobile']) || $_POST['arretTpeMobile'] == 0) && isset($_POST['arretTpeMobile'])
			&& (!empty($_POST['arretVirement']) || $_POST['arretVirement'] == 0) && isset($_POST['arretVirement'])
			&& (!empty($_POST['complementCash']) || $_POST['complementCash'] == 0) && isset($_POST['complementCash']) 
			&& (!empty($_POST['versementPros']) || $_POST['versementPros'] == 0) && isset($_POST['versementPros']) 
			&& (!empty($_POST['arretFictifM']) || $_POST['arretFictifM'] == 0) && isset($_POST['arretFictifM']) 
			&& (!empty($_POST['arretVersementClientM']) || $_POST['arretVersementClientM'] == 0) && isset($_POST['arretVersementClientM']) 
			&& (!empty($_POST['arretDepensesM']) || $_POST['arretDepensesM'] == 0) && isset($_POST['arretDepensesM'])
			&& (!empty($_POST['arretAvarie']) || $_POST['arretAvarie'] == 0) && isset($_POST['arretAvarie'])
			&& (!empty($_POST['arretBonAchat']) || $_POST['arretBonAchat'] == 0) && isset($_POST['arretBonAchat'])
			&& (!empty($_POST['arretTranfert']) || $_POST['arretTranfert'] == 0) && isset($_POST['arretTranfert'])
			&& (!empty($_POST['arretRemiseSage']) || $_POST['arretRemiseSage'] == 0) && isset($_POST['arretRemiseSage'])
			&& (!empty($_POST['arretGainPromo']) || $_POST['arretGainPromo'] == 0) && isset($_POST['arretGainPromo']) 
			&& (!empty($_POST['arretManutention']) || $_POST['arretManutention'] == 0) && isset($_POST['arretManutention'])
			&& (!empty($_POST['arretOpDirection']) || $_POST['arretOpDirection'] == 0) && isset($_POST['arretOpDirection']) 
			&& (!empty($_POST['arretFraisTaxi']) || $_POST['arretFraisTaxi'] == 0) && isset($_POST['arretFraisTaxi'])) {
			
			// var arretAvarie = $('#arretAvarie1').val();
			// var arretBonAchat = $('#arretBonAchat1').val();
			// var arretTranfert = $('#arretTranfert1').val();
			// var arretRemiseSage = $('#arretRemiseSage1').val();
			// var arretGainPromo = $('#arretGainPromo1').val();
				
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
				$date = date('Y-m-d');
				$today = date("Y-m-d h:m:s");

				$arretCash = $_POST['arretCash'];
				$arretTpeMobile = $_POST['arretTpeMobile'];
				$arretMtnMobile = $_POST['arretMtnMobile'];
				$arretOrangeMobile = $_POST['arretOrangeMobile'];
				$arretCarte = $_POST['arretCarte'];
				$arretCheque = $_POST['arretCheque'];
				$arretComplementCaisse = $_POST['complementCash'];
				$arretVirement = $_POST['arretVirement'];
				$arretFictifM = $_POST['arretFictifM'];
				$arretVersementClientM = $_POST['arretVersementClientM'];
				$arretDepensesM = $_POST['arretDepensesM'];
				$versementPros = $_POST['versementPros'];
				$arretAvarie = $_POST['arretAvarie'];
				$arretBonAchat = $_POST['arretBonAchat'];
				$arretTranfert = $_POST['arretTranfert'];
				$arretRemiseSage = $_POST['arretRemiseSage'];
				$arretGainPromo = $_POST['arretGainPromo'];
				$arretManutention = $_POST['arretManutention'];
				$arretOpDirection = $_POST['arretOpDirection'];
				$arretFraisTaxi = $_POST['arretFraisTaxi'];
				
				$totalCaisse = $arretCash + $arretTpeMobile + $arretMtnMobile + $arretOrangeMobile + $arretCarte + $arretCheque + $arretVirement + $arretFictifM + $arretVersementClientM;
			
				$stmt = ArretsCaissesM::oldCaisseAgence($today,$user['agence']);
				$arretCaisseOld = array();
				while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
					
					$arretCaisseOld = array("idArretsCaissesM"=>$result['idArretsCaissesM'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							);
				}
				
				
            if(!empty($user)){
				
				if(empty($arretCaisseOld)){
					
					//$addUpdateLog = ArretsCaissesLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$versementPros,$totalCaisse,$user['agence'],$user['idUser']);
					//							save($dateEntree,$arretCashCaisse,$arretOrangeCaisse,$arretMtnCaisse,$arretVersementCaisse,$arretEntreeFictifCaisse,$arretDepensesCaisse,$arretTpeCaisse,$arretCarteCaisse,$arretChequeCaisse,$arretComplementCaisse,$arretVirementCaisse,$versementProspecteur,$avarieAgence,$gainPromo,$transfertCarte,$bonAchat,$reglementRemiseX3,$arretManutention,$arretOpDirection,$arretTaxi,$totalCaisse,$idAgence,$idUser,$id = null)
					$addUpdate = ArretsCaissesM::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretVersementClientM,$arretFictifM,$arretDepensesM,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$versementPros,$arretAvarie,$arretGainPromo,$arretTranfert,$arretBonAchat,$arretRemiseSage,$arretManutention,$arretOpDirection,$arretFraisTaxi,$totalCaisse,$user['agence'],$user['idUser']);
																																																												
					if ($addUpdate == TRUE) {
						$message = "L'enregistrement a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}else{
										  
					//$addUpdateLog = ArretsCaissesLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$versementPros,$totalCaisse,$user['agence'],$user['idUser']);
					
					$addUpdate = ArretsCaissesM::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretVersementClientM,$arretFictifM,$arretDepensesM,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$versementPros,$arretAvarie,$arretGainPromo,$arretTranfert,$arretBonAchat,$arretRemiseSage,$arretManutention,$arretOpDirection,$arretFraisTaxi,$totalCaisse,$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaissesM']);

					if ($addUpdate == TRUE) {
						$message = "L'enregistrement a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}
				
            }else {
                $message = "Une erreur est survenue, reessayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, reessayez plus tard !!!!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);
    }
	
	public function detailsArretsM(){
		
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
            $stmtArretCaissesM = ArretsCaissesM::searchById($id);
			$arretsCaissesM = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretCaissesM, SQLSRV_FETCH_ASSOC)){
					
					$stmtAgence = Agence::searchById($result['idAgence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
						$arretsCaissesM =  array("idArretsCaissesM"=>$result['idArretsCaissesM'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretVersementCaisse"=>$result['arretVersementCaisse'],
							"arretEntreeFictifCaisse"=>$result['arretEntreeFictifCaisse'],
							"arretDepensesCaisse"=>$result['arretDepensesCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"avarieAgence"=>$result['avarieAgence'],
							"gainPromo"=>$result['gainPromo'],
							"transfertCarte"=>$result['transfertCarte'],
							"bonAchat"=>$result['bonAchat'],
							"reglementRemiseX3"=>$result['reglementRemiseX3'],
							"versementProspecteur"=>$result['versementProspecteur'],
							"arretManutention"=>$result['arretManutention'],
							"arretOpDirection"=>$result['arretOpDirection'],
							"arretTaxi"=>$result['arretTaxi'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
							
					}
						
				
            if(!empty($arretsCaissesM)){
				
					$content = '
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Date  </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretsCaissesM['dateEntree'])) .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Cash </div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretCashCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Orange Money</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretOrangeCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Mtn Money</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretMtnCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Versement Client Banque</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretVersementCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Encaissement sans espece</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretEntreeFictifCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Depenses Caisse</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretDepensesCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name"> Arret TPE</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretTpeCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Carte SOREPCO</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretCarteCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Cheque</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretChequeCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Virement</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretVirementCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arrets Complement</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretComplementCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Bon Prospecteur</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['versementProspecteur'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Avarie Caisse</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['avarieAgence'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Gain Promo</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['gainPromo'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Transfert Carte Caisse</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['transfertCarte'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Bon d\'achat</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['bonAchat'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arrets Reglement Sage X3</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['reglementRemiseX3'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret manutention Caisse</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretManutention'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret OP Direction</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretOpDirection'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arrets taxi Caisse</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['arretTaxi'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Arret Total Caisse</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsCaissesM['totalCaisse'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name">Agence</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' .$arretsCaissesM['agence'] .  '</span>
							</div>
						</div>

					</div>
				
				';
						
                $result = array("statuts" => 0, "content" => $content);
			
            }else {
                $message = "Une erreur est survenue, reessayez plus tard 111 !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, reessayez plus tard 0000 !!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }
	
	public function arretSage(){
		
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
			$stmtArretsCaisses = ArretsCaissesSage::all();
			$arretsCaisses = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($result['idAgence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
				$privilege = explode(" ", $user['privilege']);
				
				$privileges = 'Agence,Caissiere';
				if(in_array($privilege[0] ,explode(',',$privileges))) {
					
					if($user['agence'] == $result['idAgence']){
						
						$pers =  array("idArretsCaissesSage"=>$result['idArretsCaissesSage'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"totalBonPros"=>$result['totalBonPros'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
						
						$arretsCaisses[] = $pers;
					}
					
					
				 }else{
					 
					$pers =  array("idArretsCaissesSage"=>$result['idArretsCaissesSage'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"totalBonPros"=>$result['totalBonPros'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
					
					$arretsCaisses[] = $pers;
					 
				 }
				
			}
			

        $this->render('arretsCaisses.arretsSage',compact('arretsCaisses'));
            

    }
	
	public function ajoutArretsCaisseSage(){
		
		header('content-type: application/json');
        $result = [];

        if(isset($_POST['arretCash']) && (!empty($_POST['arretCash']) || $_POST['arretCash'] == 0) 
			&& isset($_POST['arretOrangeMobile']) && (!empty($_POST['arretOrangeMobile']) || $_POST['arretOrangeMobile'] == 0)
			&& (!empty($_POST['arretCarte']) || $_POST['arretCarte'] == 0) && isset($_POST['arretCarte']) 
			&& (!empty($_POST['arretCheque']) || $_POST['arretCheque'] == 0) && isset($_POST['arretCheque'])
			&& (!empty($_POST['arretMtnMobile']) || $_POST['arretMtnMobile'] == 0) && isset($_POST['arretMtnMobile'])
			&& (!empty($_POST['arretTpeMobile']) || $_POST['arretTpeMobile'] == 0) && isset($_POST['arretTpeMobile'])
			&& (!empty($_POST['complementCash']) || $_POST['complementCash'] == 0) && isset($_POST['complementCash']) 
			&& (!empty($_POST['arretVirement']) || $_POST['arretVirement'] == 0) && isset($_POST['arretVirement']) 
			&& (!empty($_POST['totalBonPros']) || $_POST['totalBonPros'] == 0) && isset($_POST['totalBonPros'])) {
			
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
				$date = date('Y-m-d');
				$today = date("Y-m-d h:m:s");

				$arretCash = $_POST['arretCash'];
				$arretTpeMobile = $_POST['arretTpeMobile'];
				$arretMtnMobile = $_POST['arretMtnMobile'];
				$arretOrangeMobile = $_POST['arretOrangeMobile'];
				$arretCarte = $_POST['arretCarte'];
				$arretCheque = $_POST['arretCheque'];
				$complementCash = $_POST['complementCash'];
				$arretVirement = $_POST['arretVirement'];
				$totalBonPros = $_POST['totalBonPros'];
				
				$totalCaisse = $arretCash + $arretOrangeMobile + $arretMtnMobile + $arretTpeMobile + $arretCarte + $arretCheque + $arretVirement + $complementCash - $totalBonPros;
			
				$stmt = ArretsCaissesSage::oldCaisseAgence($today,$user['agence']);
				$arretCaisseOld = array();
				while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
					
					$arretCaisseOld = array("idArretsCaissesSage"=>$result['idArretsCaissesSage'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							);
				}
				
            if(!empty($user)){
				
				if(empty($arretCaisseOld)){
					
					$addUpdateLog = ArretsCaissesSageLog::save($today,$complementCash,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser']);
					
					$addUpdate = ArretsCaissesSage::save($today,$complementCash,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser']);

					if ($addUpdate == TRUE) {
						$message = "L'enregistrement a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}else{
										  
					$addUpdateLog = ArretsCaissesSageLog::save($today,$complementCash,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser']);
					
					$addUpdate = ArretsCaissesSage::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$complementCash,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretVirement,$totalBonPros,$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaissesSage']);

					if ($addUpdate == TRUE) {
						$message = "L'enregistrement a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}
				
            }else {
                $message = "Une erreur est survenue, reessayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, reessayez plus tard !!!!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);
    }
	
	
}

?>