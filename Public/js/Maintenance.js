/**
 * Created by hp on 15/08/2017.
 */

jQuery(function($) {

    $( ".modalAddListChoix" ).on('click', function(e) {
        e.preventDefault();

        $('#addListChoix').modal({
            backdrop: 'static'
        });

    });

    $( "#BtnTest" ).on('click', function(e) {
        e.preventDefault();

        $('#planningModaldetails').modal({
            backdrop: 'static'
        });

    });

    $("#TestPlus").on('click', function(e) {
        e.preventDefault();

        $('#takeRdvModal').modal({
            backdrop: 'static'
        });

    });

    $( ".datepicker" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: false
        //isRTL:true,
    });

    $( "#form-AddListeMaintenance" ).on('submit', function(e) {
        e.preventDefault();

        var maintenance = $('#maintenance').val();
        var url = $(this).attr('action');

        if(maintenance !== ' '){

            if(maintenance === 'centre de cout' || maintenance === 'categoriesEmployes' || maintenance === 'certificationsEmployes' ||
                maintenance === 'statutEmployes' || maintenance === 'typesEmployes' || maintenance === 'expirationEquipmt' ||
                maintenance === 'marquesEquipmt' || maintenance === 'modelesEquipmt' || maintenance === 'typesEquipmt' ||
                maintenance === 'categoriesEquipmt' || maintenance === 'typesFluides' || maintenance === 'unitesFluides' ||
                maintenance === 'categoriesPieces' || maintenance === 'fabricantPieces' || maintenance === 'entrepotPieces' ||
                maintenance === 'unitesPieces' || maintenance === 'typesFournisseurs' || maintenance === 'statutBT' || maintenance === 'typesReparations' ||
                maintenance === 'typesTravail' || maintenance === 'typesFraisGeneraux' || maintenance === 'niveaux' || maintenance === 'typesMaintenance' ||
                maintenance === 'typesMetier' ||  maintenance === 'departements'|| maintenance === 'provinces' || maintenance === 'niveauUrgence' ||
                maintenance === 'atelierEquipmt' || maintenance === 'sectionsEquipmt' ||  maintenance === 'categoriePieceDeffect' ||  maintenance === 'sousCategoriePieceDeffect' ){

                var titre = $('#titre').val();

                if (titre!=' '){
                    $.ajax({
                        type: 'post',
                        url: url,
                        data: 'titre='+titre+'&maintenance='+maintenance,
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

            }

            if(maintenance === 'operations'){

                var titre2 = $('#titre').val();
                var description = $('#description').val();

                if (titre2!=' ' && description != ' '){
                    $.ajax({
                        type: 'post',
                        url: url,
                        data: 'titre='+titre2+'&description='+description+'&maintenance='+maintenance,
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

            }

            if(maintenance === 'reparationsCommunes'){

                var titre1 = $('#titre').val();
                var description1 = $('#description').val();
                var tentative = $('#tentative').val();
                var niveau = $('#niveau').val();
                var tempsExecution = $('#tempsExecution').val();

                if (titre1!=' ' && description1 != ' ' && tentative != ' ' && niveau != ' '&& tempsExecution != ' '){
                    $.ajax({
                        type: 'post',
                        url: url,
                        data: 'titre='+titre1+'&description='+description1+'&tentative='+tentative+'&niveau='+niveau+'&maintenance='+maintenance+'&tempsExecution='+tempsExecution,
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

            }

        }else{

            alert('Veuillez remplir tous les champs');
        }

    });


    $( ".ModifierValeur" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url'),
            maintenance = $(this).data('maintenance');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id+'&maintenance='+maintenance,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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


    $( ".supprimerMaintenance" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            maintenance = $(this).data('maintenance'),
            url = $(this).data('url');
        $('#modal-DeleteMaintenance').modal();

        $('.idMaintenance').val(id);
        $('.Maintenance').val(maintenance);

    });

    $( "#form-DeleteMaintenance" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idMaintenance").val();
        var maintenance = $(".Maintenance").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id+"&maintenance="+maintenance,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }

    });


    // les fonctions pour les fournisseurs

    $( ".addFournisseur" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddFournisseur').modal({
            backdrop: 'static'
        });

    });

    $( "#form-AddFournisseur" ).on('submit', function(e) {
        e.preventDefault();

        var maintenance = $('#maintenance').val();
        var url = $(this).attr('action');
        var nom = $('#nom').val();
        var contact = $('#contact').val();
        var adresse1 = $('#adresse1').val();
        var adresse2 = $('#adresse2').val();
        var ville = $('#ville').val();
        var codePostal = $('#codePostal').val();
        var telephone1 = $('#telephone1').val();
        var telephone2 = $('#telephone2').val();
        var fax = $('#fax').val();
        var localisation = $('#localisation').val();
        var email = $('#email').val();
        var type = $('#type').val();
        var site = $('#site').val();
        var commentaire = $('#commentaire').val();

        if (nom!=' ' && contact != ' ' && telephone1 != ' '){
            $.ajax({
                type: 'post',
                url: url,
                data: 'nom='+nom+'&contact='+contact+'&adresse1='+adresse1+'&adresse2='+adresse2+'&ville='+ville+'&email='+email+'&type='+type+'&site='+site
                +'&codePostal='+codePostal+'&telephone1='+telephone1+'&telephone2='+telephone2+'&fax='+fax+'&localisation='+localisation+'&commentaire='+commentaire,
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


    $( ".ModifierFournisseur" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerFournisseur" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteFournisseur').modal();

        $('.id').val(id);

    });

    $( "#form-DeleteFourniseur" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".id").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    // Affichage de la photo avant enregiststrement
    $(document).on('change','#photo',function (e) {
        var files = $(this)[0].files;

        if (files.length > 0) {
            // On part du principe qu'il n'y qu'un seul fichier
            // étant donné que l'on a pas renseigné l'attribut "multiple"
            var file = files[0],
                $image_preview = $('#image_preview');

            var extensions = ['jpeg','JPEG','jpg','JPG','png','PNG'];
            res = file.name.split("."),
                extension = res[res.length-1];
            // Ici on injecte les informations recoltées sur le fichier pour l'utilisateur
            $image_preview.find('.thumbnail').removeClass('hidden');
            $image_preview.find('img').attr('src', window.URL.createObjectURL(file));
            $image_preview.find('.caption .nomPhoto').html(file.name);
            $image_preview.find('.caption .sizePhoto').html(file.size +' bytes');
            /*($.inArray(extension,extensions) == -1){
             $('#photo').val('');
             $('#image_preview').find('.thumbnail').addClass('hidden');
             alertify.set('notifier','position', 'top-right');
             alertify.notify('Le fichier d\'examen doit être une image','error',5);
             }*/
        }
    });

    $(document).on('click','.caption .delPhoto',function (e) {
        e.preventDefault();
        $('#photo').val(' ');
        $('#image_preview').find('.thumbnail').addClass('hidden');
    });

    $( ".ajoutEntrepot" ).on("click", function(e) {
        e.preventDefault();

        $('.copycat:first').clone().appendTo($('#afficheEntrepot'));

    });

    //Enregistrer la nouvelle piece
    $(document).on('submit','#form-AddPieces',function (e) {
        e.preventDefault();
        /*var entrepot = $(".entrepot").serialize().split('&');
        alert(entrepot);*/
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            contentType: false,
            processData: false,
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
    });

    //enregistrer un nouvel outillage
    $(document).on('submit','#form-AddOutillage',function (e) {
        e.preventDefault();
        alert('test pour voir');
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            contentType: false,
            processData: false,
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
    });

    // les fonctions pour les pieces

    $( ".addPiece" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddPiece').modal({
            backdrop: 'static'
        });

    });

    $( ".addOutillage" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddOutillage').modal({
            backdrop: 'static'
        });

    });

    // les fonctions pour les pieces detachees
    $( ".ModifierPieces" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerPieces" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeletePiece').modal();

        $('.id').val(id);

    });

    $( "#form-DeletePiece" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".id").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    //regulariation de la quntite des pieces en stock

    $( ".regulariserStock" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-RegulariserStock').modal();
                document.getElementById("chargerReg").style.display = "none";
                document.getElementById("loaderReg").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetailsReg').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-RegulariserStock').hide();
                }
            },
            complete: function () {
                document.getElementById("chargerReg").style.display = "block";
                document.getElementById("loaderReg").style.display = "none";
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });
    });

    //les fonctions des outillages

    $( ".ModifierOutillages" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerOutillage" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteOutillage').modal();

        $('.id').val(id);

    });

    $( "#form-deleteOutillage" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".id").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    // fonction pour entrer les details d'un caisse a outil

    $( ".addDetailBoite" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddDetailBoite').modal({
            backdrop: 'static'
        });

    });

    $( "#form-AddDetailBoite" ).on("submit", function(e) {
        e.preventDefault();

        var titre = $("#titre").val();
        var quantite = $("#quantite").val();
        var description = $("#description").val();
        var url = $(this).attr("action");
        if (titre !=" " && quantite !=" " && description !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: 'titre='+titre+'&quantite='+quantite+'&description='+description,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }

    });

    $( ".ModifierPieceBoite" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerDetailBoite" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteDetailBoite').modal();

        $('.id').val(id);

    });

    $( "#form-DeletePieceDetail" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".id").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    //Add mouvement outillges

    $( ".addOutillageMvt" ).on('click', function(e) {
        e.preventDefault();

        $('#modalMvtsOutillages').modal({
            backdrop: 'static'
        });

    });

    $( ".ajoutChampsOutil" ).on("click", function(e) {
        e.preventDefault();

        $('.copycat:first').clone().appendTo($('#afficheChampsOutil'));

    });

    $( "#form-AddRetraitOutillage" ).on("submit", function(e) {
        e.preventDefault();

        var piece = $(".piece").serialize().split('&');
        var quantite = $(".quantite").serialize().split('&');
        var dateRetrait = $(".dateRetrait").val();
        var employe = $(".employe").val();
        var typeMvts = $(".typeMvts").val();
        var commentaires = $(".commentaires").val();
        //var idBon = $(".idBon").val();
        var idEntrepot = $(".entrepot").val();
        var url = $(this).attr("action");
        if (piece !=" " && quantite !=" " && dateRetrait !=" " && employe !=" " && typeMvts !=" " && idEntrepot !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: 'piece='+piece+'&quantite='+quantite+'&dateRetrait='+dateRetrait+'&employe='+employe+'&typeMvts='+typeMvts+'&commentaires='+commentaires+'&entrepot='+idEntrepot,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }

    });

    // les fonctions pour les employes

    $( ".addEmploye" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddEmploye').modal({
            backdrop: 'static'
        });

    });

    $(document).on('submit','#form-AddEmployes',function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
        //alert('test pour voir');
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            contentType: false,
            processData: false,
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
    });

    $( ".ModifierEmploye" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerEmploye" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteEmploye').modal();

        $('.id').val(id);

    });

    $( "#form-DeleteEmploye" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".id").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    // les fonctions pour les equipements

    $( ".addEquipement" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddEquipement').modal({
            backdrop: 'static'
        });

    });

    $(document).on('submit','#form-AddEquipement',function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
        //alert('test pour voir');
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            contentType: false,
            processData: false,
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
    });

    $( ".ModifierEquipement" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerEquipement" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteEquipement').modal();

        $('.id').val(id);

    });

    $( "#form-DeleteEquipement" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".id").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    // Fonctions javascript pour la codification des pieces

    $( ".listeSectionBtn" ).on('click', function(e) {
        e.preventDefault();

        $('.listeSection').removeClass('hidden');
        $('.listeActivite').addClass('hidden');
        $('.listeSousFamille').addClass('hidden');

    });

    $( ".listeActiviteBtn" ).on('click', function(e) {
        e.preventDefault();

        $('.listeSection').addClass('hidden');
        $('.listeActivite').removeClass('hidden');
        $('.listeSousFamille').addClass('hidden');

    });

    $( ".listeSousFamilleBtn" ).on('click', function(e) {
        e.preventDefault();

        $('.listeSection').addClass('hidden');
        $('.listeActivite').addClass('hidden');
        $('.listeSousFamille').removeClass('hidden');

    });

    $( ".addSection" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddSectionPieces').modal({
            backdrop: 'static'
        });

    });

    $( ".addActivite" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddActivitesPieces').modal({
            backdrop: 'static'
        });

    });

    $( ".addSousFamille" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddSousFamillePieces').modal({
            backdrop: 'static'
        });

    });

    // enregistrement de le section des pieces

    $( "#form-AddSectionPiece" ).on("submit", function(e) {
        e.preventDefault();

        var reference = $(".reference").val();
        var titre = $(".titre").val();
        var url = $(this).attr("action");

        if (reference != " " && titre != " " && url != " "  ){
            $.ajax({
                type: "post",
                url: url,
                data: "reference="+reference+'&titre='+titre,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    $( ".ModifierSectionPiece" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerSectionPiece" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteSectionPiece').modal();

        alert(id);
        $('.idSectionDelete').val(id);

    });

    $( ".ChangerTypePiece" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id')

        $('#modal-ChangerTypePiece').modal();
        $('.idPiece').val(id);

    });

    $( "#form-ChangerTypePiece" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idPiece").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    $( "#form-DeleteSectionPiece" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idSectionDelete").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });


    //Pour les livraison non terminer

    $( ".LivraisonNonTerminer" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id');

        $('#modalLivraisonBonCommande').modal();

        $('.idBonCommande').val(id);

    });

    $( "#form-LivraisonNonBonCommande" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idBonCommande").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    // enregistrement de le activites des pieces

    $( "#form-AddActiviteSection" ).on("submit", function(e) {
        e.preventDefault();

        var reference = $(".referenceActivite").val();
        var titre = $(".titreActivite").val();
        var url = $(this).attr("action");

        if (reference != " " && titre != " " && url != " "  ){
            $.ajax({
                type: "post",
                url: url,
                data: "reference="+reference+'&titre='+titre,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    $( ".ModifierActiviteSection" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".supprimerActiviteSection" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id'),
            url = $(this).data('url');
        $('#modal-DeleteActivitePiece').modal();

        $('.idActiviteDelete').val(id);

    });

    $( "#form-DeleteActivitePiece" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idActiviteDelete").val();
        var url = $(this).attr("action");

        if (id !=" "){
            $.ajax({
                type: "post",
                url: url,
                data: "id="+id,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    // enregistrement les sous familles

    $( "#form-AddSousFamille" ).on("submit", function(e) {
        e.preventDefault();

        var section = $(".section").val();
        var activite = $(".activite").val();
        var reference = $(".referenceSousFamille").val();
        var titre = $(".titreSousFamille").val();
        var url = $(this).attr("action");

        if (section != " " && activite != " " &&reference != " " && titre != " " && url != " "  ){
            $.ajax({
                type: "post",
                url: url,
                data: "reference="+reference+'&titre='+titre+'&section='+section+'&activite='+activite,
                datatype: "json",
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        alert(json.mes);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert("erreur : "+errorThrown);
                }
            });
        }else{
            alert("Veuillez remplir tous les champs");
        }
    });

    $( ".ModifierSousFamille" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-details').modal();
                document.getElementById("charger").style.display = "none";
                document.getElementById("loader").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-details').hide();
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

    $( ".detailsAffichePiece" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-detailsPieces').modal();
                document.getElementById("chargerDet").style.display = "none";
                document.getElementById("loaderDet").style.display = "block";
            },
            success: function (json) {
                if(json.statuts == 0){
                    $('.chargerDetails').html(json.content);
                }else{
                    alertify.notify(json.mes,'error',5);
                    $('#modal-detailsPieces').hide();
                }
            },
            complete: function () {
                document.getElementById("chargerDet").style.display = "block";
                document.getElementById("loaderDet").style.display = "none";
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });
    });

    $( ".detailsBonCde" ).on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id'),
                url = $(this).data('url');

            $.ajax({
                type: 'post',
                url: url,
                data: 'id='+id,
                datatype: 'json',
                beforeSend: function () {
                    $('#modal-detailsBonCommande').modal();
                    document.getElementById("chargerDet").style.display = "none";
                    document.getElementById("loaderDet").style.display = "block";
                },
                success: function (json) {
                    if(json.statuts == 0){
                        $('.chargerDetails').html(json.content);
                    }else{
                        alertify.notify(json.mes,'error',5);
                        $('#modal-detailsBonCommande').hide();
                    }
                },
                complete: function () {
                    document.getElementById("chargerDet").style.display = "block";
                    document.getElementById("loaderDet").style.display = "none";
                },
                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });
        });


    var sampleData = initiateDemoData();//see below

    $('#tree2').ace_tree({
        dataSource: sampleData['dataSource2'] ,
        loadingHTML:'<div class="tree-loading"><i class="ace-icon fa fa-refresh fa-spin blue"></i></div>',
        'open-icon' : 'ace-icon fa fa-folder-open',
        'close-icon' : 'ace-icon fa fa-folder',
        'itemSelect' : true,
        'folderSelect': true,
        'multiSelect': true,
        'selected-icon' : null,
        'unselected-icon' : null,
        'folder-open-icon' : 'ace-icon tree-plus',
        'folder-close-icon' : 'ace-icon tree-minus'
    });


    /**
     //Use something like this to reload data
     $('#tree1').find("li:not([data-template])").remove();
     $('#tree1').tree('render');
     */


    /**
     //please refer to docs for more info
     $('#tree1')
     .on('loaded.fu.tree', function(e) {
	})
     .on('updated.fu.tree', function(e, result) {
	})
     .on('selected.fu.tree', function(e) {
	})
     .on('deselected.fu.tree', function(e) {
	})
     .on('opened.fu.tree', function(e) {
	})
     .on('closed.fu.tree', function(e) {
	});
     */


    function initiateDemoData(){

        var tree_data_2 = {

            'pictures' : {text: 'Pictures', type: 'folder', 'icon-class':'red'}	,
            'music' : {text: 'Music', type: 'folder', 'icon-class':'orange'}	,
            'video' : {text: 'Video', type: 'folder', 'icon-class':'blue'}	,
            'documents' : {text: 'Documents', type: 'folder', 'icon-class':'green'}	,
            'backup' : {text: 'Backup', type: 'folder'}	,
            'readme' : {text: '<i class="ace-icon fa fa-file-text grey"></i> ReadMe.txt', type: 'item'},
            'manual' : {text: '<i class="ace-icon fa fa-book blue"></i> Manual.html', type: 'item'}
        }

        tree_data_2['music']['additionalParameters'] = {
            'children' : [
                {text: '<i class="ace-icon fa fa-music blue"></i> song1.ogg', type: 'item'},
                {text: '<i class="ace-icon fa fa-music blue"></i> song2.ogg', type: 'item'},
                {text: '<i class="ace-icon fa fa-music blue"></i> song3.ogg', type: 'item'},
                {text: '<i class="ace-icon fa fa-music blue"></i> song4.ogg', type: 'item'},
                {text: '<i class="ace-icon fa fa-music blue"></i> song5.ogg', type: 'item'}
            ]
        }
        tree_data_2['video']['additionalParameters'] = {
            'children' : [
                {text: '<i class="ace-icon fa fa-film blue"></i> movie1.avi', type: 'item'},
                {text: '<i class="ace-icon fa fa-film blue"></i> movie2.avi', type: 'item'},
                {text: '<i class="ace-icon fa fa-film blue"></i> movie3.avi', type: 'item'},
                {text: '<i class="ace-icon fa fa-film blue"></i> movie4.avi', type: 'item'},
                {text: '<i class="ace-icon fa fa-film blue"></i> movie5.avi', type: 'item'}
            ]
        }
        tree_data_2['pictures']['additionalParameters'] = {
            'children' : {
                'wallpapers' : {text: 'Wallpapers', type: 'folder', 'icon-class':'pink'},
                'camera' : {text: 'Camera', type: 'folder', 'icon-class':'pink'}
            }
        }
        tree_data_2['pictures']['additionalParameters']['children']['wallpapers']['additionalParameters'] = {
            'children' : [
                {text: '<i class="ace-icon fa fa-picture-o green"></i> wallpaper1.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> wallpaper2.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> wallpaper3.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> wallpaper4.jpg', type: 'item'}
            ]
        }
        tree_data_2['pictures']['additionalParameters']['children']['camera']['additionalParameters'] = {
            'children' : [
                {text: '<i class="ace-icon fa fa-picture-o green"></i> photo1.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> photo2.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> photo3.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> photo4.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> photo5.jpg', type: 'item'},
                {text: '<i class="ace-icon fa fa-picture-o green"></i> photo6.jpg', type: 'item'}
            ]
        }


        tree_data_2['documents']['additionalParameters'] = {
            'children' : [
                {text: '<i class="ace-icon fa fa-file-text red"></i> document1.pdf', type: 'item'},
                {text: '<i class="ace-icon fa fa-file-text grey"></i> document2.doc', type: 'item'},
                {text: '<i class="ace-icon fa fa-file-text grey"></i> document3.doc', type: 'item'},
                {text: '<i class="ace-icon fa fa-file-text red"></i> document4.pdf', type: 'item'},
                {text: '<i class="ace-icon fa fa-file-text grey"></i> document5.doc', type: 'item'}
            ]
        }

        tree_data_2['backup']['additionalParameters'] = {
            'children' : [
                {text: '<i class="ace-icon fa fa-archive brown"></i> backup1.zip', type: 'item'},
                {text: '<i class="ace-icon fa fa-archive brown"></i> backup2.zip', type: 'item'},
                {text: '<i class="ace-icon fa fa-archive brown"></i> backup3.zip', type: 'item'},
                {text: '<i class="ace-icon fa fa-archive brown"></i> backup4.zip', type: 'item'}
            ]
        }
        var dataSource2 = function(options, callback){
            var $data = null
            if(!("text" in options) && !("type" in options)){
                $data = tree_data_2;//the root tree
                callback({ data: $data });
                return;
            }
            else if("type" in options && options.type == "folder") {
                if("additionalParameters" in options && "children" in options.additionalParameters)
                    $data = options.additionalParameters.children || {};
                else $data = {}//no data
            }

            if($data != null)//this setTimeout is only for mimicking some random delay
                setTimeout(function(){callback({ data: $data });} , parseInt(Math.random() * 500) + 200);

            //we have used static data here
            //but you can retrieve your data dynamically from a server using ajax call
            //checkout examples/treeview.html and examples/treeview.js for more info
        }


        return {'dataSource2' : dataSource2}
    }

    // auto completion

    var demo1 = $('select[name="duallistbox_demo1[]"]').bootstrapDualListbox({infoTextFiltered: '<span class="label label-purple label-lg">Filtered</span>'});
    var container1 = demo1.bootstrapDualListbox('getContainer');
    container1.find('.btn').addClass('btn-white btn-info btn-bold');

    /**var setRatingColors = function() {
					$(this).find('.star-on-png,.star-half-png').addClass('orange2').removeClass('grey');
					$(this).find('.star-off-png').removeClass('orange2').addClass('grey');
				}*/
    $('.rating').raty({
        'cancel' : true,
        'half': true,
        'starType' : 'i'
        /**,

         'click': function() {
						setRatingColors.call(this);
					},
         'mouseover': function() {
						setRatingColors.call(this);
					},
         'mouseout': function() {
						setRatingColors.call(this);
					}*/
    })//.find('i:not(.star-raty)').addClass('grey');



    //////////////////
    //select2
    $('.select2').css('width','200px').select2({allowClear:true})
    $('#select2-multiple-style .btn').on('click', function(e){
        var target = $(this).find('input[type=radio]');
        var which = parseInt(target.val());
        if(which == 2) $('.select2').addClass('tag-input-style');
        else $('.select2').removeClass('tag-input-style');
    });

    //////////////////
    $('.multiselect').multiselect({
        enableFiltering: true,
        enableHTML: true,
        buttonClass: 'btn btn-white btn-primary',
        templates: {
            button: '<button type="button" class="multiselect dropdown-toggle" data-toggle="dropdown"><span class="multiselect-selected-text"></span> &nbsp;<b class="fa fa-caret-down"></b></button>',
            ul: '<ul class="multiselect-container dropdown-menu"></ul>',
            filter: '<li class="multiselect-item filter"><div class="input-group"><span class="input-group-addon"><i class="fa fa-search"></i></span><input class="form-control multiselect-search" type="text"></div></li>',
            filterClearBtn: '<span class="input-group-btn"><button class="btn btn-default btn-white btn-grey multiselect-clear-filter" type="button"><i class="fa fa-times-circle red2"></i></button></span>',
            li: '<li><a tabindex="0"><label></label></a></li>',
            divider: '<li class="multiselect-item divider"></li>',
            liGroup: '<li class="multiselect-item multiselect-group"><label></label></li>'
        }
    });


    ///////////////////

    //typeahead.js
    //example taken from plugin's page at: https://twitter.github.io/typeahead.js/examples/
    var substringMatcher = function(strs) {
        return function findMatches(q, cb) {
            var matches, substringRegex;

            // an array that will be populated with substring matches
            matches = [];

            // regex used to determine if a string contains the substring `q`
            substrRegex = new RegExp(q, 'i');

            // iterate through the pool of strings and for any string that
            // contains the substring `q`, add it to the `matches` array
            $.each(strs, function(i, str) {
                if (substrRegex.test(str)) {
                    // the typeahead jQuery plugin expects suggestions to a
                    // JavaScript object, refer to typeahead docs for more info
                    matches.push({ value: str });
                }
            });

            cb(matches);
        }
    }

    $('input.typeahead').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    }, {
        name: 'states',
        displayKey: 'value',
        source: substringMatcher(ace.vars['US_STATES']),
        limit: 10
    });


    ///////////////


    //in ajax mode, remove remaining elements before leaving page
    $(document).one('ajaxloadstart.page', function(e) {
        $('[class*=select2]').remove();
        $('select[name="duallistbox_demo1[]"]').bootstrapDualListbox('destroy');
        $('.rating').raty('destroy');
        $('.multiselect').multiselect('destroy');
    });


});
