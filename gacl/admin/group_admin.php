<?php
/*
Ready for smarty 3
Changes: Used smarty 3 data object
*/
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;

//make a smarty 3 data object
$data = new Smarty_Data;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
            echo xlt('ACL Administration Not Authorized');
            exit;
}

require_once('gacl_admin.inc.php');

//GET takes precedence.
if ($_GET['group_type'] != '') {
	$group_type = $_GET['group_type'];
} else {
	$group_type = $_POST['group_type'];
}

switch(strtolower(trim($group_type))) {
	case 'axo':
		$group_type = 'axo';
		$group_table = $gacl_api->_db_table_prefix . 'axo_groups';
		$group_map_table = $gacl_api->_db_table_prefix . 'groups_axo_map';
		$data->assign('current','axo_group');
		break;
	default:
		$group_type = 'aro';
		$group_table = $gacl_api->_db_table_prefix . 'aro_groups';
		$group_map_table = $gacl_api->_db_table_prefix . 'groups_aro_map';
		$data->assign('current','aro_group');
		break;
}

switch ($_POST['action']) {
	case 'Delete':
		//See edit_group.php
		break;
	default:
		$formatted_groups = $gacl_api->format_groups($gacl_api->sort_groups($group_type), HTML);

		$query = '
			SELECT		a.id, a.name, a.value, count(b.'. $group_type .'_id)
			FROM		'. $group_table .' a
			LEFT JOIN	'. $group_map_table .' b ON b.group_id=a.id
			GROUP BY	a.id,a.name,a.value';
		$rs = $db->Execute($query);

		$group_data = array();

		if(is_object($rs)) {
			while($row = $rs->FetchRow()) {
				$group_data[$row[0]] = array(
					'name' => $row[1],
					'value' => $row[2],
					'count' => $row[3]
				);
			}
		}

		$groups = array();

		foreach($formatted_groups as $id => $name) {
			$groups[] = array(
				'id' => $id,
				// 'parent_id' => $parent_id,
				// 'family_id' => $family_id,
				'name' => $name,
				'raw_name' => $group_data[$id]['name'],
				'value' => $group_data[$id]['value'],
				'object_count' => $group_data[$id]['count']
			);
		}

		$data->assign('groups', $groups);
		break;
}

$data->assign('group_type', $group_type);
$data->assign('return_page', $_SERVER['REQUEST_URI']);

$data->assign('current', $group_type .'_group');
$data->assign('page_title', strtoupper($group_type) .' Group Admin');

$data->assign('phpgacl_version', $gacl_api->get_version());
$data->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->display('phpgacl/group_admin.tpl',$data);
?>
