/**
 * Created by hp on 15/08/2017.
 */

jQuery(function($) {

    $( ".datepicker" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: false
        //isRTL:true,
    });

    $( "#form-AddCommande" ).on('submit', function(e) {
        e.preventDefault();

        var accessoire = $('#accessoire').val();
        var qtite = $('#qtite').val();
        var prix = $('#prix').val();
        var url = $(this).attr('action');

        if (qtite !='' && accessoire != ' ' && prix != ' ' ){
            $.ajax({
                type: 'post',
                url: url,
                data: 'qtite='+qtite+'&accessoire='+accessoire+'&prix='+prix,
                datatype: 'json',
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        }else{
            alert('Veuillez remplir tous les champs');
        }

    });

    $( "#form-transformerCommande" ).on('submit', function(e) {
        e.preventDefault();

        var idCommande = $('#idCommande').val();
        var fournisseur = $('#fournisseur').val();
        var prix = $('#prix').val();
        var url = $(this).attr('action');

        if (idCommande !=' ' && fournisseur !=' ' && prix !=''){
            $.ajax({
                type: 'post',
                url: url,
                data: 'id='+idCommande+'&fournisseur='+fournisseur+'&prix='+prix,
                datatype: 'json',
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        }else{
            alert('Veuillez remplir tous les champs');
        }

    });

    $( ".transformerCommande" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');

        $('#modal-TransformerCommande').modal();

        $('#idCommande').val(id);

    });

    $( ".SuiviCommande" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');

        $('#modal-SuiviCommande').modal();

        $('#idCommande').val(id);

    });

    $( "#form-SuiviCommande" ).on('submit', function(e) {
        e.preventDefault();

        var idCommande = $('#idCommande').val();
        var titre = $('#titre').val();
        var url = $(this).attr('action');

        if (idCommande !='' && titre != ' '){
            $.ajax({
                type: 'post',
                url: url,
                data: 'id='+idCommande+'&titre='+titre,
                datatype: 'json',
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        }else{
            alert('Veuillez remplir tous les champs');
        }

    });

    $( "#form-AddMagasin" ).on('submit', function(e) {
        e.preventDefault();

        var magasin = $('.magasin').val();
        var url = $(this).attr('action');

        if (magasin != ' '){
            $.ajax({
                type: 'post',
                url: url,
                data: 'magasin='+magasin,
                datatype: 'json',
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        }else{
            alert('Veuillez remplir tous les champs');
        }

    });

    $( ".EditCommande" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');
        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-detailsCommandes').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-detailsCommandes').hide();
                }
            },
            complete: function () {
                document.getElementById("charger").style.display = "block";
                document.getElementById("loader").style.display = "none";
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });
    });

    $( ".details" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');
        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-detailsCommandes').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-detailsCommandes').hide();
                }
            },
            complete: function () {
                document.getElementById("charger").style.display = "block";
                document.getElementById("loader").style.display = "none";
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });
    });

    $( ".editMagasin" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');
        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-detailsCommandes').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-detailsCommandes').hide();
                }
            },
            complete: function () {
                document.getElementById("charger").style.display = "block";
                document.getElementById("loader").style.display = "none";
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });
    });

    $( ".deleteMagasin" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteMagasin').modal();

        $('.idMagasin').val(id);

    });

    $( "#form-DeleteMagasin" ).on('submit', function(e) {
        e.preventDefault();

        var idMagasin = $('.idMagasin').val();
        var url = $(this).attr('action');

        if (idMagasin !=''){
            $.ajax({
                type: 'post',
                url: url,
                data: 'id='+idMagasin,
                datatype: 'json',
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        }else{
            alert('Veuillez remplir tous les champs');
        }

    });

    $( ".detailsAccessoire" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            image = $(this).data('image');

        $('#modal-detailsCommandes').modal();

        if(image != ' '){
            $('.charger').html('<img class="photo" src="data:image;base64,'+image+'"/>');
        }else{
            $('.charger').text('Pas de photo');
        }

    });

    $( ".ValiderCommande" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');

        $('#modal-validerCommandes').modal();

        $('#idCommande').val(id);

    });

    $( "#form-validerCommande" ).on('submit', function(e) {
        e.preventDefault();

        var idCommande = $('#idCommande').val();
        var url = $(this).attr('action');

        if (idCommande !=''){
            $.ajax({
                type: 'post',
                url: url,
                data: 'id='+idCommande,
                datatype: 'json',
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        }else{
            alert('Veuillez remplir tous les champs');
        }

    });

});
