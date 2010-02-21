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
 * @author	Volker Graubaum  <vg@e-netconsulting.de>, Ralf Merz <ralf@ralf-merz.de>
 */

/**
 * tx_commerce includes
 */
require_once(PATH_txcommerce.'lib/class.tx_commerce_pibase.php');
require_once(t3lib_extMgm::extPath('commerce_coupons').'lib/class.tx_commercecoupons_lib.php');
require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_div.php');

class tx_commercecoupons_pi1 extends tx_commerce_pibase {

	var $prefixId = 'tx_commercecoupons_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_commercecoupons_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'commerce_coupons';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $markerArray = array('###ERROR_MESSAGE###'=>'');

	function init($conf){
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		tx_commerce_div::initializeFeUserBasket();
		$this->couponObj = new tx_commercecoupons_lib($this);
		$this->template = $this->cObj->fileResource($this->conf["templateFile"]);
		if($this->piVars['step']) $this->step = $this->piVars['step'];
		if(!$this->step) $this->step = $this->conf['step'];
		if(!$this->step) $this->step = 'couponForm';

		#$this->debug('in tx_commercecoupons_pi1::init()');
	}


	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->init($conf);
		switch ($this->step){
			case 'couponForm' :
				#$this->couponObj->unsetSessionCoupons();
				$content = $this->showCouponForm();
				break;
			case 'addCoupon' :
				$content = $this->addCoupon();
				break;
				// new step here: returns true or false, can be used for ajax
				// inspired by David R�hr
			case 'addCouponByAjax' :
				$res = $this->addCoupon();
				return $res;
				break;
		}

		#$this->debug('am ende der tx_commercecoupons_pi1::main() ');

		return $this->pi_wrapInBaseClass($content);
	}

	// returns the couponForm

	function showCouponForm(){

		$coupons = $GLOBALS['TSFE']->fe_user->getKey('ses','coupons');

		// added 6.04.2009 by Erik Frister
		// disables the coupon form when no coupons are set
		// requested by Kneipp - don't consider this permanent for the coupons extension
		/*
		if(is_array($coupons) && 0 < count($coupons)) {
		return '';
		}
		*/
		//print '<pre>'; print_r($coupons); print '</pre>';
		$regularCoupon = false;
		$regularcontent = '';
		$activeCoupons = array();
		foreach($coupons as $coupon) {
			$activeCoupons[$coupon['record']['uid']] = $coupon['record']['own_field'];
			if ($coupon['record']['own_field'] == 0) {
				$regularCoupon = true;
			}

		}
		$couponids = array_keys($coupons);
		$this->sess_id = $this->couponObj->basket->sess_id;
		$template = $this->cObj->getSubpart($this->template,'###COUPON_FORM###');
		$template_regularcoupons = $this->cObj->getSubpart($this->template, '###REGULARCOUPONS###');
		$template_regularcoupon = $this->cObj->getSubpart($this->template, '###REGULARCOUPON###');
	
		#if (!$regularCoupon) {
			$regularmarkerArray = $this->markerArray;
			$regularmarkerArray['###URL###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);

			$regularmarkerArray['###SHOWCOUPONFORMTEXT###'] = $this->cObj->cObjGetSingle($this->conf['showCouponFormText'],$this->conf['showCouponFormText.']);
			// Ralf Merz: From whom the next line has been added? Chris?
			#$regularmarkerArray['###SHOWCOUPONFORMTEXT###'] =  $this->pi_getLL('showcouponformtext');
			
			$regularmarkerArray['###SUBMIT_CODE###'] =  '<input type="submit" value="'.$this->pi_getLL('submitCode').'" class="tx-commercecoupons-pi1-submit"/>';
			if($this->pi_getLL('submitCodeImg') != '') {
				$regularmarkerArray['###SUBMIT_CODE###'] =  $this->pi_getLL('submitCodeImg');
			}
			$regularcontent = $this->cObj->substituteMarkerArrayCached($template_regularcoupon, $regularmarkerArray);
		
		#}
		
		// break her and do not use "kneipp" specific things:
		return $this->cObj->substituteMarkerArrayCached($template, $regularmarkerArray);
		
		
		// go on with "kneipp" specific things
		$wrappedMarkerArray['###REGULARCOUPONS###'] = $regularcontent;

		$template_coupons = $this->cObj->getSubpart($this->template, '###COUPONS###');
		$template_coupon = $this->cObj->getSubpart($template_coupons, '###COUPON###');
		// get coupons with own field
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, code, button_label, description', 'tx_commercecoupons_coupons', 'own_field = 1'. tslib_cObj::enableFields('tx_commercecoupons_coupons'),'','sorting');
		$couponscontent = '';
		for ($i=0; $i<count($rows); $i++) {
			if (!array_key_exists($rows[$i]['uid'], $activeCoupons)){
				$couponmarkerArray = $this->markerArray;
				$couponmarkerArray['###URL###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
				$couponmarkerArray['###COUPON_UID###'] = $rows[$i]['uid'];
				$couponmarkerArray['###COUPON_SHOWCOUPONFORMTEXT###'] = $rows[$i]['description'];
				if (strlen($rows[$i]['button_label']) > 0) {
					$label = $rows[$i]['button_label'];
				} else {
					$label = $this->pi_getLL('submitCode');
				}
			
				$couponmarkerArray['###SHOWCOUPONFORMTEXT###'] = $this->cObj->cObjGetSingle($this->conf['showCouponFormText'],$this->conf['showCouponFormText.']);
				$couponmarkerArray['###COUPON_SUBMIT_CODE###'] =  '<input type="submit" value="'.$label.'" class="tx-commercecoupons-pi1-submit"/>';

				$couponscontent .= $this->cObj->substituteMarkerArrayCached($template_coupon, $couponmarkerArray);
			}
		}
		$wrappedMarkerArray['###COUPONS###'] = $couponscontent;
		$content = $this->cObj->substituteMarkerArrayCached($template,$markerArray , $wrappedMarkerArray);

		return $content;
	}

	// adds a coupon
	// returns the result if successful, otherwise returns the souponForm with error
	// @ToDo add locallang to the error

	function addCoupon(){

		$coupons = $GLOBALS['TSFE']->fe_user->getKey('ses','coupons');

		// added 6.04.2009 by Erik Frister
		// disables the coupon form when no coupons are set
		// requested by Kneipp - don't consider this permanent for the coupons extension
		/*
		if(is_array($coupons) && 0 < count($coupons)) {
		return $this->pi_getLL('errorAlreadyAdded');
		}
		*/
		$code = $this->piVars['code'].$this->piVars['code2'];
		$type = $this->piVars['type'];
		
		// Ralf Merz 2010-02-20: only add one coupon, not more
		if(sizeof($coupons) == 0) {
			$codeData = $this->couponObj->addCoupon($code, false, $type);
		} else {
			$codeData ='';
		}
		
//print 'after: <pre>';print_r($GLOBALS['TSFE']->fe_user->tx_commerce_basket);print '</pre><hr/>';
		#debug($this->piVars,'__piVars__');
		#debug($this->couponObj->addCoupon($code),'__ADDED__');
		#debug($codeData,'__ADDED__');

		if(is_array($codeData)){


			if($this->step == 'addCouponByAjax') {		// return true (or false), so do the rest in your ajax function
				return true;
			} else {
				/* CE added to "recalulate" delivery costs */
				$link = $this->pi_getPageLink($GLOBALS['TSFE']->id);
				$link = t3lib_div::locationHeaderUrl($link);
				header('Location: '.$link);
			}

			return $this->addedCoupon($codeData);


		} else{

			if($this->step == 'addCouponByAjax') {		// return true (or false), so do the rest in your ajax function
				return false;
			}

			#debug($this->piVars,'__ERROR__');
			#debug($this->conf, '__CONF-ARRAY__');

			## original from Tom
			/*
			$template = $this->cObj->getSubpart($this->template,'###COUPON_ERROR###');
			$markerArray = $this->markerArray;
			$errorLink = $this->pi_linkToPage($this->pi_getLL('errorLink'),$this->conf['errorPID'],$target = '',$urlParameters = array());
			$markerArray['###ERRORLINK###'] = $this->pi_getLL('errorLinkBefor').$errorLink.$this->pi_getLL('errorLinkAfter');
			$markerArray['###HEADER###'] = $this->pi_getLL('errorHeader');
			$markerArray['###BACKLINK###'] = '<a href="javascript:history.back();" title="'.$this->pi_getLL('back').'">'.$this->pi_getLL('back').'</a>';
			$content = $this->cObj->substituteMarkerArrayCached($template,$markerArray , array());


			return $content;
			*/

			// from Ralf Merz, taking an content element to configure error-message-text
			$template = $this->cObj->getSubpart($this->template,'###COUPON_ERROR###');
			$markerArray = $this->markerArray;
			#debug($markerArray);
			$markerArray['###URL###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);

			$errorLink = $this->pi_linkToPage($this->pi_getLL('errorLink'),$this->conf['errorPID'],$target = '',$urlParameters = array());
			$markerArray['###ERRORLINK###'] = $this->pi_getLL('errorLinkBefore').$errorLink.$this->pi_getLL('errorLinkAfter');
			$markerArray['###HEADER###'] = $this->pi_getLL('errorHeader');
			// the use of the backlink can now be de/activated by TYPOscript
			#debug($this->conf, 'conf array in pi1');
			if($this->conf['useBacklink'] == 0) {
				$markerArray['###BACKLINK###'] = '';
			} else {
				$markerArray['###BACKLINK###'] = '<a href="javascript:history.back();" title="'.$this->pi_getLL('back').'">'.$this->pi_getLL('back').'</a>';
			}

			$markerArray['###COUPON_ERROR_TEXT###'] = $this->pi_getLL('couponError' . $codeData);
			#$markerArray['###COUPON_ERROR_TEXT###'] = $this->cObj->cObjGetSingle($this->conf['errorText'],$this->conf['errorText.']);
			#debug($markerArray);



			$content = $this->cObj->substituteMarkerArrayCached($template,$markerArray , array());

			return $content;


			#$this->makeError('beim hinzuf&uuml;gen des Gutscheins: Gutschein-Code ung&uuml;ltig. Bitte wenden Sie sich an unseren <a href="index.php?id=84">Support</a>.');
			#return $this->showCouponForm();
		}
	}


	function debug($name) {
		// debugging
		$debugCouponObj = new tx_commercecoupons_lib($this);
		$debugBasketObj = $GLOBALS['TSFE']->fe_user->tx_commerce_basket;
		$debugArray = array();

		foreach($debugBasketObj->basket_items as $articleUid => $item) {
			if($item->tx_commercecoupons_addedbycouponid) {
				$debugArray['Durch Coupon hinzugefuegte Artikel'][$articleUid]['addedByCoupons'] = $item->tx_commercecoupons_addedbycouponid;
				$debugArray['Durch Coupon hinzugefuegte Artikel'][$articleUid]['Titel'] = $item->article->title;
				$debugArray['Durch Coupon hinzugefuegte Artikel'][$articleUid]['Anzahl'] = $item->quantity;
				$debugArray['Durch Coupon hinzugefuegte Artikel'][$articleUid]['relatedCoupon'] = $item->tx_commercecoupons_relatedcoupon;
			} else {
				$debugArray['normale Artikel'][$articleUid]['Titel'] = $item->article->title;
				$debugArray['normale Artikel'][$articleUid]['Anzahl'] = $item->quantity;
				$debugArray['normale Artikel'][$articleUid]['relatedCoupon'] = $item->tx_commercecoupons_relatedcoupon;
			}
		}
		$debugArray['Session coupons'] = $debugCouponObj->getSessionCoupons();
		#debug($debugArray,'Selektiver Debug '.$name, '', '', 8);
	}


	function getLanguageMarker(){
		$langArray = explode(',',$this->conf['langField']);
		foreach ($langArray as $k => $v){
			$this->markerArray['###LANG_'.strtoupper($v).'###'] = $this->getLL($v);
		}
	}

	/*
	 function makeError($type){
		$this->markerArray['###ERROR_MESSAGE###'] = '<span style="color:red;">Fehler '.$type.'</span>';
		return 'Fehler';
		}
		*/


	// @deprecated:

	function getCouponData(){
		$template = $this->cObj->getSubpart($this->templateCode,'###COUPON_FORM###');
		$content = $this->cObj->substituteMarkerArrayCached($template, array() , array());
		return $content;
	}


	// returns the template after adding a coupon
	// @todo add a lot of TS Values


	function addedCoupon($coupon = array()){

		if($coupon['has_articles'] && $articles = $this->couponObj->addCouponArticles($coupon)){

			$template = $this->cObj->getSubpart($this->template,'###MAIN_COUPON_WITH_ARTICLES###');

			list($k,$v) = each($articles);

			$myArticle = new tx_commerce_article($v['article_id']);
			$myArticle->load_data();
			$markerArray = $myArticle->getMarkerArray($this->cObj,array(),'article_');

			//$markerArray['###LINKTOBASKET###'] = $this->pi_getPageLink(16);
			if($this->conf['useBacklink'] == 0) {
				$markerArray['###LINKTOBASKET###'] = '';
			} else {
				$markerArray['###LINKTOBASKET###'] = $this->pi_getPageLink($this->conf['basketPid']);
			}
			$markerArray['###USE_COUPONLINK###'] = $this->pi_getPageLink($this->conf['useCouponPid'],'',array('tx_pljarticleapp_pi1[user_comes_from_redeemcoupon]'=>1));
			$markerArray['###DELETE_ARTICLE###'] = $this->pi_getPageLink($this->conf['basketPid'],'',array('tx_commerce_pi1[artAddUid]['.$myArticle->uid.'][count]'=>0));
			$markerArray['###GO_SHOPPING###'] = $this->pi_getPageLink($this->conf['basketPid']);
			#debug('addedCoupon if');
			return $this->cObj->substituteMarkerArrayCached($template,$markerArray , array());

		} else {  // Wertgutschein, normal coupon

			#debug('addedCoupon else');
			$template = $this->cObj->getSubpart($this->template,'###MAIN_COUPON_WITHOUT_ARTICLES###');

			$priceSearch_form = intval(($coupon['price_net'] - ($coupon['price_net']*0.2))/100)*(-1); // -20%
			$priceSearch_to = intval(($coupon['price_net'] + ($coupon['price_net']*0.2))/100)*(-1); // +20%
			if($this->conf['useBacklink'] == 0) {
				$markerArray['###LINKTOBASKET###'] = '';
			} else {
				$markerArray['###LINKTOBASKET###'] = '<a href="'.$this->pi_getPageLink($this->conf['basketPid']).'" title="'.$this->pi_getLL('backToBasket').'">'.$this->pi_getLL('backToBasket').'</a>';
			}
			$markerArray['###SUMME###'] = tx_moneylib::format($coupon['price_net']*(-1),'EUR');
			$markerArray['###PRICE_SEARCH_LINK###'] = $this->pi_getPageLink($this->conf['priceSearchPid'],'',array('tx_commercelistview_pi1[price]'=>$priceSearch_form.'00_'.$priceSearch_to.'00_'.$priceSearch_form.'-'.$priceSearch_to.' Euro','tx_commercelistview_pi1[showResults]'=>1));
			$markerArray['###MAIN_COUPON_WITHOUT_ARTICLES_TEXT###'] = $this->cObj->cObjGetSingle($this->conf['couponWithoutArticleText'],$this->conf['couponWithoutArticleText.']);

			return $this->cObj->substituteMarkerArrayCached($template,$markerArray , array());
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce_coupons/pi1/class.tx_commercecoupons_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce_coupons/pi1/class.tx_commercecoupons_pi1.php']);
}

?>