<?php
/**********************************************************************
    VIP Accounting System — Chart of Accounts Section Hub
    Dashboard for GL account management.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'coa';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'دليل الحسابات' : 'Chart of Accounts');

render_section_hub([
    'title_en'    => 'Chart of Accounts',
    'title_ar'    => 'دليل الحسابات',
    'subtitle_en' => 'Manage GL accounts, account groups, and classifications',
    'subtitle_ar' => 'إدارة حسابات الأستاذ العام ومجموعات الحسابات والتصنيفات',
    'color'       => '#D4AF37',
    'groups'      => [
        [
            'title_en' => 'Views',
            'title_ar' => 'العرض',
            'items'    => [
                ['en' => 'Chart of Accounts Tree',   'ar' => 'شجرة دليل الحسابات',    'href' => '/gl/gl_accounts.php',                'access' => 'SA_GLACCOUNT',    'color' => '#D4AF37'],
                ['en' => 'GL Account Inquiry',       'ar' => 'استعلام حساب الأستاذ',   'href' => '/gl/inquiry/gl_account_inquiry.php',  'access' => 'SA_GLANALYTIC',   'color' => '#0EA5E9'],
            ],
        ],
        [
            'title_en' => 'Management',
            'title_ar' => 'الإدارة',
            'items'    => [
                ['en' => 'GL Accounts',          'ar' => 'حسابات الأستاذ العام',   'href' => '/gl/manage/gl_accounts.php',         'access' => 'SA_GLACCOUNT',      'color' => '#10B981'],
                ['en' => 'Account Groups',       'ar' => 'مجموعات الحسابات',       'href' => '/gl/manage/gl_account_types.php',    'access' => 'SA_GLACCOUNTGROUP', 'color' => '#8B5CF6'],
                ['en' => 'Account Classes',      'ar' => 'تصنيفات الحسابات',       'href' => '/gl/manage/gl_account_classes.php',  'access' => 'SA_GLACCOUNTCLASS', 'color' => '#6366F1'],
                ['en' => 'GL Account Tags',      'ar' => 'علامات الحسابات',        'href' => '/admin/tags.php?type=account',       'access' => 'SA_GLACCOUNTTAGS',  'color' => '#F59E0B'],
            ],
        ],
    ],
]);

end_page();
