<?php
namespace Core\Database;

// Inclure manuellement Operation.php
require_once __DIR__ . '/Operation.php';

/**
 * Classe Cheque - Alias pour Operation pour la compatibilité
 * Cette classe permet de maintenir la compatibilité avec le code existant
 * tout en utilisant la nouvelle classe Operation qui gère chèques et virements
 */
class Cheque extends Operation
{
    // Toutes les méthodes sont héritées de Operation
    // Cette classe existe juste pour éviter de modifier tous les fichiers existants
    
    /**
     * Redirige vers Operation::getAll() 
     * Si $type est null, on renvoie tous les types (chèques ET virements)
     */
    public static function getAll($type = null) {
        // Si $type est null, on passe null à parent::getAll() pour avoir tous les types
        return parent::getAll($type);
    }
    
    /**
     * Redirige vers Operation::search()
     * Si $type est null, on renvoie tous les types (chèques ET virements)
     */
    public static function search($searchTerm, $limit = 10, $offset = 0, $type = null) {
        // Si $type est null, on passe null à parent::search() pour chercher dans tous les types
        return parent::search($searchTerm, $limit, $offset, $type);
    }
    
    /**
     * Redirige vers Operation::count()
     * Si $type est null, on renvoie le compte de tous les types (chèques ET virements)
     */
    public static function count($type = null) {
        // Si $type est null, on passe null à parent::count() pour compter tous les types
        return parent::count($type);
    }
    
    /**
     * Redirige vers Operation::searchCount()
     * Si $type est null, on renvoie le compte de recherche dans tous les types
     */
    public static function searchCount($searchTerm, $type = null) {
        // Si $type est null, on passe null à parent::searchCount() pour chercher dans tous les types
        return parent::searchCount($searchTerm, $type);
    }
    
    /**
     * Redirige vers Operation::getPaginated()
     * Si $type est null, on renvoie tous les types paginés (chèques ET virements)
     */
    public static function getPaginated($limit = 10, $offset = 0, $type = null) {
        // Si $type est null, on passe null à parent::getPaginated() pour avoir tous les types
        return parent::getPaginated($limit, $offset, $type);
    }
    
    /**
     * Redirige vers Operation::findByAgence()
     * Si $type est null, on renvoie tous les types pour l'agence (chèques ET virements)
     */
    public static function findByAgence($agence_id, $type = null) {
        // Si $type est null, on passe null à parent::findByAgence() pour avoir tous les types
        return parent::findByAgence($agence_id, $type);
    }
    
    /**
     * Redirige vers Operation::getByStatut()
     * Si $type est null, on renvoie tous les types avec ce statut (chèques ET virements)
     */
    public static function getByStatut($statut, $type = null) {
        // Si $type est null, on passe null à parent::getByStatut() pour avoir tous les types
        return parent::getByStatut($statut, $type);
    }
    
    /**
     * Redirige vers Operation::add() avec type=cheque par défaut
     * Pour la compatibilité avec les anciens appels qui ne spécifient pas le type
     */
    public static function add($data, $scanPath = null) {
        // S'assurer que le type est 'cheque' pour les anciens appels
        // Mais seulement si type_operation n'est pas déjà défini
        if (!isset($data['type_operation']) || empty($data['type_operation'])) {
            $data['type_operation'] = 'cheque';
        }
        return parent::add($data, $scanPath);
    }
    
    /**
     * Redirige vers Operation::find()
     */
    public static function find($id) {
        return parent::find($id);
    }
    
    /**
     * Redirige vers Operation::updateStatut()
     */
    public static function updateStatut($id, $statut, $observation = null, $userId = null) {
        return parent::updateStatut($id, $statut, $observation, $userId);
    }
    
    /**
     * Redirige vers Operation::updateEtatConfirmation()
     */
    public static function updateEtatConfirmation($id, $etat, $observation = null, $userId = null) {
        return parent::updateEtatConfirmation($id, $etat, $observation, $userId);
    }
    
    /**
     * Redirige vers Operation::updateEtatValidation()
     */
    public static function updateEtatValidation($id, $etat, $observation = null, $userId = null) {
        return parent::updateEtatValidation($id, $etat, $observation, $userId);
    }
    
    /**
     * Redirige vers Operation::confirm()
     */
    public static function confirm($id, $etat, $commentaire = '') {
        return parent::confirm($id, $etat, $commentaire);
    }
    
    /**
     * Redirige vers Operation::cancel()
     */
    public static function cancel($id, $motif, $commentaire = '') {
        return parent::cancel($id, $motif, $commentaire);
    }
    
    /**
     * Redirige vers Operation::update()
     */
    public static function update($id, $data) {
        return parent::update($id, $data);
    }
    
    /**
     * Redirige vers Operation::delete()
     */
    public static function delete($id) {
        return parent::delete($id);
    }
}
?>