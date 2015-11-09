<?php

require_once(dirname(__FILE__).'/../module.inc.php');

$submitted = io::post('submit');
$label = io::post('label');
//$name = io::post('name');

$errors = array();
$created = false;
if($submitted) {
	if(empty($label)) {
		$errors[] = 'Veuillez remplir tous les champs obligatoires';
	}

	if(empty($errors)) {
		$params = array(
			'method' => 'POST',
			'Name' => $label,
		);
		$response = $api->contactslist($params);
		if($api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_POST) {
			$created = true;
			$id = $response->Data[0]->ID;
		}
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
				<h1>Listes</h1>
			</div>
			<div id="breadcrumb">
				<a href="../index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
				<a href="index.php">Listes</a>
				<a href="#" class="current">Création d'une liste</a>
			</div>

			<div class="row">
				<div class="col-md-12">

					<?php if ($created): ?>
						<div class="alert alert-success">
							<strong>La liste a été créée.</strong> <a href="view.php?id=<?php echo $id ?>">Voir la liste</a>
						</div>
					<?php else: ?>
						<div class="widget-box">
						<div class="widget-title">
							<span class="icon">
								<i class="icon-th"></i>
							</span>
							<h5>Nouvelle liste</h5>
						</div>
						<div class="widget-content nopadding">
							<?php foreach ($errors as $message): ?>
								<div class="alert alert-danger">
									<button class="close" data-dismiss="alert">×</button>
									<strong><?php echo $message;?></strong>
								</div>
							<?php endforeach ?>
							<form action="" method="post" class="form-horizontal">
								<div class="form-group">
									<label class="control-label" for="label">Titre <span class="mandatory">*</span></label>
									<div class="controls">
										<input type="text" name="label" class="form-control input-sm" required value="<?php echo $label?>">
										<span class="help-block">Le titre de cette liste.</span>
									</div>
								</div>
								<div class="form-actions">
									<button type="submit" name="submit" value="1" class="btn btn-primary">Sauvegarder</button> <a class="btn btn-inverse" href="index.php">Annuler</a>
								</div>
							</form>
						</div>
					</div>
					<?php endif ?>


				</div>
			</div>

			<!-- content goes here -->
		</div>

		<?php include dirname(__FILE__).'/../partials/scripts.php'; ?>

	</body>
</html>
