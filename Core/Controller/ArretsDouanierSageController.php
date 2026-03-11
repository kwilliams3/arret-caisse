<?php

namespace Core\Controller;

require 'Core\Classes\PHPExcel.php';
require 'Core\Classes\PHPExcel\IOFactory.php';

use Core\Model\App;
use Core\Database\ArretControleSage;
use Core\Database\ActionsArretsSage;
use Core\Database\ArretsCaissesSage;
use Core\Database\ArretDouanierSage;
use Core\Database\ArretDouanierSageLog;
use Core\Database\Agence;
use Core\Database\User;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;
use Core\Model\Sms;

class ArretsDouanierSageController extends AppController{

    public function index(){
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence($today,$user['agence']);
				$arretsOld = array();
				while ($result3 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
					
					$arretsOld =  array("idArretsCaissesSage"=>$result3['idArretsCaissesSage'],
					"arretCashCaisse"=>$result3['arretCashCaisse'],
					"arretOrangeCaisse"=>$result3['arretOrangeCaisse'],
					"arretMtnCaisse"=>$result3['arretMtnCaisse'],
					"arretTpeCaisse"=>$result3['arretTpeCaisse'],
					"arretCarteCaisse"=>$result3['arretCarteCaisse'],
					"arretChequeCaisse"=>$result3['arretChequeCaisse'],
					"idUser"=>$result3['idUser'],
					"totalCaisse"=>$result3['totalCaisse'],
					"dateEntree"=>$result3['dateEntree'],
					"agence"=>$result3['idAgence'],
				);
					
				}
				
				$stmtAgenceUser = Agence::searchById($user['agence']);
				$agenceUser = array();
				while ($result3= sqlsrv_fetch_array($stmtAgenceUser, SQLSRV_FETCH_ASSOC)) {
					$agenceUser = array(
						"id" => $result3['idAgence'],
						"designation" => $result3['designation'],
					);
				}
					
				
			$stmtArretsDouanier = ArretDouanierSage::all();
			$arretsDouaniers = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
				$privilege = explode(" ", $user['privilege']);
					
				$privileges = 'AgenceSage,CaissiereSage';
				if(in_array($privilege[0] ,explode(',',$privileges))) {
					
					if($user['agence'] == $agence['id']){
						
						$formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"versements"=>$result['versements'],
									"observationVersements"=>$result['observationVersements'],
									"bordereauVersement"=>$result['bordereauVersement'],
									"MontantVerse"=>$result['MontantVerse'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result1['arretMtnCaisse'],
									"arretTpeCaisse"=>$result1['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							$arretsDouaniers[] = $pers;
					}
				}else{
					
					$formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"versements"=>$result['versements'],
									"observationVersements"=>$result['observationVersements'],
									"bordereauVersement"=>$result['bordereauVersement'],
									"MontantVerse"=>$result['MontantVerse'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result1['arretMtnCaisse'],
									"arretTpeCaisse"=>$result1['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							$arretsDouaniers[] = $pers;
					
				}
			}
			//die();
			$this->render('arretsDouaniersSage.index',compact('arretsDouaniers','arretsOld'));
	    }
		
						
	
	public function ajoutArretsDouanierSage(){
		
		header('content-type: application/json');
        $result = [];
		
        if(isset($_POST['arretDouanierSage']) && !empty($_POST['arretDouanierSage']) && isset($_POST['arretInfoSage']) && !empty($_POST['arretInfoSage']) 
			&& !empty($_POST['obsArretChefSage']) && isset($_POST['obsArretChefSage'])&& !empty($_POST['observationVersSage']) && isset($_POST['observationVersSage'])
			&& !empty($_POST['versementSage']) && isset($_POST['versementSage'])) {
			
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
				$date = date('Y-m-d');
				$today = date("Y-m-d h:m:s");
				
				$todayInt = date("d-m-Y h:m:s");

				$arretDouanier = $_POST['arretDouanierSage'];
				$arretInfo = $_POST['arretInfoSage'];
				$obsArretChef = $_POST['obsArretChefSage'];
				$observationVers = $_POST['observationVersSage'];
				$versement = $_POST['versementSage'];
			
				$stmt = ArretDouanierSage::oldCaisseDayId($today,$user['idUser']);
				$arretDouanierOld = array();
				while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
				
					$arretDouanierOld = array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"versements"=>$result['versements'],
									"MontantVerse"=>$result['MontantVerse'],
									"observationVersements"=>$result['observationVersements'],
									"bordereauVersement"=>$result['bordereauVersement'],
									"observationChef"=>$result['observationChef'],
									"dateEntree"=>$result['dateEntree'],
								);
				}
				
				$stmtAgenceUser = Agence::searchById($user['agence']);
				$agenceUser = array();
				while ($result3= sqlsrv_fetch_array($stmtAgenceUser, SQLSRV_FETCH_ASSOC)) {
					
					$designation = explode(" ",$result3['designation']);
					
					$agenceUser = array(
						"id" => $result3['idAgence'],
						"designation" => $designation[0],
					);
				}
				
				
            if(!empty($user)){
				
				if(empty($arretDouanierOld)){
					
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence($today,$user['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result['idArretsCaisses'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
					
					if(!empty($arretsCaisse)){
						
							if(strcmp($versement,'Oui') == 0){
								
								$montantVerse = $_POST['montantVerseSage'];
								$bordereauVers = $_FILES['bordereauVersSage'];
								
								move_uploaded_file($_FILES['bordereauVersSage']['tmp_name'], 'DocumentsBordereauSage/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name']));
								
								$addUpdate = ArretDouanierSage::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name'].'!',$montantVerse,$user['agence']);
															
								// $addUpdateLog = ArretDouanierSageLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name'].'!',$montantVerse,$user['agence']);
								
								if ($addUpdate == TRUE) {
									$message = "L'enregistrement a ete bien effectue";
									$result = array("statuts" => 0, "mes" => $message);
								} else {
									$message = "Erreur lors de l'enregistrement";
									$result = array("statuts" => 1, "mes" => $message);
								}
								
							}else{
								
								$addUpdate = ArretDouanierSage::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
															
								// $addUpdateLog = ArretDouanierSageLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
								
								if ($addUpdate == TRUE) {
									$message = "L'enregistrement a ete bien effectue";
									$result = array("statuts" => 0, "mes" => $message);
								} else {
									$message = "Erreur lors de l'enregistrement";
									$result = array("statuts" => 1, "mes" => $message);
								}
								
							}
						
					   }else{
						   
						   $message = "L'arret de Caissiere n'a pas encore été effectué";
						   $result = array("statuts" => 1, "mes" => $message);
							
					   }
					
				}else{
						
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence($today,$user['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result['idArretsCaisses'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
					
						}
						
						if(!empty($arretsCaisse)){
							
							if(strcmp($versement,'Oui') == 0){
								
								$montantVerse = $_POST['montantVerseSage'];
								$bordereauVers = $_FILES['bordereauVersSage'];
								
								$bordereau = explode("!",$arretDouanierOld['bordereauVersement']);
								
									if(strcmp($bordereau[0],'Aucun') == 0){
										
										move_uploaded_file($_FILES['bordereauVersSage']['tmp_name'], 'DocumentsBordereauSage/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name']));
										
										move_uploaded_file($_FILES['bordereauVersSage']['tmp_name'], 'DocumentsBordLog/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name']));
										
													
										$addUpdate = ArretDouanierSage::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name'].'!',$montantVerse,$user['agence'],$arretDouanierOld['idArretsDouanierSage']);
										
										// $addUpdate = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name'].'!',$montantVerse,$user['agence']);
										
										if ($addUpdate == TRUE) {
											$message = "L'enregistrement a ete bien effectue";
											$result = array("statuts" => 0, "mes" => $message);
										} else {
											$message = "Erreur lors de l'enregistrement";
											$result = array("statuts" => 1, "mes" => $message);
											}
										
										
									}else{
										
										unlink('DocumentsBordereauSage/'.$bordereau[0]);
										
										move_uploaded_file($_FILES['bordereauVersSage']['tmp_name'], 'DocumentsBordereauSage/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name']));
										
										move_uploaded_file($_FILES['bordereauVersSage']['tmp_name'], 'DocumentsBordLog/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name']));
										
										$addUpdate = ArretDouanierSage::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name'].'!',$montantVerse,$user['agence'],$arretDouanierOld['idArretsDouanierSage']);
										
										// $addUpdate = ArretDouanierSageLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVersSage']['name'].'!',$montantVerse,$user['agence']);
										
										if ($addUpdate == TRUE) {
											$message = "L'enregistrement a ete bien effectue";
											$result = array("statuts" => 0, "mes" => $message);
										} else {
											$message = "Erreur lors de l'enregistrement";
											$result = array("statuts" => 1, "mes" => $message);
											}
										
										
									}
									
								}else{
									
									$montantVerse = $_POST['montantVerseSage'];
									$bordereauVers = $_FILES['bordereauVersSage'];
									
									$bordereau = explode("!",$arretDouanierOld['bordereauVersement']);
									
										if(strcmp($bordereau[0],'Aucun') == 0){
											
											$addUpdate = ArretDouanierSage::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence'],$arretDouanierOld['idArretsDouanierSage']);
											
											// $addUpdate = ArretDouanierSageLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
											
											if ($addUpdate == TRUE) {
												$message = "L'enregistrement a ete bien effectue";
												$result = array("statuts" => 0, "mes" => $message);
											} else {
												$message = "Erreur lors de l'enregistrement";
												$result = array("statuts" => 1, "mes" => $message);
												}
											
										}else{
											
											unlink('DocumentsBordereauSage/'.$bordereau[0]);
											
											$addUpdate = ArretDouanierSage::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence'],$arretDouanierOld['idArretsDouanierSage']);
																		
											// $addUpdate = ArretDouanierSageLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
											
											if ($addUpdate == TRUE) {
												$message = "L'enregistrement a ete bien effectue";
												$result = array("statuts" => 0, "mes" => $message);
											} else {
												$message = "Erreur lors de l'enregistrement";
												$result = array("statuts" => 1, "mes" => $message);
												}
											
										}
									
									}
							
						}else{
							
							$message = "L'arret de Caissiere n'a pas encore été effectué";
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
	
	public function detailsArretsDouanierSage(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
            $stmtArretDouanier = ArretDouanierSage::searchById($id);
			$arretsDouanier = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretDouanier, SQLSRV_FETCH_ASSOC)){
					
					
					$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretVirementCaisse"=>$result1['arretVirementCaisse'],
							"arretComplementCaisse"=>$result1['arretComplementCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"totalBonPros"=>$result1['totalBonPros'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$arretsDouanier =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"observationCpta"=>$result['observationCpta'],
									"observationGestion"=>$result['observationGestion'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$arretsCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretTpeCaisse"=>$arretsCaisse['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"arretVirementCaisse"=>$arretsCaisse['arretVirementCaisse'],
									"arretComplementCaisse"=>$arretsCaisse['arretComplementCaisse'],
									"totalBonPros"=>$arretsCaisse['totalBonPros'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							// $arretsDouaniers[] = $pers;
						
					}
					
				
            if(!empty($arretsDouanier)){
				
				$privilege = explode(" ", $user['privilege']);
					
				$privileges = 'Agence';
				if(in_array($privilege[0] ,explode(',',$privileges))) {
					
					
                $content = '
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Date  </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])) .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Info </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsDouanier['arretInfo'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Douanier</div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsDouanier['arretDouanier'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name"> Total Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['totalCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Difference Douanier </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['diffDouanier'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Difference Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['diffCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Observation Chef Agence </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['observationChef'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Agence </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['agence'].  '</span>
							</div>
						</div>
					</div>
                
                ';
                $result = array("statuts" => 0, "content" => $content);
					
				}else{
					
					
                $content = '
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Date  </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])) .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Info </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsDouanier['arretInfo'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Douanier</div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsDouanier['arretDouanier'] .  '</span>
							</div>
						</div>

						<div class="profile-info-row">
							<div class="profile-info-name"> Complement Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretComplementCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Carte Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretCarteCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Cash </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretCashCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Orange Mobile Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretOrangeCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Mtn Mobile Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretMtnCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret TPE Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretTpeCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Cheque Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretChequeCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Virement Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['arretVirementCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Total Bon Prospecteur Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['totalBonPros'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Total Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['totalCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Difference Douanier </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['diffDouanier'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Difference Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['diffCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Observation Chef Agence </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['observationChef'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Agence </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['agence'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Observation CPTA </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['observationCpta'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Observation Controle Gestion </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretsDouanier['observationGestion'].  '</span>
							</div>
						</div>
					</div>
                
                ';
                $result = array("statuts" => 0, "content" => $content);
					
				}
				
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
	
	public function detailsArretsDouanierVerseSage(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
            $stmtArretDouanier = ArretDouanierSage::searchById($id);
			$arretsDouanier = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretDouanier, SQLSRV_FETCH_ASSOC)){
					
					
					$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretVirementCaisse"=>$result1['arretVirementCaisse'],
							"arretComplementCaisse"=>$result1['arretComplementCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$arretsDouanier =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"observationCpta"=>$result['observationCpta'],
									"versements"=>$result['versements'],
									"observationVersements"=>$result['observationVersements'],
									"bordereauVersement"=>$result['bordereauVersement'],
									"MontantVerse"=>$result['MontantVerse'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$arretsCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretTpeCaisse"=>$arretsCaisse['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"arretVirementCaisse"=>$arretsCaisse['arretVirementCaisse'],
									"arretComplementCaisse"=>$arretsCaisse['arretComplementCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							// $arretsDouaniers[] = $pers;
						
					}
					
				
            if(!empty($arretsDouanier)){
				
			$bordereau = explode("!",$arretsDouanier['bordereauVersement']);
						
					if(strcmp($bordereau[0],'Aucun') == 0){ 
					
						
                $content = '
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Date  </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])) .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Versement / Ramassage </div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsDouanier['versements'] .  '</span>
							</div>
						</div>
						<div class="profile-info-row">
							<div class="profile-info-name">Observation Versement</div>
							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretsDouanier['observationVersements'] .  '</span>
							</div>
						</div>

					</div>
                
                ';
					
					
					}else{
						
						
					$content = '
						
						<div class="profile-user-info profile-user-info-striped">
							<div class="profile-info-row">
								<div class="profile-info-name"> Date  </div>

								<div class="profile-info-value">
									<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])) .  '</span>
								</div>
							</div>
							
							<div class="profile-info-row">
								<div class="profile-info-name"> Versement / Ramassage </div>
								<div class="profile-info-value">
									<span class="editable" id="username">' . $arretsDouanier['versements'] .  '</span>
								</div>
							</div>
							
							<div class="profile-info-row">
								<div class="profile-info-name">Montant Verse</div>
								<div class="profile-info-value">
									<span class="editable" id="username">' . $arretsDouanier['MontantVerse'] .  '</span>
								</div>
							</div>
							
							<div class="profile-info-row">
								<div class="profile-info-name">Observation Versement</div>
								<div class="profile-info-value">
									<span class="editable" id="username">' . $arretsDouanier['observationVersements'] .  '</span>
								</div>
							</div>

						</div>
					
					';
						
						
					}
					
				
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
	
	
	public function interfaceCpta(){
		
		if(isset($_POST['search']) && !empty($_POST['search'])){
			
			$search = $_POST['search'];
			$session = new Session();
			$session->write('search',$search);
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanierSage::oldCaisseDay($search);
			$arretsDouaniers = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"arretVirementCaisse"=>$result1['arretVirementCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"versements"=>$result['versements'],
									"observationVersements"=>$result['observationVersements'],
									"bordereauVersement"=>$result['bordereauVersement'],
									"MontantVerse"=>$result['MontantVerse'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result1['arretMtnCaisse'],
									"arretTpeCaisse"=>$result1['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"arretVirementCaisse"=>$arretsCaisse['arretVirementCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							$arretsDouaniers[] = $pers;
						
			}

        $this->render('arretsDouaniersSage.interfaceCpta',compact('arretsDouaniers','arretsOld'));
			
			
		}else{
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanierSage::all();
			$arretsDouaniers = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"versements"=>$result['versements'],
									"observationVersements"=>$result['observationVersements'],
									"bordereauVersement"=>$result['bordereauVersement'],
									"MontantVerse"=>$result['MontantVerse'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result1['arretMtnCaisse'],
									"arretTpeCaisse"=>$result1['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							$arretsDouaniers[] = $pers;
						
			}

        $this->render('arretsDouaniersSage.interfaceCpta',compact('arretsDouaniers','arretsOld'));
			
			
		}	

    }
	
	
	
	// Les modifications de la comptabilite pour les agences SAGE 100
	
	public function miseJourArretsCptaSage(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && !empty($_POST['id'])){
				
            $id = $_POST['id'];
				
			$stmtArretDouanier = ArretDouanierSage::searchById($id);
			$arretsDouanier = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretDouanier, SQLSRV_FETCH_ASSOC)){
					
					
					$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"arretVirementCaisse"=>$result1['arretVirementCaisse'],
							"arretComplementCaisse"=>$result1['arretComplementCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$arretsDouanier =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"observationCpta"=>$result['observationCpta'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$arretsCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"arretVirementCaisse"=>$arretsCaisse['arretVirementCaisse'],
									"arretComplementCaisse"=>$arretsCaisse['arretComplementCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							// $arretsDouaniers[] = $pers;
						
					}
				
				if(!empty($arretsDouanier)){
					
						$content = '
					
					<form action="'. App::url("ajax.arretsDouanierSage.miseJourArretsCptaEditSage").'" id="form-miseJourArretsCptaEditSage">
						<input type="hidden" id="idArretsDouanierSage" value="'.$arretsDouanier['idArretsDouanierSage'].'" />
							<div class="row">
								<div class="col-sm-12">
								
									<label for="form-field-8">Observation</label>

									<textarea class="form-control obsCptaSage" id="obsCptaSage" name="obsCptaSage"  placeholder="Description">'.$arretsDouanier['observationCpta'].'</textarea>
								</div>
							</div>
							
						<div class="row mar-top" style=" margin-top: 20px ">
							<div class="col-md-4">

							</div>
							<div class="col-md-4">

							</div>
							<div class="col-md-4 right" >
								<button type="submit"  id="btnDeleteUser" class="btn btn-primary btn-block btn-labeled ">Valider</button>
							</div>
						</div>
					</form>
					
					<script type="text/javascript">
					
						$( "#form-miseJourArretsCptaEditSage" ).on("submit", function(e) {
							e.preventDefault();
							
							var id = $("#idArretsDouanierSage").val();
							var url = $(this).attr("action");
							var obsCpta = $("#obsCptaSage").val();
								
								if (id != " " && url != " " && obsCpta != " "){
									
								$.ajax({
									type: "post",
									url: url,
									data: "id="+id+"&obsCpta="+obsCpta,
									datatype: "json",
									success: function (json) {
										if (json.statuts == 0){
											alert(json.mes);
											window.location.reload();
										}else if(json.statuts == 2){
											alert(json.mes);
											window.location.reload();
										}else{
											alert(json.mes);
										}
									},
									error: function(jqXHR, textStatus, errorThrown){
										alert("erreur : "+errorThrown);
									}
								});
							}else{
								alert("Veuillez remplir tous les champs");
							}
										
						});
					</script>
                ';
				
				$result = array("statuts" => 0, "content" => $content);	
						
				}else{
					$message = ' Cette element n\'existe pas ';
					$result = array("statuts" => 1, "mes" => $message);
				}
				
        }else{
            $message = 'Une erreur est survenue, réessayer';
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }
	
	public function miseJourArretsCptaEditSage(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && isset($_POST['obsCpta'])){
				
            $id = $_POST['id'];
            $obsCpta = $_POST['obsCpta'];
			
            if(!empty($_POST['id']) && !empty($_POST['obsCpta'])){
				
				$stmtArretDouanier = ArretDouanierSage::searchById($id);
			$arretsDouanier = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretDouanier, SQLSRV_FETCH_ASSOC)){
					
					
					$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$arretsDouanier =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"observationCpta"=>$result['observationCpta'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$arretsCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretTpeCaisse"=>$arretsCaisse['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							// $arretsDouaniers[] = $pers;
						
					}
				
				$auth = App::getDBAuth();
				$session = Session::getInstance();
				
				if(!empty($arretsDouanier)){
					
					if (!empty($_SESSION['user']) && isset($_SESSION['user'])){ 
							
						$user = $_SESSION['user'];
						
						$date = date('d-m-Y');
						$today = date("Y-m-d h:m:s");
					
						$addMiseJour = ArretDouanierSage::miseJourCpta($obsCpta,$id);

							if ($addMiseJour === true) {
								
								$message = "La mise jour a bien ete enregistre ";
								$result = array("statuts" => 0, "mes" => $message);
							} else {
								
								$message = "Erreur lors de l'enregistrement";
								$result = array("statuts" => 1,"mes" => $message);
							}
						}else{
					
					$message = "Erreur lors de l'enregistrement";
					$result = array("statuts" => 2,"mes" => $message);
				}
				
				}else{
					$message = ' Cette element n\'existe pas ';
					$result = array("statuts" => 1, "mes" => $message);
				}
				
            }else{
				
                $message = 'Renseigner tous les champs obligatoires ';
                $result = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = 'Une erreur est survenue, réessayer !!!!!!';
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }
	
	
	public function interfaceGestionSage(){
		
		
		if(isset($_POST['search0']) && !empty($_POST['search0'])){
			
			$search = $_POST['search0'];
			$session = new Session();
			$session->write('search0',$search);
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanierSage::oldCaisseDay($search);
			$arretsDouaniers = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result1['arretMtnCaisse'],
									"arretTpeCaisse"=>$result1['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							$arretsDouaniers[] = $pers;
						
			}
			
        $this->render('arretsDouaniersSage.interfaceGestion',compact('arretsDouaniers'));
			
		}else{
				
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanierSage::all();
			$arretsDouaniers = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)) {
				
				$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result1['arretMtnCaisse'],
									"arretTpeCaisse"=>$result1['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);
							
							$arretsDouaniers[] = $pers;
						
			}
			
        $this->render('arretsDouaniersSage.interfaceGestion',compact('arretsDouaniers'));
				
			}

    }
	
	public function miseJourArretsControleSage(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && !empty($_POST['id'])){
				
            $id = $_POST['id'];
				
			$stmtArretDouanier = ArretDouanierSage::searchById($id);
			$arretsDouanier = array();
			$arretControle = array();
			$actions = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretDouanier, SQLSRV_FETCH_ASSOC)){
					
					$stmt = User::searchById($result['idUser']);
					$user1 = array();
					while ($result1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
						$user1 = array("idUser"=>$result1['idUser'],
							"login"=>$result1['login'],
							"agence"=>$result1['idAgence'],
						);
					}
					
					$stmtAgence = Agence::searchById($user1['agence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
					    $formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaissesSage::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaissesSage"=>$result1['idArretsCaissesSage'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
						
						$stmtArretControle = ArretControleSage::SearchByArret($result['idArretsDouanierSage']);
						
						while ($result3= sqlsrv_fetch_array($stmtArretControle, SQLSRV_FETCH_ASSOC)) {
							
							$arretControle =  array("idArretControleSage"=>$result3['idArretControleSage'],
							"controlePhysique"=>$result3['controlePhysique'],
							"commentaires"=>$result3['commentaires'],
							"dateEntree"=>$result3['dateEntree'],
						);
						
							$stmtActions = ActionsArretsSage::SearchByArret($result3['idArretControleSage']);
							$act = array();
							while ($result4= sqlsrv_fetch_array($stmtActions, SQLSRV_FETCH_ASSOC)) {
								
								$act =  array("idActionsArretsSage"=>$result4['idActionsArretsSage'],
									"designation"=>$result4['designation'],
									"delai"=>$result4['delai'],
									"pilotes"=>$result4['pilotes'],
									"idArretControle"=>$result4['idArretControle'],
									"dateEntree"=>$result4['dateEntree'],
								);
								
								 $actions[] = $act;
								
							}
							
						}
						
							
							$arretsDouanier =  array("idArretsDouanierSage"=>$result['idArretsDouanierSage'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"observationGestion"=>$result['observationGestion'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretOrangeCaisse"=>$arretsCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretTpeCaisse"=>$arretsCaisse['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
									"arretControle"=>$arretControle,
									"actions"=>$actions,
								);
							
							// $arretsDouaniers[] = $pers;
					}
					
					
				$listePhysique  = ' ';
				
				if(!empty($arretControle['controlePhysique'])){
				
					$privilege = explode(" ", $arretControle['controlePhysique']);
                    if (strcmp($privilege[0],'Oui') == 0) {
                        $listePhysique .= " <option value='Oui' selected> Oui </option>";
                    } else {
                        $listePhysique .= " <option value='Oui'> Oui </option>";
                    }
					
					if (strcmp($privilege[0],'Non') == 0) {
                        $listePhysique .= " <option value='Non' selected> Non </option>";
                    } else {
                        $listePhysique .= " <option value='Non'> Non </option>";
                    }
				}
					
					$listeActions  = ' ';
					
					$t = 0;
					
					for($i = 0; $i < sizeof($actions);$i++){ 
						
						$t = $t+1;
						
						$listeActions  .= ' 
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage'.$t.'" name="ActionEditsage'.$t.'" value="'.$actions[$i]['designation'].'" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage'.$t.'" name="delaiEditSage'.$t.'" value="'.date('Y-m-d',date_timestamp_get($actions[$i]['delai'])).'" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div> ';
						
					}
					
					if(sizeof($actions) == 2 ){
						
						$listeActions  .= ' 
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage3" name="ActionEditSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage3" name="delaiEditSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div> ';
						
					}
					
					if(sizeof($actions) == 1){
						
						$listeActions  .= ' 
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage2" name="ActionEditSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage2" name="delaiEditSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage3" name="ActionEditSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage3" name="delaiEditSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div> ';
						
					}
					
					if(sizeof($actions) == 0){
						
						$listeActions  .= ' 
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage1" name="ActionEditSage1" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage2" name="delaiEditSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage2" name="ActionEditSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage2" name="delaiEditSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEditSage3" name="ActionEditSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEditSage3" name="delaiEditSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div> ';
						
					}	
					
					
				if(!empty($arretControle)){
					
						$content = '
					
					<form action="'. App::url("ajax.arretControleSage.ajoutArretsControleSage").'" id="form-miseJourArretsControleEditSage">
						<input type="hidden" id="idArretsCaisseEditSage" value="'.$arretsDouanier['idArretsDouanierSage'].'" />
							
					<label for="form-field-8">Date Entree</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="dateArretSage" name="dateArretSage" value="'.date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])).'" placeholder="Date Entree" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Agence</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="agenceArretSage" name="agenceArretSage" value="'.$arretsDouanier['agence'].'" placeholder="Agence Arret" readonly />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Caisse</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretCaisseSage" name="diffArretCaisseSage" value="'.$arretsDouanier['diffCaisse'].'" placeholder="Difference Arret Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Douanier</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretDouanierSage" name="diffArretDouanierSage" value="'.$arretsDouanier['diffDouanier'].'" placeholder="Total Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Controle Physique et signature</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<select type="text" class="form-control" id="controlePhysEditSage" name="controlePhysEditSage">
								'.$listePhysique.'
							</select>
						</span>
					</label>
					<label for="form-field-8">Commentaires</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<textarea class="form-control" id="commentaireControleEditSage" name="commentaireControleEditSage"  placeholder="Entrer les commentaires">'.$arretControle['commentaires'].'</textarea>
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					
					<div class="row">
						'.$listeActions.'
					</div>
							
						<div class="row mar-top" style=" margin-top: 20px ">
							<div class="col-md-4">

							</div>
							<div class="col-md-4">

							</div>
							<div class="col-md-4 right" >
								<button type="submit"  id="btnDeleteUser" class="btn btn-primary btn-block btn-labeled ">Valider</button>
							</div>
						</div>
					</form>
					
					<script type="text/javascript">
					
						$( "#form-miseJourArretsControleEditSage" ).on("submit", function(e) {
						e.preventDefault();
						
							var idArretsCaisse = $("#idArretsCaisseEditSage").val();
							var controlePhys = $("#controlePhysEditSage").val();
							var commentaireControle = $("#commentaireControleEditSage").val();
							var Action1 = $("#ActionEditSage1").val();
							var delai1 = $("#delaiEditSage1").val();
							var Action2 = $("#ActionEditSage2").val();
							var delai2 = $("#delaiEditSage2").val();
							var Action3 = $("#ActionEditSage3").val();
							var delai3 = $("#delaiEditSage3").val();
							
							var url = $(this).attr("action");
							
							if (idArretsCaisse !="" && controlePhys !="" && commentaireControle !="") {
								
									$.ajax({
										type: "post",
										url: url,
										data: "idArretsCaisse="+idArretsCaisse+"&controlePhys="+controlePhys+"&commentaireControle="+commentaireControle+"&Action1="
											  +Action1+"&delai1="+delai1+"&Action2="+Action2+"&delai2="+delai2+"&Action3="+Action3+"&delai3="+delai3,
										datatype: "json",
										
										beforeSend: function () {
												$(".loaderRegister").removeClass("hidden");
												$(".connectUser").addClass("hidden");
											},
											
										success: function (json) {
											if (json.statuts == 0){
												alert(json.mes);
												window.location.reload();
											}else{
												alert(json.mes);
											}
										},
										
										complete: function () {
												$(".connectUser").removeClass("hidden");
												$(".loaderRegister").addClass("hidden");
											},
											
										error: function(jqXHR, textStatus, errorThrown){
											alert("erreur : "+errorThrown);
										}
									});
									
							}else{
								alert("Veuillez rensiegner tous les champs obligatoires javascript");
							}
						
						});
						
					</script>
                ';
				
				$result = array("statuts" => 0, "content" => $content);	
						
				}else{
					
					$content = '
						
				<form action="'.App::url('ajax.arretControleSage.ajoutArretsControleSage').'" method="POST" id="form-ajoutArretsControleTestSage" >
					<input type="hidden" id="idArretsCaisseTestSage" value="'.$arretsDouanier['idArretsDouanierSage'].'" />
					<label for="form-field-8">Date Entree</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="dateArretSage" name="dateArretSage" value="'.date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])).'" placeholder="Date Entree" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Agence</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="agenceArretSage" name="agenceArretSage" value="'.$arretsDouanier['agence'].'" placeholder="Agence Arret" readonly />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Caisse</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretCaisseSage" name="diffArretCaisseSage" value="'.$arretsDouanier['diffCaisse'].'" placeholder="Difference Arret Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Douanier</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretDouanierSage" name="diffArretDouanierSage" value="'.$arretsDouanier['diffDouanier'].'" placeholder="Total Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Controle Physique et signature</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<select type="text" class="form-control" id="controlePhysTestSage" name="controlePhysTestSage">
								<option selected="selected" value="">Choisir une option</option>
								<option value="Oui"> Oui </option>
								<option value="Non"> Non </option>
							</select>
						</span>
					</label>
					<label for="form-field-8">Commentaires</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<textarea class="form-control" id="commentaireControleTestSage" name="commentaireControleTestSage"  placeholder="Entrer les commentaires"></textarea>
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					
					<div class="row">
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionTestSage1" name="ActionTestSage1" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiTestSage1" name="delaiTestSage1" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionTestSage2" name="ActionTestSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiTestSage2" name="delaiTestSage2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionTestSage3" name="ActionTestSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiTestSage3" name="delaiTestSage3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
					</div>
					
					<div class="space-24"></div>

					<div class="clearfix resetUser">
						<button type="submit" class="width-50 pull-right btn btn-sm btn-primary">
							Valider
						</button>
					</div>
					
					<div class="clearfix hidden loaderReset">
						<center>
							<h2 class="header smaller lighter grey">
								<i class="ace-icon fa fa-spinner fa-spin green bigger-125"></i>
							</h2>
						</center>
					</div>

                </form>
				
				<script type="text/javascript">
				
					$( "#form-ajoutArretsControleTestSage" ).on("submit", function(e) {
					e.preventDefault();
					
						var idArretsCaisse = $("#idArretsCaisseTestSage").val();
						var controlePhys = $("#controlePhysTestSage").val();
						var commentaireControle = $("#commentaireControleTestSage").val();
						var Action1 = $("#ActionTestSage1").val();
						var delai1 = $("#delaiTestSage1").val();
						var Action2 = $("#ActionTestSage2").val();
						var delai2 = $("#delaiTestSage2").val();
						var Action3 = $("#ActionTestSage3").val();
						var delai3 = $("#delaiTestSage3").val();
						
						var url = $(this).attr("action");
						
						if (idArretsCaisse !="" && controlePhys !="" && commentaireControle !="" ) {
							
								$.ajax({
									type: "post",
									url: url,
									data: "idArretsCaisse="+idArretsCaisse+"&controlePhys="+controlePhys+"&commentaireControle="+commentaireControle+"&Action1="
										  +Action1+"&delai1="+delai1+"&Action2="+Action2+"&delai2="+delai2+"&Action3="+Action3+"&delai3="+delai3,
									datatype: "json",
									
									beforeSend: function () {
											$(".loaderRegister").removeClass("hidden");
											$(".connectUser").addClass("hidden");
										},
										
									success: function (json) {
										if (json.statuts == 0){
											alert(json.mes);
											window.location.reload();
										}else{
											alert(json.mes);
										}
									},
									
									complete: function () {
											$(".connectUser").removeClass("hidden");
											$(".loaderRegister").addClass("hidden");
										},
										
									error: function(jqXHR, textStatus, errorThrown){
										alert("erreur : "+errorThrown);
									}
								});
								
						}else{
							alert("Veuillez rensiegner tous les champs obligatoires javascript");
						}
					
					});
									
					</script>			
					';
					
					$result = array("statuts" => 0, "content" => $content);	
				}
				
        }else{
            $message = 'Une erreur est survenue, réessayer';
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }
	
	
}

?>