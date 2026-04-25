<?php
/**
 * VIP Acc System — Executive Dashboard  v4.0
 * Path  : vipaccsystem/themes/default/dashboard.inc
 * Compat: FA 2.4.x | LTR + RTL bilingual (Arabic/English)
 * Fix   : SET NAMES utf8 → Arabic text now displays correctly
 * Fix   : All data fetched live from DB — no hardcoded/demo values
 */

/* ═══════════════════════════════════════════════════
   MAIN ENTRY POINT
═══════════════════════════════════════════════════ */
function dashboard($sel_app)
{
    @ini_set('display_errors', '0');
    @error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

    /* ── CRITICAL FIX: Set DB connection charset to UTF-8 for Arabic support ── */
    @db_query("SET NAMES 'utf8'");
    @db_query("SET CHARACTER SET utf8");
    @db_query("SET collation_connection = 'utf8_unicode_ci'");

    if (is_object($sel_app) &&
        !$_SESSION['wa_current_user']->check_application_access($sel_app))
        return;

    $dir = isset($_SESSION['language']->dir) ? $_SESSION['language']->dir : 'ltr';
    $rtl = ($dir === 'rtl');

    echo _vd_styles($rtl);
    echo "<script src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'></script>\n";

    $app_js = addslashes($sel_app);
    echo "<script>
    function chart_update(el, id) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var t = document.createElement('div');
                t.innerHTML = xhr.responseText;
                var f = t.querySelector('#' + id);
                if (f) document.getElementById(id).innerHTML = f.innerHTML;
            }
        };
        xhr.open('POST', location.pathname, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.send('id=' + id + '&' + el.id + '=' + el.value + '&sel_app=$app_js');
    }
    </script>\n";

    if      ($sel_app === 'orders') display_customer_topten($rtl);
    elseif  ($sel_app === 'AP')     display_supplier_topten($rtl);
    elseif  ($sel_app === 'stock')  display_stock_topten(0, $rtl);
    elseif  ($sel_app === 'manuf')  display_stock_topten(1, $rtl);
    elseif  ($sel_app === 'assets') display_stock_topten(2, $rtl);
    elseif  ($sel_app === 'proj')   display_dimension_topten($rtl);
    elseif  ($sel_app === 'GL')     display_gl_info($rtl);
    else                            display_all($rtl);
}

/* ═══════════════════════════════════════════════════
   MAIN DISPLAY FUNCTIONS
═══════════════════════════════════════════════════ */

function display_all($rtl = false)
{
    $today = calc_today();
    $dir   = $rtl ? "dir='rtl'" : "";

    /* ── Welcome header ── */
    $user = isset($_SESSION['wa_current_user']->name) ? $_SESSION['wa_current_user']->name : 'Admin';
    echo "<div class='vd-welcome' $dir>\n";
    echo "  <div>\n";
    echo "    <h2 class='vd-welcome-title'>" . sprintf(_('Welcome %s'), htmlspecialchars($user)) . "</h2>\n";
    echo "    <p class='vd-welcome-sub'>" . _('Here is your business summary for today') . "</p>\n";
    echo "  </div>\n";
    echo "  <div class='vd-welcome-actions'>\n";
    echo "    <a href='javascript:location.reload()' class='vd-btn-outline'>";
    echo "      <svg width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>";
    echo "        <polyline points='23 4 23 10 17 10'/><path d='M20.49 15a9 9 0 1 1-2.12-9.36L23 10'/>";
    echo "      </svg> " . _('Refresh') . "\n";
    echo "    </a>\n";
    echo "  </div>\n";
    echo "</div>\n";

    /* ── KPI Cards (all from live DB) ── */
    _vd_kpi_cards($today, $rtl);

    /* ── Quick Actions ── */
    _vd_quick_actions($rtl);

    /* ── Charts Row ── */
    echo "<div class='vd-charts-row'>\n";
    _vd_monthly_revenue_chart($today);
    _vd_sales_distribution_chart($today);
    echo "</div>\n";

    /* ── GL Journal Entries latest 10 ── */
    _vd_gl_journal_table($today, $rtl);

    /* ── Recent Transactions ── */
    _vd_recent_transactions_table($today, $rtl);

    /* ── Bottom row: Top Customers + Top Suppliers ── */
    echo "<div class='vd-tables-row'>\n";
    _vd_customer_top_table($today, 5, $rtl);
    _vd_supplier_top_table($today, 5, $rtl);
    echo "</div>\n";
}

function display_customer_topten($rtl = false)
{
    $today = calc_today();
    _vd_section_header(_('Sales Analysis'), $rtl);
    _vd_kpi_strip([
        [_('Customers'),        get_num_customers(),      '#164E8F', 'people'],
        [_('Branches'),         get_num_branches(),       '#10B981', 'branch'],
        [_('Salesmen'),         get_num_salesmen(),       '#D4AF37', 'sales'],
        [_('Overdue Invoices'), get_num_overdue_sales(),  '#EF4444', 'alert'],
    ]);
    $cust  = _vd_customer_top_data($today, 10);
    $aging = _vd_customer_aging_data($today);
    echo "<div class='vd-charts-row'>\n";
    _vd_bar_chart('cust_top',   _('Top 10 Customers – Fiscal Year'),
        $cust['names'],  [['label' => _('Sales'),   'data' => $cust['totals'],  'color' => '#164E8F']], 'horizontal');
    _vd_bar_chart('cust_aging', _('Customers Aged Analysis') . " ($today)",
        $aging['labels'], [['label' => _('Balance'), 'data' => $aging['values'], 'color' => '#D4AF37']], 'vertical');
    echo "</div>\n";
    echo "<div class='vd-totals-row'>\n";
    _vd_total_badge(_('Sales Total'), number_format2($cust['total']),  '#164E8F');
    _vd_total_badge(_('Aging Total'), number_format2($aging['total']), '#D4AF37');
    echo "</div>\n";
    echo "<div class='vd-tables-row'>\n";
    _vd_customer_top_table($today, 10, $rtl);
    _vd_overdue_invoices_table($today, $rtl);
    echo "</div>\n";
    _vd_recurrent_invoices_table($today, $rtl);
}

function display_supplier_topten($rtl = false)
{
    $today = calc_today();
    _vd_section_header(_('Purchase Analysis'), $rtl);
    _vd_kpi_strip([
        [_('Suppliers'),        get_num_suppliers(),      '#164E8F', 'people'],
        [_('New Orders'),       get_new_purch_orders(),   '#10B981', 'orders'],
        [_('Invoices'),         get_num_supp_invoices(),  '#D4AF37', 'invoice'],
        [_('Overdue Invoices'), get_num_overdue_purch(),  '#EF4444', 'alert'],
    ]);
    $supp  = _vd_supplier_top_data($today, 10);
    $aging = _vd_supplier_aging_data($today);
    echo "<div class='vd-charts-row'>\n";
    _vd_bar_chart('supp_top',   _('Top 10 Suppliers – Fiscal Year'),
        $supp['names'],  [['label' => _('Purchases'), 'data' => $supp['totals'],  'color' => '#D4AF37']], 'horizontal');
    _vd_bar_chart('supp_aging', _('Suppliers Aged Analysis') . " ($today)",
        $aging['labels'], [['label' => _('Balance'),  'data' => $aging['values'], 'color' => '#164E8F']], 'vertical');
    echo "</div>\n";
    echo "<div class='vd-totals-row'>\n";
    _vd_total_badge(_('Purchases Total'), number_format2($supp['total']),  '#164E8F');
    _vd_total_badge(_('Aging Total'),     number_format2($aging['total']), '#D4AF37');
    echo "</div>\n";
    echo "<div class='vd-tables-row'>\n";
    _vd_supplier_top_table($today, 10, $rtl);
    _vd_unpaid_supplier_table($today, $rtl);
    echo "</div>\n";
}

function display_stock_topten($type = 0, $rtl = false)
{
    $today = calc_today();
    if ($type == 2) {
        _vd_section_header(_('Fixed Assets Analysis'), $rtl);
        $kpis = [
            [_('Fixed Assets'),        get_num_fixed_assets(),    '#164E8F', 'box'],
            [_('Locations'),           get_num_fixed_locations(), '#10B981', 'loc'],
            [_('Categories'),          get_num_fixed_categories(),'#D4AF37', 'cat'],
            [_('Fixed Asset Classes'), get_num_fixed_classes(),   '#8B5CF6', 'cclass'],
        ];
        $chart_title = _('Top 5 Fixed Assets');
    } elseif ($type == 1) {
        _vd_section_header(_('Manufacturing Analysis'), $rtl);
        $kpis = [
            [_('Assembled Items'),    get_num_assembled(),    '#164E8F', 'box'],
            [_('Manufactured Items'), get_num_manufactured(), '#10B981', 'mfg'],
            [_('Work Centres'),       get_num_workcentres(),  '#D4AF37', 'wc'],
            [_('Open Workorders'),    get_open_workorders(),  '#EF4444', 'alert'],
        ];
        $chart_title = _('Top 5 Manufactured Items – Fiscal Year');
    } else {
        _vd_section_header(_('Inventory Analysis'), $rtl);
        $kpis = [
            [_('Items'),               get_num_items(),    '#164E8F', 'box'],
            [_('Locations'),           get_num_locations(),'#10B981', 'loc'],
            [_('Kits'),                get_num_kits(),     '#D4AF37', 'kit'],
            [_('Below Reorder Level'), get_num_reorder(),  '#EF4444', 'alert'],
        ];
        $chart_title = _('Top 5 Sold Items – Fiscal Year');
    }
    _vd_kpi_strip($kpis);
    $stock = _vd_stock_top_data($today, 5, $type);
    echo "<div class='vd-charts-row'>\n";
    $ds = $type == 0
        ? [['label' => _('Sales'),   'data' => $stock['totals'],  'color' => '#164E8F'],
           ['label' => _('Costs'),   'data' => $stock['costs'],   'color' => '#D4AF37'],
           ['label' => _('Results'), 'data' => $stock['results'], 'color' => '#10B981']]
        : [['label' => _('Amount'),  'data' => $stock['totals'],  'color' => '#164E8F']];
    _vd_bar_chart('stock_top', $chart_title, $stock['names'], $ds, 'horizontal');
    echo "</div>\n";
    echo "<div class='vd-totals-row'>\n";
    _vd_total_badge(_('Total Sales'), number_format2($stock['total_sales']), '#164E8F');
    if ($type == 0) {
        _vd_total_badge(_('Total Costs'),   number_format2($stock['total_costs']),   '#D4AF37');
        _vd_total_badge(_('Total Results'), number_format2($stock['total_results']), '#10B981');
    }
    echo "</div>\n";
    echo "<div class='vd-tables-row'>\n";
    _vd_stock_top_table($today, 5, $type, $rtl);
    _vd_stock_reorder_table($today, $type, $rtl);
    echo "</div>\n";
}

function display_dimension_topten($rtl = false)
{
    $today = calc_today();
    _vd_section_header(_('Dimensions Analysis'), $rtl);
    $res = get_dim_total();
    _vd_kpi_strip([
        [_('Dimensions'),              get_num_dimensions(), '#164E8F', 'dim'],
        [_('Type 2 Dimensions'),       get_dim_type2(),      '#D4AF37', 'dim2'],
        [_('Dimensions Total Balance'),number_format2($res[0]), $res[0] < 0 ? '#EF4444' : '#164E8F', 'balance'],
        [_('Dimensions Total Result'), number_format2($res[1]), $res[1] < 0 ? '#EF4444' : '#10B981', 'result'],
    ]);
    $dim = _vd_dimension_top_data($today, 5);
    echo "<div class='vd-charts-row'>\n";
    _vd_bar_chart('dim_top', sprintf(_('Top %s Dimensions – Fiscal Year'), 5), $dim['names'], [
        ['label' => _('Balance'), 'data' => $dim['balances'], 'color' => '#164E8F'],
        ['label' => _('Result'),  'data' => $dim['results'],  'color' => '#D4AF37'],
    ], 'horizontal');
    echo "</div>\n";
    echo "<div class='vd-tables-row'>\n";
    _vd_dimension_top_table($today, 10, $rtl);
    echo "</div>\n";
}

function display_gl_info($rtl = false)
{
    $today = calc_today();
    _vd_section_header(_('Banking and General Ledger'), $rtl);
    $recv = _vd_customer_balance($today);
    $payb = -_vd_supplier_balance($today);
    _vd_kpi_strip([
        [_('Receivables'),      number_format2($recv),                              '#164E8F', 'recv'],
        [_('Payables'),         number_format2($payb),                              '#EF4444', 'payb'],
        [_('Todays Deposits'),  number_format2(_vd_get_today_deposits($today)),     '#10B981', 'dep'],
        [_('Todays Payments'),  number_format2(abs(_vd_get_today_payments($today))),'#D4AF37', 'pmt'],
    ]);
    echo "<div class='vd-charts-row'>\n";
    _vd_gl_class_chart($today);
    _vd_gl_month_chart($today);
    echo "</div>\n";
    echo "<div class='vd-charts-row'>\n";
    _vd_gl_week_chart($today);
    _vd_cash_flow_chart($today);
    echo "</div>\n";
    _vd_gl_journal_table($today, $rtl);
    echo "<div class='vd-tables-row'>\n";
    _vd_bank_balance_table($today, $rtl);
    echo "</div>\n";
}

/* ═══════════════════════════════════════════════════
   KPI CARDS — all values from live DB
═══════════════════════════════════════════════════ */

function _vd_kpi_cards($today, $rtl = false)
{
    $t1        = date2sql($today);
    $yesterday = date2sql(add_days($today, -1));

    /* Today Revenue — from sales invoices */
    $r = db_query("SELECT SUM((ov_amount+ov_discount)*rate) AS rev
                   FROM " . TB_PREF . "debtor_trans
                   WHERE type=" . ST_SALESINVOICE . " AND tran_date='$t1'");
    $rev_today = (float)($r && ($row = db_fetch($r)) ? $row['rev'] : 0);

    $r2 = db_query("SELECT SUM((ov_amount+ov_discount)*rate) AS rev
                    FROM " . TB_PREF . "debtor_trans
                    WHERE type=" . ST_SALESINVOICE . " AND tran_date='$yesterday'");
    $rev_yest = (float)($r2 && ($row2 = db_fetch($r2)) ? $row2['rev'] : 0);
    $rev_chg  = $rev_yest > 0 ? round((($rev_today - $rev_yest) / $rev_yest) * 100, 1) : null;

    /* AR Balance */
    $ar = _vd_customer_balance($today);

    /* Low Stock */
    $low = get_num_reorder();

    /* Pending purchase orders */
    $r3 = db_query("SELECT count(*) AS c FROM " . TB_PREF . "purch_orders po
                    JOIN " . TB_PREF . "purch_order_details pd ON po.order_no=pd.order_no
                    WHERE quantity_received < quantity_ordered");
    $pending_orders = (int)($r3 && ($row3 = db_fetch($r3)) ? $row3['c'] : 0);

    /* Today deposits from bank accounts */
    $dep = _vd_get_today_deposits($today);

    /* Stock movements today */
    $r4 = db_query("SELECT count(*) AS c FROM " . TB_PREF . "stock_moves WHERE tran_date='$t1'");
    $stock_moves = (int)($r4 && ($row4 = db_fetch($r4)) ? $row4['c'] : 0);

    /* Month-to-date revenue */
    $month_start = date('Y-m-01', strtotime($t1));
    $r5 = db_query("SELECT SUM((ov_amount+ov_discount)*rate) AS rev FROM " . TB_PREF . "debtor_trans
                    WHERE type=" . ST_SALESINVOICE . " AND tran_date>='$month_start' AND tran_date<='$t1'");
    $rev_month = (float)($r5 && ($row5 = db_fetch($r5)) ? $row5['rev'] : 0);

    $cur = get_company_pref('curr_default');

    $cards = [
        [
            'label'  => _('Today Revenue'),
            'value'  => ($cur ? $cur . ' ' : '') . number_format($rev_today, 0),
            'change' => $rev_chg,
            'icon'   => 'revenue', 'color' => '#164E8F',
        ],
        [
            'label'      => _('Accounts Receivable'),
            'value'      => ($cur ? $cur . ' ' : '') . number_format($ar, 0),
            'badge'      => _('Outstanding'), 'badge_color' => '#D97706',
            'icon'       => 'ar', 'color' => '#D4AF37',
        ],
        [
            'label'      => _('Low Stock Items'),
            'value'      => (string)$low,
            'badge'      => _('Alert'), 'badge_color' => '#EF4444',
            'icon'       => 'stock', 'color' => '#EF4444',
        ],
        [
            'label'  => _('Pending Orders'),
            'value'  => (string)$pending_orders,
            'icon'   => 'orders', 'color' => '#10B981',
            'change' => null,
        ],
        [
            'label'  => _('Today Payments'),
            'value'  => ($cur ? $cur . ' ' : '') . number_format($dep, 0),
            'icon'   => 'payments', 'color' => '#8B5CF6',
            'change' => null,
        ],
        [
            'label'      => _('Stock Movements'),
            'value'      => (string)$stock_moves,
            'badge'      => _('Today'), 'badge_color' => '#164E8F',
            'icon'       => 'moves', 'color' => '#0EA5E9',
        ],
    ];

    $icons = [
        'revenue'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'ar'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
        'stock'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        'orders'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
        'payments' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
        'moves'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>',
    ];

    $dir = $rtl ? " dir='rtl'" : "";
    echo "<div class='vd-kpi-grid'$dir>\n";
    $i = 0;
    foreach ($cards as $card) {
        $icon     = $icons[$card['icon']] ?? '';
        $delay    = round($i * 0.07, 2);
        $chg_html = '';
        if (isset($card['change']) && $card['change'] !== null) {
            $pos      = $card['change'] >= 0;
            $arr      = $pos ? '↑' : '↓';
            $cls      = $pos ? 'vd-chg-pos' : 'vd-chg-neg';
            $chg_html = "<span class='$cls'>$arr " . abs($card['change']) . "%</span>";
        } elseif (isset($card['badge'])) {
            $chg_html = "<span class='vd-badge-sm' style='background:" . htmlspecialchars($card['badge_color']) . "22;color:" . htmlspecialchars($card['badge_color']) . "'>" . htmlspecialchars($card['badge']) . "</span>";
        }
        echo "<div class='vd-kpi-card' style='--kc:" . htmlspecialchars($card['color']) . ";animation-delay:{$delay}s'>\n";
        echo "  <div class='vd-kpi-top'>\n";
        echo "    <div class='vd-kpi-icon'>$icon</div>\n";
        echo "    $chg_html\n";
        echo "  </div>\n";
        echo "  <div class='vd-kpi-value'>" . htmlspecialchars($card['value']) . "</div>\n";
        echo "  <div class='vd-kpi-label'>" . htmlspecialchars($card['label']) . "</div>\n";
        echo "</div>\n";
        $i++;
    }
    echo "</div>\n";
}

/* ═══════════════════════════════════════════════════
   QUICK ACTIONS
═══════════════════════════════════════════════════ */

function _vd_quick_actions($rtl = false)
{
    $actions = [
        [_('Sales Invoice'),  '../sales/customer_invoice.php',       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
        [_('Receipt'),        '../gl/add_payment.php?PaymentType=2', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>'],
        [_('New Customer'),   '../sales/manage/customers.php',       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>'],
        [_('Stock Entry'),    '../inventory/inv_transfer.php',       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>'],
        [_('Journal Entry'),  '../gl/gl_journal.php',                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>'],
        [_('Reports'),        '../reporting/reports_main.php',       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>'],
        [_('Purchase Inv.'),  '../purchasing/supplier_invoice.php',  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'],
        [_('Currencies'),     '../gl/manage/currencies.php',         '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'],
    ];

    $dir = $rtl ? " dir='rtl'" : "";
    echo "<div class='vd-section'$dir>\n";
    echo "  <h3 class='vd-section-title'>" . _('Quick Actions') . "</h3>\n";
    echo "  <div class='vd-quick-grid'>\n";
    foreach ($actions as $i => $a) {
        $delay = round($i * 0.05, 2);
        echo "  <a href='" . htmlspecialchars($a[1]) . "' class='vd-qa-btn' style='animation-delay:{$delay}s'>\n";
        echo "    <span class='vd-qa-icon'>" . $a[2] . "</span>\n";
        echo "    <span class='vd-qa-label'>" . htmlspecialchars($a[0]) . "</span>\n";
        echo "  </a>\n";
    }
    echo "  </div>\n";
    echo "</div>\n";
}

/* ═══════════════════════════════════════════════════
   GL JOURNAL TABLE (latest 10 entries from DB)
═══════════════════════════════════════════════════ */

function _vd_gl_journal_table($today, $rtl = false)
{
    $t1  = date2sql($today);
    $sql = "SELECT g.tran_date, g.type, g.type_no, g.account, a.account_name, g.memo_,
                   SUM(IF(g.amount>0, g.amount, 0)) AS debit,
                   SUM(IF(g.amount<0, ABS(g.amount), 0)) AS credit
            FROM " . TB_PREF . "gl_trans g
            JOIN " . TB_PREF . "chart_master a ON g.account=a.account_code
            WHERE g.tran_date<='$t1'
            GROUP BY g.type, g.type_no, g.account
            ORDER BY g.tran_date DESC, g.type_no DESC
            LIMIT 10";
    $r = db_query($sql);

    $type_names = [
        ST_SALESINVOICE  => _('Sales Invoice'),
        ST_CUSTCREDIT    => _('Credit Note'),
        ST_CUSTPAYMENT   => _('Customer Payment'),
        ST_SUPPINVOICE   => _('Purchase Invoice'),
        ST_SUPPCREDIT    => _('Supplier Credit'),
        ST_BANKTRANSFER  => _('Bank Transfer'),
        ST_JOURNAL       => _('Journal Entry'),
        ST_BANKDEPOSIT   => _('Bank Deposit'),
    ];

    $rows = '';
    while ($row = db_fetch($r)) {
        $type_label = $type_names[(int)$row['type']] ?? _('Entry') . ' ' . $row['type'];
        $memo       = htmlspecialchars(mb_substr($row['memo_'] ?? '', 0, 35, 'UTF-8'));
        $dr         = number_format((float)$row['debit'], 2);
        $cr         = number_format((float)$row['credit'], 2);
        $acc_name   = htmlspecialchars(mb_substr($row['account_name'], 0, 30, 'UTF-8'));
        $rows .= "<tr>"
            . "<td>" . sql2date($row['tran_date']) . "</td>"
            . "<td><span class='vd-type-badge'>" . htmlspecialchars($type_label) . "</span></td>"
            . "<td>" . htmlspecialchars($row['account']) . " – $acc_name</td>"
            . "<td class='vd-right vd-amount'>$dr</td>"
            . "<td class='vd-right vd-credit'>$cr</td>"
            . "<td class='vd-muted'>$memo</td>"
            . "</tr>\n";
    }

    $view_all = "<a href='../gl/inquiry/gl_inquiry.php' class='vd-view-all'>" . _('View All') . " →</a>";
    _vd_table(
        _('Latest Journal Entries') . ' (' . _('General Ledger') . ')',
        [_('Date'), _('Type'), _('Account'), _('Debit'), _('Credit'), _('Memo')],
        $rows ?: "<tr><td colspan='6' class='vd-empty'>" . _('No entries') . "</td></tr>",
        $view_all,
        $rtl
    );
}

/* ═══════════════════════════════════════════════════
   RECENT TRANSACTIONS (Sales + Purchases from DB)
═══════════════════════════════════════════════════ */

function _vd_recent_transactions_table($today, $rtl = false)
{
    $t1  = date2sql($today);
    $sql = "(SELECT dt.trans_no, dt.reference, dt.tran_date, dt.type,
                    d.name AS party, 'sales' AS src,
                    (dt.ov_amount+dt.ov_discount) AS amount,
                    dt.alloc, dt.due_date
             FROM " . TB_PREF . "debtor_trans dt
             JOIN " . TB_PREF . "debtors_master d ON dt.debtor_no=d.debtor_no
             WHERE dt.type IN(" . ST_SALESINVOICE . "," . ST_CUSTCREDIT . "," . ST_CUSTPAYMENT . ")
               AND dt.tran_date<='$t1'
             ORDER BY dt.tran_date DESC, dt.trans_no DESC LIMIT 5)
            UNION ALL
            (SELECT st.trans_no, st.reference, st.tran_date, st.type,
                    s.supp_name AS party, 'purchase' AS src,
                    (st.ov_amount+st.ov_discount) AS amount,
                    st.alloc, st.due_date
             FROM " . TB_PREF . "supp_trans st
             JOIN " . TB_PREF . "suppliers s ON st.supplier_id=s.supplier_id
             WHERE st.type IN(" . ST_SUPPINVOICE . "," . ST_SUPPCREDIT . "," . ST_BANKDEPOSIT . ")
               AND st.tran_date<='$t1'
             ORDER BY st.tran_date DESC, st.trans_no DESC LIMIT 5)
            ORDER BY tran_date DESC, trans_no DESC LIMIT 10";
    $r = db_query($sql);

    $today_sql = date2sql($today);
    $type_map  = [
        ST_SALESINVOICE => _('Sales Invoice'),
        ST_CUSTCREDIT   => _('Credit Note'),
        ST_CUSTPAYMENT  => _('Customer Receipt'),
        ST_SUPPINVOICE  => _('Purchase Invoice'),
        ST_SUPPCREDIT   => _('Supplier Credit'),
        ST_BANKDEPOSIT  => _('Bank Deposit'),
        ST_BANKTRANSFER => _('Bank Transfer'),
        ST_JOURNAL      => _('Journal Entry'),
    ];

    $rows = '';
    $cur  = get_company_pref('curr_default');
    while ($row = db_fetch($r)) {
        $amt   = (float)$row['amount'];
        $alloc = (float)$row['alloc'];
        $due   = $row['due_date'];
        if ($alloc >= $amt - FLOAT_COMP_DELTA) {
            $status = _('Paid');    $st_cls = 'vd-st-paid';
        } elseif ($due && $due < $today_sql && $alloc < $amt - FLOAT_COMP_DELTA) {
            $status = _('Overdue'); $st_cls = 'vd-st-overdue';
        } else {
            $status = _('Pending'); $st_cls = 'vd-st-pending';
        }
        $type_lbl = $type_map[(int)$row['type']] ?? _('Entry');
        $party    = htmlspecialchars(mb_substr($row['party'], 0, 30, 'UTF-8'));
        $rows .= "<tr>"
            . "<td><span class='vd-ref'>" . htmlspecialchars($row['reference']) . "</span></td>"
            . "<td>$party</td>"
            . "<td><span class='vd-type-badge'>" . htmlspecialchars($type_lbl) . "</span></td>"
            . "<td class='vd-right vd-amount'>" . ($cur ? $cur . ' ' : '') . number_format(abs($amt), 0) . "</td>"
            . "<td>" . sql2date($row['tran_date']) . "</td>"
            . "<td><span class='vd-status $st_cls'>" . htmlspecialchars($status) . "</span></td>"
            . "</tr>\n";
    }

    $view_all = "<a href='../sales/inquiry/customer_inquiry.php' class='vd-view-all'>" . _('View All') . " →</a>";
    _vd_table(
        _('Recent Transactions'),
        [_('Number'), _('Customer / Supplier'), _('Type'), _('Amount'), _('Date'), _('Status')],
        $rows ?: "<tr><td colspan='6' class='vd-empty'>" . _('No transactions') . "</td></tr>",
        $view_all,
        $rtl
    );
}

/* ═══════════════════════════════════════════════════
   MONTHLY REVENUE CHART (from GL transactions)
═══════════════════════════════════════════════════ */

function _vd_monthly_revenue_chart($today)
{
    global $SysPrefs;
    $months = isset($_POST['per_rev']) ? (int)$_POST['per_rev'] : 6;
    $begin  = date2sql(begin_fiscalyear());
    $t1     = date2sql($today);
    $sep    = $SysPrefs->dateseps[user_date_sep()];

    $sql = "SELECT month_name, sales, costs FROM (
                SELECT DATE_FORMAT(tran_date,'%Y{$sep}%m') AS month_name,
                       SUM(IF(c.ctype=4, amount*-1, 0)) AS sales,
                       SUM(IF(c.ctype=6, amount, 0)) AS costs
                FROM " . TB_PREF . "gl_trans
                JOIN " . TB_PREF . "chart_master a ON account=a.account_code
                JOIN " . TB_PREF . "chart_types  t ON a.account_type=t.id
                JOIN " . TB_PREF . "chart_class  c ON t.class_id=c.cid
                WHERE (c.ctype=4 OR c.ctype=6)
                  AND tran_date>='$begin' AND tran_date<='$t1'
                GROUP BY month_name
                ORDER BY month_name DESC LIMIT 0,$months
            ) b
            GROUP BY month_name ORDER BY month_name ASC";
    $rr = db_query($sql);
    $mn = $s = $c = [];
    while ($row = db_fetch($rr)) {
        $mn[] = $row['month_name'];
        $s[]  = round((float)$row['sales']);
        $c[]  = round((float)$row['costs']);
    }

    echo "<div class='vd-chart-card' id='vd_rev'>\n";
    echo "  <div class='vd-chart-head'>\n";
    echo "    <span class='vd-chart-title'>" . _('Monthly Revenue') . "</span>\n";
    echo "    <div style='display:flex;gap:8px;align-items:center'>\n";
    echo "      <select id='per_rev' onchange='chart_update(this,\"vd_rev\")' class='vd-select'>\n";
    foreach ([3, 4, 5, 6, 7, 8] as $n)
        echo "        <option value='$n'" . ($months == $n ? ' selected' : '') . ">$n " . _('Months') . "</option>\n";
    echo "      </select>\n";
    echo "    </div>\n";
    echo "  </div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_rev'></canvas></div>\n</div>\n";

    $lj = json_encode($mn, JSON_UNESCAPED_UNICODE);
    $sj = json_encode($s);
    $cj = json_encode($c);
    $ls = addslashes(_('Revenue'));
    $lc = addslashes(_('Expenses'));
    echo "<script>
(function(){
  var el=document.getElementById('chart_rev'); if(!el) return;
  new Chart(el, {type:'line',
    data:{labels:$lj, datasets:[
      {label:'$ls', data:$sj, borderColor:'#164E8F', backgroundColor:'rgba(22,78,143,0.08)', fill:true, borderWidth:2.5, pointRadius:4, pointBackgroundColor:'#164E8F', tension:0.4},
      {label:'$lc', data:$cj, borderColor:'#D4AF37', backgroundColor:'rgba(212,175,55,0.06)', fill:true, borderWidth:2, pointRadius:3, pointBackgroundColor:'#D4AF37', tension:0.4}
    ]},
    options:{responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'top', labels:{font:{family:\"'DM Sans',sans-serif\", size:12}, color:'#4A5568', boxWidth:12, usePointStyle:true}},
               tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:14}},
      scales:{x:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096'}},
              y:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096', callback:function(v){return v>=1000?(v/1000).toFixed(0)+'K':v;}}}}}
  });
})();
</script>\n";
}

/* ═══════════════════════════════════════════════════
   SALES DISTRIBUTION CHART (by category from DB)
═══════════════════════════════════════════════════ */

function _vd_sales_distribution_chart($today)
{
    $begin = date2sql(begin_fiscalyear());
    $t1    = date2sql($today);
    $sql   = "SELECT sc.description, SUM((dtd.unit_price*dtd.quantity)*d.rate) AS total
              FROM " . TB_PREF . "debtor_trans_details dtd
              JOIN " . TB_PREF . "stock_master  sm ON dtd.stock_id=sm.stock_id
              JOIN " . TB_PREF . "stock_category sc ON sm.category_id=sc.category_id
              JOIN " . TB_PREF . "debtor_trans   d  ON dtd.debtor_trans_type=d.type AND dtd.debtor_trans_no=d.trans_no
              WHERE (d.type=" . ST_SALESINVOICE . " OR d.type=" . ST_CUSTCREDIT . ")
                AND d.tran_date>='$begin' AND d.tran_date<='$t1'
              GROUP BY sc.category_id ORDER BY total DESC LIMIT 6";
    $rr    = db_query($sql);
    $names = [];
    $vals  = [];
    while ($row = db_fetch($rr)) {
        $names[] = htmlspecialchars_decode($row['description']);
        $vals[]  = round((float)$row['total']);
    }
    if (empty($vals)) { $names = [_('No Data')]; $vals = [1]; }

    echo "<div class='vd-chart-card'>\n";
    echo "  <div class='vd-chart-head'>\n";
    echo "    <span class='vd-chart-title'>" . _('Sales Distribution') . "</span>\n";
    echo "    <span class='vd-chip'>" . _('This Month') . "</span>\n";
    echo "  </div>\n";
    echo "  <div class='vd-chart-body vd-chart-body--donut'><canvas id='chart_dist'></canvas></div>\n</div>\n";

    $lj = json_encode($names, JSON_UNESCAPED_UNICODE);
    $dj = json_encode($vals);
    echo "<script>
(function(){
  var el=document.getElementById('chart_dist'); if(!el) return;
  new Chart(el, {type:'doughnut',
    data:{labels:$lj, datasets:[{data:$dj,
      backgroundColor:['#164E8F','#10B981','#D4AF37','#F59E0B','#8B5CF6','#3B82F6'],
      borderWidth:3, borderColor:'#fff', hoverOffset:10}]},
    options:{responsive:true, maintainAspectRatio:false, cutout:'65%',
      plugins:{legend:{position:'bottom', labels:{font:{family:\"'DM Sans',sans-serif\", size:11}, color:'#718096', boxWidth:10, padding:14, usePointStyle:true}},
               tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:12}}}
  });
})();
</script>\n";
}

/* ═══════════════════════════════════════════════════
   GL CHARTS
═══════════════════════════════════════════════════ */

function _vd_gl_class_chart($today)
{
    $begin = date2sql(begin_fiscalyear());
    $t1    = date2sql($today);
    $sql   = "SELECT SUM(IF(c.ctype>3,-amount,amount)) AS total, c.class_name
              FROM " . TB_PREF . "gl_trans
              JOIN " . TB_PREF . "chart_master a ON account=a.account_code
              JOIN " . TB_PREF . "chart_types  t ON a.account_type=t.id
              JOIN " . TB_PREF . "chart_class  c ON t.class_id=c.cid
              WHERE IF(c.ctype>3, tran_date>='$begin', tran_date>='0000-00-00') AND tran_date<='$t1'
              GROUP BY c.cid ORDER BY c.cid";
    $rr    = db_query($sql);
    $names = [];
    $vals  = [];
    while ($row = db_fetch($rr)) { $names[] = htmlspecialchars_decode($row['class_name']); $vals[] = round((float)$row['total']); }

    echo "<div class='vd-chart-card'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>" . _('Class Balances') . "</span></div>\n";
    echo "  <div class='vd-chart-body vd-chart-body--donut'><canvas id='chart_gl_class'></canvas></div>\n</div>\n";

    $lj = json_encode($names, JSON_UNESCAPED_UNICODE);
    $dj = json_encode($vals);
    echo "<script>
(function(){ var el=document.getElementById('chart_gl_class'); if(!el) return;
  new Chart(el, {type:'doughnut', data:{labels:$lj, datasets:[{data:$dj,
    backgroundColor:['#164E8F','#D4AF37','#10B981','#F59E0B','#8B5CF6','#3B82F6'], borderWidth:3, borderColor:'#fff', hoverOffset:8}]},
    options:{responsive:true, maintainAspectRatio:false, cutout:'65%',
      plugins:{legend:{position:'bottom', labels:{font:{size:11}, color:'#718096', boxWidth:10, padding:12, usePointStyle:true}},
               tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:12}}}});
})();
</script>\n";
}

function _vd_gl_month_chart($today)
{
    global $SysPrefs;
    $months = isset($_POST['per_gl_m']) ? (int)$_POST['per_gl_m'] : 6;
    $begin  = date2sql(begin_fiscalyear());
    $t1     = date2sql($today);
    $sep    = $SysPrefs->dateseps[user_date_sep()];
    $sql    = "SELECT month_name, sales, costs FROM (
                   SELECT DATE_FORMAT(tran_date,'%Y{$sep}%m') AS month_name,
                          SUM(IF(c.ctype=4, amount*-1, 0)) AS sales,
                          SUM(IF(c.ctype=6, amount, 0)) AS costs
                   FROM " . TB_PREF . "gl_trans
                   JOIN " . TB_PREF . "chart_master a ON account=a.account_code
                   JOIN " . TB_PREF . "chart_types  t ON a.account_type=t.id
                   JOIN " . TB_PREF . "chart_class  c ON t.class_id=c.cid
                   WHERE (c.ctype=4 OR c.ctype=6) AND tran_date>='$begin' AND tran_date<='$t1'
                   GROUP BY month_name ORDER BY month_name DESC LIMIT 0,$months
               ) b GROUP BY month_name ORDER BY month_name ASC";
    $rr = db_query($sql);
    $mn = $s = $c = $rs = [];
    while ($row = db_fetch($rr)) {
        $mn[] = $row['month_name'];
        $sv   = round((float)$row['sales']);
        $cv   = round((float)$row['costs']);
        $s[]  = $sv; $c[] = $cv; $rs[] = $sv - $cv;
    }
    $title = sprintf(_('Last %s Months Performance'), $months);
    echo "<div class='vd-chart-card' id='vd_gl_m'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>" . htmlspecialchars($title) . "</span>\n";
    echo "  <select id='per_gl_m' onchange='chart_update(this,\"vd_gl_m\")' class='vd-select'>\n";
    foreach ([3, 4, 5, 6, 7, 8] as $n) echo "<option value='$n'" . ($months == $n ? ' selected' : '') . ">$n</option>\n";
    echo "  </select></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_gl_m'></canvas></div>\n</div>\n";
    $lj = json_encode($mn, JSON_UNESCAPED_UNICODE);
    $sj = json_encode($s); $cj = json_encode($c); $rj = json_encode($rs);
    $ls = addslashes(_('Sales')); $lc = addslashes(_('Costs')); $lr = addslashes(_('Results'));
    echo "<script>
(function(){ var el=document.getElementById('chart_gl_m'); if(!el) return;
  new Chart(el, {type:'bar', data:{labels:$lj, datasets:[
    {type:'bar',  label:'$ls', data:$sj, backgroundColor:'#164E8F44', borderColor:'#164E8F', borderWidth:2, borderRadius:4},
    {type:'bar',  label:'$lc', data:$cj, backgroundColor:'#D4AF3744', borderColor:'#D4AF37', borderWidth:2, borderRadius:4},
    {type:'line', label:'$lr', data:$rj, borderColor:'#10B981', backgroundColor:'transparent', borderWidth:2.5, pointRadius:4, tension:0.4}]},
    options:{responsive:true, maintainAspectRatio:false,
      plugins:{legend:{labels:{font:{size:12}, color:'#4A5568', boxWidth:12}}, tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:12}},
      scales:{x:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096'}},
              y:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096', callback:function(v){return v>=1000?(v/1000)+'K':v;}}}}}});
})();
</script>\n";
}

function _vd_gl_week_chart($today)
{
    global $SysPrefs;
    $weeks = isset($_POST['per_gl_w']) ? (int)$_POST['per_gl_w'] : 4;
    $begin = date2sql(begin_fiscalyear());
    $t1    = date2sql($today);
    $sep   = $SysPrefs->dateseps[user_date_sep()];
    $sql   = "SELECT week_name, sales, costs FROM (
                  SELECT DATE_FORMAT(tran_date,'%X{$sep}%V') AS week_name,
                         SUM(IF(c.ctype=4, amount*-1, 0)) AS sales,
                         SUM(IF(c.ctype=6, amount, 0)) AS costs
                  FROM " . TB_PREF . "gl_trans
                  JOIN " . TB_PREF . "chart_master a ON account=a.account_code
                  JOIN " . TB_PREF . "chart_types  t ON a.account_type=t.id
                  JOIN " . TB_PREF . "chart_class  c ON t.class_id=c.cid
                  WHERE (c.ctype=4 OR c.ctype=6) AND tran_date>='$begin' AND tran_date<='$t1'
                  GROUP BY week_name ORDER BY week_name DESC LIMIT 0,$weeks
              ) b GROUP BY week_name ORDER BY week_name ASC";
    $rr = db_query($sql);
    $wn = $s = $c = $rs = [];
    while ($row = db_fetch($rr)) {
        $wn[] = $row['week_name'];
        $sv   = round((float)$row['sales']); $cv = round((float)$row['costs']);
        $s[] = $sv; $c[] = $cv; $rs[] = $sv - $cv;
    }
    $title = sprintf(_('Last %s Weeks Performance'), $weeks);
    echo "<div class='vd-chart-card' id='vd_gl_w'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>" . htmlspecialchars($title) . "</span>\n";
    echo "  <select id='per_gl_w' onchange='chart_update(this,\"vd_gl_w\")' class='vd-select'>\n";
    foreach ([3, 4, 5, 6, 7, 8] as $n) echo "<option value='$n'" . ($weeks == $n ? ' selected' : '') . ">$n</option>\n";
    echo "  </select></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_gl_w'></canvas></div>\n</div>\n";
    $lj = json_encode($wn, JSON_UNESCAPED_UNICODE);
    $sj = json_encode($s); $cj = json_encode($c); $rj = json_encode($rs);
    $ls = addslashes(_('Sales')); $lc = addslashes(_('Costs')); $lr = addslashes(_('Results'));
    echo "<script>
(function(){ var el=document.getElementById('chart_gl_w'); if(!el) return;
  new Chart(el, {type:'bar', data:{labels:$lj, datasets:[
    {type:'bar',  label:'$ls', data:$sj, backgroundColor:'#164E8F44', borderColor:'#164E8F', borderWidth:2, borderRadius:4},
    {type:'bar',  label:'$lc', data:$cj, backgroundColor:'#D4AF3744', borderColor:'#D4AF37', borderWidth:2, borderRadius:4},
    {type:'line', label:'$lr', data:$rj, borderColor:'#10B981', backgroundColor:'transparent', borderWidth:2.5, pointRadius:4, tension:0.4}]},
    options:{responsive:true, maintainAspectRatio:false,
      plugins:{legend:{labels:{font:{size:12}, color:'#4A5568', boxWidth:12}}, tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:12}},
      scales:{x:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096'}},
              y:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096', callback:function(v){return v>=1000?(v/1000)+'K':v;}}}}}});
})();
</script>\n";
}

function _vd_cash_flow_chart($today)
{
    global $date_system, $SysPrefs;
    $months = isset($_POST['per_cf']) ? (int)$_POST['per_cf'] : 6;
    if (!is_date_in_fiscalyear($today)) $today = end_fiscalyear();
    $today1 = add_months($today, -$months + 1);
    list($da, $mo, $yr) = explode_date_to_dmy($today1);
    if ($date_system == 1) list($yr, $mo, $da) = jalali_to_gregorian($yr, $mo, $da);
    elseif ($date_system == 2) list($yr, $mo, $da) = islamic_to_gregorian($yr, $mo, $da);
    $date = [];
    for ($i = 0; $i < $months; $i++) $date[$i] = date('Y-m-d', mktime(0, 0, 0, $mo + $i, 1, $yr));
    $result = get_bank_accounts();
    $total  = array_fill(0, $months, 0);
    while ($account = db_fetch($result)) {
        $sql = "SELECT";
        for ($i = 0; $i < $months; $i++)
            $sql .= " SUM(CASE WHEN trans_date<'{$date[$i]}' THEN amount ELSE 0 END) AS per" . ($i + 1) . ",";
        $sql = rtrim($sql, ',') . " FROM " . TB_PREF . "bank_trans WHERE bank_act=" . $account['id'];
        $bal = db_fetch(db_query($sql));
        $ih  = is_company_currency($account['bank_curr_code']);
        for ($i = 0; $i < $months; $i++) {
            $b = (float)$bal['per' . ($i + 1)];
            if (!$ih) $b = to_home_currency($b, $account['bank_curr_code'], sql2date($date[$i]));
            $total[$i] += $b;
        }
    }
    $sep    = $SysPrefs->dateseps[user_date_sep()];
    $labels = [];
    for ($i = 0; $i < $months; $i++) { $labels[] = substr($date[$i], 0, 4) . $sep . substr($date[$i], 5, 2); $total[$i] = round($total[$i]); }
    $title = sprintf(_('Last %s Months Cash Flow'), $months);
    echo "<div class='vd-chart-card' id='vd_cf'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>" . htmlspecialchars($title) . "</span>\n";
    echo "  <select id='per_cf' onchange='chart_update(this,\"vd_cf\")' class='vd-select'>\n";
    foreach ([3, 4, 5, 6, 7, 8] as $n) echo "<option value='$n'" . ($months == $n ? ' selected' : '') . ">$n</option>\n";
    echo "  </select></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_cf'></canvas></div>\n</div>\n";
    $lj = json_encode($labels, JSON_UNESCAPED_UNICODE);
    $dj = json_encode(array_values($total));
    $lt = addslashes(_('Balances'));
    echo "<script>
(function(){ var el=document.getElementById('chart_cf'); if(!el) return;
  new Chart(el, {type:'line', data:{labels:$lj, datasets:[{label:'$lt', data:$dj,
    borderColor:'#164E8F', backgroundColor:'rgba(22,78,143,0.08)', fill:true, borderWidth:2.5, pointRadius:4, tension:0.4}]},
    options:{responsive:true, maintainAspectRatio:false,
      plugins:{legend:{labels:{font:{size:12}, color:'#4A5568'}}, tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:12}},
      scales:{x:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096'}},
              y:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096', callback:function(v){return v>=1000?(v/1000)+'K':v;}}}}}});
})();
</script>\n";
}

/* ═══════════════════════════════════════════════════
   BAR CHART HELPER
═══════════════════════════════════════════════════ */

function _vd_bar_chart($id, $title, $labels, $datasets, $orient = 'vertical')
{
    echo "<div class='vd-chart-card'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>" . htmlspecialchars($title) . "</span></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_$id'></canvas></div>\n</div>\n";
    $palette = ['#164E8F', '#D4AF37', '#10B981', '#F59E0B', '#8B5CF6'];
    $ds = [];
    foreach ($datasets as $i => $d) {
        $c    = $d['color'] ?? $palette[$i % 5];
        $ds[] = "{label:" . json_encode($d['label']) . ", data:" . json_encode($d['data']) . ", backgroundColor:'{$c}33', borderColor:'$c', borderWidth:2, borderRadius:4}";
    }
    $ds_s = '[' . implode(',', $ds) . ']';
    $lj   = json_encode($labels, JSON_UNESCAPED_UNICODE);
    $ax   = ($orient === 'horizontal') ? 'y' : 'x';
    echo "<script>
(function(){ var el=document.getElementById('chart_$id'); if(!el) return;
  new Chart(el, {type:'bar', data:{labels:$lj, datasets:$ds_s},
    options:{indexAxis:'$ax', responsive:true, maintainAspectRatio:false,
      plugins:{legend:{labels:{font:{size:12}, color:'#4A5568', boxWidth:12}}, tooltip:{backgroundColor:'#0A2342', cornerRadius:8, padding:12}},
      scales:{x:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096', callback:function(v){return typeof v==='number'&&v>=1000?(v/1000)+'K':v;}}},
              y:{grid:{color:'#EDF0F7'}, ticks:{font:{size:11}, color:'#718096'}}}}});
})();
</script>\n";
}

/* ═══════════════════════════════════════════════════
   DATA FUNCTIONS — all pull from DB
═══════════════════════════════════════════════════ */

function _vd_customer_top_data($today, $limit = 10)
{
    $begin = date2sql(begin_fiscalyear()); $t1 = date2sql($today);
    $sql = "SELECT SUM((ov_amount+ov_discount)*rate*IF(trans.type=" . ST_CUSTCREDIT . ",-1,1)) AS total, d.name
            FROM " . TB_PREF . "debtor_trans AS trans
            JOIN " . TB_PREF . "debtors_master AS d ON trans.debtor_no=d.debtor_no
            WHERE (trans.type=" . ST_SALESINVOICE . " OR trans.type=" . ST_CUSTCREDIT . ")
              AND tran_date>='$begin' AND tran_date<='$t1'
            GROUP BY d.debtor_no ORDER BY total DESC LIMIT $limit";
    $r = db_query($sql); $names = $totals = []; $sum = 0;
    while ($row = db_fetch($r)) { $names[] = htmlspecialchars_decode($row['name']); $totals[] = round((float)$row['total']); $sum += (float)$row['total']; }
    return ['names' => $names, 'totals' => $totals, 'total' => $sum];
}

function _vd_supplier_top_data($today, $limit = 10)
{
    $begin = date2sql(begin_fiscalyear()); $t1 = date2sql($today);
    $sql = "SELECT SUM((trans.ov_amount+trans.ov_discount)*rate) AS total, s.supp_name
            FROM " . TB_PREF . "supp_trans AS trans
            JOIN " . TB_PREF . "suppliers AS s ON trans.supplier_id=s.supplier_id
            WHERE (trans.type=" . ST_SUPPINVOICE . " OR trans.type=" . ST_SUPPCREDIT . ")
              AND tran_date>='$begin' AND tran_date<='$t1'
            GROUP BY s.supplier_id ORDER BY total DESC LIMIT $limit";
    $r = db_query($sql); $names = $totals = []; $sum = 0;
    while ($row = db_fetch($r)) { $names[] = htmlspecialchars_decode($row['supp_name']); $totals[] = round((float)$row['total']); $sum += (float)$row['total']; }
    return ['names' => $names, 'totals' => $totals, 'total' => $sum];
}

function _vd_stock_top_data($today, $limit = 5, $type = 0)
{
    $begin = date2sql(begin_fiscalyear()); $t1 = date2sql($today);
    if ($type == 0) {
        $sql = "SELECT SUM((trans.unit_price*trans.quantity)*d.rate) AS total,
                       SUM((s.material_cost+s.overhead_cost+s.labour_cost)*trans.quantity) AS costs, s.description
                FROM " . TB_PREF . "debtor_trans_details AS trans
                JOIN " . TB_PREF . "stock_master AS s ON trans.stock_id=s.stock_id
                JOIN " . TB_PREF . "debtor_trans  AS d ON trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
                WHERE (d.type=" . ST_SALESINVOICE . " OR d.type=" . ST_CUSTCREDIT . ")
                  AND tran_date>='$begin' AND tran_date<='$t1'
                GROUP BY s.stock_id ORDER BY total DESC LIMIT $limit";
    } else {
        $flag = ($type == 1) ? 'M' : 'F';
        $sql  = "SELECT SUM(m.qty*(s.material_cost+s.labour_cost+s.overhead_cost)) AS total, 0 AS costs, s.description
                 FROM " . TB_PREF . "stock_master AS s
                 JOIN " . TB_PREF . "stock_moves  AS m ON s.stock_id=m.stock_id
                 WHERE s.mb_flag='$flag'" . ($type != 2 ? " AND tran_date>='$begin'" : '') . "
                   AND tran_date<='$t1'
                 GROUP BY s.stock_id ORDER BY total DESC LIMIT $limit";
    }
    $r = db_query($sql); $names = $totals = $costs = $results = []; $ts = $tc = $tr = 0;
    while ($row = db_fetch($r)) {
        $names[] = htmlspecialchars_decode($row['description']);
        $t = round((float)$row['total']); $c = round((float)$row['costs']);
        $totals[] = $t; $costs[] = $c; $results[] = $t - $c;
        $ts += $t; $tc += $c; $tr += ($t - $c);
    }
    return ['names' => $names, 'totals' => $totals, 'costs' => $costs, 'results' => $results,
            'total_sales' => $ts, 'total_costs' => $tc, 'total_results' => $tr];
}

function _vd_dimension_top_data($today, $limit = 5)
{
    $begin = date2sql(begin_fiscalyear()); $t1 = date2sql($today);
    $sql = "SELECT SUM(IF(c.ctype>3,-amount,0)) AS result, SUM(IF(c.ctype<4,amount,0)) AS balance, d.reference, d.name
            FROM " . TB_PREF . "gl_trans g
            LEFT JOIN " . TB_PREF . "chart_master a ON g.account=a.account_code
            LEFT JOIN " . TB_PREF . "chart_types  t ON t.id=a.account_type
            LEFT JOIN " . TB_PREF . "dimensions   d ON (g.dimension_id=d.id OR g.dimension2_id=d.id)
            LEFT JOIN " . TB_PREF . "chart_class  c ON t.class_id=c.cid
            WHERE IF(c.ctype>3, tran_date>='$begin', tran_date>='0000-00-00') AND tran_date<='$t1'
              AND d.reference IS NOT NULL
            GROUP BY d.reference ORDER BY result DESC LIMIT $limit";
    $r = db_query($sql); $names = $balances = $results = [];
    while ($row = db_fetch($r)) {
        $names[]    = $row['reference'] . ' ' . htmlspecialchars_decode($row['name']);
        $balances[] = round(abs((float)$row['balance']));
        $results[]  = round(abs((float)$row['result']));
    }
    return compact('names', 'balances', 'results');
}

function _vd_customer_aging_data($today)
{
    $t1   = date2sql($today);
    $p1   = get_company_pref('past_due_days');
    $p2   = 2 * $p1;
    $sign = "IF(`type` IN(" . implode(',', [ST_CUSTCREDIT, ST_CUSTPAYMENT, ST_BANKDEPOSIT]) . "),-1,1)";
    $val  = "$sign*(IF(trans.prep_amount,trans.prep_amount,ABS(trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount))-trans.alloc)*trans.rate";
    $due  = "IF(trans.type=" . ST_SALESINVOICE . ",trans.due_date,trans.tran_date)";
    $sql  = "SELECT SUM($val) AS Balance,
                    SUM(IF((TO_DAYS('$t1')-TO_DAYS($due))>0,$val,0)) AS Due,
                    SUM(IF((TO_DAYS('$t1')-TO_DAYS($due))>$p1,$val,0)) AS Overdue1,
                    SUM(IF((TO_DAYS('$t1')-TO_DAYS($due))>$p2,$val,0)) AS Overdue2
             FROM " . TB_PREF . "debtors_master debtor
             LEFT JOIN " . TB_PREF . "debtor_trans trans ON trans.tran_date<='$t1' AND debtor.debtor_no=trans.debtor_no AND trans.type<>" . ST_CUSTDELIVERY . "
             JOIN " . TB_PREF . "payment_terms terms ON debtor.payment_terms=terms.terms_indicator
             JOIN " . TB_PREF . "credit_status cs ON debtor.credit_status=cs.id
             WHERE ABS(IF(trans.prep_amount,trans.prep_amount,ABS(trans.ov_amount)+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount)-trans.alloc)>" . FLOAT_COMP_DELTA;
    $r = db_query($sql); $row = db_fetch($r);
    $p1a = $p1 + 1; $p2a = $p2 + 1;
    return [
        'labels' => [_('Current'), "1-$p1", "$p1a-$p2", "$p2a+"],
        'values' => [
            round($row['Balance'] - $row['Due']),
            round($row['Due'] - $row['Overdue1']),
            round($row['Overdue1'] - $row['Overdue2']),
            round($row['Overdue2']),
        ],
        'total'  => round($row['Balance']),
    ];
}

function _vd_supplier_aging_data($today)
{
    $t1  = date2sql($today);
    $p1  = get_company_pref('past_due_days');
    $p2  = 2 * $p1;
    $val = "IF(trans.type=" . ST_SUPPINVOICE . " OR trans.type=" . ST_BANKDEPOSIT . ",
               (trans.ov_amount+trans.ov_gst+trans.ov_discount-trans.alloc)*trans.rate,
               (trans.ov_amount+trans.ov_gst+trans.ov_discount+trans.alloc)*trans.rate)";
    $due = "IF(trans.type=" . ST_SUPPINVOICE . " OR trans.type=" . ST_SUPPCREDIT . ",trans.due_date,trans.tran_date)";
    $sql = "SELECT SUM($val) AS Balance,
                   SUM(IF((TO_DAYS('$t1')-TO_DAYS($due))>0,$val,0)) AS Due,
                   SUM(IF((TO_DAYS('$t1')-TO_DAYS($due))>$p1,$val,0)) AS Overdue1,
                   SUM(IF((TO_DAYS('$t1')-TO_DAYS($due))>$p2,$val,0)) AS Overdue2
            FROM " . TB_PREF . "suppliers supp
            LEFT JOIN " . TB_PREF . "supp_trans trans ON supp.supplier_id=trans.supplier_id AND trans.tran_date<='$t1'
              AND ABS(trans.ov_amount+trans.ov_gst+trans.ov_discount)>" . FLOAT_COMP_DELTA . "
              AND ABS(trans.ov_amount+trans.ov_gst+trans.ov_discount)-trans.alloc>" . FLOAT_COMP_DELTA;
    $r = db_query($sql); $row = db_fetch($r);
    $p1a = $p1 + 1; $p2a = $p2 + 1;
    return [
        'labels' => [_('Current'), "1-$p1", "$p1a-" . (2 * $p1), "$p2a+"],
        'values' => [
            round($row['Balance'] - $row['Due']),
            round($row['Due'] - $row['Overdue1']),
            round($row['Overdue1'] - $row['Overdue2']),
            round($row['Overdue2']),
        ],
        'total'  => round($row['Balance']),
    ];
}

function _vd_customer_balance($today)
{
    $today = date2sql($today);
    $sign  = "IF(`type` IN(" . implode(',', [ST_CUSTCREDIT, ST_CUSTPAYMENT, ST_BANKDEPOSIT]) . "),-1,1)";
    $val   = "IFNULL($sign*((trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount-trans.alloc)*trans.rate),0)";
    $sql   = "SELECT Sum($val) AS Balance FROM " . TB_PREF . "debtors_master debtor
              LEFT JOIN " . TB_PREF . "debtor_trans trans ON trans.tran_date<='$today'
                AND debtor.debtor_no=trans.debtor_no AND trans.type<>" . ST_CUSTDELIVERY . "
              WHERE ABS(trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount)-trans.alloc>" . FLOAT_COMP_DELTA;
    $r   = db_query($sql); $row = $r ? db_fetch($r) : null; return $row ? (float)$row[0] : 0.0;
}

function _vd_supplier_balance($today)
{
    $today = date2sql($today);
    $val   = "IF(trans.type=" . ST_SUPPINVOICE . " OR trans.type=" . ST_BANKDEPOSIT . ",
                 (trans.ov_amount+trans.ov_gst+trans.ov_discount-trans.alloc)*trans.rate,
                 (trans.ov_amount+trans.ov_gst+trans.ov_discount+trans.alloc)*trans.rate)";
    $sql   = "SELECT Sum($val) AS Balance FROM " . TB_PREF . "suppliers supplier
              JOIN " . TB_PREF . "supp_trans trans ON supplier.supplier_id=trans.supplier_id
              WHERE trans.tran_date<='$today'
                AND ABS(trans.ov_amount+trans.ov_gst+trans.ov_discount)>" . FLOAT_COMP_DELTA . "
                AND ABS(trans.ov_amount+trans.ov_gst+trans.ov_discount)-trans.alloc>" . FLOAT_COMP_DELTA;
    $r = db_query($sql); $row = $r ? db_fetch($r) : null; return $row ? (float)$row[0] : 0.0;
}

function _vd_get_today_deposits($today)
{
    $today = date2sql($today);
    $sql   = "SELECT SUM(gl.amount) FROM " . TB_PREF . "gl_trans gl, " . TB_PREF . "bank_accounts ba
              WHERE gl.account=ba.account_code AND gl.tran_date='$today' AND gl.amount>0.0";
    $r = db_query($sql); if (!$r) return 0; $row = db_fetch($r); return (float)$row[0];
}

function _vd_get_today_payments($today)
{
    $today = date2sql($today);
    $sql   = "SELECT SUM(gl.amount) FROM " . TB_PREF . "gl_trans gl, " . TB_PREF . "bank_accounts ba
              WHERE gl.account=ba.account_code AND gl.tran_date='$today' AND gl.amount<0.0";
    $r = db_query($sql); if (!$r) return 0; $row = db_fetch($r); return (float)$row[0];
}

/* ═══════════════════════════════════════════════════
   TABLE HELPERS — all data live from DB
═══════════════════════════════════════════════════ */

function _vd_customer_top_table($today, $limit, $rtl = false)
{
    $data = _vd_customer_top_data($today, $limit); $rows = '';
    for ($i = 0; $i < count($data['names']); $i++)
        $rows .= "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($data['names'][$i]) . "</td><td class='vd-right vd-amount'>" . number_format($data['totals'][$i], 0) . "</td></tr>\n";
    _vd_table(
        sprintf(_('Top %s Customers'), $limit),
        [_('#'), _('Customer'), _('Amount')],
        $rows ?: "<tr><td colspan='3' class='vd-empty'>" . _('No data') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_supplier_top_table($today, $limit, $rtl = false)
{
    $data = _vd_supplier_top_data($today, $limit); $rows = '';
    for ($i = 0; $i < count($data['names']); $i++)
        $rows .= "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($data['names'][$i]) . "</td><td class='vd-right vd-amount'>" . number_format($data['totals'][$i], 0) . "</td></tr>\n";
    _vd_table(
        sprintf(_('Top %s Suppliers'), $limit),
        [_('#'), _('Supplier'), _('Amount')],
        $rows ?: "<tr><td colspan='3' class='vd-empty'>" . _('No data') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_overdue_invoices_table($today, $rtl = false)
{
    $t1  = date2sql($today);
    $sql = "SELECT trans.trans_no, trans.reference, trans.tran_date, trans.due_date, debtor.name,
                   (trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount) AS total,
                   DATEDIFF('$t1', trans.due_date) AS days
            FROM " . TB_PREF . "debtor_trans AS trans
            JOIN " . TB_PREF . "debtors_master AS debtor ON debtor.debtor_no=trans.debtor_no
            JOIN " . TB_PREF . "cust_branch    AS branch  ON trans.branch_code=branch.branch_code
            WHERE trans.type=" . ST_SALESINVOICE . "
              AND (trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount-trans.alloc)>" . FLOAT_COMP_DELTA . "
              AND DATEDIFF('$t1', trans.due_date)>0 ORDER BY days DESC";
    $r   = db_query($sql); $cnt = db_num_rows($r); $rows = '';
    while ($row = db_fetch($r)) {
        $rows .= "<tr class='vd-overdue-row'>"
            . "<td>" . get_trans_view_str(ST_SALESINVOICE, $row['trans_no']) . "</td>"
            . "<td>" . htmlspecialchars($row['reference']) . "</td>"
            . "<td>" . sql2date($row['tran_date']) . "</td>"
            . "<td>" . sql2date($row['due_date']) . "</td>"
            . "<td>" . htmlspecialchars($row['name']) . "</td>"
            . "<td class='vd-right vd-amount'>" . number_format((float)$row['total'], 2) . "</td>"
            . "<td class='vd-right'><span class='vd-status vd-st-overdue'>" . ((int)$row['days']) . " " . _('days') . "</span></td>"
            . "</tr>\n";
    }
    _vd_table(
        $cnt . ' ' . _('overdue Sales Invoices'),
        [_('#'), _('Ref.'), _('Date'), _('Due Date'), _('Customer'), _('Total'), _('Days')],
        $rows ?: "<tr><td colspan='7' class='vd-empty'>" . _('No overdue invoices') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_unpaid_supplier_table($today, $rtl = false)
{
    $t1  = date2sql($today);
    $sql = "SELECT trans.trans_no, trans.reference, trans.tran_date, trans.due_date, s.supp_name,
                   (trans.ov_amount+trans.ov_gst+trans.ov_discount) AS total,
                   DATEDIFF('$t1', trans.due_date) AS days
            FROM " . TB_PREF . "supp_trans AS trans
            JOIN " . TB_PREF . "suppliers AS s ON s.supplier_id=trans.supplier_id
            WHERE trans.type=" . ST_SUPPINVOICE . "
              AND (ABS(trans.ov_amount+trans.ov_gst+trans.ov_discount)-trans.alloc)>" . FLOAT_COMP_DELTA . "
            ORDER BY days DESC";
    $r   = db_query($sql); $cnt = db_num_rows($r); $rows = '';
    while ($row = db_fetch($r)) {
        $od    = (int)$row['days'] > 0;
        $badge = $od
            ? "<span class='vd-status vd-st-overdue'>" . ((int)$row['days']) . " " . _('days') . "</span>"
            : "<span class='vd-status vd-st-paid'>" . _('Current') . "</span>";
        $rows .= "<tr class='" . ($od ? 'vd-overdue-row' : '') . "'>"
            . "<td>" . get_trans_view_str(ST_SUPPINVOICE, $row['trans_no']) . "</td>"
            . "<td>" . htmlspecialchars($row['reference']) . "</td>"
            . "<td>" . sql2date($row['tran_date']) . "</td>"
            . "<td>" . sql2date($row['due_date']) . "</td>"
            . "<td>" . htmlspecialchars($row['supp_name']) . "</td>"
            . "<td class='vd-right vd-amount'>" . number_format((float)$row['total'], 2) . "</td>"
            . "<td class='vd-right'>$badge</td>"
            . "</tr>\n";
    }
    _vd_table(
        $cnt . ' ' . _('unpaid Purchase Invoices'),
        [_('#'), _('Ref.'), _('Date'), _('Due Date'), _('Supplier'), _('Total'), _('Status')],
        $rows ?: "<tr><td colspan='7' class='vd-empty'>" . _('No unpaid invoices') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_stock_top_table($today, $limit, $type, $rtl = false)
{
    $data  = _vd_stock_top_data($today, $limit, $type);
    $heads = $type == 0
        ? [_('#'), _('Item'), _('Sales'), _('Costs'), _('Results')]
        : [_('#'), _('Item'), _('Amount')];
    $rows  = '';
    for ($i = 0; $i < count($data['names']); $i++) {
        $rows .= "<tr><td>" . ($i + 1) . "</td><td>" . htmlspecialchars($data['names'][$i]) . "</td><td class='vd-right vd-amount'>" . number_format($data['totals'][$i], 0) . "</td>";
        if ($type == 0) $rows .= "<td class='vd-right'>" . number_format($data['costs'][$i], 0) . "</td><td class='vd-right'>" . number_format($data['results'][$i], 0) . "</td>";
        $rows .= "</tr>\n";
    }
    $t = $type == 1 ? sprintf(_('Top %s Manufactured Items'), $limit)
                    : ($type == 2 ? sprintf(_('Top %s Fixed Assets'), $limit)
                                  : sprintf(_('Top %s Sold Items'), $limit));
    _vd_table($t, $heads, $rows ?: "<tr><td colspan='" . count($heads) . "' class='vd-empty'>" . _('No data') . "</td></tr>", '', $rtl);
}

function _vd_stock_reorder_table($today, $type, $rtl = false)
{
    $t1   = date2sql($today);
    $flag = ($type == 1 ? 'M' : ($type == 2 ? 'F' : 'B'));
    $sql  = "SELECT st.loc_code, l.location_name, st.stock_id, sm.description,
                    reorders.reorder_level AS r, SUM(st.qty) AS q
             FROM " . TB_PREF . "stock_moves st
             LEFT JOIN " . TB_PREF . "voided v ON st.type=v.type AND st.trans_no=v.id
             LEFT JOIN " . TB_PREF . "stock_master sm ON sm.stock_id=st.stock_id
             LEFT JOIN " . TB_PREF . "loc_stock reorders ON reorders.loc_code=st.loc_code AND reorders.stock_id=st.stock_id
             LEFT JOIN " . TB_PREF . "locations l ON l.loc_code=st.loc_code
             WHERE ISNULL(v.id) AND tran_date<='$t1' AND sm.mb_flag='$flag'
             GROUP BY sm.stock_id HAVING q<=r";
    $r   = db_query($sql); $cnt = db_num_rows($r); $rows = '';
    while ($row = db_fetch($r)) {
        $dec   = get_qty_dec($row['stock_id']);
        $rows .= "<tr>"
            . "<td>" . htmlspecialchars($row['location_name']) . "</td>"
            . "<td>" . htmlspecialchars($row['stock_id']) . "</td>"
            . "<td>" . htmlspecialchars($row['description']) . "</td>"
            . "<td class='vd-right'><span class='vd-status vd-st-pending'>" . number_format((float)$row['r'], $dec) . "</span></td>"
            . "<td class='vd-right vd-amount'>" . number_format((float)$row['q'], $dec) . "</td>"
            . "</tr>\n";
    }
    _vd_table(
        $cnt . ' ' . _('items are below Reorder Level'),
        [_('Location'), _('Stock ID'), _('Description'), _('Reorder'), _('Qty')],
        $rows ?: "<tr><td colspan='5' class='vd-empty'>" . _('No items below reorder level') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_dimension_top_table($today, $limit, $rtl = false)
{
    $data = _vd_dimension_top_data($today, $limit); $rows = '';
    for ($i = 0; $i < count($data['names']); $i++)
        $rows .= "<tr><td>" . htmlspecialchars($data['names'][$i]) . "</td><td class='vd-right vd-amount'>" . number_format($data['balances'][$i], 0) . "</td><td class='vd-right vd-amount'>" . number_format($data['results'][$i], 0) . "</td></tr>\n";
    _vd_table(
        sprintf(_('Top %s Dimensions – Fiscal Year'), $limit),
        [_('Dimension'), _('Balance'), _('Result')],
        $rows ?: "<tr><td colspan='3' class='vd-empty'>" . _('No data') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_bank_balance_table($today, $rtl = false)
{
    $t1  = date2sql($today);
    $sql = "SELECT bank_act, bank_account_name, bank_curr_code, SUM(amount) AS balance
            FROM " . TB_PREF . "bank_trans bt
            JOIN " . TB_PREF . "bank_accounts ba ON bt.bank_act=ba.id
            WHERE trans_date<='$t1' AND inactive<>1
            GROUP BY bank_act, bank_account_name ORDER BY bank_account_name";
    $r    = db_query($sql); $rows = '';
    while ($row = db_fetch($r)) {
        $lnk  = viewer_link(htmlspecialchars($row['bank_account_name']), 'gl/inquiry/bank_inquiry.php?bank_account=' . $row['bank_act']);
        $bal  = (float)$row['balance'];
        $rows .= "<tr><td>$lnk</td><td>" . htmlspecialchars($row['bank_curr_code']) . "</td><td class='vd-right vd-amount" . ($bal < 0 ? ' vd-neg' : '') . "'>" . number_format2($bal) . "</td></tr>\n";
    }
    _vd_table(
        _('Bank Account Balances'),
        [_('Account'), _('Currency'), _('Balance')],
        $rows ?: "<tr><td colspan='3' class='vd-empty'>" . _('No bank accounts') . "</td></tr>",
        '',
        $rtl
    );
}

function _vd_recurrent_invoices_table($today, $rtl = false)
{
    $result = get_recurrent_invoices($today); $rows = '';
    while ($row = db_fetch($result)) {
        if (!$row['overdue']) continue;
        $next = calculate_next_invoice($row);
        $cust = $row['debtor_no'] == 0 ? get_sales_group_name($row['group_no']) : get_customer_name($row['debtor_no']);
        $br   = $row['debtor_no'] == 0 ? '' : get_branch_name($row['group_no']);
        $rows .= "<tr>"
            . "<td>" . htmlspecialchars($row['description']) . "</td>"
            . "<td>" . get_customer_trans_view_str(ST_SALESORDER, $row['order_no']) . "</td>"
            . "<td>" . htmlspecialchars($cust) . "</td>"
            . "<td>" . htmlspecialchars($br) . "</td>"
            . "<td class='vd-right'>" . htmlspecialchars($next) . "</td>"
            . "</tr>\n";
    }
    if (!$rows) return;
    echo "<div class='vd-tables-row'>\n";
    _vd_table(
        _('Overdue Recurrent Invoices'),
        [_('Description'), _('Template No'), _('Customer'), _('Branch'), _('Next Invoice')],
        $rows,
        '',
        $rtl
    );
    echo "</div>\n";
}

/* ═══════════════════════════════════════════════════
   UI PRIMITIVES
═══════════════════════════════════════════════════ */

function _vd_section_header($title, $rtl = false)
{
    $dir = $rtl ? " dir='rtl'" : "";
    echo "<div class='vd-section-hdr'$dir><h3>" . htmlspecialchars($title) . "</h3></div>\n";
}

function _vd_kpi_strip($kpis, $rtl = false)
{
    static $icons = [
        'people'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'branch'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        'sales'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
        'alert'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        'orders'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
        'invoice' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'box'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        'loc'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'kit'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
        'cat'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        'cclass'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/></svg>',
        'mfg'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
        'wc'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>',
        'dim'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/><line x1="12" y1="22" x2="12" y2="15.5"/><polyline points="22 8.5 12 15.5 2 8.5"/></svg>',
        'dim2'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="9" height="9" rx="1"/><rect x="13" y="2" width="9" height="9" rx="1"/><rect x="2" y="13" width="9" height="9" rx="1"/><rect x="13" y="13" width="9" height="9" rx="1"/></svg>',
        'balance' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'result'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
        'recv'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
        'payb'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
        'dep'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
        'pmt'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="7" y1="15" x2="10" y2="15"/></svg>',
    ];
    $dflt = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>';
    $dir  = $rtl ? " dir='rtl'" : "";
    echo "<div class='vd-kpi-strip'$dir>\n";
    $i = 0;
    foreach ($kpis as $kpi) {
        $icon  = $icons[$kpi[3]] ?? $dflt;
        $delay = round($i * 0.07, 2);
        echo "<div class='vd-kpi-card' style='--kc:" . htmlspecialchars($kpi[2]) . ";animation-delay:{$delay}s'>\n";
        echo "  <div class='vd-kpi-top'><div class='vd-kpi-icon'>$icon</div></div>\n";
        echo "  <div class='vd-kpi-value'>" . htmlspecialchars((string)$kpi[1]) . "</div>\n";
        echo "  <div class='vd-kpi-label'>" . htmlspecialchars($kpi[0]) . "</div>\n";
        echo "</div>\n";
        $i++;
    }
    echo "</div>\n";
}

function _vd_total_badge($label, $value, $color)
{
    echo "<div class='vd-total-badge' style='border-left:4px solid $color'>\n";
    echo "  <span class='vd-total-label'>" . htmlspecialchars($label) . "</span>\n";
    echo "  <span class='vd-total-value' style='color:$color'>" . htmlspecialchars($value) . "</span>\n";
    echo "</div>\n";
}

function _vd_table($title, $heads, $rows_html, $action_link = '', $rtl = false)
{
    $dir = $rtl ? " dir='rtl'" : "";
    echo "<div class='vd-tbl-wrap'$dir>\n";
    echo "  <div class='vd-tbl-head'><span class='vd-tbl-title'>" . htmlspecialchars($title) . "</span>";
    if ($action_link) echo $action_link;
    echo "  </div>\n";
    echo "  <div class='vd-tbl-scroll'><table class='vd-tbl'>\n";
    echo "    <thead><tr>\n";
    foreach ($heads as $h) echo "      <th>" . htmlspecialchars($h) . "</th>\n";
    echo "    </tr></thead>\n    <tbody>\n$rows_html    </tbody>\n  </table></div>\n</div>\n";
}

/* ═══════════════════════════════════════════════════
   COUNT FUNCTIONS (all live from DB)
═══════════════════════════════════════════════════ */

function get_num_customers()    { $r = db_query("SELECT count(*) FROM " . TB_PREF . "debtors_master");    $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_branches()     { $r = db_query("SELECT count(*) FROM " . TB_PREF . "cust_branch");       $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_salesmen()     { $r = db_query("SELECT count(*) FROM " . TB_PREF . "salesman");          $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_suppliers()    { $r = db_query("SELECT count(*) FROM " . TB_PREF . "suppliers");         $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_items()        { $r = db_query("SELECT count(*) FROM " . TB_PREF . "stock_master");      $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_locations()    { $r = db_query("SELECT count(*) FROM " . TB_PREF . "locations");         $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_kits()         { $r = db_query("SELECT count(*) FROM " . TB_PREF . "item_codes");        $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_workcentres()  { $r = db_query("SELECT count(*) FROM " . TB_PREF . "workcentres");       $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_dimensions()   { $r = db_query("SELECT count(*) FROM " . TB_PREF . "dimensions");        $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_users()        { $r = db_query("SELECT count(*) FROM " . TB_PREF . "users");             $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_num_fixed_assets() { $r = db_query("SELECT count(*) FROM " . TB_PREF . "stock_master WHERE mb_flag='F'"); $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_dim_type2()        { $r = db_query("SELECT count(*) FROM " . TB_PREF . "dimensions WHERE type_=2"); $row = db_fetch($r); return $row ? $row[0] : 0; }
function get_open_workorders()  { $r = db_query("SELECT count(*) FROM " . TB_PREF . "workorders WHERE closed=0"); $row = db_fetch($r); return $row ? $row[0] : 0; }

function get_num_extensions() { global $installed_extensions; return count($installed_extensions); }
function get_num_languages()  { global $installed_languages;  return count($installed_languages); }

function get_num_overdue_sales()
{
    $today = date2sql(calc_today());
    $r     = db_query("SELECT COUNT(trans_no) FROM " . TB_PREF . "debtor_trans
                       WHERE type=" . ST_SALESINVOICE . "
                         AND (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount-alloc)>" . FLOAT_COMP_DELTA . "
                         AND DATEDIFF('$today',due_date)>0");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_new_purch_orders()
{
    $r   = db_query("SELECT count(*) FROM " . TB_PREF . "purch_orders po
                     JOIN " . TB_PREF . "purch_order_details pd ON po.order_no=pd.order_no
                     WHERE quantity_received<quantity_ordered");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_supp_invoices()
{
    $r   = db_query("SELECT count(*) FROM " . TB_PREF . "supp_trans WHERE (ov_amount+ov_discount+ov_gst-alloc)<>0");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_overdue_purch()
{
    $today = date2sql(calc_today());
    $r     = db_query("SELECT COUNT(*) FROM " . TB_PREF . "supp_trans
                       WHERE (ov_amount+ov_discount+ov_gst-alloc)>" . FLOAT_COMP_DELTA . "
                         AND DATEDIFF('$today',due_date)>0");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_reorder()
{
    $today = date2sql(calc_today());
    $sql   = "SELECT st.stock_id FROM " . TB_PREF . "stock_moves st
              LEFT JOIN " . TB_PREF . "voided v ON st.type=v.type AND st.trans_no=v.id
              LEFT JOIN " . TB_PREF . "stock_master sm ON sm.stock_id=st.stock_id
              LEFT JOIN " . TB_PREF . "loc_stock reorders ON reorders.loc_code=st.loc_code AND reorders.stock_id=st.stock_id
              WHERE ISNULL(v.id) AND tran_date<='$today' AND sm.mb_flag='B'
              GROUP BY st.stock_id HAVING SUM(st.qty)<=reorders.reorder_level";
    return db_num_rows(db_query($sql));
}

function get_num_assembled()
{
    $begin = date2sql(begin_fiscalyear()); $today = date2sql(calc_today());
    $r     = db_query("SELECT count(*) FROM " . TB_PREF . "workorders
                       WHERE closed=1 AND type=" . WO_ASSEMBLY . "
                         AND released_date>='$begin' AND released_date<='$today'");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_manufactured()
{
    $begin = date2sql(begin_fiscalyear()); $today = date2sql(calc_today());
    $r     = db_query("SELECT count(*) FROM " . TB_PREF . "workorders
                       WHERE closed=1 AND type=" . WO_ADVANCED . "
                         AND released_date>='$begin' AND released_date<='$today'");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_fixed_locations()
{
    $r   = db_query("SELECT count(*) FROM " . TB_PREF . "stock_master s LEFT JOIN " . TB_PREF . "loc_stock l ON s.stock_id=l.stock_id WHERE mb_flag='F'");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_fixed_categories()
{
    $r   = db_query("SELECT count(*) FROM " . TB_PREF . "stock_category c LEFT JOIN " . TB_PREF . "stock_master s ON c.category_id=s.category_id WHERE mb_flag='F'");
    $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_num_fixed_classes()
{
    $r = db_query("SELECT count(*) FROM " . TB_PREF . "stock_fa_class"); $row = db_fetch($r); return $row ? $row[0] : 0;
}

function get_dim_total()
{
    $begin = date2sql(begin_fiscalyear()); $today = date2sql(calc_today());
    $sql   = "SELECT SUM(IF(c.ctype>3,-amount,0)) AS result, SUM(IF(c.ctype<4,amount,0)) AS balance, d.reference
              FROM " . TB_PREF . "gl_trans g
              LEFT JOIN " . TB_PREF . "chart_master a ON g.account=a.account_code
              LEFT JOIN " . TB_PREF . "chart_types  t ON t.id=a.account_type
              LEFT JOIN " . TB_PREF . "dimensions   d ON (g.dimension_id=d.id OR g.dimension2_id=d.id)
              LEFT JOIN " . TB_PREF . "chart_class  c ON t.class_id=c.cid
              WHERE IF(c.ctype>3,tran_date>='$begin',tran_date>='0000-00-00') AND tran_date<='$today'
                AND d.reference IS NOT NULL
              GROUP BY d.reference";
    $bal = $res = 0.0; $r = db_query($sql);
    while ($row = db_fetch($r)) { $bal += $row['balance']; $res += $row['result']; }
    return [round($bal), round($res)];
}

function get_database_size()
{
    global $db_connections;
    $c     = $db_connections[user_company()];
    $dbase = $c['dbname'];
    $pref  = $c['tbpref'];
    $sql   = "SHOW TABLE STATUS FROM `$dbase`" . (!empty($pref) ? " LIKE '{$pref}%'" : '');
    $r     = db_query($sql); $size = 0;
    while ($row = db_fetch($r)) $size += $row['Data_length'] + $row['Index_length'];
    return number_format2(($size / 1024 / 1024), 2) . ' MB';
}

function calc_today()
{
    $today = Today();
    if (!is_date_in_fiscalyear($today)) $today = end_fiscalyear();
    return $today;
}

function calculate_next_invoice($myrow)
{
    $next = $myrow['last_sent'] == '0000-00-00'
        ? sql2date($myrow['begin'])
        : sql2date($myrow['last_sent']);
    return add_days(add_days(add_months($next, $myrow['monthly']), $myrow['days']), -1);
}

/* Legacy aliases */
function get_today_deposits($today) { return _vd_get_today_deposits($today); }
function get_today_payments($today) { return _vd_get_today_payments($today); }
function customer_balance($today)   { return _vd_customer_balance($today); }
function supplier_balance($today)   { return _vd_supplier_balance($today); }

/* ═══════════════════════════════════════════════════
   CSS (Full bilingual LTR/RTL)
═══════════════════════════════════════════════════ */

function _vd_styles($rtl = false)
{
    $dir         = $rtl ? 'rtl' : 'ltr';
    $text_align  = $rtl ? 'right' : 'left';
    $border_side = $rtl ? 'right' : 'left';
    $border_opp  = $rtl ? 'left'  : 'right';
    return <<<CSS
<style>
/* ── Base direction ── */
.vd-welcome,.vd-kpi-grid,.vd-kpi-strip,.vd-charts-row,.vd-totals-row,.vd-tables-row,.vd-quick-grid,
.vd-tbl-wrap,.vd-chart-card,.vd-kpi-card,.vd-section { direction:$dir; }

/* ── Welcome bar ── */
.vd-welcome { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; padding:20px 24px; background:var(--bg-surface,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:16px; box-shadow:0 1px 3px rgba(10,35,66,.04); }
.vd-welcome-title { font-family:var(--font-heading,'Inter',sans-serif); font-size:18px; font-weight:800; color:var(--text-primary,#050505); margin:0; }
.vd-welcome-sub   { font-size:12.5px; color:var(--text-muted,#718096); margin:4px 0 0; }
.vd-welcome-actions { display:flex; gap:10px; flex-wrap:wrap; }
.vd-btn-outline { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; font-size:12.5px; font-weight:600; border:1.5px solid var(--border,#E2E8F0); border-radius:9px; color:var(--text-secondary,#2D3748); text-decoration:none; background:var(--bg-surface,#fff); transition:all .18s; }
.vd-btn-outline:hover { border-color:#164E8F; color:#164E8F; }

/* ── KPI grid ── */
.vd-kpi-grid  { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:24px; }
.vd-kpi-strip { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:22px; }
.vd-kpi-card  { background:var(--bg-surface,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:16px; padding:20px 20px 18px; box-shadow:0 1px 3px rgba(10,35,66,.05),0 4px 14px rgba(10,35,66,.07); position:relative; overflow:hidden; animation:vd-fade-up .5s both; transition:all .22s; }
.vd-kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(10,35,66,.13); }
.vd-kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--kc,#164E8F); border-radius:16px 16px 0 0; }
.vd-kpi-top   { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.vd-kpi-icon  { width:42px; height:42px; border-radius:11px; background:#f0f5fd; border:1px solid rgba(22,78,143,.12); display:flex; align-items:center; justify-content:center; color:var(--kc,#164E8F); }
.vd-kpi-icon svg  { width:20px; height:20px; }
.vd-kpi-value { font-family:var(--font-heading,'Inter',sans-serif); font-size:22px; font-weight:800; letter-spacing:-.03em; color:var(--text-primary,#050505); line-height:1.1; margin-bottom:5px; }
.vd-kpi-label { font-size:12px; color:var(--text-muted,#718096); font-weight:500; }
.vd-chg-pos   { font-size:11px; font-weight:700; color:#059669; background:#ECFDF5; padding:2px 7px; border-radius:999px; }
.vd-chg-neg   { font-size:11px; font-weight:700; color:#DC2626; background:#FEF2F2; padding:2px 7px; border-radius:999px; }
.vd-badge-sm  { font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; }

/* ── Section ── */
.vd-section     { margin-bottom:24px; }
.vd-section-title { font-family:var(--font-heading,'Inter',sans-serif); font-size:14px; font-weight:700; color:var(--text-primary,#050505); margin:0 0 14px; padding-bottom:10px; border-bottom:2px solid var(--border,#E2E8F0); }
.vd-section-hdr { margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid var(--border,#E2E8F0); }
.vd-section-hdr h3 { font-family:var(--font-heading,'Inter',sans-serif); font-size:15px; font-weight:800; color:var(--text-primary,#050505); margin:0; }

/* ── Quick actions ── */
.vd-quick-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:12px; }
.vd-qa-btn     { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:18px 12px; background:var(--bg-surface,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:14px; text-decoration:none; color:var(--text-secondary,#2D3748); transition:all .2s; animation:vd-fade-up .45s both; }
.vd-qa-btn:hover { border-color:#164E8F; background:#f0f5fd; color:#164E8F; transform:translateY(-2px); box-shadow:0 4px 16px rgba(22,78,143,.12); }
.vd-qa-icon    { width:40px; height:40px; border-radius:11px; background:#f0f5fd; display:flex; align-items:center; justify-content:center; color:#164E8F; }
.vd-qa-icon svg { width:18px; height:18px; }
.vd-qa-label   { font-size:11.5px; font-weight:600; text-align:center; line-height:1.3; }

/* ── Charts ── */
.vd-charts-row  { display:grid; grid-template-columns:repeat(auto-fill,minmax(380px,1fr)); gap:18px; margin-bottom:22px; }
.vd-totals-row  { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:22px; }
.vd-tables-row  { display:grid; grid-template-columns:repeat(auto-fill,minmax(440px,1fr)); gap:18px; margin-bottom:22px; }
.vd-chart-card  { background:var(--bg-surface,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:16px; padding:20px 22px; box-shadow:0 1px 3px rgba(10,35,66,.05),0 4px 14px rgba(10,35,66,.07); }
.vd-chart-head  { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
.vd-chart-title { font-family:var(--font-heading,'Inter',sans-serif); font-size:13.5px; font-weight:700; color:var(--text-primary,#050505); }
.vd-chart-body  { position:relative; height:240px; }
.vd-chart-body--donut { height:220px; }
.vd-select { font-size:11.5px; padding:4px 10px 4px 8px; border:1.5px solid var(--border,#E2E8F0); border-radius:7px; background:var(--bg-surface-2,#F8F9FC); color:var(--text-muted,#718096); cursor:pointer; outline:none; transition:all .15s; }
.vd-select:focus { border-color:#164E8F; }
.vd-chip { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; background:#EFF6FF; color:#164E8F; border:1px solid #BFDBFE; }

/* ── Total badges ── */
.vd-total-badge { background:var(--bg-surface,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:12px; padding:12px 20px; display:flex; flex-direction:column; gap:4px; box-shadow:0 1px 4px rgba(10,35,66,.05); min-width:200px; border-{$border_side}:4px solid; }
.vd-total-label { font-size:11.5px; color:var(--text-muted,#718096); font-weight:500; }
.vd-total-value { font-family:var(--font-heading,'Inter',sans-serif); font-size:18px; font-weight:800; letter-spacing:-.02em; }

/* ── Tables ── */
.vd-tbl-wrap  { background:var(--bg-surface,#fff); border:1.5px solid var(--border,#E2E8F0); border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(10,35,66,.05),0 4px 14px rgba(10,35,66,.07); margin-bottom:22px; }
.vd-tbl-head  { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; background:var(--bg-surface-2,#F8F9FC); border-bottom:1.5px solid var(--border,#E2E8F0); }
.vd-tbl-title { font-family:var(--font-heading,'Inter',sans-serif); font-size:13px; font-weight:700; color:var(--text-primary,#050505); }
.vd-view-all  { font-size:12px; font-weight:600; color:#164E8F; text-decoration:none; }
.vd-view-all:hover { text-decoration:underline; }
.vd-tbl-scroll { overflow-x:auto; }
.vd-tbl { width:100%; border-collapse:collapse; }
.vd-tbl th { padding:10px 14px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted,#718096); text-align:{$text_align}; white-space:nowrap; background:var(--bg-surface-2,#F8F9FC); border-bottom:1px solid var(--border,#E2E8F0); }
.vd-tbl td { padding:11px 14px; font-size:12.5px; color:var(--text-secondary,#2D3748); border-bottom:1px solid var(--border-light,#EDF0F7); vertical-align:middle; text-align:{$text_align}; }
.vd-tbl tbody tr:last-child td { border-bottom:none; }
.vd-tbl tbody tr:hover td { background:var(--primary-ultra,#F0F5FD); }
.vd-overdue-row td { background:#FEF2F2 !important; }
.vd-overdue-row:hover td { background:#FEE2E2 !important; }
.vd-right  { text-align:right !important; }
.vd-amount { font-family:var(--font-heading,'Inter',sans-serif); font-weight:700; color:var(--text-primary,#050505); }
.vd-credit { font-family:var(--font-heading,'Inter',sans-serif); font-weight:700; color:#059669; }
.vd-neg    { color:#DC2626 !important; }
.vd-muted  { color:var(--text-muted,#718096); font-size:11.5px; }
.vd-empty  { text-align:center !important; color:var(--text-muted,#718096); padding:28px; font-size:12.5px; }
.vd-ref    { font-family:var(--font-heading,'Inter',sans-serif); font-weight:700; font-size:12px; color:#164E8F; }

/* ── Status & type badges ── */
.vd-status     { display:inline-flex; align-items:center; padding:3px 9px; border-radius:999px; font-size:11px; font-weight:700; }
.vd-st-paid    { background:#ECFDF5; color:#059669; }
.vd-st-overdue { background:#FEF2F2; color:#DC2626; }
.vd-st-pending { background:#FFFBEB; color:#D97706; }
.vd-type-badge { display:inline-flex; font-size:11px; font-weight:600; padding:2px 8px; border-radius:6px; background:#EFF6FF; color:#164E8F; white-space:nowrap; }

/* ── Animation ── */
@keyframes vd-fade-up { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

/* ── RTL overrides ── */
[dir="rtl"] .vd-tbl th  { text-align:right; }
[dir="rtl"] .vd-tbl td  { text-align:right; }
[dir="rtl"] .vd-right   { text-align:left !important; }
[dir="rtl"] .vd-kpi-card::before { right:0; left:0; }
[dir="rtl"] .vd-total-badge { border-left:1.5px solid var(--border,#E2E8F0); border-right:4px solid; }

/* ── Responsive ── */
@media(max-width:1100px){ .vd-charts-row{ grid-template-columns:1fr; } }
@media(max-width:860px) { .vd-quick-grid{ grid-template-columns:repeat(4,1fr); } .vd-tables-row{ grid-template-columns:1fr; } }
@media(max-width:600px) { .vd-kpi-grid,.vd-kpi-strip{ grid-template-columns:1fr 1fr; } .vd-quick-grid{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:400px) { .vd-kpi-grid,.vd-kpi-strip{ grid-template-columns:1fr; } .vd-quick-grid{ grid-template-columns:1fr 1fr; } }
</style>
CSS;
}
