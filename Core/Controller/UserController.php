<?php

namespace Core\Controller;

use Core\Database\Agence;
use Core\Database\User;
use Core\Model\App;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;

class UserController extends AppController{

    public function index(){

			$stmtUsers = User::all();
			$users = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtUsers, SQLSRV_FETCH_ASSOC)) {
				
					$stmtAgence = Agence::searchById($result['idAgence']);
					$agence = array();
					while ($result1 = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
						$agence = array(
							"id" => $result1['idAgence'],
							"designation" => $result1['designation'],
						);
					}
				
					$pers =  array("idUser"=>$result['idUser'],
					"login"=>$result['login'],
					"NomUser"=>$result['NomUser'],
					"password"=>$result['password'],
					"agence"=>$agence['designation'],
					"privilege"=>$result['privilege'],
				
					);
					
				$users[] = $pers;
			}
			
			$stmtAgence = Agence::all();
			$agences = array();
			$pers = array();
			while ($result = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
				$pers = array(
					"id" => $result['idAgence'],
					"designation" => $result['designation'],
				);
				$agences[] = $pers;
			}
			
        $this->render('users.index',compact('users','agences'));
    }
	
	
	public  function addUserCaisse(){
		header('content-type: application/json');
        $return = [];
		
        if(isset($_POST['nomUser']) && isset($_POST['login']) && isset($_POST['password']) && isset($_POST['confirmPassword']) 
			&& isset($_POST['privilege']) && isset($_POST['agence'])){
				
            $nomUser = $_POST['nomUser'];
            $login = $_POST['login'];
			$agence = $_POST['agence'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirmPassword'];
            $privilege = $_POST['privilege'];
			
            if(!empty($_POST['privilege']) && !empty($_POST['agence']) && !empty($_POST['login']) && !empty($_POST['password']) 
				 && !empty($_POST['confirmPassword']) && !empty($_POST['nomUser'])){
				
				if(strcmp($password,$confirmPassword) == 0){
					
					$stmtUser = User::searchBylogin($_POST['login']);
						$user = array();
						while ($result = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC)) {
							$user =  array("idUser"=>$result['idUser'],
								"login"=>$result['login'],
								"password"=>$result['password'],
								"agence"=>$result['agence'],
								);	
						}
						
					if(empty($user)){
						
						$addUser = User::save($nomUser,$login,$password,$agence,$privilege);

						if ($addUser === true) {
							$message = "L'utilisateur a bien ete enregistre";
							$return = array("statuts" => 0, "mes" => $message);
						} else {
							$message = "Erreur lors de l'enregistrement";
							$return = array("statuts" => 1,"mes" => $message);
						}
						
					}else{
						$message = 'Ce login est deja utilisé ';
						$return = array("statuts" => 1, "mes" => $message);
					}
					
				}else{
					$message = 'Les mot de passe ne sont pas identiques';
					$return = array("statuts" => 1, "mes" => $message);
				}
				
            }else{
                $message = 'Renseigner tous les champs obligatoires PHP';
                $return = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = 'Une erreur est survenue, réessayer';
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
		
	}

	public function updateUserCaisse(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {

            $id = $_POST['id'];
			
            $stmtUser = User::searchById($id);
				$user = array();
				while ($result = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC)) {
					$user =  array("idUser"=>$result['idUser'],
						"login"=>$result['login'],
						"nomUser"=>$result['NomUser'],
						"privilege"=>$result['privilege'],
						"password"=>$result['password'],
						"agence"=>$result['idAgence'],
						);	
				}

            $add = false;

            if(!empty($user)){
				
				$listePrivilege  = ' ';
					$privilege = explode(" ", $user['privilege']);
                    if (strcmp($privilege[0],'Agence') == 0) {
                        $listePrivilege .= " <option value='Agence' selected> Agence </option>";
                    } else {
                        $listePrivilege .= " <option value='Agence'> Agence </option>";
                    }
					
					if (strcmp($privilege[0],'Caissiere') == 0) {
                        $listePrivilege .= " <option value='Caissiere' selected> Caissiere </option>";
                    } else {
                        $listePrivilege .= " <option value='Caissiere'> Caissiere </option>";
                    }
					
					if (strcmp($privilege[0],'CaissiereLD') == 0) {
                        $listePrivilege .= " <option value='CaissiereLD' selected> Caissiere LD </option>";
                    } else {
                        $listePrivilege .= " <option value='CaissiereLD'> Caissiere LD </option>";
                    }
					
                    if (strcmp($privilege[0],'Administration') == 0) {
                        $listePrivilege .= " <option value='Administration' selected> Administration </option>";
                    } else {
                        $listePrivilege .= " <option value='Administration' > Administration </option>";
                    }
					
					if (strcmp($privilege[0],'ControleInterne') == 0) {
                        $listePrivilege .= " <option value='ControleInterne' selected> Controle Interne </option>";
                    } else {
                        $listePrivilege .= " <option value='ControleInterne' > Controle Interne </option>";
                    }
					
					if (strcmp($privilege[0],'Comptabilite') == 0) {
                        $listePrivilege .= " <option value='Comptabilite' selected> Comptabilite </option>";
                    } else {
                        $listePrivilege .= " <option value='Comptabilite' > Comptabilite  </option>";
                    }
					
					if (strcmp($privilege[0],'Controleur') == 0) {
                        $listePrivilege .= " <option value='Controleur' selected> Controleur </option>";
                    } else {
                        $listePrivilege .= " <option value='Controleur' > Controleur  </option>";
                    }
					
					if (strcmp($privilege[0],'OPAgence') == 0) {
                        $listePrivilege .= " <option value='OPAgence' selected> OPAgence </option>";
                    } else {
                        $listePrivilege .= " <option value='OPAgence' > OPAgence  </option>";
                    }
					
					$listeAgence = ' ';
					$stmt = Agence::all();
					$agences = array();
					$pers = array();
					while ($result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
						$pers = array("id" => $result['idAgence'],
							"designation" => $result['designation']
						);
						$agences[] = $pers;
					}

					if (!empty($agences)) {
						foreach ($agences as $agence) {
							if (strcmp($user['agence'], $agence['id']) == 0) {
								$listeAgence .= ' <option value="' . $agence['id'] . '" selected> ' . $agence['designation'] . ' </option>';
							} else {
								$listeAgence .= ' <option value="' . $agence['id'] . '"> ' . $agence['designation'] . ' </option>';
							}
						}
					}
					
                $content = '

                <form action="'.App::url('ajax.user.editUserCaisse').'" method="POST" id="form-EditUser">
                    <input type="hidden" class="id" id="id" name="id" value="'.$user['idUser'].'">
                    <label class="block clearfix">
							<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="nomEditCaisse"  name="user" value="'.$user['nomUser'].'" placeholder="Login" />
							<i class="ace-icon fa fa-user"></i>
						</span>
					</label>
					<label class="block clearfix">
							<span class="block input-icon input-icon-right">
							<input type="text" class="form-control" id="loginEditCaisse"  name="user" value="'.$user['login'].'" placeholder="Login" />
							<i class="ace-icon fa fa-user"></i>
						</span>
					</label>

					<label class="block clearfix">
							<span class="block input-icon input-icon-right">
							<select type="text" class="form-control" id="privilegeEditCaisse" name="privilege">
								<option value="">Choisir le privilege</option>
                                '.$listePrivilege.'
							</select>
							<i class="ace-icon fa fa-user"></i>
						</span>
					</label>
					
					<label class="block clearfix">
							<span class="block input-icon input-icon-right">
							<select type="text" class="form-control" id="agenceEditCaisse" name="agence">
								<option value="">Choisir l\'agence</option>
                                '.$listeAgence.'
							</select>
							<i class="ace-icon fa fa-user"></i>
						</span>
					</label>
					
					<div class="space-24"></div>

					<div class="clearfix connectRegisterEdit">
							<button type="submit" class="width-50 pull-right btn btn-sm btn-success">
							<i class="ace-icon fa fa-arrow-right icon-on-right"></i>
							Mise a jour
						</button>
					</div>
					
					<div class="clearfix hidden loaderRegisterEdit">
						<center>
							<h2 class="header smaller lighter grey">
								<i class="ace-icon fa fa-spinner fa-spin green bigger-125"></i>
							</h2>
						</center>
					</div>
                </form>
				<script type="text/javascript">

                        $( "#form-EditUser" ).on("submit", function(e) {
                            e.preventDefault();

                            var id = $("#id").val();
                            var nomUser = $("#nomEditCaisse").val();
                            var privilege = $("#privilegeEditCaisse").val();
							var login = $("#loginEditCaisse").val();
							var agence = $("#agenceEditCaisse").val();
							var url = $(this).attr("action");

                                if (url != "" && id != "" && privilege !="" && login !="" && agence !="" && nomUser !="" ) {
                                    $.ajax({
                                        type: "post",
                                        url: url,
                                        data: "id="+id+"&privilege="+privilege+"&login="+login+"&agence="+agence+"&nomUser="+nomUser,
                                        datatype: "json",
                                        success: function (json) {
											if (json.statuts == 0){
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
                                    alert("Veuillez rensiegner tous les champs obligatoires javascript");
                                }

                        });
						
                    </script>
                ';
                $result = array("statuts" => 0, "content" => $content);
            }else {
                $message = "Une erreur est survenue, r�essayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, r�essayez plus tard !!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }

    public function editUserCaisse(){
        
		header('content-type: application/json');
        $result = [];
			
			if(isset($_POST['id']) && isset($_POST['privilege']) && isset($_POST['login']) && isset($_POST['agence']) && isset($_POST['nomUser']) ){
				
            $id = $_POST['id'];
            $privilege = $_POST['privilege'];
            $login = $_POST['login'];
			$agence = $_POST['agence'];
			$nomUser = $_POST['nomUser'];
			
            if(!empty($_POST['id']) && !empty($_POST['privilege']) && !empty($_POST['login']) && !empty($_POST['agence'])&& !empty($_POST['nomUser']) ){
				
				$stmtUser = User::searchById($id);
					$user = array();
					while ($result = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC)) {
						$user =  array("idUser"=>$result['idUser'],
							"password"=>$result['password'],
							"privilege"=>$result['privilege'],
							"login"=>$result['login'],
							"agence"=>$result['idAgence'],
							);	
					}
					
					
						if(!empty($user)){
							
							$stmtUser1 = User::searchBylogin($login);
								$user1 = array();
								while ($result = sqlsrv_fetch_array($stmtUser1, SQLSRV_FETCH_ASSOC)) {
									$user1 =  array("idUser"=>$result['idUser'],
												"password"=>$result['password'],
												"privilege"=>$result['privilege'],
												"login"=>$result['login'],
												"agence"=>$result['idAgence'],
												);	
								}
								
								if(!empty($user1)){
									if($user1['idUser'] == $user['idUser']){
										
										 $addUser = User::save($nomUser,$login,$user['password'],$agence,$privilege,$id);
											if ($addUser === true) {
												$message = "L'utilisateur a bien étè enregistré";
												$result = array("statuts" => 0, "mes" => $message);
											} else {
												$message = "Erreur lors de l'enregistrement";
												$result = array("statuts" => 1,"mes" => $message);
											}
									}else{
											$message = 'Ce login est déjà utilisé ';
											$result = array("statuts" => 1, "mes" => $message);
									 }
									 
								}else{
									$addUser = User::save($nomUser,$login,$user['password'],$agence,$privilege,$id);
										if ($addUser === true) {
											$message = "L'utilisateur a bien étè enregistré";
											$result = array("statuts" => 0, "mes" => $message);
										} else {
											$message = "Erreur lors de l'enregistrement";
											$result = array("statuts" => 1,"mes" => $message);
										}
							 }
							
						}else{
							
							$message = 'Erreur lors de l\'enregistrement ';
							$result = array("statuts" => 1, "mes" => $message);
						 }
							 
				}else{
					$message = 'Une erreur est survenue, réessayer';
					$result = array("statuts" => 1, "mes" => $message);
				}
				
            }else{
				
                $message = 'Renseigner tous les champs obligatoires PHP';
                $result = array("statuts" => 1, "mes" => $message);
            }

        echo json_encode($result);

    }

	public function deleteUser(){
		
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {

            $id = $_POST['id'];

			$stmtUser = User::searchById($id);
				$user = array();
				while ($result = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC)) {
					$user =  array("idUser"=>$result['idUser'],
									"password"=>$result['password'],
									"privilege"=>$result['privilege'],
									"login"=>$result['login'],
									"agence"=>$result['agence'],
									);	
	}
            
            if(!empty($user)){
                $addUpdate = User::delete($id);

                if ($addUpdate == TRUE) {
                    $message = "La suppression a ete bien effectue";
                    $result = array("statuts" => 0, "mes" => $message);
                } else {
                    $message = "Erreur lors de l'enregistrement";
                    $result = array("statuts" => 1, "mes" => $message);
                }
            }else {
                $message = "Une erreur est survenue, r�essayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, r�essayez plus tard !!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }
	
	public function resetUserCaisse(){
		
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['confirmPassword']) && !empty($_POST['confirmPassword'])) {

            $id = $_POST['id'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirmPassword'];

			$stmtUser = User::searchById($id);
				$user = array();
					while ($result = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC)) {
						$user =  array("idUser"=>$result['idUser'],
							"password"=>$result['password'],
							"privilege"=>$result['privilege'],
							"login"=>$result['login'],
							"agence"=>$result['idAgence'],
							);	
					}
            
            if(!empty($user)){
				
				if(strcmp($password,$confirmPassword) == 0){
					
					$addUpdate = User::resetPassword($password,$id);

					if ($addUpdate == TRUE) {
						$message = "La reinitialisation a ete bien effectue";
						$result = array("statuts" => 0, "mes" => $message);
					} else {
						$message = "Erreur lors de l'enregistrement";
						$result = array("statuts" => 1, "mes" => $message);
					}
					
				}else{
					$message = 'Les mot de passe ne sont pas identiques';
					$return = array("statuts" => 1, "mes" => $message);
				}
            }else {
                $message = "Une erreur est survenue, r�essayez plus tard !!";
                $result = array("statuts" => 1, "mes" => $message);
            }

        }else {
            $message = "Une erreur est survenue, r�essayez plus tard !!";
            $result = array("statuts" => 1, "mes" => $message);
        }

        echo json_encode($result);

    }
	
	public function resetPassUtilisateur(){
		
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
				
				$ancien = $user['password'];
				$ancienSaisi = (sha1($oldPassword));
				
				
				if(strcmp($ancien,$ancienSaisi) == 0){
					
					if(strcmp($newPassword,$confirmPassword) == 0){
						
						$addUpdate = Utilisateur::resetPassword(sha1($newPassword),$user['idUser']);

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

}

?>