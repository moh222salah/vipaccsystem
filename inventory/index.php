<?php
/**********************************************************************
    VIP Accounting System — Items and Inventory Section Hub
    Renders the Inventory/Stock application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'stock';
$app = &$_SESSION["App"];
$app->selected_application = 'stock';
$app->display();
