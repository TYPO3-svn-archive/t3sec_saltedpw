<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['password']['config']['max'] = 44;
//$TCA['fe_users']['columns']['password']['config']['eval'] .= ',tx_t3secmd5salted_md5salted,password';
$TCA['fe_users']['columns']['password']['config']['eval'] = 'nospace,required,tx_t3secfemd5salted_md5salted';
?>
