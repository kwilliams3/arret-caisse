<?php
namespace Core\Database;

class Client {
    
    public static function executeQuery($sql, $params = [], $fetchOne = false) {
        // TEMPORAIRE : Simule la base pour développement
        error_log("SQL simulé: $sql");
        
        // Pour Cheque::getAll()
        if (strpos($sql, 'SELECT * FROM Cheques') !== false) {
            return [
                [
                    'id' => 1,
                    'nom_client' => 'Client Test',
                    'numero_cheque' => '123456',
                    'montant' => 150000,
                    'banque' => 'BICEC',
                    'date_reception' => '2024-01-15',
                    'date_entree' => '2024-01-15 10:30:00',
                    'statut' => 'en cours',
                    'agence_id' => 1,
                    'scan_path' => null,
                    'created_by' => 1
                ],
                [
                    'id' => 2,
                    'nom_client' => 'Autre Client',
                    'numero_cheque' => '789012',
                    'montant' => 75000,
                    'banque' => 'SGBC',
                    'date_reception' => '2024-01-16',
                    'date_entree' => '2024-01-16 14:20:00',
                    'statut' => 'confirmé',
                    'agence_id' => 2,
                    'scan_path' => null,
                    'created_by' => 2
                ]
            ];
        }
        
        // Pour INSERT (ajout)
        if (strpos($sql, 'INSERT INTO Cheques') !== false) {
            error_log("INSERT simulé avec données: " . print_r($params, true));
            return true;
        }
        
        // Pour UPDATE (confirmation/validation/annulation)
        if (strpos($sql, 'UPDATE Cheques') !== false) {
            error_log("UPDATE simulé: $sql");
            return true;
        }
        
        // Par défaut
        return $fetchOne ? null : [];
        
        /*
        // CODE RÉEL (commenté pour l'instant)
        $serverName = "localhost\\SQLEXPRESS"; // Essaie ça
        $connectionInfo = array(
            "Database" => "GMAO", 
            "UID" => "", 
            "PWD" => "",
            "CharacterSet" => "UTF-8"
        );
        
        $conn = sqlsrv_connect($serverName, $connectionInfo);
        
        if($conn === false) {
            error_log("SQL Server error: " . print_r(sqlsrv_errors(), true));
            return $fetchOne ? null : [];
        }
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if($stmt === false) {
            error_log("Query error: " . print_r(sqlsrv_errors(), true));
            sqlsrv_close($conn);
            return $fetchOne ? null : [];
        }
        
        $results = [];
        if($fetchOne) {
            $results = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        } else {
            while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $results[] = $row;
            }
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        return $results;
        */
    }
}