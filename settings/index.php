<?php
/**********************************************************************
    VIP Accounting System — Settings Section Hub
    Renders the System Setup/Settings application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'system';
$app = &$_SESSION["App"];
$app->selected_application = 'system';
$app->display();
