<?php
/**********************************************************************
    Copyright (C) Vip Accounting System, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_PRINTPROFILE';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/admin/db/printers_db.inc");
include($path_to_root . "/includes/ui.inc");

/* ═══════════════════ VIP DESIGN INJECTION ═══════════════════ */
add_js_file('');  // ensure scripts are loaded
ob_start(); // buffer original page output
?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>

/* ═══════════════════════════════════════════════
   VIP ACCOUNTING SYSTEM — ADMIN PANEL
   Aesthetic: Luxury Dark · Royal Navy × Gold
   Shared across all admin pages
═══════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; }

:root {
  --navy-deep:   #050E1F;
  --navy-main:   #0A1930;
  --navy-card:   #0D2040;
  --navy-border: rgba(212,175,55,0.18);
  --gold:        #D4AF37;
  --gold-light:  #F0D060;
  --gold-dim:    rgba(212,175,55,0.12);
  --gold-glow:   rgba(212,175,55,0.25);
  --white-hi:    #FFFFFF;
  --white-mid:   rgba(255,255,255,0.82);
  --white-low:   rgba(255,255,255,0.45);
  --white-ghost: rgba(255,255,255,0.06);
  --err:         #FF6B6B;
  --ok:          #4ade80;
  --font-h:      "Playfair Display", serif;
  --font-b:      "DM Sans", sans-serif;
}

/* ── Base ── */
html, body {
  min-height: 100%;
  background: var(--navy-deep) !important;
  font-family: var(--font-b) !important;
  color: var(--white-mid) !important;
}

/* ── Animated Background ── */
.vip-bg {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background:
    radial-gradient(ellipse 80% 60% at 15% 10%, rgba(212,175,55,0.07) 0%, transparent 60%),
    radial-gradient(ellipse 60% 80% at 85% 90%, rgba(22,78,143,0.20) 0%, transparent 60%),
    radial-gradient(ellipse 100% 100% at 50% 50%, #0A1930 0%, #050E1F 100%);
}
.orb {
  position: fixed; border-radius: 50%; filter: blur(80px);
  animation: vipFloat 14s ease-in-out infinite; pointer-events: none; z-index: 0;
}
.orb-1 { width:500px;height:500px;background:radial-gradient(circle,rgba(212,175,55,0.07),transparent 70%);top:-150px;left:-100px;animation-delay:0s; }
.orb-2 { width:400px;height:400px;background:radial-gradient(circle,rgba(22,78,143,0.12),transparent 70%);bottom:-100px;right:-80px;animation-delay:-7s; }
.orb-3 { width:300px;height:300px;background:radial-gradient(circle,rgba(212,175,55,0.05),transparent 70%);top:40%;right:15%;animation-delay:-3.5s; }
@keyframes vipFloat {
  0%,100% { transform:translate(0,0) scale(1); }
  33%      { transform:translate(25px,-18px) scale(1.04); }
  66%      { transform:translate(-18px,14px) scale(0.96); }
}
.vip-grid {
  position:fixed;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(212,175,55,0.025) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(212,175,55,0.025) 1px,transparent 1px);
  background-size:60px 60px;
}

/* ── Page Wrapper ── */
#vip-page {
  position: relative; z-index: 10;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 0 40px 0;
}

/* ── Top Bar ── */
.vip-topbar {
  width: 100%; max-width: 1200px;
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 32px 0;
  animation: vipFadeDown 0.6s ease both;
}
@keyframes vipFadeDown {
  from { opacity:0; transform:translateY(-12px); }
  to   { opacity:1; transform:translateY(0); }
}
.vip-topbar-brand {
  display: flex; align-items: center; gap: 12px;
}
.vip-topbar-logo {
  width:40px;height:40px;border-radius:10px;
  background:linear-gradient(135deg,var(--gold),#B8960A);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 14px rgba(212,175,55,0.30);
  flex-shrink:0;
}
.vip-topbar-title {
  font-family: var(--font-h);
  font-size: 16px; font-weight: 700;
  color: var(--white-hi); letter-spacing: -0.01em;
}
.vip-topbar-title span { color: var(--gold); }
.vip-topbar-nav {
  display: flex; align-items: center; gap: 8px;
}
.vip-topbar-nav a {
  font-size: 11px; color: var(--white-low);
  text-decoration: none; letter-spacing: .07em;
  text-transform: uppercase; padding: 6px 12px;
  border-radius: 6px; transition: all .2s;
}
.vip-topbar-nav a:hover { color: var(--gold); background: var(--gold-dim); }
.vip-topbar-divider { width:1px;height:24px;background:rgba(212,175,55,0.12);margin:0 4px; }

/* ── Page Header ── */
.vip-page-header {
  width: 100%; max-width: 1200px;
  padding: 28px 32px 20px;
  animation: vipFadeDown 0.6s ease 0.05s both;
}
.vip-page-title {
  font-family: var(--font-h);
  font-size: 28px; font-weight: 700;
  color: var(--white-hi); letter-spacing: -0.02em;
  display: flex; align-items: center; gap: 14px;
}
.vip-page-title-icon {
  width:44px;height:44px;border-radius:12px;
  background:var(--gold-dim);border:1px solid var(--navy-border);
  display:flex;align-items:center;justify-content:center;
  color:var(--gold);flex-shrink:0;
}
.vip-page-subtitle {
  font-size: 12px; color: var(--white-low);
  text-transform: uppercase; letter-spacing: .1em;
  margin-top: 6px; padding-left: 58px;
}
.vip-title-divider {
  width:100%;height:1px;
  background:linear-gradient(90deg,var(--navy-border),transparent);
  margin-top:16px;
}

/* ── Main Content Card ── */
.vip-content {
  width: 100%; max-width: 1200px;
  padding: 0 32px;
  animation: vipCardIn 0.7s cubic-bezier(.22,1,.36,1) 0.1s both;
}
@keyframes vipCardIn {
  from { opacity:0; transform:translateY(20px) scale(0.98); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}
.vip-card {
  background: linear-gradient(160deg,rgba(13,32,64,0.95) 0%,rgba(5,14,31,0.98) 100%);
  border: 1px solid var(--navy-border);
  border-radius: 20px;
  padding: 36px 40px;
  box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(212,175,55,0.05),
              inset 0 1px 0 rgba(212,175,55,0.08);
  backdrop-filter: blur(16px);
  position: relative; overflow: hidden;
}
.vip-card::before {
  content: ''; position: absolute; top:0; left:10%; right:10%; height:1px;
  background: linear-gradient(90deg,transparent,var(--gold),transparent);
}

/* ── Override FA table styles ── */
table { border-collapse: collapse !important; width: 100% !important; }

/* Section title rows */
.vip-section-title {
  font-family: var(--font-h);
  font-size: 13px; font-weight: 700;
  color: var(--gold); text-transform: uppercase;
  letter-spacing: .1em; padding: 18px 0 10px;
  border-bottom: 1px solid var(--navy-border);
  margin-bottom: 14px;
}

/* ── FA table overrides ── */
.vip-table { width:100%; border-collapse:collapse; }
.vip-table th {
  background: rgba(212,175,55,0.08) !important;
  color: var(--gold) !important;
  font-size: 11px !important; font-weight: 600 !important;
  text-transform: uppercase !important; letter-spacing: .09em !important;
  padding: 12px 14px !important;
  border-bottom: 1px solid var(--navy-border) !important;
  font-family: var(--font-b) !important;
}
.vip-table td {
  padding: 11px 14px !important;
  border-bottom: 1px solid rgba(212,175,55,0.05) !important;
  color: var(--white-mid) !important;
  font-size: 13px !important;
  font-family: var(--font-b) !important;
  vertical-align: middle !important;
  background: transparent !important;
}
.vip-table tr:hover td { background: rgba(212,175,55,0.04) !important; }
.vip-table tr.oddrow td  { background: rgba(255,255,255,0.01) !important; }
.vip-table tr.evenrow td { background: rgba(255,255,255,0.03) !important; }

/* ── FA form element overrides ── */
input[type=text], input[type=password], input[type=number],
input[type=email], input[type=date], textarea, select {
  background: rgba(255,255,255,0.04) !important;
  border: 1.5px solid rgba(255,255,255,0.10) !important;
  border-radius: 8px !important;
  color: var(--white-hi) !important;
  font-family: var(--font-b) !important;
  font-size: 13px !important;
  padding: 9px 12px !important;
  outline: none !important;
  transition: all .2s ease !important;
}
input[type=text]:focus, input[type=password]:focus,
input[type=number]:focus, input[type=email]:focus,
input[type=date]:focus, textarea:focus, select:focus {
  border-color: var(--gold) !important;
  background: rgba(212,175,55,0.06) !important;
  box-shadow: 0 0 0 3px rgba(212,175,55,0.12) !important;
}
select option { background: #0D2040 !important; color: #fff !important; }
textarea { resize: vertical !important; min-height: 80px !important; }

/* ── Buttons ── */
input[type=submit], button[type=submit],
.vip-btn, input[type=button] {
  background: linear-gradient(135deg,var(--gold) 0%,#B8960A 100%) !important;
  border: none !important; border-radius: 9px !important;
  color: #0A1930 !important;
  font-family: var(--font-b) !important;
  font-size: 13px !important; font-weight: 700 !important;
  letter-spacing: .06em !important; text-transform: uppercase !important;
  padding: 11px 24px !important; cursor: pointer !important;
  box-shadow: 0 4px 16px rgba(212,175,55,0.28) !important;
  transition: all .22s ease !important;
}
input[type=submit]:hover, button[type=submit]:hover,
.vip-btn:hover, input[type=button]:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 7px 24px rgba(212,175,55,0.42) !important;
}
input[type=submit]:active, button[type=submit]:active { transform: translateY(0) !important; }

/* Small secondary buttons (edit/delete/icon) */
.vip-btn-sm {
  padding: 6px 14px !important; font-size: 11px !important;
  border-radius: 6px !important;
}
a.vip-btn-edit, input[name*="Edit"], button[name*="Edit"] {
  background: rgba(212,175,55,0.12) !important;
  color: var(--gold) !important;
  border: 1px solid rgba(212,175,55,0.25) !important;
  box-shadow: none !important;
  font-size: 11px !important; padding: 5px 12px !important;
}
a.vip-btn-delete, input[name*="Delete"], button[name*="Delete"] {
  background: rgba(255,107,107,0.10) !important;
  color: var(--err) !important;
  border: 1px solid rgba(255,107,107,0.22) !important;
  box-shadow: none !important;
  font-size: 11px !important; padding: 5px 12px !important;
}
a.vip-btn-edit:hover, input[name*="Edit"]:hover, button[name*="Edit"]:hover {
  background: rgba(212,175,55,0.22) !important;
  transform: translateY(-1px) !important;
}
a.vip-btn-delete:hover, input[name*="Delete"]:hover, button[name*="Delete"]:hover {
  background: rgba(255,107,107,0.20) !important;
  transform: translateY(-1px) !important;
}

/* ── Labels & text ── */
label, .label { color: var(--white-low) !important; font-size: 12px !important; letter-spacing:.02em !important; }
.vip-label-cell { color: var(--white-low) !important; font-size: 12px !important; font-weight:600 !important; text-transform:uppercase !important; letter-spacing:.08em !important; white-space:nowrap !important; padding-right:16px !important; }

/* ── Notification / Error banners ── */
.messagebox, div.messagebox {
  background: rgba(74,222,128,0.08) !important;
  border: 1px solid rgba(74,222,128,0.25) !important;
  border-radius: 10px !important;
  color: var(--ok) !important;
  padding: 12px 18px !important;
  margin: 12px 0 !important;
  font-size: 13px !important;
}
.err_msg, .redfg, div.err_msg {
  background: rgba(255,107,107,0.08) !important;
  border: 1px solid rgba(255,107,107,0.25) !important;
  border-radius: 10px !important;
  color: var(--err) !important;
  padding: 12px 18px !important;
  margin: 12px 0 !important;
  font-size: 13px !important;
}
.infomsg {
  background: rgba(212,175,55,0.08) !important;
  border: 1px solid rgba(212,175,55,0.22) !important;
  border-radius: 10px !important;
  color: var(--gold-light) !important;
  padding: 12px 18px !important; margin: 12px 0 !important; font-size:13px !important;
}

/* ── Outer table (TABLESTYLE2 section forms) ── */
table.tablestyle2 td, .outer_table td { background: transparent !important; }
td.tableheader, .tableheader {
  background: rgba(212,175,55,0.08) !important;
  color: var(--gold) !important;
  font-family: var(--font-h) !important;
  font-size: 13px !important; font-weight: 700 !important;
  padding: 12px 16px !important;
  border-bottom: 1px solid var(--navy-border) !important;
  letter-spacing: .04em !important;
}

/* ── Tabs ── */
.tablinks, .tab {
  background: var(--gold-dim) !important;
  color: var(--white-low) !important;
  border: 1px solid var(--navy-border) !important;
  border-radius: 8px 8px 0 0 !important;
  padding: 8px 18px !important; font-size: 12px !important;
  cursor: pointer !important; transition: all .2s !important;
}
.tablinks.active, .tab.active {
  background: linear-gradient(135deg,var(--gold),#B8960A) !important;
  color: #0A1930 !important; font-weight:700 !important;
  border-color: var(--gold) !important;
}

/* ── Checkboxes ── */
input[type=checkbox] { accent-color: var(--gold) !important; width:15px;height:15px; }
input[type=radio]    { accent-color: var(--gold) !important; }

/* ── Links ── */
a { color: var(--gold) !important; text-decoration: none !important; transition: color .2s !important; }
a:hover { color: var(--gold-light) !important; }

/* ── Footer ── */
.vip-footer {
  width:100%; max-width:1200px;
  padding: 20px 32px 0;
  display:flex; align-items:center; justify-content:center; gap:20px;
  animation: vipFadeUp 0.6s ease 0.7s both;
}
@keyframes vipFadeUp {
  from { opacity:0; transform:translateY(10px); }
  to   { opacity:1; transform:translateY(0); }
}
.vip-footer a { font-size:10px; color:var(--white-low) !important; letter-spacing:.07em; text-transform:uppercase; }
.vip-footer a:hover { color:var(--gold) !important; }
.vip-footer-sep { width:3px;height:3px;border-radius:50%;background:var(--white-ghost);flex-shrink:0; }
.vip-date { font-size:10px; color:var(--white-low); letter-spacing:.04em; }

/* ── Scrollbar ── */
::-webkit-scrollbar { width:6px; height:6px; }
::-webkit-scrollbar-track { background: var(--navy-deep); }
::-webkit-scrollbar-thumb { background: rgba(212,175,55,0.25); border-radius:3px; }
::-webkit-scrollbar-thumb:hover { background: var(--gold); }


/* ── Override: wrap #_page_body in vip-card ── */
#_page_body {
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
}
</style>
<?php

page(_($help_context = "Printing Profiles"));

$selected_id = get_post('profile_id','');

//-------------------------------------------------------------------------------------------------
// Returns array of defined reports
//
function get_reports() {
	global $path_to_root, $SysPrefs;

	if ($SysPrefs->go_debug || !isset($_SESSION['reports'])) {	
	// to save time, store in session.
		$paths = array (
			$path_to_root.'/reporting/',
			company_path(). '/reporting/');
		$reports = array( '' => _('Default printing destination'));

		foreach($paths as $dirno => $path) {
			$repdir = opendir($path);
			while(false !== ($fname = readdir($repdir)))
			{
				// reports have filenames in form rep(repid).php 
				// where repid must contain at least one digit (reports_main.php is not ;)
				if (is_file($path.$fname) 
					&& preg_match('/rep(.*[0-9]+.*)[.]php/', $fname, $match))
				{
					$repno = $match[1];
					$title = '';

					$line = file_get_contents($path.$fname);
					if (preg_match('/.*(FrontReport\()\s*_\([\'"]([^\'"]*)/', $line, $match)) {
						$title = trim($match[2]);
					}
					else // for any 3rd party printouts without FrontReport() class use
					if (preg_match('/.*(\$Title).*[\'"](.*)[\'"].+/', $line, $match)) {
						$title = trim($match[2]);
					}
					$reports[$repno] = $title;
				}
			}
			closedir();
		}
		ksort($reports);
		$_SESSION['reports'] = $reports;
	}
	return $_SESSION['reports'];
}

function clear_form() 
{
	global $selected_id, $Ajax;

	$selected_id = '';
	$_POST['name'] = '';
	$Ajax->activate('_page_body');
}

function check_delete($name)
{
	// check if selected profile is used by any user
	if ($name=='') return 0; // cannot delete system default profile
	return key_in_foreign_table($name, 'users', 'print_profile');
}
//-------------------------------------------------------------------------------------------
if ( get_post('submit'))
{

	$error = 0;

	if ($_POST['profile_id'] == '' && empty($_POST['name']))
	{
		$error = 1;
		display_error( _("Printing profile name cannot be empty."));
		set_focus('name');
	} 

	if (!$error)
	{
		$prof = array('' => get_post('Prn')); // store default value/profile name
		foreach (get_reports() as $rep => $descr) {
			$val = get_post('Prn'.$rep);
			$prof[$rep] = $val;
		}
		if ($_POST['profile_id']=='')
			$_POST['profile_id'] = get_post('name');
		
		update_printer_profile($_POST['profile_id'], $prof);
		if ($selected_id == '') {
			display_notification_centered(_('New printing profile has been created')); 
			clear_form($selected_id);
		} else {
			display_notification_centered(_('Printing profile has been updated'));
		}
	}
}

if(get_post('delete'))
{
 	if (!check_delete(get_post('name'))) {
		delete_printer_profile($selected_id);
		display_notification(_('Selected printing profile has been deleted'));
		clear_form();
 	}
}

if(get_post('_profile_id_update')) {
	$Ajax->activate('_page_body');
}

start_form();
start_table();
print_profiles_list_row(_('Select printing profile'). ':', 'profile_id', null,
	_('New printing profile'), true);
end_table();
echo '<hr>';
start_table();
if (get_post('profile_id') == '')
	text_row(_("Printing Profile Name").':', 'name', null, 30, 30);
else
	label_cells(_("Printing Profile Name").':', get_post('profile_id'));
end_table(1);

$result = get_print_profile(get_post('profile_id'));
$prints = array();
while ($myrow = db_fetch($result)) {
	$prints[$myrow['report']] = $myrow['printer'];
}

start_table(TABLESTYLE);
$th = array(_("Report Id"), _("Description"), _("Printer"));
table_header($th);

$k = 0;
$unkn = 0;
foreach(get_reports() as $rep => $descr)
{
	alt_table_row_color($k);

    label_cell($rep=='' ? '-' : $rep, 'align=center');
    label_cell($descr == '' ? '???<sup>1)</sup>' : _($descr));
	$_POST['Prn'.$rep] = isset($prints[$rep]) ? $prints[$rep] : '';
    echo '<td>';
	echo printers_list('Prn'.$rep, null, 
		$rep == '' ? _('Browser support') : _('Default'));
	echo '</td>';
	if ($descr == '') $unkn = 1;
    end_row();
}
end_table();
if ($unkn)
	display_note('<sup>1)</sup>&nbsp;-&nbsp;'._("no title was found in this report definition file."), 0, 1, '');
else
	echo '<br>';

div_start('controls');
if (get_post('profile_id') == '') {
	submit_center('submit', _("Add New Profile"), true, '', 'default');
} else {
	submit_center_first('submit', _("Update Profile"), 
	  _('Update printer profile'), 'default');
	submit_center_last('delete', _("Delete Profile"), 
	  _('Delete printer profile (only if not used by any user)'), true);
}
div_end();

end_form();
end_page();

/* ── VIP BACKGROUND & WRAPPER ── */
$vip_page_ob = ob_get_clean();
// Inject VIP overlay into the buffered output
$vip_bg = "<div class='vip-bg'></div>\n<div class='vip-grid'></div>\n<div class='orb orb-1'></div>\n<div class='orb orb-2'></div>\n<div class='orb orb-3'></div>\n";
$vip_topbar = "<div class='vip-topbar'>\n  <div class='vip-topbar-brand'>\n    <div class='vip-topbar-logo'><svg width='22' height='22' viewBox='0 0 24 24' fill='none'><path d='M12 2L2 7l10 5 10-5-10-5z' fill='#0A1930'/><path d='M2 17l10 5 10-5' stroke='#0A1930' stroke-width='2' stroke-linecap='round'/><path d='M2 12l10 5 10-5' stroke='#0A1930' stroke-width='2' stroke-linecap='round'/></svg></div>\n    <div class='vip-topbar-title'>Vip <span>Accounting</span> System</div>\n  </div>\n  <div class='vip-topbar-nav'>\n    <a href=\'$path_to_root/index.php\'>&#8592; "._("Back to System")."</a>\n    <div class='vip-topbar-divider'></div>\n    <span style=\'font-size:10px;color:var(--white-low);letter-spacing:.07em;text-transform:uppercase;\'>v".(isset($version)?$version:"")."</span>\n  </div>\n</div>\n";
$vip_ph = "<div class='vip-page-header'>\n  <div class='vip-page-title'>\n    <div class='vip-page-title-icon'><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><polyline points='6 9 6 2 18 2 18 9'/><path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'/><rect x='6' y='14' width='12' height='8'/></svg></div>\n    ".(isset($help_context)?_($help_context):" ")."\n  </div>\n  <div class='vip-page-subtitle'>Setup · Printing Profiles</div>\n  <div class='vip-title-divider'></div>\n</div>\n";
$vip_foot_date = function_exists('Today') ? (Today()." | ".Now()) : (date("d/m/Y")." | ".date("h:i a"));
$vip_footer = "<div class='vip-footer'>\n  <span class='vip-date'>$vip_foot_date</span>\n".(isset($SysPrefs)?"  <span class='vip-footer-sep'></span>\n  <a href='".$SysPrefs->power_url."' target=\'_blank\'>".$SysPrefs->app_title." $version</a>\n  <span class='vip-footer-sep'></span>\n  <a href='".$SysPrefs->power_url."' target=\'_blank\'>".$SysPrefs->power_by."</a>\n":"")."</div>\n";

// Insert VIP elements into the output
$vip_page_ob = preg_replace('/<body([^>]*)>/', '<body$1>'.str_replace('\\', '\\\\', $vip_bg)."<div id='vip-page'>\n".str_replace('\\', '\\\\', $vip_topbar).str_replace('\\', '\\\\', $vip_ph)."<div class='vip-content'><div class='vip-card'>\n", $vip_page_ob, 1);
$vip_page_ob = preg_replace('/<\/body>/', "</div></div>\n".str_replace('\\', '\\\\', $vip_footer)."</div>\n</body>", $vip_page_ob, 1);
echo $vip_page_ob;



