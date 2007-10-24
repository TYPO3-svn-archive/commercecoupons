<?php

########################################################################
# Extension Manager/Repository config file for ext: "commerce_coupons"
#
# Auto generated 10-07-2007 15:52
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Commerce coupons',
	'description' => 'Provides Coupons for commerce',
	'category' => 'misc',
	'author' => 'Thomas Hempel',
	'author_email' => 'thomas@typo3-unleashed.net',
	'shy' => '',
	'dependencies' => 'commerce',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod_coupons',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'_md5_values_when_last_written' => 'a:48:{s:9:"ChangeLog";s:4:"9929";s:10:"README.txt";s:4:"ee2d";s:35:"class.tx_commercecoupons_fields.php";s:4:"a70f";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"da3c";s:14:"ext_tables.php";s:4:"ffd2";s:14:"ext_tables.sql";s:4:"9f73";s:29:"ext_typoscript_setup_save.txt";s:4:"2357";s:36:"icon_tx_commercecoupons_articles.gif";s:4:"4b3a";s:35:"icon_tx_commercecoupons_coupons.gif";s:4:"1e83";s:13:"locallang.php";s:4:"66bd";s:21:"locallang_coupons.php";s:4:"289a";s:16:"locallang_db.php";s:4:"17ef";s:7:"tca.php";s:4:"35b9";s:49:"lib/class.tx_commerce_coupons_localrecordlist.php";s:4:"07d8";s:46:"lib/class.tx_commerce_coupons_localrecordlist2";s:4:"4781";s:36:"lib/class.tx_commercecoupons_lib.php";s:4:"6f85";s:40:"lib/class.tx_commercecoupons_lib.php.dev";s:4:"dd4f";s:50:"mod_coupons/class.tx_commerce_coupons_navframe.php";s:4:"14f2";s:43:"mod_coupons/class.user_couponsedit_func.php";s:4:"1500";s:21:"mod_coupons/clear.gif";s:4:"cc11";s:20:"mod_coupons/conf.php";s:4:"dc88";s:21:"mod_coupons/index.php";s:4:"c1d1";s:22:"mod_coupons/index2.php";s:4:"be48";s:25:"mod_coupons/index_old.php";s:4:"edba";s:25:"mod_coupons/locallang.php";s:4:"2c76";s:25:"mod_coupons/locallang.xml";s:4:"7fc5";s:33:"mod_coupons/locallang_coupons.php";s:4:"289a";s:29:"mod_coupons/locallang_mod.php";s:4:"289a";s:29:"mod_coupons/locallang_mod.xml";s:4:"0aae";s:26:"mod_coupons/moduleicon.gif";s:4:"1e83";s:30:"mod_coupons/moduleicon_old.gif";s:4:"1964";s:36:"pi1/class.tx_commercecoupons_pi1.php";s:4:"0784";s:19:"pi1/couponfile.tmpl";s:4:"d287";s:23:"pi1/couponsInBasket.tpl";s:4:"8242";s:23:"pi1/couponsInFinish.tpl";s:4:"8720";s:24:"pi1/couponsInListing.tpl";s:4:"7fa7";s:17:"pi1/locallang.php";s:4:"0b95";s:24:"pi1/static/editorcfg.txt";s:4:"b4ff";s:20:"static/constants.txt";s:4:"3b8b";s:16:"static/setup.txt";s:4:"901d";s:26:"doc/baskethook_example.txt";s:4:"f83d";s:26:"doc/csv_import_example.csv";s:4:"121b";s:46:"hooks/class.tx_commercecoupons_baskethooks.php";s:4:"6898";s:42:"hooks/class.tx_commercecoupons_cohooks.php";s:4:"c7d5";s:49:"hooks/class.tx_commercecoupons_colistinghooks.php";s:4:"f041";s:42:"hooks/class.tx_commercecoupons_dmhooks.php";s:4:"b9c8";s:44:"hooks/class.tx_commercecoupons_mailhooks.php";s:4:"e992";}',
	'constraints' => array(
		'depends' => array(
			'commerce' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>