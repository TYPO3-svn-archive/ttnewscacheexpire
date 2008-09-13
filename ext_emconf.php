<?php

########################################################################
# Extension Manager/Repository config file for ext: "cacheexpire"
#
# Auto generated 07-09-2008 22:16
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
	'version' => '0.0.1',
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
	'_md5_values_when_last_written' => 'a:8:{s:13:"changelog.txt";s:4:"0bd8";s:34:"class.user_tt_news_cacheexpire.php";s:4:"fd09";s:21:"class.ux_tslib_fe.php";s:4:"f4b7";s:21:"ext_conf_template.txt";s:4:"68b3";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"51ca";s:10:"readme.txt";s:4:"4f82";s:14:"doc/manual.sxw";s:4:"67b1";}',
	'suggests' => array(
	),
);

?>