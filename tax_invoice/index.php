<?php
/**********************************************************************
    VIP Accounting System — Tax Invoice Section Hub
    Dashboard for tax invoice operations.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'taxinv';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'الفاتورة الضريبية' : 'Tax Invoice');

render_section_hub([
    'title_en'    => 'Tax Invoice',
    'title_ar'    => 'الفاتورة الضريبية',
    'subtitle_en' => 'Create and manage tax invoices and credit notes',
    'subtitle_ar' => 'إنشاء وإدارة الفواتير الضريبية وإشعارات الائتمان',
    'color'       => '#EF4444',
    'groups'      => [
        [
            'title_en' => 'Create',
            'title_ar' => 'إنشاء',
            'items'    => [
                ['en' => 'Customer Invoice',         'ar' => 'فاتورة عميل',             'href' => '/sales/customer_invoice.php',                 'access' => 'SA_SALESINVOICE',   'color' => '#EF4444'],
                ['en' => 'Direct Invoice',           'ar' => 'فاتورة مباشرة',           'href' => '/sales/sales_order_entry.php?NewInvoice=Yes',  'access' => 'SA_SALESINVOICE',   'color' => '#F59E0B'],
                ['en' => 'Customer Credit Note',     'ar' => 'إشعار دائن عميل',         'href' => '/sales/credit_note_entry.php',                'access' => 'SA_SALESCREDITINV', 'color' => '#8B5CF6'],
            ],
        ],
        [
            'title_en' => 'Inquiries',
            'title_ar' => 'الاستعلامات',
            'items'    => [
                ['en' => 'Customer Transaction Inquiry', 'ar' => 'استعلام حركات العميل',  'href' => '/sales/inquiry/customer_inquiry.php',  'access' => 'SA_SALESTRANSVIEW', 'color' => '#0EA5E9'],
                ['en' => 'Sales Reports',                'ar' => 'تقارير المبيعات',       'href' => '/reporting/reports_main.php?Class=0',  'access' => 'SA_GLANALYTIC',     'color' => '#10B981'],
            ],
        ],
    ],
]);

end_page();
