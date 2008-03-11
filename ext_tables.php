<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'COMMERCE Coupons');

$TCA["tx_commercecoupons_articles"] = Array (
    "ctrl" => Array (
            "title" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_articles",        
            "label" => "name",    
            "tstamp" => "tstamp",
	    "crdate" => "crdate",
	    "cruser_id" => "cruser_id",
	    "default_sortby" => "ORDER BY crdate",    
	   'delete' => 'deleted',
	    "enablecolumns" => Array (        
		'disabled' => 'hidden',
	    ),
    "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
    "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/tx_commercecoupons_articles.gif",
    ),
    "feInterface" => Array (
            "fe_admin_fieldList" => "hidden, name, article_id, price_gross, price_net,amount",
    )
);
	
$TCA["tx_commercecoupons_coupons"] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons',		
		'label' => 'code',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",	
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/tx_commercecoupons_coupons.gif",
		'dividers2tabs' => 1,
		'requestUpdate' => 'type',
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "deleted, hidden, starttime, endtime, fe_group, code, amount, article, count, type, limit_start, limit_end,first_name,last_name,dedication,related_articles",
	)
);


// ToDo 	fill TCA of table tx_commercecoupons_cashed
/*
$TCA["tx_commercecoupons_cashed"] = Array(
	'ctrl' => Array(
		
	)
);*/

$tempColumns = Array (
		'coupon_list' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commerce_orders.coupon_list',
			'config' => Array (
				'type' => 'user',
				'userFunc' => 'user_couponsedit_func->order_couponlist',
		
			)
		),
		'activate_coupons' => array (
		    'exclude' => 1,
		    'label' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commerce_orders.activate_coupons',
		    'config' => array (
			'type' => 'user',
			'userFunc' => 'user_couponsedit_func->activate_coupons',
		    ),
		),
		
		"tx_commercecoupons_include_exclude_category" => Array (        ## WOP:[fields][1][fields][1][fieldname]
        "exclude" => 0,        ## WOP:[fields][1][fields][1][excludeField]
        "label" => "LLL:EXT:commerce_coupons/locallang_db.xml:tx_commercecoupons_coupons.tx_commercecoupons_include_exclude_category",        ## WOP:[fields][1][fields][1][title]
        "config" => Array (
            "type" => "check",
        )
    ),
    "tx_commercecoupons_related_categories" => Array (        ## WOP:[fields][1][fields][2][fieldname]
        "exclude" => 0,        ## WOP:[fields][1][fields][2][excludeField]
        "label" => "LLL:EXT:commerce_coupons/locallang_db.xml:tx_commercecoupons_coupons.tx_commercecoupons_related_categories",        ## WOP:[fields][1][fields][2][title]
        "config" => Array (
            "type" => "select",    ## WOP:[fields][1][fields][2][conf_rel_type]
            "foreign_table" => " tx_commerce_categories",    ## WOP:[fields][1][fields][2][conf_rel_table]
            "foreign_table_where" => "ORDER BY  tx_commerce_categories.uid",    ## WOP:[fields][1][fields][2][conf_rel_type]
            "size" => 10,    ## WOP:[fields][1][fields][2][conf_relations_selsize]
            "minitems" => 0,
            "maxitems" => 100,    ## WOP:[fields][1][fields][2][conf_relations]
            "wizards" => Array(
                "_PADDING" => 2,
                "_VERTICAL" => 1,
                ## WOP:[fields][1][fields][2][conf_wiz_listrec]
                "list" => Array(
                    "type" => "script",
                    "title" => "List",
                    "icon" => "list.gif",
                    "params" => Array(
                        "table"=>" tx_commerce_categories",
                        "pid" => "###CURRENT_PID###",
                    ),
                    "script" => "wizard_list.php",
                ),
            ),
        )
    ),
		
);

t3lib_div::loadTCA('tx_commerce_orders');
t3lib_extMgm::addTCAcolumns('tx_commerce_orders',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_commerce_orders','--div--;LLL:EXT:commerce_coupons/locallang_db.php:tx_commerce_orders.coupons,coupon_list, activate_coupons');
t3lib_extMgm::addToAllTCAtypes("tx_commercecoupons_coupons","tx_commercecoupons_include_exclude_category;;;;1-1-1, tx_commercecoupons_related_categories");

 if (TYPO3_MODE=='BE')
 {
     t3lib_extMgm::addModule("txcommerceM1","txcommerceM2","",t3lib_extMgm::extPath($_EXTKEY)."mod_coupons/");
     require_once(t3lib_extMgm::extPath('commerce_coupons').'mod_coupons/class.user_couponsedit_func.php');
 }
 
 /* ######x########### PI1 (coupons) ##################### */
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY .'_pi1'] = 'layout,select_key,pages';
t3lib_extMgm::addPlugin(Array('LLL:EXT:commerce_coupons/locallang.php:tt_content.list_type_pi1', $_EXTKEY .'_pi1'), 'list_type'); 

?>