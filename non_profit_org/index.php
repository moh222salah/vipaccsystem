<?php
/**********************************************************************
    VIP Accounting System — Non-Profit Org Section Hub
    Dashboard for budget and financial planning.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'npo';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'المنظمات غير الربحية' : 'Non-Profit Organization');

render_section_hub([
    'title_en'    => 'Non-Profit Organization',
    'title_ar'    => 'المنظمات غير الربحية',
    'subtitle_en' => 'Budget management, financial planning, and NPO reports',
    'subtitle_ar' => 'إدارة الميزانية والتخطيط المالي وتقارير المنظمات غير الربحية',
    'color'       => '#EC4899',
    'groups'      => [
        [
            'title_en' => 'Budget & Planning',
            'title_ar' => 'الميزانية والتخطيط',
            'items'    => [
                ['en' => 'Budget Dashboard',         'ar' => 'لوحة الميزانية',         'href' => '/gl/budget_trans.php',     'access' => 'SA_GLANALYTIC',  'color' => '#EC4899'],
                ['en' => 'Budget Entry',             'ar' => 'إدخال الميزانية',        'href' => '/gl/gl_budget.php',        'access' => 'SA_BUDGETENTRY', 'color' => '#8B5CF6'],
            ],
        ],
        [
            'title_en' => 'Reports',
            'title_ar' => 'التقارير',
            'items'    => [
                ['en' => 'Profit & Loss Statement',  'ar' => 'قائمة الأرباح والخسائر',  'href' => '/reporting/rep114.php',    'access' => 'SA_GLANALYTIC',  'color' => '#10B981'],
                ['en' => 'Balance Sheet',            'ar' => 'الميزانية العمومية',      'href' => '/reporting/rep110.php',    'access' => 'SA_GLANALYTIC',  'color' => '#D4AF37'],
                ['en' => 'Trial Balance',            'ar' => 'ميزان المراجعة',          'href' => '/reporting/rep111.php',    'access' => 'SA_GLANALYTIC',  'color' => '#0EA5E9'],
            ],
        ],
    ],
]);

end_page();
