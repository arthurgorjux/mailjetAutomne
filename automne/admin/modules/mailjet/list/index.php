<?php

require_once(dirname(__FILE__).'/../module.inc.php');
$response = $api->contactslist(array('Limit' => '-1', 'method' => 'GET'));
$lists = $response->Data;

function sortListName($a, $b){
	return strnatcasecmp($a->Name, $b->Name);
}

usort($lists, 'sortListName'); // use usort function because we cannot sort data with the API

?>
<!DOCTYPE html>
<html lang="en">
	<?php include dirname(__FILE__).'/../partials/head.php'; ?>

	<body>
		<div id="header">
			<h1><a href="index.php"><?php echo $cms_module->getLabel($cms_language); ?></a></h1>
			<a id="menu-trigger" href="#"><i class="icon-align-justify"></i></a>
		</div>
		<?php include dirname(__FILE__).'/../partials/sidebar.php'; ?>


		<div id="content">
			<div id="content-header">
				<h1>Listes</h1>
			</div>
			<div id="breadcrumb">
				<a href="../index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
				<a href="#" class="current">Listes</a>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="btn-group">
						<a class="btn btn-primary" href="add.php"><i class="icon-file"></i> Créer une nouvelle liste</a>

					</div>
				<?php if($api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET):?>
				<div class="widget-box">
					<div class="widget-content nopadding table-responsive">
						<table class="table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th>Addresse</th>
									<th>Nom</th>
									<th>Abonnés</th>
									<th>Date de création</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($lists as $list): ?>
								<tr>
									<td><?php echo $list->Address?></td>
									<td><?php echo $list->Name?></td>
									<td><?php echo $list->SubscriberCount?></td>
									<td><?php echo date_create($list->CreatedAt)->format('d/m/Y')?></td>
									<td>
										<a href="view.php?id=<?php echo $list->ID;?>" class="btn btn-primary btn-xs"><i class="icon icon-edit"></i> Modifier</a>
										<a href="delete.php?id=<?php echo $list->ID;?>" class="btn btn-danger btn-xs"><i class="icon icon-remove"></i> Effacer</a>
									</td>
								</tr>
							<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php else:?>
					<div class="alert alert-danger">
						<button class="close" data-dismiss="alert">×</button>
						<strong>Erreur!</strong> Récupération des listes impossible.
					</div>
				<?php endif;?>
			</div>
			</div>

			<!-- content goes here -->
		</div>

		<?php include dirname(__FILE__).'/../partials/scripts.php'; ?>

	</body>
</html>
