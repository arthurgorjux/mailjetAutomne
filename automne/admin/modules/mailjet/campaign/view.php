<?php

require_once(dirname(__FILE__).'/../module.inc.php');
$campaignId = io::get('id');
$campaign = null;
$campaign = $api->campaign(array("CampaignID" => $campaignId));

// Newsletter
if(empty($campaign->Data)){
	$newsletter = $api->newsletter(array("method" => "VIEW", "ID" => $campaignId))->Data[0];
	$status = $api->newsletterStatus(array("method" => "VIEW", "ID" => $campaignId))->Data[0]->Status;
}else{ // Campaign
	$newsletterId = $campaign->Data[0]->NewsLetterID;
	$newsletter = $api->newsletter(array("method" => "VIEW", "ID" => $newsletterId))->Data[0];
	$status = $api->newsletterStatus(array("method" => "VIEW", "ID" => $newsletterId))->Data[0]->Status;
}
$campaign = new StdClass();
$campaign->Title = $newsletter->Title;
$campaign->Subject = $newsletter->Subject;
$campaign->Status = $status;
$campaign->Lang = $newsletter->Locale;
$campaign->Url = $newsletter->Url;
$campaign->Permalink = $newsletter->Permalink;
$campaign->Footer = $newsletter->Footer;
$campaign->FromEmail = $newsletter->SenderEmail;
$campaign->FromName = (isset($newsletter->SenderName)) ? $newsletter->SenderName : '';
$campaign->ListID = $newsletter->ContactsListID;
$campaign->CreatedAt = $newsletter->CreatedAt;
$campaign->SendStartAt = $newsletter->DeliveredAt;
$campaign->ReplyTo = (isset($newsletter->ReplyEmail)) ? $newsletter->ReplyEmail : '';
$campaign->SegmentationID = (isset($newsletter->SegmentationID)) ? $newsletter->SegmentationID : '';

$senderResponse = $api->sender(array("Limit" => "-1", "Status" => "Active"));
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET){
	$senders = array();
	foreach ($senderResponse->Data as $key => $sender) {
		$senders[$sender->Email] = $sender->Email;
	}
}


$allLists = $api->contactslist(array("Limit" => "-1"));
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET){
	$lists = array();
	foreach ($allLists->Data as $key => $list) {
		$lists[$list->ID] = $list->Name;
	}
}

$allSegments = $api->contactfilter(array('Limit' => '-1'));
if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET){
	$segments = array();
	foreach($allSegments->Data as $key => $segment){
		$segments[$segment->ID] = $segment->Name;
	}
}

$languages = array(
	'en_EN' => 'en',
	'fr_FR' => 'fr',
	'de_DE' => 'de',
	'it_IT' => 'it',
	'es_ES' => 'es',
	'nl_NL' => 'nl'
);

$submitted = io::post('submit');
$status = '';
if($campaign) {
	$status = $campaign->Status;
}
$errors = array();
$updated = false;
if($submitted) {
	$campaignId 	= io::post('id');
	$title        = io::post('title');
	$subject      = io::post('subject');
	$footer       = io::post('footer');
	$from         = io::post('from');
	$from_name    = io::post('from_name');
	$lang         = io::post('lang');
	$list_id      = io::post('list_id');
	$permalink    = io::post('permalink');
	$reply_to     = io::post('reply_to');
	$segment_id   = io::post('segment_id');
	if(empty($title) || empty($subject) || empty($list_id) || empty($lang) || empty($from) || empty($from_name) || empty($footer)) {
		$errors[] = 'Veuillez remplir tous les champs obligatoires';
	}
	elseif (!filter_var($from,FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Veuillez saisir un email d\'expéditeur valide';
	}
	elseif ($reply_to != '' && !filter_var($reply_to,FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Veuillez saisir un email de réponse valide';
	}

  if(empty($errors)) {
		$params = array(
	    'method' => 'PUT',
	    'unique' => $campaignId,
	    'Title' => $title,
	    'Subject' => $subject,
	    'ContactsListID' => $list_id,
	    'Locale' => $lang,
	    'SenderEmail' => $from,
	    'SenderName' => $from_name,
	    'Footer' => $footer,
	    'Permalink' => $permalink,
	    'ReplyEmail' => $reply_to,
		);
		if($segment_id !== ''){
			$params['SegmentationID'] = $segment_id;
		}

		$response = $api->newsletter($params);
		if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_GET) {
			$updated = true;
			$id = $response->Data[0]->ID;
		}
		elseif (isset($api->_response_code) && $api->_response_code !== MailjetAPI::MAILJET_STATUS_CODE_OK_GET) {
		 	$msg = 'Erreur pendant la modification de la campagne.';
		 	$errors[] = $msg;
		}
		else {
			$errors[] = 'Erreur interne pendant la modification de la campagne.';
		}
	}
}
else {
  if($campaign) {
		$title        = $campaign->Title;
		$subject      = $campaign->Subject;
		$footer       = $campaign->Footer;
		$from         = $campaign->FromEmail ;
		$from_name    = $campaign->FromName ;
		$lang         = $campaign->Lang;
		$list_id      = $campaign->ListID;
		$permalink    = $campaign->Permalink;
		$reply_to     = $campaign->ReplyTo;
		$status       = $campaign->Status;
		$segment_id   = $campaign->SegmentationID;
  }
}

$updateDisabled = ($status !== 'draft');

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
				<h1>Campagnes</h1>
			</div>
			<div id="breadcrumb">
				<a href="../index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
        <a href="index.php">Campagnes</a>
        <a href="view.php?id=<?php echo $campaignId ?>" class="current"><?php echo $campaign->Title?></a>
			</div>
			<?php if($campaign) :?>
				<div class="row">
          <div class="col-md-8 form-container">
            <?php if ($updated): ?>
              <div class="alert alert-success">
                <strong>La campagne a été mise à jour.</strong>
              </div>
            <?php endif ?>
            <div class="widget-box">
              <div class="widget-title">
                <span class="icon">
                  <i class="icon-th"></i>
                </span>
                <h5>Modification des propriétés de la campagne</h5>
                <span class="label label-<?php echo MailjetAPI::getStatusClass($campaign->Status)?>">
                	<?php echo MailjetAPI::getStatus($campaign->Status)?>
                </span>
              </div>
              <div class="widget-content nopadding">
                <?php foreach ($errors as $message): ?>
                  <div class="alert alert-danger">
                    <button class="close" data-dismiss="alert">×</button>
                    <strong><?php echo $message;?>
                  </div>
                <?php endforeach ?>
                <form action="" method="post" class="form-horizontal">
                  <fieldset <?php echo ($updateDisabled) ? 'disabled' : '' ?>>
	                  <div class="form-group">
		                  <label class="control-label" for="title">Titre <span class="mandatory">*</span></label>
		                  <div class="controls">
		                    <input type="text" name="title" class="form-control input-sm" required value="<?php echo $title?>">
		                    <span class="help-block">Le titre de la campagne. Usage interne uniquement.</span>
		                  </div>
		                </div>
		                <div class="form-group">
		                  <label class="control-label" for="subject">Sujet <span class="mandatory">*</span></label>
		                  <div class="controls">
		                    <input type="text" name="subject" class="form-control input-sm" required  value="<?php echo $subject?>">
		                    <span class="help-block">Le sujet de la campagne. Sera le titre de l'email reçu.</span>
		                  </div>
		                </div>
		                <div class="form-group">
							<label class="control-label" for="lang">Langue <span class="mandatory">*</span></label>
							<div class="controls">
								<select name="lang" class="form-control">
									<?php echo CMS_module_mailjet::buildOptions($languages,$lang);?>
								</select>
								<span class="help-block">La langue de la campagne.</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label" for="list_id">Liste <span class="mandatory">*</span></label>
							<div class="controls">
								<select name="list_id" class="form-control">
									<?php echo CMS_module_mailjet::buildOptions($lists,$list_id);?>
								</select>
								<span class="help-block">Le liste destinataire de la campagne.</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label" for="segment_id">Segment</label>
							<div class="controls">
								<select name="segment_id" class="form-control">
									<option value=""></option>
									<?php echo CMS_module_mailjet::buildOptions($segments,$segment_id);?>
								</select>
								<span class="help-block">Le segment de liste de la campagne.</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label" for="from_name">Nom de l'expéditeur <span class="mandatory">*</span></label>
							<div class="controls">
								<input type="text" name="from_name" class="form-control input-sm" required value="<?php echo $from_name?>">
								<span class="help-block">Le nom qui s'affichera chez les destinataires du mail.</span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label" for="from">Email de l'expéditeur <span class="mandatory">*</span></label>
							<div class="controls">
								<select name="from" class="form-control">
									<?php echo CMS_module_mailjet::buildOptions($senders,$from);?>
								</select>
								<span class="help-block">L'adresse email utilisée pour envoyer le mail. <a href="https://fr.mailjet.com/account/sender" target="_blank">Accéder à la gestion des expéditeurs dans Mailjet </a></span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label" for="reply_to">Répondre à</label>
							<div class="controls">
								<input type="text" name="reply_to" class="form-control input-sm" value="<?php echo $reply_to?>">
								<span class="help-block">L'adresse email qui recevra les réponses à la campagne.</span>
							</div>
						</div>
						<div class="form-actions">
							<input type="hidden" name="permalink" value="default" />
							<input type="hidden" name="footer" value="default" />
							<input type="hidden" name="id" value="<?php echo $campaignId; ?>" />
							<button type="submit" name="submit" value="1" class="btn btn-primary">Sauvegarder</button> <a class="btn btn-inverse" href="index.php">Annuler</a>
						</div>
					</fieldset>
                </form>
              </div>
            </div>
          </div><!-- end .form-container -->
          <div class="col-md-4">
          	<div class="widget-box">
							<div class="widget-title">
                <span class="icon">
                  <i class="icon-th"></i>
                </span>
                <h5>Information sur la campagne</h5>
              </div>
              <div class="widget-content">
              	<dl class="dl-horizontal">
              		<dt><strong>Date de création :</strong></dt>
              		<dd><?php echo date_create($campaign->CreatedAt)->format('d/m/Y')?></dd>
              		<dt><strong>Date d'envoi :</strong></dt>
              		<dd><?php echo ($campaign->SendStartAt != 0) ? date_create($campaign->SendStartAt)->format('d/m/Y H:i') : '-' ?></dd>
							  </dl>
							  <?php if ($campaign->Url): ?>
										<a href="<?php echo $campaign->Url;?>" target="_blank" class="btn btn-primary"><i class="icon icon-eye-open"></i> Voir en ligne</a>
								<?php endif ?>
								<?php if (isset($campaign->report_uri)): ?>
									<a href="https://fr.mailjet.com<?php echo $campaign->report_uri;?>" target="_blank" class="btn btn-primary"><i class="icon icon-signal"></i> Consulter le rapport</a>
								<?php endif ?>
								<?php if ($campaign->Status === 'archived'): ?>
									<span>Cette campagne est archivée, vous ne pouvez plus intervenir dessus.</span>
								<?php else :?>
									<?php if ($campaign->Status !== 'sent' && $campaign->Status !== 'programmed'):?>
										<a href="write.php?id=<?php echo $campaignId;?>"class="btn btn-success"><i class="icon-remove icon-pencil"></i> Rédiger</a>
									<?php endif; ?>
									<a href="archive.php?id=<?php echo $campaignId;?>"class="btn btn-danger"><i class="icon-remove icon-white"></i> Archiver</a>
								<?php endif; ?>
              </div>
            </div>
					</div>
        </div>
			<?php else:?>
				<div class="row">
          <div class="col-md-12">
						<div class="alert alert-danger">
							<button class="close" data-dismiss="alert">×</button>
							<strong>Erreur!</strong> Récupération des campagnes impossible.
						</div>
					</div>
				</div>
			<?php endif;?>

		</div><!-- end #content -->
		<?php include dirname(__FILE__).'/../partials/scripts.php'; ?>

	</body>
</html>
