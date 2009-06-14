<?php
	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

	//form evaluation function - FE users only
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_saltedpasswords_eval_fe'] = 'EXT:saltedpasswords/classes/eval/class.tx_saltedpasswords_eval_fe.php';

	//form evaluation function - BE users only
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_saltedpasswords_eval_be'] = 'EXT:saltedpasswords/classes/eval/class.tx_saltedpasswords_eval_be.php';


t3lib_extMgm::addService(
	'saltedpasswords',
	'auth',
	'tx_saltedpasswords_sv1',
	array(
		'title' => 'FE/BE Authentification salted',
		'description' => 'Salting of passwords for Frontend and Backend',
		'subtype' => 'authUserFE,authUserBE',
		'available' => TRUE,
		'priority' => 55, // must be higher than tx_sv_auth (50) and lower than OpenID (75) and RsaAuth(60)
		'quality' => 55,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath('saltedpasswords').'sv1/class.tx_saltedpasswords_sv1.php',
		'className' => 'tx_saltedpasswords_sv1',
	)
);
?>