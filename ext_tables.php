<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['password']['config']['max'] = 40;
$TCA['fe_users']['columns']['password']['config']['eval'] = 'required,tx_t3secsaltedpw_salted,password';
t3lib_div::loadTCA('be_users');
$TCA['be_users']['columns']['password']['config']['max'] = 40;
$TCA['be_users']['columns']['password']['config']['eval'] = 'required,tx_t3secsaltedpw_salted,password';
?>
