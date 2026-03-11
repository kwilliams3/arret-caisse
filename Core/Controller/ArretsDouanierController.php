<?php

namespace Core\Controller;

require 'Core\Classes\PHPExcel.php';
require 'Core\Classes\PHPExcel\IOFactory.php';

use Core\Model\App;
use Core\Database\ArretControle;
use Core\Database\ActionsArrets;
use Core\Database\ArretsCaisses;
use Core\Database\ArretDouanier;
use Core\Database\ArretDouanierLog;
use Core\Database\Agence;
use Core\Database\User;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;
use Core\Model\Sms;

class ArretsDouanierController extends AppController{

    public function index(){
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence($today,$user['agence']);
				$arretsOld = array();
				while ($result3 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
					
					$arretsOld =  array("idArretsCaisses"=>$result3['idArretsCaisses'],
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
					
				
			$stmtArretsDouanier = ArretDouanier::all();
			$arretsDouaniers = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)){
				
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
				if(in_array($user['privilege'] ,explode(',',$privileges))) {
					
					if($user['agence'] == $result['idAgence']){
						
						$formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaisses::oldCaisseDay($formatDate);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$stmtCaisse = User::searchById($result1['idUser']);
							$userCaisse = array();
							while ($result2 = sqlsrv_fetch_array($stmtCaisse, SQLSRV_FETCH_ASSOC)){
								$userCaisse = array("idUser"=>$result2['idUser'],
									"login"=>$result2['login'],
									"agence"=>$result2['idAgence'],
								);
							}
							
							$stmtAgenceCaisse = Agence::searchById($userCaisse['agence']);
							$agenceCaisse = array();
							while ($result3= sqlsrv_fetch_array($stmtAgenceCaisse, SQLSRV_FETCH_ASSOC)) {
								$agenceCaisse = array(
									"id" => $result3['idAgence'],
									"designation" => $result3['designation'],
								);
							}
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$agenceCaisse['designation'],
						);
							
						}
						
						$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
								"dateEntree"=>$result['dateEntree'],
								"totalCaisse"=>$arretsCaisse['totalCaisse'],
								"agence"=>$agence['designation'],
							);
							
							$arretsDouaniers[] = $pers;
					}
					
				}else{
					
					$formatDate = date('Y-m-d',date_timestamp_get($result['dateEntree']));
						$stmtArretsCaisses = ArretsCaisses::oldCaisseDay($formatDate);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$stmtCaisse = User::searchById($result1['idUser']);
							$userCaisse = array();
							while ($result2 = sqlsrv_fetch_array($stmtCaisse, SQLSRV_FETCH_ASSOC)){
								$userCaisse = array("idUser"=>$result2['idUser'],
									"login"=>$result2['login'],
									"agence"=>$result2['idAgence'],
								);
							}
							
							$stmtAgenceCaisse = Agence::searchById($userCaisse['agence']);
							$agenceCaisse = array();
							while ($result3= sqlsrv_fetch_array($stmtAgenceCaisse, SQLSRV_FETCH_ASSOC)) {
								$agenceCaisse = array(
									"id" => $result3['idAgence'],
									"designation" => $result3['designation'],
								);
							}
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$agenceCaisse['designation'],
						);
							
						}
						
						$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
								"dateEntree"=>$result['dateEntree'],
								"totalCaisse"=>$arretsCaisse['totalCaisse'],
								"agence"=>$agence['designation'],
							);
						
							$arretsDouaniers[] = $pers;
					
				}
				
			}
			
			$this->render('arretsDouaniers.index',compact('arretsDouaniers','arretsOld'));
	    }
		
						
	
	public function ajoutArretsDouanier(){
		
		header('content-type: application/json');
        $result = [];
		
        if(isset($_POST['arretDouanier']) && !empty($_POST['arretDouanier']) && isset($_POST['arretInfo']) && !empty($_POST['arretInfo']) 
			&& !empty($_POST['obsArretChef']) && isset($_POST['obsArretChef'])&& !empty($_POST['observationVers']) && isset($_POST['observationVers'])
			&& !empty($_POST['versement']) && isset($_POST['versement'])) {
			
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
				$date = date('Y-m-d');
				$today = date("Y-m-d h:m:s");
				
				$todayInt = date("d-m-Y h:m:s");

				$arretDouanier = $_POST['arretDouanier'];
				$arretInfo = $_POST['arretInfo'];
				$obsArretChef = $_POST['obsArretChef'];
				$observationVers = $_POST['observationVers'];
				$versement = $_POST['versement'];
			
				$stmt = ArretDouanier::oldCaisseDayId($today,$user['idUser']);
				$arretDouanierOld = array();
				while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
				
					$arretDouanierOld = array("idArretsDouanier"=>$result['idArretsDouanier'],
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
					
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence($today,$user['agence']);
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
								
								$montantVerse = $_POST['montantVerse'];
								$bordereauVers = $_FILES['bordereauVers'];
								
								move_uploaded_file($_FILES['bordereauVers']['tmp_name'], 'DocumentsBordereau/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVers']['name']));
								
								$addUpdate = ArretDouanier::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVers']['name'].'!',$montantVerse,$user['agence']);
															
								$addUpdateLog = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVers']['name'].'!',$montantVerse,$user['agence']);
								
								if ($addUpdate == TRUE) {
									$message = "L'enregistrement a ete bien effectue";
									$result = array("statuts" => 0, "mes" => $message);
								} else {
									$message = "Erreur lors de l'enregistrement";
									$result = array("statuts" => 1, "mes" => $message);
								}
								
							}else{
								
								$addUpdate = ArretDouanier::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
															
								$addUpdateLog = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
								
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
						
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence($today,$user['agence']);
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
								
								$montantVerse = $_POST['montantVerse'];
								$bordereauVers = $_FILES['bordereauVers'];
								
								$bordereau = explode("!",$arretDouanierOld['bordereauVersement']);
								
									if(strcmp($bordereau[0],'Aucun') == 0){
										
										move_uploaded_file($_FILES['bordereauVers']['tmp_name'], 'DocumentsBordereau/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVers']['name']));
										
										move_uploaded_file($_FILES['bordereauVers']['tmp_name'], 'DocumentsBordLog/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVers']['name']));
										
													
										$addUpdate = ArretDouanier::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVers']['name'].'!',$montantVerse,$user['agence'],$arretDouanierOld['idArretsDouanier']);
										
										$addUpdate = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVers']['name'].'!',$montantVerse,$user['agence']);
										
										if ($addUpdate == TRUE) {
											$message = "L'enregistrement a ete bien effectue";
											$result = array("statuts" => 0, "mes" => $message);
										} else {
											$message = "Erreur lors de l'enregistrement";
											$result = array("statuts" => 1, "mes" => $message);
											}
										
										
									}else{
										
										unlink('DocumentsBordereau/'.$bordereau[0]);
										
										move_uploaded_file($_FILES['bordereauVers']['tmp_name'], 'DocumentsBordereau/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVers']['name']));
										
										move_uploaded_file($_FILES['bordereauVers']['tmp_name'], 'DocumentsBordLog/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVers']['name']));
										
										$addUpdate = ArretDouanier::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVers']['name'].'!',$montantVerse,$user['agence'],$arretDouanierOld['idArretsDouanier']);
										
										$addUpdate = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,$agenceUser['designation'].'_'.$_FILES['bordereauVers']['name'].'!',$montantVerse,$user['agence']);
										
										if ($addUpdate == TRUE) {
											$message = "L'enregistrement a ete bien effectue";
											$result = array("statuts" => 0, "mes" => $message);
										} else {
											$message = "Erreur lors de l'enregistrement";
											$result = array("statuts" => 1, "mes" => $message);
											}
										
										
									}
									
								}else{
									
									$montantVerse = $_POST['montantVerse'];
									$bordereauVers = $_FILES['bordereauVers'];
									
									$bordereau = explode("!",$arretDouanierOld['bordereauVersement']);
									
										if(strcmp($bordereau[0],'Aucun') == 0){
											
											$addUpdate = ArretDouanier::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence'],$arretDouanierOld['idArretsDouanier']);
											
											$addUpdate = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
											
											if ($addUpdate == TRUE) {
												$message = "L'enregistrement a ete bien effectue";
												$result = array("statuts" => 0, "mes" => $message);
											} else {
												$message = "Erreur lors de l'enregistrement";
												$result = array("statuts" => 1, "mes" => $message);
												}
											
										}else{
											
											unlink('DocumentsBordereau/'.$bordereau[0]);
											
											$addUpdate = ArretDouanier::save(date('Y-m-d',date_timestamp_get($arretDouanierOld['dateEntree'])),$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence'],$arretDouanierOld['idArretsDouanier']);
																		
											$addUpdate = ArretDouanierLog::save($today,$arretInfo,$arretDouanier,($arretInfo-$arretsCaisse['totalCaisse']),($arretInfo-$arretDouanier),$obsArretChef,$user['idUser'],$versement,$observationVers,'Aucun!',0,$user['agence']);
											
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
			
			$stmtArretsDouanier = ArretDouanier::oldCaisseDay($search);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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

        $this->render('arretsDouaniers.interfaceCpta',compact('arretsDouaniers','arretsOld'));
			
			
		}else{
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanier::all();
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$result['idAgence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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

        $this->render('arretsDouaniers.interfaceCpta',compact('arretsDouaniers','arretsOld'));
			
			
		}	

    }
	
	public function interfaceGestion(){
		
		
		if(isset($_POST['search0']) && !empty($_POST['search0'])){
			
			$search = $_POST['search0'];
			$session = new Session();
			$session->write('search0',$search);
			
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanier::oldCaisseDay($search);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
			
        $this->render('arretsDouaniers.interfaceGestion',compact('arretsDouaniers'));
			
		}else{
				
			$today = date("Y-m-d h:m:s");
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			$today = date("Y-m-d");
			
			$stmtArretsDouanier = ArretDouanier::all();
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$result['idAgence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
						
						  // var_dump($result['idArretsDouanier'].'separ');
						  // var_dump($result['idUser']);
						  // var_dump($result['idAgence']);
						  // var_dump($formatDate);
						  // var_dump($user1['agence']);
						  // var_dump($arretsCaisse); 
							
							$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
			
			 // die();
			
        $this->render('arretsDouaniers.interfaceGestion',compact('arretsDouaniers'));
				
			}

    }
	
	public function detailsArretsDouanier(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
            $stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
							<div class="profile-info-name"> Bon Prospecteur Caisse</div>

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
	
	public function miseJourArretsCpta(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && !empty($_POST['id'])){
				
            $id = $_POST['id'];
				
			$stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
					
					<form action="'. App::url("ajax.arretsDouanier.miseJourArretsCptaEdit").'" id="form-miseJourArretsCptaEdit">
						<input type="hidden" id="idArretsDouanier" value="'.$arretsDouanier['idArretsDouanier'].'" />
							<div class="row">
								<div class="col-sm-12">
								
									<label for="form-field-8">Observation</label>

									<textarea class="form-control obsCpta" id="obsCpta" name="obsCpta"  placeholder="Description">'.$arretsDouanier['observationCpta'].'</textarea>
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
					
						$( "#form-miseJourArretsCptaEdit" ).on("submit", function(e) {
							e.preventDefault();
							
							var id = $("#idArretsDouanier").val();
							var url = $(this).attr("action");
							var obsCpta = $("#obsCpta").val();
								
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
	
	public function miseJourArretsCptaEdit(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && isset($_POST['obsCpta'])){
				
            $id = $_POST['id'];
            $obsCpta = $_POST['obsCpta'];
			
            if(!empty($_POST['id']) && !empty($_POST['obsCpta'])){
				
				$stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
					
						$addMiseJour = ArretDouanier::miseJourCpta($obsCpta,$id);

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
	
	public function miseJourArretsGestion(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && !empty($_POST['id'])){
				
            $id = $_POST['id'];
				
			$stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
								);
							
							// $arretsDouaniers[] = $pers;
						
					}
				
				if(!empty($arretsDouanier)){
					
						$content = '
					
					<form action="'. App::url("ajax.arretsDouanier.miseJourArretsGestionEdit").'" id="form-miseJourArretsGestionEdit">
						<input type="hidden" id="idArretsDouanier" value="'.$arretsDouanier['idArretsDouanier'].'" />
							<div class="row">
								<div class="col-sm-12">
								
									<label for="form-field-8">Observation</label>

									<textarea class="form-control obsGestion" id="obsGestion" name="obsGestion"  placeholder="Description">'.$arretsDouanier['observationGestion'].'</textarea>
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
					
						$( "#form-miseJourArretsGestionEdit" ).on("submit", function(e) {
							e.preventDefault();
							
							var id = $("#idArretsDouanier").val();
							var url = $(this).attr("action");
							var obsGestion = $("#obsGestion").val();
								
								if (id != " " && url != " " && obsGestion != " "){
									
								$.ajax({
									type: "post",
									url: url,
									data: "id="+id+"&obsGestion="+obsGestion,
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
	
	public function miseJourArretsGestionEdit(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && isset($_POST['obsGestion'])){
				
            $id = $_POST['id'];
            $obsGestion = $_POST['obsGestion'];
			
            if(!empty($_POST['id']) && !empty($_POST['obsGestion'])){
				
				$stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
					
						$addMiseJour = ArretDouanier::miseJourGestion($obsGestion,$id);

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
	
	
	public function detailsArretsDouanierVerse(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
            $stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
	
	
	public function miseJourArretsControle(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && !empty($_POST['id'])){
				
            $id = $_POST['id'];
				
			$stmtArretDouanier = ArretDouanier::searchById($id);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
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
						
						$stmtArretControle = ArretControle::SearchByArret($result['idArretsDouanier']);
						
						while ($result3= sqlsrv_fetch_array($stmtArretControle, SQLSRV_FETCH_ASSOC)) {
							
							$arretControle =  array("idArretControle"=>$result3['idArretControle'],
							"controlePhysique"=>$result3['controlePhysique'],
							"commentaires"=>$result3['commentaires'],
							"dateEntree"=>$result3['dateEntree'],
						);
						
							$stmtActions = ActionsArrets::SearchByArret($result3['idArretControle']);
							$act = array();
							while ($result4= sqlsrv_fetch_array($stmtActions, SQLSRV_FETCH_ASSOC)) {
								
								$act =  array("idActionsArrets"=>$result4['idActionsArrets'],
									"designation"=>$result4['designation'],
									"delai"=>$result4['delai'],
									"pilotes"=>$result4['pilotes'],
									"idArretControle"=>$result4['idArretControle'],
									"dateEntree"=>$result4['dateEntree'],
								);
								
								 $actions[] = $act;
								
							}
							
						}
						
							
							$arretsDouanier =  array("idArretsDouanier"=>$result['idArretsDouanier'],
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
									<input type="text" class="form-control" id="ActionEdit'.$t.'" name="ActionEdit'.$t.'" value="'.$actions[$i]['designation'].'" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEdit'.$t.'" name="delaiEdit'.$t.'" value="'.date('Y-m-d',date_timestamp_get($actions[$i]['delai'])).'" placeholder="Date Entree" />
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
									<input type="text" class="form-control" id="ActionEdit3" name="ActionEdit3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEdit3" name="delaiEdit3" value="" placeholder="Date Entree" />
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
									<input type="text" class="form-control" id="ActionEdit2" name="ActionEdit2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEdit2" name="delaiEdit2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionEdit3" name="ActionEdit3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiEdit3" name="delaiEdit3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div> ';
						
					}	
					
					
				if(!empty($arretControle)){
					
						$content = '
					
					<form action="'. App::url("ajax.arretControle.ajoutArretsControle").'" id="form-miseJourArretsControleEdit">
						<input type="hidden" id="idArretsCaisseEdit" value="'.$arretsDouanier['idArretsDouanier'].'" />
							
					<label for="form-field-8">Date Entree</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="dateArret" name="dateArret" value="'.date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])).'" placeholder="Date Entree" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Agence</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="agenceArret" name="agenceArret" value="'.$arretsDouanier['agence'].'" placeholder="Agence Arret" readonly />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Caisse</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretCaisse" name="diffArretCaisse" value="'.$arretsDouanier['diffCaisse'].'" placeholder="Difference Arret Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Douanier</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretDouanier" name="diffArretDouanier" value="'.$arretsDouanier['diffDouanier'].'" placeholder="Total Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Controle Physique et signature</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<select type="text" class="form-control" id="controlePhysEdit" name="controlePhysEdit">
								'.$listePhysique.'
							</select>
						</span>
					</label>
					<label for="form-field-8">Commentaires</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<textarea class="form-control" id="commentaireControleEdit" name="commentaireControleEdit"  placeholder="Entrer les commentaires">'.$arretControle['commentaires'].'</textarea>
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
					
						$( "#form-miseJourArretsControleEdit" ).on("submit", function(e) {
						e.preventDefault();
						
							var idArretsCaisse = $("#idArretsCaisseEdit").val();
							var controlePhys = $("#controlePhysEdit").val();
							var commentaireControle = $("#commentaireControleEdit").val();
							var Action1 = $("#ActionEdit1").val();
							var delai1 = $("#delaiEdit1").val();
							var Action2 = $("#ActionEdit2").val();
							var delai2 = $("#delaiEdit2").val();
							var Action3 = $("#ActionEdit3").val();
							var delai3 = $("#delaiEdit3").val();
							
							var url = $(this).attr("action");
							
							if (idArretsCaisse !="" && controlePhys !="" && commentaireControle !=""  && Action1 !="" && delai1 !="") {
								
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
						
				<form action="'.App::url('ajax.arretControle.ajoutArretsControleajoutArretsControle').'" method="POST" id="form-ajoutArretsControleTest" >
					<input type="hidden" id="idArretsCaisseTest" value="'.$arretsDouanier['idArretsDouanier'].'" />
					<label for="form-field-8">Date Entree</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="dateArret" name="dateArret" value="'.date('d-m-Y',date_timestamp_get($arretsDouanier['dateEntree'])).'" placeholder="Date Entree" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Agence</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="agenceArret" name="agenceArret" value="'.$arretsDouanier['agence'].'" placeholder="Agence Arret" readonly />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Caisse</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretCaisse" name="diffArretCaisse" value="'.$arretsDouanier['diffCaisse'].'" placeholder="Difference Arret Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Difference Douanier</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="diffArretDouanier" name="diffArretDouanier" value="'.$arretsDouanier['diffDouanier'].'" placeholder="Total Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					<label for="form-field-8">Controle Physique et signature</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<select type="text" class="form-control" id="controlePhysTest" name="controlePhysTest">
								<option selected="selected" value="">Choisir une option</option>
								<option value="Oui"> Oui </option>
								<option value="Non"> Non </option>
							</select>
						</span>
					</label>
					<label for="form-field-8">Commentaires</label>
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<textarea class="form-control" id="commentaireControleTest" name="commentaireControleTest"  placeholder="Entrer les commentaires"></textarea>
							<i class="ace-icon fa fa-Cash"></i>
						</span>
					</label>
					
					<div class="row">
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionTest1" name="ActionTest1" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiTest1" name="delaiTest1" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionTest2" name="ActionTest2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiTest2" name="delaiTest2" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Actions</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="text" class="form-control" id="ActionTest3" name="ActionTest3" value="" placeholder="Date Entree" />
									<i class="ace-icon fa fa-Cash"></i>
								</span>
							</label>
						</div>
						
						<div class="col-sm-6">
							<label for="form-field-8">Delai</label>
							<label class="block clearfix">
								<span class="block input-icon input-icon-right">
									<input type="date" class="form-control" id="delaiTest3" name="delaiTest3" value="" placeholder="Date Entree" />
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
				
					$( "#form-ajoutArretsControleTest" ).on("submit", function(e) {
					e.preventDefault();
					
						var idArretsCaisse = $("#idArretsCaisseTest").val();
						var controlePhys = $("#controlePhysTest").val();
						var commentaireControle = $("#commentaireControleTest").val();
						var Action1 = $("#ActionTest1").val();
						var delai1 = $("#delaiTest1").val();
						var Action2 = $("#ActionTest2").val();
						var delai2 = $("#delaiTest2").val();
						var Action3 = $("#ActionTest3").val();
						var delai3 = $("#delaiTest3").val();
						
						var url = $(this).attr("action");
						
						if (idArretsCaisse !="" && controlePhys !="" && commentaireControle !=""  && Action1 !="" && delai1 !="") {
							
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
	
	public function ArretsCaissesPrint(){
				
				$auth = App::getDBAuth();
				$session = Session::getInstance();
				$search = $_SESSION['search'];
				
				$stmtAgence = Agence::all();
				
				$arretsAgences = array();
				$arretAG = array();
				$arretCaisse = array();
				$arretDouanier = array();
					
				while ($result = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
					
					$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence($search,$result['idAgence']);
					
					while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
						
							$arretCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"arretVirementCaisse"=>$result1['arretVirementCaisse'],
							"arretComplementCaisse"=>$result1['arretComplementCaisse'],
							"versementPros"=>$result1['versementProspecteur'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
						);
						
					  }
					  
				    $stmtArretsDouanier = ArretDouanier::oldCaisseAgence($search,$result['idAgence']);
					
					while ($result2 = sqlsrv_fetch_array($stmtArretsDouanier, SQLSRV_FETCH_ASSOC)) {
						
							$arretDouanier =  array("idArretsDouanier"=>$result2['idArretsDouanier'],
									"dateEntree"=>$result2['dateEntree'],
									"arretInfo"=>$result2['arretInfo'],
									"arretDouanier"=>$result2['arretDouanier'],
									"diffCaisse"=>$result2['diffCaisse'],
									"diffDouanier"=>$result2['diffDouanier'],
									"idUser"=>$result2['idUser'],
									"versements"=>$result2['versements'],
									"observationVersements"=>$result2['observationVersements'],
									"bordereauVersement"=>$result2['bordereauVersement'],
									"MontantVerse"=>$result2['MontantVerse'],
									"idUser"=>$result2['idUser'],
								);
						}
				
					if(!empty($arretCaisse) && !empty($arretDouanier)){
						
						$arretAG =  array("arretCashCaisse"=>$arretCaisse['arretCashCaisse'],
									"arretOrangeCaisse"=>$arretCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretCaisse['arretMtnCaisse'],
									"arretTpeCaisse"=>$arretCaisse['arretTpeCaisse'],
									"arretCarteCaisse"=>$arretCaisse['arretCarteCaisse'],
									"arretChequeCaisse"=>$arretCaisse['arretChequeCaisse'],
									"arretVirementCaisse"=>$arretCaisse['arretVirementCaisse'],
									"arretComplementCaisse"=>$arretCaisse['arretComplementCaisse'],
									"versementPros"=>$arretCaisse['versementPros'],
									"totalCaisse"=>$arretCaisse['totalCaisse'],
									"arretInfo"=>$arretDouanier['arretInfo'],
									"arretDouanier"=>$arretDouanier['arretDouanier'],
									"diffCaisse"=>$arretDouanier['diffCaisse'],
									"diffDouanier"=>$arretDouanier['diffDouanier'],
									"versements"=>$arretDouanier['versements'],
									"observationVersements"=>$arretDouanier['observationVersements'],
									"MontantVerse"=>$arretDouanier['MontantVerse'],
									"agence"=>$result['designation'],
									"dateEntree"=>$arretDouanier['dateEntree'],
								);
					
						$arretsAgences[] = $arretAG;
						
					}	
					
				}
				
					$classeur = new \PHPExcel;

					$classeur->getProperties()->setCreator("SORERPCO.SA");

					$classeur->setActiveSheetIndex(0);

					$feuille=$classeur->getActiveSheet();

				 

					// ajout des données dans la feuille de calcul $sheet->mergeCells('A1:D1');
					
					// pour le titre de la feuille
					$feuille->setTitle('Arret Caisse');
					
					// pour la date en cours
					$feuille->mergeCells('A1:M1','Caisse');
					$feuille->SetCellValue('A3', 'Agences');
					$feuille->SetCellValue('B3', 'Etats PV Recu');
					$feuille->SetCellValue('C3', 'Disponibilite');
					$feuille->SetCellValue('D3', 'Ramassage Fonds');
					$feuille->SetCellValue('E3', 'Bordereau Versement');
					$feuille->SetCellValue('F3', 'Caisse Principal');
					$feuille->SetCellValue('G3', 'Transval');
					$feuille->SetCellValue('H3', 'Carte cadeau');
					$feuille->SetCellValue('I3', 'Cheque');
					$feuille->SetCellValue('J3', 'OM');
					$feuille->SetCellValue('K3', 'MOMO');
					$feuille->SetCellValue('L3', 'Virement Banque');
					$feuille->SetCellValue('M3', 'Taux de Ramassage');
					
					if(!empty($arretsAgences)){
							$count = 4;
							/*var_dump($prixArts1);
							die();*/
							
							$countarretsAgences = count($arretsAgences);
							
							// var_dump($countResultEtats);
							
						for ($y = 0 ; $y < $countarretsAgences; $y++) {
					
								$cellule =  strval('A'.$count);
								$cellule1 =  strval('B'.$count);
								$cellule2 =  strval('C'.$count);
								$cellule3 =  strval('D'.$count);
								$cellule4 =  strval('E'.$count);
								$cellule5 =  strval('F'.$count);
								$cellule6 =  strval('G'.$count);
								$cellule7 =  strval('H'.$count);
								$cellule8 =  strval('I'.$count);
								$cellule9 =  strval('J'.$count);
								$cellule10 =  strval('K'.$count);
								$cellule11 =  strval('L'.$count);
								$cellule12 =  strval('M'.$count);
								
								$feuille->SetCellValue($cellule, $arretsAgences[$y]["agence"]);
								$feuille->SetCellValue($cellule1, $arretsAgences[$y]["versements"]);
								$feuille->SetCellValue($cellule2, $arretsAgences[$y]["versements"]);
								$feuille->SetCellValue($cellule3, $arretsAgences[$y]["versements"]);
								$feuille->SetCellValue($cellule4, $arretsAgences[$y]["versements"]);
								$feuille->SetCellValue($cellule5, $arretsAgences[$y]["versements"]);
								$feuille->SetCellValue($cellule6, $arretsAgences[$y]["versements"]);
								$feuille->SetCellValue($cellule7, $arretsAgences[$y]["arretCarteCaisse"]);
								$feuille->SetCellValue($cellule8, $arretsAgences[$y]["arretChequeCaisse"]);
								$feuille->SetCellValue($cellule9, $arretsAgences[$y]["arretOrangeCaisse"]);
								$feuille->SetCellValue($cellule10, $arretsAgences[$y]["arretMtnCaisse"]);
								$feuille->SetCellValue($cellule11, $arretsAgences[$y]["arretVirementCaisse"]);
								$feuille->SetCellValue($cellule12, $arretsAgences[$y]["versements"]);
							
								$count = $count + 1;
							} 
							
							
						}
						 // die();
					//}
					
					// $BStyle = array(
						// 'font'=>array(
							// 'bold'=>true
						// ),
					  // 'borders' => array(
						// 'outline' => array(
						  // 'style' => \PHPExcel_Style_Border::BORDER_THIN
						// ),
						// 'inside' => array(
						  // 'style' => \PHPExcel_Style_Border::BORDER_THIN
						// )
					  // )
					// );
					
					
				    // $feuille->getStyle('B1:C1')->applyFromArray($BStyle);
				    // $feuille->getStyle('A2:AO2')->applyFromArray($BStyle);
				    // $feuille->getStyle('A3:AO3')->applyFromArray($BStyle);
				    // $feuille->getStyle('A4:AO'.$count)->applyFromArray($BStyle);

					// envoi du fichier au navigateur
					
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 

					header('Content-Disposition: attachment;filename="arretCaisse.xls"'); 

					header('Cache-Control: max-age=0'); 

					$writer =  \PHPExcel_IOFactory::createWriter($classeur, 'Excel5'); 

					$writer->save('php://output');
											
				$this->render('arretsDouaniers.interfaceCpta',compact('writer'));
		
  
    }
	
	public function ArretsCaissesPrint0(){
				
			$auth = App::getDBAuth();
			$session = Session::getInstance();
			$search = $_SESSION['search0'];
			
			$stmtArretsDouanier = ArretDouanier::oldCaisseDay($search);
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
						$stmtArretsCaisses = ArretsCaisses::oldCaisseAgence(date('Y-m-d',date_timestamp_get($result['dateEntree'])),$user1['agence']);
						$arretsCaisse = array();
						while ($result1 = sqlsrv_fetch_array($stmtArretsCaisses, SQLSRV_FETCH_ASSOC)) {
							
							$arretsCaisse =  array("idArretsCaisses"=>$result1['idArretsCaisses'],
							"arretCashCaisse"=>$result1['arretCashCaisse'],
							"arretOrangeCaisse"=>$result1['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result1['arretMtnCaisse'],
							"arretTpeCaisse"=>$result1['arretTpeCaisse'],
							"arretCarteCaisse"=>$result1['arretCarteCaisse'],
							"arretChequeCaisse"=>$result1['arretChequeCaisse'],
							"arretComplementCaisse"=>$result1['arretComplementCaisse'],
							"arretVirementCaisse"=>$result1['arretVirementCaisse'],
							"idUser"=>$result1['idUser'],
							"totalCaisse"=>$result1['totalCaisse'],
							"dateEntree"=>$result1['dateEntree'],
							"agence"=>$result1['idAgence'],
						);
							
						}
							
							$pers =  array("idArretsDouanier"=>$result['idArretsDouanier'],
									"dateEntree"=>$result['dateEntree'],
									"arretInfo"=>$result['arretInfo'],
									"arretDouanier"=>$result['arretDouanier'],
									"diffCaisse"=>$result['diffCaisse'],
									"diffDouanier"=>$result['diffDouanier'],
									"idUser"=>$result['idUser'],
									"observationChef"=>$result['observationChef'],
									"arretCashCaisse"=>$arretsCaisse['arretCashCaisse'],
									"arretCarteCaisse"=>$arretsCaisse['arretCarteCaisse'],
									"arretVirementCaisse"=>$arretsCaisse['arretVirementCaisse'],
									"arretOrangeCaisse"=>$arretsCaisse['arretOrangeCaisse'],
									"arretMtnCaisse"=>$arretsCaisse['arretMtnCaisse'],
									"arretTpeCaisse"=>$arretsCaisse['arretTpeCaisse'],
									"arretChequeCaisse"=>$arretsCaisse['arretChequeCaisse'],
									"arretComplementCaisse"=>$arretsCaisse['arretComplementCaisse'],
									"totalCaisse"=>$arretsCaisse['totalCaisse'],
									"agence"=>$agence['designation'],
								);					
							
							$arretsDouaniers[] = $pers;
						
			}
				
				
				
					$classeur = new \PHPExcel;

					$classeur->getProperties()->setCreator("SORERPCO.SA");

					$classeur->setActiveSheetIndex(0);

					$feuille=$classeur->getActiveSheet();

					
					$feuille->getColumnDimension('A')->setAutoSize(true);
					$feuille->getColumnDimension('B')->setAutoSize(true);
					$feuille->getColumnDimension('C')->setAutoSize(true);
					$feuille->getColumnDimension('D')->setAutoSize(true);
					$feuille->getColumnDimension('E')->setAutoSize(true);
					$feuille->getColumnDimension('F')->setAutoSize(true);
					$feuille->getColumnDimension('G')->setAutoSize(true);
					$feuille->getColumnDimension('H')->setAutoSize(true);
					$feuille->getColumnDimension('I')->setAutoSize(true);
					$feuille->getColumnDimension('J')->setAutoSize(true);
					$feuille->getColumnDimension('K')->setAutoSize(true);
					$feuille->getColumnDimension('L')->setAutoSize(true);
					$feuille->getColumnDimension('M')->setAutoSize(true);
					$feuille->getColumnDimension('N')->setAutoSize(true);

					// ajout des données dans la feuille de calcul $sheet->mergeCells('A1:D1');
					
					// pour le titre de la feuille
					$feuille->setTitle('Arret Caisse');
					
					// pour la date en cours
					//$feuille->mergeCells('A1:G1', 'Arrets Caisse');
					//$feuille->mergeCells('G2:K2', 'DOUALA');
					$feuille->SetCellValue('G1','Arrets Caisse');
					$feuille->SetCellValue('M1', $search);
					$feuille->SetCellValue('A3', 'Agences');
					$feuille->SetCellValue('B3', 'Complement en caisse');
					$feuille->SetCellValue('C3', 'Arret Cash');
					$feuille->SetCellValue('D3', 'Arret Mtn');
					$feuille->SetCellValue('E3', 'Arret Orange');
					$feuille->SetCellValue('F3', 'Arret TPE');
					$feuille->SetCellValue('G3', 'Arret Virement');
					$feuille->SetCellValue('H3', 'Arret Cheque');
					$feuille->SetCellValue('I3', 'Arret Carte');
					$feuille->SetCellValue('J3', 'Arret Info');
					$feuille->SetCellValue('K3', 'Arret Douanier');
					$feuille->SetCellValue('L3', 'Total Caisse');
					$feuille->SetCellValue('M3', 'Diff Douanier');
					$feuille->SetCellValue('N3', 'Diff Caisse');
					
					if(!empty($arretsDouaniers)){
							$count = 4;
							/*var_dump($prixArts1);
							die();*/
							
							$countarretsAgences = count($arretsDouaniers);
							
							// var_dump($countResultEtats);
							
						for ($y = 0 ; $y < $countarretsAgences; $y++) {
					
								$cellule =  strval('A'.$count);
								$cellule1 =  strval('B'.$count);
								$cellule2 =  strval('C'.$count);
								$cellule3 =  strval('D'.$count);
								$cellule4 =  strval('E'.$count);
								$cellule5 =  strval('F'.$count);
								$cellule6 =  strval('G'.$count);
								$cellule7 =  strval('H'.$count);
								$cellule8 =  strval('I'.$count);
								$cellule9 =  strval('J'.$count);
								$cellule10 =  strval('K'.$count);
								$cellule11 =  strval('L'.$count);
								$cellule12 =  strval('M'.$count);
								$cellule13 =  strval('N'.$count);
								
								$feuille->SetCellValue($cellule, $arretsDouaniers[$y]["agence"]);
								$feuille->SetCellValue($cellule1, $arretsDouaniers[$y]["arretComplementCaisse"]);
								$feuille->SetCellValue($cellule2, $arretsDouaniers[$y]["arretCashCaisse"]);
								$feuille->SetCellValue($cellule3, $arretsDouaniers[$y]["arretMtnCaisse"]);
								$feuille->SetCellValue($cellule4, $arretsDouaniers[$y]["arretOrangeCaisse"]);
								$feuille->SetCellValue($cellule5, $arretsDouaniers[$y]["arretTpeCaisse"]);
								$feuille->SetCellValue($cellule6, $arretsDouaniers[$y]["arretVirementCaisse"]);
								$feuille->SetCellValue($cellule7, $arretsDouaniers[$y]["arretChequeCaisse"]);
								$feuille->SetCellValue($cellule8, $arretsDouaniers[$y]["arretCarteCaisse"]);
								$feuille->SetCellValue($cellule9, $arretsDouaniers[$y]["arretInfo"]);
								$feuille->SetCellValue($cellule10, $arretsDouaniers[$y]["arretDouanier"]);
								$feuille->SetCellValue($cellule11, $arretsDouaniers[$y]["totalCaisse"]);
								$feuille->SetCellValue($cellule12, $arretsDouaniers[$y]["diffDouanier"]);
								$feuille->SetCellValue($cellule13, $arretsDouaniers[$y]["diffCaisse"]);
							
								$count = $count + 1;
							} 
							
							
						}
						
					$BStyle = array(
						'font'=>array(
							'bold'=>true
						),
					  'borders' => array(
						'outline' => array(
						  'style' => \PHPExcel_Style_Border::BORDER_THIN
						),
						'inside' => array(
						  'style' => \PHPExcel_Style_Border::BORDER_THIN
						)
					  )
					);
					
					
				    $feuille->getStyle('G1')->applyFromArray($BStyle);
				    $feuille->getStyle('M1')->applyFromArray($BStyle);
				    $feuille->getStyle('A3:N'.$count)->applyFromArray($BStyle);
						
						
					// envoi du fichier au navigateur
					
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 

					header('Content-Disposition: attachment;filename="arretCaisse.xls"'); 

					header('Cache-Control: max-age=0'); 

					$writer =  \PHPExcel_IOFactory::createWriter($classeur, 'Excel5'); 

					$writer->save('php://output');
											
				$this->render('arretsDouaniers.interfaceGestion',compact('writer'));
		
  
    }
	
}

?>