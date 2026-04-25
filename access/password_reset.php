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
	if (!isset($path_to_root) || isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
		die(_("Restricted access"));
	include_once($path_to_root . "/includes/ui.inc");
	include_once($path_to_root . "/includes/page/header.inc");

	$js = "<script language='JavaScript' type='text/javascript'>
function defaultCompany()
{
	document.forms[0].company_login_name.options[".user_company()."].selected = true;
}
</script>";
	add_js_file('login.js');

	if (!isset($def_coy))
		$def_coy = 0;
	$def_theme = "default";

	$login_timeout = $_SESSION["wa_current_user"]->last_act;

	$title = $SysPrefs->app_title." ".$version." - "._("Password reset");
	$encoding = isset($_SESSION['language']->encoding) ? $_SESSION['language']->encoding : "iso-8859-1";
	$rtl = isset($_SESSION['language']->dir) ? $_SESSION['language']->dir : "ltr";
	$onload = !$login_timeout ? "onload='defaultCompany()'" : "";

	echo "<!DOCTYPE html>\n";
	echo "<html dir='$rtl' lang='ar'>\n";
	echo "<head>\n";
	echo "<meta charset='$encoding'>\n";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
	echo "<title>$title</title>\n";
	echo "<link href='$path_to_root/themes/$def_theme/default.css' rel='stylesheet' type='text/css'>\n";
	echo "<link href='$path_to_root/themes/default/images/favicon.ico' rel='icon' type='image/x-icon'>\n";
	echo "<link href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap' rel='stylesheet'>\n";
	send_scripts();
	if (!$login_timeout) echo $js;
?>
<style>
/* ═══════════════════════════════════════════════
   VIP ACCOUNTING SYSTEM — PASSWORD RESET PAGE
   Aesthetic: Luxury Dark · Royal Navy × Gold
═══════════════════════════════════════════════ */
*, *::before, *::after { box-sizing:border-box;margin:0;padding:0; }

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
  --white-low:   rgba(255,255,255,0.38);
  --white-ghost: rgba(255,255,255,0.06);
  --err:         #FF6B6B;
  --radius:      14px;
  --font-h:      'Playfair Display', serif;
  --font-b:      'DM Sans', sans-serif;
}

html, body { height:100%;background:var(--navy-deep);font-family:var(--font-b);overflow:hidden; }

.vip-bg {
  position:fixed;inset:0;z-index:0;
  background:
    radial-gradient(ellipse 80% 60% at 15% 10%,rgba(212,175,55,0.08) 0%,transparent 60%),
    radial-gradient(ellipse 60% 80% at 85% 90%,rgba(22,78,143,0.25) 0%,transparent 60%),
    radial-gradient(ellipse 100% 100% at 50% 50%,#0A1930 0%,#050E1F 100%);
}
.orb { position:fixed;border-radius:50%;filter:blur(80px);animation:float 12s ease-in-out infinite;pointer-events:none;z-index:0; }
.orb-1 { width:500px;height:500px;background:radial-gradient(circle,rgba(212,175,55,0.07),transparent 70%);top:-150px;left:-100px;animation-delay:0s; }
.orb-2 { width:400px;height:400px;background:radial-gradient(circle,rgba(22,78,143,0.12),transparent 70%);bottom:-100px;right:-80px;animation-delay:-6s; }
.orb-3 { width:300px;height:300px;background:radial-gradient(circle,rgba(212,175,55,0.05),transparent 70%);top:50%;right:20%;animation-delay:-3s; }
@keyframes float {
  0%,100% { transform:translate(0,0) scale(1); }
  33%      { transform:translate(30px,-20px) scale(1.05); }
  66%      { transform:translate(-20px,15px) scale(0.95); }
}
.vip-grid {
  position:fixed;inset:0;z-index:0;
  background-image:linear-gradient(rgba(212,175,55,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(212,175,55,0.03) 1px,transparent 1px);
  background-size:60px 60px;
}

/* ── Layout ── */
.vip-wrap {
  position:relative;z-index:10;min-height:100vh;
  display:grid;grid-template-columns:1fr 480px 1fr;grid-template-rows:1fr auto 1fr;
  align-items:center;justify-items:center;padding:24px;
}

/* ── Brand Side ── */
.vip-brand-side { grid-column:1;grid-row:2;justify-self:end;padding-right:60px;animation:slideInLeft 0.8s cubic-bezier(.22,1,.36,1) forwards; }
@keyframes slideInLeft { from{opacity:0;transform:translateX(-30px);}to{opacity:1;transform:translateX(0);} }
.vip-brand-tagline { font-family:var(--font-h);font-size:42px;font-weight:700;color:var(--white-hi);line-height:1.15;letter-spacing:-0.02em;max-width:340px; }
.vip-brand-tagline span { color:var(--gold);display:block; }
.vip-brand-sub { margin-top:16px;font-size:14px;color:var(--white-low);font-weight:400;letter-spacing:0.04em;text-transform:uppercase;max-width:280px;line-height:1.7; }
.vip-brand-divider { width:48px;height:2px;background:linear-gradient(90deg,var(--gold),transparent);margin:20px 0; }
.vip-brand-stats { display:flex;gap:28px;margin-top:28px; }
.vip-stat { display:flex;flex-direction:column; }
.vip-stat-num { font-family:var(--font-h);font-size:26px;font-weight:700;color:var(--gold); }
.vip-stat-lbl { font-size:11px;color:var(--white-low);text-transform:uppercase;letter-spacing:.08em;margin-top:2px; }

/* ── Card ── */
.vip-card {
  grid-column:2;grid-row:2;width:100%;
  background:linear-gradient(160deg,rgba(13,32,64,0.95) 0%,rgba(5,14,31,0.98) 100%);
  border:1px solid var(--navy-border);border-radius:20px;padding:44px 40px;
  box-shadow:0 32px 80px rgba(0,0,0,0.6),0 0 0 1px rgba(212,175,55,0.06),inset 0 1px 0 rgba(255,255,255,0.05);
  backdrop-filter:blur(20px);
  animation:cardIn 0.7s cubic-bezier(.22,1,.36,1) 0.1s both;
}
@keyframes cardIn { from{opacity:0;transform:translateY(24px) scale(0.97);}to{opacity:1;transform:translateY(0) scale(1);} }

.vip-card-header { margin-bottom:32px;text-align:center; }
.vip-logo-mark {
  width:56px;height:56px;border-radius:14px;
  background:linear-gradient(135deg,var(--gold),#B8960A);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 16px;box-shadow:0 8px 24px rgba(212,175,55,0.35);
}
.vip-card-title { font-family:var(--font-h);font-size:22px;font-weight:700;color:var(--white-hi);letter-spacing:-0.01em; }
.vip-card-title span { color:var(--gold); }
.vip-card-subtitle { font-size:12px;color:var(--white-low);letter-spacing:.08em;text-transform:uppercase;margin-top:6px; }

/* ── Fields ── */
.vip-field { margin-bottom:20px;animation:fieldIn 0.5s cubic-bezier(.22,1,.36,1) both; }
@keyframes fieldIn { from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);} }
.vip-field:nth-child(1){animation-delay:0.20s}
.vip-field:nth-child(2){animation-delay:0.28s}
.vip-field:nth-child(3){animation-delay:0.36s}
.vip-label { display:block;font-size:11px;font-weight:600;color:var(--white-low);letter-spacing:.1em;text-transform:uppercase;margin-bottom:8px; }
.vip-input-wrap { position:relative;display:flex;align-items:center; }
.vip-input-icon { position:absolute;left:14px;color:var(--gold);opacity:.7;display:flex;pointer-events:none; }
.vip-input, .vip-select {
  width:100%;padding:13px 16px 13px 42px;
  background:rgba(255,255,255,0.04);
  border:1px solid rgba(212,175,55,0.15);
  border-radius:10px;color:var(--white-hi);
  font-family:var(--font-b);font-size:14px;
  transition:all .2s ease;outline:none;
  -webkit-appearance:none;
}
.vip-input::placeholder { color:var(--white-low); }
.vip-input:focus, .vip-select:focus {
  border-color:var(--gold);
  background:rgba(212,175,55,0.06);
  box-shadow:0 0 0 3px var(--gold-glow),0 4px 12px rgba(0,0,0,0.2);
}
.vip-select { cursor:pointer;color:var(--white-mid); }
.vip-select option { background:#0D2040;color:#fff; }

/* ── Message ── */
.vip-msg { font-size:12px;color:var(--white-low);text-align:center;margin:4px 0 16px;min-height:18px;animation:fieldIn .5s .4s both; }
.vip-msg .err-msg { color:var(--err); }

/* ── Submit ── */
.vip-btn {
  width:100%;padding:15px 24px;
  background:linear-gradient(135deg,var(--gold) 0%,#B8960A 100%);
  border:none;border-radius:10px;color:#0A1930;
  font-family:var(--font-b);font-size:14px;font-weight:700;
  letter-spacing:.06em;text-transform:uppercase;cursor:pointer;
  position:relative;overflow:hidden;transition:all .22s ease;
  box-shadow:0 4px 20px rgba(212,175,55,0.30);margin-top:8px;
  animation:fieldIn 0.5s cubic-bezier(.22,1,.36,1) 0.46s both;
}
.vip-btn::before { content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.15),transparent);opacity:0;transition:opacity .2s; }
.vip-btn:hover { transform:translateY(-2px);box-shadow:0 8px 28px rgba(212,175,55,0.45); }
.vip-btn:hover::before { opacity:1; }
.vip-btn:active { transform:translateY(0); }

/* ── Footer ── */
.vip-footer {
  grid-column:1/-1;grid-row:3;align-self:end;padding-bottom:20px;
  display:flex;align-items:center;justify-content:center;gap:24px;
  animation:fadeUp 0.6s ease 0.8s both;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);} }
.vip-footer a { font-size:11px;color:var(--white-low);text-decoration:none;letter-spacing:.06em;text-transform:uppercase;transition:color .2s; }
.vip-footer a:hover { color:var(--gold); }
.vip-footer-sep { width:3px;height:3px;border-radius:50%;background:var(--white-ghost); }
.vip-date { font-size:11px;color:var(--white-low);letter-spacing:.04em; }

/* ── Hide original FA elements ── */
table.login,table.titletext,table.bottomBar,table.footer,#_page_body { display:none !important; }

@media(max-width:960px){
  .vip-wrap{grid-template-columns:1fr;grid-template-rows:auto;}
  .vip-brand-side{display:none;}
  .vip-card{grid-column:1;}
  .vip-footer{grid-column:1;}
}
</style>
<?php
	echo "</head>\n";
	echo "<body id='loginscreen' $onload>\n";

	// ── VIP PASSWORD RESET UI ──
	echo "<div class='vip-bg'></div>\n";
	echo "<div class='vip-grid'></div>\n";
	echo "<div class='orb orb-1'></div>\n";
	echo "<div class='orb orb-2'></div>\n";
	echo "<div class='orb orb-3'></div>\n";

	echo "<div class='vip-wrap'>\n";

	// ── Brand Side ──
	echo "<div class='vip-brand-side'>\n";
	echo "  <div class='vip-brand-tagline'>نظام<span>المحاسبة</span>المتكامل</div>\n";
	echo "  <div class='vip-brand-divider'></div>\n";
	echo "  <div class='vip-brand-sub'>Integrated ERP · General Ledger<br>Inventory · Sales · Purchasing</div>\n";
	echo "  <div class='vip-brand-stats'>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>VIP</span><span class='vip-stat-lbl'>System</span></div>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>v".$version."</span><span class='vip-stat-lbl'>Version</span></div>\n";
	echo "  </div>\n";
	echo "</div>\n";

	// ── Card ──
	echo "<div class='vip-card'>\n";
	echo "  <div class='vip-card-header'>\n";
	echo "    <div class='vip-logo-mark'>\n";
	echo "      <svg width='28' height='28' viewBox='0 0 24 24' fill='none'>\n";
	echo "        <path d='M12 2L2 7l10 5 10-5-10-5z' fill='#0A1930' stroke='#0A1930' stroke-width='.5'/>\n";
	echo "        <path d='M2 17l10 5 10-5' stroke='#0A1930' stroke-width='2' stroke-linecap='round'/>\n";
	echo "        <path d='M2 12l10 5 10-5' stroke='#0A1930' stroke-width='2' stroke-linecap='round'/>\n";
	echo "      </svg>\n";
	echo "    </div>\n";
	echo "  <div class='vip-card-title'>Vip <span>Accounting</span> System</div>\n";
	echo "  <div class='vip-card-subtitle'>Build ".$SysPrefs->build_version." &nbsp;·&nbsp; "._("Password reset")."</div>\n";
	echo "  </div>\n"; // /card-header

	// ── Form ──
	echo "<form method='post' action='".@$_SESSION['timeout']['uri']."' name='resetform' autocomplete='off'>\n";
	echo "<input type='hidden' id='ui_mode' name='ui_mode' value='".fallback_mode()."'>\n";

	// Email field
	echo "<div class='vip-field'>\n";
	echo "  <label class='vip-label' for='email_entry_field'>"._("Email")."</label>\n";
	echo "  <div class='vip-input-wrap'>\n";
	echo "    <span class='vip-input-icon'><svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z'/><polyline points='22,6 12,13 2,6'/></svg></span>\n";
	echo "    <input class='vip-input' type='text' id='email_entry_field' name='email_entry_field' value='' placeholder='"._("Enter your email address")."'>\n";
	echo "  </div>\n";
	echo "</div>\n";

	// Company field
    $coy = user_company();
    if (!isset($coy)) $coy = $def_coy;
	echo "<div class='vip-field'>\n";
	echo "  <label class='vip-label'>"._("Company")."</label>\n";
	echo "  <div class='vip-input-wrap'>\n";
	echo "    <span class='vip-input-icon'><svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/><polyline points='9 22 9 12 15 12 15 22'/></svg></span>\n";
    if (!@$SysPrefs->text_company_selection) {
        echo "    <select class='vip-select' name='company_login_name'>\n";
        for ($i = 0; $i < count($db_connections); $i++)
            echo "      <option value=$i ".($i==$coy ? 'selected':'').">".htmlspecialchars($db_connections[$i]["name"])."</option>\n";
        echo "    </select>\n";
    } else {
        echo "    <input class='vip-input' type='text' name='company_login_nickname' placeholder='"._("Company nickname")."'>\n";
    }
	echo "  </div>\n";
	echo "</div>\n";

	// Message
	echo "<div class='vip-msg' id='log_msg'>"._("Please enter your e-mail")."</div>\n";

	// Submit
	echo "<button type='submit' class='vip-btn' name='SubmitReset' onclick='set_fullmode();'>&#8594;&nbsp;&nbsp;"._("Send password -->")."&nbsp;&nbsp;&#8594;</button>\n";

	echo "</form>\n";
	echo "</div>\n"; // /vip-card

	// ── Footer ──
	echo "<div class='vip-footer'>\n";
	if (isset($_SESSION['wa_current_user']))
		$date = Today() . " | " . Now();
	else
		$date = date("d/m/Y") . " | " . date("h:i a");
	echo "  <span class='vip-date'>$date</span>\n";
	echo "  <span class='vip-footer-sep'></span>\n";
	echo "  <a href='".$SysPrefs->power_url."' target='_blank'>".$SysPrefs->app_title." $version - " . _("Theme:") . " " . $def_theme . "</a>\n";
	echo "  <span class='vip-footer-sep'></span>\n";
	echo "  <a href='".$SysPrefs->power_url."' target='_blank'>".$SysPrefs->power_by."</a>\n";
	echo "</div>\n";

	echo "</div>\n"; // /vip-wrap

    echo "<script language='JavaScript' type='text/javascript'>
    //<![CDATA[
            <!--
            document.forms[0].email_entry_field.select();
            document.forms[0].email_entry_field.focus();
            //-->
    //]]>
    </script>";
	echo "</body></html>\n";
