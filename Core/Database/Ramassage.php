<?php
namespace Core\Database;

use Core\Model\Table;

class Ramassage extends Table
{
    protected static $table = 'ramassage_fond';
    private static $columnCache = null;
    
    private static function resourceToArray($resource) {
        if (!is_resource($resource) && !$resource instanceof \PDOStatement) {
            return $resource;
        }
        
        $results = [];
        while ($row = sqlsrv_fetch_array($resource, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        
        if (is_resource($resource)) {
            sqlsrv_free_stmt($resource);
        }
        
        return $results;
    }
    
    private static function getTableColumns() {
        if (self::$columnCache === null) {
            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . self::$table . "'";
            $result = parent::querySelect($sql);
            $columns = [];
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $columns[] = strtolower($row['COLUMN_NAME']);
            }
            self::$columnCache = $columns;
        }
        return self::$columnCache;
    }
    
    private static function columnExists($columnName) {
        $columns = self::getTableColumns();
        return in_array(strtolower($columnName), $columns);
    }
    
    private static function getOrCreateEntite($nom) {
        try {
            $nom = strtoupper(trim($nom));
            if (empty($nom)) return 1;
            
            $sql = "SELECT id FROM entites WHERE nom = ?";
            $p1 = $nom;
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['id'])) {
                return (int)$data[0]['id'];
            }
            
            $sql = "INSERT INTO entites (nom) OUTPUT INSERTED.id VALUES (?)";
            $result = parent::querySelect($sql, array(&$p1));
            if (is_resource($result) && sqlsrv_fetch($result)) {
                $id = sqlsrv_get_field($result, 0);
                sqlsrv_free_stmt($result);
                return (int)$id;
            }
            
            return 1;
        } catch (\Exception $e) {
            error_log("❌ getOrCreateEntite ERROR: " . $e->getMessage());
            return 1;
        }
    }
    
    private static function getOrCreateAgence($nom) {
        try {
            $nom = strtoupper(trim($nom));
            if (empty($nom)) return 1;
            
            $sql = "SELECT [idAgence] FROM [dbo].[Tb_Agence] WHERE [designation] = ?";
            $p1 = $nom;
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['idAgence'])) {
                return (int)$data[0]['idAgence'];
            }
            
            $sql = "INSERT INTO [dbo].[Tb_Agence] ([designation]) OUTPUT INSERTED.idAgence VALUES (?)";
            $result = parent::querySelect($sql, array(&$p1));
            if (is_resource($result) && sqlsrv_fetch($result)) {
                $id = sqlsrv_get_field($result, 0);
                sqlsrv_free_stmt($result);
                return (int)$id;
            }
            
            return 1;
        } catch (\Exception $e) {
            error_log("❌ getOrCreateAgence ERROR: " . $e->getMessage());
            return 1;
        }
    }
    
    public static function getAll() {
        try {
            // REQUETE AVEC JOINTURES POUR RECUPERER DIRECTEMENT LES NOMS
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    ORDER BY rf.date_creation DESC";
            
            $result = parent::querySelect($sql);
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            // Ajouter created_by_name si disponible
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                // S'assurer que les noms ont une valeur par défaut
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getAll ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function find($id) {
        try {
            // REQUETE AVEC JOINTURES POUR RECUPERER DIRECTEMENT LES NOMS
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    WHERE rf.id = ?";
            
            $p1 = intval($id);
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (!empty($data)) {
                $row = $data[0];
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                // S'assurer que les noms ont une valeur par défaut
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
                
                return $row;
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::find ERROR: " . $e->getMessage());
            return null;
        }
    }
    
    public static function getPaginated($limit = 10, $offset = 0) {
        try {
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    ORDER BY rf.date_creation DESC
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            
            $p1 = intval($offset);
            $p2 = intval($limit);
            $result = parent::querySelect($sql, array(&$p1, &$p2));
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getPaginated ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function search($searchTerm, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    WHERE rf.periode LIKE ? 
                       OR rf.observations LIKE ?
                    ORDER BY rf.date_creation DESC
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            
            $searchPattern = '%' . $searchTerm . '%';
            $p1 = $searchPattern;
            $p2 = $searchPattern;
            $p3 = intval($offset);
            $p4 = intval($limit);
            
            $result = parent::querySelect($sql, array(&$p1, &$p2, &$p3, &$p4));
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::search ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function searchCount($searchTerm) {
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM " . self::$table . " rf
                    WHERE rf.periode LIKE ? 
                       OR rf.observations LIKE ?";
            
            $searchPattern = '%' . $searchTerm . '%';
            $p1 = $searchPattern;
            $p2 = $searchPattern;
            
            $result = parent::querySelect($sql, array(&$p1, &$p2));
            $data = self::resourceToArray($result);
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::searchCount ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function count() {
        try {
            $sql = "SELECT COUNT(*) as total FROM " . self::$table;
            $result = parent::querySelect($sql);
            $data = self::resourceToArray($result);
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::count ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function create($data, $listePath = null) {
        try {
            $hasUpdatedAt = self::columnExists('updated_at');
            $hasUpdatedBy = self::columnExists('updated_by');
            $hasDateModification = self::columnExists('date_modification');
            
            $fields = [];
            $placeholders = [];
            $params = [];
            
            $entite_id = self::getOrCreateEntite($data['entite_nom']);
            $agence_id = self::getOrCreateAgence($data['agence_nom']);
            
            $fields[] = 'entite_id';
            $placeholders[] = '?';
            $p1 = $entite_id;
            $params[] = &$p1;
            
            $fields[] = 'agence_id';
            $placeholders[] = '?';
            $p2 = $agence_id;
            $params[] = &$p2;
            
            $fields[] = 'periode';
            $placeholders[] = '?';
            $p3 = isset($data['periode']) ? trim($data['periode']) : '';
            $params[] = &$p3;
            
            $fields[] = 'date_debut';
            $placeholders[] = '?';
            $p4 = isset($data['date_debut']) ? $data['date_debut'] : '';
            $params[] = &$p4;
            
            $fields[] = 'date_fin';
            $placeholders[] = '?';
            $p5 = isset($data['date_fin']) ? $data['date_fin'] : '';
            $params[] = &$p5;
            
            $fields[] = 'liste_path';
            $placeholders[] = '?';
            $p6 = $listePath;
            $params[] = &$p6;
            
            $fields[] = 'observations';
            $placeholders[] = '?';
            $p7 = isset($data['observations']) ? trim($data['observations']) : '';
            $params[] = &$p7;
            
            $fields[] = 'created_by';
            $placeholders[] = '?';
            $p8 = isset($data['created_by']) ? intval($data['created_by']) : null;
            $params[] = &$p8;
            
            $fields[] = 'date_creation';
            $placeholders[] = 'GETDATE()';
            
            if ($hasUpdatedAt) {
                $fields[] = 'updated_at';
                $placeholders[] = 'GETDATE()';
            }
            
            if ($hasUpdatedBy && isset($data['created_by'])) {
                $fields[] = 'updated_by';
                $placeholders[] = '?';
                $params[] = &$p8;
            }
            
            if ($hasDateModification) {
                $fields[] = 'date_modification';
                $placeholders[] = 'GETDATE()';
            }
            
            $sql = "INSERT INTO " . self::$table . " (" . implode(', ', $fields) . ") 
                    OUTPUT INSERTED.id
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $result = parent::querySelect($sql, $params);
            
            $insertedId = null;
            
            if (is_resource($result)) {
                if (sqlsrv_fetch($result)) {
                    $insertedId = sqlsrv_get_field($result, 0);
                    sqlsrv_free_stmt($result);
                }
            }
            
            if ($insertedId) {
                return (int)$insertedId;
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("❌ Ramassage::create ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public static function update($id, $data) {
        try {
            if (empty($id) || empty($data)) return false;
            
            $fields = [];
            $params = [];
            
            if (isset($data['entite_nom'])) {
                $entite_id = self::getOrCreateEntite($data['entite_nom']);
                $data['entite_id'] = $entite_id;
                unset($data['entite_nom']);
            }
            
            if (isset($data['agence_nom'])) {
                $agence_id = self::getOrCreateAgence($data['agence_nom']);
                $data['agence_id'] = $agence_id;
                unset($data['agence_nom']);
            }
            
            foreach ($data as $key => $value) {
                if ($key !== 'id' && self::columnExists($key)) {
                    $fields[] = "$key = ?";
                    ${'p_' . $key} = $value;
                    $params[] = &${'p_' . $key};
                }
            }
            
            if (empty($fields)) return false;
            
            $p_id = intval($id);
            $params[] = &$p_id;
            
            $sql = "UPDATE " . self::$table . " SET " . implode(', ', $fields) . " WHERE id = ?";
            
            return parent::query($sql, $params);
            
        } catch (\Exception $e) {
            error_log("❌ Ramassage::update ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public static function delete($id) {
        try {
            $ramassage = self::find($id);
            if ($ramassage && !empty($ramassage['liste_path']) && file_exists($ramassage['liste_path'])) {
                @unlink($ramassage['liste_path']);
            }
            
            $sql = "DELETE FROM " . self::$table . " WHERE id = ?";
            $p1 = intval($id);
            return parent::query($sql, array(&$p1));
        } catch (\Exception $e) {
            error_log("❌ Ramassage::delete ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getByEntite($entiteNom) {
        try {
            $entite_id = self::getOrCreateEntite($entiteNom);
            
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    WHERE rf.entite_id = ?
                    ORDER BY rf.date_creation DESC";
            
            $p1 = $entite_id;
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getByEntite ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function getByAgence($agenceNom) {
        try {
            $agence_id = self::getOrCreateAgence($agenceNom);
            
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    WHERE rf.agence_id = ?
                    ORDER BY rf.date_creation DESC";
            
            $p1 = $agence_id;
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function getValides() {
        try {
            $dateAujourdhui = date('Y-m-d');
            
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    WHERE rf.date_fin >= ?
                    ORDER BY rf.date_debut ASC";
            
            $p1 = $dateAujourdhui;
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getValides ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function getExpires() {
        try {
            $dateAujourdhui = date('Y-m-d');
            
            $sql = "SELECT 
                    rf.*,
                    e.nom as entite_nom,
                    a.designation as agence_nom
                    FROM " . self::$table . " rf
                    LEFT JOIN entites e ON rf.entite_id = e.id
                    LEFT JOIN [dbo].[Tb_Agence] a ON rf.agence_id = a.idAgence
                    WHERE rf.date_fin < ?
                    ORDER BY rf.date_fin DESC";
            
            $p1 = $dateAujourdhui;
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as &$row) {
                $row['created_by_name'] = isset($row['created_by']) ? 'Utilisateur #' . $row['created_by'] : '';
                
                if (!isset($row['entite_nom']) || empty($row['entite_nom'])) {
                    $row['entite_nom'] = 'Non défini';
                }
                if (!isset($row['agence_nom']) || empty($row['agence_nom'])) {
                    $row['agence_nom'] = 'Non défini';
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getExpires ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function estValide($ramassageId) {
        try {
            $ramassage = self::find($ramassageId);
            
            if (!$ramassage || !isset($ramassage['date_fin'])) {
                return false;
            }
            
            $dateFinStr = is_string($ramassage['date_fin']) ? $ramassage['date_fin'] : '';
            
            if ($ramassage['date_fin'] instanceof \DateTime) {
                $dateFin = $ramassage['date_fin']->getTimestamp();
            } else {
                $dateFin = strtotime($dateFinStr);
            }
            
            if (!$dateFin) {
                return false;
            }
            
            $aujourdhui = time();
            
            return $dateFin >= $aujourdhui;
            
        } catch (\Exception $e) {
            error_log("❌ Ramassage::estValide ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getJoursRestants($ramassageId) {
        try {
            $ramassage = self::find($ramassageId);
            
            if (!$ramassage || !isset($ramassage['date_fin'])) {
                return 0;
            }
            
            $dateFinStr = is_string($ramassage['date_fin']) ? $ramassage['date_fin'] : '';
            
            if ($ramassage['date_fin'] instanceof \DateTime) {
                $dateFin = $ramassage['date_fin']->getTimestamp();
            } else {
                $dateFin = strtotime($dateFinStr);
            }
            
            if (!$dateFin) {
                return 0;
            }
            
            $aujourdhui = time();
            
            if ($dateFin < $aujourdhui) {
                return 0;
            }
            
            $diff = $dateFin - $aujourdhui;
            $jours = floor($diff / (60 * 60 * 24));
            
            return $jours;
            
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getJoursRestants ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function tableExists() {
        try {
            $sql = "SELECT COUNT(*) as table_exists 
                    FROM INFORMATION_SCHEMA.TABLES 
                    WHERE TABLE_SCHEMA = 'dbo' 
                    AND TABLE_NAME = '" . self::$table . "'";
            
            $result = parent::querySelect($sql);
            $data = self::resourceToArray($result);
            if (!empty($data) && isset($data[0]['table_exists'])) {
                return (int)$data[0]['table_exists'] > 0;
            }
            return false;
        } catch (\Exception $e) {
            error_log("❌ Ramassage::tableExists ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getListeAgences() {
        try {
            $sql = "SELECT [idAgence], [designation] FROM [dbo].[Tb_Agence] ORDER BY [designation]";
            $result = parent::querySelect($sql);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Ramassage::getListeAgences ERROR: " . $e->getMessage());
            return array();
        }
    }
}