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
		$privileges = 'Administration,Caissiere,SuperAdministration';
		if(in_array($user['privilege'] ,explode(',',$privileges))) {
	?>
		<div class="row" style="padding-bottom: 10px;">
			<div class="col-md-offset-10 col-md-2">
				<a href="#" role="button" class="AddArretCaisse green btn btn-success btn-labeled fa fa-plus" data-toggle="modal"> Ajouter </a>
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
						<th> Arret Cheque / TPE</th>
						<th> Versement Pros / Total Caisse</th>
						<th>
							Agence
						</th>
					</tr>
                </thead>

                <tbody>
                <?php
                if (!empty($arretsCaisses)){
                    for($i = 0; $i < sizeof($arretsCaisses);$i++){
						 
                        ?>
                        <tr>
                            <td class="center">
                                <?= date('d-m-Y',date_timestamp_get($arretsCaisses[$i]['dateEntree'])) ?>
                            </td>
                            <td> <?= $arretsCaisses[$i]['arretCashCaisse'].' / '.$arretsCaisses[$i]['arretComplementCaisse']  ?> </td>
                            <td> <?= $arretsCaisses[$i]['arretOrangeCaisse'].' / '.$arretsCaisses[$i]['arretMtnCaisse'] ?> </td>
                            <td> <?= $arretsCaisses[$i]['arretCarteCaisse'].' / '.$arretsCaisses[$i]['arretVirementCaisse']  ?> </td>
                            <td> <?= $arretsCaisses[$i]['arretChequeCaisse'].' / '.$arretsCaisses[$i]['arretTpeCaisse'] ?> </td>
                            <td> <?= $arretsCaisses[$i]['versementPros'].' / '.$arretsCaisses[$i]['totalCaisse'] ?> </td>
							<td> <?= $arretsCaisses[$i]['agence'] ?> </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    
<div id="modalAjoutArretCaisse" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class=" modal-content">
            <div class="modal-header no-padding">
                <div class="table-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <span class="white">&times;</span>
                    </button>
                    Ajout Arret Caisse
                </div>
            </div>

            <div class="modal-body">
                <form action="<?= App::url('ajax.arretsCaisses.ajoutArretsCaisse') ?>" method="POST" id="form-ajoutArretsCaisse">
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="complementCash1" name="complementCash" placeholder="Complement en caisse" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretCash1" name="arretCash" placeholder="Arret Cash" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>

					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretOrangeMobile1" name="arretOrangeMobile" placeholder="Arret Orange Money" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretMtnMobile1" name="arretMtnMobile" placeholder="Arret MTN Money" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretTpeMobile1" name="arretTpeMobile" placeholder="Arret TPE Money" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretCarte1" name="arretCarte" placeholder="Arret Carte Sorepco" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretCheque1" name="arretCheque" placeholder="Arret Cheque" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="arretVirement1" name="arretVirement" placeholder="Arret Virement/Versement client Banque" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
					<label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="versementPros" name="versementPros" placeholder="Total Bons Prospecteur" required />
							<i class="ace-icon fa fa-Money"></i>
						</span>
					</label>
					
				    <label class="block clearfix">
						<span class="block input-icon input-icon-right">
							<input type="number" class="form-control" id="totalCaisse" name="totalCaisse1" placeholder="Total Caisse" readonly />
							<i class="ace-icon fa fa-Cash"></i>
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


