<?php
/**********************************************************************
    VIP Accounting System — Sales Section Hub
    Renders the Sales application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'orders';
$app = &$_SESSION["App"];
$app->selected_application = 'orders';
$app->display();
