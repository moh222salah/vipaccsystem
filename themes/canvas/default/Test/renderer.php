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
				$company_name = $db_connections[user_company()]["name"] ?? 'FA System';
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
				echo "      <rect width='36' height='36' rx='9' fill='#ffffff'/>\n";
				echo "      <text x='50%' y='56%' dominant-baseline='middle' text-anchor='middle' font-family='Inter,sans-serif' font-size='14' font-weight='800' fill='#0A2342'>FA</text>\n";
				echo "    </svg>\n";
				echo "  </div>\n";
				echo "  <div class='ex-brand-info'>\n";
				echo "    <span class='ex-brand-name'>FA System</span>\n";
				echo "    <span class='ex-brand-co'>" . htmlspecialchars($company_name) . "</span>\n";
				echo "  </div>\n";
				echo "  <button class='ex-collapse-btn' onclick=\"document.getElementById('ex-sidebar').classList.toggle('collapsed');document.getElementById('ex-body').classList.toggle('sidebar-collapsed');\" title='Collapse'>\n";
				echo "    <svg width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><path d='m15 18-6-6 6-6'/></svg>\n";
				echo "  </button>\n";
				echo "</div>\n";

				/* Nav Label */
				echo "<div class='ex-nav-section-label'>MAIN NAVIGATION</div>\n";

				/* Apps Navigation */
				echo "<div class='ex-nav'>\n";
				foreach ($applications as $app)
				{
					if ($_SESSION["wa_current_user"]->check_application_access($app))
					{
						$acc = access_string($app->name);
						$active = ($sel_app == $app->id);
						$icon = $this->_app_svg($app->id);
						echo "<a class='ex-nav-item" . ($active ? " is-active" : "") . "'"
							. " href='$local_path_to_root/index.php?application=" . $app->id . "'"
							. $acc[1] . ">\n";
						echo "  <span class='ex-nav-icon'>$icon</span>\n";
						echo "  <span class='ex-nav-label'>" . $acc[0] . "</span>\n";
						if ($active) echo "  <span class='ex-nav-pip'></span>\n";
						echo "</a>\n";
					}
				}
				echo "</div>\n";

				/* Sidebar Footer */
				echo "<div class='ex-sidebar-bottom'>\n";
				echo "  <div class='ex-user-card'>\n";
				echo "    <div class='ex-user-av'>$user_initial</div>\n";
				echo "    <div class='ex-user-details'>\n";
				echo "      <span class='ex-user-name'>" . htmlspecialchars($user_name) . "</span>\n";
				echo "      <span class='ex-user-badge'>Administrator</span>\n";
				echo "    </div>\n";
				echo "  </div>\n";
				echo "</div>\n";

				echo "</nav>\n";

				/* ════════════════════════════════════
				   BODY — White/Light
				════════════════════════════════════ */
				echo "<div class='ex-body' id='ex-body'>\n";

				/* ─ Top Header Bar ─ */
				echo "<header class='ex-topbar'>\n";

				/* Left zone */
				echo "  <div class='ex-topbar-l'>\n";
				echo "    <button class='ex-mobile-menu-btn' onclick=\"document.getElementById('ex-sidebar').classList.toggle('is-open')\">\n";
				echo "      <svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><line x1='3' y1='6' x2='21' y2='6'/><line x1='3' y1='12' x2='21' y2='12'/><line x1='3' y1='18' x2='21' y2='18'/></svg>\n";
				echo "    </button>\n";
				echo "    <div class='ex-breadcrumb'>" . htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'localhost') . "</div>\n";
				echo "  </div>\n";

				/* Right zone */
				echo "  <div class='ex-topbar-r'>\n";

				/* Ajax loader */
				echo "    <img id='ajaxmark' src='$indicator' style='visibility:hidden;width:20px;height:20px;margin-right:6px;' alt='loading'>\n";

				/* Dashboard */
				echo "    <a class='ex-hdr-btn' href='$path_to_root/admin/dashboard.php?sel_app=$sel_app'>\n";
				echo "      <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='3' width='7' height='7' rx='1.5'/><rect x='14' y='3' width='7' height='7' rx='1.5'/><rect x='3' y='14' width='7' height='7' rx='1.5'/><rect x='14' y='14' width='7' height='7' rx='1.5'/></svg>\n";
				echo "      <span>" . _("Dashboard") . "</span>\n";
				echo "    </a>\n";

				/* Prefs */
				echo "    <a class='ex-hdr-btn' href='$path_to_root/admin/display_prefs.php'>\n";
				echo "      <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='3' width='7' height='7' rx='1.5'/><rect x='14' y='3' width='7' height='7' rx='1.5'/><rect x='3' y='14' width='7' height='7' rx='1.5'/><rect x='14' y='14' width='7' height='7' rx='1.5'/></svg>\n";
				echo "      <span>" . _("Prefs") . "</span>\n";
				echo "    </a>\n";

				/* Help */
				if ($SysPrefs->help_base_url != null) {
					echo "    <a class='ex-hdr-btn' target='_blank' onclick=\"javascript:openWindow(this.href,this.target); return false;\" href='" . help_url() . "'>\n";
					echo "      <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><circle cx='12' cy='12' r='10'/><path d='M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3'/><line x1='12' y1='17' x2='12.01' y2='17'/></svg>\n";
				echo "      <span>" . _("setup") . "</span>\n";
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

			$app_id    = isset($_SESSION['sel_app']) ? (int)$_SESSION['sel_app'] : 0;
			$app_name  = htmlspecialchars($selected_app->name ?? 'Dashboard');
			$app_icon  = $this->_app_svg($app_id);
			$app_color = $this->_app_color($app_id);
			$user_name = isset($_SESSION["wa_current_user"]->name) ? htmlspecialchars($_SESSION["wa_current_user"]->name) : 'User';

			/* ═══════════════════════════════════════
			   DASHBOARD HERO HEADER
			═══════════════════════════════════════ */
			echo "<div class='exd-hero'>\n";
			echo "  <div class='exd-hero-l'>\n";
			echo "    <div class='exd-hero-icon' style='background:linear-gradient(135deg,{$app_color}22 0%,{$app_color}11 100%);border:1.5px solid {$app_color}33;'>\n";
			echo "      <span style='color:{$app_color}'>{$app_icon}</span>\n";
			echo "    </div>\n";
			echo "    <div>\n";
			echo "      <h1 class='exd-hero-title'>{$app_name}</h1>\n";
			echo "      <p class='exd-hero-sub'>" . _("Welcome") . " {$user_name} &mdash; " . _("Manage all operations from one place") . "</p>\n";
			echo "    </div>\n";
			echo "  </div>\n";
			echo "  <div class='exd-hero-r'>\n";
			echo "    <a href='{$path_to_root}/admin/dashboard.php' class='exd-hero-btn'>\n";
			echo "      <svg width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2'><polyline points='1 4 1 10 7 10'/><path d='M3.51 15a9 9 0 1 0 .49-5'/></svg>\n";
			echo "      " . _("Refresh") . "\n";
			echo "    </a>\n";
			echo "  </div>\n";
			echo "</div>\n";

			/* ═══════════════════════════════════════
			   MODULE CARDS GRID
			═══════════════════════════════════════ */
			echo "<div class='exd-grid'>\n";

			foreach ($selected_app->modules as $module)
			{
				if (!$_SESSION["wa_current_user"]->check_module_access($module)) continue;

				$mod_name  = htmlspecialchars($module->name);
				$mod_icon  = $this->_module_svg($module->name);
				$mod_color = $this->_module_color($module->name);

				/* count accessible links */
				$all_fns  = array_merge($module->lappfunctions ?? [], $module->rappfunctions ?? []);
				$fn_count = 0;
				foreach ($all_fns as $fn) { if ($fn->label != '' && $_SESSION["wa_current_user"]->can_access_page($fn->access)) $fn_count++; }

				echo "<div class='exd-card'>\n";

				/* ── Card Header ── */
				echo "  <div class='exd-card-head' style='--mc:{$mod_color}'>\n";
				echo "    <div class='exd-card-icon'>{$mod_icon}</div>\n";
				echo "    <div class='exd-card-meta'>\n";
				echo "      <span class='exd-card-title'>{$mod_name}</span>\n";
				echo "      <span class='exd-card-count'>{$fn_count} " . _("operations") . "</span>\n";
				echo "    </div>\n";
				echo "    <div class='exd-card-accent'></div>\n";
				echo "  </div>\n";

				/* ── Card Body ── */
				echo "  <div class='exd-card-body'>\n";

				/* Left column */
				if (!empty($module->lappfunctions)) {
					echo "    <div class='exd-col'>\n";
					foreach ($module->lappfunctions as $fn) {
						if ($fn->label == '') {
							echo "      <div class='exd-spacer'></div>\n";
						} elseif ($_SESSION["wa_current_user"]->can_access_page($fn->access)) {
							$fi = $this->_fn_svg($fn->category);
							echo "      <div class='exd-link'>\n";
							echo "        <span class='exd-link-ico'>{$fi}</span>\n";
							echo "        " . menu_link($fn->link, $fn->label) . "\n";
							echo "      </div>\n";
						} elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items()) {
							$fi = $this->_fn_svg($fn->category);
							echo "      <div class='exd-link exd-link--off'>\n";
							echo "        <span class='exd-link-ico'>{$fi}</span>\n";
							echo "        <span class='inactive'>" . access_string($fn->label, true) . "</span>\n";
							echo "      </div>\n";
						}
					}
					echo "    </div>\n";
				}

				/* Right column */
				if (!empty($module->rappfunctions)) {
					$has_r = false;
					foreach ($module->rappfunctions as $fn) { if ($fn->label != '') { $has_r = true; break; } }
					if ($has_r) {
						echo "    <div class='exd-col'>\n";
						foreach ($module->rappfunctions as $fn) {
							if ($fn->label == '') {
								echo "      <div class='exd-spacer'></div>\n";
							} elseif ($_SESSION["wa_current_user"]->can_access_page($fn->access)) {
								$fi = $this->_fn_svg($fn->category);
								echo "      <div class='exd-link'>\n";
								echo "        <span class='exd-link-ico'>{$fi}</span>\n";
								echo "        " . menu_link($fn->link, $fn->label) . "\n";
								echo "      </div>\n";
							} elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items()) {
								$fi = $this->_fn_svg($fn->category);
								echo "      <div class='exd-link exd-link--off'>\n";
								echo "        <span class='exd-link-ico'>{$fi}</span>\n";
								echo "        <span class='inactive'>" . access_string($fn->label, true) . "</span>\n";
								echo "      </div>\n";
							}
						}
						echo "    </div>\n";
					}
				}

				echo "  </div>\n"; /* /exd-card-body */
				echo "</div>\n";   /* /exd-card */
			}

			echo "</div>\n"; /* /exd-grid */
		}

		/* ── App color accent by ID ── */
		private function _app_color($id)
		{
			$c = ['#164E8F','#10B981','#F59E0B','#3B82F6','#8B5CF6','#EC4899','#14B8A6','#6366F1'];
			return $c[$id % count($c)];
		}

		/* ── Module SVG icon by name keywords ── */
		private function _module_svg($name)
		{
			$n = strtolower($name);
			if (str_contains($n,'transaction') || str_contains($n,'payment'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="7" y1="15" x2="10" y2="15"/></svg>';
			if (str_contains($n,'report') || str_contains($n,'inquir') || str_contains($n,'analys'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
			if (str_contains($n,'customer') || str_contains($n,'client'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
			if (str_contains($n,'supplier') || str_contains($n,'vendor'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
			if (str_contains($n,'invoice') || str_contains($n,'order') || str_contains($n,'sale'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
			if (str_contains($n,'stock') || str_contains($n,'inventor') || str_contains($n,'item') || str_contains($n,'product'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>';
			if (str_contains($n,'account') || str_contains($n,'ledger') || str_contains($n,'journal'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>';
			if (str_contains($n,'bank') || str_contains($n,'cash'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>';
			if (str_contains($n,'asset') || str_contains($n,'fixed'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
			if (str_contains($n,'manufactur') || str_contains($n,'bom') || str_contains($n,'work'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>';
			if (str_contains($n,'mainten') || str_contains($n,'setup') || str_contains($n,'setting') || str_contains($n,'config'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>';
			if (str_contains($n,'tax') || str_contains($n,'vat'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 14l6-6"/><circle cx="9" cy="9" r="1.2"/><circle cx="15" cy="15" r="1.2"/><circle cx="12" cy="12" r="10"/></svg>';
			if (str_contains($n,'dimension') || str_contains($n,'project') || str_contains($n,'cost'))
				return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/><line x1="12" y1="22" x2="12" y2="15.5"/><polyline points="22 8.5 12 15.5 2 8.5"/></svg>';
			/* Default */
			return '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>';
		}

		/* ── Module accent color by name ── */
		private function _module_color($name)
		{
			$n = strtolower($name);
			if (str_contains($n,'transaction') || str_contains($n,'payment')) return '#3B82F6';
			if (str_contains($n,'report') || str_contains($n,'inquir'))       return '#8B5CF6';
			if (str_contains($n,'customer'))   return '#10B981';
			if (str_contains($n,'supplier'))   return '#F59E0B';
			if (str_contains($n,'invoice') || str_contains($n,'sale')) return '#06B6D4';
			if (str_contains($n,'stock') || str_contains($n,'inventor')) return '#EC4899';
			if (str_contains($n,'account') || str_contains($n,'ledger')) return '#164E8F';
			if (str_contains($n,'bank') || str_contains($n,'cash'))   return '#0EA5E9';
			if (str_contains($n,'asset'))      return '#F97316';
			if (str_contains($n,'manufactur')) return '#14B8A6';
			if (str_contains($n,'mainten') || str_contains($n,'setup')) return '#6366F1';
			if (str_contains($n,'dimension'))  return '#A855F7';
			return '#D4AF37';
		}

		/* ── Function-level icon by category ── */
		private function _fn_svg($category)
		{
			$c = strtolower(trim($category ?? ''));
			if (str_contains($c,'sale') || str_contains($c,'revenue'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>';
			if (str_contains($c,'purch') || str_contains($c,'buy'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>';
			if (str_contains($c,'stock') || str_contains($c,'invent'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>';
			if (str_contains($c,'gl') || str_contains($c,'ledger') || str_contains($c,'journal'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>';
			if (str_contains($c,'bank') || str_contains($c,'cash') || str_contains($c,'payment'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-4 0v2"/></svg>';
			if (str_contains($c,'report') || str_contains($c,'inquiry'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
			if (str_contains($c,'manufactur') || str_contains($c,'bom'))
				return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/></svg>';
			/* default chevron */
			return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>';
		}

		/* ════════════════════════════════════════
		   LANGUAGE SWITCHER IN HEADER
		════════════════════════════════════════ */
		private function _render_lang_switcher($path_to_root)
		{
			/*
			 * الكودات الصحيحة من installed_languages.inc:
			 *   English (IN) = 'en_IN'
			 *   Arabic (EG)  = 'ar_EG'
			 *
			 * الزر يعرض اللغة المقابلة (العكس):
			 *   الحالي EN  → زر يعرض: عربي  → target: ar_EG
			 *   الحالي AR  → زر يعرض: EN    → target: en_IN
			 *
			 * النص العربي مكتوب بـ hex bytes لضمان UTF-8 صحيح
			 * "\xd8\xb9\xd8\xb1\xd8\xa8\xd9\x8a" = عربي
			 */
			$cur_code  = isset($_SESSION['language']) ? $_SESSION['language']->code : 'en_IN';
			$is_arabic = ($cur_code === 'ar_EG');

			$target_code  = $is_arabic ? 'en_IN' : 'ar_EG';
			$target_label = $is_arabic ? 'EN' : "\xd8\xb9\xd8\xb1\xd8\xa8\xd9\x8a";

			$action_url = $path_to_root . '/admin/display_prefs.php';

			echo "    <!-- Language Toggle -->\n";
			echo "    <form method='post' action='" . htmlspecialchars($action_url) . "' style='margin:0;display:inline-flex'>\n";
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
			echo "      <button type='submit' class='ex-lang-btn' title='Switch Language'>\n";
			echo "        <svg width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><circle cx='12' cy='12' r='10'/><line x1='2' y1='12' x2='22' y2='12'/><path d='M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z'/></svg>\n";
			echo "        <span class='ex-lang-label'>" . $target_label . "</span>\n";
			echo "      </button>\n";
			echo "    </form>\n";
		}

		/* ════════════════════════════════════════
		   SVG ICONS PER APP
		════════════════════════════════════════ */
		private function _app_svg($app_id)
		{
			$icons = [
				0 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
				1 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M3 21v-2a7 7 0 0 1 14 0v2"/></svg>',
				2 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
				3 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
				4 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
				5 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
				6 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/><line x1="12" y1="22" x2="12" y2="15.5"/><polyline points="22 8.5 12 15.5 2 8.5"/></svg>',
				7 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>',
			];
			return $icons[$app_id] ?? $icons[0];
		}

		/* ════════════════════════════════════════
		   LAYOUT CSS + JS
		════════════════════════════════════════ */
		private function _inject_layout_styles()
		{
			echo <<<'LAYOUT'
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap');

:root {
  --sidebar-main:   #275691;
  --sidebar-deep:   #275691;
  --sidebar-hover:  #275691;
  --sidebar-active: #275691;
  --sidebar-border: rgba(255,255,255,0.06);
  --sidebar-text:   rgba(255,255,255,0.60);
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
  --font:           'DM Sans','Inter',-apple-system,sans-serif;
  --font-h:         'Inter','DM Sans',sans-serif;
  --sidebar-w:      230px;
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
  display:flex; flex-direction:column;
  position:sticky; top:0; height:100vh;
  overflow-y:auto; overflow-x:hidden; z-index:100;
  transition:width .25s var(--ease),min-width .25s var(--ease);
  scrollbar-width:thin; scrollbar-color:rgba(255,255,255,.08) transparent;
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
.ex-sidebar.collapsed .ex-collapse-btn { display:none; }
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
  color:#FFF; font-family:var(--font-h); font-weight:700; font-size:14px;
  line-height:1.2; letter-spacing:-.01em; white-space:nowrap;
}
.ex-brand-co {
  color:rgba(255,255,255,.38); font-size:10.5px; white-space:nowrap;
  overflow:hidden; text-overflow:ellipsis; margin-top:1px;
}
.ex-collapse-btn {
  background:rgba(255,255,255,.06); border:none; color:rgba(255,255,255,.45);
  cursor:pointer; padding:5px; border-radius:6px; width:24px; height:24px;
  display:flex; align-items:center; justify-content:center;
  transition:all .18s var(--ease); flex-shrink:0; box-shadow:none; transform:none;
}
.ex-collapse-btn:hover { background:rgba(255,255,255,.12); color:#FFF; transform:none; box-shadow:none; }

/* Nav section label */
.ex-nav-section-label {
  font-size:9px; font-weight:700; color:rgba(255,255,255,.22);
  text-transform:uppercase; letter-spacing:.14em;
  padding:14px 16px 6px; flex-shrink:0;
}

/* Nav */
.ex-nav { flex:1; padding:4px 0; }
.ex-nav-item {
  display:flex; align-items:center; gap:10px; padding:10px 16px;
  color:var(--sidebar-text); text-decoration:none;
  font-family:var(--font); font-size:13px; font-weight:500;
  border-left:3px solid transparent; position:relative;
  cursor:pointer; white-space:nowrap;
  transition:all .16s var(--ease);
}
.ex-nav-item:hover {
  background:var(--sidebar-hover); color:#FFF;
  text-decoration:none; border-left-color:rgba(212,175,55,.35);
}
.ex-nav-item.is-active {
  background:rgba(22,78,143,.32); color:#FFF;
  font-weight:600; border-left-color:var(--accent);
}
.ex-nav-icon {
  flex-shrink:0; opacity:.65; display:flex; align-items:center; width:18px;
}
.ex-nav-item:hover .ex-nav-icon, .ex-nav-item.is-active .ex-nav-icon { opacity:1; }
.ex-nav-label { flex:1; }
.ex-nav-pip {
  width:6px; height:6px; border-radius:50%;
  background:var(--accent); flex-shrink:0;
}

/* Sidebar bottom */
.ex-sidebar-bottom {
  border-top:1px solid var(--sidebar-border); padding:12px; flex-shrink:0;
}
.ex-user-card {
  display:flex; align-items:center; gap:10px; padding:8px 10px;
  background:rgba(255,255,255,.04);
  border-radius:10px; border:1px solid var(--sidebar-border);
}
.ex-user-av {
  width:32px; height:32px; border-radius:50%;
  background:linear-gradient(135deg,var(--primary) 0%,var(--accent) 100%);
  color:#FFF; font-weight:700; font-size:13px; font-family:var(--font-h);
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ex-user-details { display:flex; flex-direction:column; overflow:hidden; }
.ex-user-name { color:rgba(255,255,255,.88); font-size:12.5px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ex-user-badge { color:rgba(255,255,255,.32); font-size:10.5px; }

/* ── Body ── */
.ex-body {
  flex:1; display:flex; flex-direction:column; min-width:0; min-height:100vh;
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
.ex-main { flex:1; padding:22px 26px 36px; overflow-x:auto; }

.ex-page-title {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:22px; padding-bottom:18px; border-bottom:1px solid var(--border);
}
.ex-heading {
  font-family:var(--font-h); font-size:22px; font-weight:800;
  color:var(--text-primary); margin:0; letter-spacing:-.03em;
  border-left:4px solid var(--accent); padding-left:14px;
}

/* ── Dashboard Hero ── */
.exd-hero {
  display:flex; align-items:center; justify-content:space-between;
  background:var(--bg-surface); border:1.5px solid var(--border);
  border-radius:var(--radius-lg); padding:20px 24px; margin-bottom:22px;
  box-shadow:var(--shadow-card);
}
.exd-hero-l { display:flex; align-items:center; gap:16px; }
.exd-hero-icon {
  width:48px; height:48px; border-radius:12px;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.exd-hero-icon svg { width:22px; height:22px; }
.exd-hero-title {
  font-family:var(--font-h); font-size:22px; font-weight:800;
  color:var(--text-primary); margin:0 0 3px; letter-spacing:-.03em;
}
.exd-hero-sub { font-size:12.5px; color:var(--text-muted); margin:0; }
.exd-hero-r { display:flex; gap:8px; }
.exd-hero-btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:9px 18px; border-radius:9px;
  background:var(--primary); color:#FFF;
  font-size:12.5px; font-weight:600; text-decoration:none;
  transition:all .16s var(--ease);
  box-shadow:0 4px 12px rgba(22,78,143,.28);
}
.exd-hero-btn:hover { background:var(--primary-hover); transform:translateY(-1px); text-decoration:none; color:#FFF; }

/* ── Module Grid ── */
.exd-grid {
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(360px,1fr));
  gap:18px;
}

/* ── Module Card ── */
.exd-card {
  background:var(--bg-surface); border-radius:16px;
  border:1.5px solid var(--border); box-shadow:var(--shadow-card);
  overflow:hidden; transition:all .2s var(--ease);
  display:flex; flex-direction:column;
}
.exd-card:hover {
  box-shadow:0 6px 24px rgba(10,35,66,.12),0 12px 40px rgba(10,35,66,.08);
  border-color:rgba(22,78,143,.22); transform:translateY(-3px);
}

/* Card Header */
.exd-card-head {
  display:flex; align-items:center; gap:12px;
  padding:14px 18px 13px; position:relative; overflow:hidden;
  background:linear-gradient(90deg, color-mix(in srgb,var(--mc,#164E8F) 8%,transparent) 0%, transparent 100%);
  border-bottom:1.5px solid var(--border);
}
.exd-card-head::before {
  content:''; position:absolute; left:0; top:0; bottom:0;
  width:3.5px; background:var(--mc,#164E8F); border-radius:0 3px 3px 0;
}
.exd-card-icon {
  width:36px; height:36px; border-radius:9px; flex-shrink:0;
  background:color-mix(in srgb,var(--mc,#164E8F) 12%,transparent);
  border:1px solid color-mix(in srgb,var(--mc,#164E8F) 20%,transparent);
  display:flex; align-items:center; justify-content:center;
  color:var(--mc,#164E8F);
}
.exd-card-meta { flex:1; min-width:0; }
.exd-card-title {
  display:block; font-family:var(--font-h); font-size:12px; font-weight:700;
  color:var(--text-primary); letter-spacing:.01em; text-transform:uppercase;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.exd-card-count {
  display:block; font-size:10.5px; color:var(--text-muted); font-weight:500;
  margin-top:1px;
}
.exd-card-accent {
  width:6px; height:6px; border-radius:50%;
  background:var(--mc,#164E8F); flex-shrink:0; opacity:.55;
}

/* Card Body */
.exd-card-body { display:flex; flex:1; padding:10px 0 6px; }
.exd-col {
  flex:1; padding:4px 16px 8px;
  border-right:1px solid var(--border-light);
}
.exd-col:last-child { border-right:none; }

/* Links */
.exd-link {
  display:flex; align-items:center; gap:7px;
  padding:5px 8px; border-radius:8px; margin-bottom:1px;
  transition:all .13s var(--ease); cursor:pointer;
}
.exd-link:hover { background:var(--primary-ultra); }
.exd-link a {
  font-family:var(--font); font-size:12.5px; font-weight:400;
  color:var(--text-secondary); text-decoration:none; flex:1;
  transition:color .13s; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.exd-link:hover a { color:var(--primary); font-weight:500; }
.exd-link-ico {
  flex-shrink:0; color:var(--text-muted); display:flex; align-items:center;
  opacity:.6; width:14px;
}
.exd-link:hover .exd-link-ico { opacity:1; color:var(--primary); }
.exd-link--off a, .exd-link--off span { color:#B0BEC5 !important; }
.exd-link--off .exd-link-ico { opacity:.3; }
.exd-spacer { height:8px; }

/* ── Old classes kept for compat ── */
.ex-modules { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:20px; padding:2px 0; }
.ex-mod-card { background:var(--bg-surface); border-radius:var(--radius-lg); border:1.5px solid var(--border); box-shadow:var(--shadow-card); overflow:hidden; transition:all .18s var(--ease); }
.ex-mod-head { background:linear-gradient(90deg,var(--primary-ultra) 0%,var(--bg-surface-2) 100%); border-bottom:1.5px solid var(--border); padding:11px 18px; }
.ex-mod-title { font-family:var(--font-h); font-size:10.5px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.10em; display:flex; align-items:center; gap:7px; }
.ex-mod-title::before { content:''; display:block; width:3px; height:13px; background:var(--accent); border-radius:2px; }
.ex-mod-body { display:flex; padding:14px 0; }
.ex-mod-col { flex:1; padding:0 18px; border-right:1px solid var(--border-light); }
.ex-mod-col:last-child { border-right:none; }
.ex-link-row { display:flex; align-items:center; gap:4px; padding:4px 7px; border-radius:7px; margin-bottom:2px; transition:all .14s var(--ease); }
.ex-link-row:hover { background:var(--primary-ultra); }
.ex-link-row a { font-family:var(--font); font-size:13px; font-weight:400; color:var(--text-secondary); text-decoration:none; flex:1; transition:all .14s; }
.ex-link-row:hover a { color:var(--primary); }
.ex-link-off a, .ex-link-off span { color:#B0BEC5 !important; }
.ex-spacer { height:9px; }

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

/* ── Language Switcher ── */
.ex-lang-wrap { position:relative; }
.ex-lang-btn {
  display:inline-flex; align-items:center; gap:5px;
  padding:6px 10px; border-radius:8px; cursor:pointer;
  background:transparent; border:1.5px solid var(--border);
  color:var(--text-tertiary); font-size:12px; font-weight:600;
  font-family:var(--font); transition:all .16s var(--ease);
  box-shadow:none; white-space:nowrap;
}
.ex-lang-btn:hover { background:var(--primary-light); border-color:var(--primary-border); color:var(--primary); box-shadow:none; transform:none; }
.ex-lang-btn svg { opacity:.6; flex-shrink:0; }
.ex-lang-btn:hover svg { opacity:1; }
.ex-lang-label { font-size:11.5px; font-weight:700; letter-spacing:.02em; }
.ex-lang-caret { transition:transform .2s; }
.ex-lang-drop.is-open ~ * .ex-lang-caret,
.ex-lang-wrap:has(.is-open) .ex-lang-caret { transform:rotate(180deg); }
.ex-lang-drop {
  display:none; position:absolute; top:calc(100% + 6px); right:0;
  background:var(--bg-surface); border:1.5px solid var(--border);
  border-radius:var(--radius-md); box-shadow:var(--shadow-xl);
  min-width:170px; z-index:999; padding:6px; overflow:hidden;
}
.ex-lang-drop.is-open { display:block; }
.ex-lang-drop-title {
  font-size:10px; font-weight:700; color:var(--text-muted);
  text-transform:uppercase; letter-spacing:.12em;
  padding:6px 10px 4px;
}
.ex-lang-opt {
  display:flex; align-items:center; gap:8px; width:100%;
  padding:8px 10px; border-radius:7px; cursor:pointer;
  background:transparent; border:none; text-align:left;
  font-family:var(--font); transition:background .14s; box-shadow:none;
  transform:none;
}
.ex-lang-opt:hover { background:var(--primary-ultra); box-shadow:none; transform:none; }
.ex-lang-opt.is-active { background:var(--primary-light); }
.ex-lang-opt-code {
  font-size:11px; font-weight:700; color:var(--primary);
  background:var(--primary-ultra); border:1px solid var(--primary-border);
  border-radius:5px; padding:1px 5px; min-width:24px; text-align:center;
  flex-shrink:0;
}
.ex-lang-opt-name { font-size:12.5px; font-weight:500; color:var(--text-secondary); flex:1; }
.ex-lang-opt.is-active .ex-lang-opt-name { color:var(--primary); font-weight:600; }

/* ── Responsive ── */
@media(max-width:768px){
  .ex-sidebar { position:fixed; left:-230px; height:100%; transition:left .25s; z-index:999; }
  .ex-sidebar.is-open { left:0; }
  .ex-mobile-menu-btn { display:flex; }
  .ex-main { padding:14px 14px 26px; }
  .ex-modules { grid-template-columns:1fr; gap:14px; }
  .exd-grid  { grid-template-columns:1fr; gap:14px; }
  .ex-hdr-btn span { display:none; }
  .exd-hero { flex-direction:column; gap:14px; align-items:flex-start; }
  .exd-hero-r { width:100%; }
  .exd-hero-btn { width:100%; justify-content:center; }
}
@media(max-width:1100px){
  .exd-grid { grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); }
}
</style>
LAYOUT;
		}
	}
