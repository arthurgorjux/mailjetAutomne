<?php
/**
 * file mailjet.php
 *
 * Contains the Mailjet base module.
 * @package Automne\Mailjet
 */

define("MOD_MAILJET_CODENAME", "mailjet");

spl_autoload_register (array('CMS_module_mailjet','autoload'));

/**
 * CMS_module_mailjet
 *
 * The module itself
 *
 */
class CMS_module_mailjet extends CMS_Module
{

	static protected $shared = array();
	/**
	 * Class Constructor
	 */
	function __construct()
	{
		parent::__construct(MOD_MAILJET_CODENAME);
	}

	/**
		* Module autoload handler
		* @param string $classname the classname required for loading
		*/
	public static function autoload($className) {
		$className = ltrim($className, '\\');
		$fileName	= '';
		$namespace = '';
		if ($lastNsPos = strrpos($className, '\\')) {
				$namespace = substr($className, 0, $lastNsPos);
				$className = substr($className, $lastNsPos + 1);
				$fileName	= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= PATH_MODULES_FS.'/'.MOD_MAILJET_CODENAME. DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if(file_exists($fileName)) {
			require_once $fileName;
		}
	}

	/**
	 * Returns a module parameter
	 * @param	String $key The parameter key
	 * @return String			The parameter value
	 */
	public static function getParameter($key) {
		$module = new self();
		return $module->getParameters($key);
	}

	/**
		* Return a list of objects infos to be displayed in module index according to user privileges
		*
		* @return string : HTML scripts infos
		* @access public
		*/
	function getObjectsInfos($user) {
		if ($user->hasModuleClearance($this->getCodename(), CLEARANCE_MODULE_EDIT)) {
			$objectsInfos[] = array(
				'label'			=> 'Mailjet',
				'adminLabel'	=> 'Mailjet',
				'description'	=> 'Mailjet',
				'objectId'		=> 'mailjet',
				'url'			=> PATH_ADMIN_MODULES_WR.'/'.$this->getCodename().'/index.php',
				'module'		=> $this->getCodename(),
				'class'			=> 'atm-elements',
				'frame'			=> true
			);
		}
		return $objectsInfos;
	}

	/**
	 * Returns an instance of MailjetAPI
	 * @return MailjetAPI the Mailjet API interface
	 */
	public function getAPI() {
		if(isset(self::$shared['mailjet_api'])) {
			return self::$shared['mailjet_api'];
		}

		$mailjetKey		= $this->getParameters('MAILJET_API_KEY');
		$mailjetSecret = $this->getParameters('MAILJET_SECRET_KEY');

		self::$shared['mailjet_api'] = new MailjetAPI($mailjetKey,$mailjetSecret);

		return self::$shared['mailjet_api'];
	}

	public static function buildOptions($values, $default) {
		$options = '';
		foreach ($values as $key => $value) {
			$selected = '';
			if($key == $default) {
				$selected = 'selected="selected"';
			}
			$options .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}
		return $options;
	}


	public static function getNewsletterContent($pageId) {

		$page = CMS_tree::getPageByID($pageId);
		if($page->hasError()) {
			return;
		}

		$website = $page->getWebsite();
		$websiteUrl = $website->getURL();
		$language = CMS_languagesCatalog::getByCode($page->getLanguage());
		$content = $page->getContent($language, PAGE_VISUALMODE_HTML_PUBLIC);

		$modulesTreatment = new CMS_modulesTags(MODULE_TREATMENT_LINXES_TAGS,PAGE_VISUALMODE_HTML_PUBLIC,$page);
		$modulesTreatment->setDefinition($content);
		$content = $modulesTreatment->treatContent(true);

		//eval all php code in page
		$php_evalued_content = io::evalPHPCode($content);

		//change all relative URL in page
		$parsed_content = self::prepareHTML($php_evalued_content, $websiteUrl);

		return $parsed_content;
	}

	private static function prepareHTML($content, $rootUrl) {


		// add trailing slash to the root url if needed
		if (substr($rootUrl, -1) !== '/') {
			$rootUrl .= '/';
		}

		// initiate the dom document
		$domDocument = new DomDocument();

	 	if (!$domDocument->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'))) {
	 		$errors='';
			foreach (libxml_get_errors() as $error)	{
				$errors.=$error->message.'<br/>';
			}
			libxml_clear_errors();
			return array('content' => $content, 'errors' => $errors);
	 	}

		// fix url
	 	$attributes = array('href','src', 'background');
	 	foreach ($attributes as $attribute) {
	 		self::convertRelativeToAbsoluteUrl($domDocument,$attribute,$rootUrl);
	 	}

		// remove scripts
		$tags = array('script');
		foreach ($tags as $tag) {
			self::removeTags($domDocument,$tag);
		}

	 	self::removeComments($domDocument);

	 	$htmlContent = $domDocument->saveHTML();
	 	// saveHTML converts [[UNSUB_LINK_FR]] to %5B%5BUNSUB_LINK_FR%5D%5D, we need to revert that or it will break MailjetLinks
	 	$htmlContent = preg_replace('/%5B%5B/', '[[', $htmlContent);
	 	$htmlContent = preg_replace('/%5D%5D/', ']]', $htmlContent);
		return	array('content' => $htmlContent, 'errors' => '');
	}

	private static function convertRelativeToAbsoluteUrl($domDocument, $attribute, $rootUrl) {
		$xpath = new DOMXPath($domDocument);
		$nodes = $xpath->query('//*[@'.$attribute.' != "" and not(starts-with(@'.$attribute.', "http"))]');

		foreach ($nodes as $node) {
			$relativeUrl = $node->getAttribute($attribute);
			if(!self::isIgnored($relativeUrl)) {
				$absoluteUrl = phpUri::parse($rootUrl)->join($relativeUrl);
				$node->setAttribute($attribute,$absoluteUrl);
			}
		}
	}

	private function removeTags(&$domDocument,$tag) {
		$nodes = $domDocument->getElementsByTagName($tag);
		while ($nodes->length > 0) {
			 $node = $nodes->item(0);
			 self::remove_node($node);
		}
	}

	private function removeComments(&$domDocument) {

		$xpath = new DOMXPath($domDocument);
		foreach ($xpath->query('//comment()') as $comment) {
				self::remove_node($comment);
		}
	}

	 private function remove_node(&$node) {
			 $pnode = $node->parentNode;
			 self::remove_children($node);
			 $pnode->removeChild($node);
	 }

	 private function remove_children(&$node) {
			 while ($node->firstChild) {
					 while ($node->firstChild->firstChild) {
							 self::remove_children($node->firstChild);
					 }

					 $node->removeChild($node->firstChild);
			 }
	 }

	public static function checkNewsletterContent($pageId) {
		$errors = array();
		// Newsletter must contain [[UNSUB_LINK_FR]]
		$content = self::getNewsletterContent($pageId);
		if(!preg_match("/\[\[UNSUB_LINK_FR\]\]/", $content['content'])) {
			$errors[] = 'La page doit contenir le texte [[UNSUB_LINK_FR]] pour que Mailjet insére le lien de désinscription.';
		}
		return $errors;
	}

	public static function isIgnored($url) {
		$ignoredUrls = array('[[UNSUB_LINK_FR]]', '[[UNSUB_LINK_EN]]','[[PERMALINK]]','[[SHARE_FACEBOOK]]','[[SHARE_TWITTER]]','[[SHARE_GOOGLE]]','[[SHARE_LINKEDIN]]');
		return in_array($url,$ignoredUrls);
	}
}
