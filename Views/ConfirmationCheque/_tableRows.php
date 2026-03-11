<?php
/**
 * Vue partielle pour les lignes du tableau des chèques
 * Incluse via AJAX pour rafraîchir le tableau
 */
?>

<?php if (empty($cheques)): ?>
    <tr>
        <td colspan="8" class="text-center">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Aucun chèque enregistré
            </div>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($cheques as $cheque): ?>
        <?php
        // Déterminer la classe CSS selon le statut
        $statutClass = '';
        $statutText = '';
        
        switch ($cheque['statut']) {
            case 'en cours':
                $statutClass = 'warning';
                $statutText = 'En cours';
                break;
            case 'confirmé':
                $statutClass = 'success';
                $statutText = 'Confirmé';
                break;
            case 'annulé':
                $statutClass = 'danger';
                $statutText = 'Annulé';
                break;
            default:
                $statutClass = 'secondary';
                $statutText = $cheque['statut'];
        }
        
        // Formater les dates
        $dateReception = !empty($cheque['date_reception']) 
            ? date('d/m/Y', strtotime($cheque['date_reception'])) 
            : '';
            
        $dateEntree = !empty($cheque['date_entree']) 
            ? date('d/m/Y H:i', strtotime($cheque['date_entree'])) 
            : '';
            
        // Formater le montant
        $montant = !empty($cheque['montant']) 
            ? number_format($cheque['montant'], 2, ',', ' ') 
            : '0,00';
        ?>
        
        <tr>
            <td><?php echo htmlspecialchars($cheque['id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($cheque['nom_client'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($cheque['numero_cheque'] ?? ''); ?></td>
            <td class="text-right"><?php echo $montant; ?> FCFA</td>
            <td><?php echo htmlspecialchars($cheque['banque'] ?? ''); ?></td>
            <td><?php echo $dateReception; ?></td>
            <td>
                <span class="badge badge-<?php echo $statutClass; ?>">
                    <?php echo $statutText; ?>
                </span>
            </td>
            <td>
                <?php if (($cheque['statut'] ?? '') == 'en cours'): ?>
                    <!-- Boutons d'actions seulement si en cours -->
                    <button class="btn btn-sm btn-success btn-confirmer" 
                            data-id="<?php echo $cheque['id']; ?>"
                            data-toggle="modal" 
                            data-target="#modal-confirmer-<?php echo $cheque['id']; ?>">
                        <i class="fa fa-check"></i> Confirmer
                    </button>
                    
                    <button class="btn btn-sm btn-danger btn-annuler" 
                            data-id="<?php echo $cheque['id']; ?>"
                            data-toggle="modal" 
                            data-target="#modal-annuler-<?php echo $cheque['id']; ?>">
                        <i class="fa fa-times"></i> Annuler
                    </button>
                    
                    <!-- Modal de confirmation (simplifié) -->
                    <div class="modal fade" id="modal-confirmer-<?php echo $cheque['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirmer le chèque</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p>Confirmer le chèque #<?php echo $cheque['id']; ?> ?</p>
                                    <div class="form-group">
                                        <label>Commentaire :</label>
                                        <textarea class="form-control commentaire-confirmation" 
                                                  rows="3" 
                                                  placeholder="Commentaire optionnel"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                    <button type="button" class="btn btn-primary btn-confirm-final" 
                                            data-id="<?php echo $cheque['id']; ?>"
                                            data-etat="approvisionne">
                                        Confirmer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal d'annulation (simplifié) -->
                    <div class="modal fade" id="modal-annuler-<?php echo $cheque['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Annuler le chèque</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Motif d'annulation :</label>
                                        <input type="text" class="form-control motif-annulation" 
                                               placeholder="Motif de l'annulation">
                                    </div>
                                    <div class="form-group">
                                        <label>Commentaire :</label>
                                        <textarea class="form-control commentaire-annulation" 
                                                  rows="3" 
                                                  placeholder="Commentaire optionnel"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                    <button type="button" class="btn btn-danger btn-cancel-final" 
                                            data-id="<?php echo $cheque['id']; ?>">
                                        Annuler le chèque
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <span class="text-muted">Aucune action</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>