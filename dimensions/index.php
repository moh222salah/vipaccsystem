<?php
/**********************************************************************
    VIP Accounting System — Dimensions Section Hub
    Renders the Dimensions/Projects application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'proj';
$app = &$_SESSION["App"];
$app->selected_application = 'proj';
$app->display();
