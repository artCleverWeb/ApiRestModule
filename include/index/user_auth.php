<?php
/** @const  IBLOCK_ID_SUPPLIES
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $arrSuppleis;

$arrSuppleis = \Kolos\Studio\Helpers\Elements::filterOnlyActive(IBLOCK_ID_SUPPLIES, 3);

if(count($arrSuppleis) == 0){
    include_edit_file_text('/include/index/supplies_not_found.php', false);
}
else{
    include_edit_file_text('/include/index/supplies.php', false);
}
