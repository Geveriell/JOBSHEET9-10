<?php $v = filemtime(__DIR__.'/assets/css/style.css') . filemtime(__DIR__.'/assets/js/app.js'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dompetku</title>
<link rel="stylesheet" href="assets/css/style.css?v=<?= $v ?>">
</head>
<body>
<div class="phone">

  <section id="auth" class="hide">
    <div class="auth-card">
      <h1>Dompetku</h1>
      <p class="muted" id="authSub">Masuk untuk membuka catatan keuanganmu.</p>
      <input id="nama" class="hide" placeholder="Nama lengkap" autocomplete="name">
      <input id="email" type="email" placeholder="Email" autocomplete="email">
      <input id="pw" type="password" placeholder="Kata sandi" autocomplete="current-password">
      <p class="err" id="aerr"></p>
      <button class="btn" id="authBtn">Masuk</button>
      <button class="link" id="authSwitch">Belum punya akun? Daftar</button>
    </div>
  </section>

  <section id="app" class="hide">
    <p class="hello">Halo, <b id="uname"></b></p>

    <div class="acct">
      <div class="acct-top">
        <span class="pill">Dompet Pribadi</span>
        <p>Pengguna: <b id="uname2"></b></p>
      </div>
      <div class="acct-bot">
        <small>Saldo aktif</small>
        <div class="saldo-row">
          <strong id="saldo">IDR ••••••••</strong>
          <button class="eye" id="eye" aria-label="Tampilkan atau sembunyikan saldo">👁</button>
        </div>
        <div class="acct-links"><a href="#riwayat">📒 Mutasi</a><a href="#" id="lnkCari">🔎 Cari</a></div>
      </div>
    </div>

    <div class="banner">
      <div><small id="bulanLbl"></small><p>Masuk <b id="mIn">Rp0</b> · Keluar <b id="mOut">Rp0</b></p></div>
      <span>Bulan ini ›</span>
    </div>

    <div class="sheet">
      <div class="menu">
        <button data-act="pemasukan"><i class="ic g">⬇</i>Pemasukan</button>
        <button data-act="pengeluaran"><i class="ic r">⬆</i>Pengeluaran</button>
        <button data-act="riwayat"><i class="ic b">📒</i>Riwayat</button>
        <button data-act="keluar"><i class="ic k">⎋</i>Keluar</button>
      </div>

      <h2 id="riwayat">Riwayat transaksi</h2>
      <div class="filters">
        <input id="q" placeholder="Cari deskripsi atau kategori">
        <select id="ft"><option value="">Semua</option><option value="pemasukan">Masuk</option><option value="pengeluaran">Keluar</option></select>
      </div>
      <div id="list"></div>
    </div>
  </section>
</div>

<dialog id="dlg">
  <h3 id="dt">Catat transaksi</h3>
  <label>Jenis<select id="f_tipe"><option value="pengeluaran">Pengeluaran</option><option value="pemasukan">Pemasukan</option></select></label>
  <label>Tanggal<input id="f_tgl" type="date"></label>
  <label>Kategori<input id="f_kat" list="kats" placeholder="Gaji, Makan, Transport…"></label>
  <datalist id="kats"><option>Gaji</option><option>Makan</option><option>Transport</option><option>Tagihan</option><option>Belanja</option><option>Hiburan</option><option>Tabungan</option></datalist>
  <label>Deskripsi<input id="f_des" maxlength="120"></label>
  <label>Jumlah (Rp)<input id="f_jum" type="number" min="1" step="1" inputmode="numeric"></label>
  <p class="err" id="ferr"></p>
  <div class="dlg-act"><button class="btn ghost" id="bBatal">Batal</button><button class="btn" id="bSimpan">Simpan</button></div>
</dialog>

<script src="assets/js/app.js?v=<?= $v ?>"></script>
</body>
</html>