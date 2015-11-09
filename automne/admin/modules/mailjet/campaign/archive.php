<?php

require_once(dirname(__FILE__).'/../module.inc.php');


$campaignId = io::get('id');
$campaign = $api->campaign(array("CampaignID" => $campaignId));
if(empty($campaign->Data)){
  $newsletter = $api->newsletter(array("method" => "VIEW", "ID" => $campaignId));
  if(isset($api->_response_code) && $api->_response_code === 200){
    $newsletter = $newsletter->Data[0];
  }
}else{
  $newsletterId = $campaign->Data[0]->NewsLetterID;
  $newsletter = $api->newsletter(array("method" => "VIEW", "ID" => $newsletterId));
  if(isset($api->_response_code) && $api->_response_code === 200){
    $newsletter = $newsletter->Data[0];
  }  
}

$listId = io::get('id');

$stats = $api->listsStatistics(array('id'=> $listId));

$submitted = io::post('submit');

$errors = array();
$archived = false;
if($submitted) {
  $listId = io::post('id');
  if(empty($errors)) {
    $params = array(
      'method' => 'PUT',
      'unique' => $newsletter->ID,
      'Status' => '-1', // Status -1 => Archived
    );
    $response = $api->newsletter($params);
    if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_PUT) {
      $archived = true;
      $id = $response->Data[0]->ID;
    }
    elseif (isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_ERROR) {
      $msg = 'Erreur pendant l\' archivage de la campagne.';
      $errors[] = $msg;
    }
    else {
      $errors[] = 'Erreur interne pendant l\'archivage de la campagne.';
    }
  }
}
else {
  if($newsletter) {
    $title = $newsletter->Title;
    $subject = $newsletter->Subject;
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
        <a href="index.php">Campagnes</a>
        <a href="view.php?id=<?php echo $campaignId ?>"><?php echo $newsletter->Title?></a>
        <a href="#" class="current">Archivage</a>
      </div>

      <?php if($newsletter || $submitted):?>
        <div class="row">
          <div class="col-md-12 form-container">
            <?php if ($archived): ?>
              <div class="alert alert-success">
                <strong>La campagne a été archivée.</strong> <a href="index.php">Retour</a>
              </div>
            <?php else: ?>
              <div class="widget-box">
                <div class="widget-title">
                  <span class="icon">
                    <i class="icon-th"></i>
                  </span>
                  <h5>Archivage de campagne</h5>
                </div>
                <div class="widget-content nopadding">
                  <?php foreach ($errors as $message): ?>
                    <div class="alert alert-danger">
                      <button class="close" data-dismiss="alert">×</button>
                      <strong><?php echo $message;?>
                    </div>
                  <?php endforeach ?>
                  <form action="" method="post" class="form-horizontal">
                    <p style="padding: 10px;">
                      <strong>Titre : </strong><?php echo $title?><br />
                      <strong>Sujet : </strong><?php echo $subject?><br />
                    </p>
                    <div class="form-actions">
                      <input type="hidden" name="id" value="<?php echo $listId ?>">
                      <button type="submit" name="submit" value="1" class="btn btn-primary">Etes-vous sûr de vouloir archiver cette campagne ?</button> <a class="btn btn-inverse" href="index.php">Retour</a>
                    </div>
                  </form>
                </div>
              </div>
            <?php endif ?>
          </div><!-- end .form-container -->
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
    </div> <!-- end #content  -->
    <?php include dirname(__FILE__).'/../partials/scripts.php'; ?>
  </body>
</html>
