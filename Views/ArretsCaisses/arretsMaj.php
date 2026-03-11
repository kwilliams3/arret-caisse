<?php
use Core\Model\App;
use Core\Model\Session;
use Core\Database\Agence;

$auth = App::getDBAuth();
$session = Session::getInstance();

$user = $_SESSION['user'];

//var_dump ($articles);
?>
 <div class="breadcrumbs ace-save-state" id="breadcrumbs">
		<ul class="breadcrumb">
			<li>
				<i class="ace-icon fa fa-home home-icon"></i>
				<a href="#">Arrets Caisses</a>
			</li>
		</ul><!-- /.breadcrumb -->
	</div>
 
    <div class="page-header">
		<h1>
			Liste des arrets caisses
			
			<?php
				$privileges = 'Caissiere';
				if(in_array($user['privilege'] ,explode(',',$privileges))) {
					
					$stmtAgence = Agence::searchById($user['agence']);
						$agence = array();
						while ($result2= sqlsrv_fetch_array($stmtAgence, SQLSRV_FETCH_ASSOC)) {
							$agence = array(
								"id" => $result2['idAgence'],
								"designation" => $result2['designation'],
							);
						}
						
					echo $agence['designation'];
					
				}
			?>
			
			
		</h1>
	</div><!-- /.page-header -->
	
	<?php
		$privileges = 'Caissiere,SuperAdministration,Administration';
		if(in_array($user['privilege'] ,explode(',',$privileges))) {
	?>
		<div class="row" style="padding-bottom: 10px;">
			<div class="col-md-offset-10 col-md-2">
				<a href="#" role="button" class="AddArretCaisseM green btn btn-success btn-labeled fa fa-plus" data-toggle="modal"> Ajouter </a>
			</div>
		</div>
	<?php
		}
		?>

  <div class="row" style="padding-top: 10px">
    <div class="col-xs-12">

        <div class="table-header">
           Liste des arrets caisses
        </div>

        <!-- div.table-responsive -->

        <!-- div.dataTables_borderWrap -->
        <div>
            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                <thead>
					<tr>	
						<th> Date Entree </th>
						<th> Arret Cash / Complement </th>
						<th> Arret Orange / MTN </th>
						<th> Arret Carte / Virement</th>
						<th> Versement Pros / Total Caisse</th>
						<th>
							Agence
						</th>
						<th> Actions </th>
					</tr>
                </thead>

                <tbody>
                <?php
                if (!empty($arretsCaisses)){
					$date = date('d-m-Y');
					
                    for($i = 0; $i < sizeof($arretsCaisses);$i++){
						 
					 $date1 = date('d-m-Y',date_timestamp_get($arretsCaisses[$i]['dateEntree']));
						 
                        ?>
                        <tr>
                            <td class="center">
                                <?= date('d-m-Y',date_timestamp_get($arretsCaisses[$i]['dateEntree'])) ?>
                            </td>
                            <td> <?= $arretsCaisses[$i]['arretCashCaisse'].' / '.$arretsCaisses[$i]['arretComplementCaisse']  ?> </td>
                            <td> <?= $arretsCaisses[$i]['arretOrangeCaisse'].' / '.$arretsCaisses[$i]['arretMtnCaisse'] ?> </td>
                            <td> <?= $arretsCaisses[$i]['arretCarteCaisse'].' / '.$arretsCaisses[$i]['arretVirementCaisse']  ?> </td>
                            <td> <?= $arretsCaisses[$i]['versementPros'].' / '.$arretsCaisses[$i]['totalCaisse'] ?> </td>
							<td> <?= $arretsCaisses[$i]['agence'] ?> </td>
							<td> <a class="primary detailsArretsM" href="#" data-url="<?= App::url('ajax.arretsCaisses.detailsArretsM') ?>" data-id="<?= $arretsCaisses[$i]['idArretsCaissesM'];?>" title="Afficher plus de details sur cette ligne">
									<i class="ace-icon fa fa-info-circle bigger-150"></i>
								</a>&nbsp; 	
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    
<div id="modalAjoutArretCaisseM" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class=" modal-content">
            <div class="modal-header no-padding">
                <div class="table-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <span class="white">&times;</span>
                    </button>
                    Ajout Arret Caisse MAJ
                </div>
            </div>

            <div class="modal-body">
                <form action="<?= App::url('ajax.arretsCaisses.ajoutArretsCaisseM') ?>" method="POST" id="form-ajoutArretsCaisseM">
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="complementCash1M" name="complementCashM" placeholder="Complement en caisse" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretCash1M" name="arretCashM" placeholder="Arret Cash" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>

					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretOrangeMobile1M" name="arretOrangeMobileM" placeholder="Arret Orange Money" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretMtnMobile1M" name="arretMtnMobileM" placeholder="Arret MTN Money" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretTpeMobile1M" name="arretTpeMobileM" placeholder="Arret TPE Money" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretCarte1M" name="arretCarteM" placeholder="Arret Carte Sorepco" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretCheque1M" name="arretChequeM" placeholder="Arret Cheque" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretVirement1M" name="arretVirementM" placeholder="Arret Virement" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretFictif1M" name="arretFictifM" placeholder="Arret Montant non Verser" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretVersementClient1M" name="arretVersementClientM" placeholder="Arret Versement client banque" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretDepenses1M" name="arretDepensesM" placeholder="Arret Depenses" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="versementProsM" name="versementProsM" placeholder="Bon Prospecteur" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<div class="space-24"></div>

					<div class="clearfix resetUser">
						<button type="submit" class="width-50 pull-right btn btn-sm btn-primary">
							Valider
						</button>
					</div>
					
					<div class="clearfix hidden loaderReset">
						<center>
							<h2 class="header smaller lighter grey">
								<i class="ace-icon fa fa-spinner fa-spin green bigger-125"></i>
							</h2>
						</center>
					</div>

                </form>
            </div>
            <div class="modal-footer ">
                <button class="btn btn-sm btn-danger pull-right" data-dismiss="modal">
                    <i class="ace-icon fa fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<div id="modalAjoutArretSuppl" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class=" modal-content">
            <div class="modal-header no-padding">
                <div class="table-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <span class="white">&times;</span>
                    </button>
                   Complement des montant non verser
                </div>
            </div>

            <div class="modal-body">
                <form action="#" method="POST" id="form-ajoutArretsSuppl">
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretAvarie1" name="arretAvarie" placeholder="Arrets Avaries" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretBonAchat1" name="arretBonAchat" placeholder="Arrets Bon d'achat" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretTranfert1" name="arretTranfert" placeholder="Arrets Transfert Carte" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretGainPromo1" name="arretGainPromo" placeholder="Arrets Gain Promo" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretRemiseSage1" name="arretRemiseSage" placeholder="Arrets Remise SAGE X3" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<div class="space-24"></div>

					<div class="clearfix resetUser">
						<button type="submit" id="ValiderSuppl" class="width-50 pull-right btn btn-sm btn-primary">
							Valider
						</button>
					</div>
					
					<div class="clearfix hidden loaderReset">
						<center>
							<h2 class="header smaller lighter grey">
								<i class="ace-icon fa fa-spinner fa-spin green bigger-125"></i>
							</h2>
						</center>
					</div>

                </form>
            </div>
            <div class="modal-footer ">
                <button class="btn btn-sm btn-danger pull-right" data-dismiss="modal">
                    <i class="ace-icon fa fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<div id="modalAjoutArretSuppl1" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class=" modal-content">
            <div class="modal-header no-padding">
                <div class="table-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <span class="white">&times;</span>
                    </button>
                   Complement des depenses
                </div>
            </div>

            <div class="modal-body">
                <form action="#" method="POST" id="form-ajoutArretsSuppl">
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretManutention1M" name="arretManutentionM" placeholder="Manutention" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretOpDirection1M" name="arretOpDirectionM" placeholder="Op Direction" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>

					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretFraisTaxi1M" name="arretFraisTaxiM" placeholder="Frais de taxi" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<div class="space-24"></div>

					<div class="clearfix resetUser">
						<button type="submit" id="ValiderSupp2" class="width-50 pull-right btn btn-sm btn-primary">
							Valider
						</button>
					</div>
					
					<div class="clearfix hidden loaderReset">
						<center>
							<h2 class="header smaller lighter grey">
								<i class="ace-icon fa fa-spinner fa-spin green bigger-125"></i>
							</h2>
						</center>
					</div>

                </form>
            </div>
            <div class="modal-footer ">
                <button class="btn btn-sm btn-danger pull-right" data-dismiss="modal">
                    <i class="ace-icon fa fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<div id="modal-detailsArretsCaisseM" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header no-padding">
                <div class="table-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <span class="white">&times;</span>
                    </button>
                    Afficher les details sur l'arret
                </div>
            </div>

            <div class="modal-body">

                <div id="chargerArretsCaisseM" class="row chargerDetailsArretsCaissseM" style="display:block;">

                </div>
                <div id="loaderArretsCaisseM" class="panel loaderDetailsArretsCaisseM mar-no" style="display:none;">
                    <div class="panel-body">
                        <p class="text-center mar-no">Chargement en cours...</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer no-margin-top">
                <button class="btn btn-sm btn-danger pull-right" data-dismiss="modal">
                    <i class="ace-icon fa fa-times"></i>
                    Close
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

