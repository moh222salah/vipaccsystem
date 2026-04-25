<?php
/**********************************************************************
    VIP Accounting System — Manufacturing Section Hub
    Renders the Manufacturing application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'manuf';
$app = &$_SESSION["App"];
$app->selected_application = 'manuf';
$app->display();
