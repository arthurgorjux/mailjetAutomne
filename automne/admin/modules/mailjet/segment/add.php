<?php

require_once(dirname(__FILE__).'/../module.inc.php');
$submitted = io::post('submit');
$name = io::post('name');
$description = io::post('description');
$property = io::post('property');
$expression = io::post('expression');
$mapping = array();

//get all properties (contact metadata)
$allContactMetadata = $api->contactmetadata(array('Limit' => '-1'));
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET){
	$properties = array();
	foreach ($allContactMetadata->Data as $key => $oProperty) {
		$properties[$oProperty->Name] = $oProperty->Name;
		$mapping[$oProperty->Name] = $oProperty->Datatype;
	}
}

$errors = array();
$created = false;
if($submitted) {
	if(empty($name) || empty($expression)) {
		$errors[] = 'Veuillez remplir tous les champs obligatoires';
	}

	$typeProperty = $mapping[$property];
	switch ($typeProperty) {
		case 'str':
			$expression = '"' . $expression . '"';
			break;
		case 'int':
			if($expression !== '0' && intval($expression) === 0){
				$msg = 'Erreur, le type entier ne prend que des chiffres entiers.';
				$errors[] = $msg;
			}else{
				$expression = intval($expression);
			}			
			break;
		case 'float':
		if($expression !== '0' && floatval($expression) === 0){
				$msg = 'Erreur, le type décimal ne prend que des chiffres décimaux.';
				$errors[] = $msg;
			}else{
				$expression = floatval(str_replace(',', '.', $expression));
			}	
			break;
		case 'bool':
			$checkExpression = strtolower($expression);
			switch ($checkExpression) {
				case 'vrai':
					$expression = true;
					break;
				case 'faux':
					$expression = false;
					break;
				default:
					$msg = 'Erreur, le type booléen ne prend que vrai ou faux comme valeur.';
					$errors[] = $msg;
					break;
			}
			break;
	}

	if(empty($errors)) {
		$exp = $property . '=' . $expression;
		$params = array(
			'method' => 'POST',
			'Description' => $description,
			'Expression' => $exp,
			'Name' => $name,
		);

		$response = $api->contactfilter($params);
		if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_POST) {
			$created = true;
			$id = $response->Data[0]->ID;			
		}elseif (isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_ERROR) {
		 	$msg = 'Erreur pendant la création du segment. ';
		 	$errors[] = $msg;
		}else {
			$errors[] = 'Erreur interne pendant la création du segment.';
		}
	}
}else{
	$name = '';
	$description = '';
	$property = '';
	$expression = '';
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
				<a href="index.php">Segments</a>
				<a href="#" class="current">Création d'un segment</a>
			</div>

			<div class="row">
				<div class="col-md-12">
					<?php if ($created): ?>
						<div class="alert alert-success">
							<strong>Le segment a été créé.</strong> <a href="view.php?id=<?php echo $id ?>">Voir le segment</a>
						</div>
					<?php else: ?>
						<div class="widget-box">
						<div class="widget-title">
							<span class="icon">
								<i class="icon-th"></i>
							</span>
							<h5>Nouveau segment</h5>
						</div>
						<div class="widget-content nopadding">
							<?php foreach ($errors as $message): ?>
								<div class="alert alert-danger">
									<button class="close" data-dismiss="alert">x</button>
									<strong><?php echo $message;?></strong>
								</div>
							<?php endforeach ?>
							<form action="" method="post" class="form-horizontal">
								<div class="form-group">
									<label class="control-label" for="name">Titre <span class="mandatory">*</span></label>
									<div class="controls">
										<input type="text" name="name" class="form-control input-sm" required value="<?php echo $name?>">
										<span class="help-block">Le nom du segment.</span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label" for="description">Description</label>
									<div class="controls">
										<input type="text" name="description" class="form-control input-sm" value="<?php echo $description?>">
										<span class="help-block">La description du segment.</span>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label" for="expression">Expression <span class="mandatory">*</span></label>
									<div class="controls">
										<div class="col-md-6">
											<select name="property" class="form-control">
												<?php echo CMS_module_mailjet::buildOptions($properties,$property);?>
											</select>
											<span class="help-block">L'expression du segment.
										</div>
										<div class="col-md-6">	
											<input type="text" name="expression" class="form-control input-sm" required value="<?php echo $expression?>"/>
										</div>										
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
		<?php include dirname(__FILE__).'/../partials/scripts.php'; ?>
	</body>
</html>