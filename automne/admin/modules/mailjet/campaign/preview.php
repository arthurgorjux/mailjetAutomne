<?php

require_once(dirname(__FILE__).'/../module.inc.php');
$campaignId = io::get('id');
$mailjetCampaign = new MailjetCampaign($campaignId);
$pageId = $mailjetCampaign->getPage();
define("MAILJET_PREVIEW",true);
$content = CMS_module_mailjet::getNewsletterContent($pageId);
if($content['errors'] !== ''):?>
	<!DOCTYPE html>
	<html lang="en">
	  <?php include dirname(__FILE__).'/../partials/head.php'; ?>
	  <body>
	  	<div id="content" style="margin-left: 0;">
	  		<div id="content-header">
        	<h1>Preview</h1>
      	</div>


	  		<div class="row">
	  			<div class="col-md-12">
						<div class="alert alert-danger">
							Echec de la prévisualisation : <br />
							<?php echo $content['errors'];?>
						</div>
					</div>
				</div>
			</div>
		</body>
	</html>
<?php else:
	echo $content['content'];
endif;

?>
