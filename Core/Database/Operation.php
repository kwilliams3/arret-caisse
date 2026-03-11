<?php
namespace Core\Database;

use Core\Model\Table;

class Operation extends Table
{
    protected static $table = 'Cheques';
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
            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Cheques'";
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
    
    /**
     * Vérifie si un numéro de chèque ou référence de virement existe déjà
     * @param string $numero Le numéro à vérifier
     * @param string $type Le type d'opération ('cheque' ou 'virement')
     * @param int $agenceId L'ID de l'agence
     * @param int $excludeId ID à exclure de la vérification (pour modification)
     * @return bool True si existe déjà, false sinon
     */
    public static function existsByNumero($numero, $type, $agenceId, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM Cheques 
                    WHERE numero_cheque = ? 
                    AND type_operation = ? 
                    AND agence_id = ?";
            $params = [&$numero, &$type, &$agenceId];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = &$excludeId;
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'] > 0;
            }
            return false;
        } catch (\Exception $e) {
            error_log("❌ Operation::existsByNumero ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère toutes les opérations d'une agence spécifique
     */
    public static function getByAgence($agenceId, $type = null) {
        try {
            $sql = "SELECT * FROM Cheques WHERE agence_id = ?";
            $params = [&$agenceId];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::getByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Récupère les opérations paginées d'une agence
     */
    public static function getPaginatedByAgence($agenceId, $limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM Cheques WHERE agence_id = ?";
            $params = [&$agenceId];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC 
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            
            $p_offset = intval($offset);
            $p_limit = intval($limit);
            $params[] = &$p_offset;
            $params[] = &$p_limit;
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::getPaginatedByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte le nombre d'opérations d'une agence
     */
    public static function countByAgence($agenceId, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM Cheques WHERE agence_id = ?";
            $params = [&$agenceId];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("❌ Operation::countByAgence ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Recherche dans les opérations d'une agence spécifique
     */
    public static function searchByAgence($searchTerm, $agenceId, $limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM Cheques 
                    WHERE agence_id = ? 
                    AND (nom_client LIKE ? 
                    OR numero_cheque LIKE ? 
                    OR banque LIKE ?)";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = [&$agenceId, &$searchPattern, &$searchPattern, &$searchPattern];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC 
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            
            $p_offset = intval($offset);
            $p_limit = intval($limit);
            $params[] = &$p_offset;
            $params[] = &$p_limit;
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::searchByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte les résultats de recherche dans une agence
     */
    public static function searchCountByAgence($searchTerm, $agenceId, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM Cheques 
                    WHERE agence_id = ? 
                    AND (nom_client LIKE ? 
                    OR numero_cheque LIKE ? 
                    OR banque LIKE ?)";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = [&$agenceId, &$searchPattern, &$searchPattern, &$searchPattern];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("❌ Operation::searchCountByAgence ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère les opérations archivées (statut = 'confirmé' ou 'annulé')
     */
    public static function getArchives($agenceId = null, $type = null, $limit = 1000, $offset = 0) {
        try {
            $sql = "SELECT * FROM Cheques WHERE statut IN ('confirmé', 'annulé')";
            $params = [];
            
            if ($agenceId) {
                $sql .= " AND agence_id = ?";
                $params[] = &$agenceId;
            }
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC";
            
            if ($limit) {
                $sql .= " OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
                $p_offset = intval($offset);
                $p_limit = intval($limit);
                $params[] = &$p_offset;
                $params[] = &$p_limit;
            }
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::getArchives ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte le nombre d'opérations archivées
     */
    public static function countArchives($agenceId = null, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM Cheques WHERE statut IN ('confirmé', 'annulé')";
            $params = [];
            
            if ($agenceId) {
                $sql .= " AND agence_id = ?";
                $params[] = &$agenceId;
            }
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("❌ Operation::countArchives ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function getAll($type = null) {
        try {
            $sql = "SELECT * FROM Cheques";
            $params = [];
            
            if ($type) {
                $sql .= " WHERE type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::getAll ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function getPaginated($limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM Cheques";
            $params = [];
            
            if ($type) {
                $sql .= " WHERE type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC 
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            
            $p_offset = intval($offset);
            $p_limit = intval($limit);
            $params[] = &$p_offset;
            $params[] = &$p_limit;
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::getPaginated ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function search($searchTerm, $limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM Cheques 
                    WHERE nom_client LIKE ? 
                    OR numero_cheque LIKE ? 
                    OR banque LIKE ?";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = [&$searchPattern, &$searchPattern, &$searchPattern];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC 
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            
            $p_offset = intval($offset);
            $p_limit = intval($limit);
            $params[] = &$p_offset;
            $params[] = &$p_limit;
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ Operation::search ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    public static function searchCount($searchTerm, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM Cheques 
                    WHERE nom_client LIKE ? 
                    OR numero_cheque LIKE ? 
                    OR banque LIKE ?";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = [&$searchPattern, &$searchPattern, &$searchPattern];
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("❌ Operation::searchCount ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function count($type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM Cheques";
            $params = [];
            
            if ($type) {
                $sql .= " WHERE type_operation = ?";
                $params[] = &$type;
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            if (!empty($data) && isset($data[0]['total'])) {
                return (int)$data[0]['total'];
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    public static function add($data, $scanPath = null) {
        try {
            $date_reception = $data['date_reception'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_reception)) {
                $date_reception = date('Y-m-d', strtotime($date_reception));
            }
            
            $date_entree = $data['date_entree'];
            
            $hasEtatValidation = self::columnExists('etat_validation');
            $hasEtatConfirmation = self::columnExists('etat_confirmation');
            $hasUpdatedAt = self::columnExists('updated_at');
            $hasUpdatedBy = self::columnExists('updated_by');
            
            $fields = [];
            $placeholders = [];
            $params = [];
            
            $fields[] = 'type_operation';
            $placeholders[] = '?';
            $p_type = isset($data['type_operation']) ? $data['type_operation'] : 'cheque';
            $params[] = &$p_type;
            
            $fields[] = 'nom_client';
            $placeholders[] = '?';
            $p1 = trim($data['nom_client']);
            $params[] = &$p1;
            
            $fields[] = 'numero_cheque';
            $placeholders[] = '?';
            $p2 = trim($data['numero_cheque']);
            $params[] = &$p2;
            
            $fields[] = 'montant';
            $placeholders[] = '?';
            $p3 = floatval(str_replace(',', '.', $data['montant']));
            $params[] = &$p3;
            
            $fields[] = 'banque';
            $placeholders[] = '?';
            $p4 = trim($data['banque']);
            $params[] = &$p4;
            
            $fields[] = 'date_reception';
            $placeholders[] = '?';
            $p5 = $date_reception;
            $params[] = &$p5;
            
            $fields[] = 'date_entree';
            $placeholders[] = '?';
            $p6 = $date_entree;
            $params[] = &$p6;
            
            $fields[] = 'observations';
            $placeholders[] = '?';
            $p7 = isset($data['observations']) ? trim($data['observations']) : '';
            $params[] = &$p7;
            
            $fields[] = 'agence_id';
            $placeholders[] = '?';
            $p8 = isset($data['agence_id']) ? intval($data['agence_id']) : null;
            $params[] = &$p8;
            
            $fields[] = 'created_by';
            $placeholders[] = '?';
            $p9 = isset($data['created_by']) ? intval($data['created_by']) : null;
            $params[] = &$p9;
            
            $fields[] = 'scan_path';
            $placeholders[] = '?';
            $p10 = $scanPath;
            $params[] = &$p10;
            
            $fields[] = 'statut';
            $placeholders[] = "'en cours'";
            
            if ($hasEtatConfirmation) {
                $fields[] = 'etat_confirmation';
                $placeholders[] = "'Non'";
            }
            
            if ($hasEtatValidation) {
                $fields[] = 'etat_validation';
                $placeholders[] = "'Non'";
            }
            
            if ($hasUpdatedAt) {
                $fields[] = 'updated_at';
                $placeholders[] = 'GETDATE()';
            }
            
            if ($hasUpdatedBy && isset($data['created_by'])) {
                $fields[] = 'updated_by';
                $placeholders[] = '?';
                $params[] = &$p9;
            }
            
            $sql = "INSERT INTO Cheques (" . implode(', ', $fields) . ") 
                    OUTPUT INSERTED.id
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $result = parent::querySelect($sql, $params);
            
            $insertedId = null;
            
            if (is_resource($result)) {
                if (sqlsrv_fetch($result)) {
                    $insertedId = sqlsrv_get_field($result, 0);
                    sqlsrv_free_stmt($result);
                }
            } else {
                $lastIdSql = "SELECT MAX(id) as last_id FROM Cheques";
                $lastResult = parent::querySelect($lastIdSql);
                if ($lastResult && sqlsrv_fetch($lastResult)) {
                    $insertedId = sqlsrv_get_field($lastResult, 0);
                    sqlsrv_free_stmt($lastResult);
                }
            }
            
            if ($insertedId) {
                return (int)$insertedId;
            }
            
            return false;
            
        } catch (\Exception $e) {
            error_log("❌ Erreur add(): " . $e->getMessage());
            return false;
        }
    }
    
    public static function find($id) {
        try {
            $sql = "SELECT * FROM Cheques WHERE id = ?";
            $p1 = intval($id);
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            return !empty($data) ? $data[0] : null;
        } catch (\Exception $e) {
            error_log("Erreur find($id): " . $e->getMessage());
            return null;
        }
    }
    
    public static function findByAgence($agence_id, $type = null) {
        return self::getByAgence($agence_id, $type);
    }
    
    public static function getByStatut($statut, $type = null) {
        try {
            $sql = "SELECT * FROM Cheques WHERE statut = ?";
            $params = array(&$statut);
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            return array();
        }
    }
    
    public static function updateStatut($id, $statut, $observation = null, $userId = null) {
        try {
            $columns = self::getTableColumns();
            
            $sql = "UPDATE Cheques SET statut = ?";
            $params = array(&$statut);
            
            if (in_array('date_validation', $columns)) {
                $sql .= ", date_validation = GETDATE()";
            } elseif (in_array('date_confirmation', $columns)) {
                $sql .= ", date_confirmation = GETDATE()";
            }
            
            if (in_array('validated_by', $columns) && $userId) {
                $sql .= ", validated_by = ?";
                $params[] = &$userId;
            }
            
            if (in_array('observation_validation', $columns) && $observation) {
                $sql .= ", observation_validation = ?";
                $params[] = &$observation;
            } elseif (in_array('observations', $columns) && $observation) {
                $currentObs = self::getCurrentObservations($id);
                $newObs = $currentObs . "\n\n[Changement statut: " . date('d/m/Y H:i') . "] " . $observation;
                $sql .= ", observations = ?";
                $params[] = &$newObs;
            }
            
            if (in_array('updated_at', $columns)) {
                $sql .= ", updated_at = GETDATE()";
            }
            
            if (in_array('updated_by', $columns) && $userId) {
                $sql .= ", updated_by = ?";
                $params[] = &$userId;
            }
            
            $sql .= " WHERE id = ?";
            $p_id = intval($id);
            $params[] = &$p_id;
            
            $result = parent::query($sql, $params);
            return $result;
        } catch (\Exception $e) {
            error_log("❌ Erreur updateStatut($id, $statut): " . $e->getMessage());
            return false;
        }
    }
    
    public static function updateEtatConfirmation($id, $etat, $observation = null, $userId = null) {
        try {
            $hasColumn = self::columnExists('etat_confirmation');
            
            if (!$hasColumn) {
                return false;
            }
            
            $sql = "UPDATE Cheques SET etat_confirmation = ?";
            $params = array(&$etat);
            
            if (self::columnExists('date_confirmation')) {
                $sql .= ", date_confirmation = GETDATE()";
            }
            
            if ($observation) {
                if (self::columnExists('observation_confirmation')) {
                    $sql .= ", observation_confirmation = ?";
                    $params[] = &$observation;
                } elseif (self::columnExists('observations')) {
                    $currentObs = self::getCurrentObservations($id);
                    $newObs = $currentObs . "\n\n[Confirmation: " . date('d/m/Y H:i') . "] " . $observation;
                    $sql .= ", observations = ?";
                    $params[] = &$newObs;
                }
            }
            
            if (self::columnExists('confirmed_by') && $userId) {
                $sql .= ", confirmed_by = ?";
                $params[] = &$userId;
            }
            
            if (self::columnExists('updated_at')) {
                $sql .= ", updated_at = GETDATE()";
            }
            
            if (self::columnExists('updated_by') && $userId) {
                $sql .= ", updated_by = ?";
                $params[] = &$userId;
            }
            
            $sql .= " WHERE id = ?";
            $p_id = intval($id);
            $params[] = &$p_id;
            
            $result = parent::query($sql, $params);
            
            if ($result === false) {
                $errors = sqlsrv_errors();
                if ($errors) {
                    foreach ($errors as $error) {
                        error_log("SQL Error updateEtatConfirmation: " . $error['message']);
                    }
                }
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log("❌ Erreur updateEtatConfirmation: " . $e->getMessage());
            return false;
        }
    }
    
    public static function updateEtatValidation($id, $etat, $observation = null, $userId = null) {
        try {
            $hasColumn = self::columnExists('etat_validation');
            
            if (!$hasColumn) {
                return false;
            }
            
            $sql = "UPDATE Cheques SET etat_validation = ?";
            $params = array(&$etat);
            
            if (self::columnExists('date_validation')) {
                $sql .= ", date_validation = GETDATE()";
            }
            
            if ($observation) {
                if (self::columnExists('observation_validation')) {
                    $sql .= ", observation_validation = ?";
                    $params[] = &$observation;
                } elseif (self::columnExists('observations')) {
                    $currentObs = self::getCurrentObservations($id);
                    $newObs = $currentObs . "\n\n[Validation: " . date('d/m/Y H:i') . "] " . $observation;
                    $sql .= ", observations = ?";
                    $params[] = &$newObs;
                }
            }
            
            if (self::columnExists('validated_by') && $userId) {
                $sql .= ", validated_by = ?";
                $params[] = &$userId;
            }
            
            if (self::columnExists('updated_at')) {
                $sql .= ", updated_at = GETDATE()";
            }
            
            if (self::columnExists('updated_by') && $userId) {
                $sql .= ", updated_by = ?";
                $params[] = &$userId;
            }
            
            $sql .= " WHERE id = ?";
            $p_id = intval($id);
            $params[] = &$p_id;
            
            $result = parent::query($sql, $params);
            
            if ($result === false) {
                $errors = sqlsrv_errors();
                if ($errors) {
                    foreach ($errors as $error) {
                        error_log("SQL Error updateEtatValidation: " . $error['message']);
                    }
                }
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log("❌ Erreur updateEtatValidation: " . $e->getMessage());
            return false;
        }
    }
    
    private static function getCurrentObservations($id) {
        try {
            $sql = "SELECT observations FROM Cheques WHERE id = ?";
            $p1 = intval($id);
            $result = parent::querySelect($sql, array(&$p1));
            $data = self::resourceToArray($result);
            if (!empty($data) && isset($data[0]['observations'])) {
                return $data[0]['observations'];
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
    
    public static function confirm($id, $etat, $commentaire = '') {
        try {
            $statut = ($etat == 'approvisionne') ? 'confirmé' : 'annulé';
            
            if (self::columnExists('date_confirmation')) {
                $sql = "UPDATE Cheques SET statut = ?, date_confirmation = GETDATE() WHERE id = ?";
            } else {
                $sql = "UPDATE Cheques SET statut = ? WHERE id = ?";
            }
            
            $p1 = $statut;
            $p2 = intval($id);
            return parent::query($sql, array(&$p1, &$p2));
        } catch (\Exception $e) {
            error_log("Erreur confirm($id): " . $e->getMessage());
            return false;
        }
    }
    
    public static function cancel($id, $motif, $commentaire = '') {
        try {
            if (self::columnExists('date_confirmation')) {
                $sql = "UPDATE Cheques SET statut = 'annulé', date_confirmation = GETDATE() WHERE id = ?";
            } else {
                $sql = "UPDATE Cheques SET statut = 'annulé' WHERE id = ?";
            }
            
            $p1 = intval($id);
            return parent::query($sql, array(&$p1));
        } catch (\Exception $e) {
            error_log("Erreur cancel($id): " . $e->getMessage());
            return false;
        }
    }
    
    public static function update($id, $data) {
        try {
            if (empty($id) || empty($data)) return false;
            
            $fields = [];
            $params = [];
            
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
            
            $sql = "UPDATE Cheques SET " . implode(', ', $fields) . " WHERE id = ?";
            
            return parent::query($sql, $params);
        } catch (\Exception $e) {
            error_log("❌ Erreur update($id): " . $e->getMessage());
            return false;
        }
    }
    
    public static function delete($id) {
        try {
            $sql = "DELETE FROM Cheques WHERE id = ?";
            $p1 = intval($id);
            return parent::query($sql, array(&$p1));
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public static function getAllVirements() {
        return self::getAll('virement');
    }
    
    public static function getVirementsByAgence($agence_id) {
        return self::findByAgence($agence_id, 'virement');
    }
    
    public static function searchVirements($searchTerm, $limit = 10, $offset = 0) {
        return self::search($searchTerm, $limit, $offset, 'virement');
    }
}
?>