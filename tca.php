<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

require_once(t3lib_extMgm::extPath('commerce_coupons') .'class.tx_commercecoupons_fields.php');
$commerce_coupons_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['commerce_coupons']);
$eanArticleUid = $commerce_coupons_conf['eanArticleUid'];
$TCA["tx_commercecoupons_coupons"] = Array (
	"ctrl" => $TCA["tx_commercecoupons_coupons"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "deleted,hidden,starttime,endtime,fe_group,code,amount,article,count,type,limit_start,limit_end,newpid,include_exclude_category,own_field,button_label,description,related_categories"
	),
	"feInterface" => $TCA["tx_commercecoupons_coupons"]["feInterface"],
	"columns" => Array (
		
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commerce_coupons.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
#		'newpid' => Array (
#			'exclude' => 1,
#			'label' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commerce_coupons.pid',
#			'config' => Array (
#				'type' => 'select',
#				'foreign_table' => 'pages',
#				'itemsProcFunc' =>'user_couponsedit_func->coupons_status',
#				
				/**
				 * Dumme sql, for selecting nothing
#				 */
#				#'foreign_table_where' => 'AND -1 = 1'
#			)
#		),

		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"code" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "required,trim"
			)
		),
		/*"first_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.first_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
			)
		),*/
		/*"last_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.last_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
			)
		),*/
		"amount_gross" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.amount_gross",		
			"config" => Array (
				"type" => "user",
				"userFunc" => "tx_commercecoupons_fields->calculate_price",
			),
			'displayCond' => 'FIELD:type:=:money',
		),
		"amount_net" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.amount_net",		
			"config" => Array (
				"type" => "user",
				"userFunc" => "tx_commercecoupons_fields->calculate_price",
			),
			'displayCond' => 'FIELD:type:=:money',
		),
		"amount_percent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.amount_percent",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				//"eval" => "required,integer",
			),
			'displayCond' => 'FIELD:type:=:percent',
		),
		"article" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.article",		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_commerce_articles',
				'foreign_table_where' => 'AND tx_commerce_articles.sys_language_uid IN (-1,0) AND tx_commerce_articles.article_type_uid IN (4,5,6,7)',
			)
		),
		/*"order_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.order_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_commerce_orders",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),*/
		"count" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.count",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				/* "checkbox" => "0", */
				"default" => "1",
				"eval" => "required,integer",
			)
		),
		"type" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type",	
		    'default' => 0,	
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type.I.0", "money"),
					Array("LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type.I.1", "percent"),
					Array("LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type.I.2", "article"),
				),
				"size" => 1,	
				"maxitems" => 1,
				'default' => 'money',	
			)
		),
		"EAN13_countryprefix" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.EAN13_countryprefix",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				"max" => "3",
			),
			'displayCond' => 'FIELD:article:=:'.$eanArticleUid,
		),		
		"EAN13_companynumber" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.EAN13_companynumber",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "5",
			),
			'displayCond' => 'FIELD:article:=:'.$eanArticleUid,
		),
					
		'newpid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.newpid',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'pages',
				'itemsProcFunc' =>'user_couponsedit_func->coupon_status',
			)
		),
		
		"limit_start" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.limit_start",		
			"config" => Array (
				"type" => "user",
				"userFunc" => "tx_commercecoupons_fields->calculate_price",
			)
		),
		"limit_end" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.limit_end",		
			"config" => Array (
				"type" => "user",
				"userFunc" => "tx_commercecoupons_fields->calculate_price",
			)
		),
		"include_exclude_category" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.include_exclude_category",
			"config" => Array (
				"type" => "check",
				"default" => 1,
				"items" => array(
					array('LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.include_exclude_category_label', '')
				)
			)
		),
		'related_categories' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.related_categories',
			'config' => Array (
				'type' => 'passthrough',
	            'form_type' => 'user',
	            'userFunc' => 'EXT:commerce/treelib/class.tx_commerce_tcefunc.php:&tx_commerce_tceFunc->getSingleField_selectCategories',
	            'allowProducts' => false,
	            'treeViewBrowseable' => true,
	            'size' => 10,
	            'substituteRealValues' => false,
	            'autoSizeMax' => 30,
	            'minitems' => 0,
	            'maxitems' => 50,
	            #'eval' => 'required',
			),			
		),
		/* Ralf Merz 2010-02-20: 
		 commented out the next 3 fields: Kneipp specific?
		"own_field" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.own_field",
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),		
		"button_label" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.button_label",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "trim"
			)
		),			
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.description",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "trim"
			)
		),			
		*/
		"has_articles" => Array (
		    "exclude" => 1,
    	   	"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.has_articles",
        	"config" => Array (
        	"type" => "check",
    	    	        "default" => "0"
        	    ),
        	'displayCond' => 'FIELD:type:=:article',
		),
		"related_articles" => Array (
		    "exclude" => 1,
    	    "label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.related_articles",
		    'displayCond' => 'FIELD:type:=:article',
    	    "config" => Array (
				//"type" => "user",
				//"userFunc" => "tx_commercecoupons_fields->related_article",
	     		'type' => 'select',
            	'foreign_table' => 'tx_commercecoupons_articles',
            	'maxitems' => '1',
            	'minitems' => '1',
		        'items' => Array (
               			Array('', 0),
		        ),
				'wizards' => Array(
		            '_PADDING' => 1,
	    	        '_VERTICAL' => 1,
	    	        'edit' => Array(
         				'type' => 'popup',
         				'title' => 'Edit usergroup',
         				'script' => 'wizard_edit.php',
         				'icon' => 'edit2.gif',
         				'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
     				),
					/*
	        	    'edit' => Array(
						'type' => 'script',
						'title' => 'Edit article',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 0,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
						'params' => Array(
                    		'table' => 'tx_commercecoupons_articles',
                       		'pid' => '###CURRENT_PID###',
                       		'setValue' => 'set',
                 		),
					),*/
	    	        
	    	        'add' => Array(
	    	        	'type' => 'script',
						'title' => 'add article',
						'script' => 'wizard_add.php',
						'icon' => 'add.gif',
						'popup_onlyOpenIfSelected' => 1,
						'params' => Array(
                    		'table' => 'tx_commercecoupons_articles',
                       		'pid' => '###CURRENT_PID###',
                       		'setValue' => 'set',
                 		),
					),
					
           		),
			),
		),
	),
	"types" => Array (
		/*"0" => Array("showitem" => "
		    ---div---;Coupon,hidden;;1;;1-1-1, code, amount_net, amount_gross, article, EAN13_countryprefix, EAN13_companynumber, count, type, amount_percent, order_id,  newpid, limit_start, limit_end, include_exclude_category, related_categories, own_field, button_label, description, has_articles, first_name, last_name,
		    ---div---;Artikel,related_articles;;;;1-1-1"
		)*/
		"0" => Array("showitem" => "
		    ---div---;Coupon,hidden;;1;;1-1-1, code, amount_net, amount_gross, article, EAN13_countryprefix, EAN13_companynumber, count, type, amount_percent, order_id,  newpid, limit_start, limit_end, include_exclude_category, related_categories, has_articles, first_name, last_name,
		    ---div---;Artikel,related_articles;;;;1-1-1"
		)
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);

$TCA["tx_commercecoupons_articles"] = Array (
    "ctrl" => $TCA["tx_commercecoupons_articles"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden,coupon_id,article_id"
    ),
    "feInterface" => $TCA["tx_commercecoupons_articles"]["feInterface"],
    "columns" => Array (
            "hidden" => Array (        
        	"exclude" => 1,
    	        "label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
        	"config" => Array (
        	    "type" => "check",
    	            "default" => "0"
        	)
	    ),
//	    "coupon_id" => Array (        
//	        "exclude" => 1,        
//			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_articles.coupon_id",        
//        	"config" => Array (
//	                'type' => 'select',
//					'foreign_table' => 'tx_commercecoupons_coupons',
//					'foreign_table_where' => 'AND tx_commercecoupons_coupons.has_articles=1',
//        	    
//	                "size" => 1,    
//	                "minitems" => 1,
//	                "maxitems" => 1,
//	        )
//	    ),

	    "name" => Array (        
	        "exclude" => 1,        
			"label" => "name",        
        	"config" => Array (
	                'type' => 'input',
					'eval' => 'required,trim',
	        )
	    ),
	    "article_id" => Array (        
        	"exclude" => 1,        
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_articles.article_id",        
	        "config" => Array (
		        'type' => 'select',
				'foreign_table' => 'tx_commerce_articles',
				'foreign_table_where' => 'and tx_commerce_articles.deleted = 0 AND tx_commerce_articles.sys_language_uid IN (-1,0) AND tx_commerce_articles.article_type_uid IN (1) ORDER BY tx_commerce_articles.title',
		        "size" => 1,    
		        "minitems" => 1,
		        "maxitems" => 1,
			),
	    ),
	   	'price_net' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce/locallang_db.php:tx_commerce_order_articles.price_net',
			/*'config' => Array (
				'type' => 'input',
				'size' => '6',
				'eval' => 'double2',
			)*/
			"config" => Array (
				"type" => "user",
				"userFunc" => "tx_commercecoupons_fields->calculate_price",
			),
			
		),
		'price_gross' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce/locallang_db.php:tx_commerce_order_articles.price_gross',
			/*'config' => Array (
				'type' => 'input',
				'size' => '6',
				'eval' => 'double2',
			)*/
			
			"config" => Array (
				"type" => "user",
				"userFunc" => "tx_commercecoupons_fields->calculate_price",
			),
		),
		/*'amount' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce/locallang_db.php:tx_commerce_order_articles.amount',
			'config' => Array (
				'type' => 'input',
				'size' => '2',
				'eval' => 'required,num',
			)
		),*/
	    

	),
    "types" => Array (
            "0" => Array("showitem" => "hidden;;1;;1-1-1, name, article_id,price_net,price_gross,amount"),
    ),
    "palettes" => Array (
            "1" => Array("showitem" => ""),
    ),  
);

?>