<?php
$list_switch = apply_filters('kboard_skin_contact_form_list_switch', 'editor');

if($list_switch == 'editor'){
	echo $boardBuilder->builderEditor();
}
else if($list_switch == 'list'){
	include 'admin-list.php';
}