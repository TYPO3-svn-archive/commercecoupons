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
 * Module 'Coupons' for the 'commerce' extension.
 *
 * @author	Volker Graubaum <vg@e-netconsulting.de>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
require_once (PATH_typo3.'class.db_list.inc');
require_once (PATH_typo3.'class.db_list_extra.inc');

$LANG->includeLLFile("EXT:commerce_coupons/mod_coupons/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/**
 * Load TYPO3 core libaries
 */

require_once (PATH_t3lib.'class.t3lib_page.php');
require_once (PATH_t3lib.'class.t3lib_pagetree.php');
require_once (PATH_t3lib.'class.t3lib_recordlist.php');
require_once (PATH_t3lib.'class.t3lib_clipboard.php');

require_once (PATH_t3lib.'class.t3lib_recordlist.php');



require_once (t3lib_extmgm::extPath('commerce_coupons').'lib/class.tx_commerce_coupons_localrecordlist.php');

require_once (PATH_txgraytree.'lib/class.tx_graytree_folder_db.php');

require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');

/**
 * Load Locallang
 */

$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.php');



class tx_commerce_coupons extends t3lib_SCbase {

	var $id;					// Page Id for which to make the listing
	var $pointer;				// Pointer - for browsing list of records.
	var $imagemode;				// Thumbnails or not
	var $table ='tx_commercecoupons_coupons';					// Which table to make extended listing for
	var $search_field;			// Search-fields
	var $search_levels;			// Search-levels
	var $showLimit;				// Show-limit
	var $returnUrl;				// Return URL

	var $clear_cache;			// Clear-cache flag - if set, clears page cache for current id.
	var $cmd;					// Command: Eg. "delete" or "setCB" (for TCEmain / clipboard operations)
	var $cmd_table;				// Table on which the cmd-action is performed.

		// Internal, static:
	var $perms_clause;			// Page select perms clause
	var $modTSconfig;			// Module TSconfig
	var $pageinfo;				// Current ids page record
	var $doc;					// Document template object

	var $MCONF=array();			// Module configuration
	var $MOD_MENU=array();		// Menu configuration
	var $MOD_SETTINGS=array();	// Module settings (session variable)
	var $include_once=array();	// Array, where files to include is accumulated in the init() function
	
	// by Ralf
	var $error_wrong_cArtId='';		// Error Message, gets filled if a no-int value is input in the couponArticleId field
	/**
	 *
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
		$this->table='tx_commercecoupons_coupons';
		$this->clickMenuEnabled=1;
		require_once (PATH_txcommerce.'lib/class.tx_commerce_create_folder.php');
		tx_commerce_create_folder::init_folders();
		
		/**
		 * @TODO bitte aus der ext config nehmen, volker angefragt
		 */
				 
		# Find the right pid for the Couponsfolder 
		 
		$coupon_pid = array_unique(tx_graytree_folder_db::initFolders('Coupons', 'commerce', 0, 'Commerce'));
		tx_graytree_folder_db::initFolders('Disabled', 'commerce', $coupon_pid[0]);
		tx_graytree_folder_db::initFolders('Enabled', 'commerce', $coupon_pid[0]);
		tx_graytree_folder_db::initFolders('Ordered', 'commerce', $coupon_pid[0]);
		tx_graytree_folder_db::initFolders('Used', 'commerce', $coupon_pid[0]);
		
		
		/**
		 * @TODO Find a better solution for the fist array element
		 * 
		 */
		/**
		 * If we get an id via GP use this, else use the default id
		 */
		if (t3lib_div::_GP('id'))
		{
			$this->id=t3lib_div::_GP('id');
		}
		else
		{
			$this->id = $coupon_pid[0];
		}
		/*
		if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
		
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"2" => $LANG->getLL("CouponData"),				
			)
		);
		parent::menuConfig();
	}

		// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		#debug($BACK_PATH, 'backpath');

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="index.php" enctype="'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'].'" method="post">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);
			
				// Add JavaScript functions to the page:
			#$this->content.=$this->doc->startPage($LANG->getLL("title"));
			#$this->content.=$this->doc->header($LANG->getLL("title"));
			#$this->content.=$this->doc->spacer(5);
			#$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			#$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();
			#debug('after moduleContent');

			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{
		//$this->content.=$this->doc->small();
		#debug('in printContent');
		$this->content.=$this->doc->endPage();
		echo $this->content;

	}
	
	// ###########################
	// CSV FUNCTION BEGIN
	// ###########################	
	
	
	/**
	 * Upload File
	 */
	function csvUpload($file, $path, $allowed_types ) {
		global $LANG;
		if(is_uploaded_file($_FILES[$file]["tmp_name"])) {
			if(preg_match("/\." . $allowed_types . "$/i", $_FILES[$file]["name"]))
				if(copy($_FILES[$file]["tmp_name"], $path.$_FILES[$file]["name"]))
					return 1;
		} 
		else
			return $LANG->getLL("error_upload");
	}
	
	/**
	 * Delete File
	 */
	function csvDelete($csvFile) {
		unlink($csvFile);
	}
	
	function csvLines($file, $spaltenNum) {
		global $LANG;
	
		$csvFile = fopen ($file,"r");
		$output='<strong>'.$LANG->getLL("csv_file").'</strong>';
		$row=1;
		$pid = array_keys(tx_graytree_folder_db::getFolders('commerce', $comPid[0], 'Coupons'));
		$pid = $statusPid;
		
		if($this->csvCheckIfNum($GLOBALS['HTTP_POST_VARS']['couponArticleId'])){
			$couponArticleId = $GLOBALS['HTTP_POST_VARS']['couponArticleId'];
		while(($csvdata = fgetcsv ($csvFile, 1000, ";")) !== FALSE ) { // Daten werden aus der Datei
			$num = count($csvdata);
			if($this->csvCheckCrev($spaltenNum, $num)==1) {
							
				$output.='<br><br><strong>'.$LANG->getLL("line").$row.':</strong>' ;
				
				//Check if String-Code exits
				$errorCheck[] = $this->csvCheckIfEmpty($csvdata[0]);
				
				//Check nach Typ
				if($this->csvCheckString($csvdata[1],'percent')==1) {
					//Check if percent is a number
					$errorCheck[] = $this->csvCheckIfNum($csvdata[4]);
				}
				elseif($this->csvCheckString($csvdata[1],'money')==1) {
					//Check if amount_net and amount_gross is a number
					$errorCheck[] = $this->csvCheckIfNum($csvdata[2]);
					$errorCheck[] = $this->csvCheckIfNum($csvdata[3]);
				}
				else
					$errorCheck[] = '';
				
				//Check if count is an number
				$errorCheck[] = $this->csvCheckIfNum($csvdata[5]);
				//Check if category is a number
				//$errorCheck[] = $this->csvCheckIfNum($csvdata[10]);
				
				//Check if line provides
				if (in_array('', $errorCheck)) {
				    $output .= '<br><strong>'.$LANG->getLL("error_syntax_1").'</strong>'.$LANG->getLL("error_syntax_2");
				}
				else {
					//Check if code allready exists
					$resCode=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_commercecoupons_coupons','code='.$csvdata[0].' and deleted = 0');
					if($GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCode)) {
						$output .= '<br><strong>'.$LANG->getLL("error_double_code_1").'</strong>'.$LANG->getLL("error_double_code_2").$csvdata[0].$LANG->getLL("error_double_code_3").$GLOBALS['HTTP_POST_VARS']['id'].$LANG->getLL("error_double_code_4");
					}
					else {
					
						if($this->csvCheckIfNum($csvdata[6]) != 1 && $csvdata[6]!='') {
							$csvdata[6] = '';
							$output .= '<br><i>'.$LANG->getLL("error_limit_start").'</i>';
						}
						if($this->csvCheckIfNum($csvdata[7]) != 1 && $csvdata[7]!='') {
							$csvdata[7] = '';
							$output .= '<br><i>'.$LANG->getLL("error_limit_end").'</i>';
						}
							
						if($csvdata[8]!='' && !$csvdata[8] = strtotime($csvdata[8]))
							$output .= '<br><i>'.$LANG->getLL("error_starttime").'</i>';

						if($csvdata[9]!='' && !$csvdata[9] = strtotime($csvdata[9]))
							$output .= '<br><i>'.$LANG->getLL("error_endtime").'</i>';

						$couponData = array(
						'pid' => $GLOBALS['HTTP_POST_VARS']['id'],
						'tstamp' => time(),
						'crdate' => time(),
						'article' => $couponArticleId,
						'code' => $csvdata[0],
						'type' => $csvdata[1],
						'amount_net' => $csvdata[2],
						'amount_gross' => $csvdata[3],
						'amount_percent' => $csvdata[4],
						'count' => $csvdata[5],
						'limit_start' => $csvdata[6],
						'limit_end' => $csvdata[7],
						'starttime' => $csvdata[8],
						'endtime' => $csvdata[9],
						'newpid' => $GLOBALS['HTTP_POST_VARS']['id']
						);
						$output .= '<br>'.$LANG->getLL("create_successful");
						$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_commercecoupons_coupons', $couponData);
					}
				}
				$row++;
				unset($errorCheck);
			}
			else 
				$output .= '<br>'.$LANG->getLL("error_columns");
		}  # end While
	} else {
			$this->error_wrong_cArtId = $LANG->getLL("error_wrong_couponArticleId");
		}
		fclose ($csvFile);
		$output.="<br><br><hr>";
		return $output;
	}
	
	
	// ############# CSV CHECKS ##########

	//Check number of columns
	function csvCheckCrev($spaltenNum, $num) {
		if($spaltenNum==$num)
			return 1;
	}
	
	//Check if number
	function csvCheckIfNum($data) {
		if(ctype_digit($data))
			return 1;
	}
	
	//Check if content
	function csvCheckIfEmpty($data) {
		if($data!='')
			return 1;
	}
	
	//Check if content is default content
	function csvCheckString($data, $string) {
		if($data==$string)
			return 1;
	}

	// ###########################
	// CSV FUNCTIONEN END
	// ###########################	
	
	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		
		global $BE_USER,$LANG,$BACK_PATH,$TCA,$TYPO3_CONF_VARS,$id,$table;	

		$content = '';
		
		switch((string)$this->MOD_SETTINGS["function"])	{
			case 1:
				#$content="<div align=center><strong>Hier soll die Liste stehen</strong></div><BR>";
				#	The 'Kickstarter' has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script '".substr(t3lib_extMgm::extPath("commerce"),strlen(PATH_site))."mod2/index.php' and edit it!
				#	<HR>
				#	<BR>This is the GET/POST vars sent to the script:<BR>".
				#	"GET:".t3lib_div::view_array($_GET)."<BR>".
				#	"POST:".t3lib_div::view_array($_POST)."<BR>".
				#	"";
				$this->content.=$this->doc->section($LANG->getLL("CustomerData"),$content,0,1);
				#debug('moduleContent case1');
			break;
			case 2:
			
				$this->content .= $this->couponsList();
				$this->content.='<hr />';
				if($_FILES['datei']) {
					if($this->csvUpload('datei', PATH_site.'fileadmin/', '(csv)')==1) {
						//Number of columns
						$spaltenNum = 10;
						$this->content.= $this->csvLines(PATH_site.'fileadmin/'.$_FILES["datei"]["name"], $spaltenNum).'<br /><br />';
						$this->csvDelete(PATH_site.'fileadmin/'.$_FILES["datei"]["name"]);					
					}
				}

				//Links and csv import
				if($GLOBALS['HTTP_GET_VARS']['id'] != '' || $GLOBALS['HTTP_POST_VARS']['id'] != '') {
					$this->content.='<a href="#" onclick="document.location=\'../../../../typo3/alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).'&amp;edit[tx_commercecoupons_coupons]['.$GLOBALS['HTTP_GET_VARS']['id'].']=new\'; return false;"><img src="../res/tx_commercecoupons_coupons.gif" alt="" /> '.$LANG->getLL("link_new_coupon").'</a><br />';
					$this->content.='<a href="#" onclick="document.location=\'../../../../typo3/alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).'&amp;edit[tx_commercecoupons_articles]['.$GLOBALS['HTTP_GET_VARS']['id'].']=new\'; return false;"><img src="../res/tx_commercecoupons_articles.gif" alt="" /> '.$LANG->getLL("link_new_article").'</a>';
					$this->content.='<hr /><a href="#" onclick="document.location=\'\'; return false;">'.$LANG->getLL("link_csv_import").'</a><br /><br />';
					
					$articleRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','tx_commerce_articles','sys_language_uid IN (-1,0) AND article_type_uid = 4 AND deleted = 0');
					
					$articleOptions = '';
					while ($articleRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($articleRes)) {
						$articleOptions = '<option value="'.$articleRow['uid'].'">' . $articleRow['title'] . '</option>';
					}
					$this->content.='

					<!--
						Upload Form for CSV
					-->
					<div id="typo3-listOptions">
						<form action="index.php" enctype="'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'].'" method="post">';
					
					$this->content.='<br />
						<input type="file" name="datei" style="width:336px;" size="50" onclick="changed=1;" />
						<br /><br />
						'.$LANG->getLL("couponArticleIdLabel").': <select name="couponArticleId">' . $articleOptions . '</select>';
						if(strlen($this->error_wrong_cArtId) > 0){
						 		$this->content.= ' <b>'.$this->error_wrong_cArtId.'</b>';
						 	}
						 $this->content.='<br /><br />
						<input type="submit" name="submit" value="'.$LANG->getLL("send").'" />
						<input type="hidden" name="id" value="'.$GLOBALS['HTTP_GET_VARS']['id'].'" />';
					$this->content.='
						</form>
					</div>';
				}
				else {
					$this->content.=$LANG->getLL("error_no_cat");
				}

				$this->content.=$this->doc->section($LANG->getLL("Coupon Data"),$content,0,1);
				#debug('moduleContent case2');
			break;
			case 3:
				$content.="<div align=center><strong>Menu item #3...</strong></div>";
				$this->content.=$this->doc->section("Message #3:",$content,0,1);
				#debug('moduleContent case3');
			break;
		}
	}
	/**
	 * enerates the couponslist for the module orders
	 * HTML Output will be put to $this->content;
	 * 
	 */
	function couponsList()
	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA,$TYPO3_CONF_VARS,$CLIENT;	
		$this->table='tx_commercecoupons_coupons';
		
		#debug('in couponsList');

		//Doppelt Initalisiert
		//$this->content='';
			// Start document template object:
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType='xhtml_trans';
		$this->dontShowClipControlPanels = 0;
			// Loading current page record and checking access:
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

			// Initialize the dblist object:
		$dblist = t3lib_div::makeInstance('tx_commerce_coupons_localRecordlist');
		$dblist->backPath = $BACK_PATH;
		$dblist->script = 'index.php';
		$dblist->calcPerms = $BE_USER->calcPerms($this->pageinfo);
		$dblist->thumbs = $BE_USER->uc['thumbnailsByDefault'];
		$dblist->returnUrl=$this->returnUrl;
		#$dblist->allFields = ($this->MOD_SETTINGS['bigControlPanel'] || $this->table) ? 1 : 0;
		$dblist->allFields = 1;
		
		
		$dblist->localizationView = $this->MOD_SETTINGS['localization'];
		$dblist->showClipboard = 0;	
		#$dblist->disableSingleTableView = $this->modTSconfig['properties']['disableSingleTableView'];
		#$dblist->listOnlyInSingleTableMode = $this->modTSconfig['properties']['listOnlyInSingleTableView'];
		#$dblist->clickTitleMode = $this->modTSconfig['properties']['clickTitleMode'];
		#$dblist->alternateBgColors=$this->modTSconfig['properties']['alternateBgColors']?1:0;
		#$dblist->allowedNewTables = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['allowedNewTables'],1);
		#$dblist->newWizards=$this->modTSconfig['properties']['newWizards']?1:0;



			// Clipboard is initialized:
		
		$dblist->clipObj = t3lib_div::makeInstance('t3lib_clipboard');		// Start clipboard
		$dblist->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session

			// Clipboard actions are handled:
		$CB = t3lib_div::_GET('CB');	// CB is the clipboard command array
		if ($this->cmd=='setCB') {
				// CBH is all the fields selected for the clipboard, CBC is the checkbox fields which were checked. By merging we get a full array of checked/unchecked elements
				// This is set to the 'el' array of the CB after being parsed so only the table in question is registered.
			$CB['el'] = $dblist->clipObj->cleanUpCBC(array_merge(t3lib_div::_POST('CBH'),t3lib_div::_POST('CBC')),$this->cmd_table);
		}
//		if (!$this->MOD_SETTINGS['clipBoard'])	$CB['setP']='normal';	// If the clipboard is NOT shown, set the pad to 'normal'.
//		$dblist->clipObj->setCmd($CB);		// Execute commands.
//		$dblist->clipObj->cleanCurrent();	// Clean up pad
//		$dblist->clipObj->endClipboard();	// Save the clipboard content

			// This flag will prevent the clipboard panel in being shown.
			// It is set, if the clickmenu-layer is active AND the extended view is not enabled.
		#$dblist->dontShowClipControlPanels = $CLIENT['FORMSTYLE'] && !$this->MOD_SETTINGS['bigControlPanel'] && $dblist->clipObj->current=='normal' && !$BE_USER->uc['disableCMlayers'] && !$this->modTSconfig['properties']['showClipControlPanelsDespiteOfCMlayers'];

		$this->doc->JScode=$this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{	//
					document.location = URL;
					return false;
				}
				function jumpExt(URL,anchor)	{	//
					var anc = anchor?anchor:"";
					document.location = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
					return false;
				}
				function jumpSelf(URL)	{	//
					document.location = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
					return false;
				}
				'.$this->doc->redirectUrls(
				$dblist->listURL()).'
				'.$dblist->CBfunctions().'
				function editRecords(table,idList,addParams,CBflag)	{	//
					document.location="'.$backPath.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
						'&edit["+table+"]["+idList+"]=edit"+addParams;
				}
				function editList(table,idList)	{	//
					var list="";

						// Checking how many is checked, how many is not
					var pointer=0;
					var pos = idList.indexOf(",");
					while (pos!=-1)	{
						if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
							list+=idList.substr(pointer,pos-pointer)+",";
						}
						pointer=pos+1;
						pos = idList.indexOf(",",pointer);
					}
					if (cbValue(table+"|"+idList.substr(pointer))) {
						list+=idList.substr(pointer)+",";
					}

					return list ? list : idList;
				}

				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');
			// Setting up the context sensitive menu:
			$CMparts=$this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode.=$CMparts[0];
			$this->doc->postCode.= $CMparts[2];

			// If there is access to the page, then render the list contents and set up the document template object:
		if ($access)	{

				// Deleting records...:
				// Has not to do with the clipboard but is simply the delete action. The clipboard object is used to clean up the submitted entries to only the selected table.
			/**
			 * Deleting Recors within coupons, is this possible
			 */
			/*
			if ($this->cmd=='delete')	{
				$items = $dblist->clipObj->cleanUpCBC(t3lib_div::_POST('CBC'),$this->cmd_table,1);
				if (count($items))	{
					$cmd=array();
					reset($items);
					while(list($iK)=each($items))	{
						$iKParts = explode('|',$iK);
						$cmd[$iKParts[0]][$iKParts[1]]['delete']=1;
					}
					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values=0;
					$tce->start(array(),$cmd);
					$tce->process_cmdmap();

					if (isset($cmd['pages']))	{
						t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
					}

					$tce->printLogErrorMessages(t3lib_div::getIndpEnv('REQUEST_URI'));
				}
			}
			*/

				// Initialize the listing object, dblist, for rendering the list:
			$this->pointer = t3lib_div::intInRange($this->pointer,0,100000);
			
			
			
			$dblist->start($this->id,$this->table,$this->pointer,$this->search_field,$this->search_levels,$this->showLimit);
			
			

				// Render the page header:
			$dblist->writeTop($this->pageinfo);

				// Render versioning selector:
			$dblist->HTMLcode.= $this->doc->getVersionSelector($this->id);

				// Render the list of tables:
				
			$dblist->generateList($this->id,$this->table);
			
				// Write the bottom of the page:
			$dblist->writeBottom();

				// Add JavaScript functions to the page:
			$this->doc->JScode=$this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{	//
					document.location = URL;
					return false;
				}
				function jumpExt(URL,anchor)	{	//
					var anc = anchor?anchor:"";
					document.location = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
					return false;
				}
				function jumpSelf(URL)	{	//
					document.location = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
					return false;
				}
				'.$this->doc->redirectUrls($dblist->listURL()).'
				'.$dblist->CBfunctions().'
				function editRecords(table,idList,addParams,CBflag)	{	//
					document.location="'.$backPath.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
						'&edit["+table+"]["+idList+"]=edit"+addParams;
				}
				function editList(table,idList)	{	//
					var list="";

						// Checking how many is checked, how many is not
					var pointer=0;
					var pos = idList.indexOf(",");
					while (pos!=-1)	{
						if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
							list+=idList.substr(pointer,pos-pointer)+",";
						}
						pointer=pos+1;
						pos = idList.indexOf(",",pointer);
					}
					if (cbValue(table+"|"+idList.substr(pointer))) {
						list+=idList.substr(pointer)+",";
					}

					return list ? list : idList;
				}

				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');

				// Setting up the context sensitive menu:
			//$CMparts=$this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode.=$CMparts[0];
			$this->doc->postCode.= $CMparts[2];
		} // access

			// Begin to compile the whole page, starting out with page header:
		//$this->content='';
		$this->content.=$this->doc->startPage('DB list');
		$this->content.= '<form action="'.htmlspecialchars($dblist->listURL()).'" method="post" name="dblistForm">';

			// List Module CSH:
		if (!strlen($this->id))	{
			$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_module_noId', $GLOBALS['BACK_PATH'],'<br/>|');
		} elseif (!$this->id)	{	// zero...:
			$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_module_root', $GLOBALS['BACK_PATH'],'<br/>|');
		}

			// Add listing HTML code:
		$this->content.= $dblist->HTMLcode;
		$this->content.= '<input type="hidden" name="cmd_table" /><input type="hidden" name="cmd" /></form>';

			// List Module CSH:
//		if ($this->id)	{
#			$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_module', $GLOBALS['BACK_PATH'],'<br/>|');
//		}


			// If a listing was produced, create the page footer with search form etc:
		if ($dblist->HTMLcode)	{

				// Making field select box (when extended view for a single table is enabled):
			if ($dblist->table)	{
				//$this->content.=$dblist->fieldSelectBox($dblist->table);
			}

				// Adding checkbox options for extended listing and clipboard display:
			#$this->content.='
#
#					<!--
#						Listing options for clipboard and thumbnails
#					-->
#					<div id="typo3-listOptions">
#						<form action="index.php" enctype="'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'].'" method="post">
#						'; //<form action="" method="post">

//zzz
//include_once('./typo3/db_list.php?id='.$GLOBALS['HTTP_GET_VARS']['id']);


			//Erweiterte Ansicht 
#			$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[bigControlPanel]',$this->MOD_SETTINGS['bigControlPanel'],'db_list.php','').' '.$LANG->getLL('largeControl',1).'<br />';
			
			#Klemmbrett anzeigen
#			if ($dblist->showClipboard)	{
#				$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[clipBoard]',$this->MOD_SETTINGS['clipBoard'],'db_list.php','').' '.$LANG->getLL('showClipBoard',1).'<br />';
#			}
			#Lokalisierungsansicht
#			$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[localization]',$this->MOD_SETTINGS['localization'],'db_list.php','').' '.$LANG->getLL('localization',1).'<br />';
#			$this->content.='
#						</form>
#					</div>';
#			$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_options', $GLOBALS['BACK_PATH']);

				// Printing clipboard if enabled:
			if ($this->MOD_SETTINGS['clipBoard'] && $dblist->showClipboard)	{
				//$this->content.= $dblist->clipObj->printClipboard();
				//$this->content.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_clipboard', $GLOBALS['BACK_PATH']);
			}

				// Link for creating new records:
#			if (!$this->modTSconfig['properties']['noCreateRecordsLink']) 	{
#				$this->content.='
#
#					<!--
#						Link for creating a new record:
#					-->
#					<div id="typo3-newRecordLink">
#					<a href="'.htmlspecialchars('db_new.php?id='.$this->id.'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))).'">'.
#								'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="" />'.
#								$LANG->getLL('newRecordGeneral',1).
#								'</a>
#					</div>';
#			}

				// Search box:
			//$this->content.=$dblist->getSearchBox();

				// Display sys-notes, if any are found:
#			$this->content.=$dblist->showSysNotesForPage();

				// ShortCut:
#			if ($BE_USER->mayMakeShortcut())	{
#				$this->content.='<br/>'.$this->doc->makeShortcutIcon('id,imagemode,pointer,table,search_field,search_levels,showLimit,sortField,sortRev',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']);
#			}
		}

			// Finally, close off the page:
#		$this->content= $this->doc->endPage();
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce_coupons/mod_coupons/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commerce_coupons/mod_coupons/index.php']);
}
t3lib_div::loadTca('tx_commerce_coupons');

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_commerce_coupons');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>