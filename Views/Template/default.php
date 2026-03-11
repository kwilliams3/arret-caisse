<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>Arrets Caisses</title>

    <meta name="description" content="User login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	
	<!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="Public/css/bootstrap.min.css" />
	<link rel="stylesheet" href="Public/css/css/fontawesome.css" />
	<link rel="stylesheet" href="Public/css/css/fontawesome.min.css" />
	<link rel="stylesheet" href="Public/css/css/all.css" />
	<link rel="stylesheet" href="Public/css/css/all.min.css" />
    <link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css" />
	
    <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">  -->

    <!-- page specific plugin styles -->
    <link rel="stylesheet" href="Public/css/jquery-ui.min.css" />
    <link rel="stylesheet" href="Public/css/ui.jqgrid.min.css" />
    <!-- text fonts 
    <link rel="stylesheet" href="Public/css/fonts.googleapis.com.css" /> -->

    <!-- ace styles -->
    <link rel="stylesheet" href="Public/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="Public/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->
    <link rel="stylesheet" href="Public/css/ace-skins.min.css" />
    <link rel="stylesheet" href="Public/css/ace-rtl.min.css" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="Public/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- ace settings handler -->
    <script src="Public/js/ace-extra.min.js"></script>
	
</head>

<body class="login-layout">
<div class="main-container">
    <div class="main-content">

        <?= $content ?>

    </div><!-- /.main-content -->
</div><!-- /.main-container -->

<!-- basic scripts -->

<!--[if !IE]> -->
<script src="Public/js/jquery-2.1.4.min.js"></script>

<!-- <![endif]-->

<!--[if IE]>
<script src="Public/js/jquery-1.11.3.min.js"></script>
<![endif]-->
<script type="text/javascript">
    if('ontouchstart' in document.documentElement) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>
<script src="Public/js/jquery.js"></script>
<script src="Public/js/bootstrap.js"></script>
<script src="Public/js/bootstrap.min.js"></script>
<script src="Public/js/js/fontawesome.js"></script>
<script src="Public/js/js/fontawesome.min.js"></script>
<script src="Public/js/js/all.min.js"></script>
<script src="Public/js/js/all.js"></script>
<!-- page specific plugin scripts -->

<!-- ace scripts -->
<script src="Public/js/ace-elements.min.js"></script>
<script src="Public/js/ace.min.js"></script>


<!--[if IE]>
<script src="Public/js/jquery-1.11.3.min.js"></script>
<![endif]-->

<script src="Public/js/bootstrap.min.js"></script>

<!-- page specific plugin scripts -->

<!--[if lte IE 8]>
<script src="Public/js/excanvas.min.js"></script>
<![endif]
<script src="Public/js/jquery-ui.min.js"></script>
<script src="Public/js/jquery-ui.custom.min.js"></script>
<script src="Public/js/jquery.ui.touch-punch.min.js"></script>
<script src="Public/js/jquery.easypiechart.min.js"></script>
<script src="Public/js/jquery.sparkline.index.min.js"></script>
<script src="Public/js/jquery.flot.min.js"></script>
<script src="Public/js/jquery.flot.pie.min.js"></script>
<script src="Public/js/jquery.flot.resize.min.js"></script> -->

<!-- ace scripts -->
<script src="Public/js/jquery.dataTables.min.js"></script>
<script src="Public/js/jquery.dataTables.bootstrap.min.js"></script>

<!--<script src="Public/js/bootstrap-datepicker.min.js"></script>
<script src="Public/js/bootstrap-timepicker.min.js"></script> -->

<script src="Public/js/dataTables.buttons.min.js"></script>
<!-- <script src="Public/js/ace-elements.min.js"></script> -->
<script src="Public/js/Programme.js"></script>
<script src="Public/js/jquery.maskedinput.min.js"></script>
<script src="Public/js/ace.min.js"></script>


<!-- inline scripts related to this page -->
<script type="text/javascript">

//you don't need this, just used for changing background
    jQuery(function($) {
        $.mask.definitions['~']='[+-]';
		$('.input-mask-date').mask('99/99/9999');
		$('.input-mask-phone').mask('699-999-999');

    });

    jQuery(function($) {

        $( ".form-Connection" ).on('submit', function(e) {
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

                    beforeSend: function () {
                        $('.loader').removeClass('hidden');
                        $('.connect').addClass('hidden');
                    },
                    success: function (json) {
                        if (json.statuts == 0){
							
							$('.message').text(json.mes);
                            window.location.assign(json.direct);
                            
                        }else{
                            $('.message').text(json.mes);
                        }
                    },
                    complete: function () {
                        $('.connect').removeClass('hidden');
                        $('.loader').addClass('hidden');
                    },

                    error: function(jqXHR, textStatus, errorThrown){
                        alert('erreur : '+errorThrown);
                    }
                });
            }else{
				$('.message').text('Veuillez remplir tous les champs');
            }

        });

    });
	
	
    jQuery(function($) {
        $(document).on('click', '.toolbar a[data-target]', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('.widget-box.visible').removeClass('visible');//hide others
            $(target).addClass('visible');//show target
        });
    });
	
</script>

</body>
</html>
