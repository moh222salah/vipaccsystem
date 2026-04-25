<?php
/**********************************************************************
    Copyright (C) Vip Accounting System, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
***********************************************************************/
/*=============================================================
  Vip Accounting System — "The Executive Interface"
  Design: Deep Enterprise Palette
  Redesigned by: Mohamed Salah  |  wa.me/201113903070
  Royal Prussian Blue (#0A2342) × Polished Gold (#D4AF37)
=============================================================*/

	class renderer
	{
		function get_icon($category)
		{
			global $path_to_root, $SysPrefs;
			if ($SysPrefs->show_menu_category_icons)
				$img = $category == '' ? 'right.gif' : $category.'.png';
			else
				$img = 'right.gif';
			return "<img src='$path_to_root/themes/".user_theme()."/images/$img' style='width:13px;height:13px;vertical-align:middle;opacity:0.55;' border='0'>&nbsp;";
		}

		function wa_header()
		{
			page(_($help_context = "Main Menu"), false, true);
		}

		function wa_footer()
		{
			end_page(false, true);
		}

		function menu_header($title, $no_menu, $is_index)
		{
			global $path_to_root, $SysPrefs, $db_connections;

			$indicator = "$path_to_root/themes/".user_theme()."/images/ajax-loader.gif";

			/* ── Inject styles first ── */
			$this->_inject_layout_styles();

			echo "<div class='ex-shell'>\n";

			if (!$no_menu)
			{
				$applications = $_SESSION['App']->applications;
				$local_path_to_root = $path_to_root;
				$sel_app = $_SESSION['sel_app'];
				$company_name = $db_connections[user_company()]["name"] ?? 'VIP A';
				$user_name    = $_SESSION["wa_current_user"]->name ?? 'User';
				$user_initial = strtoupper(mb_substr($user_name, 0, 1));

				/* ════════════════════════════════════
				   SIDEBAR — Royal Prussian Blue
				════════════════════════════════════ */
				echo "<nav class='ex-sidebar' id='ex-sidebar'>\n";

				/* Brand */
				echo "<div class='ex-brand'>\n";
				echo "  <div class='ex-brand-logo'>\n";
				echo "    <svg width='30' height='30' viewBox='0 0 36 36' fill='none'>\n";
				echo "      <rect width='36' height='36' rx='9' fill='#164E8F'/>\n";
				echo "      <text x='50%' y='56%' dominant-baseline='middle' text-anchor='middle' font-family='Inter,sans-serif' font-size='14' font-weight='800' fill='#ffffff'>VAS</text>\n";
				echo "    </svg>\n";
				echo "  </div>\n";
				echo "  <div class='ex-brand-info'>\n";
				echo "    <span class='ex-brand-name'>VIP Acc System</span>\n";
				echo "    <span class='ex-brand-co'>" . htmlspecialchars($company_name) . "</span>\n";
				echo "  </div>\n";
				echo "  <button class='ex-collapse-btn' onclick=\"document.getElementById('ex-sidebar').classList.toggle('collapsed');document.getElementById('ex-body').classList.toggle('sidebar-collapsed');\" title='Collapse'>\n";
				echo "    <svg width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><path d='m15 18-6-6 6-6'/></svg>\n";
				echo "  </button>\n";
				echo "</div>\n";

				/* Nav Label */
				$_nav_lbl = (isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG') ? 'القائمة الرئيسية' : 'NAVIGATION';
				echo "<div class='ex-nav-section-label'>$_nav_lbl</div>\n";

				/* ════════════════════════════════════════════════════
				   Extended Custom Navigation
				   Uses fa_get_nav_items() from Vip Accounting System.php
				   Falls back to standard app loop if not available
				════════════════════════════════════════════════════ */
				echo "<div class='ex-nav'>\n";

				$is_arabic    = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
				$current_url  = $_SERVER['REQUEST_URI'] ?? '';
				$current_base = basename(parse_url($current_url, PHP_URL_PATH) ?? '');

				/* ══════════════════════════════════════════════════════════════
				   UNIFIED SIDEBAR — Single source of truth
				   ALL items defined here, ALL rendered as one type.
				   type: 'app' = FA application | 'page' = direct page link
				══════════════════════════════════════════════════════════════ */
				$sidebar = [
					['id'=>'sales',      'en'=>'Sales',                'ar'=>'المبيعات',              'type'=>'app',  'app'=>'orders', 'icon'=>'sales',         'folder'=>'sales'],
					['id'=>'purchases',  'en'=>'Purchases',            'ar'=>'المشتريات',             'type'=>'app',  'app'=>'AP',     'icon'=>'purchases',     'folder'=>'purchasing'],
					['id'=>'inventory',  'en'=>'Items and Inventory',  'ar'=>'المخزون والأصناف',      'type'=>'app',  'app'=>'stock',  'icon'=>'inventory',     'folder'=>'inventory'],
					['id'=>'manuf',      'en'=>'Manufacturing',        'ar'=>'التصنيع',               'type'=>'app',  'app'=>'manuf',  'icon'=>'manufacturing', 'folder'=>'manufacturing', 'pref'=>'use_manufacturing'],
					['id'=>'dims',       'en'=>'Dimensions',           'ar'=>'الأبعاد التحليلية',     'type'=>'app',  'app'=>'proj',   'icon'=>'dimensions',    'folder'=>'dimensions'],
					['id'=>'gl',         'en'=>'General Ledger',       'ar'=>'الأستاذ العام',         'type'=>'app',  'app'=>'GL',     'icon'=>'gl',            'folder'=>'gl'],
					['id'=>'banking',    'en'=>'Banking',              'ar'=>'البنوك',                'type'=>'page', 'href'=>'/gl/bank_transfer.php',             'icon'=>'banking',       'sa'=>'SA_BANKTRANSFER', 'folder'=>'banking',          'pages'=>['bank_transfer.php','gl_bank.php','bank_account_reconcile.php','bank_inquiry.php','bank_accounts.php']],
					['id'=>'trial',      'en'=>'Trial Balance',        'ar'=>'ميزان المراجعة',        'type'=>'page', 'href'=>'/gl/inquiry/gl_trial_balance.php',  'icon'=>'trial_balance', 'sa'=>'SA_GLANALYTIC',   'folder'=>'trial_balance',    'pages'=>['rep111.php','rep110.php','rep114.php']],
					['id'=>'journal',    'en'=>'Journal Entries',      'ar'=>'قيود اليومية',          'type'=>'page', 'href'=>'/gl/gl_journal.php',                'icon'=>'journal',       'sa'=>'SA_JOURNALENTRY', 'folder'=>'journal_entries',  'pages'=>['gl_journal.php','journal_inquiry.php','gl_trans_view.php']],
					['id'=>'dims2',      'en'=>'Dimensions',           'ar'=>'الأبعاد',               'type'=>'page', 'href'=>'/dimensions/dimension_entry.php',   'icon'=>'dims2',         'sa'=>'SA_DIMTRANSVIEW', 'folder'=>'dimensions',       'pages'=>['dimension_entry.php','search_dimensions.php']],
					['id'=>'costcenter', 'en'=>'Cost Centers',         'ar'=>'مراكز التكلفة',         'type'=>'page', 'href'=>'/gl/cost_center.php',               'icon'=>'cost_center',   'sa'=>'SA_DIMTRANSVIEW', 'folder'=>'cost_centers',     'pages'=>['cost_center.php']],
					['id'=>'coa',        'en'=>'Chart of Accounts',    'ar'=>'دليل الحسابات',         'type'=>'page', 'href'=>'/gl/gl_accounts.php',               'icon'=>'coa',           'sa'=>'SA_GLACCOUNT',    'folder'=>'chart_of_accounts','pages'=>['gl_accounts.php','gl_account_types.php','gl_account_classes.php']],
					['id'=>'taxinv',     'en'=>'Tax Invoice',          'ar'=>'الفاتورة الضريبية',     'type'=>'page', 'href'=>'/sales/customer_invoice.php',       'icon'=>'tax',           'sa'=>'SA_SALESINVOICE', 'folder'=>'tax_invoice',      'pages'=>['customer_invoice.php','credit_note_entry.php']],
					['id'=>'opening',    'en'=>'Opening Balances',     'ar'=>'أرصدة أول المدة',       'type'=>'page', 'href'=>'/gl/gl_opening_balances.php',       'icon'=>'opening_bal',   'sa'=>'SA_GLSETUP',      'folder'=>'opening_balances', 'pages'=>['gl_opening_balances.php']],
					['id'=>'npo',        'en'=>'Non-Profit Org',       'ar'=>'المنظمات غير الربحية',  'type'=>'page', 'href'=>'/gl/budget_trans.php',              'icon'=>'npo',           'sa'=>'SA_GLANALYTIC',   'folder'=>'non_profit_org',   'pages'=>['budget_trans.php','gl_budget.php']],
					['id'=>'assets',     'en'=>'Fixed Assets',         'ar'=>'الأصول الثابتة',        'type'=>'app',  'app'=>'assets', 'icon'=>'assets',         'folder'=>'fixed_assets',  'pref'=>'use_fixed_assets'],
					['id'=>'settings',   'en'=>'Settings',             'ar'=>'الإعدادات',             'type'=>'app',  'app'=>'system', 'icon'=>'settings',       'folder'=>'settings'],
				];

				/* ── Pre-scan: find active sidebar item ── */
				$active_id = null;

				/* ══════════════════════════════════════════════════════
				   Priority 0: Direct ?application= URL parameter
				   — Most reliable: reads GET param directly, bypasses any
				     session timing issues or stale $_SESSION['sel_app'].
				   — Fires first so ?application=assets always marks
				     Fixed Assets active, ?application=orders marks Sales, etc.
				══════════════════════════════════════════════════════ */
				if ($active_id === null && !empty($_GET['application'])) {
					$_app_param = $_GET['application'];
					foreach ($sidebar as $_s_scan) {
						if ($_s_scan['type'] === 'app' && $_s_scan['app'] === $_app_param) {
							$active_id = $_s_scan['id'];
							break;
						}
					}
					unset($_s_scan, $_app_param);
				}

				/* Priority 1: Global section override (set by hub pages) */
				if ($active_id === null && isset($GLOBALS['vip_active_section'])) {
					$active_id = $GLOBALS['vip_active_section'];
				}

				/* Priority 2: Check if current URL matches a section folder */
				if ($active_id === null) {
					$current_path = parse_url($current_url, PHP_URL_PATH) ?? '';
					foreach ($sidebar as $s) {
						if (!empty($s['folder']) && strpos($current_path, '/' . $s['folder'] . '/') !== false) {
							$active_id = $s['id'];
							break;
						}
					}
				}

				/* Priority 3: Check if current page basename matches a section's pages */
				if ($active_id === null) {
					foreach ($sidebar as $s) {
						if (!empty($s['pages'])) {
							foreach ($s['pages'] as $pg) {
								if ($current_base === $pg) {
									$active_id = $s['id'];
									break 2;
								}
							}
						}
					}
				}

				/* ── Render each item uniformly ── */
				foreach ($sidebar as $s) {

					/* Conditional pref check (Manufacturing, Fixed Assets) */
					if (!empty($s['pref']) && !get_company_pref($s['pref'])) continue;

					/* Access check */
					if ($s['type'] === 'app') {
						$app = $applications[$s['app']] ?? null;
						if (!$app) continue;
						if (!$_SESSION["wa_current_user"]->check_application_access($app)) continue;
					}
					if (!empty($s['sa'])) {
						if (!$_SESSION["wa_current_user"]->can_access_page($s['sa'])) continue;
					}

					/* Label — clean, no HTML */
					$label = $is_arabic ? $s['ar'] : $s['en'];

					/* Build href — original FA paths for stable navigation */
					if ($s['type'] === 'app') {
						$href = "$local_path_to_root/index.php?application=" . $s['app'];
					} elseif (!empty($s['direct'])) {
						$href = $local_path_to_root . $s['direct'];
					} else {
						$href = $local_path_to_root . $s['href'];
					}

					/* Active state — only ONE item active */
					$is_active = false;
					if ($active_id !== null) {
						$is_active = ($s['id'] === $active_id);
					} else {
						if ($s['type'] === 'app') {
							$is_active = ($sel_app == $s['app']);
						}
					}

					/* Icon */
					$icon = $this->_nav_svg($s['icon']);

					/* ── Render ── */
					echo "<a class='ex-nav-item" . ($is_active ? " is-active" : "") . "'"
						. " href='" . htmlspecialchars($href) . "'>\n";
					echo "  <span class='ex-nav-icon'>$icon</span>\n";
					echo "  <span class='ex-nav-label'>" . htmlspecialchars($label) . "</span>\n";
					if ($is_active) echo "  <span class='ex-nav-pip'></span>\n";
					echo "</a>\n";
				}

				echo "</div>\n"; /* /ex-nav */

				/* ── Auto-scroll sidebar so active item is always visible ──
				   Needed when active item is far down the list (e.g. Fixed Assets #17).
				   Runs as early as possible, re-runs on DOMContentLoaded as fallback. */
				echo "<script>(function(){\n";
				echo "  function _vasScrollActive(){\n";
				echo "    var el=document.querySelector('#ex-sidebar .ex-nav-item.is-active');\n";
				echo "    var sb=document.getElementById('ex-sidebar');\n";
				echo "    if(!el||!sb) return;\n";
				echo "    var itemTop=el.offsetTop;\n";
				echo "    var itemH=el.offsetHeight;\n";
				echo "    var sbH=sb.clientHeight;\n";
				echo "    var sbScroll=sb.scrollTop;\n";
				echo "    /* Only scroll if item is outside visible zone */\n";
				echo "    if(itemTop < sbScroll || itemTop + itemH > sbScroll + sbH){\n";
				echo "      sb.scrollTop = itemTop - (sbH/2) + (itemH/2);\n";
				echo "    }\n";
				echo "  }\n";
				echo "  if(document.readyState==='loading'){\n";
				echo "    document.addEventListener('DOMContentLoaded',_vasScrollActive);\n";
				echo "  } else { _vasScrollActive(); }\n";
				echo "})();</script>\n";
				echo "<div class='ex-sidebar-bottom'>\n";
				echo "  <div class='ex-user-card'>\n";
				echo "    <div class='ex-user-av'>$user_initial</div>\n";
				echo "    <div class='ex-user-details'>\n";
				echo "      <span class='ex-user-name'>" . htmlspecialchars($user_name) . "</span>\n";
				$_admin_lbl = (isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG') ? 'مسؤول النظام' : 'Administrator';
				echo "      <span class='ex-user-badge'>$_admin_lbl</span>\n";
				echo "    </div>\n";
				echo "  </div>\n";
				echo "</div>\n";

				echo "</nav>\n";

				/* ════════════════════════════════════
				   BODY — White/Light
				════════════════════════════════════ */
				/* Mobile backdrop — closes sidebar on tap outside */
			echo "<div class='ex-mobile-backdrop' id='ex-mobile-backdrop' onclick=\"document.getElementById('ex-sidebar').classList.remove('is-open');this.classList.remove('is-visible');\"></div>\n";

			echo "<div class='ex-body' id='ex-body'>\n";

				/* ─ Top Header Bar ─ */
				echo "<header class='ex-topbar'>\n";

				/* Left zone */
				echo "  <div class='ex-topbar-l'>\n";
				echo "    <button class='ex-mobile-menu-btn' onclick=\"document.getElementById('ex-sidebar').classList.toggle('is-open');document.getElementById('ex-mobile-backdrop').classList.toggle('is-visible');\">\n";
				echo "      <svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><line x1='3' y1='6' x2='21' y2='6'/><line x1='3' y1='12' x2='21' y2='12'/><line x1='3' y1='18' x2='21' y2='18'/></svg>\n";
				echo "    </button>\n";

				/* ══════════════════════════════════════════════════════
				   DEEP SEARCH — Icon Trigger + Full-Screen Overlay
				══════════════════════════════════════════════════════ */
				$is_arabic  = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
				/* ── Build correct browser-accessible URL ── */
				$search_url = $path_to_root . '/admin/search.php';
				$is_ar_js   = $is_arabic ? 'true' : 'false';

				/* ══════════════════════════════════════════════════════
				   DEEP SEARCH — Trigger Button (topbar)
				══════════════════════════════════════════════════════ */
				$_ph_text = $is_arabic ? 'ابحث هنا...' : 'Search...';
				$_title   = $is_arabic ? 'بحث (Ctrl+K)' : 'Search (Ctrl+K)';

				echo "    <button class='ex-gs-trigger' id='ex-gs-open'\n";
				echo "      data-search-url='" . htmlspecialchars($search_url) . "'\n";
				echo "      data-is-ar='" . $is_ar_js . "'\n";
				echo "      data-ph='" . htmlspecialchars($is_arabic ? 'ابحث في العملاء، الفواتير، الأصناف، الحسابات...' : 'Search customers, invoices, items, accounts...') . "'\n";
				echo "      title='" . $_title . "'>\n";
				echo "      <span class='ex-gs-trig-ico'>\n";
				echo "        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg>\n";
				echo "      </span>\n";
				echo "      <span class='ex-gs-trig-lbl'>" . $_ph_text . "</span>\n";
				echo "      <span class='ex-gs-trig-kbd'>Ctrl K</span>\n";
				echo "    </button>\n";

				/* ── Trigger CSS ── */
				echo <<<'GSTRIGCSS'
<style>
.ex-gs-trigger {
  display:inline-flex;align-items:center;gap:9px;
  background:#FFFFFF;
  border:1.5px solid #CBD8EC;
  border-radius:24px;
  padding:7px 14px 7px 10px;
  cursor:pointer;
  min-width:220px;max-width:320px;
  font-family:var(--font);
  transition:border-color .15s,box-shadow .15s,background .15s;
  box-shadow:0 1px 3px rgba(10,35,66,.07);
}
.ex-gs-trigger:hover {
  border-color:#8AAFD4;
  box-shadow:0 0 0 3px rgba(27,63,122,.10),0 2px 8px rgba(10,35,66,.10);
  background:#F7FAFF;
  transform:none;
}
.ex-gs-trig-ico {
  width:26px;height:26px;border-radius:50%;
  background:linear-gradient(135deg,#1B3F7A,#0A2342);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;color:#fff;
  box-shadow:0 2px 6px rgba(27,63,122,.35);
}
.ex-gs-trig-lbl {
  flex:1;font-size:13px;font-weight:500;
  color:#94A3B8;text-align:left;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.ex-gs-trigger:hover .ex-gs-trig-lbl { color:#1B3F7A; }
.ex-gs-trig-kbd {
  flex-shrink:0;
  font-size:10px;font-weight:700;font-family:monospace;
  color:#1B3F7A;letter-spacing:.04em;
  background:rgba(27,63,122,.07);
  border:1px solid rgba(27,63,122,.18);
  border-radius:5px;padding:2px 7px;
  white-space:nowrap;
}
.ex-gs-trigger:hover .ex-gs-trig-kbd { background:#1B3F7A;color:#fff;border-color:#1B3F7A; }
[dir=rtl] .ex-gs-trig-lbl { text-align:right; }
</style>
GSTRIGCSS;

				/* ── Fix first-click: attach onclick after DOM ── */
				echo "<script>(function(){\n";
				echo "  function _gsBind(){\n";
				echo "    var b=document.getElementById('ex-gs-open');\n";
				echo "    if(!b) return;\n";
				echo "    b.addEventListener('click',function(){\n";
				echo "      if(window.exGsOpen){ window.exGsOpen(); }\n";
				echo "      else {\n";
				echo "        /* fallback: wait 400ms for JS to load then open */\n";
				echo "        setTimeout(function(){ window.exGsOpen && window.exGsOpen(); },400);\n";
				echo "      }\n";
				echo "    });\n";
				echo "  }\n";
				echo "  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',_gsBind);\n";
				echo "  else _gsBind();\n";
				echo "})();</script>\n";

				echo "  </div>\n"; /* /ex-topbar-l */

				/* Right zone */
				echo "  <div class='ex-topbar-r'>\n";

				/* Ajax loader */
				echo "    <img id='ajaxmark' src='$indicator' style='visibility:hidden;width:20px;height:20px;margin-right:6px;' alt='loading'>\n";


				/* Dashboard */
				echo "    <a class='ex-hdr-btn' href='$path_to_root/admin/dashboard.php'>\n";
				echo "      <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='3' width='7' height='7' rx='1'/><rect x='14' y='3' width='7' height='7' rx='1'/><rect x='3' y='14' width='7' height='7' rx='1'/><rect x='14' y='14' width='7' height='7' rx='1'/></svg>\n";
				echo "      <span>" . _("Dashboard") . "</span>\n";
				echo "    </a>\n";

				/* Prefs */
				if ($SysPrefs->help_base_url != null) {				
				echo "    <a class='ex-hdr-btn' href='$path_to_root/admin/display_prefs.php'>\n";
				echo "      <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='3' width='7' height='7' rx='1.5'/><rect x='14' y='3' width='7' height='7' rx='1.5'/><rect x='3' y='14' width='7' height='7' rx='1.5'/><rect x='14' y='14' width='7' height='7' rx='1.5'/></svg>\n";
				echo "      <span>" . _("Prefs") . "</span>\n";
				echo "    </a>\n";	
					
				}

				/* ── Language Switcher ── */
				$this->_render_lang_switcher($path_to_root);

				/* Divider */
				echo "    <div class='ex-hdr-divider'></div>\n";

				/* Password */
				echo "    <a class='ex-hdr-btn' href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'>\n";
				echo "      <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='11' width='18' height='11' rx='2'/><path d='M7 11V7a5 5 0 0 1 10 0v4'/></svg>\n";
				echo "      <span>" . _("admin") . "</span>\n";
				echo "    </a>\n";

				
				/* Logout — Red pill */
				echo "    <a class='ex-hdr-logout' href='$local_path_to_root/access/logout.php'>\n";
				echo "      <svg width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><path d='M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4'/><polyline points='16,17 21,12 16,7'/><line x1='21' y1='12' x2='9' y2='12'/></svg>\n";
				echo "      <span>" . _("Logout") . "</span>\n";
				echo "    </a>\n";

				echo "  </div>\n";
				echo "</header>\n";

				/* ─ Main Content ─ */
				echo "<main class='ex-main'>\n";

			} else {
				/* No-menu mode */
				echo "<div class='ex-body ex-nomenu'>\n";
				echo "<main class='ex-main'>\n";
				echo "<div style='text-align:center;padding:30px'><img id='ajaxmark' src='$indicator' style='visibility:hidden' alt=''></div>\n";
			}

			/* Page title */
			if (!$no_menu && $title && !$is_index) {
				echo "<div class='ex-page-title'>\n";
				echo "  <h1 class='ex-heading'>$title</h1>\n";
				if (user_hints()) echo "  <span id='hints'></span>\n";
				echo "</div>\n";
			}
		}

		function menu_footer($no_menu, $is_index)
		{
			global $version, $path_to_root, $Pagehelp, $Ajax, $SysPrefs;
			include_once($path_to_root . "/includes/date_functions.inc");

			echo "</main>\n";

			if ($no_menu == false) {
				$phelp = isset($_SESSION['wa_current_user']) ? implode('; ', $Pagehelp) : '';
				echo "<footer class='ex-statusbar'>\n";
				echo "  <div class='ex-statusbar-l'>";
				if (isset($_SESSION['wa_current_user'])) {
					echo Today() . " &nbsp;·&nbsp; " . Now();
					$Ajax->addUpdate(true, 'hotkeyshelp', $phelp);
				}
				echo "  </div>\n";
				echo "  <div id='hotkeyshelp' class='ex-statusbar-r'>$phelp</div>\n";
				echo "</footer>\n";
			}

			echo "</div>\n"; /* /ex-body */
			echo "</div>\n"; /* /ex-shell */

			/* ══════════════════════════════════════════════════════════
			   DEEP SEARCH — Overlay injected here as DIRECT body child
			   so position:fixed works correctly regardless of any
			   overflow/transform on parent containers.
			   Config is read from data-* attrs on #ex-gs-open button.
			══════════════════════════════════════════════════════════ */
			echo <<<'GSOVERLAY'
<div class='ex-gs-overlay' id='ex-gs-overlay' onclick="if(event.target===this)window.exGsClose&&window.exGsClose()">
  <div class='ex-gs-modal' id='ex-gs-modal'>
    <!-- Glow accent bar -->
    <div class='ex-gs-accent-bar'></div>
    <!-- Input row -->
    <div class='ex-gs-input-row'>
      <div class='ex-gs-input-icon'>
        <svg width='17' height='17' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.3'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg>
      </div>
      <input id='ex-gs-input' class='ex-gs-input' autocomplete='off' spellcheck='false' dir='auto'>
      <button class='ex-gs-esc-btn' onclick='window.exGsClose&&window.exGsClose()'>ESC</button>
    </div>
    <!-- Filter chips -->
    <div class='ex-gs-chips' id='ex-gs-chips'></div>
    <!-- Body -->
    <div class='ex-gs-body' id='ex-gs-body'>
      <div class='ex-gs-state' id='ex-gs-state'></div>
      <div id='ex-gs-results'></div>
    </div>
    <!-- Footer -->
    <div class='ex-gs-footer' id='ex-gs-footer' style='display:none'>
      <span class='ex-gs-count' id='ex-gs-count'></span>
      <button class='ex-gs-more' id='ex-gs-more' style='display:none' onclick='window.exGsShowAll&&window.exGsShowAll()'></button>
    </div>
    <!-- Keyboard hints -->
    <div class='ex-gs-hints' id='ex-gs-hints'></div>
  </div>
</div>
GSOVERLAY;

			/* ── Deep Search CSS ── */
			echo <<<'GSCSS'
<style>
/* ══ Overlay ══ */
.ex-gs-overlay {
  position:fixed;inset:0;
  background:rgba(5,15,40,.60);
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  z-index:999999;
  display:flex;align-items:flex-start;justify-content:center;
  padding-top:6vh;
  opacity:0;pointer-events:none;
  transition:opacity .2s;
}
.ex-gs-overlay.is-open { opacity:1;pointer-events:all; }

/* ══ Modal ══ */
.ex-gs-modal {
  width:100%;max-width:660px;
  background:#FFFFFF;
  border-radius:20px;
  border:1.5px solid rgba(27,63,122,.15);
  box-shadow:0 32px 80px rgba(5,15,40,.32),0 8px 24px rgba(5,15,40,.14),0 0 0 1px rgba(255,255,255,.08);
  overflow:hidden;
  transform:translateY(-24px) scale(.95);
  transition:transform .25s cubic-bezier(.34,1.56,.64,1);
  max-height:82vh;
  display:flex;flex-direction:column;
  position:relative;
}
.ex-gs-overlay.is-open .ex-gs-modal { transform:translateY(0) scale(1); }

/* Gold accent top bar */
.ex-gs-accent-bar {
  height:3px;
  background:linear-gradient(90deg,#1B3F7A 0%,#D4AF37 50%,#1B3F7A 100%);
  background-size:200% 100%;
  animation:gsBar 3s ease infinite;
  flex-shrink:0;
}
@keyframes gsBar {
  0%{background-position:0% 0%}
  50%{background-position:100% 0%}
  100%{background-position:0% 0%}
}

/* ══ Input row ══ */
.ex-gs-input-row {
  display:flex;align-items:center;gap:12px;
  padding:14px 18px 12px;
  border-bottom:1px solid rgba(27,63,122,.07);flex-shrink:0;
}
.ex-gs-input-icon {
  width:34px;height:34px;border-radius:9px;
  background:linear-gradient(135deg,#1B3F7A,#0A2342);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  color:#fff;
}
.ex-gs-input {
  flex:1;border:none;outline:none;
  font-size:15.5px;font-weight:500;color:#0A2342;
  background:transparent;font-family:var(--font);
  min-width:0;
}
.ex-gs-input::placeholder { color:#a0aec0;font-weight:400; }
.ex-gs-esc-btn {
  background:rgba(27,63,122,.07);border:1px solid rgba(27,63,122,.12);
  border-radius:6px;padding:3px 9px;font-size:10px;font-weight:800;
  color:#1B3F7A;cursor:pointer;font-family:monospace;transition:all .13s;
  letter-spacing:.04em;flex-shrink:0;
}
.ex-gs-esc-btn:hover { background:#1B3F7A;color:#fff;box-shadow:none;transform:none; }

/* ══ Chips ══ */
.ex-gs-chips {
  display:flex;gap:5px;padding:9px 18px;flex-wrap:wrap;
  border-bottom:1px solid rgba(27,63,122,.05);flex-shrink:0;
  background:rgba(27,63,122,.018);
}
.ex-gs-chip {
  padding:4px 13px;border-radius:20px;font-size:11px;font-weight:700;
  cursor:pointer;border:1.5px solid rgba(27,63,122,.14);
  color:#1B3F7A;background:transparent;transition:all .14s;font-family:var(--font);
  letter-spacing:.01em;
}
.ex-gs-chip:hover { background:rgba(27,63,122,.08);box-shadow:none;transform:none; }
.ex-gs-chip.is-on {
  background:linear-gradient(135deg,#1B3F7A,#0A2342);
  color:#fff;border-color:#1B3F7A;
  box-shadow:0 2px 8px rgba(27,63,122,.28);
}

/* ══ Body ══ */
.ex-gs-body { overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:rgba(27,63,122,.15) transparent; }
.ex-gs-body::-webkit-scrollbar { width:4px; }
.ex-gs-body::-webkit-scrollbar-thumb { background:rgba(27,63,122,.15);border-radius:4px; }

/* ══ State ══ */
.ex-gs-state {
  padding:48px 20px;display:flex;flex-direction:column;
  align-items:center;justify-content:center;text-align:center;
  color:#a0aec0;gap:14px;
}
.ex-gs-state svg { opacity:.18; }
.ex-gs-state p { font-size:13.5px;margin:0;line-height:1.6; }
.ex-gs-state strong { color:#0A2342;font-weight:700; }
.ex-gs-spinner {
  width:24px;height:24px;border-radius:50%;
  border:2.5px solid rgba(27,63,122,.12);border-top-color:#1B3F7A;
  animation:gspin .55s linear infinite;
}
@keyframes gspin { to { transform:rotate(360deg); } }

/* ══ Group header ══ */
.ex-gs-group {
  font-size:9px;font-weight:800;letter-spacing:.13em;text-transform:uppercase;
  color:rgba(27,63,122,.45);padding:12px 18px 4px;
  display:flex;align-items:center;gap:8px;
}
.ex-gs-group::after {
  content:'';flex:1;height:1px;background:rgba(27,63,122,.07);
}
.ex-gs-sep { height:1px;background:rgba(27,63,122,.05);margin:2px 18px; }

/* ══ Result item ══ */
.ex-gs-item {
  display:flex;align-items:center;gap:13px;
  padding:10px 18px;cursor:pointer;text-decoration:none;
  border-left:3px solid transparent;
  transition:background .1s,border-color .1s;
}
.ex-gs-item:hover,.ex-gs-item.is-focused {
  background:linear-gradient(90deg,rgba(27,63,122,.04),transparent);
  border-left-color:#D4AF37;text-decoration:none;
}
.ex-gs-dot {
  width:36px;height:36px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  transition:transform .14s;
}
.ex-gs-item:hover .ex-gs-dot { transform:scale(1.08); }
.ex-gs-dot svg { width:15px;height:15px; }
.ex-gs-col { flex:1;min-width:0; }
.ex-gs-title {
  font-size:13.5px;font-weight:600;color:#0A2342;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.35;
}
.ex-gs-title mark {
  background:rgba(212,175,55,.25);color:#0A2342;
  border-radius:3px;padding:0 2px;font-style:normal;font-weight:700;
}
.ex-gs-sub {
  font-size:11.5px;color:#94a3b8;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;
}
.ex-gs-badge {
  font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;flex-shrink:0;
  letter-spacing:.02em;
}
.ex-gs-arrow {
  width:14px;height:14px;color:rgba(27,63,122,.18);flex-shrink:0;
  transition:opacity .12s,transform .12s;opacity:0;
}
.ex-gs-item:hover .ex-gs-arrow { opacity:1;transform:translateX(2px); }

/* ══ Footer ══ */
.ex-gs-footer {
  align-items:center;justify-content:space-between;
  padding:10px 18px;border-top:1px solid rgba(27,63,122,.06);
  background:rgba(27,63,122,.015);flex-shrink:0;
}
.ex-gs-count { font-size:11.5px;color:#94a3b8;font-weight:500; }
.ex-gs-more {
  display:inline-flex;align-items:center;gap:5px;
  font-size:12px;font-weight:700;color:#1B3F7A;
  background:rgba(27,63,122,.07);border:1.5px solid rgba(27,63,122,.15);
  border-radius:8px;padding:5px 13px;cursor:pointer;
  transition:all .13s;font-family:var(--font);
}
.ex-gs-more:hover { background:#1B3F7A;color:#fff;border-color:#1B3F7A;box-shadow:none;transform:none; }

/* ══ Hints ══ */
.ex-gs-hints {
  display:flex;gap:16px;padding:8px 18px;
  border-top:1px solid rgba(27,63,122,.04);
  background:rgba(27,63,122,.008);flex-shrink:0;
}
.ex-gs-hints span { font-size:10.5px;color:#b0bacf;display:flex;align-items:center;gap:4px; }
.ex-gs-hints kbd {
  background:rgba(27,63,122,.07);border:1px solid rgba(27,63,122,.12);
  border-radius:4px;padding:1px 5px;font-size:9.5px;font-family:monospace;
  color:#1B3F7A;font-weight:700;
}

/* RTL flip */
[dir=rtl] .ex-gs-item { border-left:none;border-right:3px solid transparent; }
[dir=rtl] .ex-gs-item:hover,[dir=rtl] .ex-gs-item.is-focused { border-right-color:#D4AF37;border-left:none; }
[dir=rtl] .ex-gs-arrow { transform:scaleX(-1); }
[dir=rtl] .ex-gs-item:hover .ex-gs-arrow { transform:scaleX(-1) translateX(2px); }

/* ── Arabic typography for search ── */
[dir=rtl] .ex-gs-input {
  font-family:'Tajawal','DM Sans',sans-serif !important;
  font-size:16px !important;
  font-weight:500;
}
[dir=rtl] .ex-gs-title {
  font-family:'Tajawal','DM Sans',sans-serif !important;
  font-size:14.5px !important;
  font-weight:700 !important;
}
[dir=rtl] .ex-gs-sub {
  font-family:'Tajawal','DM Sans',sans-serif !important;
}
[dir=rtl] .ex-gs-group {
  font-family:'Tajawal','Playfair Display',sans-serif !important;
  font-weight:800 !important;
}
[dir=rtl] .ex-gs-chip {
  font-family:'Tajawal','DM Sans',sans-serif !important;
  font-weight:700 !important;
}
[dir=rtl] .ex-gs-badge {
  font-family:'Tajawal','DM Sans',sans-serif !important;
  font-weight:700 !important;
}
[dir=rtl] .ex-gs-footer,
[dir=rtl] .ex-gs-hints {
  font-family:'Tajawal','DM Sans',sans-serif !important;
}

/* Mobile */
@media(max-width:680px){
  .ex-gs-overlay { padding-top:0;align-items:flex-end; }
  .ex-gs-modal { max-width:100%;border-radius:20px 20px 0 0;max-height:92vh; }
}
</style>
GSCSS;

			/* ── Deep Search JS (runs after DOM is fully built) ── */
			echo "<script>\n";
			echo "(function(){\n";
			echo "  /* ── Read config from trigger button ── */\n";
			echo "  var btn=document.getElementById('ex-gs-open');\n";
			echo "  var SEARCH_URL = btn ? btn.getAttribute('data-search-url') : '';\n";
			echo "  var IS_AR      = btn ? btn.getAttribute('data-is-ar')==='true' : false;\n";
			echo "  var PH         = btn ? btn.getAttribute('data-ph') : '';\n";
			echo "\n";
			echo "  /* ── Localised strings ── */\n";
			echo "  var T={\n";
			echo "    idle:   IS_AR?'ابدأ الكتابة للبحث في جميع أركان البرنامج':'Start typing to search everywhere...',\n";
			echo "    search: IS_AR?'جاري البحث...':'Searching...',\n";
			echo "    noRes:  IS_AR?'لا توجد نتائج':'No results for',\n";
			echo "    show:   IS_AR?'عرض ':' Showing ',\n";
			echo "    of:     IS_AR?' من ':' of ',\n";
			echo "    res:    IS_AR?' نتيجة':' results',\n";
			echo "    more:   IS_AR?'عرض المزيد':'Show more',\n";
			echo "    nav:    IS_AR?'للتنقل':'navigate',\n";
			echo "    open:   IS_AR?'فتح':'open',\n";
			echo "    close:  IS_AR?'إغلاق':'close',\n";
			echo "    all:    IS_AR?'الكل':'All',\n";
			echo "    chips:  IS_AR\n";
			echo "      ? {all:'الكل',customers:'العملاء',suppliers:'الموردون',items:'الأصناف',invoices:'الفواتير',purchases:'المشتريات',accounts:'الحسابات',bank:'البنك',journal:'اليومية'}\n";
			echo "      : {all:'All',customers:'Customers',suppliers:'Suppliers',items:'Items',invoices:'Invoices',purchases:'Purchases',accounts:'Accounts',bank:'Bank',journal:'Journal'}\n";
			echo "  };\n";
			echo "\n";
			echo "  /* ── Wire up DOM elements ── */\n";
			echo "  var overlay=document.getElementById('ex-gs-overlay');\n";
			echo "  var inp=document.getElementById('ex-gs-input');\n";
			echo "  var stDiv=document.getElementById('ex-gs-state');\n";
			echo "  var resDiv=document.getElementById('ex-gs-results');\n";
			echo "  var footer=document.getElementById('ex-gs-footer');\n";
			echo "  var countEl=document.getElementById('ex-gs-count');\n";
			echo "  var moreBtn=document.getElementById('ex-gs-more');\n";
			echo "  var chipsEl=document.getElementById('ex-gs-chips');\n";
			echo "  var hintsEl=document.getElementById('ex-gs-hints');\n";
			echo "\n";
			echo "  if(!overlay||!inp) return; /* safety — elements not found */\n";
			echo "\n";
			echo "  /* ── Set placeholder & build chips & hints ── */\n";
			echo "  inp.placeholder = PH;\n";
			echo "  var chipKeys=['all','customers','suppliers','items','invoices','purchases','accounts','bank','journal'];\n";
			echo "  chipsEl.innerHTML=chipKeys.map(function(k,i){\n";
			echo "    return '<button class=\"ex-gs-chip'+(i===0?' is-on':'')+'\" data-s=\"'+k+'\">'+T.chips[k]+'</button>';\n";
			echo "  }).join('');\n";
			echo "  moreBtn.innerHTML=T.more+' <svg width=\"11\" height=\"11\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\"><polyline points=\"6 9 12 15 18 9\"/></svg>';\n";
			echo "  hintsEl.innerHTML=\n";
			echo "    '<span><kbd>&#8593;&#8595;</kbd> '+T.nav+'</span>'\n";
			echo "    +'<span><kbd>Enter</kbd> '+T.open+'</span>'\n";
			echo "    +'<span><kbd>ESC</kbd> '+T.close+'</span>';\n";
			echo "\n";
			echo "  var PREVIEW=8,LIMIT=40,allData=[],section='all',timer=null,lastQ='',focusIdx=-1;\n";
			echo "\n";
			echo "  var IDLE_SVG='<svg width=\"44\" height=\"44\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1\"><circle cx=\"11\" cy=\"11\" r=\"8\"/><line x1=\"21\" y1=\"21\" x2=\"16.65\" y2=\"16.65\"/></svg>';\n";
			echo "\n";
			echo "  function setIdle(){\n";
			echo "    stDiv.style.display='flex';\n";
			echo "    stDiv.innerHTML=IDLE_SVG+'<p>'+T.idle+'</p>';\n";
			echo "  }\n";
			echo "  setIdle();\n";
			echo "\n";
			echo "  window.exGsOpen=function(){\n";
			echo "    overlay.classList.add('is-open');\n";
			echo "    setTimeout(function(){inp.focus();},80);\n";
			echo "  };\n";
			echo "  window.exGsClose=function(){\n";
			echo "    overlay.classList.remove('is-open');\n";
			echo "    inp.value='';resDiv.innerHTML='';\n";
			echo "    footer.style.display='none';\n";
			echo "    setIdle();\n";
			echo "    allData=[];focusIdx=-1;lastQ='';\n";
			echo "  };\n";
			echo "  window.exGsShowAll=function(){\n";
			echo "    moreBtn.style.display='none';\n";
			echo "    countEl.textContent=T.all+' '+allData.length+T.res;\n";
			echo "    resDiv.innerHTML=buildItems(allData,inp.value.trim());\n";
			echo "  };\n";
			echo "\n";
			echo "  document.addEventListener('keydown',function(e){\n";
			echo "    if((e.ctrlKey||e.metaKey)&&e.key==='k'){e.preventDefault();window.exGsOpen();}\n";
			echo "    if(e.key==='Escape'&&overlay.classList.contains('is-open')){window.exGsClose();}\n";
			echo "    if(!overlay.classList.contains('is-open')) return;\n";
			echo "    var items=resDiv.querySelectorAll('.ex-gs-item');\n";
			echo "    if(e.key==='ArrowDown'){e.preventDefault();focusIdx=Math.min(focusIdx+1,items.length-1);setFocus(items);}\n";
			echo "    if(e.key==='ArrowUp'){e.preventDefault();if(focusIdx>0){focusIdx--;setFocus(items);}else{focusIdx=-1;inp.focus();}}\n";
			echo "    if(e.key==='Enter'&&focusIdx>=0&&items[focusIdx])items[focusIdx].click();\n";
			echo "  });\n";
			echo "  function setFocus(items){\n";
			echo "    items.forEach(function(it,i){it.classList.toggle('is-focused',i===focusIdx);});\n";
			echo "    if(focusIdx>=0)items[focusIdx].focus();\n";
			echo "  }\n";
			echo "\n";
			echo "  chipsEl.addEventListener('click',function(e){\n";
			echo "    var chip=e.target.closest('.ex-gs-chip');if(!chip)return;\n";
			echo "    chipsEl.querySelectorAll('.ex-gs-chip').forEach(function(c){c.classList.remove('is-on');});\n";
			echo "    chip.classList.add('is-on');\n";
			echo "    section=chip.dataset.s;\n";
			echo "    var q=inp.value.trim();\n";
			echo "    if(q.length>=2)doSearch(q,true);\n";
			echo "  });\n";
			echo "\n";
			echo "  inp.addEventListener('input',function(){\n";
			echo "    var q=inp.value.trim();\n";
			echo "    if(q.length<1){\n";
			echo "      resDiv.innerHTML='';footer.style.display='none';\n";
			echo "      setIdle();lastQ='';allData=[];return;\n";
			echo "    }\n";
			echo "    clearTimeout(timer);\n";
			echo "    stDiv.style.display='flex';\n";
			echo "    stDiv.innerHTML='<div class=\"ex-gs-spinner\"></div><p>'+T.search+'</p>';\n";
			echo "    resDiv.innerHTML='';footer.style.display='none';\n";
			echo "    timer=setTimeout(function(){doSearch(q,false);},120);\n";
			echo "  });\n";
			echo "\n";
			echo "  function doSearch(q,force){\n";
			echo "    if(q===lastQ&&!force)return;\n";
			echo "    lastQ=q;\n";
			echo "    var url=SEARCH_URL+'?q='+encodeURIComponent(q)+'&limit='+LIMIT+(section!=='all'?'&s='+section:'')+'&lang='+(IS_AR?'ar':'en');\n";
			echo "    fetch(url,{credentials:'same-origin'})\n";
			echo "      .then(function(r){return r.json();})\n";
			echo "      .then(function(d){\n";
			echo "        if(d.error){\n";
			echo "          stDiv.style.display='flex';\n";
			echo "          var msg=d.error==='Unauthorized'?(IS_AR?'انتهت الجلسة، أعد تسجيل الدخول':'Session expired, please login again'):d.error;\n";
			echo "          stDiv.innerHTML='<svg width=\"32\" height=\"32\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#F59E0B\" stroke-width=\"1.5\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><line x1=\"12\" y1=\"8\" x2=\"12\" y2=\"12\"/><line x1=\"12\" y1=\"16\" x2=\"12.01\" y2=\"16\"/></svg><p style=\"color:#F59E0B\">'+msg+'</p>';\n";
			echo "          return;\n";
			echo "        }\n";
			echo "        allData=d.results||[];renderResults(allData,q,d.total||0);\n";
			echo "      })\n";
			echo "      .catch(function(e){\n";
			echo "        stDiv.style.display='flex';\n";
			echo "        stDiv.innerHTML='<svg width=\"32\" height=\"32\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#e53e3e\" stroke-width=\"1.5\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><line x1=\"12\" y1=\"8\" x2=\"12\" y2=\"12\"/><line x1=\"12\" y1=\"16\" x2=\"12.01\" y2=\"16\"/></svg><p style=\"color:#e53e3e\">'+(IS_AR?'خطأ في الاتصال بالخادم':'Server connection error')+'</p>';\n";
			echo "        console.error('Search error:',e);\n";
			echo "      });\n";
			echo "  }\n";
			echo "\n";
			echo "  var IC={\n";
			echo "    customer:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/></svg>',\n";
			echo "    supplier:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M1 3h15v13H1z\"/><path d=\"M16 8h4l3 3v5h-7V8z\"/><circle cx=\"5.5\" cy=\"18.5\" r=\"2.5\"/><circle cx=\"18.5\" cy=\"18.5\" r=\"2.5\"/></svg>',\n";
			echo "    item:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z\"/></svg>',\n";
			echo "    invoice:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"/><polyline points=\"14 2 14 8 20 8\"/><line x1=\"16\" y1=\"13\" x2=\"8\" y2=\"13\"/><line x1=\"16\" y1=\"17\" x2=\"8\" y2=\"17\"/></svg>',\n";
			echo "    purchase:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"9\" cy=\"21\" r=\"1\"/><circle cx=\"20\" cy=\"21\" r=\"1\"/><path d=\"M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6\"/></svg>',\n";
			echo "    journal:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M4 19.5A2.5 2.5 0 0 1 6.5 17H20\"/><path d=\"M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z\"/></svg>',\n";
			echo "    bank:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"2\" y=\"7\" width=\"20\" height=\"14\" rx=\"2\"/><path d=\"M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2\"/><line x1=\"12\" y1=\"12\" x2=\"12\" y2=\"16\"/><line x1=\"10\" y1=\"14\" x2=\"14\" y2=\"14\"/></svg>',\n";
			echo "    account:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><line x1=\"8\" y1=\"6\" x2=\"21\" y2=\"6\"/><line x1=\"8\" y1=\"12\" x2=\"21\" y2=\"12\"/><line x1=\"8\" y1=\"18\" x2=\"21\" y2=\"18\"/><line x1=\"3\" y1=\"6\" x2=\"3.01\" y2=\"6\"/><line x1=\"3\" y1=\"12\" x2=\"3.01\" y2=\"12\"/><line x1=\"3\" y1=\"18\" x2=\"3.01\" y2=\"18\"/></svg>',\n";
			echo "    dimension:'<svg width=\"15\" height=\"15\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><polygon points=\"12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2\"/><line x1=\"12\" y1=\"22\" x2=\"12\" y2=\"15.5\"/><polyline points=\"22 8.5 12 15.5 2 8.5\"/></svg>'\n";
			echo "  };\n";
			echo "  var ICfallback=IC.account;\n";
			echo "\n";
			echo "  function hl(t,q){\n";
			echo "    if(!q)return t;\n";
			echo "    return t.replace(new RegExp('('+q.replace(/[.*+?^\${}()|[\\]\\\\]/g,'\\\\$&')+')','gi'),'<mark>\$1</mark>');\n";
			echo "  }\n";
			echo "\n";
			echo "  function buildItems(arr,q){\n";
			echo "    var h='',lastType='';\n";
			echo "    arr.forEach(function(r){\n";
			echo "      if(r.type!==lastType){\n";
			echo "        if(lastType)h+='<div class=\"ex-gs-sep\"></div>';\n";
			echo "        h+='<div class=\"ex-gs-group\">'+(IS_AR?r.type_ar:r.type)+'</div>';\n";
			echo "        lastType=r.type;\n";
			echo "      }\n";
			echo "      var ic=IC[r.type]||ICfallback;\n";
			echo "      var bg=r.color+'22';\n";
			echo "      h+='<a class=\"ex-gs-item\" href=\"'+r.url+'\" tabindex=\"0\">';\n";
			echo "      h+='<div class=\"ex-gs-dot\" style=\"background:'+bg+';color:'+r.color+'\">'+ic+'</div>';\n";
			echo "      h+='<div class=\"ex-gs-col\">';\n";
			echo "      h+='<div class=\"ex-gs-title\">'+hl(r.title,q)+'</div>';\n";
			echo "      if(r.subtitle)h+='<div class=\"ex-gs-sub\">'+r.subtitle+'</div>';\n";
			echo "      h+='</div>';\n";
			echo "      h+='<span class=\"ex-gs-badge\" style=\"background:'+bg+';color:'+r.color+'\">'+(IS_AR?r.type_ar:r.type)+'</span>';\n";
			echo "      h+='<svg class=\"ex-gs-arrow\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.5\"><polyline points=\"9 18 15 12 9 6\"/></svg>';\n";
			echo "      h+='</a>';\n";
			echo "    });\n";
			echo "    return h;\n";
			echo "  }\n";
			echo "\n";
			echo "  function renderResults(arr,q,total){\n";
			echo "    stDiv.style.display='none';\n";
			echo "    if(!arr.length){\n";
			echo "      stDiv.style.display='flex';\n";
			echo "      stDiv.innerHTML=IDLE_SVG+'<p>'+T.noRes+' <strong>'+q+'</strong></p>';\n";
			echo "      resDiv.innerHTML='';footer.style.display='none';return;\n";
			echo "    }\n";
			echo "    var preview=arr.slice(0,PREVIEW);\n";
			echo "    resDiv.innerHTML=buildItems(preview,q);\n";
			echo "    footer.style.display='flex';\n";
			echo "    if(arr.length>PREVIEW){\n";
			echo "      countEl.textContent=T.show+PREVIEW+T.of+arr.length+T.res;\n";
			echo "      moreBtn.style.display='inline-flex';\n";
			echo "    }else{\n";
			echo "      countEl.textContent=arr.length+T.res;\n";
			echo "      moreBtn.style.display='none';\n";
			echo "    }\n";
			echo "    focusIdx=-1;\n";
			echo "  }\n";
			echo "})();\n";
			echo "</script>\n";

			/* ── Teleport overlay to direct body child ── *
			   Guarantees position:fixed works even if any parent has
			   overflow/transform/will-change that breaks stacking context.
			   Runs as early as possible; re-wires the click handler after move.
			 * ────────────────────────────────────────────── */
			echo "<script>\n";
			echo "(function(){\n";
			echo "  function teleportOverlay(){\n";
			echo "    var ov=document.getElementById('ex-gs-overlay');\n";
			echo "    if(ov && ov.parentNode!==document.body){\n";
			echo "      document.body.appendChild(ov);\n";
			echo "    }\n";
			echo "  }\n";
			echo "  if(document.body){ teleportOverlay(); }\n";
			echo "  else { document.addEventListener('DOMContentLoaded', teleportOverlay); }\n";
			echo "})();\n";
			echo "</script>\n";

			if ($no_menu == false) {
				echo "<div class='ex-power-strip'>\n";
				echo "  <a target='_blank' href='" . $SysPrefs->power_url . "' tabindex='-1'>"
					. $SysPrefs->app_title . " $version &nbsp;·&nbsp; Theme: " . user_theme()
					. " &nbsp;·&nbsp; " . show_users_online() . "</a>\n";
				echo "  <a target='_blank' href='" . $SysPrefs->power_url . "' tabindex='-1' style='color:#D4AF37'>"
					. $SysPrefs->power_by . "</a>\n";
				echo "</div>\n";
			}
		}

		function display_applications(&$waapp)
		{
			global $path_to_root;
			$selected_app = $waapp->get_selected_application();
			if (!$_SESSION["wa_current_user"]->check_application_access($selected_app)) return;

			if (method_exists($selected_app, 'render_index')) {
				$selected_app->render_index();
				return;
			}

			echo "<div class='ex-modules'>\n";
			foreach ($selected_app->modules as $module)
			{
				if (!$_SESSION["wa_current_user"]->check_module_access($module)) continue;

				echo "<div class='ex-mod-card'>\n";
				echo "  <div class='ex-mod-head'>\n";
				echo "    <span class='ex-mod-title'>" . $module->name . "</span>\n";
				echo "  </div>\n";
				echo "  <div class='ex-mod-body'>\n";

				/* Left column */
				echo "    <div class='ex-mod-col'>\n";
				foreach ($module->lappfunctions as $fn) {
					$img = $this->get_icon($fn->category);
					if ($fn->label == '') {
						echo "      <div class='ex-spacer'></div>\n";
					} elseif ($_SESSION["wa_current_user"]->can_access_page($fn->access)) {
						echo "      <div class='ex-link-row'>$img" . menu_link($fn->link, $fn->label) . "</div>\n";
					} elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items()) {
						echo "      <div class='ex-link-row ex-link-off'>$img<span class='inactive'>" . access_string($fn->label, true) . "</span></div>\n";
					}
				}
				echo "    </div>\n";

				/* Right column */
				if (sizeof($module->rappfunctions) > 0) {
					echo "    <div class='ex-mod-col'>\n";
					foreach ($module->rappfunctions as $fn) {
						$img = $this->get_icon($fn->category);
						if ($fn->label == '') {
							echo "      <div class='ex-spacer'></div>\n";
						} elseif ($_SESSION["wa_current_user"]->can_access_page($fn->access)) {
							echo "      <div class='ex-link-row'>$img" . menu_link($fn->link, $fn->label) . "</div>\n";
						} elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items()) {
							echo "      <div class='ex-link-row ex-link-off'>$img<span class='inactive'>" . access_string($fn->label, true) . "</span></div>\n";
						}
					}
					echo "    </div>\n";
				}

				echo "  </div>\n";
				echo "</div>\n";
			}
			echo "</div>\n";
		}

		/* ════════════════════════════════════════
		   LANGUAGE SWITCHER IN HEADER
		════════════════════════════════════════ */
		private function _render_lang_switcher($path_to_root)
		{
			$cur_code  = isset($_SESSION['language']) ? $_SESSION['language']->code : 'en_IN';
			$is_arabic = ($cur_code === 'ar_EG');

			/* Always pure ASCII — zero encoding issues */
			$cur_label    = $is_arabic ? 'AR' : 'EN';
			$next_label   = $is_arabic ? 'EN' : 'AR';
			$target_code  = $is_arabic ? 'en_IN' : 'ar_EG';

			$action_url  = $path_to_root . '/admin/display_prefs.php';
			$redirect_to = $path_to_root . '/admin/dashboard.php'; /* → Dashboard after switch */

			echo "    <!-- Language Toggle -->\n";
			echo "    <form method='post' action='" . htmlspecialchars($action_url) . "' style='margin:0;display:inline-flex' id='ex-lang-form'>\n";
			echo "      <input type='hidden' name='setprefs'               value='1'>\n";
			echo "      <input type='hidden' name='language'               value='" . htmlspecialchars($target_code) . "'>\n";
			echo "      <input type='hidden' name='prices_dec'             value='" . (function_exists('user_price_dec')             ? user_price_dec()             : 2)         . "'>\n";
			echo "      <input type='hidden' name='qty_dec'                value='" . (function_exists('user_qty_dec')               ? user_qty_dec()               : 2)         . "'>\n";
			echo "      <input type='hidden' name='rates_dec'              value='" . (function_exists('user_exrate_dec')            ? user_exrate_dec()            : 4)         . "'>\n";
			echo "      <input type='hidden' name='percent_dec'            value='" . (function_exists('user_percent_dec')           ? user_percent_dec()           : 1)         . "'>\n";
			echo "      <input type='hidden' name='date_format'            value='" . (function_exists('user_date_format')           ? user_date_format()           : 'd/m/Y')   . "'>\n";
			echo "      <input type='hidden' name='date_sep'               value='" . (function_exists('user_date_sep')              ? user_date_sep()              : '/')       . "'>\n";
			echo "      <input type='hidden' name='tho_sep'                value='" . (function_exists('user_tho_sep')               ? user_tho_sep()               : ',')       . "'>\n";
			echo "      <input type='hidden' name='dec_sep'                value='" . (function_exists('user_dec_sep')               ? user_dec_sep()               : '.')       . "'>\n";
			echo "      <input type='hidden' name='theme'                  value='" . (function_exists('user_theme')                 ? user_theme()                 : 'default') . "'>\n";
			echo "      <input type='hidden' name='page_size'              value='" . (function_exists('user_pagesize')              ? user_pagesize()              : 'Letter')  . "'>\n";
			echo "      <input type='hidden' name='startup_tab'            value='" . (function_exists('user_startup_tab')           ? user_startup_tab()           : 0)         . "'>\n";
			echo "      <input type='hidden' name='print_profile'          value='" . (function_exists('user_print_profile')         ? user_print_profile()         : '')        . "'>\n";
			echo "      <input type='hidden' name='query_size'             value='" . (function_exists('user_query_size')            ? user_query_size()            : 10)        . "'>\n";
			echo "      <input type='hidden' name='transaction_days'       value='" . (function_exists('user_transaction_days')      ? user_transaction_days()      : 30)        . "'>\n";
			echo "      <input type='hidden' name='save_report_selections' value='" . (function_exists('user_save_report_selections') ? user_save_report_selections() : 0)       . "'>\n";
			echo "      <input type='hidden' name='def_print_destination'  value='" . (function_exists('user_def_print_destination')  ? user_def_print_destination()  : 0)       . "'>\n";
			echo "      <input type='hidden' name='def_print_orientation'  value='" . (function_exists('user_def_print_orientation')  ? user_def_print_orientation()  : 0)       . "'>\n";
			echo "      <input type='hidden' name='show_gl'                value='" . (function_exists('user_show_gl_info')    && user_show_gl_info()    ? 1 : 0) . "'>\n";
			echo "      <input type='hidden' name='show_codes'             value='" . (function_exists('user_show_codes')      && user_show_codes()      ? 1 : 0) . "'>\n";
			echo "      <input type='hidden' name='show_hints'             value='" . (function_exists('user_hints')           && user_hints()           ? 1 : 0) . "'>\n";
			echo "      <input type='hidden' name='rep_popup'              value='" . (function_exists('user_rep_popup')       && user_rep_popup()       ? 1 : 0) . "'>\n";
			echo "      <input type='hidden' name='graphic_links'          value='" . (function_exists('user_graphic_links')   && user_graphic_links()   ? 1 : 0) . "'>\n";
			echo "      <input type='hidden' name='sticky_doc_date'        value='" . (function_exists('sticky_doc_date')      && sticky_doc_date()      ? 1 : 0) . "'>\n";
			echo "      <input type='hidden' name='use_date_picker'        value='" . (function_exists('user_use_date_picker') && user_use_date_picker() ? 1 : 0) . "'>\n";
			/* Button: [ EN → AR ] */
			echo "      <button type='submit' class='ex-lang-btn' title='Switch to " . $next_label . "'>\n";
			echo "        <svg width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' style='flex-shrink:0'><circle cx='12' cy='12' r='10'/><line x1='2' y1='12' x2='22' y2='12'/><path d='M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'/></svg>\n";
			echo "        <span class='ex-lang-cur'>" . $cur_label . "</span>\n";
			echo "        <span class='ex-lang-sep'>&#8594;</span>\n";
			echo "        <span class='ex-lang-nxt'>" . $next_label . "</span>\n";
			echo "      </button>\n";
			echo "    </form>\n";
			/* JS: send prefs via fetch(), then redirect to Dashboard */
			echo "    <script>\n";
			echo "    (function(){\n";
			echo "      var f=document.getElementById('ex-lang-form');\n";
			echo "      if(!f)return;\n";
			echo "      f.addEventListener('submit',function(e){\n";
			echo "        e.preventDefault();\n";
			echo "        var fd=new FormData(f);\n";
			echo "        fetch(f.action,{method:'POST',body:fd,credentials:'same-origin'})\n";
			echo "          .then(function(){window.location.href='" . addslashes($redirect_to) . "';})\n";
			echo "          .catch(function(){window.location.href='" . addslashes($redirect_to) . "';});\n";
			echo "      });\n";
			echo "    })();\n";
			echo "    </script>\n";
		}

		/* ════════════════════════════════════════
		   SVG ICONS PER APP
		════════════════════════════════════════ */
		private function _app_svg($app_id)
		{
			$icons = [
				'orders'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
				'AP'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
				'stock'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
				'manuf'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h20"/><path d="M5 20V8l5 4V8l5 4V4l5 4v12"/></svg>',
				'proj'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/><line x1="12" y1="22" x2="12" y2="15.5"/><polyline points="22 8.5 12 15.5 2 8.5"/></svg>',
				'GL'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
				'assets'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
				'system'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
			];
			$fallback = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
			return $icons[$app_id] ?? $fallback;
		}

		/* ════════════════════════════════════════
		   SVG ICONS — Extended Nav (by icon_id key)
		════════════════════════════════════════ */
		private function _nav_svg($icon_id)
		{
			$icons = [
				/* ── FA App icons ── */
				'sales'        => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
				'purchases'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
				'inventory'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
				'manufacturing'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h20"/><path d="M5 20V8l5 4V8l5 4V4l5 4v12"/></svg>',
				'dimensions'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/><line x1="12" y1="22" x2="12" y2="15.5"/><polyline points="22 8.5 12 15.5 2 8.5"/></svg>',
				'gl'           => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="14" y2="11"/></svg>',
				'assets'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
				'settings'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',

				/* ── Page item icons ── */
				'banking'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M3 10h18"/><path d="M5 6l7-3 7 3"/><line x1="4" y1="10" x2="4" y2="21"/><line x1="8" y1="10" x2="8" y2="21"/><line x1="12" y1="10" x2="12" y2="21"/><line x1="16" y1="10" x2="16" y2="21"/><line x1="20" y1="10" x2="20" y2="21"/></svg>',
				'trial_balance'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><line x1="11" y1="1" x2="13" y2="1"/><line x1="11" y1="23" x2="13" y2="23"/></svg>',
				'journal'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
				'dims2'        => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
				'cost_center'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
				'coa'          => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
				'tax'          => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>',
				'opening_bal'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
				'npo'          => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',

				'default'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
			];
			return $icons[$icon_id] ?? $icons['default'];
		}

		/* ════════════════════════════════════════
		   LAYOUT CSS + JS
		════════════════════════════════════════ */
		private function _inject_layout_styles()
		{
			echo <<<'LAYOUT'
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap');

:root {
  --sidebar-main:   #1B3F7A;
  --sidebar-deep:   #142E58;
  --sidebar-hover:  rgba(255,255,255,0.07);
  --sidebar-active: rgba(212,175,55,0.14);
  --sidebar-border: rgba(255,255,255,0.09);
  --sidebar-text:   rgba(255,255,255,0.93);
  --accent:         #D4AF37;
  --accent-light:   #FBF5E0;
  --primary:        #164E8F;
  --primary-hover:  #1A5EA8;
  --primary-light:  #EBF2FB;
  --primary-ultra:  #F0F5FD;
  --primary-border: #C3D7F0;
  --bg-app:         #F4F6FA;
  --bg-surface:     #FFFFFF;
  --bg-surface-2:   #F8F9FC;
  --border:         #E2E8F0;
  --border-light:   #EDF0F7;
  --text-primary:   #050505;
  --text-secondary: #2D3748;
  --text-tertiary:  #4A5568;
  --text-muted:     #718096;
  --text-inverse:   #FFFFFF;
  --shadow-card:    0 1px 3px rgba(10,35,66,.05),0 4px 14px rgba(10,35,66,.07);
  --shadow-primary: 0 4px 16px rgba(22,78,143,.28);
  --shadow-xl:      0 16px 48px rgba(10,35,66,.18);
  --radius-lg:      14px;
  --radius-md:      10px;
  --radius-sm:      6px;
  --radius-pill:    999px;
  --radius-2xl:     28px;
  --font:           'DM Sans','Tajawal','Inter',-apple-system,sans-serif;
  --font-h:         'Playfair Display','Tajawal','Inter',sans-serif;
  --font-ar:        'Tajawal','DM Sans',sans-serif;
  --sidebar-w:      252px;
  --topbar-h:       58px;
  --ease:           cubic-bezier(.4,0,.2,1);
}

/* Shell */
.ex-shell {
  display:flex; min-height:100vh; width:100%;
  background:var(--bg-app); font-family:var(--font);
}

/* ── Sidebar ── */
.ex-sidebar {
  width:var(--sidebar-w); min-width:var(--sidebar-w);
  background:var(--sidebar-main);
  border-right:1.5px solid var(--border);
  display:flex; flex-direction:column;
  position:sticky; top:0; height:100vh;
  overflow-y:auto; overflow-x:hidden; z-index:100;
  transition:width .25s var(--ease),min-width .25s var(--ease);
  scrollbar-width:thin; scrollbar-color:var(--border) transparent;
  box-shadow:2px 0 12px rgba(22,78,143,.06);
}
.ex-sidebar::-webkit-scrollbar{width:4px}
.ex-sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px}

.ex-sidebar.collapsed {
  width:62px; min-width:62px;
}
.ex-sidebar.collapsed .ex-brand-info,
.ex-sidebar.collapsed .ex-nav-label,
.ex-sidebar.collapsed .ex-nav-section-label,
.ex-sidebar.collapsed .ex-nav-pip,
.ex-sidebar.collapsed .ex-user-details,
.ex-sidebar.collapsed .ex-collapse-btn { display:none !important; }
.ex-sidebar.collapsed .ex-brand { justify-content:center; padding:16px 0; }
.ex-sidebar.collapsed .ex-nav-item { justify-content:center; padding:12px 0; }
.ex-sidebar.collapsed .ex-user-card { justify-content:center; }

/* Brand */
.ex-brand {
  display:flex; align-items:center; gap:10px;
  padding:18px 16px 14px;
  border-bottom:1px solid var(--sidebar-border); flex-shrink:0;
}
.ex-brand-logo { flex-shrink:0; width:30px; height:30px; }
.ex-brand-info { flex:1; display:flex; flex-direction:column; overflow:hidden; }
.ex-brand-name {
  color:#FFFFFF; font-family:var(--font-h); font-weight:700; font-size:14px;
  line-height:1.2; letter-spacing:-.01em; white-space:nowrap;
}
.ex-brand-co {
  color:rgba(255,255,255,0.40); font-size:10.5px; white-space:nowrap;
  overflow:hidden; text-overflow:ellipsis; margin-top:1px;
}
.ex-collapse-btn {
  background:rgba(255,255,255,0.07); border:none; color:rgba(255,255,255,0.50);
  cursor:pointer; padding:5px; border-radius:6px; width:24px; height:24px;
  display:flex; align-items:center; justify-content:center;
  transition:all .18s var(--ease); flex-shrink:0; box-shadow:none; transform:none;
}
.ex-collapse-btn:hover { background:rgba(255,255,255,0.14); color:#FFF; transform:none; box-shadow:none; }

/* Nav section label — hidden, no group headers */
.ex-nav-section-label { display:none; }

/* Nav category separator — hidden */
.ex-nav-sep { display:none; }

/* Nav container */
.ex-nav { flex:1; padding:4px 0; }

/* ══════════════════════════════════════════════
   Each nav item = independent full-width slider
   — snow white, bold, same scale as "Dashboard"
══════════════════════════════════════════════ */
.ex-nav-item {
  display:flex !important;
  align-items:center;
  gap:13px;
  padding:13px 20px 13px 18px;
  color:#FFFFFF !important;                /* pure snow white — always */
  text-decoration:none !important;
  font-family:'Inter','DM Sans',sans-serif !important;
  font-size:14px !important;               /* clear & prominent */
  font-weight:600 !important;              /* bold */
  letter-spacing:-0.01em;
  line-height:1.3;
  border-left:3px solid transparent;
  border-bottom:1px solid rgba(255,255,255,0.06);
  position:relative; cursor:pointer; white-space:nowrap;
  transition:background .16s ease, border-color .16s ease, padding-left .14s ease;
  opacity:1 !important;
}
.ex-nav-item:last-child { border-bottom:none; }

.ex-nav-item:hover {
  background:rgba(255,255,255,0.10) !important;
  color:#FFFFFF !important;
  text-decoration:none !important;
  border-left-color:rgba(212,175,55,0.70);
  padding-left:22px;
}
.ex-nav-item.is-active {
  background:rgba(212,175,55,0.18) !important;
  color:#FFFFFF !important;
  font-weight:700 !important;
  border-left-color:var(--accent) !important;
  padding-left:22px;
}
.ex-nav-icon {
  flex-shrink:0;
  opacity:0.75;
  display:flex !important;
  align-items:center;
  width:18px;
}
.ex-nav-item:hover .ex-nav-icon,
.ex-nav-item.is-active .ex-nav-icon { opacity:1; }

.ex-nav-label {
  flex:1;
  color:#FFFFFF !important;
  font-size:14px !important;
  font-weight:600 !important;
  display:block !important;
  opacity:1 !important;
  visibility:visible !important;
}

.ex-nav-pip {
  width:7px; height:7px; border-radius:50%;
  background:var(--accent); flex-shrink:0;
  box-shadow:0 0 8px rgba(212,175,55,0.7);
}

/* Sidebar bottom */
.ex-sidebar-bottom {
  border-top:1px solid var(--sidebar-border); padding:12px; flex-shrink:0;
}
.ex-user-card {
  display:flex; align-items:center; gap:10px; padding:8px 10px;
  background:rgba(255,255,255,0.05);
  border-radius:10px; border:1px solid var(--sidebar-border);
}
.ex-user-av {
  width:32px; height:32px; border-radius:50%;
  background:linear-gradient(135deg,var(--primary) 0%,var(--accent) 100%);
  color:#FFF; font-weight:700; font-size:13px; font-family:var(--font-h);
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ex-user-details { display:flex; flex-direction:column; overflow:hidden; }
.ex-user-name { color:rgba(255,255,255,0.90); font-size:12.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ex-user-badge { color:rgba(255,255,255,0.35); font-size:10.5px; }

/* ── Body ── */
.ex-body {
  flex:1; display:flex; flex-direction:column; min-width:0; min-height:100vh;
  overflow-x:hidden; max-width:100%;
  transition:margin-left .25s var(--ease);
}
.ex-nomenu { width:100%; }
.ex-body.sidebar-collapsed { /* JS controlled via class */ }

/* ── Top Bar ── */
.ex-topbar {
  height:var(--topbar-h); background:var(--bg-surface);
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
  padding:0 22px; position:sticky; top:0; z-index:90;
  box-shadow:0 1px 0 var(--border),0 2px 8px rgba(0,0,0,.03); flex-shrink:0;
}
.ex-topbar-l { display:flex; align-items:center; gap:12px; }
.ex-breadcrumb { font-size:12.5px; color:var(--text-muted); font-weight:500; letter-spacing:.01em; }
.ex-mobile-menu-btn {
  display:none; background:transparent; border:1.5px solid var(--border);
  color:var(--text-tertiary); padding:6px; border-radius:6px; cursor:pointer;
  box-shadow:none; transform:none; transition:all .16s;
}
.ex-mobile-menu-btn:hover { background:var(--primary-light); border-color:var(--primary-border); box-shadow:none; transform:none; }
.ex-topbar-r { display:flex; align-items:center; gap:3px; }
.ex-hdr-btn {
  display:inline-flex; align-items:center; gap:5px;
  padding:7px 11px; border-radius:8px;
  color:var(--text-tertiary); font-size:12.5px; font-weight:500;
  text-decoration:none; transition:all .16s var(--ease); white-space:nowrap;
}
.ex-hdr-btn:hover { background:var(--primary-light); color:var(--primary); text-decoration:none; }
.ex-hdr-btn svg { opacity:.65; }
.ex-hdr-btn:hover svg { opacity:1; }
.ex-hdr-divider { width:1px; height:22px; background:var(--border); margin:0 6px; }
.ex-hdr-logout {
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 14px; border-radius:8px;
  background:#FEF2F2; color:#DC2626;
  font-size:12.5px; font-weight:600; text-decoration:none;
  transition:all .16s var(--ease); margin-left:4px;
}
.ex-hdr-logout:hover { background:#FECACA; color:#B91C1C; text-decoration:none; }

/* ── Main ── */
.ex-main { flex:1; padding:6px 26px 36px; overflow-x:auto; min-width:0; max-width:100%; box-sizing:border-box; }

.ex-page-title {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid var(--border);
}
.ex-heading {
  font-family:var(--font-h); font-size:22px; font-weight:800;
  color:var(--text-primary); margin:0; letter-spacing:-.03em;
  border-left:4px solid var(--accent); padding-left:14px;
}

/* ══ Module Grid — Premium ══ */
.ex-modules {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr));
  gap:20px; padding:2px 0;
}
.ex-mod-card {
  background:#fff; border-radius:16px;
  border:1px solid #E4EAF4;
  box-shadow:0 2px 10px rgba(10,35,66,.07);
  overflow:hidden;
  transition:box-shadow .22s ease, border-color .22s ease, transform .22s ease;
}
.ex-mod-card:hover {
  box-shadow:0 8px 30px rgba(10,35,66,.13);
  border-color:#B8CFEC; transform:translateY(-3px);
}
.ex-mod-head {
  position:relative; overflow:hidden;
  background:linear-gradient(135deg, #0A2342 0%, #0F3364 100%);
  border-bottom:2px solid rgba(212,175,55,.35);
  padding:13px 22px 11px;
}
.ex-mod-head::after {
  content:''; position:absolute; top:-20px; right:-20px;
  width:70px; height:70px; border-radius:50%;
  background:radial-gradient(circle, rgba(212,175,55,.10) 0%, transparent 70%);
  pointer-events:none;
}
.ex-mod-title {
  font-family:var(--font-h); font-size:11px; font-weight:700;
  color:#fff; text-transform:uppercase; letter-spacing:.12em;
  display:flex; align-items:center; gap:10px;
  position:relative; z-index:1;
}
.ex-mod-title::before {
  content:''; display:block; flex-shrink:0;
  width:20px; height:2px;
  background:linear-gradient(90deg, #D4AF37, #F0D060);
  border-radius:1px;
}
.ex-mod-body { display:flex; padding:14px 0 12px; }
.ex-mod-col { flex:1; padding:0 20px; border-right:1px solid #EEF2FA; }
.ex-mod-col:last-child { border-right:none; }

.ex-link-row {
  display:flex; align-items:center; gap:9px;
  padding:7px 10px; border-radius:8px; margin-bottom:1px;
  transition:background .15s ease;
}
.ex-link-row:hover { background:#EDF3FC; }
.ex-link-row::before {
  content:''; display:block; flex-shrink:0;
  width:5px; height:5px; border-radius:50%;
  background:#CBD8EC; transition:background .15s;
}
.ex-link-row:hover::before { background:#1A56A8; }
.ex-link-row > img {
  width:13px; height:13px; opacity:.55;
  flex-shrink:0; transition:opacity .15s;
}
.ex-link-row:hover > img { opacity:.9; }
.ex-link-row a {
  font-family:var(--font); font-size:13.5px; font-weight:600;
  color:#1C2B40; text-decoration:none; flex:1;
  line-height:1.5; transition:color .15s;
}
.ex-link-row:hover a { color:#0A2342; }
.ex-link-off { opacity:.38; pointer-events:none; }
.ex-link-off a, .ex-link-off span { color:#94A3B8 !important; font-weight:400 !important; }
.ex-spacer { height:6px; }

/* RTL */
[dir="rtl"] .ex-mod-col { border-right:none; border-left:1px solid #EEF2FA; }
[dir="rtl"] .ex-mod-col:last-child { border-left:none; }
[dir="rtl"] .ex-mod-head::after { right:auto; left:-20px; }

/* ── Status bar ── */
.ex-statusbar {
  background:var(--bg-surface); border-top:1px solid var(--border);
  padding:9px 24px; display:flex; align-items:center;
  justify-content:space-between; flex-shrink:0;
}
.ex-statusbar-l { font-size:12px; color:var(--text-muted); font-weight:500; }
.ex-statusbar-r { font-size:12px; color:var(--text-muted); font-weight:600; text-align:right; }

/* Power strip */
.ex-power-strip {
  background:var(--sidebar-deep); padding:10px 24px;
  display:flex; justify-content:center; gap:18px;
}
.ex-power-strip a { font-size:11px; color:rgba(255,255,255,.28); text-decoration:none; transition:all .15s; }
.ex-power-strip a:hover { color:rgba(255,255,255,.55); }

/* ── Language Switcher Button [ 🌐 EN → AR ] ── */
.ex-lang-btn {
  display:inline-flex; align-items:center; gap:5px;
  padding:6px 11px; border-radius:8px; cursor:pointer;
  background:transparent; border:1.5px solid var(--border);
  color:var(--text-tertiary); font-family:var(--font);
  transition:all .16s var(--ease); box-shadow:none; white-space:nowrap;
}
.ex-lang-btn:hover { background:var(--primary-light); border-color:var(--primary-border); color:var(--primary); box-shadow:none; transform:none; }
.ex-lang-btn svg { opacity:.6; }
.ex-lang-btn:hover svg { opacity:1; }
.ex-lang-cur { font-size:12px; font-weight:800; letter-spacing:.05em; color:var(--primary); }
.ex-lang-sep { font-size:10px; color:var(--text-muted); opacity:.7; line-height:1; }
.ex-lang-nxt {
  font-size:10.5px; font-weight:700; letter-spacing:.04em;
  color:var(--text-muted); background:var(--bg-surface-2);
  border:1px solid var(--border); border-radius:4px; padding:1px 5px; line-height:1.4;
}
.ex-lang-btn:hover .ex-lang-cur { color:var(--primary); }
.ex-lang-btn:hover .ex-lang-nxt { background:var(--primary-ultra); border-color:var(--primary-border); color:var(--primary); }

/* ════════════════════════════════════════════════════
   GLOBAL SEARCH — trigger button only (overlay is in body)
════════════════════════════════════════════════════ */


@media(max-width:768px){
  .ex-sidebar { position:fixed; left:-260px; height:100%; transition:left .25s; z-index:999; }
  .ex-sidebar.is-open { left:0; }
  .ex-mobile-menu-btn { display:flex; }
  .ex-main { padding:14px 14px 26px; }
  .ex-modules { grid-template-columns:1fr; gap:14px; }
  .ex-hdr-btn span { display:none; }
  .ex-gs-trigger { min-width:40px; max-width:40px; padding:7px; border-radius:50%; }
  .ex-gs-trig-lbl, .ex-gs-trig-kbd { display:none; }
}
[dir="rtl"] @media(max-width:768px){
  .ex-sidebar { left:auto; right:-260px; transition:right .25s; }
  .ex-sidebar.is-open { right:0; left:auto; }
}
@media(max-width:768px){
  [dir="rtl"] .ex-sidebar { left:auto; right:-260px; transition:right .25s; }
  [dir="rtl"] .ex-sidebar.is-open { right:0; left:auto; }
}

/* ── Mobile backdrop ── */
.ex-mobile-backdrop {
  display:none; position:fixed; inset:0; z-index:998;
  background:rgba(5,15,40,.45); backdrop-filter:blur(4px);
  opacity:0; transition:opacity .25s;
}
@media(max-width:768px){
  .ex-mobile-backdrop.is-visible { display:block; opacity:1; }
}

/* ── Fix: hide FA duplicate #title div — ex-heading already renders the title ── */
#title { display:none !important; }

/* ── Fix: inner page content starts right after topbar — remove top gap ── */
.ex-main > table.tablestyle:first-child,
.ex-main > form:first-child,
.ex-main > div:first-child:not(.ex-page-title) { margin-top:0 !important; }

/* ── Prevent content overflow ── */
.ex-main table { max-width:100%; }
.ex-main form, .ex-main .ex-body-inner { max-width:100%; overflow-x:auto; }
/* NOTE: Do NOT set overflow-x:hidden on body or ex-shell —
   it breaks position:fixed on the search overlay in Chrome/Safari.
   Use clip on ex-body instead to contain horizontal scroll. */
.ex-body { overflow-x:clip; }
.ex-sidebar { overflow-x:clip; }

/* ── Smooth scrollbars ── */
.ex-main { scrollbar-width:thin; scrollbar-color:rgba(27,63,122,.12) transparent; }
.ex-main::-webkit-scrollbar { width:6px; height:6px; }
.ex-main::-webkit-scrollbar-thumb { background:rgba(27,63,122,.12); border-radius:6px; }
.ex-main::-webkit-scrollbar-thumb:hover { background:rgba(27,63,122,.22); }

/* ── FA table overrides — premium look ── */
.ex-main .tablestyle_noborder, .ex-main .tablestyle {
  font-family: var(--font) !important;
  font-size: 13px;
  border-collapse: separate !important;
  border-spacing: 0;
  border-radius: var(--radius-md);
  overflow: hidden;
}
.ex-main .tablestyle td, .ex-main .tablestyle th {
  padding: 8px 12px !important;
  border-color: var(--border-light) !important;
}
.ex-main .tablestyle th, .ex-main .tableheader {
  background: linear-gradient(135deg, #0A2342 0%, #0F3364 100%) !important;
  color: #fff !important;
  font-weight: 700 !important;
  font-size: 11.5px !important;
  text-transform: uppercase;
  letter-spacing: .06em;
}
[dir="rtl"] .ex-main .tablestyle td,
[dir="rtl"] .ex-main .tablestyle th {
  font-family: 'Tajawal','DM Sans',sans-serif !important;
}

/* ── FA form inputs — premium ── */
.ex-main input[type="text"],
.ex-main input[type="password"],
.ex-main input[type="number"],
.ex-main select,
.ex-main textarea {
  font-family: var(--font) !important;
  border: 1.5px solid var(--border) !important;
  border-radius: var(--radius-sm) !important;
  padding: 6px 10px !important;
  transition: border-color .15s, box-shadow .15s;
  outline: none !important;
}
.ex-main input[type="text"]:focus,
.ex-main input[type="password"]:focus,
.ex-main input[type="number"]:focus,
.ex-main select:focus,
.ex-main textarea:focus {
  border-color: var(--primary) !important;
  box-shadow: 0 0 0 3px rgba(22,78,143,.1) !important;
}
[dir="rtl"] .ex-main input,
[dir="rtl"] .ex-main select,
[dir="rtl"] .ex-main textarea {
  font-family: 'Tajawal','DM Sans',sans-serif !important;
}

/* ── FA buttons — premium ── */
.ex-main input[type="submit"],
.ex-main button.ajaxsubmit,
.ex-main .inputsubmit {
  font-family: var(--font) !important;
  background: linear-gradient(135deg, var(--primary) 0%, #1A5EA8 100%) !important;
  color: #fff !important;
  border: none !important;
  border-radius: var(--radius-sm) !important;
  padding: 7px 18px !important;
  font-weight: 600 !important;
  font-size: 12.5px !important;
  cursor: pointer;
  transition: all .16s;
  box-shadow: 0 2px 8px rgba(22,78,143,.22);
  letter-spacing: .01em;
}
.ex-main input[type="submit"]:hover,
.ex-main button.ajaxsubmit:hover,
.ex-main .inputsubmit:hover {
  background: linear-gradient(135deg, #1A5EA8 0%, #2166B5 100%) !important;
  box-shadow: 0 4px 14px rgba(22,78,143,.32);
  transform: translateY(-1px);
}

/* ══════════════════════════════════════════════
   RTL & Arabic Typography — Premium
══════════════════════════════════════════════ */
[dir="rtl"] .ex-shell,
[dir="rtl"] body {
  font-family: var(--font-ar) !important;
}
[dir="rtl"] .ex-sidebar {
  border-right: none;
  border-left: 1.5px solid var(--border);
  box-shadow: -2px 0 12px rgba(22,78,143,.06);
}
[dir="rtl"] .ex-nav-item {
  border-left: none;
  border-right: 3px solid transparent;
  font-family: 'Tajawal','DM Sans',sans-serif !important;
  font-size: 14.5px !important;
  font-weight: 700 !important;
  letter-spacing: 0;
  padding: 13px 18px 13px 20px;
}
[dir="rtl"] .ex-nav-item:hover {
  border-left-color: transparent;
  border-right-color: rgba(212,175,55,0.70);
  padding-right: 22px;
  padding-left: 20px;
}
[dir="rtl"] .ex-nav-item.is-active {
  border-left-color: transparent !important;
  border-right-color: var(--accent) !important;
  padding-right: 22px;
  padding-left: 20px;
}
[dir="rtl"] .ex-nav-label {
  font-family: 'Tajawal','DM Sans',sans-serif !important;
  font-size: 14.5px !important;
  font-weight: 700 !important;
}
[dir="rtl"] .ex-brand-name {
  font-family: 'Tajawal','Playfair Display',sans-serif !important;
}
[dir="rtl"] .ex-topbar,
[dir="rtl"] .ex-main {
  font-family: var(--font-ar) !important;
}
[dir="rtl"] .ex-body {
  margin-left: 0;
}
[dir="rtl"] .ex-collapse-btn svg {
  transform: rotate(180deg);
}
[dir="rtl"] .ex-user-name,
[dir="rtl"] .ex-user-badge {
  font-family: 'Tajawal','DM Sans',sans-serif !important;
}
/* Headings in English mode use Playfair Display */
h1, h2, h3, .ex-gs-group {
  font-family: var(--font-h) !important;
}
[dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3,
[dir="rtl"] .ex-gs-group {
  font-family: 'Tajawal','Playfair Display',sans-serif !important;
}
[dir="rtl"] .ex-heading {
  border-left: none;
  border-right: 4px solid var(--accent);
  padding-left: 0;
  padding-right: 14px;
  font-family: 'Tajawal','Playfair Display',sans-serif !important;
}
[dir="rtl"] .ex-link-row a,
[dir="rtl"] .ex-link-row span {
  font-family: 'Tajawal','DM Sans',sans-serif !important;
}
[dir="rtl"] .ex-mod-title {
  font-family: 'Tajawal','Playfair Display',sans-serif !important;
}
[dir="rtl"] .ex-statusbar-r { text-align:left; }
[dir="rtl"] .ex-statusbar-l { text-align:right; }
[dir="rtl"] .ex-hdr-logout { margin-left:0; margin-right:4px; }
</style>
LAYOUT;
		}

		/* ════════════════════════════════════════
		   LOGIN PAGE — Hero Panel Injector
		════════════════════════════════════════ */
		function inject_login_enhancements()
		{
			global $SysPrefs, $version;
			$ver = isset($version) ? $version : '2.4.19';
			echo "<script>\n";
			echo "(function(){\n";
			echo "  var ls=document.getElementById('loginscreen');\n";
			echo "  if(!ls)return;\n";
			echo "  var hero=document.createElement('div');\n";
			echo "  hero.className='vas-hero';\n";
			echo "  hero.innerHTML=\"<div class='vas-hero-badge'>SYSTEM &nbsp; V{$ver}</div>\"\n";
			echo "    +\"<div class='vas-hero-title' style='margin-top:18px'>نظام<br><span>المحاسبة</span><br>المتكامل</div>\"\n";
			echo "    +\"<div class='vas-hero-sub'>Integrated ERP &middot; General Ledger<br>Inventory &middot; Sales &middot; Purchasing</div>\"\n";
			echo "    +\"<div class='vas-hero-tags'><span class='vas-hero-tag'>VIP SYSTEM</span><span class='vas-hero-tag'>V{$ver}</span></div>\";\n";
			echo "  var card=ls.querySelector('.login');\n";
			echo "  if(card)ls.insertBefore(hero,card);\n";
			echo "  ls.style.flexDirection='row';\n";
			echo "  ls.style.justifyContent='center';\n";
			echo "  ls.style.alignItems='center';\n";
			echo "  var foot=document.createElement('div');\n";
			echo "  foot.className='vas-login-footer';\n";
			echo "  var n=new Date(),pad=function(x){return x<10?'0'+x:x;};\n";
			echo "  var ds=pad(n.getMonth()+1)+'/'+pad(n.getDate())+'/'+n.getFullYear();\n";
			echo "  var h=n.getHours(),ampm=h>=12?'pm':'am';h=h%12||12;\n";
			echo "  var ts=h+':'+pad(n.getMinutes())+' '+ampm;\n";
			echo "  foot.innerHTML='<span>'+ds+' | '+ts+'</span><span>VIP ACCOUNTING SYSTEM {$ver}</span><span>VIP ACCOUNTING SYSTEM</span>';\n";
			echo "  ls.appendChild(foot);\n";
			echo "})();\n";
			echo "</script>\n";
		}
	}