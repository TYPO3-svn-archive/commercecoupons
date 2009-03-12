<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Volker Graubaum
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class contains some hooks for processing formdata.
 * Hook for saving coupon data and coupon_articles.
 *
 * @package commerce
 * @author Volker Graubaum
 *
 *
 * @TODO: Reformat Sourcecode, beautify
 */

class tx_commercecoupons_dmhooks {
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $pObj)	{
		// check if we have to do something
		if ($table == 'tx_commercecoupons_coupons')	{
			foreach($fieldArray as $key => $value) {
				if($key == 'limit_start' || $key == 'limit_end' || $key == 'amount_net' || $key == 'amount_gross') {
					$value = $value * 100;
					$fieldArray[$key] = (int)$value;
				}
			}
		}
		if ($table == 'tx_commercecoupons_articles')	{
			foreach($fieldArray as $key => $value) {
				if($key == 'price_net' || $key == 'price_gross') {
					$value = $value * 100;
					$fieldArray[$key] = (int)$value;
				}
			}
		}
		
		if ($table != 'tx_commercecoupons_coupons' || strtolower(substr($id, 0, 3)) == 'new')	{
			return;
		}
		
		
		
		
		$this->moveCoupon($status, $table, $id, $fieldArray, $th_obj);
	}
	
	function moveCoupon($status, $table, $id, &$fieldArray, &$th_obj) {
		if (in_array('newpid', array_keys($fieldArray)))	{
			$fieldArray['pid'] = $fieldArray['newpid'];
			$result=$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_commercecoupons_articles',' coupon_id = '.$id, array('pid'=>$fieldArray['newpid']));
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']["ext/commerce_coupons/hooks/class.tx_commercecoupons_dmhooks.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']["ext/commerce_coupons/hooks/class.tx_commercecoupons_dmhooks.php"]);
}
?>