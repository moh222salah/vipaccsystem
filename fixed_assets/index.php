<?php
/**********************************************************************
    VIP Accounting System — Fixed Assets Section Hub
    Renders the Fixed Assets application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'assets';
$app = &$_SESSION["App"];
$app->selected_application = 'assets';
$app->display();
