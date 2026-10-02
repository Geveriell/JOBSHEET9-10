const $ = s => document.querySelector(s);
const rp = n => 'Rp' + Number(n).toLocaleString('id-ID');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let data = [], editId = null, hidden = true, regMode = false;

async function api(a, body) {
  const r = await fetch('api.php?a=' + a, body ? {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)} : {});
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw Object.assign(new Error(j.error || 'Terjadi kesalahan'), {status:r.status});
  return j;
}

/* ---------- Auth ---------- */
function setMode(reg) {
  regMode = reg;
  $('#nama').classList.toggle('hide', !reg);
  $('#authBtn').textContent = reg ? 'Daftar' : 'Masuk';
  $('#authSwitch').textContent = reg ? 'Sudah punya akun? Masuk' : 'Belum punya akun? Daftar';
  $('#authSub').textContent = reg ? 'Buat akun untuk mulai mencatat.' : 'Masuk untuk membuka catatan keuanganmu.';
  $('#aerr').textContent = '';
}
$('#authSwitch').onclick = () => setMode(!regMode);
$('#authBtn').onclick = async () => {
  try {
    const body = {email:$('#email').value, password:$('#pw').value};
    if (regMode) body.nama = $('#nama').value;
    const r = await api(regMode ? 'register' : 'login', body);
    showApp(r.nama);
  } catch (e) { $('#aerr').textContent = e.message; }
};

function showApp(nama) {
  $('#auth').classList.add('hide'); $('#app').classList.remove('hide');
  $('#uname').textContent = $('#uname2').textContent = nama;
  $('#bulanLbl').textContent = new Date().toLocaleDateString('id-ID', {month:'long', year:'numeric'});
  load();
}

/* ---------- Data ---------- */
async function load() {
  try { data = await api('list'); render(); }
  catch (e) { if (e.status === 401) location.reload(); else alert(e.message); }
}

function render() {
  const sum = (arr, k) => arr.filter(r => r.tipe === k).reduce((s, r) => s + Number(r.jumlah), 0);
  const total = sum(data, 'pemasukan') - sum(data, 'pengeluaran');
  $('#saldo').textContent = hidden ? 'IDR ••••••••' : 'IDR ' + Number(total).toLocaleString('id-ID');

  const bln = new Date().toISOString().slice(0, 7), bi = data.filter(r => r.tanggal.startsWith(bln));
  $('#mIn').textContent = rp(sum(bi, 'pemasukan')); $('#mOut').textContent = rp(sum(bi, 'pengeluaran'));

  const q = $('#q').value.toLowerCase(), t = $('#ft').value;
  const rows = data.filter(r => (!t || r.tipe === t) && (!q || (r.deskripsi + ' ' + r.kategori).toLowerCase().includes(q)));
  $('#list').innerHTML = rows.length ? rows.map(r => `
    <div class="item">
      <span class="dot ${r.tipe}">${r.tipe === 'pemasukan' ? '⬇' : '⬆'}</span>
      <div>${esc(r.deskripsi) || esc(r.kategori)}<small>${esc(r.kategori)} · ${new Date(r.tanggal).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})}</small></div>
      <div class="amt ${r.tipe}">${r.tipe === 'pemasukan' ? '+' : '−'}${rp(r.jumlah)}
        <div><button onclick="openForm(${r.id})">Ubah</button><button class="d" onclick="del(${r.id})">Hapus</button></div></div>
    </div>`).join('') : '<div class="empty">Belum ada transaksi. Ketuk Pemasukan atau Pengeluaran untuk mencatat.</div>';
}

/* ---------- Form ---------- */
function openForm(id, tipe) {
  editId = id ?? null;
  const r = data.find(x => x.id == id) || {tipe:tipe || 'pengeluaran', tanggal:new Date().toISOString().slice(0, 10), kategori:'', deskripsi:'', jumlah:''};
  $('#dt').textContent = id ? 'Ubah transaksi' : 'Catat transaksi';
  $('#f_tipe').value = r.tipe; $('#f_tgl').value = r.tanggal; $('#f_kat').value = r.kategori;
  $('#f_des').value = r.deskripsi || ''; $('#f_jum').value = r.jumlah ? Math.round(r.jumlah) : ''; $('#ferr').textContent = '';
  $('#dlg').showModal();
}
$('#bBatal').onclick = () => $('#dlg').close();
$('#bSimpan').onclick = async () => {
  const r = {id:editId, tipe:$('#f_tipe').value, tanggal:$('#f_tgl').value, kategori:$('#f_kat').value.trim(), deskripsi:$('#f_des').value.trim(), jumlah:Number($('#f_jum').value)};
  if (!r.tanggal || !r.kategori || !(r.jumlah > 0)) { $('#ferr').textContent = 'Tanggal, kategori, dan jumlah (lebih dari 0) wajib diisi.'; return; }
  try { await api('save', r); $('#dlg').close(); load(); } catch (e) { $('#ferr').textContent = e.message; }
};
async function del(id) { if (confirm('Hapus transaksi ini?')) { try { await api('delete', {id}); load(); } catch (e) { alert(e.message); } } }

/* ---------- Event ---------- */
$('#eye').onclick = () => { hidden = !hidden; render(); };
$('#q').oninput = $('#ft').onchange = render;
$('#lnkCari').onclick = e => { e.preventDefault(); $('#q').focus(); };
document.querySelectorAll('.menu button').forEach(b => b.onclick = async () => {
  const a = b.dataset.act;
  if (a === 'riwayat') $('#riwayat').scrollIntoView({behavior:'smooth'});
  else if (a === 'keluar') { await api('logout'); location.reload(); }
  else openForm(null, a);
});

/* ---------- Mulai ---------- */
api('me').then(r => showApp(r.nama)).catch(() => $('#auth').classList.remove('hide'));