<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Cheque;

$session = Session::getInstance();
$user = $_SESSION['user'];

$id = $_POST['cheque_id'];
$etat = $_POST['etat_confirmation'];
$commentaire = $_POST['commentaire_confirmation'];

// Confirmer le chèque
$result = Cheque::confirm($id, $etat, $commentaire);

echo json_encode(['success' => $result]);
?>