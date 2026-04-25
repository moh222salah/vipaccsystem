<?php
/**********************************************************************
    VIP Accounting System — Banking Section Hub
    Dashboard for all banking operations.
**********************************************************************/
$path_to_root = "..";
$page_security = 'SA_OPEN';
$GLOBALS['vip_active_section'] = 'banking';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/section_hub.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
page($is_ar ? 'البنوك' : 'Banking');

render_section_hub([
    'title_en'    => 'Banking',
    'title_ar'    => 'البنوك',
    'subtitle_en' => 'Bank transfers, payments, deposits, and reconciliation',
    'subtitle_ar' => 'التحويلات البنكية والمدفوعات والإيداعات والتسويات',
    'color'       => '#0EA5E9',
    'groups'      => [
        [
            'title_en' => 'Transactions',
            'title_ar' => 'المعاملات',
            'items'    => [
                ['en' => 'Bank Transfer',    'ar' => 'تحويل بنكي',    'href' => '/gl/bank_transfer.php',         'access' => 'SA_BANKTRANSFER', 'color' => '#0EA5E9'],
                ['en' => 'Bank Payment',     'ar' => 'دفعة بنكية',    'href' => '/gl/gl_bank.php?NewPayment=Yes','access' => 'SA_PAYMENT',      'color' => '#EF4444'],
                ['en' => 'Bank Deposit',     'ar' => 'إيداع بنكي',    'href' => '/gl/gl_bank.php?NewDeposit=Yes','access' => 'SA_DEPOSIT',      'color' => '#10B981'],
            ],
        ],
        [
            'title_en' => 'Inquiries & Reports',
            'title_ar' => 'الاستعلامات والتقارير',
            'items'    => [
                ['en' => 'Bank Account Inquiry',   'ar' => 'استعلام حساب بنكي',  'href' => '/gl/inquiry/bank_inquiry.php',      'access' => 'SA_GLANALYTIC',  'color' => '#6366F1'],
                ['en' => 'Bank Reconciliation',    'ar' => 'تسوية بنكية',        'href' => '/gl/bank_account_reconcile.php',    'access' => 'SA_BANKREC',     'color' => '#F59E0B'],
            ],
        ],
        [
            'title_en' => 'Setup',
            'title_ar' => 'الإعداد',
            'items'    => [
                ['en' => 'Bank Accounts',    'ar' => 'الحسابات البنكية',  'href' => '/gl/manage/bank_accounts.php',  'access' => 'SA_BANKACCOUNT', 'color' => '#8B5CF6'],
            ],
        ],
    ],
]);

end_page();
