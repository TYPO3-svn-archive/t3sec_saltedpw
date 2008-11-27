<?php
	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_t3secsaltedpw_salted'] = 'EXT:' .  $_EXTKEY . '/res/eval/class.tx_t3secsaltedpw_salted.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin/pi1/class.tx_felogin_pi1.php'] = t3lib_extMgm::extPath($_EXTKEY, 'res/ux/class.ux_tx_felogin_pi1.php');
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/install/mod/class.tx_install.php'] = t3lib_extMgm::extPath($_EXTKEY, 'res/ux/class.ux_tx_install.php');

if ($GLOBALS['TYPO3_LOADED_EXT']['feuser_admin']) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['media/scripts/fe_adminLib.inc'] = t3lib_extMgm::extPath($_EXTKEY, 'res/ux/ux_fe_adminLib.inc');
}

t3lib_extMgm::addService(
	't3sec_saltedpw',
	'auth',
	'tx_t3secsaltedpw_sv1',
	array(
		'title' => 'FE/BE Authentification salted',
		'description' => 'Salting of passwords with PHPassword for Frontend and Backend',
		'subtype' => 'authUserFE,authUserBE',
		'available' => TRUE,
		'priority' => 60, // must be higher than tx_sv_auth (50) and lower than OpenID (75)
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath('t3sec_saltedpw').'sv1/class.tx_t3secsaltedpw_sv1.php',
		'className' => 'tx_t3secsaltedpw_sv1',
	)
);
?>