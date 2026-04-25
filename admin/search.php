<?php
/**********************************************************************
 *  VIP Accounting System — Enterprise Global Search API v11
 *  Path: vipaccsystem/admin/search.php
 *
 *  !! STANDALONE — does NOT include session.inc !!
 *  Authenticates via $_SESSION, connects to MySQL directly.
 *  Returns JSON for ALL code paths (auth fail, empty query, results, errors).
 *
 *  Searches: customers, suppliers, items, invoices, purchases,
 *            journal entries, bank transactions, GL accounts, dimensions.
 **********************************************************************/

/* ── Suppress errors from leaking into JSON output ── */
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');

/* ══════════════════════════════════════════════════════════════
   1. LOAD CLASS DEFINITIONS — must be loaded BEFORE session_start()
      so PHP can deserialize session objects properly.
      Without this, $_SESSION objects become __PHP_Incomplete_Class.
══════════════════════════════════════════════════════════════ */
$path_to_root = "..";

// config_db.php defines $def_coy, $db_connections (needed by includes)
require_once($path_to_root . '/config_db.php');

// Class definitions stored in $_SESSION — load in dependency order
require_once($path_to_root . '/includes/prefs/userprefs.inc');
require_once($path_to_root . '/includes/current_user.inc');
require_once($path_to_root . '/includes/lang/language.inc');

/* ══════════════════════════════════════════════════════════════
   2. SESSION — start with correct FA session name
      FA uses: 'FA' . md5(dirname(__FILE__)) where __FILE__ = session.inc
══════════════════════════════════════════════════════════════ */
$session_inc_dir = realpath($path_to_root . '/includes');
if ($session_inc_dir) {
    session_name('FA' . md5($session_inc_dir));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ══════════════════════════════════════════════════════════════
   3. AUTHENTICATION
══════════════════════════════════════════════════════════════ */
$authenticated = false;

if (isset($_SESSION['wa_current_user']) && is_object($_SESSION['wa_current_user'])
    && !($_SESSION['wa_current_user'] instanceof __PHP_Incomplete_Class)) {
    if (method_exists($_SESSION['wa_current_user'], 'logged_in')
        && $_SESSION['wa_current_user']->logged_in()) {
        $authenticated = true;
    }
}

if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'results' => [], 'total' => 0]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   4. DATABASE CONNECTION
══════════════════════════════════════════════════════════════ */
$company_id = (int)$_SESSION['wa_current_user']->company;
$c    = $db_connections[$company_id] ?? $db_connections[$def_coy] ?? $db_connections[0];
$pref = !empty($c['tbpref']) ? $c['tbpref'] : '0_';

mysqli_report(MYSQLI_REPORT_OFF); // prevent exceptions on query errors
$db = @mysqli_connect(
    $c['host'] ?? 'localhost',
    $c['dbuser'] ?? 'root',
    $c['dbpassword'] ?? '',
    $c['dbname'] ?? '',
    !empty($c['port']) ? (int)$c['port'] : 3306
);

if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'results' => [], 'total' => 0]);
    exit;
}

mysqli_set_charset($db, 'utf8mb4');

/* ══════════════════════════════════════════════════════════════
   5. INPUT
══════════════════════════════════════════════════════════════ */
$q       = isset($_GET['q'])     ? trim($_GET['q'])     : '';
$limit   = isset($_GET['limit']) ? (int)$_GET['limit']  : 40;
$section = isset($_GET['s'])     ? trim($_GET['s'])      : 'all';

if (mb_strlen($q) < 1) {
    echo json_encode(['q' => $q, 'total' => 0, 'results' => []]);
    mysqli_close($db);
    exit;
}
if ($limit < 1 || $limit > 100) $limit = 40;

$allowed = ['all','customers','suppliers','items','invoices','purchases','journal','bank','accounts','dimensions'];
if (!in_array($section, $allowed)) $section = 'all';

/* ── DEBUG ── */
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    echo json_encode([
        'status'  => 'ok',
        'version' => 'v11-standalone',
        'session' => session_id(),
        'user'    => $_SESSION['wa_current_user']->username ?? null,
        'company' => $company_id,
        'db'      => 'connected',
        'dbname'  => $c['dbname'],
        'prefix'  => $pref,
        'query'   => $q,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    mysqli_close($db);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   6. HELPERS
══════════════════════════════════════════════════════════════ */
function gs_q($sql) {
    global $db;
    $rows = [];
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        mysqli_free_result($res);
    }
    return $rows;
}

function gs_esc($str) {
    global $db;
    return mysqli_real_escape_string($db, $str);
}

$term    = gs_esc($q);
$results = [];

/* ── Colours ── */
define('CLR_CUSTOMER',  '#10B981');
define('CLR_SUPPLIER',  '#F59E0B');
define('CLR_ITEM',      '#EC4899');
define('CLR_INVOICE',   '#06B6D4');
define('CLR_PURCHASE',  '#8B5CF6');
define('CLR_JOURNAL',   '#1B3F7A');
define('CLR_BANK',      '#0EA5E9');
define('CLR_ACCOUNT',   '#D4AF37');
define('CLR_DIMENSION', '#A855F7');

$is_ar = false;
if (isset($_SESSION['language']) && is_object($_SESSION['language'])
    && !($_SESSION['language'] instanceof __PHP_Incomplete_Class)
    && isset($_SESSION['language']->code)) {
    $is_ar = ($_SESSION['language']->code === 'ar_EG');
}

// Also accept explicit language parameter from frontend
if (isset($_GET['lang']) && $_GET['lang'] === 'ar') {
    $is_ar = true;
} elseif (isset($_GET['lang']) && $_GET['lang'] === 'en') {
    $is_ar = false;
}

/* ══════════════════════════════════════════════════════════════
   7. SEARCH QUERIES
   Columns verified against actual DB schema (2026-03-28)
══════════════════════════════════════════════════════════════ */

/* ════════════ 1. CUSTOMERS ════════════
   Columns: debtor_no, name, debtor_ref, address, tax_id, curr_code,
            sales_type, dimension_id, dimension2_id, credit_status,
            payment_terms, discount, pymt_discount, credit_limit, notes, inactive
*/
if ($section === 'all' || $section === 'customers') {
    $rows = gs_q("
        SELECT debtor_no, name, debtor_ref, curr_code,
               COALESCE(address,'') AS address,
               COALESCE(tax_id,'') AS tax_id,
               COALESCE(notes,'') AS notes
        FROM {$pref}debtors_master
        WHERE name       LIKE '%{$term}%'
           OR debtor_ref LIKE '%{$term}%'
           OR address    LIKE '%{$term}%'
           OR tax_id     LIKE '%{$term}%'
           OR notes      LIKE '%{$term}%'
           OR debtor_no  LIKE '%{$term}%'
           OR curr_code  LIKE '%{$term}%'
        ORDER BY CASE WHEN name LIKE '{$term}%' THEN 0 ELSE 1 END, name
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $sub = array_filter([$r['debtor_ref'], $r['curr_code'], $r['tax_id']]);
        $results[] = [
            'type'    => 'customer',
            'type_ar' => 'عميل',
            'icon'    => 'users',
            'color'   => CLR_CUSTOMER,
            'title'   => $r['name'],
            'subtitle'=> implode(' · ', $sub) ?: 'ID: '.$r['debtor_no'],
            'url'     => '../sales/manage/customers.php?debtor_no=' . (int)$r['debtor_no'],
            'id'      => $r['debtor_no'],
        ];
    }
}

/* ════════════ 2. SUPPLIERS ════════════
   Columns: supplier_id, supp_name, supp_ref, address, supp_address,
            gst_no, contact, supp_account_no, website, bank_account,
            curr_code, ...
*/
if ($section === 'all' || $section === 'suppliers') {
    $rows = gs_q("
        SELECT supplier_id, supp_name, supp_ref, curr_code,
               COALESCE(contact,'') AS contact,
               COALESCE(address,'') AS address,
               COALESCE(website,'') AS website,
               COALESCE(gst_no,'') AS gst_no
        FROM {$pref}suppliers
        WHERE supp_name   LIKE '%{$term}%'
           OR supp_ref    LIKE '%{$term}%'
           OR contact     LIKE '%{$term}%'
           OR address     LIKE '%{$term}%'
           OR website     LIKE '%{$term}%'
           OR gst_no      LIKE '%{$term}%'
           OR supplier_id LIKE '%{$term}%'
           OR curr_code   LIKE '%{$term}%'
        ORDER BY CASE WHEN supp_name LIKE '{$term}%' THEN 0 ELSE 1 END, supp_name
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $sub = array_filter([$r['supp_ref'], $r['contact'], $r['curr_code']]);
        $results[] = [
            'type'    => 'supplier',
            'type_ar' => 'مورد',
            'icon'    => 'truck',
            'color'   => CLR_SUPPLIER,
            'title'   => $r['supp_name'],
            'subtitle'=> implode(' · ', $sub) ?: 'ID: '.$r['supplier_id'],
            'url'     => '../purchasing/manage/suppliers.php?supplier_id=' . (int)$r['supplier_id'],
            'id'      => $r['supplier_id'],
        ];
    }
}

/* ════════════ 3. INVENTORY ITEMS ════════════ */
if ($section === 'all' || $section === 'items') {
    $rows = gs_q("
        SELECT s.stock_id, s.description, s.long_description, s.units,
               s.category_id, COALESCE(c.description,'') AS cat_name
        FROM {$pref}stock_master s
        LEFT JOIN {$pref}stock_category c ON s.category_id = c.category_id
        WHERE s.description      LIKE '%{$term}%'
           OR s.stock_id         LIKE '%{$term}%'
           OR s.long_description LIKE '%{$term}%'
           OR c.description      LIKE '%{$term}%'
        ORDER BY CASE WHEN s.description LIKE '{$term}%' OR s.stock_id LIKE '{$term}%' THEN 0 ELSE 1 END, s.description
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $sub_parts = array_filter([$r['stock_id'], $r['cat_name'], $r['units']]);
        $results[] = [
            'type'    => 'item',
            'type_ar' => 'صنف',
            'icon'    => 'box',
            'color'   => CLR_ITEM,
            'title'   => $r['description'],
            'subtitle'=> implode(' · ', $sub_parts),
            'url'     => '../inventory/manage/items.php?stock_id=' . urlencode($r['stock_id']),
            'id'      => $r['stock_id'],
        ];
    }
}

/* ════════════ 4. SALES INVOICES / ORDERS / CREDIT NOTES ════════════
   debtor_trans: trans_no, type, debtor_no, tran_date, reference, ov_amount, ov_gst ...
*/
if ($section === 'all' || $section === 'invoices') {
    $type_labels = [
        10 => ['Sales Invoice',  'فاتورة مبيعات'],
        11 => ['Credit Note',    'إشعار دائن'],
        13 => ['Sales Order',    'أمر بيع'],
    ];
    $rows = gs_q("
        SELECT d.trans_no, d.type, d.tran_date, d.ov_amount, d.ov_gst, d.reference,
               dm.name AS cust_name, dm.curr_code
        FROM {$pref}debtor_trans d
        LEFT JOIN {$pref}debtors_master dm ON d.debtor_no = dm.debtor_no
        WHERE d.type IN (10,11,13)
          AND (d.reference  LIKE '%{$term}%'
            OR dm.name      LIKE '%{$term}%'
            OR d.trans_no   LIKE '%{$term}%'
            OR d.ov_amount  LIKE '%{$term}%')
        ORDER BY d.tran_date DESC
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $tt       = (int)$r['type'];
        $label    = $type_labels[$tt][0] ?? 'Invoice';
        $label_ar = $type_labels[$tt][1] ?? 'فاتورة';
        $amt      = number_format(abs((float)$r['ov_amount'] + (float)($r['ov_gst'] ?? 0)), 2);
        $cur      = $r['curr_code'] ?? '';
        $sub      = implode(' · ', array_filter([$r['cust_name'], $r['tran_date'], $cur.' '.$amt]));
        $display_label = $is_ar ? $label_ar : $label;
        $results[] = [
            'type'    => 'invoice',
            'type_ar' => $label_ar,
            'icon'    => 'file-text',
            'color'   => CLR_INVOICE,
            'title'   => $display_label . ' #' . $r['trans_no'] . ($r['reference'] ? ' — '.$r['reference'] : ''),
            'subtitle'=> $sub,
            'url'     => '../sales/inquiry/customer_inquiry.php?trans_no=' . (int)$r['trans_no'],
            'id'      => $r['trans_no'],
        ];
    }
}

/* ════════════ 5. PURCHASE INVOICES / CREDIT NOTES ════════════
   supp_trans: trans_no, type, supplier_id, reference, tran_date, ov_amount, ov_gst ...
*/
if ($section === 'all' || $section === 'purchases') {
    $pur_labels = [
        20 => ['Purchase Invoice',     'فاتورة مشتريات'],
        21 => ['Supplier Credit Note', 'إشعار مورد دائن'],
    ];
    $rows = gs_q("
        SELECT st.trans_no, st.type, st.tran_date, st.ov_amount, st.ov_gst, st.reference,
               s.supp_name, s.curr_code
        FROM {$pref}supp_trans st
        LEFT JOIN {$pref}suppliers s ON st.supplier_id = s.supplier_id
        WHERE st.type IN (20,21)
          AND (st.reference  LIKE '%{$term}%'
            OR s.supp_name   LIKE '%{$term}%'
            OR st.trans_no   LIKE '%{$term}%'
            OR st.ov_amount  LIKE '%{$term}%')
        ORDER BY st.tran_date DESC
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $tt       = (int)$r['type'];
        $label    = $pur_labels[$tt][0] ?? 'Purchase';
        $label_ar = $pur_labels[$tt][1] ?? 'مشتريات';
        $amt      = number_format(abs((float)$r['ov_amount'] + (float)($r['ov_gst'] ?? 0)), 2);
        $cur      = $r['curr_code'] ?? '';
        $sub      = implode(' · ', array_filter([$r['supp_name'], $r['tran_date'], $cur.' '.$amt]));
        $display_label = $is_ar ? $label_ar : $label;
        $results[] = [
            'type'    => 'purchase',
            'type_ar' => $label_ar,
            'icon'    => 'shopping-cart',
            'color'   => CLR_PURCHASE,
            'title'   => $display_label . ' #' . $r['trans_no'] . ($r['reference'] ? ' — '.$r['reference'] : ''),
            'subtitle'=> $sub,
            'url'     => '../purchasing/inquiry/supplier_inquiry.php?trans_no=' . (int)$r['trans_no'],
            'id'      => $r['trans_no'],
        ];
    }
}

/* ════════════ 6. JOURNAL ENTRIES ════════════
   gl_trans: counter, type, type_no, tran_date, account, memo_, amount ...
*/
if ($section === 'all' || $section === 'journal') {
    $rows = gs_q("
        SELECT g.type, g.type_no, g.tran_date, g.amount, g.memo_,
               g.account, a.account_name
        FROM {$pref}gl_trans g
        LEFT JOIN {$pref}chart_master a ON g.account = a.account_code
        WHERE g.memo_        LIKE '%{$term}%'
           OR g.type_no      LIKE '%{$term}%'
           OR g.account      LIKE '%{$term}%'
           OR a.account_name LIKE '%{$term}%'
        GROUP BY g.type, g.type_no, g.tran_date
        ORDER BY g.tran_date DESC, g.type_no DESC
        LIMIT {$limit}
    ");
    $gl_types    = [0=>'Journal',1=>'Payment',2=>'Receipt',4=>'Transfer',
                    10=>'Sales Inv',11=>'Credit Note',20=>'Purch Inv',25=>'Bank Deposit'];
    $gl_types_ar = [0=>'قيد يومية',1=>'دفعة',2=>'إيصال',4=>'تحويل',
                    10=>'فاتورة مبيعات',11=>'إشعار دائن',20=>'فاتورة مشتريات',25=>'إيداع بنكي'];
    foreach ($rows as $r) {
        $tid      = (int)$r['type'];
        $type_lbl = $gl_types[$tid] ?? 'Entry #'.$tid;
        $type_ar  = $gl_types_ar[$tid] ?? 'قيد';
        $sub = implode(' · ', array_filter([
            $r['tran_date'],
            $r['account'].' '.$r['account_name'],
            $r['memo_'] ? mb_substr($r['memo_'], 0, 60) : '',
        ]));
        $display_lbl = $is_ar ? $type_ar : $type_lbl;
        $results[] = [
            'type'    => 'journal',
            'type_ar' => $type_ar,
            'icon'    => 'book',
            'color'   => CLR_JOURNAL,
            'title'   => $display_lbl . ' #' . $r['type_no'],
            'subtitle'=> $sub,
            'url'     => '../gl/view/gl_trans_view.php?type_id='.$tid.'&trans_no='.(int)$r['type_no'],
            'id'      => $r['type_no'],
        ];
    }
}

/* ════════════ 7. BANK TRANSACTIONS ════════════
   bank_trans: id, type, trans_no, bank_act, ref, trans_date, amount, memo_ (NO memo_ column!)
   bank_accounts: id, bank_account_name, bank_curr_code ...
*/
if ($section === 'all' || $section === 'bank') {
    $rows = gs_q("
        SELECT bt.id, bt.type, bt.trans_no, bt.trans_date, bt.ref, bt.amount,
               ba.bank_account_name, ba.bank_curr_code
        FROM {$pref}bank_trans bt
        LEFT JOIN {$pref}bank_accounts ba ON bt.bank_act = ba.id
        WHERE bt.ref               LIKE '%{$term}%'
           OR ba.bank_account_name LIKE '%{$term}%'
           OR bt.amount            LIKE '%{$term}%'
           OR bt.trans_no          LIKE '%{$term}%'
        ORDER BY bt.trans_date DESC
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $amt = number_format((float)$r['amount'], 2);
        $cur = $r['bank_curr_code'] ?? '';
        $sub = implode(' · ', array_filter([
            $r['bank_account_name'],
            $r['trans_date'],
            $cur . ' ' . $amt,
        ]));
        $bank_label = $r['bank_account_name'] ?? ($is_ar ? 'بنك' : 'Bank');
        $results[] = [
            'type'    => 'bank',
            'type_ar' => 'معاملة بنكية',
            'icon'    => 'credit-card',
            'color'   => CLR_BANK,
            'title'   => $bank_label . ' — ' . ($r['ref'] ?: '#'.$r['trans_no']),
            'subtitle'=> $sub,
            'url'     => '../gl/inquiry/bank_inquiry.php?bank_account=' . (int)$r['id'],
            'id'      => $r['id'],
        ];
    }
}

/* ════════════ 8. GL ACCOUNTS ════════════
   chart_master: account_code, account_code2, account_name, account_type, inactive
   chart_types:  id, name, class_id, parent, inactive
*/
if ($section === 'all' || $section === 'accounts') {
    $rows = gs_q("
        SELECT cm.account_code, cm.account_name, cm.account_type,
               ct.name AS type_name
        FROM {$pref}chart_master cm
        LEFT JOIN {$pref}chart_types ct ON cm.account_type = ct.id
        WHERE cm.account_name LIKE '%{$term}%'
           OR cm.account_code LIKE '%{$term}%'
           OR ct.name         LIKE '%{$term}%'
        ORDER BY CASE WHEN cm.account_code LIKE '{$term}%' THEN 0 ELSE 1 END, cm.account_code
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $results[] = [
            'type'    => 'account',
            'type_ar' => 'حساب',
            'icon'    => 'layers',
            'color'   => CLR_ACCOUNT,
            'title'   => $r['account_code'] . ' — ' . $r['account_name'],
            'subtitle'=> $r['type_name'] ?? ('Type ' . $r['account_type']),
            'url'     => '../gl/inquiry/gl_account_inquiry.php?account=' . urlencode($r['account_code']),
            'id'      => $r['account_code'],
        ];
    }
}

/* ════════════ 9. DIMENSIONS ════════════
   dimensions: id, reference, name, type_, closed, date_, due_date
*/
if ($section === 'all' || $section === 'dimensions') {
    $rows = gs_q("
        SELECT id, reference, name, date_, closed
        FROM {$pref}dimensions
        WHERE name      LIKE '%{$term}%'
           OR reference LIKE '%{$term}%'
        ORDER BY reference
        LIMIT {$limit}
    ");
    foreach ($rows as $r) {
        $status = ((int)($r['closed'] ?? 0))
            ? ($is_ar ? 'مغلق' : 'Closed')
            : ($is_ar ? 'مفتوح' : 'Open');
        $results[] = [
            'type'    => 'dimension',
            'type_ar' => 'بُعد تحليلي',
            'icon'    => 'hexagon',
            'color'   => CLR_DIMENSION,
            'title'   => $r['reference'] . ' — ' . $r['name'],
            'subtitle'=> $status . ($r['date_'] ? ' · ' . $r['date_'] : ''),
            'url'     => '../dimensions/dimension_entry.php?selected_id=' . (int)$r['id'],
            'id'      => $r['id'],
        ];
    }
}

/* ══════════════════════════════════════════════════════════════
   8. SORT — exact-start matches first
══════════════════════════════════════════════════════════════ */
usort($results, function ($a, $b) use ($q) {
    $aS = (int)(mb_stripos($a['title'], $q) === 0);
    $bS = (int)(mb_stripos($b['title'], $q) === 0);
    if ($bS !== $aS) return $bS - $aS;
    $aC = (int)(mb_stripos($a['title'], $q) !== false);
    $bC = (int)(mb_stripos($b['title'], $q) !== false);
    return $bC - $aC;
});

$total   = count($results);
$results = array_slice($results, 0, $limit);

/* ══════════════════════════════════════════════════════════════
   9. OUTPUT
══════════════════════════════════════════════════════════════ */
echo json_encode(
    ['q' => $q, 'total' => $total, 'shown' => count($results), 'results' => $results, 'lang' => $is_ar ? 'ar' : 'en'],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

mysqli_close($db);
