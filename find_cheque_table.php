<?php
// find_cheque_table.php
session_start();
require_once 'Core/Model/App.php';
require_once 'Core/Model/Table.php';

echo "<h3>Recherche de la table Cheques</h3>";

// Utiliser la même connexion que l'application
$db = Core\Model\App::getDB();

// 1. Chercher toutes les tables avec "cheque"
$sql = "SELECT TABLE_SCHEMA, TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_NAME LIKE '%cheque%' 
        ORDER BY TABLE_SCHEMA, TABLE_NAME";
        
echo "<h4>Tables trouvées :</h4>";
try {
    $tables = $db->query($sql, null, true);
    
    if (empty($tables)) {
        echo "Aucune table contenant 'cheque' trouvée.<br>";
        
        // 2. Voir toutes les tables
        $sql2 = "SELECT TABLE_SCHEMA, TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_TYPE = 'BASE TABLE'
                ORDER BY TABLE_SCHEMA, TABLE_NAME";
        $allTables = $db->query($sql2, null, true);
        
        echo "<h4>Toutes les tables :</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Schéma</th><th>Table</th></tr>";
        foreach ($allTables as $table) {
            echo "<tr>";
            echo "<td>" . $table['TABLE_SCHEMA'] . "</td>";
            echo "<td>" . $table['TABLE_NAME'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Schéma</th><th>Table</th></tr>";
        foreach ($tables as $table) {
            echo "<tr>";
            echo "<td>" . $table['TABLE_SCHEMA'] . "</td>";
            echo "<td>" . $table['TABLE_NAME'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}

// 3. Tester différentes combinaisons
echo "<h4>Test d'accès direct :</h4>";

$testCombinations = [
    'Cheques',
    '[Cheques]',
    'dbo.Cheques',
    '[dbo].[Cheques]',
    'public.Cheques',
    '[public].[Cheques]',
    'Cheques.dbo.Cheques',
    'Cheques..Cheques'
];

foreach ($testCombinations as $tableName) {
    $sql = "SELECT COUNT(*) as cnt FROM $tableName";
    echo "Test : $tableName => ";
    
    try {
        $result = $db->query($sql, null, true);
        echo "✅ OK (" . $result[0]['cnt'] . " lignes)<br>";
        
        // Voir la structure
        $sql2 = "SELECT TOP 1 * FROM $tableName";
        $structure = $db->query($sql2, null, true);
        if (!empty($structure)) {
            echo "Structure : " . print_r(array_keys($structure[0]), true) . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erreur : " . $e->getMessage() . "<br>";
    }
}

// 4. Si aucune ne marche, chercher dans tous les schémas
echo "<h4>Recherche dans tous les schémas :</h4>";
$sql = "SELECT DISTINCT TABLE_SCHEMA FROM INFORMATION_SCHEMA.TABLES";
$schemas = $db->query($sql, null, true);

foreach ($schemas as $schema) {
    $schemaName = $schema['TABLE_SCHEMA'];
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = '$schemaName' AND TABLE_NAME = 'Cheques'";
    $result = $db->query($sql, null, true);
    
    if (!empty($result)) {
        echo "✅ Table Cheques trouvée dans le schéma : $schemaName<br>";
        
        // Tester l'accès
        $testSql = "SELECT COUNT(*) as cnt FROM [$schemaName].[Cheques]";
        $testResult = $db->query($testSql, null, true);
        echo "Accès : " . print_r($testResult, true) . "<br>";
    }
}
?>