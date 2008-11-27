<?php

########################################################################
# Extension Manager/Repository config file for ext: "t3sec_femd5salted"
#
# Auto generated 15-11-2008 06:27
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'salted MD5 FE passwords',
	'description' => 'Sets the FE password type to password,md5. Also enables md5 hashed password for registration using "feadmin_user"',
	'category' => 'fe',
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
	'clearCacheOnLoad' => 0,
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
		),
		'conflicts' => array(
			'kb_md5fepw' => '',
			'newloginbox' => '',
			'pt_feauthcryptpw' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:12:"ext_icon.gif";s:4:"5a9a";s:17:"ext_localconf.php";s:4:"f804";s:14:"ext_tables.php";s:4:"0cc9";s:14:"ext_tables.sql";s:4:"4163";s:34:"res/ux/class.ux_t3lib_tceforms.php";s:4:"cfa9";s:47:"res/staticlib/class.tx_t3secfemd5salted_div.php";s:4:"e876";s:20:"res/js/md5_salted.js";s:4:"c692";s:48:"res/eval/class.tx_t3secfemd5salted_md5salted.php";s:4:"8966";s:37:"sv1/class.tx_t3secfemd5salted_sv1.php";s:4:"957c";}',
	'suggests' => array(
	),
);

?>