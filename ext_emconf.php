<?php

########################################################################
# Extension Manager/Repository config file for ext: "t3sec_saltedpw"
#
# Auto generated 17-11-2008 14:07
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3 Security: Salted FE and BE user passwords',
	'description' => 'Uses Portable PHP password hashing framework for storing passwords. Integrates into system extension "felogin" and extension "feadmin_user". Use SSL to secure datatransfer!',
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
	'version' => '0.0.4',
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
	'_md5_values_when_last_written' => 'a:17:{s:10:"README.txt";s:4:"3501";s:20:"class.ext_update.php";s:4:"5689";s:21:"ext_conf_template.txt";s:4:"2830";s:12:"ext_icon.gif";s:4:"4324";s:17:"ext_localconf.php";s:4:"047b";s:14:"ext_tables.php";s:4:"294a";s:14:"ext_tables.sql";s:4:"c67c";s:28:"ext_typoscript_constants.txt";s:4:"887f";s:45:"tests/class.tx_t3secsaltedpw_div_testcase.php";s:4:"29e9";s:48:"tests/class.tx_t3secsaltedpw_phpass_testcase.php";s:4:"7069";s:34:"res/ux/class.ux_tx_felogin_pi1.php";s:4:"c4b3";s:25:"res/ux/ux_fe_adminLib.inc";s:4:"71e3";s:41:"res/lib/class.tx_t3secsaltedpw_phpass.php";s:4:"ab71";s:31:"res/tmpl/fe_admin_fe_users.tmpl";s:4:"064e";s:44:"res/staticlib/class.tx_t3secsaltedpw_div.php";s:4:"0a65";s:42:"res/eval/class.tx_t3secsaltedpw_salted.php";s:4:"3171";s:34:"sv1/class.tx_t3secsaltedpw_sv1.php";s:4:"5e3e";}',
	'suggests' => array(
	),
);

?>