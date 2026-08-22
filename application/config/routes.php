<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['stock/in'] = 'stock/stock_in_data';
$route['stock/in/add'] = 'stock/stock_in_add';
$route['stock/in/add_after/(:num)'] = 'stock/stock_in_add_after/$1';
$route['stock/in/add_multiple'] = 'stock/stock_in_add_multiple';
$route['stock/in/edit/(:num)/(:num)'] = 'stock/stock_in_edit/$1';
$route['stock/in/del/(:num)/(:num)'] = 'stock/stock_in_del';

$route['stock/out'] = 'stock/stock_out_data';
$route['stock/out/add'] = 'stock/stock_out_add';
$route['stock/out/del/(:num)/(:num)'] = 'stock/stock_out_del';

$route['stock/in/report'] = 'stock/stock_in_report';
$route['stock/in/report/export'] = 'stock/export_stock_in_report';

// ── Item Supplier & Utilities ─────────────────────────────
$route['item/add_supplier']         = 'item/add_supplier';
$route['item/remove_supplier']      = 'item/remove_supplier';
$route['item/check_barcode_range']  = 'item/check_barcode_range';

// ── Service Item (Jasa) ───────────────────────────────────
$route['service-item']              = 'service_item/index';
$route['service-item/get_json']     = 'service_item/get_json';
$route['service-item/add']          = 'service_item/add';
$route['service-item/edit/(:num)']  = 'service_item/edit/$1';
$route['service-item/delete/(:num)']= 'service_item/delete/$1';

// ── Stock Review ──────────────────────────────────────────
$route['stock-review']                   = 'stock_review/index';
$route['stock-review/get_json']          = 'stock_review/get_json';
$route['stock-review/get_ref_price']     = 'stock_review/get_ref_price';
$route['stock-review/get_item_suppliers']  = 'stock_review/get_item_suppliers';
$route['stock-review/check_item_ordered']  = 'stock_review/check_item_ordered';

// ── PO Cart ───────────────────────────────────────────────
$route['po-cart']                        = 'po_cart/index';
$route['po-cart/add']                    = 'po_cart/add';
$route['po-cart/update']                 = 'po_cart/update';
$route['po-cart/remove/(:num)']          = 'po_cart/remove/$1';
$route['po-cart/clear']                  = 'po_cart/clear';
$route['po-cart/create_po']              = 'po_cart/create_po';

// ── Purchase Order — specific routes BEFORE (:num) wildcard ──
$route['purchase-order']                 = 'purchase_order/index';
$route['purchase-order/get_json']        = 'purchase_order/get_json';
$route['purchase-order/receiving']        = 'penerimaan/receiving_list';
$route['purchase-order/receiving-data']  = 'penerimaan/receiving_supplier_data';
$route['purchase-order/history']         = 'penerimaan/receiving_history';
$route['purchase-order/history-data']    = 'penerimaan/receiving_history_data';
$route['purchase-order/history/(:num)']         = 'penerimaan/receiving_history_detail/$1';
$route['purchase-order/history/(:num)/edit']    = 'penerimaan/edit_receipt_form/$1';
$route['purchase-order/history/delete-detail']  = 'penerimaan/delete_receipt_detail';
$route['purchase-order/history/add-detail']     = 'penerimaan/add_receipt_detail';
$route['purchase-order/history/add-extra-item'] = 'penerimaan/add_receipt_extra_item';
$route['purchase-order/history/update-prices'] = 'penerimaan/update_receipt_prices';
$route['purchase-order/history/delete/(:num)']  = 'penerimaan/delete_receipt/$1';
$route['purchase-order/history/mark-labeled/(:num)']  = 'penerimaan/mark_labeled/$1';
$route['purchase-order/add-detail']      = 'purchase_order/add_detail_draft';
$route['purchase-order/remove-detail']   = 'purchase_order/remove_detail_draft';
$route['purchase-order/update-detail']   = 'purchase_order/update_detail_draft';
$route['purchase-order/search-item']     = 'purchase_order/search_item_draft';
$route['purchase-order/close']           = 'purchase_order/close_po';
$route['purchase-order/receive']         = 'penerimaan/receive';
$route['purchase-order/receive/(:num)']  = 'penerimaan/receive_form/$1';
$route['purchase-order/confirm-price']   = 'purchase_order/confirm_price_update';
$route['purchase-order/overdue-count']   = 'purchase_order/overdue_count';
$route['purchase-order/status']          = 'purchase_order/update_status';
$route['purchase-order/register-item']   = 'purchase_order/register_temp_item';
$route['purchase-order/receive-add-item']   = 'penerimaan/receive_add_item';
$route['purchase-order/receive-direct']     = 'penerimaan/receive_direct_start';
$route['purchase-order/print/(:num)']    = 'purchase_order/print_po/$1';
$route['purchase-order/(:num)']          = 'purchase_order/detail/$1';

// ── Notifications ──────────────────────────────────────────────────────────
$route['dashboard/notifications']          = 'dashboard/get_notifications_json';
$route['dashboard/notifications/read']     = 'dashboard/mark_notifications_read';
$route['dashboard/notifications/read-one'] = 'dashboard/mark_one_notification_read';
$route['auth/update-profile']           = 'auth/update_profile';

// ── Finance: Chart of Accounts ────────────────────────────
$route['coa']                    = 'coa/index';
$route['coa/get_json']           = 'coa/get_json';
$route['coa/add']                = 'coa/add';
$route['coa/edit/(:num)']        = 'coa/edit/$1';
$route['coa/delete/(:num)']      = 'coa/delete/$1';

// ── Finance: Jurnal Umum ──────────────────────────────────
$route['journal']                = 'journal/index';
$route['journal/get_json']       = 'journal/get_json';
$route['journal/add']            = 'journal/add';
$route['journal/void/(:num)']    = 'journal/void/$1';
$route['journal/detail/(:num)']  = 'journal/detail/$1';

// ── Finance: Piutang (AR) ──────────────────────────────────
$route['ar-invoice']                = 'ar_invoice/index';
$route['ar-invoice/get_json']       = 'ar_invoice/get_json';
$route['ar-invoice/add']            = 'ar_invoice/add';
$route['ar-invoice/void/(:num)']    = 'ar_invoice/void/$1';
$route['ar-invoice/reactivate/(:num)'] = 'ar_invoice/reactivate/$1';
$route['ar-invoice/refresh-all-due-dates'] = 'ar_invoice/refresh_all_due_dates';
$route['ar-invoice/detail/(:num)']  = 'ar_invoice/detail/$1';

$route['ar-payment/process']        = 'ar_payment/process';
$route['ar-payment/void/(:num)']    = 'ar_payment/void/$1';
$route['ar-payment/add/(:num)']     = 'ar_payment/add/$1';

// ── Finance: Kontra Bon (konsolidasi tagihan piutang) ──────
$route['kontra-bon']                     = 'ar_kontra_bon/index';
$route['kontra-bon/get_json']            = 'ar_kontra_bon/get_json';
$route['kontra-bon/preview']             = 'ar_kontra_bon/preview';
$route['kontra-bon/add']                 = 'ar_kontra_bon/add';
$route['kontra-bon/void/(:num)']         = 'ar_kontra_bon/void/$1';
$route['kontra-bon/cetak/(:num)']        = 'ar_kontra_bon/cetak/$1';
$route['kontra-bon/detail/(:num)']       = 'ar_kontra_bon/detail/$1';

$route['kontra-bon-payment/add/(:num)']  = 'ar_kontra_bon_payment/add/$1';
$route['kontra-bon-payment/process']     = 'ar_kontra_bon_payment/process';
$route['kontra-bon-payment/void/(:num)'] = 'ar_kontra_bon_payment/void/$1';

// ── Finance: Hutang (AP) ────────────────────────────────────
$route['ap-invoice']                = 'ap_invoice/index';
$route['ap-invoice/get_json']       = 'ap_invoice/get_json';
$route['ap-invoice/void/(:num)']    = 'ap_invoice/void/$1';
$route['ap-invoice/refresh-all-due-dates'] = 'ap_invoice/refresh_all_due_dates';
$route['ap-invoice/detail/(:num)']  = 'ap_invoice/detail/$1';

$route['ap-payment/process']        = 'ap_payment/process';
$route['ap-payment/void/(:num)']    = 'ap_payment/void/$1';
$route['ap-payment/add/(:num)']     = 'ap_payment/add/$1';

// ── Finance: Kontra Bon Hutang (konsolidasi tagihan hutang) ──
$route['ap-kontra-bon']                     = 'ap_kontra_bon/index';
$route['ap-kontra-bon/get_json']            = 'ap_kontra_bon/get_json';
$route['ap-kontra-bon/preview']             = 'ap_kontra_bon/preview';
$route['ap-kontra-bon/add']                 = 'ap_kontra_bon/add';
$route['ap-kontra-bon/void/(:num)']         = 'ap_kontra_bon/void/$1';
$route['ap-kontra-bon/cetak/(:num)']        = 'ap_kontra_bon/cetak/$1';
$route['ap-kontra-bon/detail/(:num)']       = 'ap_kontra_bon/detail/$1';

$route['ap-kontra-bon-payment/add/(:num)']  = 'ap_kontra_bon_payment/add/$1';
$route['ap-kontra-bon-payment/process']     = 'ap_kontra_bon_payment/process';
$route['ap-kontra-bon-payment/void/(:num)'] = 'ap_kontra_bon_payment/void/$1';

// ── Migrasi data piutang lama (SEKALI PAKAI — hapus setelah dipakai) ──
$route['migrate-ar']      = 'migrate_ar/index';
$route['migrate-ar/run']  = 'migrate_ar/run';

// ── Finance: Beban Operasional ─────────────────────────────
$route['beban']              = 'beban/index';
$route['beban/get_json']     = 'beban/get_json';
$route['beban/add']          = 'beban/add';
$route['beban/void/(:num)']  = 'beban/void/$1';

// ── SDM: Karyawan ───────────────────────────────────────────
$route['karyawan']             = 'karyawan/index';
$route['karyawan/add']         = 'karyawan/add';
$route['karyawan/edit/(:num)'] = 'karyawan/edit/$1';
$route['karyawan/process']     = 'karyawan/process';
$route['karyawan/del/(:num)']  = 'karyawan/del/$1';

// ── SDM: Absensi & Uang Makan Karyawan ──────────────────────
$route['absensi']                      = 'absensi/index';
$route['absensi/history']              = 'absensi/history';
$route['absensi/history-json']         = 'absensi/history_json';
$route['absensi/save']                 = 'absensi/save';
$route['absensi/process']              = 'absensi/process';
$route['absensi/update-tarif']         = 'absensi/update_tarif';
$route['absensi/void/(:num)']          = 'absensi/void/$1';
$route['absensi/(:num)-(:num)-(:num)'] = 'absensi/index/$1/$2/$3';

// ── Report: Operasional ─────────────────────────────────────
$route['report-beban']        = 'report_beban/index';
$route['report-beban/cetak']  = 'report_beban/cetak';
$route['report-beban/export'] = 'report_beban/export_excel';

// ── Report: Pembelian ───────────────────────────────────────
$route['report-purchase']        = 'report_purchase/index';
$route['report-purchase/cetak']  = 'report_purchase/cetak';
$route['report-purchase/export'] = 'report_purchase/export_excel';

// ── Report: Piutang ────────────────────────────────────────
$route['report-ar']                    = 'report_ar/index';
$route['report-ar/aging']              = 'report_ar/aging';
$route['report-ar/cetak-aging']        = 'report_ar/cetak_aging';
$route['report-ar/export-aging']       = 'report_ar/export_excel_aging';
$route['report-ar/daftar']             = 'report_ar/daftar';
$route['report-ar/cetak-daftar']       = 'report_ar/cetak_daftar';
$route['report-ar/export-daftar']      = 'report_ar/export_excel_daftar';
$route['report-ar/kartu/(:num)']       = 'report_ar/kartu_piutang/$1';
$route['report-ar/cetak-kartu/(:num)'] = 'report_ar/cetak_kartu/$1';

// ── Finance: Pembayaran Keluar (riwayat hutang) ─────────────
$route['pembayaran-keluar']         = 'pembayaran_keluar/index';
$route['pembayaran-keluar/cetak']   = 'pembayaran_keluar/cetak';
$route['pembayaran-keluar/export']  = 'pembayaran_keluar/export_excel';

// ── Finance: Pembayaran Masuk (riwayat piutang) ─────────────
$route['pembayaran-masuk']          = 'pembayaran_masuk/index';
$route['pembayaran-masuk/cetak']    = 'pembayaran_masuk/cetak';
$route['pembayaran-masuk/export']   = 'pembayaran_masuk/export_excel';

// ── Report: Hutang ──────────────────────────────────────────
$route['report-ap']                    = 'report_ap/index';
$route['report-ap/aging']              = 'report_ap/aging';
$route['report-ap/cetak-aging']        = 'report_ap/cetak_aging';
$route['report-ap/export-aging']       = 'report_ap/export_excel_aging';
$route['report-ap/daftar']             = 'report_ap/daftar';
$route['report-ap/cetak-daftar']       = 'report_ap/cetak_daftar';
$route['report-ap/export-daftar']      = 'report_ap/export_excel_daftar';
$route['report-ap/kartu/(:num)']       = 'report_ap/kartu_hutang/$1';
$route['report-ap/cetak-kartu/(:num)'] = 'report_ap/cetak_kartu/$1';