<?php
/**********************************************************************
    Copyright (C) VIPAccSystem, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_GLTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

include($path_to_root . "/includes/db_pager.inc");

include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");

$js = '';
set_focus('account');
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "General Ledger Inquiry"), false, false, '', $js);

//----------------------------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('Show')) 
{
	$Ajax->activate('trans_tbl');
}

if (isset($_GET["account"]))
	$_POST["account"] = $_GET["account"];
if (isset($_GET["TransFromDate"]))
	$_POST["TransFromDate"] = $_GET["TransFromDate"];
if (isset($_GET["TransToDate"]))
	$_POST["TransToDate"] = $_GET["TransToDate"];
if (isset($_GET["Dimension"]))
	$_POST["Dimension"] = $_GET["Dimension"];
if (isset($_GET["Dimension2"]))
	$_POST["Dimension2"] = $_GET["Dimension2"];
if (isset($_GET["amount_min"]))
	$_POST["amount_min"] = $_GET["amount_min"];
if (isset($_GET["amount_max"]))
	$_POST["amount_max"] = $_GET["amount_max"];

if (!isset($_POST["amount_min"]))
	$_POST["amount_min"] = price_format(0);
if (!isset($_POST["amount_max"]))
	$_POST["amount_max"] = price_format(0);

//----------------------------------------------------------------------------------------------------

function gl_inquiry_controls()
{
	$dim = get_company_pref('use_dimension');
    start_form();

    start_table(TABLESTYLE_NOBORDER);
	start_row();
    gl_all_accounts_list_cells(_("Account:"), 'account', null, false, false, _("All Accounts"));
	date_cells(_("from:"), 'TransFromDate', '', null, -user_transaction_days());
	date_cells(_("to:"), 'TransToDate');
    end_row();
	end_table();

	start_table(TABLESTYLE_NOBORDER);
	start_row();
	if ($dim >= 1)
		dimensions_list_cells(_("Dimension")." 1:", 'Dimension', null, true, " ", false, 1);
	if ($dim > 1)
		dimensions_list_cells(_("Dimension")." 2:", 'Dimension2', null, true, " ", false, 2);

	ref_cells(_("Memo:"), 'Memo', '',null, _('Enter memo fragment or leave empty'));
	small_amount_cells(_("Amount min:"), 'amount_min', null, " ");
	small_amount_cells(_("Amount max:"), 'amount_max', null, " ");
	submit_cells('Show',_("Show"),'','', 'default');
	end_row();
	end_table();

	echo '<hr>';
    end_form();
}

//----------------------------------------------------------------------------------------------------

function show_results()
{
	global $path_to_root, $systypes_array;

	if (!isset($_POST["account"]))
		$_POST["account"] = null;

	$act_name = $_POST["account"] ? get_gl_account_name($_POST["account"]) : "";
	$dim = get_company_pref('use_dimension');

    /*Now get the transactions  */
    if (!isset($_POST['Dimension']))
    	$_POST['Dimension'] = 0;
    if (!isset($_POST['Dimension2']))
    	$_POST['Dimension2'] = 0;
	$result = get_gl_transactions($_POST['TransFromDate'], $_POST['TransToDate'], -1,
    	$_POST["account"], $_POST['Dimension'], $_POST['Dimension2'], null,
    	input_num('amount_min'), input_num('amount_max'), null, null, $_POST['Memo']);

	$colspan = ($dim == 2 ? "7" : ($dim == 1 ? "6" : "5"));

	if ($_POST["account"] != null)
		display_heading($_POST["account"]. "&nbsp;&nbsp;&nbsp;".$act_name);

	// Only show balances if an account is specified AND we're not filtering by amounts
	$show_balances = $_POST["account"] != null && 
                     input_num("amount_min") == 0 && 
                     input_num("amount_max") == 0;
		
	start_table(TABLESTYLE);
	
	$first_cols = array(_("Type"), _("#"), _("Reference"), _("Date"));
	
	if ($_POST["account"] == null)
	    $account_col = array(_("Account"));
	else
	    $account_col = array();
	
	if ($dim == 2)
		$dim_cols = array(_("Dimension")." 1", _("Dimension")." 2");
	elseif ($dim == 1)
		$dim_cols = array(_("Dimension"));
	else
		$dim_cols = array();
	
	if ($show_balances)
	    $remaining_cols = array(_("Person/Item"), _("Debit"), _("Credit"), _("Balance"), _("Memo"), "");
	else
	    $remaining_cols = array(_("Person/Item"), _("Debit"), _("Credit"), _("Memo"), "");
	    
	$th = array_merge($first_cols, $account_col, $dim_cols, $remaining_cols);
			
	table_header($th);
	if ($_POST["account"] != null && is_account_balancesheet($_POST["account"]))
		$begin = "";
	else
	{
		$begin = get_fiscalyear_begin_for_date($_POST['TransFromDate']);
		if (date1_greater_date2($begin, $_POST['TransFromDate']))
			$begin = $_POST['TransFromDate'];
		$begin = add_days($begin, -1);
	}

	$bfw = 0;
	if ($show_balances) {
	    $bfw = get_gl_balance_from_to($begin, $_POST['TransFromDate'], $_POST["account"], $_POST['Dimension'], $_POST['Dimension2']);
    	start_row("class='inquirybg'");
    	label_cell("<b>"._("Opening Balance")." - ".$_POST['TransFromDate']."</b>", "colspan=$colspan");
    	display_debit_or_credit_cells($bfw, true);
    	label_cell("");
    	label_cell("");
    	end_row();
	}
	
	$running_total = $bfw;
	$j = 1;
	$k = 0; //row colour counter

	while ($myrow = db_fetch($result))
	{

    	alt_table_row_color($k);

    	$running_total += $myrow["amount"];

    	$trandate = sql2date($myrow["tran_date"]);

    	label_cell($systypes_array[$myrow["type"]]);
		label_cell(get_gl_view_str($myrow["type"], $myrow["type_no"], $myrow["type_no"], true));
		label_cell(get_trans_view_str($myrow["type"],$myrow["type_no"],$myrow['reference']));
    	label_cell($trandate);
    	
    	if ($_POST["account"] == null)
    	    label_cell($myrow["account"] . ' ' . get_gl_account_name($myrow["account"]));
    	
		if ($dim >= 1)
			label_cell(get_dimension_string($myrow['dimension_id'], true));
		if ($dim > 1)
			label_cell(get_dimension_string($myrow['dimension2_id'], true));
		label_cell(payment_person_name($myrow["person_type_id"],$myrow["person_id"]));
		display_debit_or_credit_cells($myrow["amount"]);
		if ($show_balances)
		    amount_cell($running_total);
		if ($myrow['memo_'] == "")
			$myrow['memo_'] = get_comments_string($myrow['type'], $myrow['type_no']);
    	label_cell($myrow['memo_']);
        if ($myrow["type"] == ST_JOURNAL)
            echo "<td>" . trans_editor_link( $myrow["type"], $myrow["type_no"]) . "</td>";
        else
            label_cell("");
    	end_row();

    	$j++;
    	if ($j == 12)
    	{
    		$j = 1;
    		table_header($th);
    	}
	}
	//end of while loop

	if ($show_balances) {
    	start_row("class='inquirybg'");
    	label_cell("<b>" . _("Ending Balance") ." - ".$_POST['TransToDate']. "</b>", "colspan=$colspan");
    	display_debit_or_credit_cells($running_total, true);
    	label_cell("");
    	label_cell("");
    	end_row();
	}

	end_table(2);
	if (db_num_rows($result) == 0)
		display_note(_("No general ledger transactions have been created for the specified criteria."), 0, 1);

}

//----------------------------------------------------------------------------------------------------

gl_inquiry_controls();

div_start('trans_tbl');

if (get_post('Show') || get_post('account'))
    show_results();

div_end();

//----------------------------------------------------------------------------------------------------
/* ═══════════════════════════════════════════════════════════════════
   VIP STYLE INJECTION — GL Account Inquiry — Snow White Glass Theme
   ALL selectors scoped to #main_div — ZERO effect on header/nav
   ═══════════════════════════════════════════════════════════════════ */
echo "
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap');

/* ── PALETTE ─────────────────────────────────────────────────── */
:root {
    --navy:         #0A2342;
    --royal:        #1a5fb4;
    --royal-mid:    #164E8F;
    --gold:         #D4AF37;
    --gold-light:   #F0D060;
    --gold-dark:    #a8882a;
    --charcoal:     #111827;       /* نص أسود فحمي لامع */
    --charcoal-2:   #1f2937;
    --snow:         #FAFCFF;       /* أبيض ثلجي */
    --glass-bg:     rgba(255,255,255,0.82);
    --glass-border: rgba(255,255,255,0.65);
    --glass-shadow: 0 8px 40px rgba(10,35,66,0.11), 0 2px 12px rgba(10,35,66,0.07);
    --field-bg:     rgba(248,251,255,0.90);
    --field-border: rgba(180,200,228,0.80);
    --debit-bg:     #fff3f3;
    --credit-bg:    #f3fff7;
    --debit-clr:    #b91c1c;
    --credit-clr:   #166534;
    --row-alt:      #f5f8ff;
    --row-hover:    #edf3ff;
    --gold-glow:    0 4px 18px rgba(212,175,55,0.28);
}

/* ── BODY BACKGROUND ──────────────────────────────────────────
   Safe: only targets body color, no nav/header elements         */
body { background: #e8edf5 !important; }

/* ══════════════════════════════════════════════════════════════
   ALL RULES SCOPED TO #main_div — Header/Nav untouched
   ══════════════════════════════════════════════════════════════ */

#main_div, #main_div * {
    font-family: 'Cairo', 'Tajawal', Arial, sans-serif !important;
}

/* ── PAGE WRAPPER PADDING ─────────────────────────────────── */
#main_div {
    padding: 8px 16px 24px !important;
}

/* ── PAGE TITLE ───────────────────────────────────────────── */
#main_div .page_title,
#main_div div.page_title,
#main_div h1.page_title,
#main_div #page_title {
    background: linear-gradient(120deg, var(--navy) 0%, var(--royal-mid) 55%, var(--royal) 100%) !important;
    color: #fff !important;
    font-size: 23px !important;
    font-weight: 900 !important;
    text-align: center !important;
    padding: 15px 36px !important;
    border-radius: 16px !important;
    border-bottom: 4px solid var(--gold) !important;
    box-shadow: var(--glass-shadow), 0 6px 22px rgba(212,175,55,0.18) !important;
    margin: 14px auto 22px !important;
    width: 100% !important;
    text-shadow: 0 2px 10px rgba(0,0,0,0.30) !important;
    letter-spacing: 0.5px !important;
}

/* ══════════════════════════════════════════════════════════════
   GLASS FILTER FORM — Snow White Frosted Card
   ══════════════════════════════════════════════════════════════ */
#main_div form {
    background: var(--glass-bg) !important;
    backdrop-filter: blur(18px) saturate(1.4) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.4) !important;
    border-radius: 20px !important;
    border: 1.5px solid var(--glass-border) !important;
    box-shadow:
        var(--glass-shadow),
        inset 0 1px 0 rgba(255,255,255,0.95),
        inset 0 -1px 0 rgba(200,220,240,0.30) !important;
    padding: 0 !important;
    margin: 0 0 24px !important;
    width: 100% !important;
    overflow: hidden !important;
    position: relative !important;
}

/* Glass shimmer top strip */
#main_div form::before {
    content: '';
    display: block;
    height: 3px;
    width: 100%;
    background: linear-gradient(90deg,
        transparent 0%,
        var(--gold) 20%,
        var(--gold-light) 50%,
        var(--gold) 80%,
        transparent 100%
    );
    opacity: 0.75;
}

/* Inner padding wrapper for all form content */
#main_div form > table {
    padding: 20px 24px 16px !important;
    width: 100% !important;
    background: transparent !important;
}

/* ── FORM LABELS ──────────────────────────────────────────── */
#main_div form td,
#main_div .noborder td {
    font-size: 13px !important;
    font-weight: 800 !important;
    color: var(--charcoal) !important;
    padding: 7px 10px !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
    letter-spacing: 0.15px !important;
}

/* ── FIELD GROUPS — glass inner frames ────────────────────── */
/* Each label+input pair sits in a subtle glass pill */
#main_div form tr {
    background: transparent !important;
}

/* ── TEXT / SELECT INPUTS ─────────────────────────────────── */
#main_div form input[type='text'],
#main_div form input[type='number'],
#main_div form input[type='date'],
#main_div form select,
#main_div form .combo {
    font-size: 13px !important;
    font-weight: 700 !important;
    color: var(--charcoal) !important;
    background: var(--field-bg) !important;
    border: 1.5px solid var(--field-border) !important;
    border-radius: 10px !important;
    padding: 7px 12px !important;
    min-width: 130px !important;
    box-shadow: inset 0 2px 5px rgba(10,35,66,0.06), 0 1px 3px rgba(255,255,255,0.9) !important;
    transition: border-color 0.22s, box-shadow 0.22s, background 0.22s !important;
    outline: none !important;
}
#main_div form input[type='text']:focus,
#main_div form input[type='number']:focus,
#main_div form select:focus {
    border-color: var(--royal) !important;
    background: #fff !important;
    box-shadow: 0 0 0 3.5px rgba(26,95,180,0.13), inset 0 2px 5px rgba(10,35,66,0.04) !important;
}
#main_div form input[type='text']:hover,
#main_div form select:hover {
    border-color: rgba(26,95,180,0.45) !important;
}

/* ── SUBMIT BUTTON ────────────────────────────────────────── */
#main_div form input[type='submit'],
#main_div form .inputsubmit,
#main_div form button[type='submit'] {
    background: linear-gradient(135deg, var(--royal-mid) 0%, var(--royal) 60%, #2471d4 100%) !important;
    color: #fff !important;
    border: none !important;
    padding: 9px 30px !important;
    border-radius: 11px !important;
    font-size: 14px !important;
    font-weight: 900 !important;
    cursor: pointer !important;
    letter-spacing: 0.5px !important;
    box-shadow: 0 4px 16px rgba(26,95,180,0.35), inset 0 1px 0 rgba(255,255,255,0.22) !important;
    transition: transform 0.18s, box-shadow 0.22s !important;
    text-shadow: 0 1px 3px rgba(0,0,0,0.20) !important;
}
#main_div form input[type='submit']:hover {
    transform: translateY(-2px) scale(1.04) !important;
    box-shadow: 0 8px 28px rgba(26,95,180,0.45), inset 0 1px 0 rgba(255,255,255,0.22) !important;
}
#main_div form input[type='submit']:active {
    transform: translateY(0) scale(0.97) !important;
}

/* ── HR DIVIDER ───────────────────────────────────────────── */
#main_div hr {
    border: none !important;
    height: 1.5px !important;
    background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, var(--gold-light) 50%, var(--gold) 70%, transparent 100%) !important;
    opacity: 0.45 !important;
    margin: 14px 0 !important;
}

/* ── ACCOUNT HEADING ──────────────────────────────────────── */
#main_div .heading,
#main_div div.heading,
#main_div td.heading {
    background: linear-gradient(110deg, var(--navy) 0%, var(--royal-mid) 100%) !important;
    color: #fff !important;
    font-size: 15px !important;
    font-weight: 900 !important;
    text-align: center !important;
    padding: 12px 24px !important;
    border-radius: 12px !important;
    border-bottom: 3px solid var(--gold) !important;
    box-shadow: 0 5px 18px rgba(10,35,66,0.18) !important;
    margin: 0 0 14px !important;
    text-shadow: 0 1px 4px rgba(0,0,0,0.22) !important;
    letter-spacing: 0.4px !important;
}

/* ═══════════════════════════════════════════════════════════
   TRANSACTIONS TABLE — Glass White Luxury
   ═══════════════════════════════════════════════════════════ */
#main_div #trans_tbl {
    width: 100% !important;
    margin: 0 auto !important;
}

/* Table shell */
#main_div #trans_tbl table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    background: var(--snow) !important;
    border-radius: 18px !important;
    box-shadow: var(--glass-shadow) !important;
    border: 1.5px solid rgba(212,175,55,0.20) !important;
    overflow: hidden !important;
    margin-bottom: 22px !important;
}

/* ── TABLE HEADER ─────────────────────────────────────────── */
#main_div #trans_tbl th {
    background: linear-gradient(112deg, var(--navy) 0%, var(--royal-mid) 100%) !important;
    color: #fff !important;
    font-size: 12.5px !important;
    font-weight: 900 !important;
    text-align: center !important;
    padding: 12px 10px !important;
    border-bottom: 3px solid var(--gold) !important;
    white-space: nowrap !important;
    text-shadow: 0 1px 4px rgba(0,0,0,0.25) !important;
    letter-spacing: 0.2px !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 2 !important;
}
#main_div #trans_tbl th + th {
    border-right: 1px solid rgba(212,175,55,0.22) !important;
}

/* ── TABLE BODY CELLS ─────────────────────────────────────── */
#main_div #trans_tbl td {
    font-size: 12.5px !important;
    font-weight: 700 !important;
    color: var(--charcoal) !important;
    padding: 9px 10px !important;
    border-bottom: 1px solid #edf1f8 !important;
    text-align: center !important;
    transition: background 0.14s !important;
    vertical-align: middle !important;
}

/* ── ALTERNATING ROWS ─────────────────────────────────────── */
#main_div #trans_tbl tr:nth-child(even) td {
    background: rgba(245,248,255,0.80) !important;
}
#main_div #trans_tbl tr:nth-child(odd) td {
    background: var(--snow) !important;
}
#main_div #trans_tbl tr:not(.inquirybg):hover td {
    background: var(--row-hover) !important;
    color: var(--navy) !important;
}

/* ── OPENING / CLOSING BALANCE ROWS ──────────────────────── */
#main_div #trans_tbl tr.inquirybg td,
#main_div #trans_tbl .inquirybg td {
    background: linear-gradient(90deg, #0d2c52 0%, #1a5fb4 100%) !important;
    color: #fff !important;
    font-size: 13px !important;
    font-weight: 900 !important;
    border-top: 2px solid var(--gold) !important;
    border-bottom: 2px solid var(--gold) !important;
    text-shadow: 0 1px 3px rgba(0,0,0,0.25) !important;
    padding: 11px 12px !important;
}
#main_div #trans_tbl tr.inquirybg td:not(:first-child) {
    color: var(--gold-light) !important;
}

/* ── DEBIT ────────────────────────────────────────────────── */
#main_div #trans_tbl td.debit,
#main_div #trans_tbl .debit {
    color: var(--debit-clr) !important;
    background: var(--debit-bg) !important;
    font-weight: 900 !important;
}

/* ── CREDIT ───────────────────────────────────────────────── */
#main_div #trans_tbl td.credit,
#main_div #trans_tbl .credit {
    color: var(--credit-clr) !important;
    background: var(--credit-bg) !important;
    font-weight: 900 !important;
}

/* ── LINKS ────────────────────────────────────────────────── */
#main_div #trans_tbl td a {
    color: var(--royal) !important;
    font-weight: 800 !important;
    text-decoration: none !important;
    transition: color 0.18s !important;
}
#main_div #trans_tbl td a:hover {
    color: var(--gold-dark) !important;
    text-decoration: underline !important;
}

/* ── NO RESULTS NOTICE ────────────────────────────────────── */
#main_div .note,
#main_div .notes,
#main_div div.note {
    background: #fffcf0 !important;
    border: 1.5px solid var(--gold) !important;
    border-radius: 12px !important;
    padding: 13px 22px !important;
    color: var(--charcoal) !important;
    font-size: 13.5px !important;
    font-weight: 800 !important;
    text-align: center !important;
    margin: 14px 0 !important;
    box-shadow: 0 2px 12px rgba(212,175,55,0.18) !important;
}

/* ── ICONS ────────────────────────────────────────────────── */
#main_div img[src*='cal'],
#main_div img[src*='search'] {
    cursor: pointer !important;
    transition: transform 0.18s, opacity 0.18s !important;
    opacity: 0.80 !important;
}
#main_div img[src*='cal']:hover,
#main_div img[src*='search']:hover {
    transform: scale(1.18) !important;
    opacity: 1 !important;
}

/* ── SCROLLBAR ────────────────────────────────────────────── */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #e8edf5; border-radius: 8px; }
::-webkit-scrollbar-thumb {
    background: linear-gradient(var(--royal-mid), var(--navy));
    border-radius: 8px;
}
::-webkit-scrollbar-thumb:hover { background: var(--gold); }
</style>
";

end_page();