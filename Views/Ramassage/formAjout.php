<?php
// Formulaire d'ajout - chargé via AJAX
?>
<div class="row">
    <div class="col-md-12">
        <form id="form-ajout-ramassage" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="entite_id">Entité (qui ramasse) *</label>
                        <select class="form-control" id="entite_id" name="entite_id" required>
                            <option value="">Sélectionner une entité</option>
                            <?php foreach ($agences as $agence): ?>
                                <option value="<?php echo $agence['id']; ?>">
                                    <?php echo htmlspecialchars($agence['designation']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="agence_id">Agence à ramasser *</label>
                        <select class="form-control" id="agence_id" name="agence_id" required>
                            <option value="">Sélectionner une agence</option>
                            <?php foreach ($agences as $agence): ?>
                                <option value="<?php echo $agence['id']; ?>">
                                    <?php echo htmlspecialchars($agence['designation']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="periode">Période *</label>
                        <input type="text" class="form-control" id="periode" name="periode" 
                               placeholder="Ex: Janvier 2024" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_debut">Date de début *</label>
                        <div class="input-group">
                            <input type="text" class="form-control date-picker" id="date_debut" 
                                   name="date_debut" placeholder="jj/mm/aaaa" required>
                            <span class="input-group-addon">
                                <i class="fa fa-calendar bigger-110"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_fin">Date de fin *</label>
                        <div class="input-group">
                            <input type="text" class="form-control date-picker" id="date_fin" 
                                   name="date_fin" placeholder="jj/mm/aaaa" required>
                            <span class="input-group-addon">
                                <i class="fa fa-calendar bigger-110"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="liste_ramasseurs">Liste des ramasseurs (PDF) *</label>
                        <input type="file" class="form-control" id="liste_ramasseurs" 
                               name="liste_ramasseurs" accept=".pdf" required>
                        <small class="text-muted">Format PDF uniquement, max 10MB</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="observations">Observations</label>
                        <textarea class="form-control" id="observations" name="observations" 
                                  rows="3" placeholder="Notes supplémentaires..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> 
                <strong>Important :</strong> La période de ramassage ne doit pas dépasser 1 mois (31 jours).
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#form-ajout-ramassage').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: 'index.php?p=ramassage&action=ajoutRamassage',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var data = JSON.parse(response);
                    if (data.success) {
                        alert(data.message);
                        $('#modal-ramassage').modal('hide');
                        location.reload(); // Recharger la page pour voir le nouvel enregistrement
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                } catch (e) {
                    alert('Erreur de traitement des données');
                }
            },
            error: function() {
                alert('Erreur lors de l\'envoi du formulaire');
            }
        });
    });
});
</script>