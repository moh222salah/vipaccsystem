<?php
/**********************************************************************
 * VIP Accounting System — Executive Dashboard v4.0
 * All-in-One: No external dashboard.inc required
 * Path: C:\xampp\htdocs\frontaccounting\admin\dashboard.php
 **********************************************************************/
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

/* ── Force UTF-8 so Arabic text displays correctly ── */
if (isset($_SESSION['language'])) {
    $_SESSION['language']->encoding = 'utf-8';
}

$page_security = 'SA_SETUPDISPLAY';

$app = isset($_GET['sel_app'])  ? $_GET['sel_app']
     : (isset($_POST['sel_app']) ? $_POST['sel_app'] : "all");

/* ── AJAX refresh ── */
if (get_post('id')) { _vd_run($app); exit; }

$js = "";
if ($SysPrefs->use_popup_windows) $js .= get_js_open_window(800,500);
page(_($help_context = 'Dashboard'), false, false, '', $js);
_vd_run($app);
end_page();

/* ══════════════════════════════════════════════════════
   MAIN ENTRY
══════════════════════════════════════════════════════ */
function _vd_run($sel_app)
{
    /* UTF-8 fix — Arabic text */
    db_query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
    db_query("SET CHARACTER SET utf8mb4");

    $rtl = (isset($_SESSION['language']->dir) && $_SESSION['language']->dir === 'rtl');

    echo _vd_styles();
    echo "<script src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'></script>\n";
    echo "<script src='https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js'></script>\n";

    $app_js = addslashes($sel_app);
    echo "<script>
    function chart_update(el,id){
        var xhr=new XMLHttpRequest();
        xhr.onreadystatechange=function(){if(xhr.readyState==4&&xhr.status==200){
            var t=document.createElement('div');t.innerHTML=xhr.responseText;
            var f=t.querySelector('#'+id);if(f)document.getElementById(id).innerHTML=f.innerHTML;
        }};
        xhr.open('POST',location.pathname,true);
        xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
        xhr.send('id='+id+'&'+el.id+'='+el.value+'&sel_app=$app_js');
    }
    </script>\n";

    _vd_display_all($rtl);
}

/* ══════════════════════════════════════════════════════
   DISPLAY ALL — Main Dashboard
══════════════════════════════════════════════════════ */
function _vd_display_all($rtl=false)
{
    $today     = _vd_today();
    $t1        = date2sql($today);
    $mtd_start = date('Y-m-01');                 // First day of current month
    $cur       = get_company_pref('curr_default') ?: 'USD';
    $coy       = get_company_pref('coy_name') ?: 'VIP Acc System';
    $user      = $_SESSION['wa_current_user']->name ?? 'Admin';

    /* ────────────────────────────────────────────────
       KPI DATA QUERIES
    ──────────────────────────────────────────────── */

    /* Today Revenue (Sales Invoices) */
    $r = db_query("SELECT COALESCE(SUM((ov_amount+ov_gst+ov_discount)*rate),0) AS v
                   FROM ".TB_PREF."debtor_trans
                   WHERE type=".ST_SALESINVOICE." AND tran_date='$t1'");
    $rev_today = (float)db_fetch($r)['v'];

    /* Yesterday Revenue (for % change) */
    $yt  = date2sql(add_days($today, -1));
    $r2  = db_query("SELECT COALESCE(SUM((ov_amount+ov_gst+ov_discount)*rate),0) AS v
                     FROM ".TB_PREF."debtor_trans
                     WHERE type=".ST_SALESINVOICE." AND tran_date='$yt'");
    $rev_yest = (float)db_fetch($r2)['v'];
    $rev_pct  = $rev_yest > 0 ? round((($rev_today - $rev_yest) / $rev_yest) * 100, 1) : 0;

    /* MTD Revenue — from GL (Revenue class ctype=4) */
    $r_rev = db_query("SELECT COALESCE(SUM(g.amount*-1),0) AS v
                       FROM ".TB_PREF."gl_trans g
                       JOIN ".TB_PREF."chart_master a ON g.account=a.account_code
                       JOIN ".TB_PREF."chart_types  t ON a.account_type=t.id
                       JOIN ".TB_PREF."chart_class  c ON t.class_id=c.cid
                       WHERE c.ctype=4
                         AND g.tran_date>='$mtd_start' AND g.tran_date<='$t1'");
    $rev_mtd = (float)db_fetch($r_rev)['v'];

    /* MTD Expenses — from GL (Expense class ctype=6) */
    $r_exp = db_query("SELECT COALESCE(SUM(g.amount),0) AS v
                       FROM ".TB_PREF."gl_trans g
                       JOIN ".TB_PREF."chart_master a ON g.account=a.account_code
                       JOIN ".TB_PREF."chart_types  t ON a.account_type=t.id
                       JOIN ".TB_PREF."chart_class  c ON t.class_id=c.cid
                       WHERE c.ctype=6
                         AND g.tran_date>='$mtd_start' AND g.tran_date<='$t1'");
    $exp_mtd = (float)db_fetch($r_exp)['v'];

    $net_profit    = $rev_mtd - $exp_mtd;
    $profit_margin = $rev_mtd > 0 ? round(($net_profit / $rev_mtd) * 100, 1) : 0;

    /* Fiscal Year Revenue & Expenses */
    $fy_start = date2sql(begin_fiscalyear());
    $r_fy_rev = db_query("SELECT COALESCE(SUM(g.amount*-1),0) AS v
                          FROM ".TB_PREF."gl_trans g
                          JOIN ".TB_PREF."chart_master a ON g.account=a.account_code
                          JOIN ".TB_PREF."chart_types  t ON a.account_type=t.id
                          JOIN ".TB_PREF."chart_class  c ON t.class_id=c.cid
                          WHERE c.ctype=4 AND g.tran_date>='$fy_start' AND g.tran_date<='$t1'");
    $fy_rev = (float)db_fetch($r_fy_rev)['v'];

    $r_fy_exp = db_query("SELECT COALESCE(SUM(g.amount),0) AS v
                          FROM ".TB_PREF."gl_trans g
                          JOIN ".TB_PREF."chart_master a ON g.account=a.account_code
                          JOIN ".TB_PREF."chart_types  t ON a.account_type=t.id
                          JOIN ".TB_PREF."chart_class  c ON t.class_id=c.cid
                          WHERE c.ctype=6 AND g.tran_date>='$fy_start' AND g.tran_date<='$t1'");
    $fy_exp = (float)db_fetch($r_fy_exp)['v'];
    $fy_net = $fy_rev - $fy_exp;

    /* AR / AP Balances */
    $ar = _vd_ar_balance($t1);
    $ap = _vd_ap_balance($t1);

    /* Low Stock & Pending Orders */
    $low  = _vd_low_stock_count($t1);
    $r3   = db_query("SELECT COUNT(*) AS c FROM ".TB_PREF."sales_orders WHERE trans_type=".ST_SALESORDER);
    $pend = (int)db_fetch($r3)['c'];

    /* Bank Total Balance (all active bank accounts) */
    $r_bank = db_query("SELECT COALESCE(SUM(bt.amount),0) AS balance
                        FROM ".TB_PREF."bank_trans bt
                        JOIN ".TB_PREF."bank_accounts ba ON bt.bank_act=ba.account_code
                        WHERE ba.inactive=0");
    $bank_balance = (float)db_fetch($r_bank)['balance'];

    /* MTD Bank Deposits & Payments */
    $r4      = db_query("SELECT COALESCE(SUM(amount),0) AS v FROM ".TB_PREF."bank_trans
                         WHERE trans_date>='$mtd_start' AND trans_date<='$t1' AND amount>0");
    $dep_mtd = (float)db_fetch($r4)['v'];

    $r5      = db_query("SELECT COALESCE(SUM(ABS(amount)),0) AS v FROM ".TB_PREF."bank_trans
                         WHERE trans_date>='$mtd_start' AND trans_date<='$t1' AND amount<0");
    $pmt_mtd = (float)db_fetch($r5)['v'];

    /* Counts */
    $r6       = db_query("SELECT COUNT(*) AS c FROM ".TB_PREF."debtors_master WHERE inactive=0");
    $num_cust = (int)db_fetch($r6)['c'];

    $r7       = db_query("SELECT COUNT(*) AS c FROM ".TB_PREF."suppliers WHERE inactive=0");
    $num_supp = (int)db_fetch($r7)['c'];

    $r8        = db_query("SELECT COUNT(*) AS c FROM ".TB_PREF."stock_master WHERE inactive=0 AND mb_flag='B'");
    $num_items = (int)db_fetch($r8)['c'];

    /* Overdue Invoices */
    $r9          = db_query("SELECT COUNT(*) AS c FROM ".TB_PREF."debtor_trans
                             WHERE type=".ST_SALESINVOICE."
                               AND (ov_amount+ov_gst+ov_discount-alloc)>0.001
                               AND due_date < '$t1'");
    $overdue_cnt = (int)db_fetch($r9)['c'];

    /* GL Transaction Count (all-time) */
    $r_gl     = db_query("SELECT COUNT(DISTINCT CONCAT(type,'_',type_no)) AS c FROM ".TB_PREF."gl_trans");
    $gl_count = (int)db_fetch($r_gl)['c'];

    /* Financial Health Score (0-100) */
    $health = _vd_health_score($ar, $ap, $bank_balance, $overdue_cnt, $low, $net_profit);

    /* ────────────────────────────────────────────────
       WELCOME BAR
    ──────────────────────────────────────────────── */
    $now_h  = date('H:i');
    $hour   = (int)date('H');
    $eg_time = gmdate('H:i', time() + 2*3600);
    $eg_ampm = (int)gmdate('H', time() + 2*3600) < 12 ? _('AM') : _('PM');

    /* ── Bilingual greeting — fully in system language ── */
    if ($rtl) {
        $greeting    = $hour < 12 ? 'صباح الخير' : ($hour < 17 ? 'مساء الخير' : 'مساء الخير');
        $dev_label   = 'تطوير بواسطة';
        $dash_label  = 'لوحة التحكم';
        $refresh_lbl = 'تحديث';
        $mtd_label   = 'الشهر حتى الآن';
        $ar_months   = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                        7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
        $mtd_full    = date('d') . ' ' . $ar_months[(int)date('m')] . ' ' . date('Y');
    } else {
        $greeting    = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
        $dev_label   = 'Developed by';
        $dash_label  = 'Dashboard';
        $refresh_lbl = 'Refresh';
        $mtd_label   = 'MTD';
        $mtd_full    = date('d F Y');
    }

    echo "<div class='vd-welcome'>\n";
    echo "  <div class='vd-welcome-left'>\n";
    echo "    <div class='vd-welcome-meta'>$dev_label: <strong>Moh222salah</strong></div>\n";
    echo "    <h2 class='vd-welcome-title'>$greeting, ".htmlspecialchars($user)."</h2>\n";
    echo "    <p class='vd-welcome-sub'>$mtd_label: <strong>$mtd_full</strong> &nbsp;&middot;&nbsp; $eg_time $eg_ampm - EG TIME</p>\n";
    echo "  </div>\n";
    $arr = $rtl ? '&larr;' : '&rarr;';
    $auto_lbl   = $rtl ? 'تحديث تلقائي' : 'Auto';
    $counter_lbl = $rtl ? 'ث' : 's';
    echo "  <div class='vd-welcome-right'>\n";
    echo "    <span id='vd-auto-lbl' style='font-size:11px;color:rgba(255,255,255,.5);margin-top:2px'>$auto_lbl: <span id='vd-countdown'>60</span>$counter_lbl</span>\n";
    echo "    <button onclick='vd_refresh()' class='vd-btn-outline' id='vd-refresh-btn'>"
        ."<svg width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><polyline points='23 4 23 10 17 10'/><path d='M20.49 15a9 9 0 1 1-2.12-9.36L23 10'/></svg> $refresh_lbl"
        ."</button>\n";
    echo "  </div>\n";
    echo "</div>\n";
    /* Internal AJAX Refresh + Auto-Refresh every 60s */
    echo "<script>
var vd_timer=60, vd_interval=null;
function vd_refresh(){
  var btn=document.getElementById('vd-refresh-btn');
  if(!btn)return;
  btn.disabled=true; btn.style.opacity='0.5';
  var xhr=new XMLHttpRequest();
  xhr.onreadystatechange=function(){
    if(xhr.readyState==4){
      btn.disabled=false; btn.style.opacity='1';
      vd_timer=60;
      if(xhr.status==200){ location.reload(); }
    }
  };
  xhr.open('POST',location.pathname,true);
  xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xhr.send('id=vd_refresh&sel_app=all');
}
function vd_start_auto(){
  vd_interval=setInterval(function(){
    vd_timer--;
    var el=document.getElementById('vd-countdown');
    if(el) el.textContent=vd_timer;
    if(vd_timer<=0){ vd_timer=60; vd_refresh(); }
  },1000);
}
document.addEventListener('DOMContentLoaded',vd_start_auto);
</script>\n";

    /* ── ALERT STRIP ── */
    $arr = $rtl ? '&larr;' : '&rarr;';
    if ($overdue_cnt > 0) {
        echo "<div class='vd-alert-strip'>&#9888; <strong>$overdue_cnt</strong> "._('overdue Sales Invoices require attention')." &nbsp;";
        echo "<a href='../sales/inquiry/customer_inquiry.php'>"._('View')." $arr</a></div>\n";
    }
    if ($low > 0) {
        echo "<div class='vd-alert-strip vd-alert-warn'>&#9889; <strong>$low</strong> "._('items below reorder level')." &nbsp;";
        echo "<a href='../inventory/inquiry/stock_inquiry.php'>"._('View')." $arr</a></div>\n";
    }

    /* ────────────────────────────────────────────────
       KPI GRID — 8 cards
    ──────────────────────────────────────────────── */
    $icons = _vd_icons();
    $kpis  = [
        ['label'=>_('Today Revenue'),       'val'=>$cur.' '.number_format($rev_today,0),  'icon'=>'revenue', 'color'=>'#164E8F',
         'chg'=>$rev_pct, 'sub'=>_('vs yesterday')],
        ['label'=>_('Bank Balance'),        'val'=>$cur.' '.number_format($bank_balance,0),'icon'=>'dep',    'color'=>'#10B981',
         'badge'=>_('Cash Position'), 'bc'=>'#059669'],
        ['label'=>_('Accounts Receivable'), 'val'=>$cur.' '.number_format($ar,0),         'icon'=>'ar',      'color'=>'#D4AF37',
         'badge'=>_('Outstanding'), 'bc'=>'#D97706'],
        ['label'=>_('Accounts Payable'),    'val'=>$cur.' '.number_format($ap,0),         'icon'=>'ap',      'color'=>'#EF4444',
         'badge'=>_('Payable'), 'bc'=>'#EF4444'],
        ['label'=>_('MTD Deposits'),        'val'=>$cur.' '.number_format($dep_mtd,0),    'icon'=>'dep',     'color'=>'#8B5CF6',
         'chg'=>0, 'sub'=>_('this month')],
        ['label'=>_('MTD Payments'),        'val'=>$cur.' '.number_format($pmt_mtd,0),    'icon'=>'pmt',     'color'=>'#0EA5E9',
         'chg'=>0, 'sub'=>_('this month')],
        ['label'=>_('Low Stock Items'),     'val'=>(string)$low,                          'icon'=>'stock',   'color'=>'#F59E0B',
         'badge'=>_('Alert'), 'bc'=>'#EF4444'],
        ['label'=>_('Customers'),           'val'=>(string)$num_cust,                     'icon'=>'cust',    'color'=>'#6366F1',
         'badge'=>_('Active'), 'bc'=>'#10B981'],
    ];

    echo "<div class='vd-kpi-grid'>\n";
    foreach ($kpis as $i => $k) {
        $ic    = $icons[$k['icon']] ?? $icons['default'];
        $delay = round($i * 0.06, 2);
        $badge_html = '';
        if (isset($k['chg'])) {
            $pos        = $k['chg'] >= 0;
            $badge_html = "<span class='vd-chg ".($pos?'pos':'neg')."'>".($pos?'&uarr;':'&darr;').abs($k['chg'])."%</span>";
        } elseif (isset($k['badge'])) {
            $badge_html = "<span class='vd-badge' style='color:".htmlspecialchars($k['bc'])."'>".htmlspecialchars($k['badge'])."</span>";
        }
        echo "<div class='vd-kpi-card' style='--kc:".htmlspecialchars($k['color']).";animation-delay:{$delay}s'>\n";
        echo "  <div class='vd-kpi-top'><span class='vd-kpi-icon'>$ic</span>$badge_html</div>\n";
        echo "  <div class='vd-kpi-val'>".htmlspecialchars($k['val'])."</div>\n";
        echo "  <div class='vd-kpi-lbl'>".htmlspecialchars($k['label'])."</div>\n";
        if (isset($k['sub'])) echo "  <div class='vd-kpi-sub'>".htmlspecialchars($k['sub'])."</div>\n";
        echo "</div>\n";
    }
    echo "</div>\n";

    /* ────────────────────────────────────────────────
       MONTH NAME — Bilingual
    ──────────────────────────────────────────────── */
    $ar_months_full = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                       7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
    $month_name = $rtl
        ? ($ar_months_full[(int)date('m')] . ' ' . date('Y'))
        : date('F Y');

    /* ────────────────────────────────────────────────
       FINANCIAL SNAPSHOT — P&L + FY Summary
    ──────────────────────────────────────────────── */
    _vd_financial_snapshot($cur, $rev_mtd, $exp_mtd, $net_profit, $profit_margin,
                           $fy_rev, $fy_exp, $fy_net, $bank_balance, $ar, $ap, $month_name);

    /* ── QUICK ACTIONS ── */
    _vd_quick_actions($rtl);

    /* ── CHARTS ROW ── */
    echo "<div class='vd-charts-row'>\n";
    _vd_revenue_chart($today, $rtl);
    _vd_sales_dist_chart($today, $rtl);
    echo "</div>\n";

    /* ── SECONDARY CHARTS ── */
    echo "<div class='vd-charts-row'>\n";
    _vd_cashflow_chart($today, $rtl);
    _vd_ar_ap_chart($today, $rtl);
    echo "</div>\n";

    /* ── BANK ACCOUNTS ── */
    _vd_bank_accounts_table($cur, $rtl);

    /* ── TABLES ── */
    _vd_gl_journal_table($t1, $rtl);
    _vd_recent_transactions($t1, $cur, $rtl);

    echo "<div class='vd-tables-row'>\n";
    _vd_top_customers($today, $rtl);
    _vd_top_suppliers($today, $rtl);
    echo "</div>\n";

    /* ── LOW STOCK TABLE ── */
    _vd_low_stock_table($t1, $rtl);

    /* ── FOOTER STATS ── */
    echo "<div class='vd-footer-stats'>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$num_items</span><span class='vd-fs-lbl'>"._('Total Items')."</span></div>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$num_cust</span><span class='vd-fs-lbl'>"._('Customers')."</span></div>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$num_supp</span><span class='vd-fs-lbl'>"._('Suppliers')."</span></div>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$overdue_cnt</span><span class='vd-fs-lbl'>"._('Overdue Invoices')."</span></div>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$low</span><span class='vd-fs-lbl'>"._('Low Stock Items')."</span></div>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$pend</span><span class='vd-fs-lbl'>"._('Open Orders')."</span></div>\n";
    echo "  <div class='vd-fs-item'><span class='vd-fs-num'>$gl_count</span><span class='vd-fs-lbl'>"._('GL Transactions')."</span></div>\n";
    echo "</div>\n";
}

/* ══════════════════════════════════════════════════════
   FINANCIAL HEALTH SCORE
══════════════════════════════════════════════════════ */
function _vd_health_score($ar, $ap, $bank, $overdue, $low_stock, $net_profit)
{
    $score = 100;
    /* Deduct for overdue invoices */
    if ($overdue > 0)  $score -= min(25, $overdue * 5);
    /* Deduct for low stock */
    if ($low_stock > 0) $score -= min(20, $low_stock * 4);
    /* Deduct if AP > AR (more owed than owed to us) */
    if ($ap > 0 && $ar < $ap) $score -= 15;
    /* Deduct if negative cash */
    if ($bank < 0) $score -= 20;
    /* Deduct if net loss this month */
    if ($net_profit < 0) $score -= 20;
    return max(0, min(100, $score));
}

/* ══════════════════════════════════════════════════════
   FINANCIAL SNAPSHOT — P&L Widget
══════════════════════════════════════════════════════ */
function _vd_financial_snapshot($cur, $rev_mtd, $exp_mtd, $net, $margin,
                                $fy_rev, $fy_exp, $fy_net, $bank, $ar, $ap, $month_name)
{
    $net_cls   = $net >= 0   ? 'vd-snap-pos' : 'vd-snap-neg';
    $fy_cls    = $fy_net >= 0 ? 'vd-snap-pos' : 'vd-snap-neg';
    $liq       = $bank + $ar - $ap;   // Quick liquidity indicator
    $liq_cls   = $liq >= 0  ? 'vd-snap-pos' : 'vd-snap-neg';

    /* MTD progress bar: expenses as % of revenue */
    $exp_pct = $rev_mtd > 0 ? min(100, round(($exp_mtd / $rev_mtd) * 100)) : 0;

    echo "<div class='vd-snapshot-grid'>\n";

    /* Card 1: MTD P&L */
    echo "<div class='vd-snap-card'>\n";
    echo "  <div class='vd-snap-head'>\n";
    echo "    <span class='vd-snap-title'>"._('Monthly P&L')."</span>\n";
    echo "    <span class='vd-chip'>".htmlspecialchars($month_name)."</span>\n";
    echo "  </div>\n";
    echo "  <div class='vd-snap-rows'>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('Revenue')."</span><span class='vd-snap-val vd-snap-pos'>$cur ".number_format($rev_mtd,2)."</span></div>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('Expenses')."</span><span class='vd-snap-val vd-snap-neg'>$cur ".number_format($exp_mtd,2)."</span></div>\n";
    echo "    <div class='vd-snap-divider'></div>\n";
    echo "    <div class='vd-snap-row vd-snap-total'><span class='vd-snap-lbl'>"._('Net Profit / Loss')."</span><span class='vd-snap-val $net_cls'>$cur ".number_format($net,2)."</span></div>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('Profit Margin')."</span><span class='vd-snap-val $net_cls'>$margin%</span></div>\n";
    echo "  </div>\n";
    if ($rev_mtd > 0) {
        echo "  <div class='vd-snap-bar-lbl'>"._('Expenses / Revenue ratio')."</div>\n";
        echo "  <div class='vd-pbar' style='height:8px;margin-top:6px'><div class='vd-pbar-fill' style='width:{$exp_pct}%'></div></div>\n";
        echo "  <div class='vd-snap-bar-pct'>$exp_pct%</div>\n";
    }
    echo "</div>\n";

    /* Card 2: Fiscal Year Summary */
    echo "<div class='vd-snap-card'>\n";
    echo "  <div class='vd-snap-head'>\n";
    echo "    <span class='vd-snap-title'>"._('Fiscal Year Summary')."</span>\n";
    echo "    <span class='vd-chip'>YTD</span>\n";
    echo "  </div>\n";
    echo "  <div class='vd-snap-rows'>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('YTD Revenue')."</span><span class='vd-snap-val vd-snap-pos'>$cur ".number_format($fy_rev,2)."</span></div>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('YTD Expenses')."</span><span class='vd-snap-val vd-snap-neg'>$cur ".number_format($fy_exp,2)."</span></div>\n";
    echo "    <div class='vd-snap-divider'></div>\n";
    echo "    <div class='vd-snap-row vd-snap-total'><span class='vd-snap-lbl'>"._('YTD Net Profit')."</span><span class='vd-snap-val $fy_cls'>$cur ".number_format($fy_net,2)."</span></div>\n";
    echo "  </div>\n";
    /* Gauge-style bar for YTD */
    $fy_exp_pct = $fy_rev > 0 ? min(100, round(($fy_exp / $fy_rev) * 100)) : 0;
    echo "  <div class='vd-snap-bar-lbl'>"._('YTD Expenses / Revenue ratio')."</div>\n";
    echo "  <div class='vd-pbar' style='height:8px;margin-top:6px'><div class='vd-pbar-fill' style='width:{$fy_exp_pct}%'></div></div>\n";
    echo "  <div class='vd-snap-bar-pct'>$fy_exp_pct%</div>\n";
    echo "</div>\n";

    /* Card 3: Liquidity Position */
    echo "<div class='vd-snap-card'>\n";
    echo "  <div class='vd-snap-head'>\n";
    echo "    <span class='vd-snap-title'>"._('Liquidity Position')."</span>\n";
    echo "    <span class='vd-chip'>"._('Working Capital')."</span>\n";
    echo "  </div>\n";
    echo "  <div class='vd-snap-rows'>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('Bank Cash')."</span><span class='vd-snap-val vd-snap-pos'>$cur ".number_format($bank,2)."</span></div>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('Receivables (AR)')."</span><span class='vd-snap-val vd-snap-pos'>$cur ".number_format($ar,2)."</span></div>\n";
    echo "    <div class='vd-snap-row'><span class='vd-snap-lbl'>"._('Payables (AP)')."</span><span class='vd-snap-val vd-snap-neg'>$cur ".number_format($ap,2)."</span></div>\n";
    echo "    <div class='vd-snap-divider'></div>\n";
    echo "    <div class='vd-snap-row vd-snap-total'><span class='vd-snap-lbl'>"._('Net Liquid Position')."</span><span class='vd-snap-val $liq_cls'>$cur ".number_format($liq,2)."</span></div>\n";
    echo "  </div>\n";
    /* Current Ratio indicator */
    $curr_ratio = $ap > 0 ? round(($bank + $ar) / $ap, 2) : '&infin;';
    echo "  <div class='vd-snap-row' style='margin-top:10px;padding-top:10px;border-top:1px dashed #E2E8F0'>\n";
    echo "    <span class='vd-snap-lbl'>"._('Current Ratio')."</span><span class='vd-snap-val ".($ap>0&&($bank+$ar)>=$ap?'vd-snap-pos':'vd-snap-neg')."'>$curr_ratio</span>\n";
    echo "  </div>\n";
    echo "</div>\n";

    echo "</div>\n"; /* end vd-snapshot-grid */
}

/* ══════════════════════════════════════════════════════
   BANK ACCOUNTS TABLE
══════════════════════════════════════════════════════ */
function _vd_bank_accounts_table($cur, $rtl=false)
{
    $sql = "SELECT ba.bank_name, ba.bank_account_name, ba.bank_curr_code AS currency_code,
                   COALESCE(SUM(bt.amount),0) AS balance
            FROM ".TB_PREF."bank_accounts ba
            LEFT JOIN ".TB_PREF."bank_trans bt ON bt.bank_act=ba.account_code
            WHERE ba.inactive=0
            GROUP BY ba.account_code
            ORDER BY balance DESC";
    $r = db_query($sql);
    if (!$r || db_num_rows($r) == 0) return;

    $rows = '';
    $total = 0.0;
    while ($row = db_fetch($r)) {
        $bal   = (float)$row['balance'];
        $total += $bal;
        $cls   = $bal >= 0 ? 'vd-snap-pos' : 'vd-snap-neg';
        $rows .= "<tr>"
               . "<td><strong>".htmlspecialchars($row['bank_account_name'])."</strong></td>"
               . "<td class='vd-muted'>".htmlspecialchars($row['bank_name'])."</td>"
               . "<td class='vd-muted'>".htmlspecialchars($row['currency_code'])."</td>"
               . "<td class='vd-r $cls' style='font-weight:700'>".number_format($bal,2)."</td>"
               . "</tr>\n";
    }
    $total_lbl = $rtl ? 'إجمالي الوضع النقدي' : _('Total Cash Position');
    $rows .= "<tr style='background:#F0F5FD'>"
           . "<td colspan='3' style='font-weight:700;text-align:right;padding-right:20px'>$total_lbl</td>"
           . "<td class='vd-r' style='font-weight:800;color:var(--primary)'>$cur ".number_format($total,2)."</td>"
           . "</tr>\n";

    _vd_table(_('Bank Accounts - Cash Position'),
        [_('Account Name'),_('Bank'),_('Currency'),_('Balance')],
        $rows);
}

/* ══════════════════════════════════════════════════════
   CHARTS
══════════════════════════════════════════════════════ */
function _vd_revenue_chart($today, $rtl=false)
{
    global $SysPrefs;
    $months = isset($_POST['per_rev']) ? (int)$_POST['per_rev'] : 6;
    $begin  = date2sql(begin_fiscalyear());
    $t1     = date2sql($today);
    $sep    = $SysPrefs->dateseps[user_date_sep()];

    $sql = "SELECT month_name, sales, costs FROM (
              SELECT DATE_FORMAT(tran_date,'%Y-%m') AS month_name,
                     SUM(IF(c.ctype=4, amount*-1, 0)) AS sales,
                     SUM(IF(c.ctype=6, amount, 0))    AS costs
              FROM ".TB_PREF."gl_trans
              JOIN ".TB_PREF."chart_master a ON account=a.account_code
              JOIN ".TB_PREF."chart_types  t ON a.account_type=t.id
              JOIN ".TB_PREF."chart_class  c ON t.class_id=c.cid
              WHERE (c.ctype=4 OR c.ctype=6)
                AND tran_date>='$begin' AND tran_date<='$t1'
              GROUP BY month_name ORDER BY month_name DESC LIMIT $months
            ) b ORDER BY month_name ASC";

    $rr = db_query($sql);
    $mn=$s=$c=[];
    while ($row=db_fetch($rr)) {
        $mn[] = _vd_format_month($row['month_name'], $rtl);
        $s[]  = round((float)$row['sales']);
        $c[]  = round((float)$row['costs']);
    }

    $months_lbl = $rtl ? 'أشهر' : _('Months');
    echo "<div class='vd-chart-card' id='vd_rev'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>"._('Monthly Revenue vs Expenses')."</span>\n";
    echo "  <select id='per_rev' onchange='chart_update(this,\"vd_rev\")' class='vd-select'>\n";
    foreach([3,4,5,6,7,8] as $n) echo "<option value='$n'".($months==$n?' selected':'').">$n $months_lbl</option>\n";
    echo "  </select></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_rev'></canvas></div>\n</div>\n";

    $lj=json_encode($mn,JSON_UNESCAPED_UNICODE); $sj=json_encode($s); $cj=json_encode($c);
    $ls=addslashes(_('Revenue')); $lc=addslashes(_('Expenses'));
    echo "<script>
(function(){var el=document.getElementById('chart_rev');if(!el)return;
  new Chart(el,{type:'line',data:{labels:$lj,datasets:[
    {label:'$ls',data:$sj,borderColor:'#164E8F',backgroundColor:'rgba(22,78,143,0.09)',fill:true,borderWidth:2.5,pointRadius:5,pointBackgroundColor:'#164E8F',tension:0.4},
    {label:'$lc',data:$cj,borderColor:'#D4AF37',backgroundColor:'rgba(212,175,55,0.07)',fill:true,borderWidth:2,pointRadius:4,pointBackgroundColor:'#D4AF37',tension:0.4}
  ]},
  options:{responsive:true,maintainAspectRatio:false,
    plugins:{legend:{position:'top',labels:{font:{family:\"'DM Sans',sans-serif\",size:12},color:'#4A5568',boxWidth:12,usePointStyle:true}},
             tooltip:{backgroundColor:'#0A2342',cornerRadius:8,padding:14}},
    scales:{x:{grid:{color:'#EDF0F7'},ticks:{font:{size:11},color:'#718096'}},
            y:{grid:{color:'#EDF0F7'},ticks:{font:{size:11},color:'#718096',callback:function(v){return v>=1000?(v/1000).toFixed(0)+'K':v;}}}}}
  });
})();
</script>\n";
}

function _vd_sales_dist_chart($today, $rtl=false)
{
    $begin=date2sql(begin_fiscalyear()); $t1=date2sql($today);
    $sql="SELECT sc.description, COALESCE(SUM((dtd.unit_price*dtd.quantity)*d.rate),0) AS total
          FROM ".TB_PREF."debtor_trans_details dtd
          JOIN ".TB_PREF."stock_master sm ON dtd.stock_id=sm.stock_id
          JOIN ".TB_PREF."stock_category sc ON sm.category_id=sc.category_id
          JOIN ".TB_PREF."debtor_trans d ON dtd.debtor_trans_type=d.type AND dtd.debtor_trans_no=d.trans_no
          WHERE d.type IN(".ST_SALESINVOICE.",".ST_CUSTCREDIT.")
            AND d.tran_date>='$begin' AND d.tran_date<='$t1'
          GROUP BY sc.category_id ORDER BY total DESC LIMIT 8";
    $rr=db_query($sql); $names=[]; $vals=[];
    while($row=db_fetch($rr)){ $names[]=htmlspecialchars_decode($row['description']); $vals[]=round((float)$row['total']); }
    if(empty($vals)){ $names=[_('No Data')]; $vals=[1]; }

    echo "<div class='vd-chart-card'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>"._('Sales Distribution by Category')."</span><span class='vd-chip'>"._('Fiscal Year')."</span></div>\n";
    echo "  <div class='vd-chart-body vd-chart-body--donut'><canvas id='chart_dist'></canvas></div>\n</div>\n";

    $lj=json_encode($names,JSON_UNESCAPED_UNICODE); $dj=json_encode($vals);
    echo "<script>
(function(){var el=document.getElementById('chart_dist');if(!el)return;
  new Chart(el,{type:'doughnut',data:{labels:$lj,datasets:[{data:$dj,
    backgroundColor:['#164E8F','#10B981','#D4AF37','#F59E0B','#8B5CF6','#3B82F6','#EF4444','#0EA5E9'],
    borderWidth:3,borderColor:'#fff',hoverOffset:12}]},
  options:{responsive:true,maintainAspectRatio:false,cutout:'65%',
    plugins:{legend:{position:'bottom',labels:{font:{size:11},color:'#718096',boxWidth:10,padding:12,usePointStyle:true}},
             tooltip:{backgroundColor:'#0A2342',cornerRadius:8,padding:12}}}});
})();
</script>\n";
}

function _vd_cashflow_chart($today, $rtl=false)
{
    global $SysPrefs;
    $months = isset($_POST['per_cf'])?(int)$_POST['per_cf']:6;
    $t1     = date2sql($today);
    $begin  = date('Y-m-d', strtotime("-".($months-1)." months", strtotime(date('Y-m-01'))));

    $sql="SELECT DATE_FORMAT(trans_date,'%Y-%m') AS mo,
                 SUM(IF(amount>0,amount,0)) AS deposits,
                 SUM(IF(amount<0,ABS(amount),0)) AS payments
          FROM ".TB_PREF."bank_trans
          WHERE trans_date>='$begin' AND trans_date<='$t1'
          GROUP BY mo ORDER BY mo ASC";
    $rr=db_query($sql); $mn=$d=$p=[];
    while($row=db_fetch($rr)){
        $mn[] = _vd_format_month($row['mo'], $rtl);
        $d[]  = round((float)$row['deposits']);
        $p[]  = round((float)$row['payments']);
    }

    $months_lbl = $rtl ? 'أشهر' : _('Months');
    echo "<div class='vd-chart-card' id='vd_cf'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>"._('Cash Flow')."</span>\n";
    echo "  <select id='per_cf' onchange='chart_update(this,\"vd_cf\")' class='vd-select'>\n";
    foreach([3,4,5,6,7,8] as $n) echo "<option value='$n'".($months==$n?' selected':'').">$n $months_lbl</option>\n";
    echo "  </select></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_cf'></canvas></div>\n</div>\n";

    $lj=json_encode($mn,JSON_UNESCAPED_UNICODE); $dj=json_encode($d); $pj=json_encode($p);
    $ld=addslashes(_('Deposits')); $lp=addslashes(_('Payments'));
    echo "<script>
(function(){var el=document.getElementById('chart_cf');if(!el)return;
  new Chart(el,{type:'bar',data:{labels:$lj,datasets:[
    {label:'$ld',data:$dj,backgroundColor:'rgba(16,185,129,0.75)',borderColor:'#10B981',borderWidth:1.5,borderRadius:5},
    {label:'$lp',data:$pj,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#EF4444',borderWidth:1.5,borderRadius:5}
  ]},
  options:{responsive:true,maintainAspectRatio:false,
    plugins:{legend:{position:'top',labels:{font:{size:12},color:'#4A5568',boxWidth:12,usePointStyle:true}},
             tooltip:{backgroundColor:'#0A2342',cornerRadius:8,padding:12}},
    scales:{x:{grid:{color:'#EDF0F7'},ticks:{font:{size:11},color:'#718096'}},
            y:{grid:{color:'#EDF0F7'},ticks:{font:{size:11},color:'#718096',callback:function(v){return v>=1000?(v/1000)+'K':v;}}}}}});
})();
</script>\n";
}

function _vd_ar_ap_chart($today, $rtl=false)
{
    $t1 = date2sql($today);
    $ar = max(0, _vd_ar_balance($t1));
    $ap = max(0, _vd_ap_balance($t1));

    /* Aging AR */
    $p1 = (int)(get_company_pref('past_due_days') ?: 30);
    $p2 = $p1*2;
    $sql="SELECT
           SUM(IF(DATEDIFF('$t1',due_date)<=0,(ov_amount+ov_gst+ov_freight+ov_discount-alloc)*rate,0)) AS current_,
           SUM(IF(DATEDIFF('$t1',due_date) BETWEEN 1 AND $p1,(ov_amount+ov_gst+ov_freight+ov_discount-alloc)*rate,0)) AS p1,
           SUM(IF(DATEDIFF('$t1',due_date) BETWEEN ".($p1+1)." AND $p2,(ov_amount+ov_gst+ov_freight+ov_discount-alloc)*rate,0)) AS p2,
           SUM(IF(DATEDIFF('$t1',due_date)>$p2,(ov_amount+ov_gst+ov_freight+ov_discount-alloc)*rate,0)) AS p3
          FROM ".TB_PREF."debtor_trans
          WHERE type=".ST_SALESINVOICE." AND (ov_amount+ov_gst+ov_freight+ov_discount-alloc)>0.001";
    $row = db_fetch(db_query($sql));
    $days_lbl = $rtl ? 'يوم' : _('days');
    $curr_lbl = $rtl ? 'جاري'  : _('Current');
    $ag_labels = json_encode([$curr_lbl, "1-$p1 $days_lbl", ($p1+1)."-$p2 $days_lbl", "$p2+ $days_lbl"], JSON_UNESCAPED_UNICODE);
    $ag_data   = json_encode([round((float)$row['current_']), round((float)$row['p1']), round((float)$row['p2']), round((float)$row['p3'])]);

    echo "<div class='vd-chart-card'>\n";
    echo "  <div class='vd-chart-head'><span class='vd-chart-title'>"._('AR Aging Analysis')."</span><span class='vd-chip'>"._('Receivables')."</span></div>\n";
    echo "  <div class='vd-chart-body'><canvas id='chart_aging'></canvas></div>\n</div>\n";

    echo "<script>
(function(){var el=document.getElementById('chart_aging');if(!el)return;
  new Chart(el,{type:'bar',data:{labels:$ag_labels,datasets:[{
    data:$ag_data,
    backgroundColor:['rgba(22,78,143,0.8)','rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(139,92,246,0.8)'],
    borderWidth:0,borderRadius:6}]},
  options:{responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false},tooltip:{backgroundColor:'#0A2342',cornerRadius:8,padding:12}},
    scales:{x:{grid:{display:false},ticks:{font:{size:11},color:'#718096'}},
            y:{grid:{color:'#EDF0F7'},ticks:{font:{size:11},color:'#718096',callback:function(v){return v>=1000?(v/1000)+'K':v;}}}}}});
})();
</script>\n";
}

/* ══════════════════════════════════════════════════════
   TABLES
══════════════════════════════════════════════════════ */
function _vd_gl_journal_table($t1, $rtl=false)
{
    $today_sql = date('Y-m-d');

    /* ── Stats bar: today's GL activity ── */
    $sq = "SELECT COUNT(DISTINCT j.trans_no) AS cnt,
                  COALESCE(SUM(IF(g.amount>0,g.amount,0)),0) AS tot_dr,
                  COALESCE(SUM(IF(g.amount<0,ABS(g.amount),0)),0) AS tot_cr
           FROM ".TB_PREF."journal j
           LEFT JOIN ".TB_PREF."gl_trans g ON g.type=j.type AND g.type_no=j.trans_no
           WHERE j.tran_date='$today_sql'";
    $sr     = db_fetch(db_query($sq));
    $s_cnt  = (int)($sr['cnt']??0);
    $s_dr   = (float)($sr['tot_dr']??0);
    $s_cr   = (float)($sr['tot_cr']??0);
    $s_ok   = abs($s_dr - $s_cr) < 0.01;

    /* ── Main query: transaction-level grouping ── */
    $sql = "SELECT j.tran_date, j.type, j.trans_no, j.reference, j.currency,
                   COALESCE(SUM(IF(g.amount>0,g.amount,0)),0) AS total_debit,
                   COALESCE(SUM(IF(g.amount<0,ABS(g.amount),0)),0) AS total_credit,
                   COUNT(g.counter) AS line_count,
                   CONVERT(MIN(NULLIF(g.memo_,'')) USING utf8) AS memo_
            FROM ".TB_PREF."journal j
            LEFT JOIN ".TB_PREF."gl_trans g ON g.type=j.type AND g.type_no=j.trans_no
            WHERE j.tran_date<='$t1'
            GROUP BY j.type, j.trans_no, j.tran_date, j.reference, j.currency
            ORDER BY j.tran_date DESC, j.trans_no DESC
            LIMIT 12";
    $r = db_query($sql);

    $type_cfg = [
        ST_JOURNAL      => [_('Journal Entry'),    'vd-tb-journal'],
        ST_BANKPAYMENT  => [_('Bank Payment'),     'vd-tb-payment'],
        ST_BANKDEPOSIT  => [_('Bank Deposit'),     'vd-tb-deposit'],
        ST_BANKTRANSFER => [_('Bank Transfer'),    'vd-tb-transfer'],
        ST_SALESINVOICE => [_('Sales Invoice'),    'vd-tb-sales'],
        ST_CUSTCREDIT   => [_('Credit Note'),      'vd-tb-sales'],
        ST_CUSTPAYMENT  => [_('Receipt'),          'vd-tb-deposit'],
        ST_SUPPINVOICE  => [_('Purchase Invoice'), 'vd-tb-purchase'],
        ST_SUPPCREDIT   => [_('Supp. Credit'),     'vd-tb-purchase'],
        ST_SUPPAYMENT   => [_('Supp. Payment'),    'vd-tb-payment'],
    ];

    /* ── Stats bar HTML ── */
    $b_cls  = $s_ok ? 'vd-gl-bal-ok' : 'vd-gl-bal-err';
    $b_ico  = $s_ok ? '&#10003;' : '&#33;';
    $b_lbl  = $s_ok ? _('Balanced') : _('Unbalanced');
    $stats  = "<div class='vd-gl-stats-bar'>"
            . "<div class='vd-gl-stat'><span class='vd-gl-stat-num'>$s_cnt</span><span class='vd-gl-stat-lbl'>"._("Today's Entries")."</span></div>"
            . "<div class='vd-gl-stat'><span class='vd-gl-stat-num vd-gl-dr'>".number_format($s_dr,2)."</span><span class='vd-gl-stat-lbl'>"._('Total Debit')."</span></div>"
            . "<div class='vd-gl-stat'><span class='vd-gl-stat-num vd-gl-cr'>".number_format($s_cr,2)."</span><span class='vd-gl-stat-lbl'>"._('Total Credit')."</span></div>"
            . "<div class='vd-gl-stat'><span class='$b_cls'>$b_ico $b_lbl</span></div>"
            . "</div>";

    /* ── Table rows ── */
    $rows = '';
    while ($row = db_fetch($r)) {
        $type = (int)$row['type'];
        [$tlbl, $tcls] = $type_cfg[$type] ?? [_('Entry').' '.$type, 'vd-tb-journal'];
        $dr  = (float)$row['total_debit'];
        $cr  = (float)$row['total_credit'];
        $ok  = abs($dr - $cr) < 0.01;
        $ind = $ok
            ? "<span class='vd-gl-ind vd-gl-ind-ok' title='"._('Balanced')."'>&#10003;</span>"
            : "<span class='vd-gl-ind vd-gl-ind-err' title='"._('Unbalanced')."'>&#33;</span>";
        $ref   = htmlspecialchars($row['reference'] ?: '#'.$row['trans_no']);
        $memo  = htmlspecialchars(mb_substr($row['memo_']??'', 0, 36));
        $lines = (int)$row['line_count'];
        $rows .= "<tr>"
               . "<td class='vd-gl-date'>".sql2date($row['tran_date'])."</td>"
               . "<td><span class='vd-gl-type-badge $tcls'>".htmlspecialchars($tlbl)."</span></td>"
               . "<td><span class='vd-gl-ref'>$ref</span></td>"
               . "<td class='vd-r vd-amt'>".number_format($dr,2)."</td>"
               . "<td class='vd-r vd-crd'>".number_format($cr,2)."</td>"
               . "<td class='vd-gl-center'>$ind</td>"
               . "<td class='vd-gl-center'><span class='vd-lines-pill'>$lines</span></td>"
               . "<td class='vd-muted'>$memo</td>"
               . "</tr>\n";
    }
    if (!$rows)
        $rows = "<tr><td colspan='8' class='vd-empty'>"._('No entries')."</td></tr>";

    $arr       = $rtl ? '&larr;' : '&rarr;';
    $title     = _('Latest Journal Entries')." <small class='vd-gl-subtitle'>("._('General Ledger').")</small>";
    $hdrs_html = '';
    foreach ([_('Date'),_('Type'),_('Reference'),_('Debit'),_('Credit'),_('Bal.'),_('Lines'),_('Description')] as $h)
        $hdrs_html .= "<th>$h</th>";

    $footer = "<a href='../gl/inquiry/gl_inquiry.php' class='vd-gl-foot-link'>"._('Journal Inquiry')."</a>"
            . "<a href='../gl/reports/gl_trial_balance.php' class='vd-gl-foot-link'>"._('Trial Balance')."</a>"
            . "<a href='../gl/inquiry/gl_inquiry.php' class='vd-gl-foot-link vd-gl-foot-all'>"._('View All')." $arr</a>";

    echo "<div class='vd-gl-wrap'>\n";
    echo "  <div class='vd-gl-header'>\n";
    echo "    <div class='vd-gl-title-row'>\n";
    echo "      <span class='vd-gl-title'>$title</span>\n";
    echo "      <div class='vd-gl-header-actions'>\n";
    echo "        <a href='../gl/gl_journal.php' class='vd-gl-btn-new'>"._('+ New Entry')."</a>\n";
    echo "        <a href='../gl/inquiry/gl_inquiry.php' class='vd-gl-btn-inq'>"._('Inquiry')."</a>\n";
    echo "      </div>\n";
    echo "    </div>\n";
    echo $stats;
    echo "  </div>\n";
    echo "  <div class='vd-tbl-scroll'>\n";
    echo "  <table class='vd-tbl vd-gl-tbl'><thead><tr>$hdrs_html</tr></thead><tbody>$rows</tbody></table>\n";
    echo "  </div>\n";
    echo "  <div class='vd-gl-footer'>$footer</div>\n";
    echo "</div>\n";
}

function _vd_recent_transactions($t1, $cur, $rtl=false)
{
    $sql="(SELECT dt.trans_no, dt.reference, dt.tran_date, dt.type,
                  CONVERT(d.name USING utf8) AS party, 'sales' AS src,
                  (dt.ov_amount+dt.ov_gst+dt.ov_discount)*dt.rate AS amount,
                  dt.alloc, dt.due_date
           FROM ".TB_PREF."debtor_trans dt
           JOIN ".TB_PREF."debtors_master d ON dt.debtor_no=d.debtor_no
           WHERE dt.type IN(".ST_SALESINVOICE.",".ST_CUSTCREDIT.",".ST_CUSTPAYMENT.")
             AND dt.tran_date<='$t1'
           ORDER BY dt.tran_date DESC LIMIT 5)
          UNION ALL
          (SELECT st.trans_no, CONVERT(st.reference USING utf8), st.tran_date, st.type,
                  CONVERT(s.supp_name USING utf8), 'purchase',
                  (st.ov_amount+st.ov_gst+st.ov_discount)*st.rate,
                  st.alloc, st.due_date
           FROM ".TB_PREF."supp_trans st
           JOIN ".TB_PREF."suppliers s ON st.supplier_id=s.supplier_id
           WHERE st.tran_date<='$t1'
           ORDER BY st.tran_date DESC LIMIT 5)
          ORDER BY tran_date DESC LIMIT 10";
    $r=db_query($sql);

    $type_map=[
        ST_SALESINVOICE=>_('Sales Invoice'), ST_CUSTCREDIT=>_('Credit Note'),
        ST_CUSTPAYMENT=>_('Customer Receipt'), ST_SUPPINVOICE=>_('Purchase Invoice'),
        ST_SUPPCREDIT=>_('Supplier Credit'),
    ];
    $rows='';
    while($row=db_fetch($r)){
        $amt=(float)$row['amount']; $alloc=(float)$row['alloc']; $due=$row['due_date'];
        if($alloc>=$amt-0.001){ $st=_('Paid');    $sc='vd-st-paid'; }
        elseif($due&&$due<$t1){ $st=_('Overdue'); $sc='vd-st-overdue'; }
        else                  { $st=_('Pending'); $sc='vd-st-pending'; }
        $type_lbl=$type_map[(int)$row['type']]??_('Entry');
        $rows.="<tr>"
              ."<td><span class='vd-ref'>".htmlspecialchars($row['reference'])."</span></td>"
              ."<td>".htmlspecialchars(mb_substr($row['party'],0,30))."</td>"
              ."<td><span class='vd-type-badge'>".htmlspecialchars($type_lbl)."</span></td>"
              ."<td class='vd-r vd-amt'>$cur ".number_format(abs($amt),0)."</td>"
              ."<td>".sql2date($row['tran_date'])."</td>"
              ."<td><span class='vd-status $sc'>".htmlspecialchars($st)."</span></td>"
              ."</tr>\n";
    }
    $arr  = $rtl ? '&larr;' : '&rarr;';
    $link = "<a href='../sales/inquiry/customer_inquiry.php' class='vd-view-all'>"._('View All')." $arr</a>";
    _vd_table(_('Recent Transactions'),
        [_('Ref.'),_('Party'),_('Type'),_('Amount'),_('Date'),_('Status')],
        $rows ?: "<tr><td colspan='6' class='vd-empty'>"._('No transactions')."</td></tr>", $link);
}

function _vd_top_customers($today, $rtl=false)
{
    $begin=date2sql(begin_fiscalyear()); $t1=date2sql($today);
    $sql="SELECT CONVERT(d.name USING utf8) AS name,
                 SUM((dt.ov_amount+dt.ov_gst+dt.ov_discount)*dt.rate) AS total
          FROM ".TB_PREF."debtor_trans dt
          JOIN ".TB_PREF."debtors_master d ON dt.debtor_no=d.debtor_no
          WHERE dt.type=".ST_SALESINVOICE."
            AND dt.tran_date>='$begin' AND dt.tran_date<='$t1'
          GROUP BY d.debtor_no ORDER BY total DESC LIMIT 5";
    $r=db_query($sql); $rows=''; $i=1;
    while($row=db_fetch($r)){
        $pct = 100; // visual bar width
        $rows.="<tr><td>$i</td><td>".htmlspecialchars(mb_substr($row['name'],0,30))."</td>"
              ."<td class='vd-r vd-amt'>".number_format((float)$row['total'],0)."</td></tr>\n";
        $i++;
    }
    _vd_table(sprintf(_('Top %s Customers'),5),[_('#'),_('Customer'),_('Amount')],
        $rows ?: "<tr><td colspan='3' class='vd-empty'>"._('No data')."</td></tr>");
}

function _vd_top_suppliers($today, $rtl=false)
{
    $begin=date2sql(begin_fiscalyear()); $t1=date2sql($today);
    $sql="SELECT CONVERT(s.supp_name USING utf8) AS supp_name,
                 SUM((st.ov_amount+st.ov_gst+st.ov_discount)*st.rate) AS total
          FROM ".TB_PREF."supp_trans st
          JOIN ".TB_PREF."suppliers s ON st.supplier_id=s.supplier_id
          WHERE st.type IN(".ST_SUPPINVOICE.",".ST_SUPPCREDIT.")
            AND st.tran_date>='$begin' AND st.tran_date<='$t1'
          GROUP BY s.supplier_id ORDER BY total DESC LIMIT 5";
    $r=db_query($sql); $rows=''; $i=1;
    while($row=db_fetch($r)){
        $rows.="<tr><td>$i</td><td>".htmlspecialchars(mb_substr($row['supp_name'],0,30))."</td>"
              ."<td class='vd-r vd-amt'>".number_format((float)$row['total'],0)."</td></tr>\n";
        $i++;
    }
    _vd_table(sprintf(_('Top %s Suppliers'),5),[_('#'),_('Supplier'),_('Amount')],
        $rows ?: "<tr><td colspan='3' class='vd-empty'>"._('No data')."</td></tr>");
}

function _vd_low_stock_table($t1, $rtl=false)
{
    $sql="SELECT sm.stock_id, CONVERT(sm.description USING utf8) AS description,
                 COALESCE(SUM(st.qty),0) AS qty_on_hand,
                 COALESCE(reorders.reorder_level,0) AS reorder_level
          FROM ".TB_PREF."stock_master sm
          LEFT JOIN ".TB_PREF."stock_moves st ON sm.stock_id=st.stock_id AND st.tran_date<='$t1'
          LEFT JOIN ".TB_PREF."loc_stock reorders ON reorders.stock_id=sm.stock_id
          WHERE sm.mb_flag='B' AND sm.inactive=0
          GROUP BY sm.stock_id
          HAVING qty_on_hand <= COALESCE(reorders.reorder_level,0)
          ORDER BY qty_on_hand ASC LIMIT 10";
    $r=db_query($sql);
    if(db_num_rows($r)==0) return;

    $rows='';
    while($row=db_fetch($r)){
        $qty=(float)$row['qty_on_hand']; $rl=(float)$row['reorder_level'];
        $pct=$rl>0?min(100,max(5,round(($qty/$rl)*100))):0;
        $cls=$qty<=0?'vd-st-overdue':'vd-st-pending';
        $rows.="<tr>"
              ."<td>".htmlspecialchars($row['stock_id'])."</td>"
              ."<td>".htmlspecialchars(mb_substr($row['description'],0,35))."</td>"
              ."<td class='vd-r'><span class='vd-status $cls'>".number_format($qty,0)."</span></td>"
              ."<td class='vd-r'>".number_format($rl,0)."</td>"
              ."<td><div class='vd-pbar'><div class='vd-pbar-fill' style='width:{$pct}%'></div></div></td>"
              ."</tr>\n";
    }
    _vd_table(_('Items Below Reorder Level'),
        [_('Stock ID'),_('Description'),_('On Hand'),_('Reorder Level'),_('Level')], $rows);
}

/* ══════════════════════════════════════════════════════
   QUICK ACTIONS
══════════════════════════════════════════════════════ */
function _vd_quick_actions($rtl=false)
{
    $actions=[
        [_('Sales Invoice'),  '../sales/customer_invoice.php',        'invoice'],
        [_('Receipt'),        '../gl/add_payment.php?PaymentType=2',  'receipt'],
        [_('New Customer'),   '../sales/manage/customers.php',        'customer'],
        [_('Stock Entry'),    '../inventory/inv_transfer.php',        'stock'],
        [_('Journal Entry'),  '../gl/gl_journal.php',                 'journal'],
        [_('Reports'),        '../reporting/reports_main.php',        'reports'],
        [_('Purchase Inv.'),  '../purchasing/supplier_invoice.php',   'purchase'],
        [_('Trial Balance'),  '../gl/inquiry/trial_balance.php',      'trial'],
    ];
    $svgs=_vd_icons();

    echo "<div class='vd-section'>\n";
    echo "  <h3 class='vd-section-title'>"._('Quick Actions')."</h3>\n";
    echo "  <div class='vd-quick-grid'>\n";
    foreach($actions as $i=>$a){
        $ic=$svgs[$a[2]]??$svgs['default'];
        $delay=round($i*0.05,2);
        echo "  <a href='".htmlspecialchars($a[1])."' class='vd-qa-btn' style='animation-delay:{$delay}s'>\n";
        echo "    <span class='vd-qa-icon'>$ic</span>\n";
        echo "    <span class='vd-qa-label'>".htmlspecialchars($a[0])."</span>\n";
        echo "  </a>\n";
    }
    echo "  </div>\n</div>\n";
}

/* ══════════════════════════════════════════════════════
   MONTH LABEL HELPER — converts "YYYY-MM" to Arabic or English
══════════════════════════════════════════════════════ */
function _vd_format_month($ym, $rtl=false)
{
    $ar = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
           7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
    $en = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
           7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
    /* $ym is always "YYYY-MM" from DB */
    $parts = explode('-', $ym);
    if (count($parts) < 2) return $ym;
    $m = (int)$parts[1];
    $y = $parts[0];
    if ($rtl) return ($ar[$m] ?? $ym) . ' ' . $y;
    return ($en[$m] ?? $ym) . ' ' . $y;
}

/* ══════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════ */
function _vd_today()
{
    $t=Today(); if(!is_date_in_fiscalyear($t)) $t=end_fiscalyear(); return $t;
}

function _vd_ar_balance($t1)
{
    $sql="SELECT COALESCE(SUM((ov_amount+ov_gst+ov_freight+ov_discount-alloc)*rate),0) AS v
          FROM ".TB_PREF."debtor_trans
          WHERE type=".ST_SALESINVOICE." AND tran_date<='$t1'
            AND (ov_amount+ov_gst+ov_freight+ov_discount-alloc)>0.001";
    $r=db_query($sql); $row=db_fetch($r); return (float)($row?$row['v']:0);
}

function _vd_ap_balance($t1)
{
    $sql="SELECT COALESCE(SUM((ov_amount+ov_gst+ov_discount-alloc)*rate),0) AS v
          FROM ".TB_PREF."supp_trans
          WHERE type=".ST_SUPPINVOICE." AND tran_date<='$t1'
            AND (ov_amount+ov_gst+ov_discount-alloc)>0.001";
    $r=db_query($sql); $row=db_fetch($r); return (float)($row?$row['v']:0);
}

function _vd_low_stock_count($t1)
{
    $sql="SELECT COUNT(DISTINCT sm.stock_id) AS c
          FROM ".TB_PREF."stock_master sm
          LEFT JOIN (SELECT stock_id, SUM(qty) AS qty FROM ".TB_PREF."stock_moves WHERE tran_date<='$t1' GROUP BY stock_id) mv ON mv.stock_id=sm.stock_id
          LEFT JOIN ".TB_PREF."loc_stock ls ON ls.stock_id=sm.stock_id
          WHERE sm.mb_flag='B' AND sm.inactive=0
            AND COALESCE(mv.qty,0) <= COALESCE(ls.reorder_level,0)";
    $r=db_query($sql); $row=db_fetch($r); return (int)($row?$row['c']:0);
}

function _vd_table($title,$heads,$rows_html,$action='')
{
    echo "<div class='vd-tbl-wrap'>\n";
    echo "  <div class='vd-tbl-head'><span class='vd-tbl-title'>".htmlspecialchars($title)."</span>$action</div>\n";
    echo "  <div class='vd-tbl-scroll'><table class='vd-tbl'>\n";
    echo "    <thead><tr>\n";
    foreach($heads as $h) echo "      <th>".htmlspecialchars($h)."</th>\n";
    echo "    </tr></thead><tbody>\n$rows_html    </tbody>\n  </table></div>\n</div>\n";
}

/* ══════════════════════════════════════════════════════
   ICONS
══════════════════════════════════════════════════════ */
function _vd_icons(){
    return [
        'revenue' =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'ar'      =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
        'ap'      =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 15h.01M10 15h4"/></svg>',
        'stock'   =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
        'orders'  =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
        'dep'     =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
        'pmt'     =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="7" y1="15" x2="10" y2="15"/></svg>',
        'cust'    =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'invoice' =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'receipt' =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4v16l3-2 3 2 3-2 3 2 3-2V4"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="12" y2="13"/></svg>',
        'customer'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
        'journal' =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        'reports' =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
        'purchase'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2 3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
        'trial'   =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'default' =>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    ];
}

/* ══════════════════════════════════════════════════════
   CSS
══════════════════════════════════════════════════════ */
function _vd_styles(){
return <<<CSS
<style>
/* ── Fonts: Inter (EN) + Cairo (AR) ── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&family=Cairo:wght@400;500;600;700;800;900&display=swap');

/* ── Variables ── */
:root {
  --primary:#164E8F; --primary-dark:#0A2342; --accent:#D4AF37;
  --success:#10B981; --danger:#EF4444; --warning:#F59E0B; --purple:#8B5CF6;
  --bg:#F4F6FA; --surface:#FFFFFF; --border:#E2E8F0; --border-light:#EDF0F7;
  --text-1:#050505; --text-2:#2D3748; --text-3:#718096;
  --font:'Inter','DM Sans',sans-serif;
  --font-ar:'Cairo',sans-serif;
  --radius:14px; --shadow:0 1px 3px rgba(10,35,66,.05),0 4px 14px rgba(10,35,66,.07);
  --ease:cubic-bezier(.4,0,.2,1);
}

/* ── Arabic mode: Cairo font, RTL layout ── */
[dir="rtl"] { font-family:var(--font-ar) !important; }
[dir="rtl"] .vd-kpi-val  { font-family:var(--font-ar); font-weight:800; letter-spacing:0; }
[dir="rtl"] .vd-kpi-lbl  { font-family:var(--font-ar); font-size:13px; }
[dir="rtl"] .vd-kpi-sub  { font-family:var(--font-ar); font-size:12px; }
[dir="rtl"] .vd-snap-title { font-family:var(--font-ar); font-size:14px; }
[dir="rtl"] .vd-snap-lbl   { font-family:var(--font-ar); font-size:13px; }
[dir="rtl"] .vd-snap-val   { font-family:var(--font-ar); }
[dir="rtl"] .vd-welcome-title { font-family:var(--font-ar); font-size:24px; letter-spacing:0; }
[dir="rtl"] .vd-welcome-sub  { font-family:var(--font-ar); font-size:14px; }
[dir="rtl"] .vd-welcome-meta { font-family:var(--font-ar); letter-spacing:0; }
[dir="rtl"] .vd-tbl th   { font-family:var(--font-ar); font-size:12px; letter-spacing:0; text-align:right; }
[dir="rtl"] .vd-tbl td   { font-family:var(--font-ar); font-size:13px; text-align:right; }
[dir="rtl"] .vd-tbl-title { font-family:var(--font-ar); font-size:14px; }
[dir="rtl"] .vd-chart-title { font-family:var(--font-ar); font-size:14px; }
[dir="rtl"] .vd-section-title { font-family:var(--font-ar); font-size:15px; letter-spacing:0; }
[dir="rtl"] .vd-qa-label  { font-family:var(--font-ar); font-size:12.5px; }
[dir="rtl"] .vd-type-badge { font-family:var(--font-ar); }
[dir="rtl"] .vd-status     { font-family:var(--font-ar); }
[dir="rtl"] .vd-badge      { font-family:var(--font-ar); }
[dir="rtl"] .vd-fs-lbl     { font-family:var(--font-ar); font-size:12px; letter-spacing:0; }
[dir="rtl"] .vd-fs-num     { font-family:var(--font-ar); font-size:24px; }
[dir="rtl"] .vd-chg        { font-family:var(--font-ar); }
[dir="rtl"] .vd-btn-outline { font-family:var(--font-ar); font-size:13px; }
[dir="rtl"] .vd-health-lbl  { font-family:var(--font-ar); letter-spacing:0; font-size:10px; }
[dir="rtl"] .vd-alert-strip { font-family:var(--font-ar); font-size:13px; }
/* RTL number direction stays LTR */
[dir="rtl"] .vd-kpi-val, [dir="rtl"] .vd-amt, [dir="rtl"] .vd-crd { direction:ltr; text-align:right; }

/* ── Base ── */
.ex-main { background:var(--bg) !important; font-family:var(--font) !important; }
* { box-sizing:border-box; }

/* ── Welcome ── */
.vd-welcome {
  display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;
  background:linear-gradient(135deg,var(--primary-dark) 0%,var(--primary) 60%,#1a5fa8 100%);
  border-radius:var(--radius); padding:22px 28px; margin-bottom:20px;
  box-shadow:0 8px 28px rgba(10,35,66,.28); position:relative; overflow:hidden;
}
.vd-welcome::before {
  content:''; position:absolute; top:-40px; right:-40px; width:180px; height:180px;
  border-radius:50%; background:radial-gradient(circle,rgba(212,175,55,.15) 0%,transparent 70%);
}
.vd-welcome::after {
  content:''; position:absolute; bottom:-30px; left:30%; width:140px; height:140px;
  border-radius:50%; background:radial-gradient(circle,rgba(255,255,255,.04) 0%,transparent 70%);
}
.vd-welcome-meta { font-size:11px; color:rgba(255,255,255,.55); font-weight:600; letter-spacing:.06em; text-transform:uppercase; margin-bottom:4px; }
.vd-welcome-title { font-size:22px; font-weight:800; color:#fff; margin:0 0 4px; letter-spacing:-.02em; }
.vd-welcome-sub { font-size:13px; color:rgba(255,255,255,.7); margin:0; }
.vd-welcome-meta2 { font-size:10.5px; color:rgba(255,255,255,.4); margin-top:8px; font-weight:500; letter-spacing:.03em; }
.vd-welcome-right { display:flex; gap:10px; flex-wrap:wrap; position:relative; z-index:1; align-items:center; }
.vd-welcome-left { position:relative; z-index:1; }
.vd-btn-outline {
  display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
  font-size:12px; font-weight:600; border:1.5px solid rgba(255,255,255,.3);
  border-radius:8px; color:rgba(255,255,255,.85); text-decoration:none;
  background:rgba(255,255,255,.08); transition:all .18s var(--ease);
  cursor:pointer; font-family:inherit;
}
.vd-btn-outline:hover { background:rgba(255,255,255,.18); border-color:rgba(255,255,255,.6); color:#fff; }

/* ── Alert Strip ── */
.vd-alert-strip {
  background:#FEF2F2; border:1.5px solid #FECACA; border-radius:10px;
  padding:10px 18px; margin-bottom:16px; font-size:12.5px; color:#DC2626; font-weight:600;
}
.vd-alert-strip a { color:#DC2626; font-weight:700; margin-left:8px; }

/* ── KPI Grid ── */
.vd-kpi-grid {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
  gap:14px; margin-bottom:22px;
}
.vd-kpi-card {
  background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius);
  padding:20px 18px 16px; position:relative; overflow:hidden;
  box-shadow:var(--shadow); transition:all .22s var(--ease); animation:vdUp .5s both;
}
.vd-kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 30px rgba(10,35,66,.13); border-color:#C3D7F0; }
.vd-kpi-card::before {
  content:''; position:absolute; top:0; left:0; right:0; height:3px;
  background:var(--kc,var(--primary)); border-radius:var(--radius) var(--radius) 0 0;
}
.vd-kpi-card::after {
  content:''; position:absolute; top:-20px; right:-20px; width:80px; height:80px;
  border-radius:50%; background:var(--kc,var(--primary)); opacity:.05;
}
.vd-kpi-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.vd-kpi-icon { width:40px; height:40px; border-radius:10px; background:rgba(22,78,143,.06); border:1px solid rgba(22,78,143,.1); display:flex; align-items:center; justify-content:center; color:var(--kc,var(--primary)); }
.vd-kpi-icon svg { width:18px; height:18px; }
.vd-kpi-val { font-size:21px; font-weight:800; letter-spacing:-.03em; color:var(--text-1); line-height:1.1; margin-bottom:5px; }
.vd-kpi-lbl { font-size:12px; color:var(--text-3); font-weight:500; }
.vd-kpi-sub { font-size:11px; color:var(--text-3); margin-top:4px; }
.vd-chg.pos  { font-size:11px; font-weight:700; color:#059669; background:#ECFDF5; padding:2px 7px; border-radius:999px; }
.vd-chg.neg  { font-size:11px; font-weight:700; color:#DC2626; background:#FEF2F2; padding:2px 7px; border-radius:999px; }
.vd-badge    { font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; background:#FFF9EB; }

/* ── Section Title ── */
.vd-section { margin-bottom:22px; }
.vd-section-title { font-size:13.5px; font-weight:700; color:var(--text-1); margin:0 0 14px; padding-bottom:10px; border-bottom:2px solid var(--border); }

/* ── Quick Actions ── */
.vd-quick-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(115px,1fr)); gap:10px; }
.vd-qa-btn {
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:9px;
  padding:16px 10px; background:var(--surface); border:1.5px solid var(--border);
  border-radius:12px; text-decoration:none; color:var(--text-2); transition:all .2s var(--ease);
  animation:vdUp .45s both;
}
.vd-qa-btn:hover { border-color:var(--primary); background:#f0f5fd; color:var(--primary); transform:translateY(-2px); box-shadow:0 4px 16px rgba(22,78,143,.12); }
.vd-qa-icon { width:38px; height:38px; border-radius:10px; background:#EFF6FF; display:flex; align-items:center; justify-content:center; color:var(--primary); }
.vd-qa-icon svg { width:17px; height:17px; }
.vd-qa-label { font-size:11.5px; font-weight:600; text-align:center; line-height:1.3; }

/* ── Charts ── */
.vd-charts-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(380px,1fr)); gap:16px; margin-bottom:18px; }
.vd-chart-card { background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius); padding:20px 22px; box-shadow:var(--shadow); }
.vd-chart-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:8px; }
.vd-chart-title { font-size:13px; font-weight:700; color:var(--text-1); }
.vd-chart-body { position:relative; height:240px; }
.vd-chart-body--donut { height:220px; }
.vd-select { font-size:11.5px; padding:4px 10px; border:1.5px solid var(--border); border-radius:7px; background:#F8F9FC; color:var(--text-3); cursor:pointer; outline:none; }
.vd-select:focus { border-color:var(--primary); }
.vd-chip { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; background:#EFF6FF; color:var(--primary); border:1px solid #BFDBFE; }

/* ── Tables ── */
.vd-tbl-wrap { background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow); margin-bottom:18px; }
.vd-tbl-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; background:#F8F9FC; border-bottom:1.5px solid var(--border); }
.vd-tbl-title { font-size:13px; font-weight:700; color:var(--text-1); }
.vd-view-all { font-size:12px; font-weight:600; color:var(--primary); text-decoration:none; }
.vd-view-all:hover { text-decoration:underline; }
.vd-tbl-scroll { overflow-x:auto; }
.vd-tbl { width:100%; border-collapse:collapse; font-family:var(--font); font-size:12.5px; }
.vd-tbl th { padding:10px 14px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--text-3); background:#F8F9FC; border-bottom:1px solid var(--border); white-space:nowrap; }
.vd-tbl td { padding:11px 14px; color:var(--text-2); border-bottom:1px solid var(--border-light); vertical-align:middle; }
.vd-tbl tbody tr:last-child td { border-bottom:none; }
.vd-tbl tbody tr:hover td { background:#F0F5FD; }
.vd-r { text-align:right !important; }
.vd-amt { font-weight:700; color:var(--text-1); font-family:var(--font); }
.vd-crd { font-weight:700; color:var(--success); }
.vd-muted { color:var(--text-3); font-size:11.5px; }
.vd-empty { text-align:center !important; color:var(--text-3); padding:28px; font-size:12.5px; }
.vd-ref { font-weight:700; font-size:12px; color:var(--primary); }
.vd-type-badge { display:inline-flex; font-size:11px; font-weight:600; padding:2px 8px; border-radius:6px; background:#EFF6FF; color:var(--primary); white-space:nowrap; }
.vd-status { display:inline-flex; align-items:center; padding:3px 9px; border-radius:999px; font-size:11px; font-weight:700; }
.vd-st-paid    { background:#ECFDF5; color:#059669; }
.vd-st-overdue { background:#FEF2F2; color:#DC2626; }
.vd-st-pending { background:#FFFBEB; color:#D97706; }
.vd-tables-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(440px,1fr)); gap:16px; margin-bottom:18px; }

/* ── Progress Bar ── */
.vd-pbar { height:6px; background:#EDF0F7; border-radius:999px; min-width:80px; }
.vd-pbar-fill { height:6px; background:linear-gradient(90deg,var(--warning),var(--danger)); border-radius:999px; transition:width .4s; }

/* ── Footer Stats ── */
.vd-footer-stats {
  display:flex; flex-wrap:wrap; gap:12px; margin-bottom:24px;
  background:linear-gradient(135deg,var(--primary-dark),var(--primary));
  border-radius:var(--radius); padding:20px 24px;
  box-shadow:0 4px 18px rgba(10,35,66,.25);
}
.vd-fs-item { flex:1; min-width:120px; text-align:center; }
.vd-fs-num { display:block; font-size:22px; font-weight:800; color:var(--accent); letter-spacing:-.02em; }
.vd-fs-lbl { display:block; font-size:11px; color:rgba(255,255,255,.6); font-weight:500; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }

/* ── Animations ── */
@keyframes vdUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }

/* ── Responsive ── */
@media(max-width:1100px){ .vd-charts-row{ grid-template-columns:1fr; } }
@media(max-width:860px) { .vd-tables-row{ grid-template-columns:1fr; } .vd-quick-grid{ grid-template-columns:repeat(4,1fr); } }
@media(max-width:600px) { .vd-kpi-grid{ grid-template-columns:1fr 1fr; } .vd-quick-grid{ grid-template-columns:repeat(3,1fr); } }

/* ── Financial Health Badge ── */
.vd-health-badge {
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  gap:2px; position:relative; width:64px;
}
.vd-health-ring { width:56px; height:56px; transform:rotate(-90deg); }
.vd-health-ring .ring-bg  { fill:none; stroke:#E2E8F0; stroke-width:3.5; }
.vd-health-ring .ring-fill { fill:none; stroke-width:3.5; stroke-linecap:round; transition:stroke-dasharray .6s var(--ease); }
.vd-health-ring .ring-fill.good { stroke:#10B981; }
.vd-health-ring .ring-fill.fair { stroke:#F59E0B; }
.vd-health-ring .ring-fill.poor { stroke:#EF4444; }
.vd-health-num { position:absolute; top:12px; font-size:14px; font-weight:800; line-height:1; }
.vd-health-num.good { color:#10B981; }
.vd-health-num.fair { color:#F59E0B; }
.vd-health-num.poor { color:#EF4444; }
.vd-health-lbl { font-size:9px; font-weight:700; color:rgba(255,255,255,.55); text-transform:uppercase; letter-spacing:.08em; }

/* ── Auto-refresh countdown ── */
#vd-auto-lbl { display:flex; align-items:center; gap:4px; }
#vd-countdown { font-weight:800; color:var(--accent); min-width:20px; display:inline-block; text-align:center; }

/* ── Alert strip warning variant ── */
.vd-alert-warn { background:#FFFBEB; border-color:#FDE68A; color:#B45309; }
.vd-alert-warn a { color:#B45309; }

/* ── Financial Snapshot Grid ── */
.vd-snapshot-grid {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:16px; margin-bottom:22px;
}
.vd-snap-card {
  background:var(--surface); border:1.5px solid var(--border);
  border-radius:var(--radius); padding:20px 22px; box-shadow:var(--shadow);
}
.vd-snap-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.vd-snap-title { font-size:13px; font-weight:700; color:var(--text-1); }
.vd-snap-rows  { display:flex; flex-direction:column; gap:8px; }
.vd-snap-row   { display:flex; align-items:center; justify-content:space-between; }
.vd-snap-lbl   { font-size:12.5px; color:var(--text-3); }
.vd-snap-val   { font-size:13px; font-weight:700; font-family:var(--font); }
.vd-snap-pos   { color:#059669; }
.vd-snap-neg   { color:#DC2626; }
.vd-snap-divider { height:1px; background:var(--border); margin:4px 0; }
.vd-snap-total .vd-snap-lbl { font-weight:700; color:var(--text-2); font-size:13px; }
.vd-snap-total .vd-snap-val { font-size:15px; }
.vd-snap-bar-lbl  { font-size:10.5px; color:var(--text-3); margin-top:14px; }
.vd-snap-bar-pct  { font-size:11px; color:var(--text-3); margin-top:4px; text-align:right; }

/* ── RTL ── */
[dir="rtl"] .vd-tbl th,[dir="rtl"] .vd-tbl td { text-align:right; }
[dir="rtl"] .vd-r { text-align:left !important; }
[dir="rtl"] .vd-welcome::before { right:auto; left:-40px; }
[dir="rtl"] .vd-snapshot-grid { direction:rtl; }

/* ── GL Journal Enhanced Table ── */
.vd-gl-wrap { background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius); overflow:hidden; box-shadow:var(--shadow); margin-bottom:18px; }
.vd-gl-header { background:linear-gradient(135deg,var(--primary-dark) 0%,var(--primary) 100%); }
.vd-gl-title-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; padding:14px 18px 10px; }
.vd-gl-title { font-size:13.5px; font-weight:700; color:#fff; line-height:1.3; }
.vd-gl-subtitle { font-size:11px; color:rgba(255,255,255,.5); font-weight:500; }
.vd-gl-header-actions { display:flex; gap:8px; flex-wrap:wrap; }
.vd-gl-btn-new { display:inline-flex; align-items:center; padding:6px 13px; font-size:11.5px; font-weight:700; border-radius:7px; background:var(--accent); color:var(--primary-dark); text-decoration:none; transition:all .18s; white-space:nowrap; }
.vd-gl-btn-new:hover { background:#e8c83d; transform:translateY(-1px); }
.vd-gl-btn-inq { display:inline-flex; align-items:center; padding:6px 13px; font-size:11.5px; font-weight:600; border-radius:7px; border:1.5px solid rgba(255,255,255,.3); color:rgba(255,255,255,.85); background:rgba(255,255,255,.1); text-decoration:none; transition:all .18s; white-space:nowrap; }
.vd-gl-btn-inq:hover { background:rgba(255,255,255,.2); border-color:rgba(255,255,255,.6); color:#fff; }
.vd-gl-stats-bar { display:flex; border-top:1px solid rgba(255,255,255,.12); }
.vd-gl-stat { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:10px 8px; border-right:1px solid rgba(255,255,255,.1); min-width:80px; }
.vd-gl-stat:last-child { border-right:none; }
.vd-gl-stat-num { font-size:16px; font-weight:800; color:var(--accent); line-height:1.1; direction:ltr; }
.vd-gl-stat-lbl { font-size:10px; color:rgba(255,255,255,.55); font-weight:500; text-transform:uppercase; letter-spacing:.04em; margin-top:2px; text-align:center; }
.vd-gl-dr { color:#6EE7B7 !important; }
.vd-gl-cr { color:#FCA5A5 !important; }
.vd-gl-bal-ok  { font-size:12px; font-weight:700; color:#6EE7B7; padding:3px 10px; border-radius:999px; background:rgba(16,185,129,.2); border:1px solid rgba(16,185,129,.35); }
.vd-gl-bal-err { font-size:12px; font-weight:700; color:#FCA5A5; padding:3px 10px; border-radius:999px; background:rgba(239,68,68,.2); border:1px solid rgba(239,68,68,.35); }
.vd-gl-type-badge { display:inline-flex; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:6px; white-space:nowrap; }
.vd-tb-journal  { background:#EFF6FF; color:#1D4ED8; }
.vd-tb-payment  { background:#FEF2F2; color:#DC2626; }
.vd-tb-deposit  { background:#ECFDF5; color:#059669; }
.vd-tb-transfer { background:#F5F3FF; color:#7C3AED; }
.vd-tb-sales    { background:#F0FDFA; color:#0D9488; }
.vd-tb-purchase { background:#FDF4FF; color:#9333EA; }
.vd-gl-ind { display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; font-size:11px; font-weight:800; line-height:1; }
.vd-gl-ind-ok  { background:#ECFDF5; color:#059669; }
.vd-gl-ind-err { background:#FEF2F2; color:#DC2626; }
.vd-gl-center  { text-align:center !important; }
.vd-gl-ref { font-weight:700; font-size:12px; color:var(--primary); font-family:var(--font); direction:ltr; display:inline-block; }
.vd-lines-pill { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:18px; padding:0 5px; border-radius:999px; font-size:10.5px; font-weight:700; background:#EFF6FF; color:var(--primary); border:1px solid #BFDBFE; }
.vd-gl-date { color:var(--text-3); font-size:12px; white-space:nowrap; }
.vd-gl-tbl th:nth-child(4),.vd-gl-tbl th:nth-child(5) { text-align:right; }
.vd-gl-tbl td:nth-child(4),.vd-gl-tbl td:nth-child(5) { text-align:right !important; font-family:var(--font); }
.vd-gl-tbl td:nth-child(6),.vd-gl-tbl td:nth-child(7) { text-align:center !important; }
.vd-gl-footer { display:flex; align-items:center; gap:16px; flex-wrap:wrap; padding:10px 18px; background:#F8F9FC; border-top:1.5px solid var(--border); }
.vd-gl-foot-link { font-size:12px; font-weight:600; color:var(--primary); text-decoration:none; white-space:nowrap; }
.vd-gl-foot-link:hover { text-decoration:underline; }
.vd-gl-foot-all { margin-left:auto; color:var(--accent) !important; font-weight:700; }
[dir="rtl"] .vd-gl-stat { border-right:none; border-left:1px solid rgba(255,255,255,.1); }
[dir="rtl"] .vd-gl-stat:last-child { border-left:none; }
[dir="rtl"] .vd-gl-foot-all { margin-left:0; margin-right:auto; }
[dir="rtl"] .vd-gl-title-row { flex-direction:row-reverse; }
[dir="rtl"] .vd-gl-type-badge,[dir="rtl"] .vd-gl-stat-lbl,[dir="rtl"] .vd-gl-title,[dir="rtl"] .vd-gl-foot-link,[dir="rtl"] .vd-gl-btn-new,[dir="rtl"] .vd-gl-btn-inq { font-family:var(--font-ar); }
</style>
CSS;
}

/* Legacy alias */
function dashboard($sel_app){ _vd_run($sel_app); }