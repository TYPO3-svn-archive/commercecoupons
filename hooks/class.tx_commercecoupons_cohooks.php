<?php
 require_once(t3lib_extMgm::extPath('commerce_coupons').'lib/class.tx_commercecoupons_lib.php');
 require_once(t3lib_extmgm::extPath('commerce').'lib/class.tx_commerce_div.php');
/**
 * This class implements coupons in the checkout of the commerce basket
 */
class tx_commercecoupons_cohooks {
	

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
	
	
	
	/**
	 * adds the Coupon-Markers to the basketarray.
	 * checks, if an related article was removed from basket, so we also remove the coupon
	 * ToDo: check if quantity of a related article is only 1, not higher
	 */
	function ProcessMarker($markerArray, &$pObj){
		# no basket anymore in pObj, so we get it from the GLOBALS
		$pObj->basket = $GLOBALS['TSFE']->fe_user->tx_commerce_basket;
		
		$markerArray['###COUPON_VIEW###'] = '';
		
		// display coupons in basket view
  	$couponArticles = array_merge($pObj->basket->get_articles_by_article_type_uid_asuidlist($pObj->conf['couponNormalType']), $pObj->basket->get_articles_by_article_type_uid_asuidlist($pObj->conf['couponRelatedType']));
		  
  	// it is better to get the coupon-template code from a separated file
 		$pObj->templateCodeCoupon = $pObj->cObj->fileResource($pObj->conf['couponFinishTemplateFile']);
  	$couponTemplate = $pObj->cObj->getSubpart($pObj->templateCodeCoupon, '###COUPON_VIEW_IN_FINISH###');

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
	  $priceSub = $price_gross2 + $priceCoupons;
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
	

	

	/**
	 * This method is called when all in the checkot has been done.
	 * Here we create new coupons in the database whenever an article
	 * has the type 'coupon'.
	 *
	 * @param	object	$basket: The basket we're working on
	 * @param	object	$checkout: The checkout class
	 */
	function postFinish(&$basket, $checkout,$couponId='')	{
	
		#debug($basket, 'basket');
		#debug($checkout, 'checkout');
			$GLOBALS['TSFE']->fe_user->setKey('ses','order_finished','done');
		
		// detect the uid of article type "coupon"
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_commerce_article_types', 'title=\'coupon_buy\'');
		$couponUid = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$couponUid = $couponUid['uid'];
		
		// get the page id
		list($modPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders('Commerce', 'commerce');
		list($coupPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders('Coupons', 'commerce',$modPid);

		$statusNo = $checkout->statusNo;
		$status = $checkout->status;
		list($statusPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders($status, 'commerce',$coupPid);

		$pid = $statusPid;

		# if(!$payment->paymentRefId && $basket->basket_sum_net > 0){		
		
		$allArticles = array();
		$coupons = array();
		
		// now get all articles from the basket that have this type
		$articles = $basket->get_articles_by_article_type_uid_asUidlist(1);
		$allArticles = array_merge($allArticles, $articles);
		
		$couponObj = new tx_commercecoupons_lib($checkout);
		$couponsHavingAddedAnArticle = array();
				

		// Update the coupons
		// ToDo update coupons if more than one is avaible. In the moment, all coupons are disabled
		// changed: coupons will only be disabled, if newCount = 0. If newCount is > 0, we decrement the count of the coupon
		$sessionCoupons = $couponObj->getSessionCoupons();
		
		foreach($sessionCoupons as $coupon) {
					$debugArray = array();
					$oldCount = intval($coupon['record']['count']);
					$newCount = $oldCount-1;
					
					$debugArray[] = $oldCount;
					$debugArray[] = $newCount;
					$debugArray[] = $coupon;
		
					if($newCount == 0){
						#debug($coupon, 'coupon reached 0');
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_commercecoupons_coupons', 'uid = '.$coupon['uid'], array('hidden' => 1, 'count' => $newCount));
						// send mail (specified by typoscript) that Coupon count has reached 0
						$this->sendCountNotification($checkout->conf['adminmail.']['mailto'], $checkout->conf['couponNotificationSubject'], $coupon['code']);
					}
					
					if($newCount > 0){
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_commercecoupons_coupons', 'uid = '.$coupon['uid'], array('count' => $newCount));
					}
					
					if($newCount == -1){
						// nothing to do... or shall i?
					}
					
					// insert data into table tx_commercecoupons_cashed
					$now = time();
					
					$cashedArray = array(
						'pid' => $coupon['record']['pid'],
						'tstamp' => $now,
						'crdate' => $now,
						'cruser_id' => $coupon['record']['cruser_id'],
						'fe_group' => $coupon['record']['fe_group'],
						
						'deleted' => $coupon['record']['deleted'],
						'hidden' => $coupon['record']['hidden'],
						'coupon_pid' => $coupon['uid'],
						'sess_id' => $basket->sess_id, 
						'order_pid' => $checkout->orderId
					);
					
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_commercecoupons_cashed', $cashedArray);
					
					unset($cashedArray);
		}
				
		$couponObj->removeInvalidCouponsFromSession();
		$couponObj->unsetSessionCoupons();
		
		$basket->store_data();	
		
		
		// now get all articles from the basket that have this type
		// For Each CouponType a new coupon should be created
		$articles = $basket->get_articles_by_article_type_uid_asUidlist($couponUid);
		$allArticles = array_merge($allArticles, $articles);
		
			
			// now create bought money coupons
		if (is_array($articles))	{
			foreach ($articles as $articleUid)	{
				$now = time();
				// get the page id
				$comPid = array_keys(tx_graytree_folder_db::getFolders('commerce', 0, 'COMMERCE'));
				$pid = array_keys(tx_graytree_folder_db::getFolders('commerce', $comPid[0], 'Coupons'));
				list($statusPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders($status, 'commerce',$coupPid);
				$pid = $statusPid;
				
				$articleData = $basket->basket_items[$articleUid];

				for($i = 1; $i <= $basket->basket_items[$articleUid]->quantity; $i++) {
								
					// create a unique code
					$code = 'V'.$GLOBALS['TSFE']->fe_user->user['uid'].date('dmy').$this->makeRandCode(12,'alpha');	
					
					$addPrice +=  $amount_gross;


					$amount_net = $basket->basket_items[$articleUid]->get_price_net();
					$amount_gross = $basket->basket_items[$articleUid]->get_price_gross();
				
						// build the insert data
						// count is allways 1 here
						// type is allways money
						// there is no limit for start and end
					$couponData = array(
						'pid' => $pid,
						'hidden' => $statusNo,
						'tstamp' => $now,
						'crdate' => $now,
						'count' => 1,
						'type' => 'money',
						'amount_net' => $amount_net,
						'amount_gross' => $amount_gross,	
						'article' => $checkout->conf['moneyCouponArt'],
						'endtime' => strtotime("+1 year"),
						'code' => $code,
						#'order_id' => $checkout->orderUid,
						#'last_name' => $GLOBALS['TSFE']->fe_user->user['last_name'],
						#'first_name' => $GLOBALS['TSFE']->fe_user->user['first_name'],
					);
					
						// insert it into the database
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_commercecoupons_coupons', $couponData);
					$couponId = $GLOBALS['TYPO3_DB']->sql_insert_id();
					$coupons[$couponId] = $couponData;
					$basket->basket_items[$articleUid]->tx_commercecoupons_relatedcoupon[] = $couponId;
				    
				}
			
		    }
		}

		$basket->store_data();
	 
			// create a coupon that have the amount of the negative basket sum if the sum of the basket is lower than 0
		if ($basket->get_gross_sum() < 0)	{
			$now = time();
				
			// get the page id
			list($modPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders('Commerce', 'commerce');
			list($coupPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders('Coupons', 'commerce',$modPid);
			list($statusPid,$defaultFolder,$folderList) = tx_graytree_folder_db::initFolders($status, 'commerce',$coupPid);
			
			$pid = $statusPid;		
			
			// create a unique code
			$code = 'V'.$GLOBALS['TSFE']->fe_user->user['uid'].date('dmy').$this->makeRandCode(12,'alpha');
			$amount_net = -1 *$basket->get_net_sum();
			$amount_gross = -1 *$basket->get_gross_sum();
				
			// build the insert data
			// count is allways 1 here
			// type is allways money
			// there is no limit for start, but for end (now + 1 year)
			$couponData = array(
				'pid' => $pid,
				'hidden' => $statusNo,
				'tstamp' => $now,
				'crdate' => $now,
				'count' => 1,
				'type' => 'money',
				'amount_net' => $amount_net,
				'amount_gross' => $amount_gross,	
				'article' => $checkout->conf['moneyCouponArt'],	
				'endtime' => strtotime("+1 year"),
				'code' => $code,
			);
				
			// insert it into the database
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_commercecoupons_coupons', $couponData);
			
			$couponId = $GLOBALS['TYPO3_DB']->sql_insert_id();
			
			$coupons[$couponId] = $couponData;
			// add an couponArticle for the coupon, so nobody can get more than one article, even if the script 
			// crashed later on.
			// @ToDo change static article Id
			$coupArtId = intval($pObj->conf['couponArticleId']);
			$allArticles[] = $coupArtId;
			$basket->add_article($coupArtId,1);
			$basket->changePrices($coupArtId,$amount_gross,$amount_net);
			$basket->basket_items[$coupArtId]->tx_commercecoupons_relatedcoupon[] = $couponId;
			$basket->store_data();
		}

		// print_r(array($allArticles, $coupons)); 
		
	// 	@ToDo think about sending pdf's make a download function or things like that for the coupons bought
		
		if(is_array($attachements)){
			$files = implode(' ',$attachements);		    
		}
#		} // look above, beginning if is still there
	}

	function makeRandCode($length,$type="numeric"){
		// RANDOM KEY PARAMETERS
	#	$keychars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$keychars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$keynumeric = '1234567890';
		switch ($type) {
			case  'numeric' :
				$keys = $keynumeric;
				break;
			case 'alpha' :
				$keys = $keychars.$keynumeric;
				break;
			default :
				$keys = $keychars;
		}
	
		// init random function
		srand((double)microtime()*1000000);
	
		// RANDOM KEY GENERATOR
		$randkey = "";
		$max=strlen($keys)-1;
		for ($i=0;$i<=$length;$i++) {
		  $randkey .= substr($keys, rand(0, $max), 1);
		}
		return $randkey;	
	}
	
	/**
	 * sends an plain-Text email if a coupon reaches count = 0
	 * @param string mailTo is set in TS commerce_pi3.adminmail.mailto
	 * @param string subject Subject of the email, set by TS in commerce_pi3
	 * @param string couponCode Coupon code which has been cashed
	 * 
	 * TODO: Maybe use an HTML or TEXT Template-File here...
	 */
	function sendCountNotification($mailTo, $subject, $couponCode) {
		// send mail...
		#$email = 'ralf@ralf-merz.de';
		$email = $mailTo;
		$subject = $subject.' Code: '.$couponCode;
		$message = 'Der Gutschein mit dem Code '.$couponCode.' hat nun die Anzahl "0" und wurde deaktiviert.';
		$headers = 'From: order@schutzgeld.de';
		t3lib_div::plainMailEncoded($email,$subject,$message,$headers='',$enc='',$charset='',$dontEncodeHeader=false);
		#debug($email, 'mail should have been sent to');
	}
	
}

?>