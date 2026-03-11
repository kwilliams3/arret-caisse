<?php

namespace Core\Controller;

use Core\Database\Agence;
use Core\Database\ArretControle;
use Core\Database\ArretDouanier;
use Core\Database\ActionsArrets;
use Core\Database\User;
use Core\Model\App;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;

class ArretControleController extends AppController{

	public  function ajoutArretsControle(){
		
		header('content-type: application/json');
        $return = [];
		
        if(isset($_POST['idArretsCaisse']) && isset($_POST['controlePhys']) && isset($_POST['Action1']) && isset($_POST['delai1'])){
				
            $id = $_POST['idArretsCaisse'];
            $controlePhys = $_POST['controlePhys'];
			$commentaireControle = $_POST['commentaireControle'];
            $Action1 = $_POST['Action1'];
            $delai1 = $_POST['delai1'];
			
			
            if(!empty($_POST['idArretsCaisse']) && !empty($_POST['controlePhys']) && !empty($_POST['commentaireControle']) && !empty($_POST['Action1']) 
				 && !empty($_POST['delai1'])){
					 
					 // verification de l'ancien enregistrement
					 
					 $date = date('Y-m-d');
					 $today = date("Y-m-d h:m:s");
					 
					 $stmtArretConrol = ArretControle::SearchByArret($id);
						$arretControl = array();
						while ($result = sqlsrv_fetch_array($stmtArretConrol, SQLSRV_FETCH_ASSOC)) {
							
							$arretControl =  array("idArretControle"=>$result['idArretControle'],
								"controlePhysique"=>$result['controlePhysique'],
								"commentaires"=>$result['commentaires'],
								"idArretDouanier"=>$result['idArretDouanier'],
								"dateEntree"=>$result['dateEntree'],
								);	
						}
						
					if(!empty($arretControl)){
						
						$stmtActions = ActionsArrets::SearchByArret($arretControl['idArretControle']);
							$act = array();
								while ($result4= sqlsrv_fetch_array($stmtActions, SQLSRV_FETCH_ASSOC)) {
									 $addActions = ActionsArrets::delete($result4['idActionsArrets']);
									}
								
							$addArretControle = ArretControle::save(date('d-m-Y',date_timestamp_get($arretControl['dateEntree'])),$controlePhys,$commentaireControle,$id,$arretControl['idArretControle']);	
							
							$addActions = ActionsArrets::save(date('d-m-Y',date_timestamp_get($arretControl['dateEntree'])),$Action1,$delai1,'Cpta',$arretControl['idArretControle']);
						
							if(isset($_POST['Action2']) && !empty($_POST['delai2'])){
							
								$addActions = ActionsArrets::save(date('d-m-Y',date_timestamp_get($arretControl['dateEntree'])),$_POST['Action2'],$_POST['delai2'],'Cpta',$arretControl['idArretControle']);

							}
							
							if(isset($_POST['Action3']) && !empty($_POST['delai3'])){
							
								$addActions = ActionsArrets::save(date('d-m-Y',date_timestamp_get($arretControl['dateEntree'])),$_POST['Action3'],$_POST['delai3'],'Cpta',$arretControl['idArretControle']);

							}
						
							if ($addArretControle === true) {
								$message = "L'enregistrement a bien ete enregistre";
								$return = array("statuts" => 0, "mes" => $message);
							} else {
								$message = "Erreur lors de l'enregistrement";
								$return = array("statuts" => 1,"mes" => $message);
							}
						
					}else{
															
						$addArretControle = ArretControle::save($today,$controlePhys,$commentaireControle,$id);
						
						// Recuperation du dernier enregistrement
						
						$stmtLastArretConrol = ArretControle::lastInsert();
						$last = array();
						while ($result = sqlsrv_fetch_array($stmtLastArretConrol, SQLSRV_FETCH_ASSOC)) {
							
							$last =  array("last"=>$result['last']);	
						}
						
						$addActions = ActionsArrets::save($today,$Action1,$delai1,'Cpta',$last['last']);
						
							if(isset($_POST['Action2']) && !empty($_POST['delai2'])){
							
								$addActions = ActionsArrets::save($today,$_POST['Action2'],$_POST['delai2'],'Cpta',$last['last']);

							}
							
							if(isset($_POST['Action3']) && !empty($_POST['delai3'])){
							
								$addActions = ActionsArrets::save($today,$_POST['Action3'],$_POST['delai3'],'Cpta',$last['last']);

							}
						
						if ($addArretControle === true) {
							$message = "L'enregistrement a bien ete enregistre";
							$return = array("statuts" => 0, "mes" => $message);
						} else {
							$message = "Erreur lors de l'enregistrement";
							$return = array("statuts" => 1,"mes" => $message);
						}
					
					}
				
            }else{
                $message = 'Renseigner tous les champs obligatoires';
                $return = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = 'Une erreur est survenue, réessayer !!!!!!!!!!!!!!!!!!!!!!';
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
		
	}
	
	public function detailsArretsControl(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {
			
            $id = $_POST['id'];
			
			$auth = App::getDBAuth();
			$session = Session::getInstance();

			$user = $_SESSION['user'];
			
			
			$stmtArretDouanier = ArretDouanier::searchById($id);
			$arretControle = array();
			$actions = array();
			
				while ($result = sqlsrv_fetch_array($stmtArretDouanier, SQLSRV_FETCH_ASSOC)){
						
					$stmtArretControle = ArretControle::SearchByArret($id);
					
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
					
				}
				
				$listeActions  = ' ';
					
					$t = 0;
					
					for($i = 0; $i < sizeof($actions);$i++){ 
						
						$listeActions  .= ' 
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Actions </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . $actions[$i]['designation'].  '</span>
							</div>
						</div> 
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Delai </div>

							<div class="profile-info-value">
								<span class="editable" id="city">' . date('d-m-Y',date_timestamp_get($actions[$i]['delai'])).  '</span>
							</div>
						</div>
						
						';
						
					}
			
            
				
            if(!empty($arretControle)){
				
                $content = '
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Date  </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . date('d-m-Y',date_timestamp_get($arretControle['dateEntree'])) .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Controle Physique </div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretControle['controlePhysique'] .  '</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name">Commentaires</div>

							<div class="profile-info-value">
								<span class="editable" id="username">' . $arretControle['commentaires'] .  '</span>
							</div>
						</div>
						'.$listeActions.'
					</div>
                
                ';
                $result = array("statuts" => 0, "content" => $content);
					
				}else{
					
					$content = '
					
					<div class="profile-user-info profile-user-info-striped">
						
						Aucun element saisi pour le moment
						
					</div>
                
                ';
                $result = array("statuts" => 0, "content" => $content);
					
				}
				
				
            }else {
                $message = "Une erreur est survenue, reessayez plus tard 111 !!";
                $result = array("statuts" => 1, "mes" => $message);
            }


        echo json_encode($result);

    }

}

?>