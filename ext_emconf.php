<?php

########################################################################
# Extension Manager/Repository config file for ext: "commerce_coupons"
#
# Auto generated 30-01-2008 18:39
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
	'version' => '0.0.8',
	'_md5_values_when_last_written' => 'a:68:{s:9:"ChangeLog";s:4:"9af5";s:10:"README.txt";s:4:"c628";s:35:"class.tx_commercecoupons_fields.php";s:4:"4409";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"2e1c";s:14:"ext_tables.php";s:4:"9d99";s:14:"ext_tables.sql";s:4:"a67f";s:29:"ext_typoscript_setup_save.txt";s:4:"21b9";s:13:"locallang.xml";s:4:"ff67";s:21:"locallang_coupons.xml";s:4:"9e05";s:16:"locallang_db.xml";s:4:"da3a";s:7:"tca.php";s:4:"cd6d";s:49:"lib/class.tx_commerce_coupons_localrecordlist.php";s:4:"edab";s:46:"lib/class.tx_commerce_coupons_localrecordlist2";s:4:"4781";s:36:"lib/class.tx_commercecoupons_lib.php";s:4:"0162";s:40:"lib/class.tx_commercecoupons_lib.php.dev";s:4:"eb70";s:26:"doc/baskethook_example.txt";s:4:"076c";s:26:"doc/csv_import_example.csv";s:4:"121b";s:19:"doc/quick_howto.txt";s:4:"7226";s:27:"pi1/Copy of couponfile.tmpl";s:4:"119a";s:36:"pi1/class.tx_commercecoupons_pi1.php";s:4:"f28b";s:19:"pi1/couponfile.tmpl";s:4:"ad21";s:23:"pi1/couponsInBasket.tpl";s:4:"1f5d";s:23:"pi1/couponsInFinish.tpl";s:4:"8720";s:24:"pi1/couponsInListing.tpl";s:4:"f43c";s:22:"pi1/couponsInMails.tpl";s:4:"1374";s:17:"pi1/locallang.xml";s:4:"cb0b";s:24:"pi1/static/editorcfg.txt";s:4:"e8dd";s:20:"static/constants.txt";s:4:"7d2c";s:16:"static/setup.txt";s:4:"8bd7";s:50:"mod_coupons/class.tx_commerce_coupons_navframe.php";s:4:"f4d1";s:43:"mod_coupons/class.user_couponsedit_func.php";s:4:"16b4";s:21:"mod_coupons/clear.gif";s:4:"cc11";s:20:"mod_coupons/conf.php";s:4:"5854";s:21:"mod_coupons/index.php";s:4:"82ce";s:22:"mod_coupons/index2.php";s:4:"950a";s:25:"mod_coupons/index_old.php";s:4:"8b06";s:25:"mod_coupons/locallang.xml";s:4:"46f8";s:33:"mod_coupons/locallang_coupons.xml";s:4:"c710";s:29:"mod_coupons/locallang_mod.xml";s:4:"4b98";s:26:"mod_coupons/moduleicon.gif";s:4:"1e83";s:30:"mod_coupons/moduleicon_old.gif";s:4:"1964";s:54:"hooks/Copy of class.tx_commercecoupons_baskethooks.php";s:4:"5dc8";s:46:"hooks/class.tx_commercecoupons_baskethooks.php";s:4:"5302";s:42:"hooks/class.tx_commercecoupons_cohooks.php";s:4:"a8fe";s:49:"hooks/class.tx_commercecoupons_colistinghooks.php";s:4:"98d1";s:42:"hooks/class.tx_commercecoupons_dmhooks.php";s:4:"c096";s:44:"hooks/class.tx_commercecoupons_mailhooks.php";s:4:"ae74";s:35:"res/tx_commercecoupons_articles.gif";s:4:"4b3a";s:42:"res/tx_commercecoupons_articles__d.gif.gif";s:4:"2895";s:42:"res/tx_commercecoupons_articles__h.gif.gif";s:4:"5ad2";s:34:"res/tx_commercecoupons_coupons.gif";s:4:"1e83";s:41:"res/tx_commercecoupons_coupons__d.gif.gif";s:4:"714d";s:41:"res/tx_commercecoupons_coupons__f.gif.gif";s:4:"53c7";s:42:"res/tx_commercecoupons_coupons__fu.gif.gif";s:4:"2125";s:41:"res/tx_commercecoupons_coupons__h.gif.gif";s:4:"8294";s:42:"res/tx_commercecoupons_coupons__hf.gif.gif";s:4:"8294";s:43:"res/tx_commercecoupons_coupons__hfu.gif.gif";s:4:"f1e1";s:42:"res/tx_commercecoupons_coupons__ht.gif.gif";s:4:"186f";s:43:"res/tx_commercecoupons_coupons__htf.gif.gif";s:4:"186f";s:44:"res/tx_commercecoupons_coupons__htfu.gif.gif";s:4:"2669";s:43:"res/tx_commercecoupons_coupons__htu.gif.gif";s:4:"2669";s:42:"res/tx_commercecoupons_coupons__hu.gif.gif";s:4:"f1e1";s:41:"res/tx_commercecoupons_coupons__t.gif.gif";s:4:"a8b2";s:42:"res/tx_commercecoupons_coupons__tf.gif.gif";s:4:"a8b2";s:43:"res/tx_commercecoupons_coupons__tfu.gif.gif";s:4:"c352";s:42:"res/tx_commercecoupons_coupons__tu.gif.gif";s:4:"c352";s:41:"res/tx_commercecoupons_coupons__u.gif.gif";s:4:"1b09";}',
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