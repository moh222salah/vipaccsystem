<?php
/**********************************************************************
    Copyright (C) Vip Accounting System, LLC.
    Released under the terms of the GNU General Public License, GPL,
    as published by the Free Software Foundation, either version 3
    of the License, or (at your option) any later version.
***********************************************************************/

/*
 * custom_nav.php
 * ─────────────────────────────────────────────────────────────────────
 * Adds eight sidebar items to the navigation rendered by renderer.php.
 * The renderer already calls  fa_get_extra_nav_items()  when it exists
 * (see renderer.php line ~110) — so defining the function here is all
 * that is needed.
 *
 * INSTALLATION — ONE LINE ONLY:
 * ──────────────────────────────
 * Open  vipaccsystem.php  and add this single line anywhere after
 * the existing include_once block (e.g. after installed_extensions.php):
 *
 *     include_once($path_to_root . '/applications/custom_nav.php');
 *
 * That is the ONLY change required. No other files are touched.
 * ─────────────────────────────────────────────────────────────────────
 */

if (!isset($path_to_root) || isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
    die("Restricted access");

if (!function_exists('fa_get_extra_nav_items'))
{
    function fa_get_extra_nav_items($root)
    {
        /*
         * Each entry is an associative array with the keys the renderer
         * expects (see renderer.php  _nav_svg  and the loop at ~line 110):
         *
         *   type        — 'link' | 'separator'
         *   label       — English label (also used as Arabic fallback)
         *   label_ar    — Arabic label shown when UI language is ar_EG
         *   href        — Full URL relative to document root
         *   icon_id     — Key that matches _nav_svg() (see renderer.php)
         *   access      — FA security constant checked via can_access_page()
         *                 Leave empty '' to show the item to all logged-in users.
         *   conditional — get_company_pref() key; item hidden when pref is falsy.
         *                 Leave empty '' to always show.
         *
         * Items appear in the sidebar in the exact order listed below,
         * appended after the standard FA application tabs.
         */
        return [

            /* ── 1. Trial Balance ─────────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Trial Balance',
                'label_ar'    => 'ميزان المراجعة',
                'href'        => $root . '/reporting/rep111.php',
                'icon_id'     => 'trial_balance',
                'access'      => 'SA_GLANALYTIC',
                'conditional' => '',
            ],

            /* ── 2. Journal Entries ───────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Journal Entries',
                'label_ar'    => 'قيود اليومية',
                'href'        => $root . '/gl/gl_journal.php',
                'icon_id'     => 'journal',
                'access'      => 'SA_JOURNALENTRY',
                'conditional' => '',
            ],

            /* ── 3. Dimensions ────────────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Dimensions',
                'label_ar'    => 'الأبعاد',
                'href'        => $root . '/index.php?application=dimensions',
                'icon_id'     => 'dimensions',
                'access'      => 'SA_DIMTRANSVIEW',
                'conditional' => '',
            ],

            /* ── 4. Cost Centers ──────────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Cost Centers',
                'label_ar'    => 'مراكز التكلفة',
                'href'        => $root . '/gl/cost_center.php',
                'icon_id'     => 'cost_center',
                'access'      => 'SA_DIMTRANSVIEW',
                'conditional' => '',
            ],

            /* ── 5. Chart of Accounts ─────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Chart of Accounts',
                'label_ar'    => 'دليل الحسابات',
                'href'        => $root . '/gl/gl_accounts.php',
                'icon_id'     => 'coa',
                'access'      => 'SA_GLACCOUNT',
                'conditional' => '',
            ],

            /* ── 6. Tax Invoice ───────────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Tax Invoice',
                'label_ar'    => 'الفاتورة الضريبية',
                'href'        => $root . '/sales/customer_invoice.php',
                'icon_id'     => 'tax',
                'access'      => 'SA_SALESINVOICE',
                'conditional' => '',
            ],

            /* ── 7. Opening Balances ──────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Opening Balances',
                'label_ar'    => 'أرصدة أول المدة',
                'href'        => $root . '/gl/gl_opening_balances.php',
                'icon_id'     => 'opening_bal',
                'access'      => 'SA_GLSETUP',
                'conditional' => '',
            ],

            /* ── 8. Non-Profit Org ────────────────────────────────── */
            [
                'type'        => 'link',
                'label'       => 'Non-Profit Org',
                'label_ar'    => 'المنظمات غير الربحية',
                'href'        => $root . '/gl/budget_trans.php',
                'icon_id'     => 'npo',
                'access'      => 'SA_GLANALYTIC',
                'conditional' => '',
            ],

        ];
    }
}
