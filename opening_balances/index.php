<?php
/**********************************************************************
    VIP Accounting System — Opening Balances Section Hub
    Dashboard for opening balances and initial setup.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'opening';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'أرصدة أول المدة' : 'Opening Balances');

render_section_hub([
    'title_en'    => 'Opening Balances',
    'title_ar'    => 'أرصدة أول المدة',
    'subtitle_en' => 'View and manage account opening balances',
    'subtitle_ar' => 'عرض وإدارة أرصدة الحسابات الافتتاحية',
    'color'       => '#8B5CF6',
    'groups'      => [
        [
            'title_en' => 'Opening Balances',
            'title_ar' => 'الأرصدة الافتتاحية',
            'items'    => [
                ['en' => 'Opening Balances Tree',    'ar' => 'شجرة أرصدة أول المدة',   'href' => '/gl/gl_opening_balances.php',  'access' => 'SA_GLSETUP',    'color' => '#8B5CF6'],
                ['en' => 'GL Account Inquiry',       'ar' => 'استعلام حساب الأستاذ',    'href' => '/gl/inquiry/gl_account_inquiry.php', 'access' => 'SA_GLANALYTIC', 'color' => '#0EA5E9'],
            ],
        ],
        [
            'title_en' => 'Related Setup',
            'title_ar' => 'الإعداد ذو الصلة',
            'items'    => [
                ['en' => 'Chart of Accounts',    'ar' => 'دليل الحسابات',        'href' => '/gl/gl_accounts.php',            'access' => 'SA_GLACCOUNT',  'color' => '#D4AF37'],
                ['en' => 'Fiscal Years',         'ar' => 'السنوات المالية',       'href' => '/admin/fiscalyears.php',         'access' => 'SA_FISCALYEARS','color' => '#10B981'],
            ],
        ],
    ],
]);

end_page();
