<?php
$document_switch = apply_filters('kboard_skin_contact_form_document_switch', 'editor');

if($document_switch == 'editor'){
	echo $boardBuilder->builderEditor();
}
else if($document_switch == 'document'){
	include 'admin-document.php';
}