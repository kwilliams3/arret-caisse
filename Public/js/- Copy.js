/**
 * Created by hp on 15/08/2017.
 */

jQuery(function($) {

    $( ".datepicker" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: false
        //isRTL:true,
    });

    $( ".article" ).on('change', function(e) {
        e.preventDefault();
        alert('test ');
        var article = $('.article').val().split(',');
        alert($('.article').val());
        $('.qtite').val(article[1]);

    });

    $( "#form-transfereCommandes" ).on('submit', function(e) {
        e.preventDefault();

        var url = $(this).attr('action');

            $.ajax({
                type: 'post',
                url: url,
                datatype: 'json',

                beforeSend: function () {
                    $('#modal-TransfererArticle').modal();
                },
                success: function (json) {
                    if (json.statuts == 0){
                        alert(json.mes);
                        window.location.reload();
                    }else{
                        $('#modal-TransfererArticle').close();
                        alert(json.mes);
                    }
                },
                complete: function () {
                    $('#modal-TransfererArticle').close();
                },

                error: function(jqXHR, textStatus, errorThrown){
                    alert('erreur : '+errorThrown);
                }
            });

    });

    // Transfert des articles rotation
    $( "#form-transfereArticleRota" ).on('submit', function(e) {
        e.preventDefault();

        var url = $(this).attr('action');

        $.ajax({
            type: 'post',
            url: url,
            datatype: 'json',

            beforeSend: function () {
                $('#modal-TransfererArticleRotation').modal();
            },
            success: function (json) {
                if (json.statuts == 0){
                    alert(json.mes);
                    window.location.reload();
                }else{
                    $('#modal-TransfererArticleRotation').close();
                    alert(json.mes);
                }
            },
            complete: function () {
                $('#modal-TransfererArticleRotation').close();
            },

            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });

    });

    // Transfert des articles rotation
    $( "#form-transfereArticleRupt" ).on('submit', function(e) {
        e.preventDefault();

        var url = $(this).attr('action');

        $.ajax({
            type: 'post',
            url: url,
            datatype: 'json',

            beforeSend: function () {
                $('#modal-TransfererArticleRupture').modal();
            },
            success: function (json) {
                if (json.statuts == 0){
                    alert(json.mes);
                    window.location.reload();
                }else{
                    $('#modal-TransfererArticleRupture').close();
                    alert(json.mes);
                }
            },
            complete: function () {
                $('#modal-TransfererArticleRupture').close();
            },

            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });

    });

    // Transfert des articles rotation
    $( "#form-transfereArticleStoc" ).on('submit', function(e) {
        e.preventDefault();

        var url = $(this).attr('action');

        $.ajax({
            type: 'post',
            url: url,
            datatype: 'json',

            beforeSend: function () {
                $('#modal-TransfererArticleStock').modal();
            },
            success: function (json) {
                if (json.statuts == 0){
                    alert(json.mes);
                    window.location.reload();
                }else{
                    $('#modal-TransfererArticleStock').close();
                    alert(json.mes);
                }
            },
            complete: function () {
                $('#modal-TransfererArticleStock').close();
            },

            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });

    });

    // Transfert des articles rotation
    $( "#form-transfereArticleProp" ).on('submit', function(e) {
        e.preventDefault();

        var url = $(this).attr('action');

        $.ajax({
            type: 'post',
            url: url,
            datatype: 'json',

            beforeSend: function () {
                $('#modal-TransfererArticleProp').modal();
            },
            success: function (json) {
                if (json.statuts == 0){
                    alert(json.mes);
                    window.location.reload();
                }else{
                    $('#modal-TransfererArticleProp').close();
                    alert(json.mes);
                }
            },
            complete: function () {
                $('#modal-TransfererArticleProp').close();
            },

            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });

    });

    // Transfert des articles rotation
    $( "#form-transfereArticleRuptMag" ).on('submit', function(e) {
        e.preventDefault();

        var url = $(this).attr('action');

        $.ajax({
            type: 'post',
            url: url,
            datatype: 'json',

            beforeSend: function () {
                $('#modal-TransfererArticleRuptureMag').modal();
            },
            success: function (json) {
                if (json.statuts == 0){
                    alert(json.mes);
                    window.location.reload();
                }else{
                    $('#modal-TransfererArticleRuptureMag').close();
                    alert(json.mes);
                }
            },
            complete: function () {
                $('#modal-TransfererArticleRuptureMag').close();
            },

            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });

    });


    //Debut des  fonction la gestion des utilisateurs
    $( ".AddUtilisateur" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddUtilisateur').modal();
    });

    $( "#form-AddUtilisateur" ).on('submit', function(e) {
        e.preventDefault();
        //alert('test pour voir');
        var url = $(this).attr('action');
        var login = $('.login').val();
        var password = $('.password').val();
        var confirmPassword = $('.confirmPassword').val();
        var privilege = $('.privilege').val();
        var agence = $('.agence').val();
        var adresseIP = $('.ipServeur').val();
        var baseDonnees = $('.baseDonnees').val();

        if(password == confirmPassword){
            if (url != ' ' && login != ' ' && password != ' ' && confirmPassword != ' ' && privilege != ' ' && agence != ' ' && adresseIP != ' ' && baseDonnees != ' ') {
                $.ajax({
                    type: 'post',
                    url: url,
                    data: 'login='+login+'&password='+password+'&confirmPassword='+confirmPassword+'&privilege='+privilege+
                    '&agence='+agence+'&adresseIP='+adresseIP+'&baseDonnees='+baseDonnees,
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
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
        }else{
            alert('Les mots de passes ne sont pas identiques');
        }

    });

    $( ".modifierUtilisateur" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-UpdateUtilisateur').modal();
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

    $( ".deleteUtilisateur" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        $('#modal-DeleteUtilisateur').modal();

        $('.idUser').val(id);

    });

    $( "#form-DeleteUtilisateur" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idUser").val();
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

    //Fin des fonctions de gestion des utilisateurs modifierActionsCorrective

    $( ".creationActionsCorrection" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id').split('_'),
          article = id[1]+'('+id[0]+')';

        $('#modalAddActionsCorrectiveArticle').modal();
            $('.article').val(article);
            $('.qtite').val(id[2]);
            $('.idArticle').val(id);
    });

    $( ".AddActionsCorrective" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddActionsCorrective').modal();
    });

    $( "#form-AddActionsCorrectiveCor" ).on('submit', function(e) {
        e.preventDefault();
        //alert('test pour voir');
        var url = $(this).attr('action');
        var date = $('.dateLivraison').val();
        var qtite = $('.qtite').val();
        var article = $('.article').val();
        var agence = $('.agence').val();
        var actionsCorrective = $('.actionsCorrective').val();
        var actionsPropose = $('.actionsPropose').val();
        var pilote = $('.pilote').val();
        var delai = $('.delai').val();
        var statut = $('.statut').val();
        var obsAgence = $('.obsAgence').val();
        var obsRV = $('.obsRV').val();

        //alert($('.qtite').val());
        //alert($('.article').val());

        if (url != ' ' && date != ' ' && qtite != ' ' && article != ' ' && agence != ' ' && actionsCorrective != ' ' && actionsPropose != ' ' && pilote != ' '
            && delai != ' ' && statut != ' ') {
            $.ajax({
                type: 'post',
                url: url,
                data: 'date='+date+'&qtite='+qtite+'&article='+article+'&agence='+agence+'&actionsCorrective='+actionsCorrective+
                '&actionsPropose='+actionsPropose+'&pilote='+pilote+'&delai='+delai+'&statut='+statut+'&obsAgence='+obsAgence+'&obsRV='+obsRV,
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
            alert('Veuillez rensiegner tous les champs obligatoires');
        }


    });

    $( "#form-AddActionsCorrective" ).on('submit', function(e) {
        e.preventDefault();
        //alert('test pour voir');
        var url = $(this).attr('action');
        var date = $('.dateLivraison').val();
        var qtite = $('.qtite').val();
        var article = $('.article').val();
        var agence = $('.agence').val();
        var actionsCorrective = $('.actionsCorrective').val();
        var actionsPropose = $('.actionsPropose').val();
        var pilote = $('.pilote').val();
        var delai = $('.delai').val();
        var statut = $('.statut').val();
        var obsAgence = $('.obsAgence').val();
        var obsRV = $('.obsRV').val();

        alert($('.qtite').val());
        alert($('.article').val());

        if (url != ' ' && date != ' ' && qtite != ' ' && article != ' ' && agence != ' ' && actionsCorrective != ' ' && actionsPropose != ' ' && pilote != ' '
            && delai != ' ' && statut != ' ') {
            $.ajax({
                type: 'post',
                url: url,
                data: 'date='+date+'&qtite='+qtite+'&article='+article+'&agence='+agence+'&actionsCorrective='+actionsCorrective+
                '&actionsPropose='+actionsPropose+'&pilote='+pilote+'&delai='+delai+'&statut='+statut+'&obsAgence='+obsAgence+'&obsRV='+obsRV,
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
            alert('Veuillez rensiegner tous les champs obligatoires');
        }


    });

    $( ".modifierActionsCorrective" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-UpdateActionsCorrective').modal();
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

    $( ".deleteActionsCorrective" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        $('#modal-DeleteActionsCorrection').modal();

        $('.idAction').val(id);

    });

    $( "#form-ActionsCorrective" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idAction").val();
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

    //Debut des  fonction la gestion des agences

    $( ".AddAgence" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddAgence').modal();
    });

    $( "#form-AddAgence" ).on('submit', function(e) {
        e.preventDefault();
        //alert('test pour voir');
        var url = $(this).attr('action');
        var titre = $('.titre').val();
        var adresseIP = $('.adresseIP').val();
        var baseDonnees = $('.baseDonnees').val();

            if (url != ' ' && adresseIP != ' ' && baseDonnees != ' ' && titre != ' ') {
                $.ajax({
                    type: 'post',
                    url: url,
                    data: 'titre='+titre+'&adresseIP='+adresseIP+'&baseDonnees='+baseDonnees,
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
                alert('Veuillez rensiegner tous les champs obligatoires ');
            }


    });

    $( ".modifierAgence" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-UpdateAgence').modal();
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

    $( ".deleteAgence" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        $('#modal-DeleteAgence').modal();

        $('.idAgence').val(id);

    });

    $( "#form-DeleteAgence" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idAgence").val();
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

    //Fin des  fonction la gestion des agences

    //Debut des  fonction la gestion des agences

    $( ".AddArticle" ).on('click', function(e) {
        e.preventDefault();

        $('#modalAddArticle').modal();
    });

    $( "#form-AddArticle" ).on('submit', function(e) {
        e.preventDefault();
        //alert('test pour voir');
        var url = $(this).attr('action');
        var code = $('.code').val();
        var famille = $('.famille').val();
        var designation = $('.designation').val();

        alert(designation);

        if (url != ' ' && code != ' ' && famille != ' ' && designation != ' ') {
            $.ajax({
                type: 'post',
                url: url,
                data: 'code='+code+'&famille='+famille+'&designation='+designation,
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
            alert('Veuillez rensiegner tous les champs obligatoires');
        }


    });

    $( ".modificationAgence" ).on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            url = $(this).data('url');

        $.ajax({
            type: 'post',
            url: url,
            data: 'id='+id,
            datatype: 'json',
            beforeSend: function () {
                $('#modal-UpdateArticle').modal();
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

    $( ".deleteArticle" ).on('click', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        $('#modal-DeleteArticle').modal();

        $('.idArticle').val(id);

    });

    $( "#form-DeleteArticle" ).on("submit", function(e) {
        e.preventDefault();

        var id = $(".idArticle").val();
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

    //Fin des  fonction la gestion des agences//Debut des  fonction la gestion des agences

});
