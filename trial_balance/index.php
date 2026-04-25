<?php
/**********************************************************************
    VIP Accounting System — Trial Balance Section Hub
    Dashboard for financial reports and analysis.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'trial';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'ميزان المراجعة' : 'Trial Balance');

render_section_hub([
    'title_en'    => 'Trial Balance',
    'title_ar'    => 'ميزان المراجعة',
    'subtitle_en' => 'Financial reports, balance sheet, and profit & loss analysis',
    'subtitle_ar' => 'التقارير المالية والميزانية العمومية وتحليل الأرباح والخسائر',
    'color'       => '#D4AF37',
    'groups'      => [
        [
            'title_en' => 'Financial Reports',
            'title_ar' => 'التقارير المالية',
            'items'    => [
                ['en' => 'Trial Balance',        'ar' => 'ميزان المراجعة',       'href' => '/reporting/rep111.php',  'access' => 'SA_GLANALYTIC', 'color' => '#D4AF37'],
                ['en' => 'Balance Sheet',        'ar' => 'الميزانية العمومية',    'href' => '/reporting/rep110.php',  'access' => 'SA_GLANALYTIC', 'color' => '#10B981'],
                ['en' => 'Profit & Loss Statement','ar' => 'قائمة الأرباح والخسائر','href' => '/reporting/rep114.php', 'access' => 'SA_GLANALYTIC', 'color' => '#6366F1'],
            ],
        ],
        [
            'title_en' => 'Detailed Reports',
            'title_ar' => 'تقارير تفصيلية',
            'items'    => [
                ['en' => 'GL Account Transactions',  'ar' => 'حركات حساب الأستاذ',   'href' => '/gl/inquiry/gl_account_inquiry.php', 'access' => 'SA_GLANALYTIC', 'color' => '#0EA5E9'],
                ['en' => 'Journal Inquiry',          'ar' => 'استعلام اليومية',       'href' => '/gl/inquiry/journal_inquiry.php',    'access' => 'SA_GLANALYTIC', 'color' => '#8B5CF6'],
                ['en' => 'Tax Report',               'ar' => 'تقرير الضرائب',         'href' => '/reporting/rep709.php',              'access' => 'SA_GLANALYTIC', 'color' => '#F59E0B'],
            ],
        ],
    ],
]);

end_page();
