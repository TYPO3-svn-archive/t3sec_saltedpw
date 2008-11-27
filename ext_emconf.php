<?php

########################################################################
# Extension Manager/Repository config file for ext: "t3sec_saltedpw"
#
# Auto generated 15-11-2008 08:43
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3 Security: Salted MD5 FE passwords',
	'description' => 'Sets the FE password type to salted md5. Also enables salted md5 hashed password for registration using sysext "felogin" and "feadmin_user"',
	'category' => 'fe',
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
	'version' => '0.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.0-0.0.0',
			'php' => '5.0.0-0.0.0',
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
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:10:"README.txt";s:4:"26f1";s:12:"ext_icon.gif";s:4:"4324";s:17:"ext_localconf.php";s:4:"317f";s:14:"ext_tables.php";s:4:"dbe6";s:14:"ext_tables.sql";s:4:"4163";s:34:"res/ux/class.ux_t3lib_tceforms.php";s:4:"cfa9";s:34:"res/ux/class.ux_tx_felogin_pi1.php";s:4:"3f60";s:44:"res/staticlib/class.tx_t3secsaltedpw_div.php";s:4:"cdee";s:20:"res/js/md5_salted.js";s:4:"c692";s:39:"res/eval/class.tx_t3secsaltedpw_md5.php";s:4:"0de3";s:34:"sv1/class.tx_t3secsaltedpw_sv1.php";s:4:"0d94";}',
	'suggests' => array(
	),
);

?>