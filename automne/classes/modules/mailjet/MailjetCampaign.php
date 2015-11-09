<?php
/**
 * file MailjetCampaign.php
 *
 * Contains the MailjetCampaign class to provide database abstraction.
 * @package Automne\Mailjet
 */

/**
* MailjetCampaign
*
*/
class MailjetCampaign
{
	private $campaignId = 0;
	public $data = array();

	function __construct($campaignId)
	{
		$this->campaignId = $campaignId;
		if(self::exists($campaignId)) {
			$this->data = self::getData($this->campaignId);
		}
		else {
			self::create($this->campaignId,$this->data);
		}
	}

	public function save() {
		$sql = 'UPDATE mod_mailjet set data =\''.json_encode($this->data).'\' where campaignId = '.$this->campaignId.';';
		$query = new CMS_query($sql);
		if($query->hasError()) {
			return array('error' => true,'message' => 'Erreur lors de la sauvegarde dans la base de donnée.');
		}
		return array('error' => false);
	}

	private function getValue($propName, $default = null){
		return (isset($this->data[$propName])) ? $this->data[$propName] : $default;
	}

	private function setValue($propName, $value){
		$this->data[$propName] = $value;
	}

	public function getPage() {
		return $this->getValue('page');
	}

	public function setPage($pageId) {
		if(!CMS_tree::getPageByID($pageId)) {
			return array('error' => true,'message' => 'Ce numéro ne correspond pas à une page valide');
		}
		$this->setValue('page',$pageId);
		return array('error' => false);
	}

	public function getPageTitle() {
		$pageId = $this->getValue('page');
		return CMS_tree::getPageValue($pageId,'title');
	}

	/**
	 * Check if a campaing exists on the local database
	 * @param integer $campaignId        the mailchimp campaign id
	 * @return bool
	 */
	public static function exists($campaignId) {
		if(!io::isPositiveInteger($campaignId)) {
			return false;
		}
		$sql = 'SELECT count(*) as c from mod_mailjet where campaignId = '.$campaignId.';';
		$query = new CMS_query($sql);
		$res = $query->getAll();
		return $res[0]['c'] > 0;
	}

	public static function create($campaignId, $data = array()) {
		if(!io::isPositiveInteger($campaignId)) {
			return false;
		}
		$sql = 'INSERT INTO mod_mailjet VALUES ('.$campaignId.',"'.json_encode($data).'");';
		$query = new CMS_query($sql);
		return !$query->hasError();
	}

	/**
	 * Returns the action for the given user on this icr / datastrip
	 * @param integer $uid        the user id
	 * @param string $icr         the ICR identifier
	 * @param string $datastripId the datastrip identifier
	 * @return mixed              the action or NULL
	 */
	public static function getData($campaignId) {
		$sql = 'SELECT data from mod_mailjet where campaignId = '.$campaignId.';';
		$query = new CMS_query($sql);
		$res = $query->getAll();
		if(isset($res[0])) {
			return json_decode($res[0]['data'],true);
		}
		return null;
	}
}