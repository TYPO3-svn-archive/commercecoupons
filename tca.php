<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

require_once(t3lib_extMgm::extPath('commerce_coupons') .'class.tx_commercecoupons_fields.php');

$TCA["tx_commercecoupons_coupons"] = Array (
	"ctrl" => $TCA["tx_commercecoupons_coupons"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,code,amount,article,count,type,limit_start,limit_end,newpid"
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
		'newpid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce_coupons/locallang_db.php:tx_commerce_coupons.pid',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'pages',
				'itemsProcFunc' =>'user_couponsedit_func->coupons_status',
				
				/**
				 * Dumme sql, for selecting nothing
				 */
				#'foreign_table_where' => 'AND -1 = 1'
			)
		),

		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",

				"checkbox" => "0"
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
				"eval" => "required,trim",
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
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,integer",
			)
		),
		"amount_net" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.amount_net",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,integer",
			)
		),
		"amount_percent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.amount_percent",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				//"eval" => "required,integer",
			)
		),
		"article" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.article",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",	
				"allowed" => "tx_commerce_articles",	
				"size" => 1,	
				"minitems" => 1,
				"maxitems" => 1,
				
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
				"eval" => "required,integer",
			)
		),
		"type" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type.I.0", "money"),
					Array("LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.type.I.1", "percent"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
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
				"type" => "input",	
				"size" => "30",	
				"eval" => "integer",
			)
		),
		"limit_end" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.limit_end",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "integer",
			)
		),
		"has_articles" => Array (
		    "exclude" => 1,
    	    	    "label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_coupons.has_articles",
        	    "config" => Array (
        		"type" => "check",
    	    	        "default" => "0"
        	    )
		),
		"related_article" => Array (
		    "exclude" => 1,
		    "label" => "Artikel",
		    "config" => Array (
			"type" => "user",
			"userFunc" => "tx_commercecoupons_fields->related_article",
		    ),
		),
	    

	),
	"types" => Array (
		"0" => Array("showitem" => "
		    ---div---;Coupon,hidden;;1;;1-1-1, code, amount_net,amount_gross, article, count, type, amount_percent, order_id,  newpid, limit_start, limit_end, has_articles,first_name,last_name,
		    ---div---;Artikel,related_article;;;;1-1-1"
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
	    "coupon_id" => Array (        
	        "exclude" => 1,        
		"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_articles.coupon_id",        
        	"config" => Array (
	                "type" => "group",    
	                "internal_type" => "db",    
	                "allowed" => "tx_commercecoupons_coupons",    
	                "size" => 1,    
	                "minitems" => 0,
	                "maxitems" => 1,
	        )
	    ),
	    "article_id" => Array (        
        	"exclude" => 1,        
			"label" => "LLL:EXT:commerce_coupons/locallang_db.php:tx_commercecoupons_articles.article_id",        
	        "config" => Array (
		        "type" => "group",    
		        "internal_type" => "db",    
		        "allowed" => "tx_commerce_articles",    
		        "size" => 1,    
		        "minitems" => 0,
		        "maxitems" => 1,
			),
	    ),
	   	'price_net' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce/locallang_db.php:tx_commerce_order_articles.price_net',
			'config' => Array (
				'type' => 'input',
				'size' => '6',
				'eval' => 'integer',
			)
		),
		'price_gross' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:commerce/locallang_db.php:tx_commerce_order_articles.price_gross',
			'config' => Array (
				'type' => 'input',
				'size' => '6',
				'eval' => 'integer',
			)
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
            "0" => Array("showitem" => "hidden;;1;;1-1-1, coupon_id, article_id,price_net,price_gross,amount"),
    ),
    "palettes" => Array (
            "1" => Array("showitem" => ""),
    ),  
);

?>