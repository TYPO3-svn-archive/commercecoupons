<?php

	require_once(PATH_txgraytree.'lib/class.tx_graytree_folder_db.php');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi3/class.tx_commerce_pi3.php']['finishIt'][] = 'EXT:commerce_coupons/hooks/class.tx_commercecoupons_cohooks.php:tx_commercecoupons_cohooks';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:commerce_coupons/hooks/class.tx_commercecoupons_dmhooks.php:tx_commercecoupons_dmhooks';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi2/class.tx_commerce_pi2.php']['postartAddUid'][] = 'EXT:commerce_coupons/hooks/class.tx_commercecoupons_baskethooks.php:tx_commercecoupons_baskethooks';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi2/class.tx_commerce_pi2.php']['generateBasketMarker'][] = 'EXT:commerce_coupons/hooks/class.tx_commercecoupons_baskethooks.php:tx_commercecoupons_baskethooks';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi3/class.tx_commerce_pi3.php']['getListing'][] = 'EXT:commerce_coupons/hooks/class.tx_commercecoupons_colistinghooks.php:tx_commercecoupons_colistinghooks';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi3/class.tx_commerce_pi3.php']['generateMail'][] = 'EXT:commerce_coupons/hooks/class.tx_commercecoupons_mailhooks.php:tx_commercecoupons_mailhooks';
	
	t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_commercecoupons_pi1.php', '_pi1', 'list_type', 0);
	
	$TYPO3_CONF_VARS['EXTCONF'][COMMERCE_EXTkey]['SYSPRODUCTS']['COUPONS'] = array(
        'tablefields' => array (
                'title' => 'SYSTEMPRODUCT_COUPONS',
                'description' => 'Produkt fuer Gutscheine',
        ),
        'types' => array(
                'syscoupons' => array ('type'=>'4'),
                'syscoupons_special' => array ('type'=>'5'),
                'syscoupons_use' => array ('type'=>'6'),
        ),
	);
	
?>