<?php
/**********************************************************************
    Copyright (C) Vip Accounting System, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
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

	if ($SysPrefs->allow_demo_mode == true)
		$demo_text = "Login as user: demouser and password: password";
	else {
		$demo_text = "Please login here";
		if (@$SysPrefs->allow_password_reset)
			$demo_text .= " or <a href='$path_to_root/index.php?reset=1'>request new password</a>";
	}

	if (check_faillog()) {
		$blocked = true;
		$js .= "<script>setTimeout(function() {
			document.getElementsByName('SubmitUser')[0].disabled=0;
			document.getElementById('log_msg').innerHTML='$demo_text'}, 1000*".$SysPrefs->login_delay.");</script>";
		$demo_text = '<span class="err-msg">Too many failed login attempts.<br>Please wait a while or try later.</span>';
	} elseif ($_SESSION["wa_current_user"]->login_attempt > 1) {
		$demo_text = '<span class="err-msg">Invalid - Please try again</span>';
	}

	flush_dir(user_js_cache());
	if (!isset($def_coy)) $def_coy = 0;
	$def_theme = "default";
	$login_timeout = $_SESSION["wa_current_user"]->last_act;
	$title = 'Login';
	$encoding = 'utf-8';   /* Force UTF-8 — no language mixing */
	$rtl      = 'ltr';     /* Force LTR for login page */
	$onload = !$login_timeout ? "onload='defaultCompany()'" : "";

	echo "<!DOCTYPE html>\n";
	echo "<html dir='$rtl' lang='en'>\n";
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
   VIP ACCOUNTING SYSTEM — LOGIN PAGE
   Aesthetic: Luxury Dark · Royal Navy × Gold
═══════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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

html, body {
  min-height: 100%;
  background: var(--navy-deep);
  font-family: var(--font-b);
  overflow-y: auto;
}

/* ── Animated Background ── */
.vip-bg {
  position: fixed; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 80% 60% at 15% 10%, rgba(212,175,55,0.08) 0%, transparent 60%),
    radial-gradient(ellipse 60% 80% at 85% 90%, rgba(22,78,143,0.25) 0%, transparent 60%),
    radial-gradient(ellipse 100% 100% at 50% 50%, #0A1930 0%, #050E1F 100%);
}

/* Floating orbs */
.orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  animation: float 12s ease-in-out infinite;
  pointer-events: none;
  z-index: 0;
}
.orb-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(212,175,55,0.07), transparent 70%);
  top: -150px; left: -100px;
  animation-delay: 0s;
}
.orb-2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(22,78,143,0.12), transparent 70%);
  bottom: -100px; right: -80px;
  animation-delay: -6s;
}
.orb-3 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(212,175,55,0.05), transparent 70%);
  top: 50%; right: 20%;
  animation-delay: -3s;
}
@keyframes float {
  0%, 100% { transform: translate(0,0) scale(1); }
  33%       { transform: translate(30px,-20px) scale(1.05); }
  66%       { transform: translate(-20px,15px) scale(0.95); }
}

/* Grid overlay */
.vip-grid {
  position: fixed; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(212,175,55,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(212,175,55,0.03) 1px, transparent 1px);
  background-size: 60px 60px;
}

/* ── Main Layout ── */
.vip-wrap {
  position: relative; z-index: 10;
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 440px 1fr;
  grid-template-rows: 1fr auto 1fr;
  align-items: center;
  justify-items: center;
  padding: 20px;
  gap: 0;
}

/* ── Brand Side (Left) ── */
.vip-brand-side {
  grid-column: 1;
  grid-row: 2;
  justify-self: end;
  padding-right: 60px;
  animation: slideInLeft 0.8s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes slideInLeft {
  from { opacity:0; transform: translateX(-30px); }
  to   { opacity:1; transform: translateX(0); }
}
.vip-brand-tagline {
  font-family: var(--font-h);
  font-size: 42px;
  font-weight: 700;
  color: var(--white-hi);
  line-height: 1.15;
  letter-spacing: -0.02em;
  max-width: 340px;
}
.vip-brand-tagline span {
  color: var(--gold);
  display: block;
}
.vip-brand-sub {
  margin-top: 16px;
  font-size: 14px;
  color: var(--white-low);
  font-weight: 400;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  max-width: 280px;
  line-height: 1.7;
}
.vip-brand-divider {
  width: 48px; height: 2px;
  background: linear-gradient(90deg, var(--gold), transparent);
  margin: 20px 0;
}
.vip-brand-stats {
  display: flex; gap: 28px; margin-top: 28px;
}
.vip-stat { display: flex; flex-direction: column; }
.vip-stat-num {
  font-family: var(--font-h);
  font-size: 26px; font-weight: 700;
  color: var(--gold);
}
.vip-stat-lbl {
  font-size: 11px; color: var(--white-low);
  text-transform: uppercase; letter-spacing: .08em;
  margin-top: 2px;
}

/* ── Card ── */
.vip-card {
  grid-column: 2;
  grid-row: 2;
  width: 100%;
  background: linear-gradient(160deg, rgba(13,32,64,0.95) 0%, rgba(5,14,31,0.98) 100%);
  border: 1px solid var(--navy-border);
  border-radius: 20px;
  padding: 32px 36px;
  box-shadow:
    0 0 0 1px rgba(212,175,55,0.08),
    0 32px 80px rgba(0,0,0,0.6),
    0 8px 24px rgba(0,0,0,0.4),
    inset 0 1px 0 rgba(212,175,55,0.12);
  backdrop-filter: blur(20px);
  animation: cardIn 0.7s cubic-bezier(.22,1,.36,1) 0.1s both;
  position: relative;
  overflow: hidden;
}
@keyframes cardIn {
  from { opacity:0; transform: translateY(24px) scale(0.97); }
  to   { opacity:1; transform: translateY(0) scale(1); }
}

/* Card top accent line */
.vip-card::before {
  content: '';
  position: absolute; top: 0; left: 10%; right: 10%;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
}

/* ── Card Header ── */
.vip-card-header {
  text-align: center;
  margin-bottom: 24px;
}
.vip-logo-mark {
  display: inline-flex; align-items: center; justify-content: center;
  width: 52px; height: 52px;
  background: linear-gradient(135deg, var(--gold) 0%, #B8960A 100%);
  border-radius: 14px;
  margin-bottom: 14px;
  box-shadow: 0 8px 24px rgba(212,175,55,0.35);
  position: relative;
}
.vip-logo-mark::after {
  content: '';
  position: absolute; inset: -1px;
  border-radius: 19px;
  background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
  pointer-events: none;
}
.vip-logo-mark svg { filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }

.vip-card-title {
  font-family: var(--font-h);
  font-size: 22px; font-weight: 700;
  color: var(--white-hi);
  letter-spacing: -0.02em;
  line-height: 1.2;
}
.vip-card-title span { color: var(--gold); }
.vip-card-subtitle {
  margin-top: 6px;
  font-size: 11px;
  color: var(--white-low);
  text-transform: uppercase;
  letter-spacing: .12em;
  font-weight: 500;
}

/* ── Form Fields ── */
.vip-field {
  margin-bottom: 14px;
  animation: fieldIn 0.5s cubic-bezier(.22,1,.36,1) both;
}
.vip-field:nth-child(1) { animation-delay: 0.25s; }
.vip-field:nth-child(2) { animation-delay: 0.32s; }
.vip-field:nth-child(3) { animation-delay: 0.39s; }
@keyframes fieldIn {
  from { opacity:0; transform: translateX(-12px); }
  to   { opacity:1; transform: translateX(0); }
}

.vip-label {
  display: block;
  font-size: 10.5px; font-weight: 600;
  color: var(--white-low);
  text-transform: uppercase;
  letter-spacing: .10em;
  margin-bottom: 6px;
}

.vip-input-wrap {
  position: relative; display: flex; align-items: center;
}
.vip-input-icon {
  position: absolute; left: 14px;
  color: var(--gold); opacity: 0.6;
  pointer-events: none; transition: opacity .2s;
  display: flex; align-items: center;
}
.vip-input-wrap:focus-within .vip-input-icon { opacity: 1; }

.vip-input,
.vip-select {
  width: 100%;
  padding: 11px 14px 11px 42px;
  background: rgba(255,255,255,0.04);
  border: 1.5px solid rgba(255,255,255,0.10);
  border-radius: 10px;
  color: var(--white-hi);
  font-family: var(--font-b);
  font-size: 14px; font-weight: 400;
  outline: none;
  transition: all .2s ease;
  -webkit-appearance: none;
}
.vip-input::placeholder { color: var(--white-low); }
.vip-input:focus,
.vip-select:focus {
  border-color: var(--gold);
  background: rgba(212,175,55,0.06);
  box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
}
.vip-select option { background: var(--navy-card); color: var(--white-hi); }

/* ── Message ── */
.vip-msg {
  text-align: center;
  font-size: 12.5px;
  color: var(--white-low);
  margin: 2px 0 12px;
  min-height: 18px;
  transition: all .3s;
}
.err-msg { color: var(--err) !important; font-weight: 500; }

/* ── Submit Button ── */
.vip-btn {
  width: 100%;
  padding: 12px 24px;
  background: linear-gradient(135deg, var(--gold) 0%, #B8960A 100%);
  border: none; border-radius: 10px;
  color: #0A1930;
  font-family: var(--font-b);
  font-size: 14px; font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  cursor: pointer;
  position: relative; overflow: hidden;
  transition: all .22s ease;
  box-shadow: 0 4px 20px rgba(212,175,55,0.30);
  margin-top: 4px;
  animation: fieldIn 0.5s cubic-bezier(.22,1,.36,1) 0.46s both;
}
.vip-btn::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
  opacity: 0; transition: opacity .2s;
}
.vip-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(212,175,55,0.45); }
.vip-btn:hover::before { opacity: 1; }
.vip-btn:active { transform: translateY(0); }
.vip-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ── Footer ── */
.vip-footer {
  grid-column: 1 / -1;
  grid-row: 3;
  align-self: end;
  padding-bottom: 14px;
  display: flex; align-items: center; justify-content: center; gap: 24px;
  animation: fadeUp 0.6s ease 0.8s both;
}
@keyframes fadeUp {
  from { opacity:0; transform: translateY(10px); }
  to   { opacity:1; transform: translateY(0); }
}
.vip-footer a {
  font-size: 11px; color: var(--white-low);
  text-decoration: none; letter-spacing: .06em;
  text-transform: uppercase; transition: color .2s;
}
.vip-footer a:hover { color: var(--gold); }
.vip-footer-sep { width: 3px; height: 3px; border-radius: 50%; background: var(--white-ghost); }
.vip-date { font-size: 11px; color: var(--white-low); letter-spacing: .04em; }

/* ── Timeout message ── */
.vip-timeout {
  text-align: center;
  font-family: var(--font-h);
  font-size: 20px;
  color: var(--gold);
  margin-bottom: 24px;
}

/* ── Hide / neutralize FA framework wrappers ── */
table.login, table.titletext, table.bottomBar,
table.footer, #_page_body { display: none !important; }
.ex-shell, .ex-body, .ex-body.ex-nomenu, main.ex-main {
  display: contents !important;
}
#ajaxmark, .ex-page-title { display: none !important; }
main.ex-main > div:first-child:not(.vip-bg):not(.vip-wrap):not(.vip-grid):not(.orb):not(.vip-dev-sig) {
  display: none !important;
}

/* ── Responsive ── */
@media (max-width: 960px) {
  .vip-wrap { grid-template-columns: 1fr; grid-template-rows: auto; }
  .vip-brand-side { display: none; }
  .vip-card { grid-column: 1; }
  .vip-footer { grid-column: 1; }
  .vip-dev-sig { left: 50% !important; right: auto !important; transform: translateX(-50%); text-align: center; bottom: 12px; }
  .vip-dev-links { justify-content: center; }
}

/* ── Card shimmer on top accent ── */
@keyframes accentPulse {
  0%, 100% { opacity: 0.5; }
  50%       { opacity: 1; }
}
.vip-card::before { animation: accentPulse 4s ease-in-out infinite; }

/* ── Secure Online Badge ── */
.vip-secure-badge {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  margin-top: 12px;
  font-size: 10px; color: rgba(255,255,255,0.28);
  letter-spacing: .08em; text-transform: uppercase;
}
.vip-secure-dot {
  width: 5px; height: 5px; border-radius: 50%;
  background: #10B981; box-shadow: 0 0 7px rgba(16,185,129,.9);
  animation: dotPulse 2s ease-in-out infinite;
}
@keyframes dotPulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.4; transform: scale(0.75); }
}

/* ── Brand feature list ── */
.vip-brand-features { margin-top: 24px; display: flex; flex-direction: column; gap: 8px; }
.vip-brand-feat {
  display: flex; align-items: center; gap: 10px;
  font-size: 12.5px; color: var(--white-low); letter-spacing: .02em;
}
.vip-brand-feat-dot {
  width: 5px; height: 5px; border-radius: 50%;
  background: var(--gold); flex-shrink: 0; opacity: 0.7;
}

/* ══════════════════════════════════════
   DEVELOPER SIGNATURE — Premium Design
══════════════════════════════════════ */
.vip-dev-sig {
  position:fixed; bottom:22px; right:26px; z-index:200;
  min-width:280px;
  backdrop-filter:blur(24px) saturate(180%);
  -webkit-backdrop-filter:blur(24px) saturate(180%);
  background:
    linear-gradient(135deg, rgba(13,32,64,.92) 0%, rgba(5,14,31,.95) 100%);
  border:1px solid rgba(212,175,55,.22);
  border-radius:20px;
  padding:0;
  overflow:hidden;
  box-shadow:
    0 8px 32px rgba(0,0,0,.50),
    0 0 0 1px rgba(212,175,55,.06),
    inset 0 1px 0 rgba(255,255,255,.04);
  animation:sigSlideIn 0.8s cubic-bezier(.22,1,.36,1) 1.2s both;
  transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease;
}
@keyframes sigSlideIn {
  from { opacity:0; transform:translateY(30px) scale(0.95); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}
.vip-dev-sig:hover {
  transform:translateY(-5px);
  border-color:rgba(212,175,55,.45);
  box-shadow:
    0 16px 48px rgba(0,0,0,.55),
    0 0 0 1px rgba(212,175,55,.12),
    0 0 40px rgba(212,175,55,.06);
}
.vip-dev-sig::before {
  content:'';
  display:block;
  height:2px;
  background:linear-gradient(90deg, transparent 5%, var(--gold) 30%, var(--gold-light) 50%, var(--gold) 70%, transparent 95%);
  opacity:.7;
}
.vip-dev-inner { padding:18px 22px 16px; }
.vip-dev-label-row {
  display:flex; align-items:center; gap:7px; margin-bottom:10px;
}
.vip-dev-label-icon {
  width:22px; height:22px; border-radius:6px;
  background:linear-gradient(135deg, rgba(212,175,55,.15), rgba(212,175,55,.05));
  border:1px solid rgba(212,175,55,.18);
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.vip-dev-label {
  font-size:9px; color:rgba(255,255,255,.40);
  letter-spacing:.08em; text-transform:uppercase; line-height:1.3;
}
.vip-dev-label strong {
  color:rgba(255,255,255,.55); font-weight:600;
}
.vip-dev-name {
  font-family:var(--font-h);
  font-size:17px; font-weight:800;
  background:linear-gradient(135deg, var(--gold-light) 0%, var(--gold) 50%, #C4992A 100%);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  background-clip:text;
  letter-spacing:.04em;
  margin-bottom:4px;
  text-align:right;
}
.vip-dev-badge {
  display:inline-flex; align-items:center; gap:5px;
  background:rgba(212,175,55,.08);
  border:1px solid rgba(212,175,55,.14);
  border-radius:20px;
  padding:4px 12px;
  margin-bottom:12px;
  float:right;
}
.vip-dev-badge-dot {
  width:5px; height:5px; border-radius:50%;
  background:var(--gold);
  box-shadow:0 0 6px rgba(212,175,55,.6);
  animation:dotPulse 2.5s ease-in-out infinite;
}
.vip-dev-badge-text {
  font-size:8.5px; color:rgba(255,255,255,.45);
  letter-spacing:.06em; text-transform:uppercase; font-weight:500;
}
.vip-dev-divider {
  clear:both;
  height:1px;
  background:linear-gradient(90deg, transparent, rgba(212,175,55,.18), transparent);
  margin:8px 0 12px;
}
.vip-dev-links {
  display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap;
}
.vip-dev-links a {
  display:inline-flex; align-items:center; gap:5px;
  padding:5px 10px;
  border-radius:8px;
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.06);
  font-size:9.5px; color:rgba(255,255,255,.40);
  text-decoration:none; letter-spacing:.03em;
  font-weight:500;
  transition:all .2s ease;
}
.vip-dev-links a:hover {
  background:rgba(212,175,55,.10);
  border-color:rgba(212,175,55,.25);
  color:var(--gold);
  transform:translateY(-1px);
}
.vip-dev-links a svg { flex-shrink:0; opacity:.6; transition:opacity .2s; }
.vip-dev-links a:hover svg { opacity:1; }
/* RTL: Signature flips to left side */
[dir="rtl"] .vip-dev-sig { right:auto; left:26px; }
[dir="rtl"] .vip-dev-name { text-align:left; }
[dir="rtl"] .vip-dev-badge { float:left; }
[dir="rtl"] .vip-dev-links { justify-content:flex-start; }
</style>
<?php
	echo "</head>\n";
	echo "<body $onload>\n";

	// ── VIP LOGIN UI ──
	echo "<div class='vip-bg'></div>\n";
	echo "<div class='vip-grid'></div>\n";
	echo "<div class='orb orb-1'></div>\n";
	echo "<div class='orb orb-2'></div>\n";
	echo "<div class='orb orb-3'></div>\n";

	echo "<div class='vip-wrap'>\n";

	// ── Brand Side ──
	echo "<div class='vip-brand-side'>\n";
	echo "  <div class='vip-brand-tagline'><span>VIP Accounting</span>System</div>\n";
	echo "  <div class='vip-brand-divider'></div>\n";
	echo "  <div class='vip-brand-features'>\n";
	echo "    <div class='vip-brand-feat'><span class='vip-brand-feat-dot'></span>Multi-company &amp; Multi-currency</div>\n";
	echo "    <div class='vip-brand-feat'><span class='vip-brand-feat-dot'></span>Real-time Financial Reporting</div>\n";
	echo "    <div class='vip-brand-feat'><span class='vip-brand-feat-dot'></span>Advanced Inventory Control</div>\n";
	echo "  </div>\n";
	echo "  <div class='vip-brand-stats'>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>VIP</span><span class='vip-stat-lbl'>System</span></div>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>V".$version."</span><span class='vip-stat-lbl'>Version</span></div>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>ERP</span><span class='vip-stat-lbl'>Platform</span></div>\n";
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
	if ($login_timeout) {
		echo "  <div class='vip-timeout'>Authorization Timeout</div>\n";
	} else {
		echo "  <div class='vip-card-title'>Vip <span>Accounting</span> System</div>\n";
	}
	echo "  </div>\n"; // /card-header

	$allow = SECURE_ONLY !== true ? true : (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_NAME'] === "localhost";
	$value = $login_timeout ? $_SESSION['wa_current_user']->loginname : ($SysPrefs->allow_demo_mode ? "demouser":"");
	$password_val = $SysPrefs->allow_demo_mode ? "password":"";

	// ── Form ──
	echo "<form method='post' action='".$_SESSION['timeout']['uri']."' name='loginform' autocomplete='off'>\n";

	if ($allow) {
		// Username
		echo "<div class='vip-field'>\n";
		echo "  <label class='vip-label' for='user_name_entry_field'>Username</label>\n";
		echo "  <div class='vip-input-wrap'>\n";
		echo "    <span class='vip-input-icon'><svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2'/><circle cx='12' cy='7' r='4'/></svg></span>\n";
		echo "    <input class='vip-input' type='text' id='user_name_entry_field' name='user_name_entry_field' value='".htmlspecialchars($value)."' placeholder='Enter your username' autocomplete='username'>\n";
		echo "  </div>\n";
		echo "</div>\n";

		// Password
		echo "<div class='vip-field'>\n";
		echo "  <label class='vip-label' for='password'>Password</label>\n";
		echo "  <div class='vip-input-wrap'>\n";
		echo "    <span class='vip-input-icon'><svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><rect x='3' y='11' width='18' height='11' rx='2'/><path d='M7 11V7a5 5 0 0 1 10 0v4'/></svg></span>\n";
		echo "    <input class='vip-input' type='password' id='password' name='password' value='".htmlspecialchars($password_val)."' placeholder='Enter your password' autocomplete='current-password'>\n";
		echo "  </div>\n";
		echo "</div>\n";

		// Company
		if (!$login_timeout) {
			$coy = user_company();
			if (!isset($coy)) $coy = $def_coy;
			if (isset($db_connections)) {
				echo "<div class='vip-field'>\n";
				echo "  <label class='vip-label'>Company</label>\n";
				echo "  <div class='vip-input-wrap'>\n";
				echo "    <span class='vip-input-icon'><svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'/><polyline points='9 22 9 12 15 12 15 22'/></svg></span>\n";
				if (!@$SysPrefs->text_company_selection) {
					echo "    <select class='vip-select' name='company_login_name'>\n";
					for ($i = 0; $i < count($db_connections); $i++)
						echo "      <option value=$i ".($i==$coy ? 'selected':'').">".htmlspecialchars($db_connections[$i]["name"])."</option>\n";
					echo "    </select>\n";
				} else {
					echo "    <input class='vip-input' type='text' name='company_login_nickname' placeholder='Company nickname'>\n";
				}
				echo "  </div>\n";
				echo "</div>\n";
			}
		} else {
			echo "<input type='hidden' name='company_login_name' value='".user_company()."'>\n";
		}
	} else {
		$demo_text = '<span class="err-msg">HTTP access is not allowed on this site.</span>';
	}

	// Message
	echo "<div class='vip-msg' id='log_msg'>$demo_text</div>\n";

	// Submit
	if ($allow) {
		echo "<button type='submit' class='vip-btn' name='SubmitUser'"
			." onclick='".(in_ajax() ? 'retry();': 'set_fullmode();')."'"
			.(isset($blocked) ? " disabled" : '').">"
			."&#8594;&nbsp;&nbsp;Login&nbsp;&nbsp;&#8594;</button>\n";
	}

	// Secure badge
	echo "<div class='vip-secure-badge'><span class='vip-secure-dot'></span>256-bit SSL Encrypted &nbsp;&middot;&nbsp; Secure Login</div>\n";

	// Hidden fields
	echo "<input type='hidden' id='ui_mode' name='ui_mode' value='".!fallback_mode()."'>\n";
	foreach($_SESSION['timeout']['post'] as $p => $val) {
		if (!in_array($p, array('ui_mode','user_name_entry_field','password','SubmitUser','company_login_name')))
			if (!is_array($val))
				echo "<input type='hidden' name='$p' value='$val'>";
			else
				foreach($val as $i => $v)
					echo "<input type='hidden' name='{$p}[$i]' value='$v'>";
	}
	echo "</form>\n";
	echo "</div>\n"; // /vip-card

// ── Footer ──
echo "<div class='vip-footer'>\n";
if (isset($_SESSION['wa_current_user']))
    $date = Today() . " | " . Now();
else
    $date = date("d/m/Y") . " | " . date("h:i a");
echo "</div>\n";

	// ── Developer Signature — Premium ──
	echo "<div class='vip-dev-sig'>\n";
	echo "  <div class='vip-dev-inner'>\n";
	echo "    <div class='vip-dev-label-row'>\n";
	echo "      <div class='vip-dev-label-icon'><svg width='11' height='11' viewBox='0 0 24 24' fill='none' stroke='#f1c943' stroke-width='2.5' stroke-linecap='round'><path d='M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2'/><circle cx='9' cy='7' r='4'/><path d='M22 21v-2a4 4 0 0 0-3-3.87'/><path d='M16 3.13a4 4 0 0 1 0 7.75'/></svg></div>\n";
	echo "      <div class='vip-dev-label'><strong>Developed by</strong> &middot; مطور بواسطة</div>\n";
	echo "    </div>\n";
	echo "    <div class='vip-dev-name'>MOHAMED SALAH</div>\n";
	echo "    <div class='vip-dev-badge'><span class='vip-dev-badge-dot'></span><span class='vip-dev-badge-text'>ERPNext &amp; Automation Expert</span></div>\n";
	echo "    <div class='vip-dev-divider'></div>\n";
	echo "    <div class='vip-dev-links'>\n";
	echo "      <a href='https://linkedin.com/in/mo222salah' target='_blank' rel='noopener'>"
		."<svg width='12' height='12' viewBox='0 0 24 24' fill='currentColor'>"
		."<path d='M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z'/>"
		."<rect x='2' y='9' width='4' height='12'/><circle cx='4' cy='4' r='2'/></svg>"
		."LinkedIn</a>\n";
	echo "      <a href='https://moh222salah.github.io/cv' target='_blank' rel='noopener'>"
		."<svg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>"
		."<path d='M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71'/>"
		."<path d='M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71'/></svg>"
		."Portfolio</a>\n";
	echo "      <a href='https://wa.me/201113903070' target='_blank' rel='noopener'>"
		."<svg width='12' height='12' viewBox='0 0 24 24' fill='currentColor'>"
		."<path d='M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z'/></svg>"
		."WhatsApp</a>\n";
	echo "    </div>\n";
	echo "  </div>\n";
	echo "</div>\n";

	echo "</div>\n"; // /vip-wrap

	// FA Ajax scripts
	$Ajax->addScript(true, "if (document.forms.length) document.forms[0].password.focus();");
?>
<script>
document.forms[0] && document.forms[0].user_name_entry_field && (
	document.forms[0].user_name_entry_field.select(),
	document.forms[0].user_name_entry_field.focus()
);
</script>
</body>
</html>