<?php
	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

	//form evaluation function - FE users only
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_saltedpasswords_eval_fe'] = 'EXT:saltedpasswords/classes/eval/class.tx_saltedpasswords_eval_fe.php';

	//form evaluation function - BE users only
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_saltedpasswords_eval_be'] = 'EXT:saltedpasswords/classes/eval/class.tx_saltedpasswords_eval_be.php';

	//hook for processing "forgotPassword" in felogin
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['password_changed'][] = 'EXT:saltedpasswords/classes/class.tx_saltedpasswords_div.php:tx_saltedpasswords_div->feloginForgotPasswordHook';

t3lib_extMgm::addService(
	'saltedpasswords',
	'auth',
	'tx_saltedpasswords_sv1',
	array(
		'title' => 'FE/BE Authentification salted',
		'description' => 'Salting of passwords for Frontend and Backend',
		'subtype' => 'authUserFE,authUserBE',
		'available' => TRUE,
		'priority' => 70, // must be higher than tx_sv_auth (50) and rsaauth (60) but lower than OpenID (75)
		'quality' => 70,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath('saltedpasswords').'sv1/class.tx_saltedpasswords_sv1.php',
		'className' => 'tx_saltedpasswords_sv1',
	)
);
?>