<?php

########################################################################
# Extension Manager/Repository config file for ext: "saltedpasswords"
#
# Auto generated 18-06-2009 10:19
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Salted user password hashes',
	'description' => 'Uses password hashing framework for storing passwords. Integrates into system extension "felogin". Use SSL or rsaauth to secure datatransfer! PLEASE READ MANUAL FIRST!',
	'category' => 'services',
	'shy' => 0,
	'dependencies' => 'cms',
	'conflicts' => 'kb_md5fepw,newloginbox,pt_feauthcryptpw,t3sec_saltedpw',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users,be_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Marcus Krause, Steffen Ritter',
	'author_email' => 'marcus#exp2009@t3sec.info',
	'author_company' => 'TYPO3 Security Team',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-0.0.0',
			'php' => '5.2.0-0.0.0',
			'cms' => '',
		),
		'conflicts' => array(
			'kb_md5fepw' => '',
			'newloginbox' => '',
			'pt_feauthcryptpw' => '',
			't3sec_saltedpw' => '',
		),
		'suggests' => array(
			'rsaauth' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:10:{s:21:"ext_conf_template.txt";s:4:"863d";s:12:"ext_icon.gif";s:4:"4324";s:17:"ext_localconf.php";s:4:"dd42";s:14:"ext_tables.php";s:4:"f3ff";s:14:"ext_tables.sql";s:4:"86d5";s:40:"classes/class.tx_saltedpasswords_div.php";s:4:"5187";s:46:"classes/eval/class.tx_saltedpasswords_eval.php";s:4:"ab92";s:49:"classes/eval/class.tx_saltedpasswords_eval_be.php";s:4:"76e7";s:49:"classes/eval/class.tx_saltedpasswords_eval_fe.php";s:4:"8b01";s:36:"sv1/class.tx_saltedpasswords_sv1.php";s:4:"5ba0";}',
	'suggests' => array(
	),
);

?>