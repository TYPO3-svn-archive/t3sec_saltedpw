<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");


	//$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/class.tslib_content.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_tslib_content.php';
if ($GLOBALS['TYPO3_LOADED_EXT']['feuser_admin']) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['media/scripts/fe_adminLib.inc'] = t3lib_extMgm::extPath($_EXTKEY).'ux_feadminLib.php';
}

//$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tceforms.php'] = t3lib_extMgm::extPath($_EXTKEY, 'res/ux/class.ux_t3lib_tceforms.php');
//$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_t3secmd5salted_md5salted'] = 'EXT:t3sec_md5salted/class.tx_t3secmd5salted_md5salted.php';
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_t3secfemd5salted_md5salted'] = t3lib_extMgm::extPath($_EXTKEY, 'res/eval/class.tx_t3secfemd5salted_md5salted.php');

t3lib_extMgm::addService(
	't3sec_femd5salted',
	'auth',
	'tx_t3secfemd5salted_sv1',
	array(
		'title' => 'FE Authentification salted',
		'description' => '',
		'subtype' => 'authUserFE',
		'available' => TRUE,
		'priority' => 90,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath('t3sec_femd5salted').'sv1/class.tx_t3secfemd5salted_sv1.php',
		'className' => 'tx_t3secfemd5salted_sv1',
	)
);
?>