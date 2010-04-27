<?php

########################################################################
# Extension Manager/Repository config file for ext "commerce_coupons".
#
# Auto generated 20-02-2010 18:29
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Commerce coupons',
	'description' => 'Provides Coupons for commerce',
	'category' => 'plugin',
	'author' => 'Thomas Hempel, Volker Graubaum, Ralf Merz, Ingo Schmitt, Joerg Sprung, Christian Ehret',
	'author_email' => 'thomas@typo3-unleashed.net, team@typo3-commerce.org, ralf@ralf-merz.de, ce@toco3.com',
	'shy' => 0,
	'dependencies' => 'commerce,graytree',
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
	'author_company' => 'n@work Internet Informationssysteme GmbH, e-netconsulting KG, merzilla, Marketing Factory Consulting GmbH, TOCO3 GmbH & Co. KG',
	'version' => '0.2.6',
	'_md5_values_when_last_written' => 'a:74:{s:9:"ChangeLog";s:4:"4550";s:10:"README.txt";s:4:"c628";s:35:"class.tx_commercecoupons_fields.php";s:4:"2f26";s:21:"ext_conf_template.txt";s:4:"c662";s:12:"ext_icon.gif";s:4:"86fa";s:17:"ext_localconf.php";s:4:"74b9";s:14:"ext_tables.php";s:4:"4fa1";s:14:"ext_tables.sql";s:4:"e46e";s:29:"ext_typoscript_setup_save.txt";s:4:"21b9";s:36:"icon_tx_commercecoupons_articles.gif";s:4:"4b3a";s:35:"icon_tx_commercecoupons_coupons.gif";s:4:"1e83";s:13:"locallang.xml";s:4:"c65a";s:21:"locallang_coupons.xml";s:4:"cb48";s:16:"locallang_db.xml";s:4:"123d";s:7:"tca.php";s:4:"79ee";s:26:"doc/baskethook_example.txt";s:4:"076c";s:26:"doc/csv_import_example.csv";s:4:"121b";s:14:"doc/manual.sxw";s:4:"4bb9";s:19:"doc/quick_howto.txt";s:4:"7226";s:54:"hooks/Copy of class.tx_commercecoupons_baskethooks.php";s:4:"5dc8";s:46:"hooks/class.tx_commercecoupons_baskethooks.php";s:4:"0c3f";s:52:"hooks/class.tx_commercecoupons_basketrenderhooks.php";s:4:"8a77";s:42:"hooks/class.tx_commercecoupons_cohooks.php";s:4:"3afa";s:49:"hooks/class.tx_commercecoupons_colistinghooks.php";s:4:"edb0";s:42:"hooks/class.tx_commercecoupons_dmhooks.php";s:4:"3a51";s:44:"hooks/class.tx_commercecoupons_mailhooks.php";s:4:"7d70";s:49:"lib/class.tx_commerce_coupons_localrecordlist.php";s:4:"cca7";s:46:"lib/class.tx_commerce_coupons_localrecordlist2";s:4:"4781";s:36:"lib/class.tx_commercecoupons_lib.php";s:4:"c409";s:40:"lib/class.tx_commercecoupons_lib.php.dev";s:4:"eb70";s:50:"mod_coupons/class.tx_commerce_coupons_navframe.php";s:4:"f4d1";s:43:"mod_coupons/class.user_couponsedit_func.php";s:4:"8b28";s:21:"mod_coupons/clear.gif";s:4:"cc11";s:20:"mod_coupons/conf.php";s:4:"5854";s:21:"mod_coupons/index.php";s:4:"d251";s:32:"mod_coupons/index.php.conflicted";s:4:"fe7e";s:22:"mod_coupons/index2.php";s:4:"950a";s:25:"mod_coupons/index_old.php";s:4:"8b06";s:25:"mod_coupons/locallang.xml";s:4:"5405";s:33:"mod_coupons/locallang_coupons.xml";s:4:"0e89";s:29:"mod_coupons/locallang_mod.xml";s:4:"4b98";s:26:"mod_coupons/moduleicon.gif";s:4:"1e83";s:30:"mod_coupons/moduleicon_old.gif";s:4:"1964";s:27:"pi1/Copy of couponfile.tmpl";s:4:"21a7";s:36:"pi1/class.tx_commercecoupons_pi1.php";s:4:"cd8e";s:19:"pi1/couponfile.tmpl";s:4:"c3c9";s:23:"pi1/couponsInBasket.tpl";s:4:"0ad8";s:23:"pi1/couponsInFinish.tpl";s:4:"8720";s:24:"pi1/couponsInListing.tpl";s:4:"4e6c";s:22:"pi1/couponsInMails.tpl";s:4:"bc0a";s:17:"pi1/locallang.xml";s:4:"a86a";s:24:"pi1/static/editorcfg.txt";s:4:"e8dd";s:35:"res/tx_commercecoupons_articles.gif";s:4:"4b3a";s:38:"res/tx_commercecoupons_articles__d.gif";s:4:"2895";s:38:"res/tx_commercecoupons_articles__h.gif";s:4:"5ad2";s:34:"res/tx_commercecoupons_coupons.gif";s:4:"1e83";s:37:"res/tx_commercecoupons_coupons__d.gif";s:4:"714d";s:37:"res/tx_commercecoupons_coupons__f.gif";s:4:"53c7";s:38:"res/tx_commercecoupons_coupons__fu.gif";s:4:"2125";s:37:"res/tx_commercecoupons_coupons__h.gif";s:4:"8294";s:38:"res/tx_commercecoupons_coupons__hf.gif";s:4:"8294";s:39:"res/tx_commercecoupons_coupons__hfu.gif";s:4:"f1e1";s:38:"res/tx_commercecoupons_coupons__ht.gif";s:4:"186f";s:39:"res/tx_commercecoupons_coupons__htf.gif";s:4:"186f";s:40:"res/tx_commercecoupons_coupons__htfu.gif";s:4:"2669";s:39:"res/tx_commercecoupons_coupons__htu.gif";s:4:"2669";s:38:"res/tx_commercecoupons_coupons__hu.gif";s:4:"f1e1";s:37:"res/tx_commercecoupons_coupons__t.gif";s:4:"a8b2";s:38:"res/tx_commercecoupons_coupons__tf.gif";s:4:"a8b2";s:39:"res/tx_commercecoupons_coupons__tfu.gif";s:4:"c352";s:38:"res/tx_commercecoupons_coupons__tu.gif";s:4:"c352";s:37:"res/tx_commercecoupons_coupons__u.gif";s:4:"1b09";s:20:"static/constants.txt";s:4:"6d9a";s:16:"static/setup.txt";s:4:"c46f";}',
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