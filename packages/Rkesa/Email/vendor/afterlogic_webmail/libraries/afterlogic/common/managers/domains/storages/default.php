<?php

/*
 * Copyright 2004-2017, AfterLogic Corp.
 * Licensed under AGPLv3 license or AfterLogic license
 * if commercial version of the product was purchased.
 * See the LICENSE file for a full license statement.
 */

/**
 * @internal
 * 
 * @package Domains
 * @subpackage Storages
 */
class CApiDomainsStorage extends AApiManagerStorage
{
	/**
	 * @param CApiGlobalManager &$oManager
	 */
	public function __construct($sStorageName, CApiGlobalManager &$oManager)
	{
		parent::__construct('domains', $sStorageName, $oManager);
	}

	/**
	 * @param string $sDomainId
	 *
	 * @return CDomain
	 */
	public function getDomainById($sDomainId)
	{
		return null;
	}

	/**
	 * @param string $sDomainName
	 *
	 * @return CDomain
	 */
	public function getDomainByName($sDomainName)
	{
		return null;
	}

	/**
	 * @param string $sDomainUrl
	 *
	 * @return CDomain
	 */
	public function getDomainByUrl($sDomainUrl)
	{
		return null;
	}

	/**
	 * @param CDomain &$oDomain
	 *
	 * @return bool
	 */
	public function createDomain(CDomain &$oDomain)
	{
		return false;
	}

	/**
	 * @param CDomain $oDomain
	 *
	 * @return bool
	 */
	public function updateDomain(CDomain $oDomain)
	{
		return false;
	}
	
	/**
	 * @param int $iVisibility
	 * @param int $iTenantId
	 *
	 * @return bool
	 */
	public function setGlobalAddressBookVisibilityByTenantId($iVisibility, $iTenantId)
	{
		return false;
	}

	/**
	 * @param array $aDomainsIds
	 *
	 * @return bool
	 */
	public function areDomainsEmpty($aDomainsIds)
	{
		return true;
	}

	/**
	 * @param array $aDomainsIds
	 * @param bool $bEnable
	 *
	 * @return bool
	 */
	public function enableOrDisableDomains($aDomainsIds, $bEnable)
	{
		return false;
	}

	/**
	 * @param int $iTenantId
	 * @param bool $bEnable
	 *
	 * @return bool
	 */
	public function enableOrDisableDomainsByTenantId($iTenantId, $bEnable)
	{
		return false;
	}

	/**
	 * @param array $aDomainsIds
	 *
	 * @return bool
	 */
	public function deleteDomains($aDomainsIds)
	{
		return false;
	}

	/**
	 * @param int $iDomainId
	 * @return bool
	 */
	public function deleteDomain($iDomainId)
	{
		return false;
	}

	/**
	 * @param int $iPage
	 * @param int $iDomainsPerPage
	 * @param string $sOrderBy Default value is **'name'**
	 * @param bool $bOrderType Default value is **true**
	 * @param string $sSearchDesc Default value is empty string
	 * @param int $iTenantId Default value is **0**
	 *
	 * @return array|false [IdDomain => [IsInternal, Name]]
	 */
	public function getDomainsList($iPage, $iDomainsPerPage, $sOrderBy = 'name', $bOrderType = true, $sSearchDesc = '', $iTenantId = 0)
	{
		return false;
	}

	/**
	 * @param int $iTenantId
	 *
	 * @return array
	 */
	public function getDomainIdsByTenantId($iTenantId)
	{
		return false;
	}
	
	/**
	 * @param int $iTenantId
	 *
	 * @return \CDomain
	 */
	public function getDefaultDomainByTenantId($iTenantId)
	{
		return null;
	}
	
	/**
	 * @param string $sDomainName
	 *
	 * @return bool
	 */
	public function domainExists($sDomainName)
	{
		return false;
	}

	/**
	 * @param string $sSearchDesc Default value is empty string.
	 * @param int $iTenantId Default value is **0**.
	 *
	 * @return int|false
	 */
	public function getDomainCount($sSearchDesc = '', $iTenantId = 0)
	{
		return 0;
	}
}
