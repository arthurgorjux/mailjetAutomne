<?php

require_once(dirname(__FILE__).'/../module.inc.php');

$campaignId = io::request('id');

$mailjetCampaign = null;
$campaign = null;
$contentErrors = array();
$errors = array();
$valid = false;
$disableActions = false;
$testMailOk = false;
$recipient = null;
$name = null;
$mailjetHtml = '';

$pageFormSubmitted = io::post('page-form');
if($pageFormSubmitted) {
  $pageId = io::post('pageId');
  $mailjetCampaign = new MailjetCampaign($campaignId);
  $result = $mailjetCampaign->setPage($pageId);
  if($result['error']) {
    $errors[] = $result['message'];
  }
  else {
    $result = $mailjetCampaign->save();
    if($result['error']) {
      $errors[] = $result['message'];
    }
    else {
      $valid = true;
    }
  }
}
else {
  $mailjetCampaign = new MailjetCampaign($campaignId);
  $pageId = $mailjetCampaign->getPage();
  $valid = $pageId !== null;
}

//$campaignId = "4618244234";
$response = $api->newsletter(array('method' => 'VIEW', 'ID'=> $campaignId));
if($response){
  if (isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_VIEW) {
    $campaign = $response->Data[0];
  }
}else{ // case, id is not a newsletter's id but a campaign's id
  $response = $api->campaign(array('method' => 'VIEW', 'ID' => $campaignId));
  if (isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_VIEW) {
    $campaign = $api->newsletter(array('method' => 'VIEW', 'ID' => $response->Data[0]->NewsLetterID))->Data[0];
  }
}

if($pageId && $valid) {
  $contentErrors = CMS_module_mailjet::checkNewsletterContent($pageId);
  $email = CMS_module_mailjet::getNewsletterContent($pageId);
  $htmlVersion = $email['content'];
  $textVersion = HtmlToText::convert_html_to_text($htmlVersion);
}
if(!$valid || count($contentErrors) > 0) {
  $disableActions = true;
}

$validateContentSubmitted = io::post('validate-content');
if($validateContentSubmitted) {

  $params = array(
    'method' => 'PUT',
    'ID' => $campaignId,
    'Html-part' => $htmlVersion,
    'Text-part' => $textVersion
  );
  $response = $api->newsletterDetailContent($params);
  if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_VIEW) {
    $contentOk = true;
  }
  elseif (isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_ERROR) {
    $errors[] = 'Erreur pendant l\'envoi du code source de l\'email à Mailjet.';
  }
  else {
    $errors[] = 'Erreur interne pendant l\'envoi du code source de l\'email à Mailjet.';
  }
}

$testEmail = io::post('test-email');
if($testEmail) {
  $recipient = io::post('test-recipient');
  $name = io::post('name-recipient');
  if (!filter_var($recipient,FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Veuillez saisir un email valide.';
  }
  else {
    $params = array(
      "method" => "POST",
      "ID" => $campaignId,
      "Recipients" => 
        json_decode('[
          {
            "Email": "' . $recipient . '",
            "Name": "' . $name . '"
          }
        ]', true),
    );
    $response = $api->newsletterTest($params);
    if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_POST) {
      $testMailOk = true;
    }
    elseif (isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_ERROR) {
      $msg = 'Erreur pendant l\'envoi du mail de test.';
      $errors[] = $msg;
    }
    else {
      $errors[] = 'Erreur interne pendant l\'envoi du mail de test.';
    }
  }
}


// get the up-to-date mailjet HTML
$response = $api->newsletterDetailContent(array('method' => 'VIEW', 'ID' => $campaignId));
if(isset($api->_response_code) && $api->_response_code === 200) {
  $attribute = 'Html-part';
  $mailjetHtml = $response->Data[0]->$attribute;
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
        <a href="view.php?id=<?php echo $campaignId ?>"><?php echo $campaign->Title?></a>
        <a href="#" class="current">Contenu du mail</a>
      </div>
      <div class="row">
        <div class="col-md-12">
           <?php foreach ($errors as $message): ?>
              <div class="alert alert-danger">
                <button class="close" data-dismiss="alert">×</button>
                <strong><?php echo $message;?></strong>
              </div>
            <?php endforeach ?>
        </div>
      </div>
      <?php if($campaign):?>
        <div class="row">
          <div class="col-md-4 page-selector-container">
            <div class="widget-box">
              <div class="widget-title">
                <span class="icon">
                  <i class="icon-th"></i>
                </span>
                <h5>Sélection d'une page</h5>
              </div>
              <div class="widget-content nopadding">
                <form action="" method="post" class="form-horizontal">
                  <?php if(!$pageId):?>
                    <div class="alert alert-info">
                      Avant de continuer, vous devez choisir la page Automne qui sera envoyée par email.
                    </div>
                  <?php endif;?>
                  <div class="form-group">
                    <label class="control-label" for="pageId">Numéro de la page <span class="mandatory">*</span></label>
                    <div class="controls">
                      <input type="text" name="pageId" class="form-control input-sm" required value="<?php echo $pageId?>">
                      <?php if($pageId && $valid):?>
                        <span class="help-block">Titre de la page : <?php echo $mailjetCampaign->getPageTitle()?></span>
                      <?php endif;?>
                    </div>
                  </div>
                  <div class="form-actions">
                    <input type="hidden" name="id" value="<?php echo $campaignId ?>">
                    <button type="submit" name="page-form" value="1" class="btn btn-primary">Valider</button> <a class="btn btn-inverse" href="index.php">Retour</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php if($pageId && $valid):?>
            <div class="col-md-4 col-md-offset-4 clearfix text-center">
              <a href="send.php?id=<?php echo $campaignId ?>" <?php echo $disableActions ? 'disabled="disabled"' : ''; ?> class="btn btn-lg btn-success">
                <i class="icon-envelope icon-4x"></i>
                <div class="">Préparer l'envoi</div>
              </a>
            </div>
          <?php endif;?>
        </div>
        <div class="row">
          <?php if ($pageId && $valid): ?>
            <div class="col-md-8 preview-container">
              <div class="widget-box">
                <div class="widget-title">
                  <span class="icon">
                    <i class="icon-th"></i>
                  </span>
                  <h5>Preview de la campagne</h5>
                </div>
                <div class="widget-content nopadding">
                  <?php if (count($contentErrors) > 0): ?>
                    <div class="alert alert-danger">
                      <strong>Le contenu de la page n'est pas correct. Les erreurs suivantes ont été trouvées :</strong>
                      <?php foreach ($contentErrors as $message): ?>
                        <ul>
                          <li><?php echo $message;?></li>
                        </ul>
                      <?php endforeach ?>
                    </div>
                  <?php endif;?>
                  <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#html">HTML</a></li>
                    <li><a data-toggle="tab" href="#text">Texte</a></li>
                  </ul>
                  <div class="tab-content">
                    <div id="html" class="tab-pane active">
                      <div class="alert alert-info">
                        Voici la page telle qu'elle sera envoyée par Mailjet. Vous pouvez editer la page via l'administration d'Automne et consulter ici le code qui sera envoyé à Mailjet.<br />
                        Les balises script et les commentaires HTML seront automatiquement enlevés lors de l'envoi à Mailjet.
                      </div>
                      <iframe id="preview" width="100%" src="preview.php?id=<?php echo $campaignId;?>"></iframe>
                    </div>
                    <div id="text" class="tab-pane">
                      <div class="alert alert-info">
                        Voici le contenu de la page en mode "texte", tel qu'il sera affiché par les clients mail ne gérant pas le HTML.
                      </div>
                      <pre>
<?php echo $textVersion; ?>
                      </pre>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- end .preview-container -->
            <div class="col-md-4">
              <?php if($valid && $mailjetHtml !== $htmlVersion):?>
                <div class="widget-box">
                  <div class="widget-content">
                    <?php if (count($contentErrors) > 0): ?>
                      <div class="alert alert-warning">
                        La page doit être valide avant qu'elle soit envoyée à Mailjet.
                      </div>
                    <?php endif;?>
                    <form action="" method="post">
                        <div class="alert alert-warning">
                          <span class="icon icon-warning-sign icon-3x"></span>
                          <strong>
                            Le contenu de la page est différent de celui connu par Mailjet.<br />
                            Après chaque modification de la page, vous devez cliquer sur le bouton "Mettre à jour le mail" pour envoyer le code HTML mis à jour à Mailjet.
                          </strong>
                        </div>
                      <?php if($validateContentSubmitted && $contentOk) :?>
                        <div class="alert alert-success">
                          Le contenu du mail a été mis à jour avec succès chez Mailjet.
                        </div>
                      <?php endif;?>
                      <div class="form-actions">
                        <input type="hidden" name="id" value="<?php echo $campaignId ?>">
                        <button type="submit" name="validate-content" <?php echo $disableActions ? 'disabled="disabled"' : ''; ?> value="1" class="btn btn-primary">Mettre à jour le mail</button>
                      </div>
                    </form>
                  </div>
                </div>
              <?php endif;?>
              <div class="widget-box">
                <div class="widget-content">
                  <form action="" method="post">
                    <?php if($testMailOk) :?>
                      <div class="alert alert-success">
                        Le mail de test a été envoyé avec succès.
                      </div>
                    <?php endif;?>
                    <div class="form-group">
                      <label class="control-label" for="test-recipient">Destinataire <span class="mandatory">*</span></label>
                      <div class="controls">
                        <input type="text" name="test-recipient" <?php echo $disableActions ? 'disabled="disabled"' : ''; ?> class="form-control input-sm" required value="<?php echo $recipient?>"/>
                        <span class="help-block">Adresse email où envoyer l'email de test</span>
                        <input type="text" name="name-recipient" <?php echo $disableActions ? 'disabled="disabled"' : ''; ?> class="form-control input-sm" required value="<?php echo $name?>"/>
                        <span class="help-block">Nom de la personne à qui envoyer l'email de test </span>
                      </div>
                    </div>
                    <div class="form-actions">
                      <input type="hidden" name="id" value="<?php echo $campaignId ?>">
                      <button type="submit" name="test-email" <?php echo $disableActions ? 'disabled="disabled"' : ''; ?> value="1" class="btn btn-primary">Envoyer le mail de test</button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="widget-box">
                <div class="widget-content">
                  <p>Vous pouvez utiliser les variables suivantes dans la page, elles seront automatiquement transformées par Mailjet :</p>
                  <pre>
  E-mail du destinataire : [[EMAIL_TO]]
  Lien permanent         : [[PERMALINK]]
  URL Facebook           : [[SHARE_FACEBOOK]]
  URL Twitter            : [[SHARE_TWITTER]]
  URL Google             : [[SHARE_GOOGLE]]
  URL Linkedin           : [[SHARE_LINKEDIN]]
  </pre>
                </div>
              </div>
            </div>
          </div>
        <?php endif ?>
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
    <script src="/automne/admin/modules/mailjet/assets/js/min/write.min.js"></script>
  </body>
</html>
