<?php

########################################################################
# Extension Manager/Repository config file for ext: "saltedpasswords"
#
# Auto generated 16-09-2009 12:36
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Salted user password hashes',
	'description' => 'Uses a password hashing framework for storing passwords. Integrates into the system extension "felogin". Use SSL or rsaauth to secure datatransfer! Please read the manual first!',
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
	'version' => '0.9.0',
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
	'_md5_values_when_last_written' => 'a:25:{s:9:"ChangeLog";s:4:"0962";s:21:"ext_conf_template.txt";s:4:"99b4";s:12:"ext_icon.gif";s:4:"4324";s:17:"ext_localconf.php";s:4:"3e72";s:14:"ext_tables.php";s:4:"f3ff";s:14:"ext_tables.sql";s:4:"a0d9";s:13:"locallang.xml";s:4:"3d55";s:40:"classes/class.tx_saltedpasswords_div.php";s:4:"cc59";s:49:"classes/class.tx_saltedpasswords_emconfhelper.php";s:4:"9e28";s:54:"classes/class.tx_saltedpasswords_emconfhelper.php.orig";s:4:"8fbe";s:57:"classes/salts/class.tx_saltedpasswords_abstract_salts.php";s:4:"2748";s:57:"classes/salts/class.tx_saltedpasswords_salts_blowfish.php";s:4:"1fee";s:56:"classes/salts/class.tx_saltedpasswords_salts_factory.php";s:4:"5d51";s:52:"classes/salts/class.tx_saltedpasswords_salts_md5.php";s:4:"1c27";s:55:"classes/salts/class.tx_saltedpasswords_salts_phpass.php";s:4:"e35f";s:63:"classes/salts/interfaces/interface.tx_saltedpasswords_salts.php";s:4:"63a1";s:46:"classes/eval/class.tx_saltedpasswords_eval.php";s:4:"4756";s:49:"classes/eval/class.tx_saltedpasswords_eval_be.php";s:4:"47b4";s:49:"classes/eval/class.tx_saltedpasswords_eval_fe.php";s:4:"29c6";s:36:"sv1/class.tx_saltedpasswords_sv1.php";s:4:"8754";s:41:"tests/tx_saltedpasswords_div_testcase.php";s:4:"35fb";s:52:"tests/tx_saltedpasswords_salts_blowfish_testcase.php";s:4:"07e3";s:51:"tests/tx_saltedpasswords_salts_factory_testcase.php";s:4:"dff7";s:47:"tests/tx_saltedpasswords_salts_md5_testcase.php";s:4:"5fa2";s:50:"tests/tx_saltedpasswords_salts_phpass_testcase.php";s:4:"460c";}',
	'suggests' => array(
	),
);

?>