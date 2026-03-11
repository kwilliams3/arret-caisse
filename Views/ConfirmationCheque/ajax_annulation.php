<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Cheque;

$session = Session::getInstance();
$user = $_SESSION['user'];

$id = $_POST['cheque_id'];
$motif = $_POST['motif_annulation'];
$commentaire = $_POST['commentaire_annulation'];

// Annuler le chèque
$result = Cheque::cancel($id, $motif, $commentaire);

echo json_encode(['success' => $result]);
?>