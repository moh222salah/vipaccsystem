<?php
/**********************************************************************
    VIP Accounting System — Journal Entries Section Hub
    Dashboard for journal entry operations.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'journal';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'قيود اليومية' : 'Journal Entries');

render_section_hub([
    'title_en'    => 'Journal Entries',
    'title_ar'    => 'قيود اليومية',
    'subtitle_en' => 'Manual journal entries, inquiries, and GL transactions',
    'subtitle_ar' => 'قيود اليومية اليدوية والاستعلامات وحركات الأستاذ العام',
    'color'       => '#1B3F7A',
    'groups'      => [
        [
            'title_en' => 'Transactions',
            'title_ar' => 'المعاملات',
            'items'    => [
                ['en' => 'Journal Entry',        'ar' => 'قيد يومية',            'href' => '/gl/gl_journal.php',       'access' => 'SA_JOURNALENTRY', 'color' => '#1B3F7A'],
                ['en' => 'Budget Entry',         'ar' => 'إدخال الميزانية',      'href' => '/gl/gl_budget.php',        'access' => 'SA_BUDGETENTRY',  'color' => '#10B981'],
            ],
        ],
        [
            'title_en' => 'Inquiries',
            'title_ar' => 'الاستعلامات',
            'items'    => [
                ['en' => 'Journal Inquiry',          'ar' => 'استعلام اليومية',       'href' => '/gl/inquiry/journal_inquiry.php',    'access' => 'SA_GLANALYTIC',   'color' => '#6366F1'],
                ['en' => 'GL Account Inquiry',       'ar' => 'استعلام حساب الأستاذ',  'href' => '/gl/inquiry/gl_account_inquiry.php', 'access' => 'SA_GLANALYTIC',   'color' => '#0EA5E9'],
                ['en' => 'GL Transaction View',      'ar' => 'عرض حركات الأستاذ',     'href' => '/gl/view/gl_trans_view.php',         'access' => 'SA_GLANALYTIC',   'color' => '#8B5CF6'],
            ],
        ],
    ],
]);

end_page();
