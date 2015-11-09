<?php
define("APPLICATION_DEFAULT_ENCODING", "utf-8");
require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

//CHECKS
if (!$cms_user->hasModuleClearance('mailjet', CLEARANCE_MODULE_EDIT)) {
	die('No rights for this module');
}

$cms_module = CMS_modulesCatalog::getByCodename('mailjet');
global $cms_language;


$key    = $cms_module->getParameters('MAILJET_API_KEY');
$secret = $cms_module->getParameters('MAILJET_SECRET_KEY');
$api = null;
if($key && $secret) {
	$api = $cms_module->getAPI();
}
