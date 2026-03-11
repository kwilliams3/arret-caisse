/**
 * Created by hp on 15/08/2017.
 */

jQuery(function($) {

    $( ".datepicker" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: false
        //isRTL:true,
    });

   /* $( "#form-LoginUser" ).on('submit', function(e) {
        e.preventDefault();

        var login = $('#login').val();
        var password = $('#password').val();
        var url = $(this).attr('action');

        if (login !=' ' && password !=' ' ){
            $.ajax({
                type: 'post',
                url: url,
                data: 'login='+login+'&password='+password,
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

    });*/

    // la fonction pour deconnexion de l'utilisateur

    $( "#deconnexion" ).on('click', function(e) {
        e.preventDefault();
        alert('Bouton de deconnexion');
        $.ajax({
            type: 'post',
            url: 'index.php?p=ajax.home.logout',
            datatype: 'json',
            success: function (json){
                if (json.statuts == 0){
                    window.location.assign(json.direct);
                    alert(json.mes);
                }else{
                    alert(json.mes);
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                alert('erreur : '+errorThrown);
            }
        });

    });

});
