<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<section class="content-header">
    <h1>
        <i class="fa fa-file-invoice text-primary"></i> Laporan Pajak (Preview)
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Report</a></li>
        <li class="active">Tax Preview</li>
    </ol>
</section>

<section class="content">

    <!-- ================= FILTER ================= -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Pilih Periode Transaksi</h3>
        </div>
        <div class="box-body">
            <form method="get" action="<?= site_url('report_tax') ?>" class="form-inline">
                <div class="form-group">
                    <label class="mr-2">Bulan </label>
                    <input type="month" name="period" value="<?= $period ?>" class="form-control mx-sm-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Tampilkan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= AUTO SELECT ================= -->
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-magic"></i> Auto Pilih berdasarkan Target Nominal</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nominal Minimum (Rp)</label>
                        <input type="text" id="input-min" class="form-control input-nominal" placeholder="cth: 75000000" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nominal Maksimum (Rp)</label>
                        <input type="text" id="input-max" class="form-control input-nominal" placeholder="cth: 80000000" value="">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Min Jumlah Faktur</label>
                        <input type="number" id="input-min-count" class="form-control" placeholder="cth: 50" min="1" value="">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Max per Invoice (Rp)</label>
                        <input type="text" id="input-max-invoice" class="form-control input-nominal" placeholder="cth: 2500000" value="2500000">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" id="btn-auto-select" class="btn btn-warning">
                            <i class="fa fa-magic"></i> Auto Pilih
                        </button>
                        <button type="button" id="btn-reset-select" class="btn btn-default">
                            <i class="fa fa-times"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
            <div id="auto-select-info" class="alert" style="display:none;"></div>
        </div>
    </div>

    <!-- ================= SUMMARY BOX ================= -->
    <div class="row">
        <div class="col-md-2">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 id="sum-invoice">0</h3>
                    <p>Faktur Terpilih</p>
                </div>
                <div class="icon"><i class="fa fa-check-square"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 id="sum-dpp" style="font-size:20px;">0</h3>
                    <p>Total DPP</p>
                </div>
                <div class="icon"><i class="fa fa-calculator"></i></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 id="sum-ppn" style="font-size:20px;">0</h3>
                    <p>Total PPN (12%)</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3 id="sum-grand" style="font-size:20px;">0</h3>
                    <p>Grand Total</p>
                </div>
                <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div id="range-indicator-box" class="small-box bg-gray">
                <div class="inner">
                    <h3 id="range-status-text" style="font-size:14px;">-</h3>
                    <p id="range-status-label">Status Range</p>
                </div>
                <div class="icon"><i class="fa fa-bullseye"></i></div>
            </div>
        </div>
    </div>

    <!-- ================= DATE BREAKDOWN ================= -->
    <div id="date-breakdown-box" class="box box-default" style="display:none;">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-calendar"></i> Rincian per Tanggal</h3>
        </div>
        <div class="box-body">
            <div id="date-breakdown-content" class="row"></div>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-list"></i> 
                List Penjualan (Periode: <?= $period ?>)
            </h3>
        </div>

        <div class="box-body table-responsive">

            <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success">
                    <?= $this->session->flashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('report_tax/generate_selected') ?>">

                <table class="table table-bordered table-striped" id="table-tax-preview">
                    <thead class="bg-primary">
                        <tr>
                            <th width="40px" class="text-center">
                                <input type="checkbox" id="check-all">
                            </th>
                            <th>Status</th>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th class="text-center">Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">DPP (11/12)</th>
                            <th class="text-right">PPN (12%)</th>
                            <th class="text-right">Total + PPN</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if (!empty($preview)): ?>
                            <?php foreach ($preview as $row):

                                $subtotal = (float)$row->grand_total;
                                $dpp = round($subtotal * 11 / 12);
                                $ppn = round($dpp * 0.12);
                                $total_with_ppn = $subtotal + $ppn;

                                $isGenerated = !empty($row->tax_id);
                            ?>

                            <tr class="<?= $isGenerated ? 'bg-success' : '' ?>">
                                <td class="text-center">

                                    <?php if ($isGenerated): ?>
                                        <input type="checkbox" checked disabled>
                                    <?php else: ?>
                                        <input type="checkbox"
                                            name="sale_ids[]"
                                            value="<?= $row->sale_id ?>"
                                            class="check-item"
                                            data-date="<?= $row->sale_date ?>"
                                            data-qty="<?= $row->total_qty ?>"
                                            data-total="<?= $subtotal ?>"
                                            data-dpp="<?= $dpp ?>"
                                            data-ppn="<?= $ppn ?>"
                                            data-grand="<?= $total_with_ppn ?>">
                                    <?php endif; ?>

                                </td>

                                <td class="text-center">
                                    <?php if ($isGenerated): ?>
                                        <span class="label label-success">
                                            <i class="fa fa-check"></i> Sudah
                                        </span>
                                    <?php else: ?>
                                        <span class="label label-warning">
                                            <i class="fa fa-clock-o"></i> Belum
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td><strong><?= $row->invoice ?></strong></td>
                                <td><?= date('d-m-Y', strtotime($row->sale_date)) ?></td>
                                <td class="text-center"><?= number_format($row->total_item) ?></td>
                                <td class="text-center"><?= number_format($row->total_qty) ?></td>
                                <td class="text-right"><?= number_format($subtotal) ?></td>
                                <td class="text-right text-blue"><?= number_format($dpp) ?></td>
                                <td class="text-right text-green"><?= number_format($ppn) ?></td>
                                <td class="text-right text-bold"><?= number_format($total_with_ppn) ?></td>
                            </tr>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    Tidak ada data transaksi.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

                <div class="box-footer">
                    <button type="submit" class="btn btn-success btn-lg" id="btn-generate" disabled>
                        <i class="fa fa-database"></i> Generate Data Terpilih
                    </button>
                </div>

            </form>

        </div>
    </div>
</section>

<script>
// ============================================================
// FORMAT INPUT NOMINAL: otomatis tambah titik saat mengetik
// ============================================================
document.querySelectorAll('.input-nominal').forEach(function(el) {
    el.addEventListener('input', function() {
        let raw = this.value.replace(/\D/g, '');
        this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    });
});

function parseNominal(str) {
    return parseInt((str || '0').replace(/\./g, '').replace(/,/g, '')) || 0;
}

// ============================================================
// UPDATE SUMMARY + INDIKATOR RANGE
// ============================================================
function updateSummary() {
    let totalInvoice = 0;
    let dpp   = 0;
    let ppn   = 0;
    let grand = 0;
    let subtotal = 0;

    document.querySelectorAll('.check-item:checked').forEach(cb => {
        totalInvoice++;
        subtotal += parseFloat(cb.dataset.total || 0);
        dpp      += parseFloat(cb.dataset.dpp   || 0);
        ppn      += parseFloat(cb.dataset.ppn   || 0);
        grand    += parseFloat(cb.dataset.grand  || 0);
    });

    document.getElementById('sum-invoice').innerText = totalInvoice;
    document.getElementById('sum-dpp').innerText     = dpp.toLocaleString('id-ID');
    document.getElementById('sum-ppn').innerText     = ppn.toLocaleString('id-ID');
    document.getElementById('sum-grand').innerText   = grand.toLocaleString('id-ID');
    document.getElementById('btn-generate').disabled = (totalInvoice === 0);

    updateRangeIndicator(subtotal);
    updateDateBreakdown();
}

function updateDateBreakdown() {
    const checked = document.querySelectorAll('.check-item:checked');
    const box     = document.getElementById('date-breakdown-box');
    const content = document.getElementById('date-breakdown-content');

    if (checked.length === 0) {
        box.style.display = 'none';
        return;
    }

    // Kumpulkan per tanggal
    const dateMap = {};
    checked.forEach(cb => {
        const raw = cb.dataset.date || '';
        if (!raw) return;
        if (!dateMap[raw]) dateMap[raw] = { count: 0, total: 0 };
        dateMap[raw].count++;
        dateMap[raw].total += parseFloat(cb.dataset.total || 0);
    });

    // Urutkan tanggal ASC
    const dates = Object.keys(dateMap).sort();

    let html = '';
    dates.forEach(d => {
        const parts   = d.split('-');
        const label   = parts[2] + '-' + parts[1] + '-' + parts[0];
        const count   = dateMap[d].count;
        const nominal = dateMap[d].total.toLocaleString('id-ID');
        html += `<div class="col-md-2 col-sm-3 col-xs-4" style="margin-bottom:6px;">
            <div class="info-box" style="min-height:0;padding:4px 8px;">
                <span class="info-box-icon bg-aqua" style="width:36px;height:36px;line-height:36px;font-size:13px;">${count}</span>
                <div class="info-box-content" style="margin-left:40px;padding:4px 0;">
                    <span class="info-box-text" style="font-size:11px;">${label}</span>
                    <span class="info-box-number" style="font-size:11px;">Rp ${nominal}</span>
                </div>
            </div>
        </div>`;
    });

    content.innerHTML = html;
    box.style.display = 'block';
}

function updateRangeIndicator(subtotal) {
    const minTarget   = parseNominal(document.getElementById('input-min').value);
    const maxTarget   = parseNominal(document.getElementById('input-max').value);
    const box         = document.getElementById('range-indicator-box');
    const statusText  = document.getElementById('range-status-text');
    const statusLabel = document.getElementById('range-status-label');

    if (!minTarget && !maxTarget) {
        box.className = 'small-box bg-gray';
        statusText.innerText  = '-';
        statusLabel.innerText = 'Status Range';
        return;
    }

    const fmt = subtotal.toLocaleString('id-ID');

    if (minTarget && maxTarget) {
        if (subtotal >= minTarget && subtotal <= maxTarget) {
            box.className = 'small-box bg-green';
            statusText.innerText  = 'Rp ' + fmt;
            statusLabel.innerText = '✔ Dalam Range';
        } else if (subtotal < minTarget) {
            box.className = 'small-box bg-yellow';
            const kurang = (minTarget - subtotal).toLocaleString('id-ID');
            statusText.innerText  = 'Rp ' + fmt;
            statusLabel.innerText = 'Kurang Rp ' + kurang;
        } else {
            box.className = 'small-box bg-red';
            const lebih = (subtotal - maxTarget).toLocaleString('id-ID');
            statusText.innerText  = 'Rp ' + fmt;
            statusLabel.innerText = 'Lebih Rp ' + lebih;
        }
    } else {
        box.className = 'small-box bg-blue';
        statusText.innerText  = 'Rp ' + fmt;
        statusLabel.innerText = 'Total Terpilih';
    }
}

// ============================================================
// AUTO SELECT (GREEDY)
// ============================================================
document.getElementById('btn-auto-select').addEventListener('click', function() {
    const minTarget     = parseNominal(document.getElementById('input-min').value);
    const maxTarget     = parseNominal(document.getElementById('input-max').value);
    const minCount      = parseInt(document.getElementById('input-min-count').value) || 0;
    const maxPerInvoice = parseNominal(document.getElementById('input-max-invoice').value);
    const infoBox       = document.getElementById('auto-select-info');

    if (!minTarget || !maxTarget) {
        infoBox.className = 'alert alert-warning';
        infoBox.style.display = 'block';
        infoBox.innerHTML = '<i class="fa fa-warning"></i> Isi nominal minimum dan maksimum terlebih dahulu.';
        return;
    }
    if (minTarget >= maxTarget) {
        infoBox.className = 'alert alert-warning';
        infoBox.style.display = 'block';
        infoBox.innerHTML = '<i class="fa fa-warning"></i> Nominal minimum harus lebih kecil dari maksimum.';
        return;
    }

    // Kumpulkan semua checkbox yang eligible (filter max per invoice)
    let candidates = [];
    document.querySelectorAll('.check-item:not(:disabled)').forEach(cb => {
        const total = parseFloat(cb.dataset.total || 0);
        if (maxPerInvoice > 0 && total > maxPerInvoice) return;
        candidates.push({ cb, total });
    });

    // Uncheck semua dulu
    document.querySelectorAll('.check-item:not(:disabled)').forEach(cb => cb.checked = false);

    let running  = 0;
    let selected = 0;

    // Pass 1: jika minCount diset, wajib pilih minimal N faktur terkecil dulu
    if (minCount > 0) {
        let sorted = [...candidates].sort((a, b) => a.total - b.total);
        for (let item of sorted) {
            if (selected >= minCount) break;
            item.cb.checked = true;
            running += item.total;
            selected++;
        }
    }

    // Pass 2: greedy dari terbesar — tambah selama total <= maxTarget
    let remaining = candidates.filter(item => !item.cb.checked);
    remaining.sort((a, b) => b.total - a.total);
    remaining.forEach(item => {
        if (running + item.total <= maxTarget) {
            item.cb.checked = true;
            running += item.total;
            selected++;
        }
    });

    // Pass 3: jika masih belum mencapai minTarget, paksa tambah invoice terkecil yang tersisa
    if (running < minTarget) {
        let leftover = candidates.filter(item => !item.cb.checked);
        leftover.sort((a, b) => a.total - b.total);
        for (let item of leftover) {
            if (running >= minTarget) break;
            item.cb.checked = true;
            running += item.total;
            selected++;
        }
    }

    // Hitung yang diskip karena di atas max per invoice
    let skipped = 0;
    document.querySelectorAll('.check-item:not(:disabled)').forEach(cb => {
        const total = parseFloat(cb.dataset.total || 0);
        if (maxPerInvoice > 0 && total > maxPerInvoice) skipped++;
    });

    const fmt     = running.toLocaleString('id-ID');
    const inRange = running >= minTarget && running <= maxTarget;
    const countOk = minCount === 0 || selected >= minCount;

    const skipDesc = skipped > 0
        ? ' | <span class="text-muted">' + skipped + ' faktur diskip (nominal > Rp ' + maxPerInvoice.toLocaleString('id-ID') + ')</span>'
        : '';

    const countDesc = minCount > 0
        ? ' | Jumlah faktur: <strong>' + selected + '</strong> / min <strong>' + minCount + '</strong>' + (countOk ? ' ✔' : ' ✘')
        : ' | <strong>' + selected + ' faktur</strong>';

    infoBox.style.display = 'block';
    infoBox.className = (inRange && countOk) ? 'alert alert-success' : 'alert alert-warning';
    infoBox.innerHTML = ((inRange && countOk) ? '<i class="fa fa-check-circle"></i> ' : '<i class="fa fa-warning"></i> ')
        + 'Total subtotal: <strong>Rp ' + fmt + '</strong>'
        + countDesc
        + skipDesc
        + (!inRange ? ' &mdash; <em>Nominal belum masuk range, silakan adjust manual.</em>' : '')
        + (inRange && !countOk ? ' &mdash; <em>Jumlah faktur kurang dari minimum.</em>' : '');

    updateSummary();
});

// ============================================================
// RESET
// ============================================================
document.getElementById('btn-reset-select').addEventListener('click', function() {
    document.querySelectorAll('.check-item:not(:disabled)').forEach(cb => cb.checked = false);
    document.getElementById('check-all').checked = false;
    document.getElementById('auto-select-info').style.display = 'none';
    updateSummary();
});

// ============================================================
// EVENT LISTENERS
// ============================================================
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('check-item') || e.target.id === 'check-all') {
        updateSummary();
    }
});

document.getElementById('check-all').addEventListener('click', function() {
    document.querySelectorAll('.check-item:not(:disabled)').forEach(cb => cb.checked = this.checked);
    updateSummary();
});

// Update indikator juga saat user mengubah input range
['input-min', 'input-max'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        let subtotal = 0;
        document.querySelectorAll('.check-item:checked').forEach(cb => {
            subtotal += parseFloat(cb.dataset.total || 0);
        });
        updateRangeIndicator(subtotal);
    });
});
</script>