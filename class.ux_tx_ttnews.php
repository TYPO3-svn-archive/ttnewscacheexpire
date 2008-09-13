<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Martin Holtz (typo3@martinholtz.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

// require_once (PATH_t3lib.'class.t3lib_div.php');
/** 
 * Plugin 'Cache Expire'
 *
 * @author	Martin Holtz <typo3@martinholtz.de>
 */
class ux_tx_ttnews extends tx_ttnews {
	

		/**
	 * Init Function: here all the needed configuration values are stored in class variables..
	 *
	 * @param	array		$conf : configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		$this->conf = $conf; //store configuration
		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		$this->enableFields = $this->cObj->enableFields('tt_news');
		$this->tt_news_uid = intval($this->piVars['tt_news']); // Get the submitted uid of a news (if any)

		// MH
// 		$GLOBALS['TSFE']->cacheExpires = $GLOBALS['EXEC_TIME']+60*3;
		// TODO: should check only tt_news records, which could be
		//       listed by this plugin
		//		 should only used in an list-plugin (?)
		// 		 should respect selected categorys etc.
		$pageSelect = t3lib_div::makeInstance('t3lib_pageSelect');
		$tstamp = $GLOBALS['TSFE']->cacheExpires; 		
		// Experimental
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*', 
				'tt_news', 
				' (	(tt_news.starttime > '.$GLOBALS['EXEC_TIME'].') OR (tt_news.endtime > '.$GLOBALS['EXEC_TIME'].' )) '.$pageSelect->enableFields('tt_news',0,array('starttime' => true,'endtime' => true),FALSE));
			
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					// $tstamp is the orignial expire-date of that page
					// usually it is calculated by cache-expiredate and 
					// $GLOBALS['EXEC_TIME']
					// the page starttime/endtime is checked before
					// it is requested from cache. So we do not have to care
					// of starttime/endtime of the page itself 
					
					// we want to respect the starttime / endtime of the
					// content elements 
					// 
					// we have to check for each content element only, if it has a starttime
					// or an endtime which takes effect betwwen $GLOBALS['EXEC_TIME']
					// and the default-expire date ($tstamp). 
				if (($row['starttime'] < $tstamp || 0 == $tstamp) && $row['starttime'] > $GLOBALS['EXEC_TIME']) {  
					$tstamp = $row['starttime'];
				}
				if (($row['endtime'] < $tstamp  || 0 == $tstamp) && $row['endtime'] > $GLOBALS['EXEC_TIME']) {
					$tstamp = $row['endtime'];
				}
			}
			$GLOBALS['TSFE']->cacheExpires = $tstamp;
		
		if (!isset($this->conf['compatVersion']) || !preg_match('/^\d+\.\d+\.\d+$/', $this->conf['compatVersion'])) {
			$this->conf['compatVersion'] = $this->getCurrentVersion();
		}

		if (t3lib_extMgm::isLoaded('version')) {
			$this->versioningEnabled = true;
		}
		// load available syslanguages
		$this->initLanguages();
		// sys_language_mode defines what to do if the requested translation is not found
		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode'] : $GLOBALS['TSFE']->sys_language_mode;

		// "CODE" decides what is rendered: codes can be set by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->config['code'] = $code ? $code : $this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);

		// initialize category vars
		$this->initCategoryVars();

			// get fieldnames from the tt_news db-table
		$this->fieldNames = array_keys($GLOBALS['TYPO3_DB']->admin_get_fields('tt_news'));

		if ($this->conf['searchFieldList']) {
			$searchFieldList = $this->validateFields($this->conf['searchFieldList']);
			if ($searchFieldList) {
				$this->searchFieldList = $searchFieldList;
			}
		}
			// Archive:
		$this->config['archiveMode'] = trim($this->conf['archiveMode']) ; // month, quarter or year listing in AMENU
		$this->config['archiveMode'] = $this->config['archiveMode']?$this->config['archiveMode']:'month';

		// arcExclusive : -1=only non-archived; 0=don't care; 1=only archived
		$arcExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'archive', 'sDEF');
		$this->arcExclusive = $arcExclusive?$arcExclusive:$this->conf['archive'];

		$this->config['datetimeDaysToArchive'] = intval($this->conf['datetimeDaysToArchive']);
		$this->config['datetimeHoursToArchive'] = intval($this->conf['datetimeHoursToArchive']);
		$this->config['datetimeMinutesToArchive'] = intval($this->conf['datetimeMinutesToArchive']);

		if ($this->conf['useHRDates']) {
			$this->convertDates();
		}

		// list of pages where news records will be taken from
		if (!$this->conf['dontUsePidList']) {
			$this->initPidList();
		}

		// itemLinkTarget is only used for categoryLinkMode 3 (catselector) in framesets
		$this->config['itemLinkTarget'] = trim($this->conf['itemLinkTarget']);
		// id of the page where the search results should be displayed
		$this->config['searchPid'] = intval($this->conf['searchPid']);

		// pages in Single view will be divided by this token
		$this->config['pageBreakToken'] = trim($this->conf['pageBreakToken'])?trim($this->conf['pageBreakToken']):'<---newpage--->';

		$this->config['singleViewPointerName'] = trim($this->conf['singleViewPointerName'])?trim($this->conf['singleViewPointerName']):'sViewPointer';


		$maxWordsInSingleView = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maxWordsInSingleView', 's_misc'));
		$maxWordsInSingleView = $maxWordsInSingleView?$maxWordsInSingleView:intval($this->conf['maxWordsInSingleView']);
		$this->config['maxWordsInSingleView'] = $maxWordsInSingleView?$maxWordsInSingleView:0;
		$this->config['useMultiPageSingleView'] = $maxWordsInSingleView>1?1:$this->conf['useMultiPageSingleView'];

		// pid of the page with the single view. the old var PIDitemDisplay is still processed if no other value is found
		$singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PIDitemDisplay', 's_misc');
		$singlePid = $singlePid?$singlePid:intval($this->cObj->stdWrap($this->conf['singlePid'],$this->conf['singlePid.']));
		$this->config['singlePid'] = $singlePid ? $singlePid:intval($this->conf['PIDitemDisplay']);

		// pid to return to when leaving single view
		$backPid = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'backPid', 's_misc'));
		$backPid = $backPid?$backPid:intval($this->conf['backPid']);
		$backPid = $backPid?$backPid:intval($this->piVars['backPid']);
		$backPid = $backPid?$backPid:$GLOBALS['TSFE']->id ;
		$this->config['backPid'] = $backPid;

		// max items per page
		$FFlimit = t3lib_div::intInRange($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listLimit', 's_misc'), 0, 1000);

		$limit = t3lib_div::intInRange($this->cObj->stdWrap($this->conf['limit'],$this->conf['limit.']), 0, 1000);
		$limit = $limit?$limit:	50;
		$this->config['limit'] = $FFlimit?$FFlimit:	$limit;

		$latestLimit = t3lib_div::intInRange($this->cObj->stdWrap($this->conf['latestLimit'],$this->conf['latestLimit.']), 0, 1000);
		$latestLimit = $latestLimit?$latestLimit:10;
		$this->config['latestLimit'] = $FFlimit?$FFlimit:$latestLimit;

		// orderBy and groupBy statements for the list Query
		$orderBy = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listOrderBy', 'sDEF');
		$orderByTS = trim($this->conf['listOrderBy']);
		$orderBy = $orderBy?$orderBy:$orderByTS;
		$this->config['orderBy'] = $orderBy;

		if ($orderBy) {
			$ascDesc = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascDesc', 'sDEF');
			$this->config['ascDesc'] = $ascDesc;
			if ($this->config['ascDesc']) {
				// remove ASC/DESC from 'orderBy' if it is already set from TS
				$this->config['orderBy'] = preg_replace('/( DESC| ASC)\b/i','',$this->config['orderBy']);
			}
		}
		$this->config['groupBy'] = trim($this->conf['listGroupBy']);

		// if this is set, the first image is handled as preview image, which is only shown in list view
		$fImgPreview = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'firstImageIsPreview', 's_misc');
		$this->config['firstImageIsPreview'] = $fImgPreview?$fImgPreview : $this->conf['firstImageIsPreview'];
		$forcefImgPreview = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'forceFirstImageIsPreview', 's_misc');
		$this->config['forceFirstImageIsPreview'] = $forcefImgPreview?$fImgPreview : $this->conf['forceFirstImageIsPreview'];

		// List start id
		$listStartId = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listStartId', 's_misc'));
		$this->config['listStartId'] = $listStartId?$listStartId:intval($this->conf['listStartId']);
		// supress pagebrowser
		$noPageBrowser = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'noPageBrowser', 's_misc');
		$this->config['noPageBrowser'] = $noPageBrowser?$noPageBrowser:	$this->conf['noPageBrowser'];


		// image sizes given from FlexForms
		$this->config['FFimgH'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxHeight', 's_template'));
		$this->config['FFimgW'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxWidth', 's_template'));

		// Get number of alternative Layouts (loop layout in LATEST and LIST view) default is 2:
		$altLayouts = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'alternatingLayouts', 's_template'));
		$altLayouts = $altLayouts?$altLayouts:intval($this->conf['alternatingLayouts']);
		$this->alternatingLayouts = $altLayouts?$altLayouts:2;

		// Get cropping lenght
		$this->config['croppingLenght'] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'croppingLenght', 's_template'));

		$this->initTemplate();

		// Configure caching
		$this->allowCaching = $this->conf['allowCaching']?1:0;
		if (!$this->allowCaching) {
			$GLOBALS['TSFE']->set_no_cache();
		}

		// get siteUrl for links in rss feeds. the 'dontInsert' option seems to be needed in some configurations depending on the baseUrl setting
		if (!$this->conf['displayXML.']['dontInsertSiteUrl']) {
			$this->config['siteUrl'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}
	}
	
	
}


?>