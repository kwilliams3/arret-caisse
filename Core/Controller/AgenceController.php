<?php

namespace Core\Controller;

use Core\Database\Agence;
use Core\Database\RequeteVentes;
use Core\Model\App;
use Core\Model\AppController;
use Core\Model\Model;
use Core\Model\Session;

class AgenceController extends AppController{

    public function index(){

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
        $this->render('agence.index',compact('agences'));
	
		}


    public function addAgenceCaisse()
    {
        header('content-type: application/json');
        $result = [];

            if(isset($_POST['designation']) && !empty($_POST['designation'])) {

                $designation = $_POST['designation'];

                    $addAgence = Agence::save($designation);

                    if ($addAgence === true) {
                        $message = "l'agence a bien ete enregistre";
                        $result = array("statuts" => 0, "mes" => $message);
                    } else {
                        $message = "Erreur lors de l'enregistrement";
                        $result = array("statuts" => 1,"mes" => $message);
                    }

            }else{
                $message = "Veuillez renseigner les champs obligatoires";
                $result = array("statuts" => 1, "mes" => $message);
            }


        echo json_encode($result);
    }

    public function updateAgenceCaisse(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {

            $id = $_POST['id'];

            $stmtAgence = Agence::searchById($id);
            $agence = array();
            while ($result = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                $agence = array(
                    "id" => $result['idAgence'],
                    "designation" => $result['designation'],
                );
            }


            $add = false;

            if(!empty($agence)){

                $content = '

                <form action="'.App::url('ajax.agence.editAgenceCaisse').'" method="POST" id="form-EditAgence">
                    <input type="hidden" class="idAgenceCaisse1" name="idAgence" value="'.$agence['id'].'">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="control-label">Designation</label>
                            <input type="text" class="designation1 form-control add-tooltip" id="designation1" value="'.$agence['designation'].'" name="code" data-placeholder="Entrer la designation" title="Entrer la designation" style="width: 100%;" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-offset-9 col-md-3" style="padding-top: 25px;">
                            <button type="submit" class="register btn btn-primary ">Enregistrer</button>
                        </div>
                    </div>
                    <div class="clearfix hidden loader">
                        <center>
                            <h2 class="header smaller lighter grey">
                                <i class="ace-icon fa fa-spinner fa-spin green bigger-125"></i>
                            </h2>
                        </center>
                    </div>
                </form>

                    <script type="text/javascript">

                        $( "#form-EditAgence" ).on("submit", function(e) {
                            e.preventDefault();

                            var url = $(this).attr("action");
                            var idAgence = $(".idAgenceCaisse1").val();
                            var designation = $(".designation1").val();

                                if (url != " " && idAgence != " " && designation != " ") {
                                    $.ajax({
                                        type: "post",
                                        url: url,
                                        data: "id="+idAgence+"&designation="+designation,
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
                                    alert("Veuillez rensiegner tous les champs obligatoires");
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

    public function editAgenceCaisse(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['designation']) && !empty($_POST['designation']) && isset($_POST['id']) && !empty($_POST['id'])) {

            $designation = ($_POST['designation']);
            $id = $_POST['id'];

            $addUpdate = false;

            $stmtAgence = Agence::searchById($id);
            $agence = array();
            while ($result = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                $agence = array(
                    "id" => $result['idAgence'],
                    "designation" => $result['designation'],
                );
            }

            if(!empty($agence)){
                $addUpdate = Agence::save($designation,$id);

                if ($addUpdate == TRUE) {
                    $message = "l'enregistrement de l'agence a ete bien enregistre";
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

    public function deleteAgence(){
        header('content-type: application/json');
        $result = [];

        if(isset($_POST['id']) && !empty($_POST['id'])) {

            $id = $_POST['id'];

            $stmtAgence = Agence::searchById($id);
            $agence = array();
            while ($result = sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
                $agence = array(
                    "id" => $result['idAgence'],
                    "titre" => $result['titre'],
                );
            }
            if(!empty($agence)){
                $addUpdate = Agence::delete($id);

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

}

?>