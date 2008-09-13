<?php

########################################################################
# Extension Manager/Repository config file for ext: "ttnewscacheexpire"
#
# Auto generated 13-09-2008 09:36
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'tt_news Cache Expire',
	'description' => 'Page Cache Expires cares of tt_news starttime/endtime',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.1.0',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'experimental',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Martin Holtz',
	'author_email' => 'typo3@martinholtz.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.0-0.0.0',
			'php' => '5.0.0-0.0.0',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:6:{s:13:"changelog.txt";s:4:"eebb";s:22:"class.ux_tx_ttnews.php";s:4:"095c";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"c6de";s:10:"readme.txt";s:4:"b9af";s:14:"doc/manual.sxw";s:4:"277a";}',
	'suggests' => array(
	),
);

?>