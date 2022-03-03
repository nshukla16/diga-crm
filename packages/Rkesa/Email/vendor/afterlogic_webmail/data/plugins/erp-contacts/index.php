<?php

/* -AFTERLOGIC LICENSE HEADER- */
// https://github.com/afterlogic/plugin-add-suggest-contacts-example/blob/master/index.php

class_exists('CApi') or die();

class CAddSuggestContactsExamplePlugin extends AApiPlugin
{
	/**
	 * @param CApiPluginManager $oPluginManager
	 */
	public function __construct(CApiPluginManager $oPluginManager)
	{
		parent::__construct('1.0', $oPluginManager);

		$this->AddHook('webmail.change-suggest-list', 'WebmailChangeSuggestList');
	}

	public function getProtocol(){
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            return 'https://';
        }
        else {
            return 'http://';
        }
    }

	/**
	 * @param CAccount $oAccount
	 * @param string $sSearch
	 * @param array $aList
	 * @param array $aCounts
	 */
	public function WebmailChangeSuggestList($oAccount, $sSearch, &$aList, &$aCounts)
	{
		if ($oAccount)
		{
			$url = $this->getProtocol().$_SERVER['HTTP_HOST'].'/api/clients/autocomplete?email='.$oAccount->getEmail().'&q=' . $sSearch;

			//open connection
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ch, CURLOPT_VERBOSE, TRUE);

			//execute post
			$response = curl_exec($ch);
			$aResultContacts = json_decode($response, true);

			//close connection
			curl_close($ch);

			if (is_array($aResultContacts))
			{
				foreach ($aResultContacts as $sResultEmail)
				{
					$oContactItem = new CContactListItem();
					$oContactItem->Id = 0;
					$oContactItem->IdStr = '';
					$oContactItem->Name = '';
					$oContactItem->Email = $sResultEmail;
					$oContactItem->UseFriendlyName = true;
					$oContactItem->Global = true;
					$oContactItem->ReadOnly = true;
					$oContactItem->ItsMe = $oContactItem->Email === $oAccount->Email;

					$aList[] = $oContactItem;
				}
			}
		}
	}
}

return new CAddSuggestContactsExamplePlugin($this);
