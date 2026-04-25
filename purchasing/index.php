<?php
/**********************************************************************
    VIP Accounting System — Purchases Section Hub
    Renders the Purchases/AP application module grid.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();
$_SESSION['sel_app'] = 'AP';
$app = &$_SESSION["App"];
$app->selected_application = 'AP';
$app->display();
