<?php

/* Vorbereitung fr ein eigenes Gutscheinmodul: vgl. mod_orders. */

/***************************************************************
*  Copyright notice
*
*  (c) 1999-2004 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  (c) 2005- @author	Daniel Schöttgen <ds@web-factory.de>
*  (c) 2005- ingo Schmitt <is@marketing-factory.de>  
*   All  rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Contains base class for creating a browsable array/page/folder tree in HTML
 *
 * $Id: class.tx_commerce_coupons_localrecordlist.php,v 1.1 2005/11/07 17:23:19 mfc_is Exp $
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Schöttgen <ds@web-factory.de>
 */
 /**
  * @TODO Check Xclass inclusion in inhertation
  */

require_once (PATH_typo3.'class.db_list.inc');
require_once (PATH_typo3.'class.db_list_extra.inc');


 class tx_commerce_coupons_localRecordlist extends localRecordList {


 	function makeQueryArray($table, $id, $addWhere="",$fieldList='*')
 	{
 		if ($this->sortField){
 			$orderby=$this->sortField.' ';
 			if ($this->sortRev==1)
 			{
 				$orderby.='DESC';
 			}
 		}
 		else
 		{
 			$orderby='crdate DESC';
 		}
 		$query_array=array(
	
 			'SELECT' => 'coupons.uid,coupons.pid,coupons.deleted,coupons.hidden,coupons.starttime, coupons.endtime,coupons.fe_group,coupons.crdate,coupons.code,coupons.count,coupons.type,coupons.amount_net/100 as amount_net,coupons.amount_gross/100 as amount_gross,coupons.amount_percent,coupons.limit_start/100 as limit_start,coupons.limit_end/100 as limit_end,coupons.article,article.title',
 			'FROM' =>'tx_commercecoupons_coupons AS coupons INNER JOIN tx_commerce_articles AS article ON article.uid = coupons.article',
 			'WHERE' =>'coupons.newpid='.$id.' and coupons.deleted = 0 ' ,
 			'GROUPBY' => '',
 			'ORDERBY' => $orderby,
 			'sorting' => '',
			'LIMIT' => ''
 			
 		);

 		$this->dontShowClipControlPanels = 1;
 		return $query_array;
 		#return parent::makeQueryArray($table, $id, $addWhere="",$fieldList='*');
 	}
 	

 	function generateList()	{
		global $TCA;
		t3lib_div::loadTCA("tx_commerce_coupons");
			// Traverse the TCA table array:
		reset($TCA);

		/**
		 * @TODO auf eine tabell ebeschränken, keine while liste mehr
		 */
 
		while (list($tableName)=each($TCA))	{
			
				// Checking if the table should be rendered:
			if ((!$this->table || $tableName==$this->table) && (!$this->tableList || t3lib_div::inList($this->tableList,$tableName)) && $GLOBALS['BE_USER']->check('tables_select',$tableName))	{		// Checks that we see only permitted/requested tables:
			
					// Load full table definitions:
				t3lib_div::loadTCA($tableName);

					// iLimit is set depending on whether we're in single- or multi-table mode
				if ($this->table)	{
					$this->iLimit=(isset($TCA[$tableName]['interface']['maxSingleDBListItems'])?intval($TCA[$tableName]['interface']['maxSingleDBListItems']):$this->itemsLimitSingleTable);
				} else {
					$this->iLimit=(isset($TCA[$tableName]['interface']['maxDBListItems'])?intval($TCA[$tableName]['interface']['maxDBListItems']):$this->itemsLimitPerTable);
				}
				if ($this->showLimit)	$this->iLimit = $this->showLimit;

					// Setting fields to select:
				$fields=array("code","count","type","amount_net","amount_gross","amount_percent","title","limit_start","limit_end","crdate");
				
				$this->HTMLcode.=$this->getTable($tableName, $this->id,implode(',',$fields));
			}
		}
	}
	
	/**
	 * Rendering the header row for a table
	 *
	 * @param	string		Table name
	 * @param	array		Array of the currectly displayed uids of the table
	 * @return	string		Header table row
	 * @access private
	 * @see class.db_list_extra.php 
	 */

	function renderListHeader($table,$currentIdList)	{
	
		global $TCA, $LANG;

			// Init:
		$theData = Array();

			// Traverse the fields:

		foreach($this->fieldArray as $fCol)	{
			

			
				// Calculate users permissions to edit records in the table:
			$permsEdit = $this->calcPerms & ($table=='pages'?2:16);

			switch((string)$fCol)	{
				
//				
				default:			// Regular fields header:
					$theData[$fCol]='';
					if ($this->table && is_array($currentIdList))	{
							// If the numeric clipboard pads are selected, show duplicate sorting link:
						if ($this->clipNumPane()) {
							$theData[$fCol].='<a href="'.htmlspecialchars($this->listURL('',-1).'&duplicateField='.$fCol).'">'.
											'<img'.t3lib_iconWorks::skinImg('','gfx/select_duplicates.gif','width="11" height="11"').' title="'.$LANG->getLL('clip_duplicates',1).'" alt="" />'.
											'</a>';
						}
						
					}
					
					/**
					 * Modified from this point to use relationla table queris
					 */

					$tables=array('tx_commerce_coupons');
					$temp_data='';
					foreach ($tables as $work_table)
					{
						if ($TCA[$work_table]['columns'][$fCol])
						{
							$temp_data=$this->addSortLink($LANG->sL(t3lib_BEfunc::getItemLabel($work_table,$fCol,'<i>[|]</i>')),$fCol,$table);
						}	
						
					}
					if ($temp_data)
					{
						// Only if we have a entry in locallang
						$theData[$fCol]=$temp_data;
					}
					else
					{
						//Chance titel titel into article
						$fCol=="title" ? $fCol_new = "article" : $fCol_new = $fCol;
						// default handling
						$theData[$fCol].=$this->addSortLink($LANG->sL(t3lib_BEfunc::getItemLabel($table,$fCol_new,'<i>[|]</i>')),$fCol,$table);
					}
				break;
			}
		}
	
			// Create and return header table row:
		return $this->addelement(1,'',$theData,' class="c-headLine"','');
	}
 	
 	

 
 
	function getTable($table,$id,$rowlist)	{
		global $TCA;


		// Loading all TCA details for this table:
		
		#t3lib_div::loadTCA($table);
		
			// Init
		$addWhere = '';
		$titleCol = $TCA[$table]['ctrl']['label'];
		$thumbsCol = $TCA[$table]['ctrl']['thumbnail'];
		$l10nEnabled = $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable'];

			// Cleaning rowlist for duplicates and place the $titleCol as the first column always!
		$this->fieldArray=array();
		$this->fieldArray[] = $titleCol;	// Add title column
		
		
		if ($this->localizationView && $l10nEnabled)	{
			$this->fieldArray[] = '_LOCALIZATION_';
			$addWhere.=' AND '.$TCA[$table]['ctrl']['languageField'].'<=0';
		}
		if (!t3lib_div::inList($rowlist,'_CONTROL_'))	{
			//$this->fieldArray[] = '_CONTROL_';
		}
		if ($this->showClipboard)	{
			$this->fieldArray[] = '_CLIPBOARD_';
		}
		if ($this->searchLevels)	{
			$this->fieldArray[]='_PATH_';
		}
			// Cleaning up:
		$this->fieldArray=array_unique(array_merge($this->fieldArray,t3lib_div::trimExplode(',',$rowlist,1)));
		if ($this->noControlPanels)	{
			$tempArray = array_flip($this->fieldArray);
			unset($tempArray['_CONTROL_']);
			unset($tempArray['_CLIPBOARD_']);
			$this->fieldArray = array_keys($tempArray);
		}

			// Creating the list of fields to include in the SQL query:
		$selectFields = $this->fieldArray;
		$selectFields[] = 'uid';
		$selectFields[] = 'pid';
		if ($thumbsCol)	$selectFields[] = $thumbsCol;	// adding column for thumbnails
		if ($table=='pages')	{
			if (t3lib_extMgm::isLoaded('cms'))	{
				$selectFields[] = 'module';
				$selectFields[] = 'extendToSubpages';
			}
			$selectFields[] = 'doktype';
		}
		if (is_array($TCA[$table]['ctrl']['enablecolumns']))	{
			$selectFields = array_merge($selectFields,$TCA[$table]['ctrl']['enablecolumns']);
		}
		if ($TCA[$table]['ctrl']['type'])	{
			$selectFields[] = $TCA[$table]['ctrl']['type'];
		}
		if ($TCA[$table]['ctrl']['typeicon_column'])	{
			$selectFields[] = $TCA[$table]['ctrl']['typeicon_column'];
		}
		if ($TCA[$table]['ctrl']['versioning'])	{
			$selectFields[] = 't3ver_id';
		}
		if ($l10nEnabled)	{
			$selectFields[] = $TCA[$table]['ctrl']['languageField'];
			$selectFields[] = $TCA[$table]['ctrl']['transOrigPointerField'];
		}
		if ($TCA[$table]['ctrl']['label_alt'])	{
			$selectFields = array_merge($selectFields,t3lib_div::trimExplode(',',$TCA[$table]['ctrl']['label_alt'],1));
		}
		
		$selectFields = array_unique($selectFields);		// Unique list!
		$selectFields = array_intersect($selectFields,$this->makeFieldList($table,1));		// Making sure that the fields in the field-list ARE in the field-list from TCA!
		//print_r($selectFields);
		$selFieldList = implode(',',$selectFields);		// implode it into a list of fields for the SQL-statement.

			// Create the SQL query for selecting the elements in the listing:

		$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)	
		
		$this->setTotalItems($queryParts);		// Finding the total amount of records on the page (API function from class.db_list.inc)

			// Init:
		$dbCount = 0;
		$out = '';

			// If the count query returned any number of records, we perform the real query, selecting records.
		if ($this->totalItems)	{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
			$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
		}


		$LOISmode = $this->listOnlyInSingleTableMode && !$this->table;

			// If any records was selected, render the list:
		if ($dbCount)	{

				// Half line is drawn between tables:
			if (!$LOISmode)	{
				$theData = Array();
				if (!$this->table && !$rowlist)	{
		   			$theData[$titleCol] = '<img src="clear.gif" width="'.($GLOBALS['SOBE']->MOD_SETTINGS['bigControlPanel']?'230':'350').'" height="1" alt="" />';
					//if (in_array('_CONTROL_',$this->fieldArray))	$theData['_CONTROL_']='';
					//if (in_array('_CLIPBOARD_',$this->fieldArray))	$theData['_CLIPBOARD_']='';
				}
				$out.=$this->addelement(0,'',$theData,'',$this->leftMargin);
			}

				// Header line is drawn
			$theData = Array();
			if ($this->disableSingleTableView)	{
				$theData[$titleCol] = '<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.')';
			} else {
				$theData[$titleCol] = $this->linkWrapTable($table,'<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.') <img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/'.($this->table?'minus':'plus').'bullet_list.gif','width="18" height="12"').' hspace="10" class="absmiddle" title="'.$GLOBALS['LANG']->getLL(!$this->table?'expandView':'contractView',1).'" alt="" />');
			}

				// CSH:
			$theData[$titleCol].= t3lib_BEfunc::cshItem($table,'',$this->backPath,'',FALSE,'margin-bottom:0px; white-space: normal;');

			if ($LOISmode)	{
				$out.='
					<tr>
						<td class="c-headLineTable" style="width:95%;"'.$theData[$titleCol].'</td>
					</tr>';
				
				if ($GLOBALS['BE_USER']->uc["edit_showFieldHelp"])	{
					$GLOBALS['LANG']->loadSingleTableDescription($table);
					if (isset($GLOBALS['TCA_DESCR'][$table]['columns']['']))	{
						$onClick = 'vHWin=window.open(\'view_help.php?tfID='.$table.'.\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;';
						$out.='
					<tr>
						<td class="c-tableDescription">'.t3lib_BEfunc::helpTextIcon($table,'',$this->backPath,TRUE).$GLOBALS['TCA_DESCR'][$table]['columns']['']['description'].'</td>
					</tr>';
					}
				}
			} else {
				
				$theUpIcon = ($table=='pages'&&$this->id&&isset($this->pageRow['pid'])) ? '<a href="'.htmlspecialchars($this->listURL($this->pageRow['pid'])).'"><img'.t3lib_iconWorks::skinImg('','gfx/i/pages_up.gif','width="18" height="16"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.upOneLevel',1).'" alt="" /></a>':'';
				$out.=$this->addelement(1,$theUpIcon,$theData,' class="c-headLineTable"','');
			}

			if (!$LOISmode)	{
					// Fixing a order table for sortby tables
				$this->currentTable = array();
				$currentIdList = array();
				$doSort = ($TCA[$table]['ctrl']['sortby'] && !$this->sortField);

				$prevUid = 0;
				$prevPrevUid = 0;
				$accRows = array();	// Accumulate rows here
		
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
				
					
					$accRows[] = $row;
					$currentIdList[] = $row['uid'];
					if ($doSort)	{
						if ($prevUid)	{
							$this->currentTable['prev'][$row['uid']] = $prevPrevUid;
							$this->currentTable['next'][$prevUid] = '-'.$row['uid'];
							$this->currentTable['prevUid'][$row['uid']] = $prevUid;
						}
						$prevPrevUid = isset($this->currentTable['prev'][$row['uid']]) ? -$prevUid : $row['pid'];
						$prevUid=$row['uid'];
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);

					// CSV initiated
				if ($this->csvOutput) $this->initCSV();

					// Render items:
				$this->CBnames=array();
				$this->duplicateStack=array();
				$this->eCounter=$this->firstElementNumber;

				$iOut = '';
				$cc = 0;
				foreach($accRows as $row)	{

						// Forward/Backwards navigation links:
					list($flag,$code) = $this->fwd_rwd_nav($table);
					$iOut.=$code;

						// If render item, increment counter and call function
					if ($flag)	{
						$cc++;
					
						$iOut.=$this->renderListRow($table,$row,$cc,$titleCol,$thumbsCol);

							// If localization view is enabled it means that the selected records are either default or All language and here we will not select translations which point to the main record:
						if ($this->localizationView && $l10nEnabled)	{

								// Look for translations of this record:
							$translations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
								$selFieldList,
								$table,
								'pid='.$row['pid'].
									' AND '.$TCA[$table]['ctrl']['languageField'].'>0'.
									' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($row['uid']).
									t3lib_BEfunc::deleteClause($table)
							);

								// For each available translation, render the record:
							foreach($translations as $lRow)	{
								$iOut.=$this->renderListRow($table,$lRow,$cc,$titleCol,$thumbsCol,18);
							}
						}
					}

						// Counter of total rows incremented:
					$this->eCounter++;
				}

					// The header row for the table is now created:
					
				$out.=$this->renderListHeader($table,$currentIdList);
			}

				// The list of records is added after the header:
			$out.=$iOut;

				// ... and it is all wrapped in a table:
			$out='



			<!--
				DB listing of elements:	"'.htmlspecialchars($table).'"
			-->
				<table border="0" cellpadding="2" cellspacing="0" class="typo3-dblist'.($LOISmode?' typo3-dblist-overview':'').'">
					'.$out.'
				</table>';

				// Output csv if...
			if ($this->csvOutput)	$this->outputCSV($table);	// This ends the page with exit.
		}

			// Return content:
		return $out;
	}
	
 /**
	 * Rendering a single row for the list
	 *
	 * @param	string		Table name
	 * @param	array		Current record
	 * @param	integer		Counter, counting for each time an element is rendered (used for alternating colors)
	 * @param	string		Table field (column) where header value is found
	 * @param	string		Table field (column) where (possible) thumbnails can be found
	 * @param	integer		Indent from left.
	 * @return	string		Table row for the element
	 * @access private
	 * @see getTable()
	 */
	
	function renderListRow($table,$row,$cc,$titleCol,$thumbsCol,$indent=0)	{
		$iOut = '';
		
		if (strlen($this->searchString))	{	// If in search mode, make sure the preview will show the correct page
			$id_orig = $this->id;
			$this->id = $row['pid'];
		}

			// In offline workspace, look for alternative record:
		t3lib_BEfunc::workspaceOL($table, $row, $GLOBALS['BE_USER']->workspace);

			// Background color, if any:
		$row_bgColor=
			$this->alternateBgColors ?
			(($cc%2)?'' :' class="db_list_alt"') :
			'';

			// Overriding with versions background color if any:
		$row_bgColor = $row['_CSSCLASS'] ? ' class="'.$row['_CSSCLASS'].'"' : $row_bgColor;

			// Incr. counter.
		$this->counter++;
		
			// The icon with link
		$alttext = t3lib_BEfunc::getRecordIconAltText($row,$table);
		$iconImg = t3lib_iconWorks::getIconImage($table,$row,$this->backPath,'title="'.htmlspecialchars($alttext).'"'.($indent ? ' style="margin-left: '.$indent.'px;"' : ''));
		$theIcon = $this->clickMenuEnabled ? $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg,$table,$row['uid'],1,'','+edit') : $iconImg;
		$theIcon = $this->clickMenuEnabled ? $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg,$table,$row['uid'],1,'','') : $iconImg;
			// Preparing and getting the data-array
		$theData = Array();
		foreach($this->fieldArray as $fCol)	{
			if ($fCol==$titleCol)	{
				$recTitle = t3lib_BEfunc::getRecordTitle($table,$row,FALSE,TRUE);
				$theData[$fCol] = $this->linkWrapItems($table,$row['uid'],$recTitle,$row);
			} elseif ($fCol=='pid') {
				$theData[$fCol]=$row[$fCol];
			} elseif ($fCol=='_PATH_') {
				$theData[$fCol]=$this->recPath($row['pid']);
			} elseif ($fCol=='_REF_') {
				$theData[$fCol]=$this->makeRef($table,$row['uid']);
			} elseif ($fCol=='_CONTROL_') {
				$theData[$fCol]=$this->makeControl($table,$row);
			} elseif ($fCol=='_CLIPBOARD_') {
				$theData[$fCol]=$this->makeClip($table,$row);
#				$t3lib_transl8tools = new t3lib_transl8tools;
#				$theData[$fCol].=t3lib_div::view_array($t3lib_transl8tools->translationInfo($table,$row['uid']));
			} elseif ($fCol=='_LOCALIZATION_') {
				list($lC1, $lC2) = $this->makeLocalizationPanel($table,$row);
				$theData[$fCol] = $lC1;
				$theData[$fCol.'b'] = $lC2;
			} elseif ($fCol=='_LOCALIZATION_b') {
				// Do nothing, has been done above.
			} else {
				$theData[$fCol] = $this->linkUrlMail(htmlspecialchars(t3lib_BEfunc::getProcessedValueExtra($table,$fCol,$row[$fCol],100,$row['uid'])),$row[$fCol]);
			}
		}

		if (strlen($this->searchString))	{	// Reset the ID if it was overwritten
			$this->id = $id_orig;
		}

			// Add row to CSV list:
		if ($this->csvOutput) $this->addToCSV($row,$table);

			// Create element in table cells:
		$iOut.=$this->addelement(1,$theIcon,$theData,$row_bgColor);

			// Render thumbsnails if a thumbnail column exists and there is content in it:
		if ($this->thumbs && trim($row[$thumbsCol]))	{
			$iOut.=$this->addelement(4,'', Array($titleCol=>$this->thumbCode($row,$table,$thumbsCol)),$row_bgColor);
		}

			// Finally, return table row element:
		return $iOut;
	}


 }
 

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce_coupons/lib/class.tx_commerce_coupons_localRecordList.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce_coupons/lib/class.tx_commerce_coupons_localRecordList.php']);
}

 ?>