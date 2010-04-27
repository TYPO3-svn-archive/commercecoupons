<?php
class tx_commercecoupons_treelibhooks {

	/**
	 * Hook: processItemArrayForBrowseableTreeDefault
	 * @param 	$itemFormElValue	contains the comma-separated uid-list of the categories
	 * @param	$table				table name, but is empty in this case
	 * @param	$uid				the uid of the category
	 *
	 * @return	string()			string with the needed values to show the selected categories
	 */
	function processDefault($itemFormElValue, $table, $uid) {
		if(empty($table)) {
			$table = 'tx_commerce_categories_';
		}
		
		// category.
		$category = t3lib_div::makeInstance('tx_commerce_category');
		$category->init($uid);
		$category->load_data();

		#return $table . $uid . '|' . $category->get_title();
		return $uid . '|' . $category->get_title();
	}

}
?>