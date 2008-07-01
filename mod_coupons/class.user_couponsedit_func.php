<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Volker Graubaum (vg@e-netconsulting.de)
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
 * User Class for displaying Orders
 *
 * @package commerce
 * @subpackage order view
 * @author Volker Graubaum <vg@e-netconsulting.de>
 * @author Thomas Hempel <thomas@typo3-unleashed.net>
 * @maintainer Volker Graubaum <vg@e-netconsulting.de>
 */

require_once (PATH_t3lib.'class.t3lib_recordlist.php');
require_once (PATH_t3lib.'class.t3lib_div.php');
require_once (PATH_typo3.'class.db_list.inc');
require_once (PATH_typo3.'class.db_list_extra.inc');
 
require_once (PATH_txgraytree.'lib/class.tx_graytree_folder_db.php');
require_once (PATH_txcommerce.'lib/class.tx_commerce_create_folder.php');
require_once (PATH_txcommerce.'lib/class.tx_commerce_div.php');

class user_couponsedit_func {
 	
	/**
	 * Artcile order_id
	 * Just a hidden field
	 * @param $PA
	 * @param $fobj
	 * @return HTML-Content
	 */
	function article_coupon_id($PA, $fobj)	{
		$content.=htmlspecialchars($PA['itemFormElValue']);
		$content.='<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'">';
		return $content;
	}
	
	

	/**
	 * Artcile order_id
	 * Just a hidden field
	 * @param $PA
	 * @param $fobj
	 * @return HTML-Content
	 */
	function amount_gross_format($PA, $fobj)	{
		#	$content.= tx_commerce_div::formatPrice($PA['itemFormElValue']);
		$content .= '<input type="text" disabled name="' .$PA['itemFormElName'] .'" value="' .tx_commerce_div::formatPrice($PA['itemFormElValue'] /100) .'" />';
		return $content;
	}
	

 	
 	function coupon_status(&$data, &$pObj)	{
		tx_commerce_create_folder::init_folders();
		#debug($data['config']['foreign_table'], 'in order_couponlist');
		$data['items'] = array();
		
		# Find the right pid for the Couponsfolder 

		$couponPid = array_unique(tx_graytree_folder_db::initFolders('Coupons', 'commerce', 0, 'Commerce'));
 		#debug($couponPid[0], 'couponPid');
 		$result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $data['config']['foreign_table'], 'pid=' .$couponPid[0] .t3lib_BEfunc::deleteClause($data['config']['foreign_table']));
 		if ($GLOBALS['TYPO3_DB']->sql_num_rows($result) > 0)	{
 			while ($return_data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
 				$data['items'][] = array($return_data['title'], $return_data['uid']);
 			}
 			$GLOBALS['TYPO3_DB']->sql_free_result($result);
 		} else {
 			t3lib_BEfunc::typo3PrintError('No Coupons Sysfolders', 'There are no coupon sysfolders present. Something went terrible wrong');
 		}
 	}
 	
	/*
 	 * Renders the crdate
	 * @author Volker Graubaum
 	 * @param $PA
 	 * @param $fobj
 	 * @return HTML-Content
	*/	
	
	function crdate($PA,$fObj)	
 	{
	    $PA['itemFormElValue'] = date('d.m.y',$PA['itemFormElValue']);
	
 		/**
 		 * Normal
 		 */
		$content.= $fObj->getSingleField_typeNone_render(array(),$PA['itemFormElValue']);
		return $content;
 	}
 	
	/**
	 * Renders the couponlist for an order
	 * @author Volker Graubaum
	 * @param $PA
	 * @param $fobj
	 * @return HTML-Content
	 */	
	function order_couponlist($PA, $fObj) {
		global $TCA;
		
			// select this order uid
		$orderUid = $PA['row']['uid'];
		
			// activate all coupons that where checked before
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_commercecoupons_coupons', 'order_id=' .$orderUid, array('hidden' => 1));
		
		if (is_array($_REQUEST['activate']))	{
			$activatedCoupons = array();
			foreach ($_REQUEST['activate'] as $uid => $value)	{
				$activatedCoupons[] = $uid;
			}
			
				// set new activations
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_commercecoupons_coupons',
				'order_id=' .$orderUid .' AND uid IN (' .implode(',', $activatedCoupons) .')',
				array('hidden' => 0)
			);
		}	
		
			// build the output
 		$table = 'tx_commercecoupons_coupons';

		$field_rows = array('hidden', 'code', 'starttime', 'endtime', 'amount', 'type');
		$field_row_list = implode(',', $field_rows);
			 
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
 			$table,
			'order_id=' .$orderUid .' AND deleted=0'
		 );
 		
		$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			
		if ($dbCount)	{
			$theData[$titleCol] = '<span class="c-table">'.$GLOBALS['LANG']->sL('LLL:EXT:commerce/locallang_be.php:order_view.items.article_list',1).'</span> ('.$dbCount.')';
			
			foreach($field_rows as $field)	{
				$out .= '<td class="c-headLineTable"><b>'.
					$GLOBALS['LANG']->sL(t3lib_BEfunc::getItemLabel($table, $field)).
					'</b></td>';
			}
			
			$out .= '<td class="c-headLineTable"></td>';
			$out .= '</tr>';
				
			
			$cc = 0;
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
				$cc++;
				
				$row_bgColor = (($cc %2) ? '' : ' bgcolor="'.t3lib_div::modifyHTMLColor($GLOBALS['SOBE']->doc->bgColor4,+10,+10,+10).'"');
				
				$iOut.='<tr '.$row_bgColor.'">';
				foreach ($field_rows as $field)	{
					$iOut .= '<td>';
					$wrap = array('', '');
					switch ($field)	{
						case 'hidden':
							$iOut .= '<input type="checkbox" name="activate[' .$row['uid'] .']"';
							if ($row[$field] == 0)	{
								$iOut .= ' checked="checked"';
							}
							$iOut .= ' />';
							break;
						default:
							$iOut .= implode(t3lib_BEfunc::getProcessedValue($table, $field, $row[$field], 100), $wrap);
							break;
					}
					$iOut .= '</td>';
				}
				
				$iOut .= '<td>&nbsp;';
				// put edit methods here
				$iOut .= '</td>';
				$iOut .= '</tr>';
			}

			$out .= $iOut;
			$out .= '<tr>';
		
			foreach($field_rows as $field)	{
				$out.='<td class="c-headLineTable"><b>';
				if ($sum[$field]>0)
				{
					$out .= t3lib_BEfunc::getProcessedValueExtra($foreign_table,$field,$sum[$field],100);	
				}
				
				$out.='</b></td>';
			}
			$out.='<td class="c-headLineTable"></td>';
			$out.='</tr>';
				
		}
	
		$out='
	<!--
		DB listing of elements:	"'.htmlspecialchars($table).'"
	-->
		<table border="0" cellpadding="0" cellspacing="0" class="typo3-dblist">
			' .$out .'
		</table>';
		$content .= $out;
		
		return $content;
	}
	
	function activate_coupons($PA, $fObj)	{
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce/mod_coupons/class.user_couponsedit_func.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce/mod_coupons/class.user_couponsedit_func.php']);
}
 
 ?>