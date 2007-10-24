<?php

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/commerce_coupons/mod_coupons/');
$BACK_PATH='../../../../typo3/';

$MCONF['name']='txcommerceM1_txcommerceM2';
$MCONF['access']='user,group';
//$MCONF['script']='../../../../typo3/db_list.php';
$MCONF['script']='index.php';
$MCONF['navFrameScript']='class.tx_commerce_coupons_navframe.php';


$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref']='LLL:EXT:commerce_coupons/mod_coupons/locallang_mod.xml';
?>