<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005  Volker Graubaum (vg@e-netconsulting.de)
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
 * Plugin 'Coupon Handling' for the 'commerce_coupons' extension.
 *
 * @author	Volker Graubaum  <vg@e-netconsulting.de>
 */


/**
 * tx_commerce includes
 */
require_once(PATH_txcommerce.'lib/class.tx_commerce_category.php');

define('ERROR_COUPON_CODEMISSING',1);
define('ERROR_COUPON_EXISTS',2);
define('ERROR_COUPON_LOWORDERVALUE',3);

class tx_commercecoupons_lib {

	var $basket;	// reference to basket object

	function tx_commercecoupons_lib(&$pObj){
		$this->init($pObj);
	}

	/**
	 * Init method for class for PHP5 and PHP4
	 */

	function init(&$pObj){
		$this->pObj = &$pObj;
		$this->basket = &$GLOBALS['TSFE']->fe_user->tx_commerce_basket;

		// do some clean up:
		$this->removeInvalidCouponsFromSession();
		$this->removeArticlesAddedByInvalidCoupon();
		$this->regenerateCouponArticle();
		$this->reduceAddedByCouponsValueIfQuantityIsLess();
		#$this->reduceRelatedCouponsValueIfQuantityIsLess();
	}

	// remove the array of related coupons for an article

	function reduceAddedByCouponsValueIfQuantityIsLess() {
		if(is_array($this->basket->basket_items)){
			foreach($this->basket->basket_items as $articleUid => $item) {
				if(is_array($item->tx_commercecoupons_addedbycouponid)) {
					#debug($item);
				}
				if(is_array($item->tx_commercecoupons_addedbycouponid) && count($item->tx_commercecoupons_addedbycouponid)>$item->quantity) {
					$new_addedByCoupons_value = $this->getFirstItemsOfArray($item->tx_commercecoupons_addedbycouponid, $item->quantity);
					$old_addedByCoupons_value = $item->tx_commercecoupons_addedbycouponid;
					if(is_array($this->basket->basket_items[$articleUid]->tx_commercecoupons_relatedcoupon)) {

						$removedItems = array_diff($old_addedByCoupons_value,$new_addedByCoupons_value);
						$this->removeItemsByValue($this->basket->basket_items[$articleUid]->tx_commercecoupons_relatedcoupon,$removedItems);
					}
					$this->basket->basket_items[$articleUid]->tx_commercecoupons_addedbycouponid = $new_addedByCoupons_value;
				}
			}
		}
	}

	/*
	 * ToDo think about this function. Should an coupon be removed, if the article will be removed
	 *


	function reduceRelatedCouponsValueIfQuantityIsLess() {
		foreach($this->basket->basket_items as $articleUid => $item) {
			if(is_array($item->tx_commercecoupons_addedbycouponid) && count($item->tx_commercecoupons_addedbycouponid)>$item->quantity) {
				$new_addedByCoupons_value = $this->getFirstItemsOfArray($item->tx_commercecoupons_addedbycouponid, $item->quantity);
				$old_addedByCoupons_value = $item->tx_commercecoupons_addedbycouponid;
				if(is_array($this->basket->basket_items[$articleUid]->tx_commercecoupons_relatedcoupon)) {
					$removedItems = array_diff($old_addedByCoupons_value,$new_addedByCoupons_value);
					$this->removeItemsByValue($this->basket->basket_items[$articleUid]->tx_commercecoupons_relatedcoupon,$removedItems);
				}
				$this->basket->basket_items[$articleUid]->tx_commercecoupons_addedbycouponid = $new_addedByCoupons_value;
			}
		}
	}
	*/

	function removeInvalidCouponsFromSession() {

			// check if all coupons in the session are still valid and if not, drop them
		$coupons = $this->getSessionCoupons();
		$cleanedCoupons = array();
		foreach($coupons as $coupon) {
		
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_commercecoupons_coupons','uid = '.intval($coupon['uid']).$GLOBALS['TSFE']->cObj->enableFields('tx_commercecoupons_coupons'));
			if(mysql_fetch_assoc($res)) $cleanedCoupons[$coupon['uid']] = $coupon;
		}
		$this->setSessionCoupons($cleanedCoupons);

	}


	// if a coupon gets invalid (for example while trying to use it double)
	// remove also the articles

	function removeArticlesAddedByInvalidCoupon() {
		$coupons = $this->getSessionCoupons();
		if(is_array($this->basket->basket_items)){
			reset($this->basket->basket_items);
			foreach($this->basket->basket_items as $articleUid => $theItem) {
				$item = &$this->basket->basket_items[$articleUid];
				if(is_array($item->tx_commercecoupons_addedbycouponid)) {
					foreach($item->tx_commercecoupons_addedbycouponid as $key => $couponid) {
						if(!$coupons[$couponid]) {
							$this->basket->change_quantity($articleUid,$item->get_quantity()-1);
							unset($item->tx_commercecoupons_addedbycouponid[$key]);
						}
					}
				}
			}
		}
	}

	// recreate the session complete

	function regenerateCouponArticle() {

		$coupons = $this->getSessionCoupons();

		$behind = $this->getSessionCoupons_discountOnly();
		if(is_array($behind)){
		    $coupons2 = array();
		    while(list($k,$v) = each($behind)){
			    unset($coupons[$k]);
		    	    if($v['record']['type']<>'percent'){
			        $coupons[$k] = $v;
			    }else{
				$coupons2[$k] = $v;
			    }
		    }
		    while(list($k,$v) = each($coupons2)){
		        $coupons[$k] = $v;
		    }
		}
		// we have 2 article types: 1) having a related article (e.g. trial article) and only moneydiscount
		$couponArticleUids_type1 = $this->basket->get_articles_by_article_type_uid_asuidlist($this->pObj->conf['couponNormalType']);	// was 4
	  $couponArticleUids_type2 = $this->basket->get_articles_by_article_type_uid_asuidlist($this->pObj->conf['couponRelatedType']);	// was 5
		$couponArticleUids = array_merge($couponArticleUids_type1,$couponArticleUids_type2);
			// erstmal alle coupon artikel l�schen:
		foreach($couponArticleUids as $couponArticleUid) {
			if($this->basket->basket_items[$couponArticleUid]) {

				$this->basket->delete_article($couponArticleUid);
			}
		}

		if(!count($coupons)) return false;

		reset($coupons);

		$b = &$this->basket->basket_items;
			// dann alle neu generieren
		foreach($coupons as $coupon) {

			if($coupon['record']['type']=='percent'){
				$coupon = $this->getCouponData($coupon['uid'],true);
			}

				// add coupon article to the basket or add quantity to existing article
			if($b[$coupon['articleId']]->quantity) {

				$previous_price_net = $b[$coupon['articleId']]->get_price_net();
				$previous_price_gross = $b[$coupon['articleId']]->get_price_gross();
				$b[$coupon['articleId']]->tx_commercecoupons_addedbycouponid[] = $coupon['uid'];
				$b[$coupon['articleId']]->related_coupon = $coupon['uid'];
				$b[$coupon['articleId']]->setPriceNet($previous_price_net+$coupon['price_net']);
				$b[$coupon['articleId']]->setPriceGross($previous_price_gross+$coupon['price_gross']);
			} else {

				$this->basket->add_article($coupon['articleId']);
				$b[$coupon['articleId']]->tx_commercecoupons_addedbycouponid = array($coupon['uid']);
				$b[$coupon['articleId']]->related_coupon = array($coupon['uid']);
				$this->basket->changePrices($coupon['articleId'],$coupon['price_gross'],$coupon['price_net']);
			}
		}
		
		
		$this->basket->store_data();
	}



	// get the first x items of an array

	function getFirstItemsOfArray($array, $numberOfItemsToReturn) {
		$outArray = array();
		foreach($array as $key => $value) {
			if($count++ < $numberOfItemsToReturn) {
				$outArray[$key] = $value;
			}
		}
		return $outArray;
	}

	// removing some elements from an array

	function removeItemsByValue(&$array,$itemsToRemove) {
		foreach($array as $key => $value) {
			if(in_array($value,$itemsToRemove)) unset($array[$key]);
		}
	}



	// add a coupon to the session

	function addCoupon($code,$isId=false){
		$coupon = $this->getCouponData($code,$isId);
		#debug($coupon,'METHOD addCoupon');
		if(is_array($coupon) AND count($coupon) > 1) {
			$sessionCoupons = $this->getSessionCoupons();
#debug($coupon['uid'], 'Session');
			if(!$sessionCoupons[$coupon['uid']]) {

				$this->setSessionCoupon($coupon);
				#$this->changeCouponCount($coupon);	// depricated
				$this->regenerateCouponArticle();
				
				#debug($coupon, 'coupon in lib, addCoupon');
				
				return $coupon;
			} else {	// if coupon exists
				#debug('BEREITS VORHANDEN');
				return ERROR_COUPON_EXISTS;
					#$this->changeCouponCount($sessionCoupons);
			}
		}
		return $coupon;
	}


	// gets the user, if needed

	function getUser($orderId){
	    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_commerce_orders','uid = \''.$orderId.'\'');
	    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	    $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','uid = \''.$row['cust_fe_user'].'\'');
	    $user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2);
	    return $user;

	}

	// get the couponData for an special coupon
	// @ToDo clear tax things in the limit check
	// @ToDo clear for multiple coupons in one code

	function getCouponData($code,$isId=false){
		$couponData =  ERROR_COUPON_CODEMISSING;
	    $couponEnableFolderWhere = '';
		if(!$isId) {
			$coupon_pid = array_unique(tx_graytree_folder_db::initFolders('Coupons', 'commerce', 0, 'Commerce'));
			$couponFolder = array_unique(tx_graytree_folder_db::initFolders('Enabled', 'commerce', $coupon_pid[0]));
			$couponUseFolder[] = $couponFolder[0];
			$couponFolder = array_unique(tx_graytree_folder_db::initFolders('Ordered', 'commerce', $coupon_pid[0]));
			$couponUseFolder[] = $couponFolder[0];
			$couponEnableFolderWhere = ' AND pid in (' . implode(',',$couponUseFolder) .')';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_commercecoupons_coupons',($isId?'uid='.intval($code):'code = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($code, 'tx_commercecoupons_coupons')).$GLOBALS['TSFE']->cObj->enableFields('tx_commercecoupons_coupons').$couponEnableFolderWhere);
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

			if(intval($row['count']) > 0 || intval($row['count']) == -1){
				$calculationPrice = $this->calculatePriceForRabatt();
							
				$categoryObject = new tx_commerce_category();
				
				// get parent categories of articles in the basket
				foreach($this->basket->basket_items as $item) {
					if(intval($item->article->article_type_uid) == 1 ) {	// only normal Article Types
						$parentCategory = $item->product->getMasterparentCategorie();		// get_parent_category($item->product->uid);
						
						$categoryObject->init($parentCategory);
						#$categoryObject->load_data();
						$catUidlist = array_unique(array_merge($catUidlist,$categoryObject->get_categorie_rootline_uidlist()));
					}	
				}
				debug($parentCategory, 'parent Categories');
				debug($catUidlist, 'cat UID list');
				
				$relatedCategories = explode(',', $row['related_categories']);
				debug($relatedCategories, 'relatedCategories');
				
				debug($row['include_exclude_category'], '1 = include, 0 = exclude');
																
				// here we check if the articles in the basket belong
				// to the categories selected for the coupon
				if(intval($row['include_exclude_category']) == 0) { // categories will be excluded
					$row['related_categories'];
				
				} elseif(intval($row['include_exclude_category']) == 1) { // categories will be included
					$catOK = array();
					foreach($catUidlist as $catUid) {
						if(in_array($catUid, $relatedCategories)) {
							$catOK[] = true; 
							debug($catUid, 'category is included');
						} else {
							debug($catUid, 'this article is not in the categoryIncludeList');
						}
						#debug($catUid, 'ausserhalb IF category is included');
					}
				}
				
				if(in_array(true, $catOK)) {
					debug($catOK, 'OK');
				}
				
				#debug($row, 'Felder aus DB');
				
				if(($row['limit_start'] <= $calculationPrice['gross'] || $row['limit_start'] == 0)){	
					$couponData = array();
					# $calculationPrice = $this->calculatePriceForRabatt();
					// everything OK; set Information
					if($row['type'] == 'percent'){
						if($row['limit_end']&&$row['limit_end'] < $calculationPrice['gross']*($row['amount_percent']/100)){
							
							$couponData['price_net']   = 	- intval($row['limit_end'])/1.19;		// *($row['amount_percent']/10))/1.19;		// quotient was 100 originally
							$couponData['price_gross'] = 	- intval($row['limit_end']);		// *($row['amount_percent']/10)));
						}else{
							$couponData['price_net']   = 	- intval($calculationPrice['net']*($row['amount_percent']/100)); ## - $row['limit_end'];		//
							$couponData['price_gross'] =  - intval($calculationPrice['gross']*($row['amount_percent']/100)); ##  - $row['limit_end'];			//
						}
					} else {
						$couponData['price_net']   = -$row['amount_net'];
						$couponData['price_gross']   = -$row['amount_gross'];
					}

					$couponData['articleId'] = $row['article'];
					$couponData['record'] = $row;
					$couponData['code'] = $row['code'];
					$couponData['uid'] = $row['uid'];
					$couponData['has_articles'] = $row['has_articles'];
				} else {
					$couponData =  ERROR_COUPON_LOWORDERVALUE;
				}
			} 
		}
		return $couponData;
	}

	// gets the price for percental rabatt
	function calculatePriceForRabatt(){
// @ToDo remove static Values 

	 $basketIns = array_merge($this->basket->get_articles_by_article_type_uid_asuidlist(1));  //,$this->basket->get_articles_by_article_type_uid_asuidlist($this->pObj->conf['couponNormalType']),$this->basket->get_articles_by_article_type_uid_asuidlist($this->pObj->conf['couponRelatedType'])); // 4 and 5
	 $value = array('net'=>0,'gross'=>0);
	    
	 foreach ($basketIns as $itemObjId) {
		$temp = $this->basket->basket_items[$itemObjId];
		$temp->recalculate_item_sums();
		
		$value['net'] += $temp->get_item_sum_net();
		$value['gross'] += $temp->get_item_sum_gross();
	    }

	    if($value['net']< 0){
		$value['net'] = 0;
	    }

	    if($value['gross'] < 0){
		$value['gross'] = 0;
	    }
	    return $value;
	}

	//  add an article to the basket, if it wasn'T insert already. Otherwise change the quantity
	function addCouponArticles($coupon = array()){
		if(!$coupon['record']['has_articles']) return false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_commercecoupons_articles','uid = \''.$coupon['record']['related_articles'].'\''.$GLOBALS['TSFE']->cObj->enableFields('tx_commercecoupons_articles'));
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
		    if($row['amount']>0) {

		    	$returnData[$row['article_id']] = $row;

					// add article to the basket or add quantity if article is already in basket
				if($this->basket->basket_items[$row['article_id']]) {
					$item = &$this->basket->basket_items[$row['article_id']];
					$item->change_quantity(1);
					$this->basket->changePrices($row['article_id'],$row['price_gross'],$row['price_net']);
				} else {
					$this->basket->add_article($row['article_id'],1); // has been: $row['amount']
					$item = &$this->basket->basket_items[$row['article_id']];
					$this->basket->changePrices($row['article_id'],$row['price_gross'],$row['price_net']);
				}

				$item->tx_commercecoupons_addedbycouponid[] = $coupon['uid'];
				$item->tx_commercecoupons_relatedcoupon[] = $coupon['uid'];

		        }
		}

		$this->basket->store_data();
			// store information about articles added by this coupon into coupons session array
		$coupon['_addedArticles'] = $returnData;
		$this->setSessionCoupon($coupon);

		return $returnData;
	}

	// returns a single coupon from the database
	function getCoupon($id) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_commercecoupons_coupons','uid = '.intval($id).$GLOBALS['TSFE']->cObj->enableFields('tx_commercecoupons_coupons'));
		return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	// retuen a single coupon from session
	function getSessionCoupon($id) {
		$couponsArray = $this->getSessionCoupons();
		return $couponsArray[$id];
	}

	// adds a single coupon to the session
	function setSessionCoupon($coupon) {
		$couponsArray = $this->getSessionCoupons();
		$couponsArray[$coupon['uid']] = $coupon;
		$this->setSessionCoupons($couponsArray);
	}

	// set all coupons
	function setSessionCoupons($couponsArray) {
		$GLOBALS['TSFE']->fe_user->setKey('ses','coupons',$couponsArray);
	}
	// unset all coupons
	function unsetSessionCoupons() {
		$GLOBALS['TSFE']->fe_user->setKey('ses','coupons','');
	}

	// return the array with all user coupons
	function getSessionCoupons() {
		$coupons = $GLOBALS['TSFE']->fe_user->getKey('ses','coupons');
		return is_array($coupons)?$coupons:array();
	}


	// return all coupons without articles
	function getSessionCoupons_discountOnly() {
		if($couponsArray = $this->getSessionCoupons()) {
			foreach($couponsArray as $couponId => $coupon) {
				if(!$coupon['has_articles']) $outArray[$couponId] = $coupon;
			}
			return $outArray;
		} else {
			return false;
		}
	}

	// return all coupons with articles
	function getSessionCoupons_hasArticlesOnly() {
		if($couponsArray = $this->getSessionCoupons()) {
			foreach($couponsArray as $couponId => $coupon) {
				if($coupon['has_articles']) $outArray[$couponId] = $coupon;
			}
			return $outArray;
		} else {
			return false;
		}
	}
}
?>