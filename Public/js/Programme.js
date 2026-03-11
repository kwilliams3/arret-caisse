/**
 * Created by hp on 15/08/2017.
 */

jQuery(function($) {
	
	$( ".addUserCaisse" ).on('click', function(e) {
			e.preventDefault();

			$('#modalAddUserCaisse').modal();
		});
		
		$( "#form-AddUserCaisse" ).on('submit', function(e) {
        e.preventDefault();
		
		var login = $('#login1').val();
		var nomUser = $('#nomUser1').val();
		var password = $('#password1').val();
		var confirmPassword = $('#confirmPassword1').val();
		var privilege = $('#privilege1').val();
		var agence = $('#agence1').val();
		var url = $(this).attr('action');
		
            if (login !='' && password !='' && confirmPassword !='' && nomUser !='' && privilege !='' && url !='' && agence !='') {
				if (password == confirmPassword) {
					
					$.ajax({
						type: 'post',
						url: url,
						data: 'nomUser='+nomUser+'&login='+login+'&password='+password+'&confirmPassword='+confirmPassword+'&privilege='+privilege+'&agence='+agence,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert('Les mots de passes ne sont pas identiques');
					}

            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		});
	
	$( ".modifierUserCaisse" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-UpdateUserCaisse').modal();
					document.getElementById("chargerUserCaisse").style.display = "none";
					document.getElementById("loaderUserCaisse").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsUserCaisse').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-details').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerUserCaisse").style.display = "block";
					document.getElementById("loaderUserCaisse").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
		$( ".ReinitialiserPassCaisse" ).on('click', function(e) {
			e.preventDefault();
			
			var id = $(this).data('id');
			$('#modalResetUserCaisse').modal();
			
			$('.idUserCaisse').val(id);
		});
	
	
	$( "#form-ResetUser" ).on('submit', function(e) {
        e.preventDefault();
		
		var password = $('#newPasswordCaisse1').val();
		var confirmPassword = $('#confirm_PasswordCaisse1').val();
		var id = $('.idUserCaisse').val();
		var url = $(this).attr('action');
			
            if (id !='' && password !='' && confirmPassword !='' && url !='') {
				if (password == confirmPassword) {
					$.ajax({
						type: 'post',
						url: url,
						data: 'id='+id+'&password='+password+'&confirmPassword='+confirmPassword,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderReset').removeClass('hidden');
								$('.resetUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.resetUser').removeClass('hidden');
								$('.loaderReset').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert('Les mots de passes ne sont pas identiques');
					}

            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		});
		
		//  javasript pour le gestion des agences
		$( ".AddAgenceCaisse" ).on('click', function(e) {
			e.preventDefault();

			$('#modalAddAgenceCaisse').modal();
		});
		
		$( "#form-AddAgenceCaisse" ).on('submit', function(e) {
        e.preventDefault();
		
		var designation = $('#designationCaisse1').val();
		var url = $(this).attr('action');
		
            if (designation !='') {
					
					$.ajax({
						type: 'post',
						url: url,
						data: 'designation='+designation,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegisterAg').removeClass('hidden');
								$('.connectAg').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectAg').removeClass('hidden');
								$('.loaderRegisterAg').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		});
		
		$( ".modifierAgenceCaisse" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-UpdateAgenceCaisse').modal();
					document.getElementById("chargerAg").style.display = "none";
					document.getElementById("loaderAg").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsAg').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-details').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerAg").style.display = "block";
					document.getElementById("loaderAg").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
	//  javasript pour le gestion des arrets a la caisse
	$( ".AddArretCaisse" ).on('click', function(e) {
		e.preventDefault();

		$('#modalAjoutArretCaisse').modal();
	});
	
	$( "#arretCash1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });	
	
	$( "#arretOrangeMobile1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });	
	
	$( "#arretMtnMobile1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });	
	
	$( "#arretTpeMobile1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });	
	
	$( "#arretCarte1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });	
	
	$( "#arretCheque1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });
	
	$( "#arretVirement1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });

	$( "#totalBonPros" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCash1').val();
		var arretOrangeMobile = $('#arretOrangeMobile1').val();
		var arretMtnMobile = $('#arretMtnMobile1').val();
		var arretTpeMobile = $('#arretTpeMobile1').val();
		var arretCarte = $('#arretCarte1').val();
		var arretCheque = $('#arretCheque1').val();
		var arretVirement = $('#arretVirement1').val();
		// var complementCash = $('#complementCash1').val();
		var totalBonPros = $('#totalBonPros').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisse').val(total);
		
    });

	$( "#form-ajoutArretsCaisse" ).on('submit', function(e) {
        e.preventDefault();
		
			var arretCash = $('#arretCash1').val();
			// var complementCash = $('#complementCash1').val();
			var arretOrangeMobile = $('#arretOrangeMobile1').val();
			var arretMtnMobile = $('#arretMtnMobile1').val();
			var arretTpeMobile = $('#arretTpeMobile1').val();
			var arretCarte = $('#arretCarte1').val();
			var arretCheque = $('#arretCheque1').val();
			var arretVirement = $('#arretVirement1').val();
			var totalBonPros = $('#totalBonPros').val();
			
			var url = $(this).attr('action');
			
			if ((arretCash !='' || arretCash ==0) && (arretOrangeMobile !='' || arretOrangeMobile == 0) && (arretMtnMobile !='' || arretMtnMobile == 0) 
			   && (arretTpeMobile !='' || arretTpeMobile == 0) && (arretCarte !='' || arretCarte ==0) && (arretCheque !='' || arretCheque == 0) 
			   && (arretVirement !='' || arretVirement ==0) && (totalBonPros !='' || totalBonPros == 0)) {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'arretCash='+arretCash+'&arretOrangeMobile='+arretOrangeMobile+'&arretMtnMobile='+arretMtnMobile+'&arretTpeMobile='
							  +arretTpeMobile+'&arretCarte='+arretCarte+'&arretCheque='+arretCheque+'&arretVirement='+arretVirement+'&totalBonPros='+totalBonPros,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
//  javasript pour le gestion des arrets a la caisse douanier

	$( ".AddArretDouanier" ).on('click', function(e) {
		e.preventDefault();

		$('#modalAjoutArretDouanier').modal();
		
	});
	
	$( "#arretInfo" ).on("change", function(e) {
        e.preventDefault();
        
		var totalCaisseD = $('#totalCaisseD').val();
		var arretDouanier = $('#arretDouanier').val();
		var arretInfo = $('#arretInfo').val();
		
		var diffCaisse = Number(arretInfo) - Number(totalCaisseD);
		var diffDouanier = Number(arretInfo) - Number(arretDouanier);
		
		$('#diffCaisse').val(diffCaisse);
		$('#diffDouanier').val(diffDouanier);
		
    });
	
	$( "#arretDouanier" ).on("change", function(e) {
        e.preventDefault();
        
		var arretDouanier = $('#arretDouanier').val();
		var arretInfo = $('#arretInfo').val();
		
		var diffDouanier = Number(arretInfo) + Number(arretDouanier);
		
		$('#diffDouanier').val(diffDouanier);
		
    });
	
	$( "#versement" ).on("change", function(e) {
			e.preventDefault();
			
			var versement = $('#versement').val();
			
			if(versement == 'Oui'){
				
				$("#borde").removeClass('hidden');
				$("#bordereauVers").removeClass('hidden');
				$("#MontantV").removeClass('hidden');
				$("#montantVerse").removeClass('hidden');
				
			}else{
				
				$("#borde").addClass('hidden');
				$("#bordereauVers").addClass('hidden');
				$("#MontantV").addClass('hidden');
				$("#montantVerse").addClass('hidden');
				
			}
			
		});
		
	 $( "#form-ajoutArretsDouanier" ).on('submit', function(e) {
        e.preventDefault();
		
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
		
		 
            if (url !='' && arretDouanier !='' && arretInfo !='' && obsArretChef !='' && versement !='' && observationVers !='') {
				
				 var versement = $('#versement').val();
				 
					 if(versement == 'Oui'){
						
						var montantVerse = $('#montantVerse').val();
						var bordereauVers = $('#bordereauVers').val();
						
						 if (bordereauVers !='' && bordereauVers !='') {
							 
								$.ajax({
									type: 'post',
									url:  url,
									data: data,
									contentType: false,
									processData: false,
									datatype: 'json',
									
									beforeSend: function () {
											$('.loaderRegister').removeClass('hidden');
											$('.connectUser').addClass('hidden');
										},
										
									success: function (json) {
										if (json.statuts == 0){
											alert(json.mes);
											window.location.reload();
										}else if(json.statuts == 2){
											alert(json.mes);
											window.location.reload();
										}else{
											alert(json.mes);
										}
									},
									
									complete: function () {
											$('.connectUser').removeClass('hidden');
											$('.loaderRegister').addClass('hidden');
											
										},
										
									error: function(jqXHR, textStatus, errorThrown){
										alert('erreur : '+errorThrown);
									}
								});
							   
							 }else{
								 
								alert('Veuillez rensiegner le montant et le bordereau de versement');
								
							}
					}else{
								 
						$.ajax({
							type: 'post',
							url:  url,
							data: data,
							contentType: false,
							processData: false,
							datatype: 'json',
							
							beforeSend: function () {
									$('.loaderRegister').removeClass('hidden');
									$('.connectUser').addClass('hidden');
								},
								
							success: function (json) {
								if (json.statuts == 0){
									alert(json.mes);
									window.location.reload();
								}else if(json.statuts == 2){
									alert(json.mes);
									window.location.reload();
								}else{
									alert(json.mes);
								}
							},
							
							complete: function () {
									$('.connectUser').removeClass('hidden');
									$('.loaderRegister').addClass('hidden');
									
								},
								
							error: function(jqXHR, textStatus, errorThrown){
								alert('erreur : '+errorThrown);
							}
						});		 
					
					}	
					
			
			}else{
				
                alert('Veuillez rensiegner tous les champs obligatoires');

            }
        
    });
	
	
	/* $( "#form-ajoutArretsDouanier" ).on('submit', function(e) {
        e.preventDefault();
		
			var arretDouanier = $('#arretDouanier').val();
			var arretInfo = $('#arretInfo').val();
			var obsArretChef = $('#obsArretChef1').val();
			
			var url = $(this).attr('action');
			
			if (arretDouanier !='' && arretDouanier !='' && arretInfo !='' && arretInfo !='' && obsArretChef !='' && obsArretChef !='' ) {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'arretDouanier='+arretDouanier+'&arretInfo='+arretInfo+'&obsArretChef='+obsArretChef,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});  */
		
		$( ".detailsArrets" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArrets').modal();
					document.getElementById("chargerArrets").style.display = "none";
					document.getElementById("loaderArrets").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArrets').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArrets').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArrets").style.display = "block";
					document.getElementById("loaderArrets").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
		$( ".miseJourCpta" ).on('click', function(e) {
			e.preventDefault();

			var id = $(this).data('id'),
				url = $(this).data('url');
				
				if (id !=""){
					$.ajax({
						type: 'post',
						url: url,
						data: 'id='+id,
						datatype: 'json',
						beforeSend: function () {
							$('#modal-MiseJourArretsCpta').modal();
							document.getElementById("chargerObsArretCpta").style.display = "none";
							document.getElementById("loaderObsArretCpta").style.display = "block";
						},
						success: function (json) {
							if(json.statuts == 0){
								$('.chargerDetailsObsArretCpta').html(json.content);
							}else{
								alertify.notify(json.mes,'error',5);
								$('#modal-MiseJourArretsCpta').hide();
							}
						},
						complete: function () {
							document.getElementById("chargerObsArretCpta").style.display = "block";
							document.getElementById("loaderObsArretCpta").style.display = "none";
						},
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert("Veuillez remplir tous les champs");
					}

		});
		
		$( ".miseJourControle" ).on('click', function(e) {
			e.preventDefault();
			
			var id = $(this).data('id');
			var date = $(this).data('date');
			var agence = $(this).data('agence');
			var diffCaisse = $(this).data('diff01');
			var diffDouanier = $(this).data('diff02');
			var controle = $(this).data('diff03');
			var actions = $(this).data('diff04');
			
			alert(diffCaisse);
			alert(diffDouanier);
			alert(controle);
			alert(actions);
				
			$('#idArretsCaisse').val(id);
			$('#dateArret').val(date);
			$('#agenceArret').val(agence);
			$('#diffArretCaisse').val(diffCaisse);
			$('#diffArretDouanier').val(diffDouanier);

			$('#modal-MiseJourArretsGestion').modal();
			
		});
		
		$( ".miseJourControleTest" ).on('click', function(e) {
			e.preventDefault();

			var id = $(this).data('id'),
				url = $(this).data('url');
				
				if (id !=""){
					$.ajax({
						type: 'post',
						url: url,
						data: 'id='+id,
						datatype: 'json',
						beforeSend: function () {
							$('#modal-MiseJourArretControle').modal();
							document.getElementById("chargerArretsControle").style.display = "none";
							document.getElementById("loaderArretsControle").style.display = "block";
						},
						success: function (json) {
							if(json.statuts == 0){
								$('.chargerDetailsArretsControle').html(json.content);
							}else{
								alertify.notify(json.mes,'error',5);
								$('#modal-MiseJourArretControle').hide();
							}
						},
						complete: function () {
							document.getElementById("chargerArretsControle").style.display = "block";
							document.getElementById("loaderArretsControle").style.display = "none";
						},
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert("Veuillez remplir tous les champs");
					}

		});
		
		$( "#form-ajoutArretsControle" ).on('submit', function(e) {
        e.preventDefault();
		
			var idArretsCaisse = $('#idArretsCaisse').val();
			var controlePhys = $('#controlePhys').val();
			var commentaireControle = $('#commentaireControle').val();
			var Action1 = $('#Action1').val();
			var delai1 = $('#delai1').val();
			var Action2 = $('#Action2').val();
			var delai2 = $('#delai2').val();
			var Action3 = $('#Action3').val();
			var delai3 = $('#delai3').val();
			
			var url = $(this).attr('action');
			
			if (idArretsCaisse !='' && controlePhys !='' && commentaireControle !=''  && Action1 !='' && delai1 !='') {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'idArretsCaisse='+idArretsCaisse+'&controlePhys='+controlePhys+'&commentaireControle='+commentaireControle+'&Action1='
							  +Action1+'&delai1='+delai1+'&Action2='+Action2+'&delai2='+delai2+'&Action3='+Action3+'&delai3='+delai3,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
		$( ".miseJourGestion1" ).on('click', function(e) {
			e.preventDefault();

			var id = $(this).data('id'),
				url = $(this).data('url');
				
				if (id !=""){
					$.ajax({
						type: 'post',
						url: url,
						data: 'id='+id,
						datatype: 'json',
						beforeSend: function () {
							$('#modal-MiseJourArretsGestion').modal();
							document.getElementById("chargerObsArretGestion").style.display = "none";
							document.getElementById("loaderObsArretGestion").style.display = "block";
						},
						success: function (json) {
							if(json.statuts == 0){
								$('.chargerDetailsObsArretGestion').html(json.content);
							}else{
								alertify.notify(json.mes,'error',5);
								$('#modal-MiseJourArretsGestion').hide();
							}
						},
						complete: function () {
							document.getElementById("chargerObsArretGestion").style.display = "block";
							document.getElementById("loaderObsArretGestion").style.display = "none";
						},
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert("Veuillez remplir tous les champs");
					}

		});
		
	// Deconnexion fonction
		$( "#deconnexion" ).on('click', function(e) {
			e.preventDefault();
			$.ajax({
				type: 'post',
				url: 'index.php?p=ajax.home.logout',
				datatype: 'json',
				success: function (json){
					if (json.statuts == 0){
						alert(json.mes);
						window.location.assign(json.direct);
					}else{
						alert(json.mes);
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});

		});


		$( ".ResetPassUser" ).on('click', function(e) {
			e.preventDefault();
			
			$('#modalResetPassUser').modal();
		});
		
		$( "#form-ResetPassUser" ).on('submit', function(e) {
        e.preventDefault();
		
		var newPassword = $('#newPasswordUser').val();
		var confirmPassword = $('#confirmNewPassword').val();
		var oldPassword = $('#oldPasswordUser').val();
		
		var url = $(this).attr('action');
		
            if (newPassword !='' && oldPassword !='' && confirmPassword !='' && url !='' && url =='index.php?p=ajax.home.resetPass') {
				
				if (newPassword == confirmPassword) {
					$.ajax({
						type: 'post',
						url: url,
						data: 'oldPassword='+oldPassword+'&newPassword='+newPassword+'&confirmPassword='+confirmPassword,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderPassReset').removeClass('hidden');
								$('.resetPassUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.resetPassUser').removeClass('hidden');
								$('.loaderPassReset').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert('Les mots de passes ne sont pas identiques');
					}

            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		});
	
	$( ".detailsArretsVerse" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsVerse').modal();
					document.getElementById("chargerArretsVerse").style.display = "none";
					document.getElementById("loaderArretsVerse").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsVerse').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArretsVerse').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsVerse").style.display = "block";
					document.getElementById("loaderArretsVerse").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
		//  javasript pour le gestion des arrets a la caisse
		$( ".AddArretCaisseLD" ).on('click', function(e) {
			e.preventDefault();

			$('#modalAjoutArretCaisseLD').modal();
		});
		
		$( "#versementLD" ).on("change", function(e) {
			e.preventDefault();
			
			var versementLD = $('#versementLD').val();
			
			if(versementLD == 'Oui'){
				
				$("#bordeLD").removeClass('hidden');
				$("#MontantLD").removeClass('hidden');
				$("#bordereauVersLD").removeClass('hidden');
				$("#montantVerseLD").removeClass('hidden');
				
			}else{
				
				$("#bordeLD").addClass('hidden');
				$("#MontantLD").addClass('hidden');
				$("#bordereauVersLD").addClass('hidden');
				$("#montantVerseLD").addClass('hidden');
				
			}
			
		});
		
	 $( "#form-ajoutArretsCaisseLD" ).on('submit', function(e) {
        e.preventDefault();
		
		//alert('Test de fonctionement 000');
		
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
		
		//alert('Test de fonctionnement');

		 
            if (url !='' && (arretCashLD !='' || arretCashLD ==0) && (arretOrangeMobileLD !='' || arretOrangeMobileLD == 0) && (arretMtnMobileLD !='' || arretMtnMobileLD == 0) 
			   && (arretTpeMobileLD !='' || arretTpeMobileLD == 0) && (arretCarteLD !='' || arretCarteLD ==0) && (arretChequeLD !='' || arretChequeLD == 0) 
			   && (arretVirementLD !='' || arretVirementLD ==0) && (complementCashLD !='' || complementCashLD == 0) && (arretInfoLD !='' || arretInfoLD == 0) &&  versementLD !='' && observationVersLD !='') {
				
				// alert('Test de fonctionnement 01');
				
				 var versement = $('#versementLD').val();
				 
					 if(versement == 'Oui'){
						
						var montantVerseLD = $('#montantVerseLD').val();
						var bordereauVersLD = $('#bordereauVersLD').val();
						
						 if (montantVerseLD !='' && bordereauVersLD !='') {
							 
								$.ajax({
									type: 'post',
									url:  url,
									data: data,
									contentType: false,
									processData: false,
									datatype: 'json',
									
									beforeSend: function () {
											$('.loaderRegister').removeClass('hidden');
											$('.connectUser').addClass('hidden');
										},
										
									success: function (json) {
										if (json.statuts == 0){
											alert(json.mes);
											window.location.reload();
										}else if(json.statuts == 2){
											alert(json.mes);
											window.location.reload();
										}else{
											alert(json.mes);
										}
									},
									
									complete: function () {
											$('.connectUser').removeClass('hidden');
											$('.loaderRegister').addClass('hidden');
											
										},
										
									error: function(jqXHR, textStatus, errorThrown){
										alert('erreur : '+errorThrown);
									}
								});
							   
							 }else{
								 
								alert('Veuillez rensiegner le montant et le bordereau de versement');
								
							}
					}else{
								 
						$.ajax({
							type: 'post',
							url:  url,
							data: data,
							contentType: false,
							processData: false,
							datatype: 'json',
							
							beforeSend: function () {
									$('.loaderRegister').removeClass('hidden');
									$('.connectUser').addClass('hidden');
								},
								
							success: function (json) {
								if (json.statuts == 0){
									alert(json.mes);
									window.location.reload();
								}else if(json.statuts == 2){
									alert(json.mes);
									window.location.reload();
								}else{
									alert(json.mes);
								}
							},
							
							complete: function () {
									$('.connectUser').removeClass('hidden');
									$('.loaderRegister').addClass('hidden');
									
								},
								
							error: function(jqXHR, textStatus, errorThrown){
								alert('erreur : '+errorThrown);
							}
						});		 
					
					}	
					
			
			}else{
				
                alert('Veuillez rensiegner tous les champs obligatoires');

            }
        
    });
	
	$( ".detailsArretsControl" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsControl').modal();
					document.getElementById("chargerArretsControl").style.display = "none";
					document.getElementById("loaderArretsControl").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsControl').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArretsControl').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsControl").style.display = "block";
					document.getElementById("loaderArretsControl").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
		$( ".detailsArretsLD" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsLD').modal();
					document.getElementById("chargerArretsLD").style.display = "none";
					document.getElementById("loaderArretsLD").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsLD').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArretsLD').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsLD").style.display = "block";
					document.getElementById("loaderArretsLD").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
		
		//  javasript pour le gestion des arrets a la caisse MAJ
	$( ".AddArretCaisseM" ).on('click', function(e) {
		e.preventDefault();

		$('#modalAjoutArretCaisseM').modal();
	});
	
	$( ".arretSupplM" ).on('click', function(e) {
		e.preventDefault();
		
		var id = $(this).data('id');
		$('#idAvarie1').val(id);
		$('#modalAjoutArretSuppl').modal();
	});
	
	$( "#form-ajoutArretsCaisseM" ).on('submit', function(e) {
        e.preventDefault();
		
			var arretCash = $('#arretCash1M').val();
			var complementCash = $('#complementCash1M').val();
			var arretOrangeMobile = $('#arretOrangeMobile1M').val();
			var arretMtnMobile = $('#arretMtnMobile1M').val();
			var arretTpeMobile = $('#arretTpeMobile1M').val();
			var arretCarte = $('#arretCarte1M').val();
			var arretCheque = $('#arretCheque1M').val();
			var arretVirement = $('#arretVirement1M').val();
			var versementPros = $('#versementProsM').val();
			var arretFictifM = $('#arretFictif1M').val();
			var arretVersementClientM = $('#arretVersementClient1M').val();
			var arretDepensesM = $('#arretDepenses1M').val();
			
			// informations supplementaires
			var arretAvarie = $('#arretAvarie1').val();
			var arretBonAchat = $('#arretBonAchat1').val();
			var arretTranfert = $('#arretTranfert1').val();
			var arretRemiseSage = $('#arretRemiseSage1').val();
			var arretGainPromo = $('#arretGainPromo1').val();
			
			// information supplementaires depenses
			var arretManutention = $('#arretManutention1M').val();
			var arretOpDirection = $('#arretOpDirection1M').val();
			var arretFraisTaxi = $('#arretFraisTaxi1M').val();
			
			var url = $(this).attr('action');
			
			if ((arretCash !='' || arretCash ==0) && (arretOrangeMobile !='' || arretOrangeMobile == 0) && (arretMtnMobile !='' || arretMtnMobile == 0) 
			   && (arretTpeMobile !='' || arretTpeMobile == 0) && (arretCarte !='' || arretCarte ==0) && (arretCheque !='' || arretCheque == 0) 
			   && (arretVirement !='' || arretVirement ==0) && (complementCash !='' || complementCash == 0) && (versementPros !='' || versementPros == 0)
			   && (arretFictifM !='' || arretFictifM == 0) && (arretVersementClientM !='' || arretVersementClientM == 0) && (arretDepensesM !='' || arretDepensesM == 0)
			   && (arretAvarie !='' || arretAvarie == 0) && (arretBonAchat !='' || arretBonAchat == 0) && (arretTranfert !='' || arretTranfert == 0)
			   && (arretRemiseSage !='' || arretRemiseSage == 0) && (arretGainPromo !='' || arretGainPromo == 0) 
			   && (arretManutention !='' || arretManutention == 0) && (arretOpDirection !='' || arretOpDirection == 0) 
			   && (arretFraisTaxi !='' || arretFraisTaxi == 0)) {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'arretCash='+arretCash+'&arretOrangeMobile='+arretOrangeMobile+'&arretMtnMobile='+arretMtnMobile+'&arretTpeMobile='
							  +arretTpeMobile+'&arretCarte='+arretCarte+'&arretCheque='+arretCheque+'&arretVirement='+arretVirement+'&complementCash='+complementCash
							  +'&versementPros='+versementPros+'&arretFictifM='+arretFictifM+'&arretVersementClientM='+arretVersementClientM+'&arretDepensesM='+arretDepensesM
							  +'&arretAvarie='+arretAvarie+'&arretBonAchat='+arretBonAchat+'&arretTranfert='+arretTranfert+'&arretRemiseSage='+arretRemiseSage
							  +'&arretGainPromo='+arretGainPromo+'&arretManutention='+arretManutention+'&arretOpDirection='+arretOpDirection+'&arretFraisTaxi='+arretFraisTaxi,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
	$( "#form-ajoutArretsSuppl" ).on('submit', function(e) {
        e.preventDefault();
		
			var idAvarie1 = $('#idAvarie1').val();
			var arretAvarie1 = $('#arretAvarie1').val();
			var arretBonAchat1 = $('#arretBonAchat1').val();
			var arretTranfert1 = $('#arretTranfert1').val();
			var arretManutention1 = $('#arretManutention1').val();
			var arretRemiseSage1 = $('#arretRemiseSage1').val();
			var arretResteFDR1 = $('#arretResteFDR1').val();
			var arretFondCaisse1 = $('#arretFondCaisse1').val();
			var arretGainPromo1 = $('#arretGainPromo1').val();
			
			var url = $(this).attr('action');
			
			if ((idAvarie1 !='' || idAvarie1 ==0) && (arretAvarie1 !='' || arretAvarie1 == 0) && (arretBonAchat1 !='' || arretBonAchat1 == 0) 
			   && (arretTranfert1 !='' || arretTranfert1 == 0) && (arretManutention1 !='' || arretManutention1 ==0) && (arretRemiseSage1 !='' || arretRemiseSage1 == 0) 
			   && (arretResteFDR1 !='' || arretResteFDR1 ==0) && (arretFondCaisse1 !='' || arretFondCaisse1 == 0) && (arretGainPromo1 !='' || arretGainPromo1 == 0)) {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'idAvarie1='+idAvarie1+'&arretAvarie1='+arretAvarie1+'&arretBonAchat1='+arretBonAchat1+'&arretTranfert1='
							  +arretTranfert1+'&arretManutention1='+arretManutention1+'&arretRemiseSage1='+arretRemiseSage1
							  +'&arretResteFDR1='+arretResteFDR1+'&arretFondCaisse1='+arretFondCaisse1+'&arretGainPromo1='+arretGainPromo1,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
		// Pour les montant non verser
		
		$( "#arretFictif1M" ).on("click", function(e) {
			e.preventDefault();
			
			//$('#modalAjoutArretCaisseM').modal();
			$('#modalAjoutArretSuppl').modal();
			
		});
		
		$( "#ValiderSuppl" ).on("click", function(e) {
			e.preventDefault();
			
			var arretAvarie = $('#arretAvarie1').val();
			var arretBonAchat = $('#arretBonAchat1').val();
			var arretTranfert = $('#arretTranfert1').val();
			var arretRemiseSage = $('#arretRemiseSage1').val();
			var arretGainPromo = $('#arretGainPromo1').val();
			//alert();
			var total = Number(arretAvarie) + Number(arretBonAchat) + Number(arretTranfert) + Number(arretRemiseSage) + Number(arretGainPromo);
			//alert();
			$('#arretFictif1M').val(total);
			
			$('#modalAjoutArretSuppl').modal('hide');
		});
		
		// Pour les montant des depenses
		
		$( "#arretDepenses1M" ).on("click", function(e) {
			e.preventDefault();
			
			//$('#modalAjoutArretCaisseM').modal();
			$('#modalAjoutArretSuppl1').modal();
			
		});
		
		$( "#ValiderSupp2" ).on("click", function(e) {
			e.preventDefault();
			
			var arretManutention = $('#arretManutention1M').val();
			var arretOpDirection = $('#arretOpDirection1M').val();
			var arretFraisTaxi = $('#arretFraisTaxi1M').val();
			//alert();
			var total = Number(arretManutention) + Number(arretOpDirection) + Number(arretFraisTaxi) ;
			//alert();
			$('#arretDepenses1M').val(total);
			
			$('#modalAjoutArretSuppl1').modal('hide');
		});
		
		// arretFictif1M
		$( ".detailsArretsM" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsCaisseM').modal();
					document.getElementById("chargerArretsCaisseM").style.display = "none";
					document.getElementById("loaderArretsCaisseM").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsCaissseM').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArretsCaisseM').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsCaisseM").style.display = "block";
					document.getElementById("loaderArretsCaisseM").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
			
// Pour les agences SAGE 100 

$( ".AddArretCaissesSage" ).on('click', function(e) {
		e.preventDefault();

		$('#modalAjoutArretCaisseSage').modal();
	});

$( "#complementCashSage1" ).on("change", function(e) {
        e.preventDefault();
		
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
    });	
	
	$( "#arretCashSage1" ).on("change", function(e) {
        e.preventDefault();
		
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
		
    });	

	$( "#arretOrangeMobileSage1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
    });
	
	$( "#arretMtnMobileSage1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
		
    });
	
	$( "#arretTpeMobileSage1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
    });
	
	$( "#arretCarteSage1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
		
    });
		
	$( "#arretChequeSage1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
		
    });
	
	$( "#totalBonProsSage1" ).on("change", function(e) {
        e.preventDefault();
        
		var arretCash = $('#arretCashSage1').val();
		var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
		var arretMtnMobile = $('#arretMtnMobileSage1').val();
		var arretTpeMobile = $('#arretTpeMobileSage1').val();
		var arretCarte = $('#arretCarteSage1').val();
		var arretCheque = $('#arretChequeSage1').val();
		var complementCash = $('#complementCashSage1').val();
		var arretVirement = $('#arretVirementSage1').val();
		var totalBonPros = $('#totalBonProsSage1').val();
		var total = Number(arretCash) + Number(arretOrangeMobile) + Number(arretMtnMobile) + Number(arretTpeMobile) + Number(arretCarte) + Number(arretCheque) + Number(complementCash) + Number(arretVirement) - Number(totalBonPros);
		
		$('#totalCaisseSage1').val(total);
		
		
    });
	
	$( "#form-ajoutArretsCaisseSageTest" ).on('submit', function(e) {
        e.preventDefault();
		
			var arretCash = $('#arretCashSage1').val();
			var complementCash = $('#complementCashSage1').val();
			var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
			var arretMtnMobile = $('#arretMtnMobileSage1').val();
			var arretTpeMobile = $('#arretTpeMobileSage1').val();
			var arretCarte = $('#arretCarteSage1').val();
			var arretCheque = $('#arretChequeSage1').val();
			var arretVirement = $('#arretVirementSage1').val();
			var totalBonPros = $('#totalBonProsSage1').val();
			
			var url = $(this).attr('action');
			
			if ((arretCash !='' || arretCash ==0) && (arretOrangeMobile !='' || arretOrangeMobile == 0) && (arretMtnMobile !='' || arretMtnMobile == 0) 
			   && (arretTpeMobile !='' || arretTpeMobile == 0) && (arretCarte !='' || arretCarte ==0) && (arretCheque !='' || arretCheque == 0) 
			   && (complementCash !='' || complementCash ==0) && (arretVirement !='' || arretVirement == 0) && (totalBonPros !='' || totalBonPros == 0)) {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'arretCash='+arretCash+'&arretOrangeMobile='+arretOrangeMobile+'&arretMtnMobile='+arretMtnMobile+'&arretTpeMobile='
							  +arretTpeMobile+'&arretCarte='+arretCarte+'&arretCheque='+arretCheque+'&complementCash='+complementCash+'&arretVirement='+arretVirement+'&totalBonPros='+totalBonPros,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
		
	// pour les chef des agences SAGE 100

		$( ".AddArretDouanierSage" ).on('click', function(e) {
		e.preventDefault();

		$('#modalAjoutArretDouanierSage').modal();
		
	});
	
	$( "#arretInfoSage" ).on("change", function(e) {
        e.preventDefault();
        
		var totalCaisseD = $('#totalCaisseDSage').val();
		var arretDouanier = $('#arretDouanierSage').val();
		var arretInfo = $('#arretInfoSage').val();
		
		var diffCaisse = Number(arretInfo) - Number(totalCaisseD);
		var diffDouanier = Number(arretInfo) - Number(arretDouanier);
		
		$('#diffCaisseSage').val(diffCaisse);
		$('#diffDouanierSage').val(diffDouanier);
		
    });
	
	$( "#arretDouanierSage" ).on("change", function(e) {
        e.preventDefault();
        
		var arretDouanier = $('#arretDouanierSage').val();
		var arretInfo = $('#arretInfoSage').val();
		
		var diffDouanier = Number(arretInfo) + Number(arretDouanier);
		
		$('#diffDouanierSage').val(diffDouanier);
		
    });
	
	$( "#versementSage" ).on("change", function(e) {
			e.preventDefault();
			
			var versement = $('#versementSage').val();
			
			if(versement == 'Oui'){
				
				$("#bordeSage").removeClass('hidden');
				$("#bordereauVersSage").removeClass('hidden');
				$("#MontantVSage").removeClass('hidden');
				$("#montantVerseSage").removeClass('hidden');
				
			}else{
				
				$("#bordeSage").addClass('hidden');
				$("#bordereauVersSage").addClass('hidden');
				$("#MontantVSage").addClass('hidden');
				$("#montantVerseSage").addClass('hidden');
				
			}
			
		});
		
	
		$( "#form-ajoutArretsDouanierSage" ).on('submit', function(e) {
        e.preventDefault();
		
        var url = $(this).attr('action');
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
		
		 
            if (url !='' && arretDouanierSage !='' && arretInfoSage !='' && obsArretChefSage !='' && versementSage !='' && observationVersSage !='') {
				
				 var versement = $('#versementSage').val();
				 
					 if(versement == 'Oui'){
						
						var montantVerse = $('#montantVerseSage').val();
						var bordereauVers = $('#bordereauVersSage').val();
						
						 if (bordereauVers !='' && montantVerse !='') {
							 
								$.ajax({
									type: 'post',
									url:  url,
									data: data,
									contentType: false,
									processData: false,
									datatype: 'json',
									
									beforeSend: function () {
											$('.loaderRegister').removeClass('hidden');
											$('.connectUser').addClass('hidden');
										},
										
									success: function (json) {
										if (json.statuts == 0){
											alert(json.mes);
											window.location.reload();
										}else if(json.statuts == 2){
											alert(json.mes);
											window.location.reload();
										}else{
											alert(json.mes);
										}
									},
									
									complete: function () {
											$('.connectUser').removeClass('hidden');
											$('.loaderRegister').addClass('hidden');
											
										},
										
									error: function(jqXHR, textStatus, errorThrown){
										alert('erreur : '+errorThrown);
									}
								});
							   
							 }else{
								 
								alert('Veuillez rensiegner le montant et le bordereau de versement');
								
							}
					}else{
								 
						$.ajax({
							type: 'post',
							url:  url,
							data: data,
							contentType: false,
							processData: false,
							datatype: 'json',
							
							beforeSend: function () {
									$('.loaderRegister').removeClass('hidden');
									$('.connectUser').addClass('hidden');
								},
								
							success: function (json) {
								if (json.statuts == 0){
									alert(json.mes);
									window.location.reload();
								}else if(json.statuts == 2){
									alert(json.mes);
									window.location.reload();
								}else{
									alert(json.mes);
								}
							},
							
							complete: function () {
									$('.connectUser').removeClass('hidden');
									$('.loaderRegister').addClass('hidden');
									
								},
								
							error: function(jqXHR, textStatus, errorThrown){
								alert('erreur : '+errorThrown);
							}
						});		 
					
					}	
					
			
			}else{
				
                alert('Veuillez rensiegner tous les champs obligatoires');

            }
        
    });
	
	
	// Les details pour les agences sage 100 
	
	$( ".detailsArretsSage" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsSage').modal();
					document.getElementById("chargerArretsSage").style.display = "none";
					document.getElementById("loaderArretsSage").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsSage').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArrets').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsSage").style.display = "block";
					document.getElementById("loaderArretsSage").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
	
		
		$( ".detailsArretsVerseSage" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsVerseSage').modal();
					document.getElementById("chargerArretsVerseSage").style.display = "none";
					document.getElementById("loaderArretsVerseSage").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsVerseSage').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArretsVerseSage').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsVerseSage").style.display = "block";
					document.getElementById("loaderArretsVerseSage").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
		
	// Les mise jour cote comptabilité des agences SAGE x3	
	$( ".miseJourCptaSage" ).on('click', function(e) {
			e.preventDefault();

			var id = $(this).data('id'),
				url = $(this).data('url');
				
				if (id !=""){
					$.ajax({
						type: 'post',
						url: url,
						data: 'id='+id,
						datatype: 'json',
						beforeSend: function () {
							$('#modal-MiseJourArretsCptaSage').modal();
							document.getElementById("chargerObsArretCptaSage").style.display = "none";
							document.getElementById("loaderObsArretCptaSage").style.display = "block";
						},
						success: function (json) {
							if(json.statuts == 0){
								$('.chargerDetailsObsArretCptaSage').html(json.content);
							}else{
								alertify.notify(json.mes,'error',5);
								$('#modal-MiseJourArretsCptaSage').hide();
							}
						},
						complete: function () {
							document.getElementById("chargerObsArretCptaSage").style.display = "block";
							document.getElementById("loaderObsArretCptaSage").style.display = "none";
						},
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert("Veuillez remplir tous les champs");
					}

		});
		
		
		$( "#form-ajoutArretsCaisseSage" ).on('submit', function(e) {
        e.preventDefault();
			alert('Test de fonctionnement');
			var arretCash = $('#arretCashSage1').val();
			var complementCash = $('#complementCashSage1').val();
			var arretOrangeMobile = $('#arretOrangeMobileSage1').val();
			var arretMtnMobile = $('#arretMtnMobileSage1').val();
			var arretTpeMobile = $('#arretTpeMobileSage1').val();
			var arretCarte = $('#arretCarteSage1').val();
			var arretCheque = $('#arretChequeSage1').val();
			var totalBonPros = $('#totalBonProsSage1').val();
			
			var url = $(this).attr('action');
			
			if ((arretCash !='' || arretCash ==0) && (arretOrangeMobile !='' || arretOrangeMobile == 0) && (arretMtnMobile !='' || arretMtnMobile == 0) 
			   && (arretTpeMobile !='' || arretTpeMobile == 0) && (arretCarte !='' || arretCarte ==0) && (arretCheque !='' || arretCheque == 0) 
			   && (complementCash !='' || complementCash ==0) && (totalBonPros !='' || totalBonPros == 0)) {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'arretCash='+arretCash+'&arretOrangeMobile='+arretOrangeMobile+'&arretMtnMobile='+arretMtnMobile+'&arretTpeMobile='
							  +arretTpeMobile+'&arretCarte='+arretCarte+'&arretCheque='+arretCheque+'&complementCash='+complementCash+'&totalBonPros='+totalBonPros,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
		$( ".miseJourControleSage" ).on('click', function(e) {
			e.preventDefault();

			var id = $(this).data('id'),
				url = $(this).data('url');
				
				if (id !=""){
					$.ajax({
						type: 'post',
						url: url,
						data: 'id='+id,
						datatype: 'json',
						beforeSend: function () {
							$('#modal-MiseJourArretControleSage').modal();
							document.getElementById("chargerArretsControleSage").style.display = "none";
							document.getElementById("loaderArretsControleSage").style.display = "block";
						},
						success: function (json) {
							if(json.statuts == 0){
								$('.chargerDetailsArretsControleSage').html(json.content);
							}else{
								alertify.notify(json.mes,'error',5);
								$('#modal-MiseJourArretControleSage').hide();
							}
						},
						complete: function () {
							document.getElementById("chargerArretsControleSage").style.display = "block";
							document.getElementById("loaderArretsControleSage").style.display = "none";
						},
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
					}else{
						alert("Veuillez remplir tous les champs");
					}

		});
		
		$( "#form-ajoutArretsControleSage" ).on('submit', function(e) {
        e.preventDefault();
		
			var idArretsCaisse = $('#idArretsCaisseSage').val();
			var controlePhys = $('#controlePhysSage').val();
			var commentaireControle = $('#commentaireControleSage').val();
			var Action1 = $('#Action1Sage').val();
			var delai1 = $('#delai1Sage').val();
			var Action2 = $('#Action2Sage').val();
			var delai2 = $('#delai2Sage').val();
			var Action3 = $('#Action3Sage').val();
			var delai3 = $('#delai3Sage').val();
			
			var url = $(this).attr('action');
			
			if (idArretsCaisse !='' && controlePhys !='' && commentaireControle !='') {
				
					$.ajax({
						type: 'post',
						url: url,
						data: 'idArretsCaisse='+idArretsCaisse+'&controlePhys='+controlePhys+'&commentaireControle='+commentaireControle+'&Action1='
							  +Action1+'&delai1='+delai1+'&Action2='+Action2+'&delai2='+delai2+'&Action3='+Action3+'&delai3='+delai3,
						datatype: 'json',
						
						beforeSend: function () {
								$('.loaderRegister').removeClass('hidden');
								$('.connectUser').addClass('hidden');
							},
							
						success: function (json) {
							if (json.statuts == 0){
								alert(json.mes);
								window.location.reload();
							}else{
								alert(json.mes);
							}
						},
						
						complete: function () {
								$('.connectUser').removeClass('hidden');
								$('.loaderRegister').addClass('hidden');
							},
							
						error: function(jqXHR, textStatus, errorThrown){
							alert('erreur : '+errorThrown);
						}
					});
					
            }else{
                alert('Veuillez rensiegner tous les champs obligatoires');
            }
		
		});
		
		
	$( ".detailsArretsControlSage" ).on('click', function(e) {
			e.preventDefault();
			var id = $(this).data('id'),
				url = $(this).data('url');
				
			$.ajax({
				type: 'post',
				url: url,
				data: 'id='+id,
				datatype: 'json',
				beforeSend: function () {
					$('#modal-detailsArretsControlSage').modal();
					document.getElementById("chargerArretsControlSage").style.display = "none";
					document.getElementById("loaderArretsControlSage").style.display = "block";
				},
				success: function (json) {
					if(json.statuts == 0){
						$('.chargerDetailsArretsControlSage').html(json.content);
					}else{
						alertify.notify(json.mes,'error',5);
						$('#modal-detailsArretsControlSage').hide();
					}
				},
				complete: function () {
					document.getElementById("chargerArretsControlSage").style.display = "block";
					document.getElementById("loaderArretsControlSage").style.display = "none";
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert('erreur : '+errorThrown);
				}
			});
		});
			
	
});
