<?php
/**********************************************************************
    VIP Accounting System — Cost Centers Section Hub
    Dashboard for cost center management.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'costcenter';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'مراكز التكلفة' : 'Cost Centers');

render_section_hub([
    'title_en'    => 'Cost Centers',
    'title_ar'    => 'مراكز التكلفة',
    'subtitle_en' => 'Manage cost centers, dimensions, and project tracking',
    'subtitle_ar' => 'إدارة مراكز التكلفة والأبعاد وتتبع المشاريع',
    'color'       => '#EC4899',
    'groups'      => [
        [
            'title_en' => 'Views & Management',
            'title_ar' => 'العرض والإدارة',
            'items'    => [
                ['en' => 'Cost Centers Overview',    'ar' => 'نظرة عامة على مراكز التكلفة',  'href' => '/gl/cost_center.php',                   'access' => 'SA_DIMTRANSVIEW', 'color' => '#EC4899'],
                ['en' => 'New Dimension',            'ar' => 'بُعد جديد',                    'href' => '/dimensions/dimension_entry.php',        'access' => 'SA_DIMTRANSVIEW', 'color' => '#8B5CF6'],
                ['en' => 'Search Dimensions',        'ar' => 'بحث في الأبعاد',               'href' => '/dimensions/inquiry/search_dimensions.php','access' => 'SA_DIMTRANSVIEW','color' => '#6366F1'],
            ],
        ],
    ],
]);

end_page();
