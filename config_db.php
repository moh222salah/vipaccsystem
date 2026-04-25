<?php
/**********************************************************************
    VIP Acc System — Database Connection Configuration
    FIXED: collation utf8_xx → utf8_unicode_ci (was causing ??? Arabic text)
***********************************************************************/

/*
'host'       - server ip/name (default: localhost)
'port'       - db port (default: 3306, set empty for default)
'dbuser'     - database user
'dbpassword' - database password
'dbname'     - database name
'collation'  - character set (utf8_unicode_ci for Arabic support)
'tbpref'     - table prefix, or '' if not used
*/

$def_coy = 0;

$tb_pref_counter = 1;

$db_connections = array (
  0 =>
  array (
    'name'       => 'VIP Acc System',
    'host'       => 'localhost',
    'port'       => '3306',
    'dbname'     => 'vip-system',
    'collation'  => 'utf8_unicode_ci',   // ✅ FIXED: was 'utf8_xx' (invalid)
    'tbpref'     => '0_',
    'dbuser'     => 'root',
    'dbpassword' => '',
  ),
);
