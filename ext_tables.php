<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');

t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['password']['config']['max'] = 40;

if (tx_t3secsaltedpw_div::isUsageEnabled('FE')) {
	$TCA['fe_users']['columns']['password']['config']['eval'] = 'trim,required,tx_t3secsaltedpw_salted,password';
}
t3lib_div::loadTCA('be_users');
$TCA['be_users']['columns']['password']['config']['max'] = 40;

if (tx_t3secsaltedpw_div::isUsageEnabled('BE')) {
	$TCA['be_users']['columns']['password']['config']['eval'] = 'trim,required,tx_t3secsaltedpw_salted,password';
}
?>
