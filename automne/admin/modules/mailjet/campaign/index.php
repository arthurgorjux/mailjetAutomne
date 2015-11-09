<?php


# @see : pagination avec mailjet https://github.com/mailjet/mailjet-apiv3-php-simple/issues/86

require_once(dirname(__FILE__).'/../module.inc.php');
$response = $api->campaignoverview();
/*echo '<pre>';
var_dump($response->Data);
echo '</pre>';
die;*/
$campaings = array();

foreach ($response->Data as $campaign) {

	// Store values in an array
	$values = array();
	$values['id'] = $campaign->ID;
	$values['title'] = $campaign->Title;
	$values['subject'] = $campaign->Subject;

	// Campaign is a newsletter
	if($campaign->IDType === MailjetAPI::MAILJET_TYPE_NL){
		$newsletter = $api->newsletter(array("method" => "VIEW", "ID" => $campaign->ID))->Data[0];
		$newsletterStatus = $api->newsletterStatus(array("ID" => $campaign->ID))->Data[0]->Status;
		$values['createdAt'] = $newsletter->CreatedAt;
		$values['sendAt'] = $newsletter->DeliveredAt;		
		$values['status'] = $newsletterStatus;
	
	}elseif($campaign->IDType === MailjetAPI::MAILJET_TYPE_CAMPAIGN) {
		$campaignTemp = $api->campaign(array("CampaignID" => $campaign->ID))->Data[0];
		$status = $api->newsletterStatus(array("method" => "VIEW", "ID" => $campaignTemp->NewsLetterID))->Data[0]->Status;
		$values['createdAt'] = $campaignTemp->CreatedAt;
		$values['sendAt'] = $campaignTemp->SendStartAt;
		$values['status'] = $api->newsletterStatus(array("method" => "VIEW", "ID" => $campaignTemp->NewsLetterID))->Data[0]->Status;
	}
	
	$campaigns[] = $values;
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
				<h1>Campagnes</h1>
			</div>
			<div id="breadcrumb">
				<a href="../index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
				<a href="#" class="current">Campagnes</a>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="btn-group">
						<a class="btn btn-primary" href="add.php"><i class="icon-file"></i> Créer une nouvelle campagne</a>

					</div>
				<?php if($api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_VIEW): ?>
					<?php if(!empty($campaigns)) : ?>
					<div class="widget-box">
					<div class="widget-content nopadding table-responsive">
						<table class="table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th>Statut</th>
									<th>Titre</th>
									<th>Sujet</th>
									<th>Date de création</th>
									<th>Date d'envoi</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($campaigns as $campaign):?>
								<tr>
									<td><span class="label label-<?php echo MailjetAPI::getStatusClass($campaign['status'])?>"><?php echo MailjetAPI::getStatus($campaign['status'])?></span></td>
									<td><?php echo $campaign['title']?></td>
									<td><?php echo $campaign['subject']?></td>
									<td><?php echo (isset($campaign['createdAt']) && $campaign['createdAt'] != 0) ? date_create($campaign['createdAt'])->format('d/m/Y') : '-'?></td>
									<td><?php echo (isset($campaign['sendAt']) && $campaign['sendAt'] != 0) ? date_create($campaign['sendAt'])->format('d/m/Y H:i') : '-' ?></td>
									<td>
										<a href="view.php?id=<?php echo $campaign['id'];?>" class="btn btn-primary btn-xs"><i class="icon icon-edit"></i> Modifier</a>
										<?php if ($campaign['status'] !== 'archived' && $campaign['status'] !== 'sent' && $campaign['status'] !== 'programmed'):?>
											<a href="write.php?id=<?php echo $campaign['id'];?>"class="btn btn-success btn-xs"><i class="icon-remove icon-pencil"></i> Rédiger</a>
										<?php endif; ?>
										<?php if (isset($campaign['url']) && $campaign['url'] !== ''): ?>
											<a href="<?php echo $campaign['url'];?>" target="_blank" class="btn btn-primary btn-xs"><i class="icon icon-eye-open"></i> Voir en ligne</a>
										<?php endif ?>
										<?php if (isset($campaign->report_uri)): ?>
											<a href="https://fr.mailjet.com<?php echo $campaign->report_uri;?>" target="_blank" class="btn btn-primary btn-xs"><i class="icon icon-signal"></i> Consulter le rapport</a>
										<?php endif ?>
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
							<strong>Erreur!</strong> Récupération des campagnes impossible.
						</div>
					<?php endif; ?>
				<?php endif;?>
			</div>
			</div>

			<!-- content goes here -->
		</div>

		<?php include dirname(__FILE__).'/../partials/scripts.php'; ?>

	</body>
</html>
