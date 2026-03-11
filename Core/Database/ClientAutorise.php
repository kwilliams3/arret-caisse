<?php
namespace Core\Database;

use Core\Model\Table;

class ClientAutorise extends Table
{
    protected static $table = 'clients_autorises';
    
    /**
     * Récupère tous les clients autorisés
     */
    public static function getAll($site = null) {
        try {
            $sql = "SELECT * FROM clients_autorises";
            $params = array();
            
            if ($site) {
                $sql .= " WHERE site_demandeur LIKE ?";
                $params[] = &$site;
            }
            
            $sql .= " ORDER BY site_demandeur, nom_client";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::getAll ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Récupère les clients autorisés par site
     */
    public static function getBySite($site) {
        try {
            $sql = "SELECT * FROM clients_autorises WHERE site_demandeur LIKE ? ORDER BY nom_client";
            $params = array("%$site%");
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::getBySite ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Récupère un client par son ID
     */
    public static function find($id) {
        try {
            $sql = "SELECT * FROM clients_autorises WHERE id = ?";
            $params = array(intval($id));
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            return !empty($data) ? $data[0] : null;
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::find ERROR: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Recherche des clients autorisés
     */
    public static function search($searchTerm, $site = null) {
        try {
            $sql = "SELECT * FROM clients_autorises 
                    WHERE nom_client LIKE ? 
                    OR site_demandeur LIKE ? 
                    OR contact LIKE ?";
            $params = array("%$searchTerm%", "%$searchTerm%", "%$searchTerm%");
            
            if ($site) {
                $sql .= " AND site_demandeur LIKE ?";
                $params[] = "%$site%";
            }
            
            $sql .= " ORDER BY site_demandeur, nom_client";
            
            $result = parent::querySelect($sql, $params);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::search ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Ajoute un nouveau client autorisé
     */
    public static function add($data) {
        try {
            $sql = "INSERT INTO clients_autorises (site_demandeur, nom_client, contact, plafond, parrainage) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $params = array(
                $data['site_demandeur'],
                $data['nom_client'],
                isset($data['contact']) ? $data['contact'] : '',
                isset($data['plafond']) ? $data['plafond'] : 0,
                isset($data['parrainage']) ? $data['parrainage'] : ''
            );
            
            $result = parent::query($sql, $params);
            
            if ($result) {
                // Récupérer l'ID inséré
                $idSql = "SELECT MAX(id) as last_id FROM clients_autorises";
                $idResult = parent::querySelect($idSql);
                $idData = self::resourceToArray($idResult);
                return !empty($idData) ? $idData[0]['last_id'] : null;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::add ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour un client autorisé
     */
    public static function update($id, $data) {
        try {
            $fields = array();
            $params = array();
            
            if (isset($data['site_demandeur'])) {
                $fields[] = "site_demandeur = ?";
                $params[] = $data['site_demandeur'];
            }
            if (isset($data['nom_client'])) {
                $fields[] = "nom_client = ?";
                $params[] = $data['nom_client'];
            }
            if (isset($data['contact'])) {
                $fields[] = "contact = ?";
                $params[] = $data['contact'];
            }
            if (isset($data['plafond'])) {
                $fields[] = "plafond = ?";
                $params[] = $data['plafond'];
            }
            if (isset($data['parrainage'])) {
                $fields[] = "parrainage = ?";
                $params[] = $data['parrainage'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $params[] = intval($id);
            $sql = "UPDATE clients_autorises SET " . implode(', ', $fields) . " WHERE id = ?";
            
            return parent::query($sql, $params);
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::update ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime un client autorisé
     */
    public static function delete($id) {
        try {
            $sql = "DELETE FROM clients_autorises WHERE id = ?";
            $params = array(intval($id));
            return parent::query($sql, $params);
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::delete ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Compte le nombre de clients autorisés
     */
    public static function count($site = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM clients_autorises";
            $params = array();
            
            if ($site) {
                $sql .= " WHERE site_demandeur LIKE ?";
                $params[] = "%$site%";
            }
            
            $result = parent::querySelect($sql, $params);
            $data = self::resourceToArray($result);
            
            return !empty($data) ? (int)$data[0]['total'] : 0;
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::count ERROR: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère tous les sites distincts
     */
    public static function getSites() {
        try {
            $sql = "SELECT DISTINCT site_demandeur FROM clients_autorises WHERE site_demandeur IS NOT NULL ORDER BY site_demandeur";
            $result = parent::querySelect($sql);
            return self::resourceToArray($result);
        } catch (\Exception $e) {
            error_log("❌ ClientAutorise::getSites ERROR: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Convertit une ressource SQL en tableau
     */
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
}