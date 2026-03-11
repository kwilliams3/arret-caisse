<?php
echo "<pre>";
echo "=== IMPORT DES CLIENTS AUTORISÉS (AVEC DOUBLONS) ===\n\n";

// Paramètres de connexion avec authentification Windows
$serverName = "localhost";
$connectionOptions = array(
    "Database" => "ArretsCaisses",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true
);

// Tentative de connexion
echo "Connexion à la base de données...\n";
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("✗ Erreur de connexion : " . print_r(sqlsrv_errors(), true));
}
echo "✓ Connexion à la base de données établie\n\n";

// SUPPRIMER LA CONTRAINTE UNIQUE
echo "Recherche et suppression de la contrainte UNIQUE...\n";
$findConstraint = "SELECT name FROM sys.key_constraints 
                   WHERE type = 'UQ' AND parent_object_id = OBJECT_ID('clients_autorises')";
$result = sqlsrv_query($conn, $findConstraint);

if ($result && sqlsrv_has_rows($result)) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $constraintName = $row['name'];
        $dropSql = "ALTER TABLE clients_autorises DROP CONSTRAINT [$constraintName]";
        if (sqlsrv_query($conn, $dropSql)) {
            echo "✓ Contrainte '$constraintName' supprimée\n";
        } else {
            echo "✗ Erreur lors de la suppression de la contrainte '$constraintName'\n";
        }
    }
} else {
    echo "✓ Aucune contrainte UNIQUE trouvée\n";
}
echo "\n";

// Vider la table existante
echo "Nettoyage de la table clients_autorises...\n";
$truncateSql = "DELETE FROM clients_autorises";
sqlsrv_query($conn, $truncateSql);
echo "✓ Table nettoyée\n\n";

// Données extraites du fichier Excel
$clients = array(
    // PV MOKOLO
    array('site' => 'PV MOKOLO', 'nom' => 'SONECOMX', 'contact' => '655775555', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'PV MOKOLO', 'nom' => 'STE DOVV SARL', 'contact' => '699416085', 'plafond' => 20000000, 'parrainage' => 'NA'),
    array('site' => 'PV MOKOLO', 'nom' => 'STE TZ CONSTRUCTION', 'contact' => '675554525', 'plafond' => 2000000, 'parrainage' => 'NA'),
    
    // HM MOKOLO
    array('site' => 'HM MOKOLO', 'nom' => 'COMEC SARL', 'contact' => '677783796', 'plafond' => 1000000, 'parrainage' => 'NA'),
    
    // NLONGKAK
    array('site' => 'NLONGKAK', 'nom' => 'STE CONGELCAM SA', 'contact' => '681165935', 'plafond' => 20000000, 'parrainage' => 'NA'),
    
    // GRAND HANGAR
    array('site' => 'GRAND HANGAR', 'nom' => 'BOCOM INDUSTRY', 'contact' => '691149060', 'plafond' => 7000000, 'parrainage' => 'NA'),
    array('site' => 'GRAND HANGAR', 'nom' => 'KADJI FOOD SERVICE', 'contact' => '693831553', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'GRAND HANGAR', 'nom' => 'GRAPHICS SYSTÈME', 'contact' => '695181088', 'plafond' => 8000000, 'parrainage' => 'NA'),
    array('site' => 'GRAND HANGAR', 'nom' => 'GROUPE WAGA SARL', 'contact' => '698258559', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'GRAND HANGAR', 'nom' => 'BOCOM PETROLEUM', 'contact' => '679221540', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'GRAND HANGAR', 'nom' => 'SITRABCAM', 'contact' => '', 'plafond' => 10000000, 'parrainage' => 'NA'),
    
    // SHOW ROOM
    array('site' => 'SHOW ROOM', 'nom' => 'MIT CHIMIE SARL', 'contact' => '699681643', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'CONCEPT CAMEROUN', 'contact' => '652066202', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'MCQ SARL', 'contact' => '699973958', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'CODIREL CAMEROUN', 'contact' => '699948897', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'VOGE ENERGY', 'contact' => '', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'FACOMEC SARL', 'contact' => '675132725 / 697826747', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'COMI ET TELECOM', 'contact' => '677724129', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'ELECTRO PLOMB', 'contact' => '677795088', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'INSTRUMELEC', 'contact' => '696614691', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'SODIMEI', 'contact' => '699849505', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'NITTRAL/SEICITEL', 'contact' => '677510552', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'ETS TYRES AND CARS ACCESSOIRS', 'contact' => '691 88 30 32', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'INTEC-M', 'contact' => '699 52 60 64', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'FOURNI TOUT SARL', 'contact' => '677 23 12 40', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'SAFETY SARL', 'contact' => '677526187', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'POWERLINK', 'contact' => '656132880', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'SHOW ROOM', 'nom' => 'SOPIC PLUS', 'contact' => '677341734/696173866', 'plafond' => 5000000, 'parrainage' => 'NA'),
    
    // TAMDJA
    array('site' => 'TAMDJA', 'nom' => 'DEFEUGAING JOSEPH / BWB', 'contact' => '695145801', 'plafond' => 5000000, 'parrainage' => 'NA'),
    
    // YASSA
    array('site' => 'YASSA', 'nom' => 'CANOCAM', 'contact' => '683134781', 'plafond' => 5000000, 'parrainage' => 'NA'),
    
    // BEACH
    array('site' => 'BEACH', 'nom' => 'STE DES ETS MONKAM', 'contact' => '656500753', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'KALFRELEC SARL', 'contact' => '677.79.30.88', 'plafond' => 50000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'BATISSEURS REUNIS', 'contact' => '699.25.45.45', 'plafond' => 30000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'STE CONGELCAM SA', 'contact' => '681.24.50.00', 'plafond' => 50000000, 'parrainage' => 'NA'),  // DOUBLON
    array('site' => 'BEACH', 'nom' => 'NAT SERVICES/LYNA TECHNOLOGIE/SPRING', 'contact' => '696105667', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SCI JHOANLES M DOGMO SYLVESTRE', 'contact' => '699960052', 'plafond' => 20000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SOTIN SARL/POLLA JACQUES BOULANGERIE', 'contact' => '699917154', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SAE/RWKING', 'contact' => '677848664/698121985', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'ZATI SARL', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SEMEM DISTRIBUTORS', 'contact' => '670599438/674151012', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'PRISO OLIVIER M ME ENFANT PRISO MOULEMA', 'contact' => '699969762', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'CAMBUILD SARL', 'contact' => '656500753', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'POLYPHARMA', 'contact' => '699319849', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'FOCALI', 'contact' => '695354821', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'EBOUMBOU EBOA MQNDFRED', 'contact' => '677708676', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'AFRIMAR CAMEROUN', 'contact' => '', 'plafond' => 4000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'FORCE TYRE', 'contact' => '', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'NEPTUNE OIL', 'contact' => '674301315', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'FOBS', 'contact' => '677305197', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SONOLEC', 'contact' => '699948485', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SOCOCAM', 'contact' => '696437799', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'EXPRESS EXCHANGE', 'contact' => '677505711', 'plafond' => 50000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SCICORP/ROSTY GROUP', 'contact' => '672597979', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'S2TBED SARL', 'contact' => '699965661', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SATECH', 'contact' => '670287639', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SCI TAZ', 'contact' => '697134205', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'TAC', 'contact' => '699681509', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'STE ECO SERVICES SARL', 'contact' => '696189998', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'STE GDE SARL GENERALE DE DISTRIBUTION ELECTRIQUE / GDE', 'contact' => '699912136', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'PAC INTERNATION', 'contact' => '', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'DR FONKOUA RODOLPHE', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'M ONANA NDOH LIN', 'contact' => '675697077/699929046', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SCI HIBISCUS OU PLATINUM', 'contact' => '6942772843/699926782', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'JEMMI MONIQUE', 'contact' => '699686803', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'EGESCO INTERNATIONAL', 'contact' => '699911740', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'PROBAT SARL', 'contact' => '699933270', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'ATLANTIC CONSTRUTION SA', 'contact' => '696973984', 'plafond' => 1500000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'GEMAT SARL', 'contact' => '699836915', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'ETD SARL', 'contact' => '69989591', 'plafond' => 3000000, 'parrainage' => 'NA'),  // DOUBLON 1
    array('site' => 'BEACH', 'nom' => 'SOTICAM', 'contact' => '695459181/695929292', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'CREBAT SARL', 'contact' => '674182611/650749805', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'DIC INGENEERING', 'contact' => '694303725', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'M.FEUTO SAMUEL', 'contact' => '', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'THOMTHE', 'contact' => '', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'CHRISTINA HOTEL', 'contact' => '691143022', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'FODIS SAS', 'contact' => '677305197', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'BEACH', 'nom' => 'SIETEL SARL', 'contact' => '699688632', 'plafond' => 1000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'KM40 CONSULTING AND SERVICE SARL', 'contact' => '696369780', 'plafond' => 1000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'BELL ENGINEERING', 'contact' => '696281872', 'plafond' => 1000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'ESTIA SYNERGI CAMEROUN', 'contact' => '653742612/654944444', 'plafond' => 1000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'AMD ACIER ET METAUX DIVERS', 'contact' => '677246397', 'plafond' => 5000000, 'parrainage' => 'NAIM'),  // DOUBLON
    array('site' => 'BEACH', 'nom' => 'PHARMACIE DE LA COTE', 'contact' => '694336582', 'plafond' => 1000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'PETIT ROUSSEAU', 'contact' => '699929551', 'plafond' => 1000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'ETD SARL', 'contact' => '699889591', 'plafond' => 3000000, 'parrainage' => 'NAIM'),  // DOUBLON 2
    array('site' => 'BEACH', 'nom' => 'FD CONSULTING / FOTSO DANIEL', 'contact' => '677754981', 'plafond' => 5000000, 'parrainage' => 'NAIM'),
    array('site' => 'BEACH', 'nom' => 'BOCOM PETROLEUM', 'contact' => '691207794', 'plafond' => 5000000, 'parrainage' => 'NAIM'),  // DOUBLON
    array('site' => 'BEACH', 'nom' => 'SOCAFER', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'NAIM'),  // DOUBLON
    array('site' => 'BEACH', 'nom' => 'STE RG ET COMPAGNIE', 'contact' => '677936379', 'plafond' => 2000000, 'parrainage' => '30 JOURS APRES LIVRAISON /NAIM'),
    array('site' => 'BEACH', 'nom' => 'STE LEABACK SARL', 'contact' => '696131588', 'plafond' => 1000000, 'parrainage' => 'BERNARD KAMGA'),
    array('site' => 'BEACH', 'nom' => 'CIS', 'contact' => '696567602', 'plafond' => 2000000, 'parrainage' => 'BERNARD KAMGA'),
    array('site' => 'BEACH', 'nom' => 'ISIC SARL', 'contact' => '677495150', 'plafond' => 3000000, 'parrainage' => 'TOUKAM SERGE'),
    array('site' => 'BEACH', 'nom' => 'ELEC TEC', 'contact' => '696630755', 'plafond' => 3000000, 'parrainage' => 'TOUKAM SERGE'),
    array('site' => 'BEACH', 'nom' => 'SMBA', 'contact' => '675785128', 'plafond' => 2000000, 'parrainage' => 'TAPA ARNAUD'),
    array('site' => 'BEACH', 'nom' => 'COMEBAT', 'contact' => '699785890', 'plafond' => 2000000, 'parrainage' => 'NAIM / TAPA ARNAUD'),
    array('site' => 'BEACH', 'nom' => 'STE LES MARCHANDS REUNIS CAM (STE MRC)', 'contact' => '692782736', 'plafond' => 2000000, 'parrainage' => 'TAPA ARNAUD'),
    array('site' => 'BEACH', 'nom' => 'PRINT INDUSTRY', 'contact' => '677975980', 'plafond' => 2000000, 'parrainage' => 'BERNARD KAMGA'),
    array('site' => 'BEACH', 'nom' => 'ARNO SA', 'contact' => '693751414', 'plafond' => 2000000, 'parrainage' => 'SERGE TAMO'),
    array('site' => 'BEACH', 'nom' => 'COCIC SARL/ETS ROUSSEAUX INTERNATIONAL SHCOOL/RHITROUSSEAU/HIGTHER INSTITUTE OP TECHNOLOGIE', 'contact' => '699727533', 'plafond' => 3000000, 'parrainage' => 'DENISE OUAMBO'),
    array('site' => 'BEACH', 'nom' => 'STE ELECIT ENGENERING', 'contact' => '699938522', 'plafond' => 1000000, 'parrainage' => 'DENISE OUAMBO'),
    array('site' => 'BEACH', 'nom' => 'SCI MELANG /SOCORPA', 'contact' => '699915413/670077982', 'plafond' => 3000000, 'parrainage' => 'DENISE OUAMBO'),
    array('site' => 'BEACH', 'nom' => 'GROUPE WAGA SARL', 'contact' => '699960415', 'plafond' => 6000000, 'parrainage' => 'DENISE OUAMBO'),  // DOUBLON
    
    // MBOPPI
    array('site' => 'MBOPPI', 'nom' => 'AT GRAPHILINE INDUSTRY', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'MBOPPI', 'nom' => 'GPI', 'contact' => '', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'MBOPPI', 'nom' => 'PROMOTECH', 'contact' => '655-00-01-71', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'MBOPPI', 'nom' => 'MANU CYCLE', 'contact' => '699204835', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'MBOPPI', 'nom' => 'CAMDRES SARL', 'contact' => '691599629', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'MBOPPI', 'nom' => 'SOCAFER', 'contact' => '', 'plafond' => 1500000, 'parrainage' => 'NA'),  // DOUBLON
    array('site' => 'MBOPPI', 'nom' => 'AMD ACIER ET METAUX DIVERS', 'contact' => '', 'plafond' => 5000000, 'parrainage' => 'NA'),  // DOUBLON
    
    // EMERAUDE
    array('site' => 'EMERAUDE', 'nom' => 'STE CONCEPT SARL', 'contact' => '678-94-74-67', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'METCH ELEC', 'contact' => '691-14-38-00', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'CORE MANUFACTURATION (M. FANKEM)', 'contact' => '694-19-45-24', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'GLOBAL PETROLIUM', 'contact' => '656-74-94-36', 'plafond' => 15000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'CEFO TECH', 'contact' => '699-80-68-85', 'plafond' => 1000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'FOKA CONSTRUCTION', 'contact' => '699-33-00-79', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'SAAGRY SA', 'contact' => '673-75-51-11', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'SOMETAL', 'contact' => '699-83-14-27', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'GPE SCOLAIRE WAFO PIERRE', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'HEAVY DUTY ENGINEERING', 'contact' => '670-79-34-56', 'plafond' => 10000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'MAISON DU PLOMBIER', 'contact' => '', 'plafond' => 1500000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'SCI HOMES', 'contact' => '696990233', 'plafond' => 2000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'TECHNIQUE ELECTRIQUE', 'contact' => '696714199', 'plafond' => 3000000, 'parrainage' => 'NA'),
    array('site' => 'EMERAUDE', 'nom' => 'MANOU SARL (M. FANKEM TAYOU) / HOTEL MANO', 'contact' => '699-93-40-91', 'plafond' => 200000000, 'parrainage' => 'NA'),
    
    // KRIBI
    array('site' => 'KRIBI', 'nom' => 'HOTEL LE LAGON', 'contact' => '699608263', 'plafond' => 5000000, 'parrainage' => 'NA'),
    
    // SODIKO
    array('site' => 'SODIKO', 'nom' => 'SPARK SARL', 'contact' => '', 'plafond' => 1000000, 'parrainage' => '60 JOURS APRES LIVRAISON'),
    
    // DIRECTION GENERALE
    array('site' => 'DIRECTION GENERALE', 'nom' => 'MCG', 'contact' => '', 'plafond' => 500000, 'parrainage' => 'NA'),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'SCI CHIMEDE', 'contact' => '', 'plafond' => 5000000, 'parrainage' => 'NA'),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'BEETLE HERITAGE HOLDING S.A', 'contact' => '698 005 348 / 699 901 708 /699 905 739 / 699 920 612', 'plafond' => 10000000, 'parrainage' => '30 JOURS DES RECEPTION DE LA COMMANDE'),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'AFRICA FOOD MANUFACTURE DIVISION PATES ALIMENTAIRES', 'contact' => '', 'plafond' => 25000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'AFRICA FOOD MANUFACTURE DIVISION SEMOULERIE', 'contact' => '', 'plafond' => 25000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'AFRICA FOOD MANUFACTURE DIVISION EPICERIE', 'contact' => '', 'plafond' => 25000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'AFRICA FOOD MANUFACTURE DIVISION RAFFINERIE', 'contact' => '', 'plafond' => 25000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'AFRICA FOOD IMPORT', 'contact' => '', 'plafond' => 10000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'THE BRIDGE INTERNATIONAL SCHOOL', 'contact' => '', 'plafond' => 10000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'AFRICA FOOD DISTRIBUTION S.A', 'contact' => '', 'plafond' => 10000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'SEALAND SERVICES SARL', 'contact' => '', 'plafond' => 10000000, 'parrainage' => ''),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'GSTC', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'WOUCHE ARMAND'),
    array('site' => 'DIRECTION GENERALE', 'nom' => 'EMEI DIESEL', 'contact' => '', 'plafond' => 2000000, 'parrainage' => 'NA'),
    
    // DIRECTION REGIONALE YAOUNDE
    array('site' => 'DIRECTION REGIONALE YAOUNDE', 'nom' => 'ASHISH', 'contact' => '', 'plafond' => 5000000, 'parrainage' => '15 JOURS APRES LIVRAISON'),
    
    // NDOGPASSI
    array('site' => 'NDOGPASSI', 'nom' => 'AFRIQUE SERVICE INDUSTRIE(ASI S.A)', 'contact' => '696295549', 'plafond' => 3000000, 'parrainage' => 'NKAMDEM NORBERT'),
    
    // PK12
    array('site' => 'PK12', 'nom' => 'FUTURA CONSTRUCTION SARL', 'contact' => '696295549', 'plafond' => 3000000, 'parrainage' => 'DEFFO MACAIR CEDIC'),
    array('site' => 'PK12', 'nom' => 'LST(L\'entreprise de services et travaux)', 'contact' => '677733259', 'plafond' => 2000000, 'parrainage' => ''),
    array('site' => 'PK12', 'nom' => 'NODICAM', 'contact' => '690557699/673487329', 'plafond' => 1000000, 'parrainage' => 'WADJIE KUMGNE'),
    
    // NOUVELLE AJOUT - EMERAUDE
    array('site' => 'EMERAUDE', 'nom' => 'DU VAAL (COLLEGE POLYVALENT BILINGUE)', 'contact' => '699038399 / 678435538 / 656755628', 'plafond' => 5000000, 'parrainage' => 'BACCOUCHE MOHAMED SALAH')
);

echo "Import de " . count($clients) . " clients...\n\n";

$success = 0;
$errors = 0;
$doublons = array();

foreach ($clients as $index => $client) {
    $sql = "INSERT INTO clients_autorises (site_demandeur, nom_client, contact, plafond, parrainage) 
            VALUES (?, ?, ?, ?, ?)";
    
    $params = array(
        $client['site'],
        $client['nom'],
        $client['contact'],
        $client['plafond'],
        $client['parrainage']
    );
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        $success++;
        echo "✓ [" . $client['site'] . "] " . $client['nom'] . "\n";
    } else {
        $errors++;
        $error = sqlsrv_errors();
        if (isset($error[0]['code']) && $error[0]['code'] == 2627) {
            // C'est une erreur de doublon
            $doublons[] = $client['nom'] . " (Site: " . $client['site'] . ")";
            echo "⚠️ DOUBLON ignoré : " . $client['nom'] . " (Site: " . $client['site'] . ")\n";
        } else {
            echo "✗ Erreur pour " . $client['nom'] . ": " . print_r($error, true) . "\n";
        }
    }
}

echo "\n=== RÉSULTAT DE L'IMPORT ===\n";
echo "Total clients dans le fichier : " . count($clients) . "\n";
echo "Importés avec succès : $success\n";
echo "Erreurs : $errors\n";

if (!empty($doublons)) {
    echo "\n⚠️ DOUBLONS DÉTECTÉS (" . count($doublons) . ") :\n";
    foreach ($doublons as $doublon) {
        echo "   - $doublon\n";
    }
}

if ($errors === 0) {
    echo "\n✅ IMPORT TERMINÉ AVEC SUCCÈS !\n";
} else {
    echo "\n⚠️ IMPORT TERMINÉ AVEC DES ERREURS\n";
}

sqlsrv_close($conn);
echo "</pre>";
?>