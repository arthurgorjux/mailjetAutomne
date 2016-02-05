<?php

require_once(dirname(__FILE__).'/../module.inc.php');
$contactFilterId = io::get('id');

$contactFilter = $api->contactfilter(array("method" => "VIEW", "ID" => $contactFilterId));

if($contactFilter){
  $contactFilter = $contactFilter->Data[0];
}

$submitted = io::post('submit');

$errors = array();
$deleted = false;
if($submitted) {
  $contactFilterId = io::post('id');
  if(empty($errors)) {
    $response = $api->contactfilter(array('method' => 'DELETE', 'ID'=> $contactFilterId));
    echo '<pre>';
    var_dump($api);
    echo '</pre>';
    if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_DELETE) {
      $deleted = true;
    }
  }
}
else {
  if($contactFilter) {
    $name = $contactFilter->Name;
    $description = ($contactFilter->Description != '') ? $contactFilter->Description : '-';
    $expression = $contactFilter->Expression;
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

      <?php if($contactFilter):?>
        <div class="row">
          <div class="col-md-12 form-container">
            <?php if ($deleted): ?>
              <div class="alert alert-success">
                <strong>Le segment a été supprimé.</strong> <a href="index.php">Retour</a>
              </div>
            <?php else: ?>
              <div class="widget-box">
                <div class="widget-title">
                  <span class="icon">
                    <i class="icon-th"></i>
                  </span>
                  <h5>Suppression de segment</h5>
                </div>
                <div class="widget-content nopadding">
                  <?php foreach ($errors as $message): ?>
                    <div class="alert alert-danger">
                      <button class="close" data-dismiss="alert">×</button>
                      <strong><?php echo $message;?></strong>
                    </div>
                  <?php endforeach ?>
                  <form action="" method="post" class="form-horizontal">
                    <p style="padding: 10px;">
                      <strong>Nom : </strong><?php echo $name?><br />
                      <strong>Description : </strong><?php echo $description?><br />
                      <strong>Expression : </strong><?php echo $expression?><br />
                    </p>
                    <div class="form-actions">
                      <input type="hidden" name="id" value="<?php echo $contactFilterId ?>">
                      <button type="submit" name="submit" value="1" class="btn btn-primary">Etes-vous sûr de vouloir supprimer ce segment ?</button> <a class="btn btn-inverse" href="index.php">Retour</a>
                    </div>
                  </form>
                </div>
              </div>
            <?php endif ?>
          </div>
        </div>
      <?php else:?>
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-danger">
              <button class="close" data-dismiss="alert">×</button>
              <strong>Erreur!</strong> Récupération des informations impossible.
            </div>
          </div>
        </div>
      <?php endif;?>
    </div>
    <?php include dirname(__FILE__).'/../partials/scripts.php'; ?>
  </body>
</html>
