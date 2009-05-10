<?php

########################################################################
# Extension Manager/Repository config file for ext: "t3sec_saltedpw"
#
# Auto generated 10-05-2009 03:09
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3 Security - Salted user password hashes',
	'description' => 'Uses Portable PHP password hashing framework for storing passwords. Integrates into system extension "felogin" and extension "feuser_admin". Use SSL to secure datatransfer! PLEASE READ MANUAL FIRST!',
	'category' => 'services',
	'shy' => 0,
	'dependencies' => 'cms',
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
	'author_email' => 'marcus#exp2009@t3sec.info',
	'author_company' => 'TYPO3 Security Team',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.2.7',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.0-0.0.0',
			'php' => '5.1.0-0.0.0',
			'cms' => '',
		),
		'conflicts' => array(
			'kb_md5fepw' => '',
			'newloginbox' => '',
			'pt_feauthcryptpw' => '',
		),
		'suggests' => array(
			'felogin' => '',
			'feuser_admin' => '',
			'phpunit' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:24:{s:9:"ChangeLog";s:4:"5dde";s:11:"LICENSE.txt";s:4:"6f86";s:20:"class.ext_update.php";s:4:"1ab8";s:21:"ext_conf_template.txt";s:4:"2e9c";s:12:"ext_icon.gif";s:4:"4324";s:17:"ext_localconf.php";s:4:"b313";s:14:"ext_tables.php";s:4:"e703";s:14:"ext_tables.sql";s:4:"de0e";s:28:"ext_typoscript_constants.txt";s:4:"887f";s:24:"ext_typoscript_setup.txt";s:4:"8748";s:48:"tests/class.tx_t3secsaltedpw_phpass_testcase.php";s:4:"5cf6";s:34:"res/ux/class.ux_tx_felogin_pi1.php";s:4:"e123";s:30:"res/ux/class.ux_tx_install.php";s:4:"0c2c";s:25:"res/ux/ux_fe_adminLib.inc";s:4:"c3d0";s:41:"res/lib/class.tx_t3secsaltedpw_phpass.php";s:4:"b466";s:31:"res/tmpl/fe_admin_fe_users.tmpl";s:4:"064e";s:30:"res/tmpl/felogin_template.html";s:4:"2abe";s:44:"res/staticlib/class.tx_t3secsaltedpw_div.php";s:4:"0f98";s:28:"res/LL/felogin_locallang.xml";s:4:"94dc";s:42:"res/eval/class.tx_t3secsaltedpw_salted.php";s:4:"388b";s:45:"res/eval/class.tx_t3secsaltedpw_salted_be.php";s:4:"cde7";s:45:"res/eval/class.tx_t3secsaltedpw_salted_fe.php";s:4:"92d3";s:14:"doc/manual.sxw";s:4:"3807";s:34:"sv1/class.tx_t3secsaltedpw_sv1.php";s:4:"bf8e";}',
	'suggests' => array(
	),
);

?>