<?php
$path_to_root = "..";
$page_security = 'SA_GLANALYTIC';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';

page($is_ar ? "\xd8\xa7\xd9\x84\xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9" : "Budget & Financial Planning");

// Get current fiscal year
$sql_fy = "SELECT id, begin, end FROM ".TB_PREF."fiscal_year WHERE closed = 0 ORDER BY begin DESC LIMIT 1";
$fy_result = db_query($sql_fy, "Could not get fiscal year");
$fiscal_year = db_fetch($fy_result);

$fy_begin = $fiscal_year ? $fiscal_year['begin'] : date('Y-01-01');
$fy_end = $fiscal_year ? $fiscal_year['end'] : date('Y-12-31');
$fy_label = $fiscal_year ? (substr($fiscal_year['begin'], 0, 4) . ' - ' . substr($fiscal_year['end'], 0, 4)) : date('Y');

// Fetch budget data grouped by class -> type -> account with totals
$sql = "SELECT coa.account_code, coa.account_name,
        ct.id AS type_id, ct.name AS type_name,
        cc.cid AS class_id, cc.class_name, cc.ctype,
        COALESCE(SUM(bt.amount), 0) AS budget_total
    FROM ".TB_PREF."chart_master coa
    JOIN ".TB_PREF."chart_types ct ON coa.account_type = ct.id
    JOIN ".TB_PREF."chart_class cc ON ct.class_id = cc.cid
    LEFT JOIN ".TB_PREF."budget_trans bt ON bt.account = coa.account_code
        AND bt.tran_date >= ".db_escape($fy_begin)."
        AND bt.tran_date <= ".db_escape($fy_end)."
    GROUP BY coa.account_code, coa.account_name, ct.id, ct.name, cc.cid, cc.class_name, cc.ctype
    HAVING budget_total <> 0
    ORDER BY cc.cid, ct.id, coa.account_code";
$result = db_query($sql, "Could not retrieve budget data");

$data = array();
while ($row = db_fetch($result)) {
    $data[] = $row;
}

// Also get actual totals for comparison
$sql_actual = "SELECT gl.account,
        COALESCE(SUM(gl.amount), 0) AS actual_total
    FROM ".TB_PREF."gl_trans gl
    WHERE gl.tran_date >= ".db_escape($fy_begin)."
      AND gl.tran_date <= ".db_escape($fy_end)."
    GROUP BY gl.account";
$act_result = db_query($sql_actual, "Could not get actuals");
$actuals = array();
while ($row = db_fetch($act_result)) {
    $actuals[$row['account']] = floatval($row['actual_total']);
}

// Build tree
$tree = array();
foreach ($data as $row) {
    $cid = $row['class_id'];
    $tid = $row['type_id'];
    if (!isset($tree[$cid])) {
        $tree[$cid] = array('name' => $row['class_name'], 'ctype' => $row['ctype'], 'types' => array(), 'total' => 0);
    }
    if (!isset($tree[$cid]['types'][$tid])) {
        $tree[$cid]['types'][$tid] = array('name' => $row['type_name'], 'accounts' => array(), 'total' => 0);
    }
    $budget = floatval($row['budget_total']);
    $actual = isset($actuals[$row['account_code']]) ? $actuals[$row['account_code']] : 0;
    $row['actual_total'] = $actual;
    $row['variance'] = $budget - $actual;
    $tree[$cid]['types'][$tid]['accounts'][] = $row;
    $tree[$cid]['types'][$tid]['total'] += $budget;
    $tree[$cid]['total'] += $budget;
}

$dir_attr = $is_ar ? ' dir="rtl"' : '';
$title = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9 \xd9\x88\xd8\xa7\xd9\x84\xd8\xaa\xd8\xae\xd8\xb7\xd9\x8a\xd8\xb7 \xd8\xa7\xd9\x84\xd9\x85\xd8\xa7\xd9\x84\xd9\x8a" : "Budget & Financial Planning";
$subtitle = $is_ar ? "\xd8\xaa\xd8\xad\xd9\x84\xd9\x8a\xd9\x84 \xd8\xa7\xd9\x84\xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9 \xd9\x85\xd9\x82\xd8\xa7\xd8\xa8\xd9\x84 \xd8\xa7\xd9\x84\xd9\x81\xd8\xb9\xd9\x84\xd9\x8a" : "Budget vs Actual Analysis";
$lbl_budget = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9" : "Budget";
$lbl_actual = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x81\xd8\xb9\xd9\x84\xd9\x8a" : "Actual";
$lbl_variance = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x81\xd8\xb1\xd9\x82" : "Variance";
$lbl_fiscal = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xb3\xd9\x86\xd8\xa9 \xd8\xa7\xd9\x84\xd9\x85\xd8\xa7\xd9\x84\xd9\x8a\xd8\xa9" : "Fiscal Year";
$lbl_budgeted = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x85\xd8\xae\xd8\xb7\xd8\xb7" : "Budgeted";
$lbl_no_data = $is_ar ? "\xd9\x84\xd8\xa7 \xd8\xaa\xd9\x88\xd8\xac\xd8\xaf \xd8\xa8\xd9\x8a\xd8\xa7\xd9\x86\xd8\xa7\xd8\xaa \xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9" : "No budget data found for the current fiscal year";
$lbl_accounts_budgeted = $is_ar ? "\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa \xd9\x85\xd8\xae\xd8\xb7\xd8\xb7\xd8\xa9" : "Accounts Budgeted";
$lbl_total_budget = $is_ar ? "\xd8\xa5\xd8\xac\xd9\x85\xd8\xa7\xd9\x84\xd9\x8a \xd8\xa7\xd9\x84\xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9" : "Total Budget";

echo <<<STYLES
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
.vip-tree-container {
    font-family: 'DM Sans', sans-serif;
    max-width: 1060px;
    margin: 20px auto;
    padding: 0 16px;
}
.vip-tree-container * { box-sizing: border-box; }
.vip-tree-header {
    background: linear-gradient(135deg, #050E1F 0%, #0A1A3A 50%, #050E1F 100%);
    border: 1px solid rgba(212,175,55,0.3);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.vip-tree-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #D4AF37, transparent);
}
.vip-tree-header h2 {
    font-family: 'Playfair Display', serif;
    color: #D4AF37;
    font-size: 26px;
    margin: 0 0 6px 0;
    font-weight: 700;
}
.vip-tree-header .hdr-sub {
    color: rgba(255,255,255,0.6);
    font-size: 14px;
    margin: 0;
}
.vip-tree-header .hdr-fy {
    display: inline-block;
    margin-top: 10px;
    background: rgba(212,175,55,0.12);
    border: 1px solid rgba(212,175,55,0.25);
    color: #D4AF37;
    padding: 5px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}
.vip-stats-row {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.vip-stat-card {
    flex: 1;
    min-width: 150px;
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 12px;
    padding: 18px 20px;
    text-align: center;
}
.vip-stat-card .stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: #D4AF37;
    font-weight: 700;
}
.vip-stat-card .stat-label {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.vip-tree-card {
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 14px;
    margin-bottom: 16px;
    overflow: hidden;
}
/* Level 1: Class */
.vip-bc { border-bottom: 1px solid rgba(212,175,55,0.1); }
.vip-bc:last-child { border-bottom: none; }
.vip-bc > summary {
    display: flex;
    align-items: center;
    padding: 18px 24px;
    cursor: pointer;
    color: #D4AF37;
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 17px;
    list-style: none;
    transition: background 0.2s;
    gap: 14px;
    user-select: none;
}
.vip-bc > summary::-webkit-details-marker { display: none; }
.vip-bc > summary:hover { background: rgba(212,175,55,0.05); }
.vip-bc > summary .bc-icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
    background: rgba(212,175,55,0.12);
    border: 1px solid rgba(212,175,55,0.25);
}
.vip-bc > summary .bc-label { flex: 1; }
.vip-bc > summary .bc-total {
    color: #D4AF37;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
}
.vip-bc > summary .bc-arrow {
    color: rgba(212,175,55,0.5);
    transition: transform 0.3s;
    font-size: 12px;
}
.vip-bc[open] > summary .bc-arrow { transform: rotate(90deg); }

/* Level 2: Type */
.vip-bt-wrap { padding: 0 24px 14px 64px; }
[dir="rtl"] .vip-bt-wrap { padding: 0 64px 14px 24px; }
.vip-bt {
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 10px;
    margin-bottom: 8px;
    background: rgba(255,255,255,0.015);
    position: relative;
}
.vip-bt::before {
    content: '';
    position: absolute;
    left: -20px; top: 20px;
    width: 16px; height: 1px;
    background: rgba(212,175,55,0.15);
}
[dir="rtl"] .vip-bt::before { left: auto; right: -20px; }
.vip-bt > summary {
    display: flex;
    align-items: center;
    padding: 13px 18px;
    cursor: pointer;
    color: #fff;
    font-weight: 500;
    font-size: 14px;
    list-style: none;
    transition: background 0.2s;
    gap: 10px;
    user-select: none;
}
.vip-bt > summary::-webkit-details-marker { display: none; }
.vip-bt > summary:hover { background: rgba(212,175,55,0.04); }
.vip-bt > summary .bt-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    background: rgba(241,196,15,0.12);
    border: 1px solid rgba(241,196,15,0.25);
}
.vip-bt > summary .bt-label { flex: 1; }
.vip-bt > summary .bt-total {
    color: rgba(212,175,55,0.8);
    font-size: 13px;
    font-weight: 600;
}
.vip-bt > summary .bt-arrow {
    color: rgba(255,255,255,0.3);
    transition: transform 0.3s;
    font-size: 11px;
}
.vip-bt[open] > summary .bt-arrow { transform: rotate(90deg); }

/* Level 3: Account budget rows */
.vip-ba-wrap { padding: 6px 18px 14px 46px; }
[dir="rtl"] .vip-ba-wrap { padding: 6px 46px 14px 18px; }

.vip-ba-hdr {
    display: flex;
    padding: 6px 14px 8px;
    gap: 10px;
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid rgba(212,175,55,0.08);
    margin-bottom: 6px;
}
.vip-ba-hdr .bh-code { min-width: 70px; }
.vip-ba-hdr .bh-name { flex: 1; }
.vip-ba-hdr .bh-num { min-width: 90px; text-align: right; }
[dir="rtl"] .vip-ba-hdr .bh-num { text-align: left; }

.vip-ba {
    display: flex;
    align-items: center;
    padding: 9px 14px;
    margin-bottom: 3px;
    border-radius: 8px;
    border: 1px solid rgba(212,175,55,0.04);
    transition: all 0.2s;
    gap: 10px;
    position: relative;
}
.vip-ba:hover {
    background: rgba(212,175,55,0.04);
    border-color: rgba(212,175,55,0.18);
}
.vip-ba::before {
    content: '';
    position: absolute;
    left: -14px; top: 50%;
    width: 10px; height: 1px;
    background: rgba(212,175,55,0.1);
}
[dir="rtl"] .vip-ba::before { left: auto; right: -14px; }
.vip-ba .ba-code {
    color: #D4AF37;
    font-size: 12px;
    font-weight: 600;
    min-width: 70px;
}
.vip-ba .ba-name {
    flex: 1;
    color: rgba(255,255,255,0.8);
    font-size: 13px;
}
.vip-ba .ba-num {
    font-size: 12px;
    font-weight: 600;
    min-width: 90px;
    text-align: right;
}
[dir="rtl"] .vip-ba .ba-num { text-align: left; }
.ba-budget { color: rgba(255,255,255,0.7); }
.ba-actual { color: rgba(52,152,219,0.9); }
.ba-var-pos { color: #2ecc71; }
.ba-var-neg { color: #e74c3c; }

/* Variance bar */
.vip-ba .ba-bar-wrap {
    width: 60px;
    height: 6px;
    background: rgba(255,255,255,0.06);
    border-radius: 3px;
    overflow: hidden;
    flex-shrink: 0;
}
.vip-ba .ba-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.4s;
}
.ba-bar-green { background: linear-gradient(90deg, #2ecc71, #27ae60); }
.ba-bar-red { background: linear-gradient(90deg, #e74c3c, #c0392b); }
.ba-bar-gold { background: linear-gradient(90deg, #D4AF37, #B8960C); }

.vip-empty {
    text-align: center;
    padding: 48px 24px;
    color: rgba(255,255,255,0.4);
    font-size: 15px;
}
.vip-empty .empty-icon { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
</style>
STYLES;

// Stats
$total_budget_amt = 0;
$total_actual_amt = 0;
foreach ($data as $row) {
    $total_budget_amt += floatval($row['budget_total']);
    $actual = isset($actuals[$row['account_code']]) ? $actuals[$row['account_code']] : 0;
    $total_actual_amt += $actual;
}

echo '<div class="vip-tree-container"' . $dir_attr . '>';

echo '<div class="vip-tree-header">';
echo '<h2>' . htmlspecialchars($title) . '</h2>';
echo '<p class="hdr-sub">' . htmlspecialchars($subtitle) . '</p>';
echo '<span class="hdr-fy">&#128197; ' . htmlspecialchars($lbl_fiscal) . ': ' . htmlspecialchars($fy_label) . '</span>';
echo '</div>';

echo '<div class="vip-stats-row">';
echo '<div class="vip-stat-card"><div class="stat-value">' . count($data) . '</div><div class="stat-label">' . htmlspecialchars($lbl_accounts_budgeted) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . number_format(abs($total_budget_amt), 0) . '</div><div class="stat-label">' . htmlspecialchars($lbl_total_budget) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . number_format(abs($total_actual_amt), 0) . '</div><div class="stat-label">' . htmlspecialchars($lbl_actual) . '</div></div>';
$total_var = $total_budget_amt - $total_actual_amt;
echo '<div class="vip-stat-card"><div class="stat-value" style="color:' . ($total_var >= 0 ? '#2ecc71' : '#e74c3c') . '">' . number_format(abs($total_var), 0) . '</div><div class="stat-label">' . htmlspecialchars($lbl_variance) . '</div></div>';
echo '</div>';

if (empty($tree)) {
    echo '<div class="vip-tree-card"><div class="vip-empty">';
    echo '<div class="empty-icon">&#128202;</div>';
    echo '<div>' . htmlspecialchars($lbl_no_data) . '</div>';
    echo '</div></div>';
} else {
    echo '<div class="vip-tree-card">';
    foreach ($tree as $cid => $class_data) {
        $cls_icon = (intval($class_data['ctype']) == 0) ? '&#127974;' : '&#128200;';
        echo '<details class="vip-bc" open>';
        echo '<summary>';
        echo '<span class="bc-icon">' . $cls_icon . '</span>';
        echo '<span class="bc-label">' . htmlspecialchars($class_data['name']) . '</span>';
        echo '<span class="bc-total">' . number_format(abs($class_data['total']), 2) . '</span>';
        echo '<span class="bc-arrow">&#9654;</span>';
        echo '</summary>';
        echo '<div class="vip-bt-wrap">';

        foreach ($class_data['types'] as $tid => $type_data) {
            echo '<details class="vip-bt">';
            echo '<summary>';
            echo '<span class="bt-icon">&#128196;</span>';
            echo '<span class="bt-label">' . htmlspecialchars($type_data['name']) . '</span>';
            echo '<span class="bt-total">' . number_format(abs($type_data['total']), 2) . '</span>';
            echo '<span class="bt-arrow">&#9654;</span>';
            echo '</summary>';
            echo '<div class="vip-ba-wrap">';

            // Column headers
            echo '<div class="vip-ba-hdr">';
            echo '<span class="bh-code">' . htmlspecialchars($lbl_budget) . '</span>';
            echo '<span class="bh-name"></span>';
            echo '<span class="bh-num">' . htmlspecialchars($lbl_budgeted) . '</span>';
            echo '<span class="bh-num">' . htmlspecialchars($lbl_actual) . '</span>';
            echo '<span class="bh-num">' . htmlspecialchars($lbl_variance) . '</span>';
            echo '<span style="width:60px"></span>';
            echo '</div>';

            foreach ($type_data['accounts'] as $acct) {
                $budget_val = floatval($acct['budget_total']);
                $actual_val = floatval($acct['actual_total']);
                $var_val = $acct['variance'];
                $var_class = ($var_val >= 0) ? 'ba-var-pos' : 'ba-var-neg';

                // Compute bar percentage
                $bar_pct = 0;
                if (abs($budget_val) > 0) {
                    $bar_pct = min(100, abs($actual_val / $budget_val) * 100);
                }
                $bar_class = ($bar_pct > 100) ? 'ba-bar-red' : (($bar_pct > 80) ? 'ba-bar-gold' : 'ba-bar-green');

                echo '<div class="vip-ba">';
                echo '<span class="ba-code">' . htmlspecialchars($acct['account_code']) . '</span>';
                echo '<span class="ba-name">' . htmlspecialchars($acct['account_name']) . '</span>';
                echo '<span class="ba-num ba-budget">' . number_format(abs($budget_val), 2) . '</span>';
                echo '<span class="ba-num ba-actual">' . number_format(abs($actual_val), 2) . '</span>';
                echo '<span class="ba-num ' . $var_class . '">' . ($var_val >= 0 ? '' : '-') . number_format(abs($var_val), 2) . '</span>';
                echo '<span class="ba-bar-wrap"><span class="ba-bar ' . $bar_class . '" style="width:' . round($bar_pct) . '%"></span></span>';
                echo '</div>';
            }

            echo '</div>';
            echo '</details>';
        }

        echo '</div>';
        echo '</details>';
    }
    echo '</div>';
}

echo '</div>';

end_page();
