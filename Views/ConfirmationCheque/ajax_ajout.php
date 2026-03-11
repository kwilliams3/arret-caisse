<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Cheque;

$session = Session::getInstance();
$user = $_SESSION['user'];

// Récupérer les données POST
$data = [
    'nom_client' => $_POST['nom_client'],
    'numero_cheque' => $_POST['numero_cheque'],
    'montant' => $_POST['montant'],
    'banque' => $_POST['banque'],
    'date_reception' => $_POST['date_reception'],
    'observations' => $_POST['observations'],
    'agence_id' => $user['agence'],
    'created_by' => $user['id']
];

// Gérer l'upload du scan
if (!empty($_FILES['scan_cheque']['name'])) {
    $targetDir = "DocumentsCheques/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['scan_cheque']['name']);
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($_FILES['scan_cheque']['tmp_name'], $targetPath)) {
        $data['scan_path'] = $targetPath;
    }
}

// Ajouter le chèque
$result = Cheque::add($data);

// Retourner la réponse JSON
echo json_encode(['success' => $result]);
?>