<?php

namespace Core\Controller;

use Core\Model\App;
use Core\Database\ArretsCaissesLD;
use Core\Database\ArretsCaissesLDLog;
use Core\Database\Agence;
use Core\Database\User;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;
use Core\Model\Sms;

class ArretsCaissesLDController extends AppController{

    public function index(){
		
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
			$stmtArretsCaissesLD = ArretsCaissesLD::all();
			$arretsCaissesld = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtArretsCaissesLD, SQLSRV_FETCH_ASSOC)) {
				
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
					
					//var_dump('Test de fonctionnement 01');
					
				$privilege = explode(" ", $user['privilege']);
				
				//var_dump('Test de fonctionnement 02');
				
				$privileges = 'CaissiereLD';
				if(in_array($privilege[0] ,explode(',',$privileges))) {
					
					if($user['agence'] == $result['idAgence']){
						
						$pers =  array("idArretsCaissesLD"=>$result['idArretsCaissesLD'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"versements"=>$result['versements'],
							"observationVersements"=>$result['observationVersements'],
							"bordereauVersement"=>$result['bordereauVersement'],
							"MontantVerse"=>$result['MontantVerse'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
						);
						
						$arretsCaissesld[] = $pers;
					}
					
					
				 }else{
					 
					$pers =  array("idArretsCaissesLD"=>$result['idArretsCaissesLD'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"versements"=>$result['versements'],
							"observationVersements"=>$result['observationVersements'],
							"bordereauVersement"=>$result['bordereauVersement'],
							"MontantVerse"=>$result['MontantVerse'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							"agence"=>$agence['designation'],
							
						);
					
					$arretsCaissesld[] = $pers;
					 
				 }
				
			}

        $this->render('ArretsCaissesLD.index',compact('arretsCaissesld'));
            

    }
	
	
	public function ajoutArretsCaisseLD(){
		
		header('content-type: application/json');
        $result = [];
		
        if(isset($_POST['arretCashLD']) && (!empty($_POST['arretCashLD']) || $_POST['arretCashLD'] == 0) 
			&& isset($_POST['arretOrangeMobileLD']) && (!empty($_POST['arretOrangeMobileLD']) || $_POST['arretOrangeMobileLD'] == 0)
			&& (!empty($_POST['arretCarteLD']) || $_POST['arretCarteLD'] == 0) && isset($_POST['arretCarteLD']) 
			&& (!empty($_POST['arretChequeLD']) || $_POST['arretChequeLD'] == 0) && isset($_POST['arretChequeLD'])
			&& (!empty($_POST['arretMtnMobileLD']) || $_POST['arretMtnMobileLD'] == 0) && isset($_POST['arretMtnMobileLD'])
			&& (!empty($_POST['arretTpeMobileLD']) || $_POST['arretTpeMobileLD'] == 0) && isset($_POST['arretTpeMobileLD'])
			&& (!empty($_POST['arretVirementLD']) || $_POST['arretVirementLD'] == 0) && isset($_POST['arretVirementLD'])
			&& (!empty($_POST['complementCashLD']) || $_POST['complementCashLD'] == 0) && isset($_POST['complementCashLD']) 
			&& (!empty($_POST['arretInfoLD']) || $_POST['arretInfoLD'] == 0) && isset($_POST['arretInfoLD'])
			&& (!empty($_POST['versementLD']) || $_POST['versementLD'] == 0) && isset($_POST['versementLD']) 
			&& (!empty($_POST['observationVersLD']) || $_POST['observationVersLD'] == 0) && isset($_POST['observationVersLD'])) {
			
				$user = array();
				$session = Session::getInstance();
				$user = $_SESSION['user'];
				
				$stmtAgenceUser = Agence::searchById($user['agence']);
				$agenceUser = array();
				while ($result3= sqlsrv_fetch_array($stmtAgenceUser, SQLSRV_FETCH_ASSOC)) {
					
					$designation = explode(" ",$result3['designation']);
					
					$agenceUser = array(
						"id" => $result3['idAgence'],
						"designation" => $designation[0],
					);
				}
				
				$date = date('Y-m-d');
				$today = date("Y-m-d h:m:s");

				$arretCash = $_POST['arretCashLD'];
				$arretTpeMobile = $_POST['arretTpeMobileLD'];
				$arretMtnMobile = $_POST['arretMtnMobileLD'];
				$arretOrangeMobile = $_POST['arretOrangeMobileLD'];
				$arretCarte = $_POST['arretCarteLD'];
				$arretCheque = $_POST['arretChequeLD'];
				$arretComplementCaisse = $_POST['complementCashLD'];
				$arretVirement = $_POST['arretVirementLD'];
				$arretInfo = $_POST['arretInfoLD'];
				$versementLD = $_POST['versementLD'];
				$observationVersLD = $_POST['observationVersLD'];
				
				$totalCaisse = $arretCash + $arretOrangeMobile + $arretMtnMobile + $arretTpeMobile + $arretCarte + $arretCheque + $arretComplementCaisse + $arretVirement;
			
				$stmt = ArretsCaissesLD::oldCaisseAgence($today,$user['agence']);
				$arretCaisseOld = array();
				while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
					
					$arretCaisseOld =  array("idArretsCaissesLD"=>$result['idArretsCaissesLD'],
							"arretCashCaisse"=>$result['arretCashCaisse'],
							"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
							"arretMtnCaisse"=>$result['arretMtnCaisse'],
							"arretTpeCaisse"=>$result['arretTpeCaisse'],
							"arretCarteCaisse"=>$result['arretCarteCaisse'],
							"arretChequeCaisse"=>$result['arretChequeCaisse'],
							"arretVirementCaisse"=>$result['arretVirementCaisse'],
							"arretComplementCaisse"=>$result['arretComplementCaisse'],
							"arretInfo"=>$result['arretInfo'],
							"versements"=>$result['versements'],
							"observationVersements"=>$result['observationVersements'],
							"bordereauVersement"=>$result['bordereauVersement'],
							"MontantVerse"=>$result['MontantVerse'],
							"idUser"=>$result['idUser'],
							"totalCaisse"=>$result['totalCaisse'],
							"dateEntree"=>$result['dateEntree'],
							
							);
				}
				
            if(!empty($user)){
				
				if(empty($arretCaisseOld)){
					
					if(strcmp($versementLD,'Oui') == 0){
								
							$montantVerse = $_POST['montantVerseLD'];
							$bordereauVers = $_FILES['bordereauVersLD'];
							
							move_uploaded_file($_FILES['bordereauVersLD']['tmp_name'], 'DocumentsBordereauLD/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name']));
								
							$addUpdate = ArretsCaissesLD::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,$montantVerse,$agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name'].'!',$totalCaisse,$user['agence'],$user['idUser']);
														
							$addUpdateLog = ArretsCaissesLDLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,$montantVerse,$agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name'].'!',$totalCaisse,$user['agence'],$user['idUser']);
							
							if ($addUpdate == TRUE) {
								$message = "L'enregistrement a ete bien effectue";
								$result = array("statuts" => 0, "mes" => $message);
							} else {
								$message = "Erreur lors de l'enregistrement";
								$result = array("statuts" => 1, "mes" => $message);
							}
							
						}else{
							
							$addUpdate = ArretsCaissesLD::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,0,'Aucun!',$totalCaisse,$user['agence'],$user['idUser']);
														
							$addUpdateLog = ArretsCaissesLDLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,0,'Aucun!',$totalCaisse,$user['agence'],$user['idUser']);
							
							if ($addUpdate == TRUE) {
								$message = "L'enregistrement a ete bien effectue";
								$result = array("statuts" => 0, "mes" => $message);
							} else {
								$message = "Erreur lors de l'enregistrement";
								$result = array("statuts" => 1, "mes" => $message);
							}
							
						}
					
					
				}else{
					

					if(strcmp($versementLD,'Oui') == 0){
								
					$montantVerse = $_POST['montantVerseLD'];
					$bordereauVers = $_FILES['bordereauVersLD'];
					
					$bordereau = explode("!",$arretCaisseOld['bordereauVersement']);
					
						if(strcmp($bordereau[0],'Aucun') == 0){
							
						 move_uploaded_file($_FILES['bordereauVersLD']['tmp_name'], 'DocumentsBordereauLD/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name']));
							
						// move_uploaded_file($_FILES['bordereauVers']['tmp_name'], 'DocumentsBordLog/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVers']['name']));
							
							$addUpdate = ArretsCaissesLD::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,$montantVerse,$agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name'].'!',$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaissesLD']);
														
							$addUpdateLog = ArretsCaissesLDLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,$montantVerse,$agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name'].'!',$totalCaisse,$user['agence'],$user['idUser']);
							
							if ($addUpdate == TRUE) {
								$message = "L'enregistrement a ete bien effectue";
								$result = array("statuts" => 0, "mes" => $message);
							} else {
								$message = "Erreur lors de l'enregistrement";
								$result = array("statuts" => 1, "mes" => $message);
								}
							
							
						}else{
							
							unlink('DocumentsBordereauLD/'.$bordereau[0]);
							
							move_uploaded_file($_FILES['bordereauVersLD']['tmp_name'], 'DocumentsBordereauLD/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name']));
							
							move_uploaded_file($_FILES['bordereauVersLD']['tmp_name'], 'DocumentsBordLogLD/' . basename($agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name']));
							
							$addUpdate = ArretsCaissesLD::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,$montantVerse,$agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name'].'!',$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaissesLD']);
														
							$addUpdateLog = ArretsCaissesLDLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,$montantVerse,$agenceUser['designation'].'_'.$_FILES['bordereauVersLD']['name'].'!',$totalCaisse,$user['agence'],$user['idUser']);
							
							if ($addUpdate == TRUE) {
								$message = "L'enregistrement a ete bien effectue";
								$result = array("statuts" => 0, "mes" => $message);
							} else {
								$message = "Erreur lors de l'enregistrement";
								$result = array("statuts" => 1, "mes" => $message);
								}
							
							
						}
						
					}else{
						
						$montantVerse = $_POST['montantVerseLD'];
						$bordereauVers = $_FILES['bordereauVersLD'];
						
						$bordereau = explode("!",$arretCaisseOld['bordereauVersement']);
						
							if(strcmp($bordereau[0],'Aucun') == 0){
								
								$addUpdate = ArretsCaissesLD::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,0,'Aucun!',$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaissesLD']);
														
								$addUpdateLog = ArretsCaissesLDLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,0,'Aucun!',$totalCaisse,$user['agence'],$user['idUser']);
							
								if ($addUpdate == TRUE) {
									$message = "L'enregistrement a ete bien effectue";
									$result = array("statuts" => 0, "mes" => $message);
								} else {
									$message = "Erreur lors de l'enregistrement";
									$result = array("statuts" => 1, "mes" => $message);
									}
								
							}else{
								
								unlink('DocumentsBordereauLD/'.$bordereau[0]);
								
								$addUpdate = ArretsCaissesLD::save(date('Y-m-d',date_timestamp_get($arretCaisseOld['dateEntree'])),$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,0,'Aucun!',$totalCaisse,$user['agence'],$user['idUser'],$arretCaisseOld['idArretsCaissesLD']);
														
								$addUpdateLog = ArretsCaissesLDLog::save($today,$arretCash,$arretOrangeMobile,$arretMtnMobile,$arretTpeMobile,$arretCarte,$arretCheque,$arretComplementCaisse,$arretVirement,$arretInfo,$versementLD,$observationVersLD,0,'Aucun!',$totalCaisse,$user['agence'],$user['idUser']);
							
								if ($addUpdate == TRUE) {
									$message = "L'enregistrement a ete bien effectue";
									$result = array("statuts" => 0, "mes" => $message);
								} else {
									$message = "Erreur lors de l'enregistrement";
									$result = array("statuts" => 1, "mes" => $message);
									}
								
							}
						
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
	
	
	public function detailsArretsLD(){
		
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
            $stmtArretLD = ArretsCaissesLD::searchById($id);
			$arretLD = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretLD, SQLSRV_FETCH_ASSOC)){
					
					$stmtAgence = Agence::searchById($result['idAgence']);
					$agence = array();
					while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result2['idAgence'],
							"designation" => $result2['designation'],
						);
					}
					
							$arretLD =  array("idArretsCaissesLD"=>$result['idArretsCaissesLD'],
									"dateEntree"=>$result['dateEntree'],
									"arretCashCaisse"=>$result['arretCashCaisse'],
									"arretOrangeCaisse"=>$result['arretOrangeCaisse'],
									"arretMtnCaisse"=>$result['arretMtnCaisse'],
									"arretTpeCaisse"=>$result['arretTpeCaisse'],
									"arretCarteCaisse"=>$result['arretCarteCaisse'],
									"arretChequeCaisse"=>$result['arretChequeCaisse'],
									"arretVirementCaisse"=>$result['arretVirementCaisse'],
									"arretComplementCaisse"=>$result['arretComplementCaisse'],
									"arretInfo"=>$result['arretInfo'],
									"totalCaisse"=>$result['totalCaisse'],
									"versements"=>$result['versements'],
									"MontantVerse"=>$result['MontantVerse'],
									"observationVersements"=>$result['observationVersements'],
									"agence"=>$agence['designation'],
								);
							
							// $arretsDouaniers[] = $pers;
						
					}
					
				
            if(!empty($arretLD)){
			
				$content = '
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Date  </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretLD['dateEntree'])) .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Info </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretLD['arretInfo'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Complement Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretComplementCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Carte Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretCarteCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Arret Cash </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretCashCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Orange Mobile Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretOrangeCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Mtn Mobile Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretMtnCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret TPE Caisse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretTpeCaisse'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Cheque Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretChequeCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Arret Virement Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['arretVirementCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Total Caisse</div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['totalCaisse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Versement/Ramassage </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['versements'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Observation Versement/Ramassage </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['observationVersements'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Montant Verse </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['MontantVerse'].  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Agence </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $arretLD['agence'].  '</span>
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
	
	
}

?>