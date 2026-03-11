<?php
namespace Core\Database;

use Core\Model\Table;

class EffetCommerce extends Table
{
    protected static $table = 'EffetsCommerce';
    private static $columnCache = null;
    
    private static function resourceToArray($resource) {
        if (!is_resource($resource) && !$resource instanceof \PDOStatement) {
            return $resource;
        }
        
        $results = array();
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
            $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'EffetsCommerce'";
            $result = parent::querySelect($sql);
            $columns = array();
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
     * Génère automatiquement le numéro d'effet (format plus court)
     * Format: BO-YYMMDD-XXX ou LC-YYMMDD-XXX
     * BO = Billet à Ordre, LC = Lettre de Change
     * YYMMDD = date, XXX = incrément du jour
     */
    private static function generateNumero($type_operation) {
        $prefix = ($type_operation === 'billet_ordre') ? 'BO' : 'LC';
        $date = date('ymd'); // 250309 pour 2025-03-09
        
        $sql = "SELECT COUNT(*) as total FROM EffetsCommerce WHERE numero LIKE ?";
        $searchPattern = $prefix . '-' . $date . '-%';
        $params = array(&$searchPattern);
        
        $result = parent::querySelect($sql, $params);
        $data = self::resourceToArray($result);
        
        $count = !empty($data) ? (int)$data[0]['total'] + 1 : 1;
        $suffix = str_pad($count, 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $date . '-' . $suffix;
    }
    
    /**
     * Récupère toutes les opérations (pour admin/compta)
     */
    public static function getAll($type = null) {
        try {
            $sql = "SELECT * FROM EffetsCommerce";
            $params = array();
            
            if ($type) {
                $sql .= " WHERE type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ EffetCommerce::getAll ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Récupère les opérations paginées (pour admin/compta)
     */
    public static function getPaginated($limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM EffetsCommerce";
            $params = array();
            
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
            error_log("❌ EffetCommerce::getPaginated ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte le nombre total d'opérations (pour admin/compta)
     */
    public static function count($type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM EffetsCommerce";
            $params = array();
            
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
            error_log("❌ EffetCommerce::count ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Recherche dans toutes les opérations (pour admin/compta)
     */
    public static function search($searchTerm, $limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM EffetsCommerce 
                    WHERE nom_tireur LIKE ? 
                    OR nom_tire LIKE ? 
                    OR numero LIKE ? 
                    OR banque LIKE ?";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = array(&$searchPattern, &$searchPattern, &$searchPattern, &$searchPattern);
            
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
            error_log("❌ EffetCommerce::search ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte les résultats de recherche (pour admin/compta)
     */
    public static function searchCount($searchTerm, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM EffetsCommerce 
                    WHERE nom_tireur LIKE ? 
                    OR nom_tire LIKE ? 
                    OR numero LIKE ? 
                    OR banque LIKE ?";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = array(&$searchPattern, &$searchPattern, &$searchPattern, &$searchPattern);
            
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
            error_log("❌ EffetCommerce::searchCount ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère toutes les opérations d'une agence
     */
    public static function getByAgence($agenceId, $type = null) {
        try {
            $sql = "SELECT * FROM EffetsCommerce WHERE agence_id = ?";
            $params = array(&$agenceId);
            
            if ($type) {
                $sql .= " AND type_operation = ?";
                $params[] = &$type;
            }
            
            $sql .= " ORDER BY date_entree DESC";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ EffetCommerce::getByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Récupère les opérations paginées d'une agence
     */
    public static function getPaginatedByAgence($agenceId, $limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM EffetsCommerce WHERE agence_id = ?";
            $params = array(&$agenceId);
            
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
            error_log("❌ EffetCommerce::getPaginatedByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte le nombre d'opérations d'une agence
     */
    public static function countByAgence($agenceId, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM EffetsCommerce WHERE agence_id = ?";
            $params = array(&$agenceId);
            
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
            error_log("❌ EffetCommerce::countByAgence ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Recherche dans les opérations d'une agence
     */
    public static function searchByAgence($searchTerm, $agenceId, $limit = 10, $offset = 0, $type = null) {
        try {
            $sql = "SELECT * FROM EffetsCommerce 
                    WHERE agence_id = ? 
                    AND (nom_tireur LIKE ? 
                    OR nom_tire LIKE ? 
                    OR numero LIKE ? 
                    OR banque LIKE ?)";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = array(&$agenceId, &$searchPattern, &$searchPattern, &$searchPattern, &$searchPattern);
            
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
            error_log("❌ EffetCommerce::searchByAgence ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Compte les résultats de recherche dans une agence
     */
    public static function searchCountByAgence($searchTerm, $agenceId, $type = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM EffetsCommerce 
                    WHERE agence_id = ? 
                    AND (nom_tireur LIKE ? 
                    OR nom_tire LIKE ? 
                    OR numero LIKE ? 
                    OR banque LIKE ?)";
            
            $searchPattern = '%' . $searchTerm . '%';
            $params = array(&$agenceId, &$searchPattern, &$searchPattern, &$searchPattern, &$searchPattern);
            
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
            error_log("❌ EffetCommerce::searchCountByAgence ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Vérifie si un numéro d'effet existe déjà
     */
    public static function existsByNumero($numero, $agenceId, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM EffetsCommerce 
                    WHERE numero = ? AND agence_id = ?";
            $params = array(&$numero, &$agenceId);
            
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
            error_log("❌ EffetCommerce::existsByNumero ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute un nouvel effet de commerce
     */
    public static function add($data, $scanPath = null) {
        try {
            // Génération automatique du numéro avec le type d'opération
            $type_operation = isset($data['type_operation']) ? $data['type_operation'] : 'billet_ordre';
            $numero = self::generateNumero($type_operation);
            
            $date_emission = $data['date_emission'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_emission)) {
                $date_emission = date('Y-m-d', strtotime($date_emission));
            }
            
            $date_entree = date('Y-m-d H:i:s');
            
            // Gestion de l'échéance (date ou nombre de jours)
            $echeance = null;
            $nb_jours = null;
            if (isset($data['echeance_type'])) {
                if ($data['echeance_type'] === 'date' && !empty($data['echeance_date'])) {
                    $echeance = $data['echeance_date'];
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $echeance)) {
                        $echeance = date('Y-m-d', strtotime($echeance));
                    }
                } elseif ($data['echeance_type'] === 'jours' && !empty($data['echeance_jours'])) {
                    $nb_jours = intval($data['echeance_jours']);
                    $echeance = date('Y-m-d', strtotime('+' . $nb_jours . ' days'));
                }
            }
            
            $fields = array();
            $placeholders = array();
            $params = array();
            
            // Champs obligatoires
            $fields[] = 'type_operation';
            $placeholders[] = '?';
            $p_type = $type_operation;
            $params[] = &$p_type;
            
            $fields[] = 'numero';
            $placeholders[] = '?';
            $params[] = &$numero;
            
            $fields[] = 'nom_tireur';
            $placeholders[] = '?';
            $p_tireur = trim($data['nom_tireur']);
            $params[] = &$p_tireur;
            
            $fields[] = 'nom_tire';
            $placeholders[] = '?';
            $p_tire = trim($data['nom_tire']);
            $params[] = &$p_tire;
            
            $fields[] = 'date_emission';
            $placeholders[] = '?';
            $p_emission = $date_emission;
            $params[] = &$p_emission;
            
            $fields[] = 'echeance';
            $placeholders[] = '?';
            $params[] = &$echeance;
            
            $fields[] = 'nb_jours';
            $placeholders[] = '?';
            $p_nb_jours = $nb_jours;
            $params[] = &$p_nb_jours;
            
            $fields[] = 'banque';
            $placeholders[] = '?';
            $p_banque = trim($data['banque']);
            $params[] = &$p_banque;
            
            $fields[] = 'agence_id';
            $placeholders[] = '?';
            $p_agence = isset($data['agence_id']) ? intval($data['agence_id']) : null;
            $params[] = &$p_agence;
            
            $fields[] = 'created_by';
            $placeholders[] = '?';
            $p_created = isset($data['created_by']) ? intval($data['created_by']) : null;
            $params[] = &$p_created;
            
            $fields[] = 'date_entree';
            $placeholders[] = '?';
            $params[] = &$date_entree;
            
            $fields[] = 'scan_path';
            $placeholders[] = '?';
            $params[] = &$scanPath;
            
            // Champs optionnels
            $fields[] = 'observations';
            $placeholders[] = '?';
            $p_obs = isset($data['observations']) ? trim($data['observations']) : '';
            $params[] = &$p_obs;
            
            // Statuts par défaut
            $fields[] = 'statut';
            $placeholders[] = "'en cours'";
            
            $fields[] = 'etat_confirmation';
            $placeholders[] = "'Non'";
            
            $fields[] = 'etat_validation';
            $placeholders[] = "'Non'";
            
            $sql = "INSERT INTO EffetsCommerce (" . implode(', ', $fields) . ") 
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
            
            return $insertedId ? (int)$insertedId : false;
            
        } catch (\Exception $e) {
            error_log("❌ Erreur EffetCommerce::add(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Trouve une opération par son ID
     */
    public static function find($id) {
        try {
            $sql = "SELECT * FROM EffetsCommerce WHERE id = ?";
            $p1 = intval($id);
            $params = array(&$p1);
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            return !empty($data) ? $data[0] : null;
        } catch (\Exception $e) {
            error_log("❌ EffetCommerce::find($id) ERROR: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Met à jour une opération
     */
    public static function update($id, $data) {
        try {
            if (empty($id) || empty($data)) return false;
            
            $fields = array();
            $params = array();
            
            // Traitement spécial pour l'échéance
            if (isset($data['echeance_type'])) {
                if ($data['echeance_type'] === 'date' && !empty($data['echeance_date'])) {
                    $echeance = $data['echeance_date'];
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $echeance)) {
                        $echeance = date('Y-m-d', strtotime($echeance));
                    }
                    $data['echeance'] = $echeance;
                    $data['nb_jours'] = null;
                } elseif ($data['echeance_type'] === 'jours' && !empty($data['echeance_jours'])) {
                    $nb_jours = intval($data['echeance_jours']);
                    $data['nb_jours'] = $nb_jours;
                    $data['echeance'] = date('Y-m-d', strtotime('+' . $nb_jours . ' days'));
                }
            }
            
            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'echeance_type' && $key !== 'echeance_date' && $key !== 'echeance_jours' && self::columnExists($key)) {
                    $fields[] = "$key = ?";
                    ${'p_' . $key} = $value;
                    $params[] = &${'p_' . $key};
                }
            }
            
            if (empty($fields)) return false;
            
            if (self::columnExists('updated_at')) {
                $fields[] = "updated_at = GETDATE()";
            }
            
            $p_id = intval($id);
            $params[] = &$p_id;
            
            $sql = "UPDATE EffetsCommerce SET " . implode(', ', $fields) . " WHERE id = ?";
            
            return parent::query($sql, $params);
            
        } catch (\Exception $e) {
            error_log("❌ Erreur EffetCommerce::update($id): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour le statut
     */
    public static function updateStatut($id, $statut, $observation = null, $userId = null) {
        try {
            $sql = "UPDATE EffetsCommerce SET statut = ?";
            $params = array(&$statut);
            
            if (self::columnExists('date_validation')) {
                $sql .= ", date_validation = GETDATE()";
            }
            
            if (self::columnExists('validated_by') && $userId) {
                $sql .= ", validated_by = ?";
                $params[] = &$userId;
            }
            
            if (self::columnExists('observation_validation') && $observation) {
                $sql .= ", observation_validation = ?";
                $params[] = &$observation;
            } elseif ($observation) {
                $currentObs = self::getCurrentObservations($id);
                $newObs = $currentObs . "\n\n[Changement statut: " . date('d/m/Y H:i') . "] " . $observation;
                if (self::columnExists('observations')) {
                    $sql .= ", observations = ?";
                    $params[] = &$newObs;
                }
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
            
            return parent::query($sql, $params);
            
        } catch (\Exception $e) {
            error_log("❌ Erreur EffetCommerce::updateStatut($id): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour l'état de confirmation
     */
    public static function updateEtatConfirmation($id, $etat, $observation = null, $userId = null) {
        try {
            $sql = "UPDATE EffetsCommerce SET etat_confirmation = ?";
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
            
            return parent::query($sql, $params);
            
        } catch (\Exception $e) {
            error_log("❌ Erreur EffetCommerce::updateEtatConfirmation($id): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour l'état de validation
     */
    public static function updateEtatValidation($id, $etat, $observation = null, $userId = null) {
        try {
            $sql = "UPDATE EffetsCommerce SET etat_validation = ?";
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
            
            return parent::query($sql, $params);
            
        } catch (\Exception $e) {
            error_log("❌ Erreur EffetCommerce::updateEtatValidation($id): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les observations actuelles
     */
    private static function getCurrentObservations($id) {
        try {
            $sql = "SELECT observations FROM EffetsCommerce WHERE id = ?";
            $p1 = intval($id);
            $params = array(&$p1);
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            return !empty($data) && isset($data[0]['observations']) ? $data[0]['observations'] : '';
        } catch (\Exception $e) {
            return '';
        }
    }
    
    /**
     * Supprime une opération
     */
    public static function delete($id) {
        try {
            $sql = "DELETE FROM EffetsCommerce WHERE id = ?";
            $p1 = intval($id);
            $params = array(&$p1);
            return parent::query($sql, $params);
        } catch (\Exception $e) {
            error_log("❌ EffetCommerce::delete($id) ERROR: " . $e->getMessage());
            return false;
        }
    }
}