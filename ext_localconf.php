<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_t3secsaltedpw_salted'] = t3lib_extMgm::extPath($_EXTKEY, 'res/eval/class.tx_t3secsaltedpw_salted.php');
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin/pi1/class.tx_felogin_pi1.php'] = t3lib_extMgm::extPath($_EXTKEY).'res/ux/class.ux_tx_felogin_pi1.php';

t3lib_extMgm::addService(
	't3sec_saltedpw',
	'auth',
	'tx_t3secsaltedpw_sv1',
	array(
		'title' => 'FE Authentification salted',
		'description' => '',
		'subtype' => 'authUserFE',
		'available' => TRUE,
		'priority' => 90,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath('t3sec_saltedpw').'sv1/class.tx_t3secsaltedpw_sv1.php',
		'className' => 'tx_t3secsaltedpw_sv1',
	)
);
?>