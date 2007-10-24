<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Ralf Merz <ralf.merz@heindl.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Part of the COMMERCECOUPONS (Advanced Shopping System) extension.
 * ProcessMarkerArray: add the markers for the coupon_view in the listing
 *
 * @author	Ralf Merz <ralf.merz@heindl.de>
 *
 * @TODO: ...
 *
 * @see tx_commerce_basket
 * @see tx_commerce_basic_basekt
 *
 * $Id: $
 */


require_once(PATH_tslib."class.tslib_pibase.php");

/**
 * tx_commerce includes
 */
require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_product.php');
require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_basket.php');
require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_basket_item.php');
require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_category.php');
require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_pibase.php');

/**
 * tx_commercecoupons includes
 */
require_once(t3lib_extMgm::extPath('commerce_coupons').'lib/class.tx_commercecoupons_lib.php');


class tx_commercecoupons_colistinghooks	extends tx_commerce_basket_item {

	/**
	 * adds the Coupon-Markers to the basketarray.
	 * checks, if an related article was removed from basket, so we also remove the coupon
	 * ToDo: check if quantity of a related article is only 1, not higher
	 */
	function ProcessMarker($markerArray, &$pObj){
	
		## don´t allow, that the quantity of a coupon can be changed
		$article_id = $pObj->piVars['artAddUid'];
		if(is_array($article_id)){
			foreach($article_id as $art => $aObj){
	
				if(intval($aObj['count']) == 0 || intval($aObj['count']) > 1){
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_commercecoupons_articles','article_id = '.$art.$GLOBALS['TSFE']->cObj->enableFields('tx_commercecoupons_articles'));
					if($row = mysql_fetch_assoc($res)){
						$couponArtUid = $pObj->basket->get_articles_by_article_type_uid_asuidlist(8);
						$pObj->basket->delete_article($couponArtUid[0]);
						unset($basket->basket_items[$couponArtUid]);
						unset($GLOBALS['TSFE']->fe_user->sesData['coupons']);
						$pObj->couponObj = new tx_commercecoupons_lib($pObj);
						$pObj->couponObj->unsetSessionCoupons();
						$pObj->basket->store_data();
					} 
				}

			}
		}
		
		# no basket anymore in pObj, so we get it from the GLOBALS
		$pObj->basket = $GLOBALS['TSFE']->fe_user->tx_commerce_basket;

		$markerArray['###COUPON_VIEW###'] = '';
		// display coupons in basket view
  	$couponArticles = array_merge($pObj->basket->get_articles_by_article_type_uid_asuidlist($pObj->conf['couponNormalType']), $pObj->basket->get_articles_by_article_type_uid_asuidlist($pObj->conf['couponRelatedType']));
  
  	// it is better to get the coupon-template code from a separated file
 		$pObj->templateCodeCoupon = $pObj->cObj->fileResource($pObj->conf['couponListingTemplateFile']);
  	$couponTemplate = $pObj->cObj->getSubpart($pObj->templateCodeCoupon, '###COUPON_VIEW_IN_LISTING###');

	  foreach ($couponArticles as $currentCouponArticleUid) {
	  			
			$sessionCoupons = $GLOBALS['TSFE']->fe_user->sesData['coupons'];
			
			$couponCodesArray = array();
			$couponCodes = '';
			foreach($sessionCoupons as $sc => $scObj){
				if($currentCouponArticleUid == intval($scObj['articleId'])){
					$couponCodesArray[] = $scObj['code'];
				}
			}
			$couponCodes = implode(', ', $couponCodesArray);
	  	
	   	$priceCoupons += $pObj->basket->basket_items[$currentCouponArticleUid]->get_item_sum_gross();
	   	$ma['###ARTICLE_TOTAL_PRICE###'] = tx_moneylib::format($pObj->basket->basket_items[$currentCouponArticleUid]->get_item_sum_gross(), $pObj->conf['currency']);
	   	$ma['###PRODUCT_TITLE###'] = $pObj->basket->basket_items[$currentCouponArticleUid]->article->get_title();
	   	$ma['###PRODUCT_DESCRIPTION###'] = $pObj->basket->basket_items[$currentCouponArticleUid]->product->get_description();
	   	$ma['###COUPON_CODES###'] = $couponCodes;
	   	if (!empty($pObj->basket->basket_items[$currentCoupnArticleUid]->tx_commercecoupons_addedbycouponid)) {
	   		$couponCode = $GLOBALS['TYPO3_DB']->exec_SELECTquery('code', 'tx_commercecoupons_coupons', 'article=' .$pObj->basket->basket_items[$currentCoupnArticleUid]->tx_commercecoupons_addedbycouponid);
	   		$couponCode = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($couponCode);

	   		$ma['###PRODUCT_TITLE###'] .= ' (' .$couponCode['code'] .')';
	   	}
	   	$markerArray['###COUPON_VIEW###'] .= $pObj->cObj->substituteMarkerArray($couponTemplate, $ma);
	  }
	
	  $price_gross2 = $pObj->basket->getArticleTypeSumGross(NORMALArticleType);
	  $priceSub = $price_gross2 - $priceCoupons -$priceDel;
	  $markerArray['###BASKET_GROSS_PRICE_SUB###'] = tx_moneylib::format($priceSub,$pObj->currency);
	
	  $markerArray['###COUPON_FORMSTART###']  = '
	   <form name="coupon" action="'.$pObj->pi_getPageLink($pObj->conf['basketPid']).'" method="get">
	   <input type="hidden" name="id" value="'.$pObj->conf['basketPid'].'" />
	  ';
	
	  $markerArray['###DISCOUNTCOUPON_FORMSTART###']  = '
	   <form name="discountcoupon" action="'.$pObj->pi_getPageLink($pObj->conf['basketPid']).'" method="get">
	   <input type="hidden" name="id" value="'.$pObj->conf['basketPid'].'" />
	  ';
	
	  #$discountCoupons = $this->couponObj->getSessionCoupons_discountOnly();
	
	   // deaktiviert, weil discountcoupons noch nicht implementiert sind
	  if(false && is_array($discountCoupons) && count($discountCoupons)) {
	    $markerArray['###DISCOUNTCOUPON_FORM_STYLE###']  = 'display:none;';
	    $markerArray['###DISCOUNTCOUPON_MESSAGE_STYLE###']  = '';
	
	   list(,$discountCoupon) = each($discountCoupons);
	          # $markerArray['###DISCOUNTCOUPON_DETAILS###']  = 'Sie haben Ihren Rabattcoupon mit dem Couponcode "'.$discountCoupon['code'].'" eingel&ouml;st.';
	  } else {
	   $markerArray['###DISCOUNTCOUPON_FORM_STYLE###']  = '';
	   $markerArray['###DISCOUNTCOUPON_MESSAGE_STYLE###']  = 'display:none;';
	  }
	  return $markerArray;
	}
}
?>