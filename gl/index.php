<?php
/**********************************************************************
    VIP Accounting System — General Ledger Section Hub
    Renders the General Ledger application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'GL';
$app = &$_SESSION["App"];
$app->selected_application = 'GL';
$app->display();
