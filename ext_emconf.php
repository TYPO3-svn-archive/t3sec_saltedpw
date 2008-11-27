<?php

########################################################################
# Extension Manager/Repository config file for ext: "t3sec_saltedpw"
#
# Auto generated 26-11-2008 13:36
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3 Security - Salted FE and BE user password hashes',
	'description' => 'Uses Portable PHP password hashing framework for storing passwords. Integrates into system extension "felogin" and extension "feuser_admin". Use SSL to secure datatransfer! PLEASE READ MANUAL FIRST!',
	'category' => 'services',
	'shy' => 0,
	'dependencies' => 'cms,felogin',
	'conflicts' => 'kb_md5fepw,newloginbox,pt_feauthcryptpw',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Marcus Krause',
	'author_email' => 'marcus#exp2008@t3sec.info',
	'author_company' => 'TYPO3 Security Team',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.0-0.0.0',
			'php' => '5.1.0-0.0.0',
			'cms' => '',
			'felogin' => '',
		),
		'conflicts' => array(
			'kb_md5fepw' => '',
			'newloginbox' => '',
			'pt_feauthcryptpw' => '',
		),
		'suggests' => array(
			'feuser_admin' => '',
			'phpunit' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:20:{s:9:"ChangeLog";s:4:"c82e";s:11:"LICENSE.txt";s:4:"6f86";s:20:"class.ext_update.php";s:4:"1b05";s:21:"ext_conf_template.txt";s:4:"b755";s:12:"ext_icon.gif";s:4:"4324";s:17:"ext_localconf.php";s:4:"4784";s:14:"ext_tables.php";s:4:"3398";s:14:"ext_tables.sql";s:4:"de0e";s:28:"ext_typoscript_constants.txt";s:4:"887f";s:24:"ext_typoscript_setup.txt";s:4:"8748";s:48:"tests/class.tx_t3secsaltedpw_phpass_testcase.php";s:4:"9024";s:34:"res/ux/class.ux_tx_felogin_pi1.php";s:4:"f276";s:30:"res/ux/class.ux_tx_install.php";s:4:"bf89";s:25:"res/ux/ux_fe_adminLib.inc";s:4:"d9bb";s:41:"res/lib/class.tx_t3secsaltedpw_phpass.php";s:4:"a749";s:31:"res/tmpl/fe_admin_fe_users.tmpl";s:4:"064e";s:44:"res/staticlib/class.tx_t3secsaltedpw_div.php";s:4:"fe48";s:42:"res/eval/class.tx_t3secsaltedpw_salted.php";s:4:"79cc";s:14:"doc/manual.sxw";s:4:"911d";s:34:"sv1/class.tx_t3secsaltedpw_sv1.php";s:4:"903f";}',
	'suggests' => array(
	),
);

?>