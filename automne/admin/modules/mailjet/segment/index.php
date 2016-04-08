<?php
require_once(dirname(__FILE__).'/../module.inc.php');

$allSegments = $api->contactfilter(array('Limit' => '-1'));
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET){
	$segments = array();
	foreach($allSegments->Data as $key => $segment){
		$segments[] = $segment;
	}
}
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
				<h1>Segments</h1>
			</div>
			<div id="breadcrumb">
				<a href="../index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
				<a href="#" class="current">Segments</a>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="btn-group">
						<a class="btn btn-primary" href="add.php"><i class="icon-file"></i> Créer un nouveau segment</a>
					</div>
					<?php if(!empty($segments)) : ?>
					<div class="widget-box">
						<div class="widget-content nopadding table-responsive">
							<table class="table table-bordered table-striped table-hover">
								<thead>
									<tr>
										<th>Nom</th>
										<th>Description</th>
										<th>Expression</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($segments as $segment): ?>
										<tr>
											<td><?php echo $segment->Name;?></td>
											<td><?php echo ($segment->Description !== '') ? $segment->Description : '-';?></td>
											<td><?php echo $segment->Expression;?></td>
											<td>
												<a href="view.php?id=<?php echo $segment->ID;?>" class="btn btn-primary btn-xs"><i class="icon icon-edit"></i> Modifier</a>
												<?php if($segment->Status !== 'used'): ?>
													<a href="delete.php?id=<?php echo $segment->ID;?>" class="btn btn-danger btn-xs"><i class="icon icon-remove"></i> Effacer</a>
												<?php else: ?>
													<button disabled class="btn btn-danger btn-xs"><i class="icon icon-remove"></i> Effacer</button>
												<?php endif;?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
					<?php else: ?>
						<div class="alert alert-danger">
							<button class="close" data-dismiss="alert">×</button>
							<strong>Erreur!</strong> Récupération des segments impossible.
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php include dirname(__FILE__).'/../partials/scripts.php'; ?>
	</body>
</html>