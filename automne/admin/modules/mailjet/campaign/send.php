<?php

require_once(dirname(__FILE__).'/../module.inc.php');


$campaignId = io::get('id');
$newsletter = null;
$response = $api->newsletter(array('method' => 'VIEW', 'ID' => $campaignId));
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_VIEW){
  $newsletter = $response->Data[0];
}
$date = '';
$time = '';
$programmedErrors = array();
$immediateErrors = array();
$archived = false;
$programmedSuccess = false;
$sent = false;

// get the html as known by Mailjet
$params = array(
  'method' => 'VIEW',
  'ID' => $campaignId,
);
$response = $api->newsletterDetailContent($params);
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_VIEW){
  $attribut = 'Html-part';
  $mailjetHtml = $response->Data[0]->$attribut;
}

$params = array(
  'method' => 'POST',
  'ID' => $campaignId,
);

$immediateSending = io::post('immediateSending');
if($immediateSending){
  $response = $api->newsletterSend($params);
  if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_POST){
    $sent = true;
  }elseif(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_ERROR){
    $msg = 'Erreur pendant la programmation de l\'envoi.';
    $immediateErrors[] = $msg;
  }else{
    $immediateErrors[] = 'Erreur interne pendant la programmation de l\'envoi.';
  }
}

$programmed = io::post('programmedSending');
if($programmed){
  $date = io::post('date_submit');
  $time = io::post('time');
  if(empty($date) || empty($time)){
    $programmedErrors[] = 'Veuillez remplir la date et l\'heure';
  }
  if(empty($programmedErrors)){
     $dateProgrammed = date_create($date.' '.$time)->format('c');
   
    // now we can programme the newsletter sending
    $params = array(
      'method' => 'POST',
      'ID' => $campaignId,
      'Date' => $dateProgrammed,
    );
    $response = $api->newsletterSchedule($params);
    if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_POST){
      $programmedSuccess = true;
    }elseif(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_ERROR){
      $msg = 'Erreur pendant la programmation de l\'envoi.';
      $programmedErrors[] = $msg;
    }else{
      $programmedErrors[] = 'Erreur interne pendant la programmation de l\'envoi.';
    }

  }
}



/*if($immediateSending) {
  $params = array(
    'method' => 'POST',
    'id' => $campaignId
  );

  $response = $api->messageSendCampaign($params);
  if(isset($response->status) && $response->status == "OK") {
    $sent = true;
  }
  elseif (isset($response->status) && isset($response->errors)) {
    $msg = 'Erreur pendant la programmation de l\'envoi. Voici les messages en provenance de Mailjet :';
    $msg .= '<ul>';
    foreach ($response->errors as $key => $value) {
      $msg .= '<li>' . $key . ' : ' . $value .'</li>';
    }
    $msg .= '</ul>';
    $immediateErrors[] = $msg;
  }
  else {
    $immediateErrors[] = 'Erreur interne pendant la programmation de l\'envoi.';
  }
}*/

/*if($programmed) {
  $date = io::post('date_submit');
  $time = io::post('time');
  if(empty($date) || empty($time)) {
    $programmedErrors[] = 'Veuillez remplir la date et l\'heure';
  }
  if(empty($programmedErrors)) {

    $d = DateTime::createFromFormat("Y-m-d H:i", $date.' '.$time);
    $timestamp = $d->format("U");

    $params = array(
      'method' => 'POST',
      'id' => $campaignId,
      'title' => $campaign->title,
      'subject' => $campaign->subject,
      'list_id' => $campaign->list_id,
      'lang' => substr($campaign->locale, 0, 2),
      'from' => $campaign->sender_email,
      'from_name' => $campaign->sender_name,
      'footer' => $campaign->footer,
      'permalink' => $campaign->permalink,
      'sending_date' => $timestamp
    );
    $response = $api->messageUpdatecampaign($params);
    if(isset($response->status) && $response->status == "OK") {
      $programmedSuccess = true;
    }
    elseif (isset($response->status) && isset($response->errors)) {
      $msg = 'Erreur pendant la programmation de l\'envoi. Voici les messages en provenance de Mailjet :';
      $msg .= '<ul>';
      foreach ($response->errors as $key => $value) {
        $msg .= '<li>' . $key . ' : ' . $value .'</li>';
      }
      $msg .= '</ul>';
      $programmedErrors[] = $msg;
    }
    else {
      $programmedErrors[] = 'Erreur interne pendant la programmation de l\'envoi.';
    }
  }

}*/
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
        <a href="#" class="current">Envoi</a>
      </div>
      <?php if($newsletter):?>
        <?php if(empty($mailjetHtml)):?>
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-danger">
                <strong>Erreur!</strong> Aucun contenu HTML n'est prêt à être envoyé du coté de Mailjet.
              </div>
            </div>
          </div>
        <?php else:?>
          <div class="row">
            <div class="col-md-6 page-selector-container">
              <div class="widget-box">
                <div class="widget-title">
                  <span class="icon">
                    <i class="icon-th"></i>
                  </span>
                  <h5>Envoi immédiat</h5>
                </div>
                <div class="widget-content nopadding">
                  <form action="" method="post" class="form-horizontal">
                    <?php if($sent):?>
                      <div class="alert alert-success">
                        <strong>La campagne a été envoyée avec succès.</strong>
                      </div>
                    <?php endif;?>
                    <?php foreach ($immediateErrors as $message): ?>
                      <div class="alert alert-danger">
                        <strong><?php echo $message;?></strong>
                      </div>
                    <?php endforeach ?>
                    <div class="form-actions">
                      <input type="hidden" name="id" value="<?php echo $campaignId ?>">
                      <button type="submit" name="immediateSending" value="1" class="btn btn-primary">Envoyer maintenant</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-md-6 page-selector-container">
              <div class="widget-box">
                <div class="widget-title">
                  <span class="icon">
                    <i class="icon-th"></i>
                  </span>
                  <h5>Envoi différé</h5>
                </div>
                <div class="widget-content nopadding">
                  <form action="" method="post" class="form-horizontal">
                    <?php foreach ($programmedErrors as $message): ?>
                      <div class="alert alert-danger">
                        <strong><?php echo $message;?></strong>
                      </div>
                    <?php endforeach ?>
                    <?php if($programmedSuccess):?>
                      <div class="alert alert-success">
                        <strong>L'envoi a été programmé avec succès.</strong>
                      </div>
                    <?php endif;?>
                    <div class="form-group">
                      <label class="control-label" for="date">Date d'envoi <span class="mandatory">*</span></label>
                      <div class="controls">
                        <input type="text" name="date" class="form-control input-sm" required value="<?php echo $date?>" id="date">
                        <span class="help-block">La date à laquelle l'email sera envoyé</span>
                      </div>
                    </div>
                     <div class="form-group">
                      <label class="control-label" for="time">Heure d'envoi <span class="mandatory">*</span></label>
                      <div class="controls">
                        <input type="text" name="time" class="form-control input-sm" required value="<?php echo $time?>" id="time">
                        <span class="help-block">L'heure à laquelle l'email sera envoyé</span>
                      </div>
                    </div>
                    <div class="form-actions">
                      <input type="hidden" name="id" value="<?php echo $campaignId ?>">
                      <button type="submit" name="programmedSending" value="1" class="btn btn-primary">Programmer l'envoi</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endif;?>
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
    <script src="/automne/admin/modules/mailjet/assets/js/pickadate/picker.js"></script>
    <script src="/automne/admin/modules/mailjet/assets/js/pickadate/picker.date.js"></script>
    <script src="/automne/admin/modules/mailjet/assets/js/pickadate/picker.time.js"></script>
    <script src="/automne/admin/modules/mailjet/assets/js/pickadate/legacy.js"></script>fr_FR
    <script src="/automne/admin/modules/mailjet/assets/js/pickadate/translations/fr_FR.js"></script>
    <script src="/automne/admin/modules/mailjet/assets/js/min/send.min.js"></script>

  </body>
</html>
