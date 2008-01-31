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
 *
 *
 * @package TYPO3
 * @subpackage tx_commerce
 * @author Volker Graubaum
 */


class tx_commercecoupons_fields {

    function related_article($PA, $fObj)	{
	$cUid = $PA['row']['uid'];
	
	    // get the relation from coupon to article

	//HIER Fetch Fehler, $res ist leer
	// cUid ist was drin
	// datenbank hat noch keinen Eintrag, $res bleibt dadurch leer
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_commercecoupons_articles', 'coupon_id=\''.$cUid.'\'');

	//If Check
	if($res)
		$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	
	if (empty($data['article_id']))	return 'No article found';
	
	    // get article
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_commerce_articles', 'uid=' .$data['article_id']);
	$article = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	
	    // get prices for article
	$pricesRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_commerce_article_prices', 'uid_article=' .$article['uid']);
	
	    // compile result
	$result = '';
	$result = '<strong>' .$article['title'] .' (' .$article['uid'] .')</strong>';
	
	    // add prices
	while ($price = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pricesRes))	{
	    $result .= '<br /><span style="width: 20px"></span>' .sprintf('%2.2f', ($price['price_net'] /100)).' &euro; (net)<br />' .sprintf('%2.2f', ($price['price_gross'] /100)) .' &euro; (gross)';
	}
	
	    // return the result
	return $result;
    }

    function calculate_price($PA, $fObj)	{
    	$result = '';
		$result = '<input name="';
		$result .= $PA['itemFormElName'];
		$result .= '_hr" value="'.sprintf('%2.2f', ($PA['itemFormElValue'] /100)).'" style="width: 288px;" class="formField1" maxlength="256" onchange="typo3form.fieldGet(\'';
		$result .= $PA['itemFormElName'];
		$result .= '\',\'double2,nospace\',\'\',0,\'\');TBE_EDITOR.fieldChanged(\'';
		$result .= $PA['table'] . "','".$PA['row']['uid']."','" . $PA['field'] ."','" . $PA['itemFormElName'] ."');";
		$result .= '" type="text"><input name="';
		$result .= $PA['itemFormElName'];
		$result .= '" value="'.sprintf('%2.2f', ($PA['itemFormElValue'] /100)).'" type="hidden">';
    	return $result;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']["ext/commerce_coupons/class.tx_commercecoupons_fields.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']["ext/commerce_coupons/class.tx_commercecoupons_fields.php"]);
}
?>