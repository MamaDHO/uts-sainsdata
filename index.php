<?php
/**
 * Viewer praktikum (PHP native, tanpa library).
 * Semua yang tampil dibaca langsung saat halaman dibuka:
 *   - kode  : dibaca dari file soalN_*.php
 *   - output: dihasilkan dengan MENJALANKAN file soal itu
 *   - data  : dibaca dari dataset_mahasiswa.csv
 * Halaman otomatis memuat ulang saat salah satu file berubah.
 */
$dir  = __DIR__;
$csv  = $dir . '/dataset_mahasiswa.csv';
$soal = [
    1 => ['soal1_pipeline.php', 'Pipeline data',
          'Data transaksi mentah masuk, hanya baris yang struktur dan isinya benar yang lolos ke analisis.',
          ['Siapkan data mentah transaksi sebagai array asosiatif, sengaja dengan beberapa baris rusak.',
           'Validasi tiap baris: kunci wajib ada, produk tidak kosong, jumlah dan harga numerik positif.',
           'Pisahkan baris valid dan baris ditolak.',
           'Proses baris valid: samakan tipe data dan hitung total.',
           'Cetak laporan jumlah di setiap tahap.']],
    2 => ['soal2_eda_csv.php', 'EDA dari CSV',
          'Mengenal isi dataset_mahasiswa.csv sebelum dianalisis.',
          ['Buka file dengan fopen().',
           'Baca baris pertama dengan fgetcsv() sebagai nama kolom.',
           'Baca sisanya dalam loop sampai file habis.',
           'Tampilkan jumlah rekaman, nama kolom, 5 baris pertama, dan tipe data tiap kolom.',
           'Tutup file dengan fclose().']],
    3 => ['soal3_cleaning.php', 'Cleaning dan Min-Max',
          'Mengisi nilai kosong dengan mean, lalu menyeragamkan skala ke 0 sampai 1.',
          ['Hitung mean hanya dari nilai valid: 495 / 6 = 82,5.',
           'Ganti null dan string kosong dengan nilai mean. Gunakan === null, bukan empty(), supaya angka 0 tidak ikut terhapus.',
           'Normalisasi: (x - min) / (max - min), dengan min 65 dan max 100.',
           'Jaga kasus min = max agar tidak terjadi pembagian nol.']],
    4 => ['soal4_statistik.php', 'Statistik dan grafik ASCII',
          'Ringkasan angka sebuah kumpulan nilai, plus histogram dari karakter #.',
          ['Mean: jumlah dibagi banyak data.',
           'Median: nilai tengah setelah data diurutkan.',
           'Modus: nilai dengan frekuensi tertinggi, bisa lebih dari satu atau tidak ada.',
           'Standar deviasi: akar dari varians. Kode menampilkan versi populasi dan sampel.',
           "Hitung frekuensi tiap rentang nilai, cetak dengan str_repeat('#', jumlah)."]],
    5 => ['soal5_split.php', 'Train-test split',
          'Membagi 50 sampel menjadi 80% data latih dan 20% data uji tanpa duplikasi.',
          ['Buat daftar indeks 0 sampai n - 1.',
           'Acak indeks dengan Fisher-Yates, setiap indeks muncul tepat sekali.',
           'Hitung jumlah uji: round(n x test_size) = 10, sisanya 40 untuk latih.',
           'Potong indeks dengan array_slice lalu ambil datanya.',
           'Verifikasi: irisan id train dan test harus kosong.']],
];

function sidikJari(array $soal, string $csv): string
{
    $s = '';
    foreach ($soal as $x) { $f = $x[0];
        $p = __DIR__ . '/' . $f;
        clearstatcache(true, $p);
        $s .= $f . @filemtime($p) . '/' . @filesize($p) . ';';
    }
    clearstatcache(true, $csv);
    return md5($s . @filemtime($csv) . '/' . @filesize($csv));
}

if (isset($_GET['poll'])) {          // dipakai JavaScript untuk mendeteksi perubahan file
    header('Content-Type: text/plain');
    header('Cache-Control: no-store');
    echo sidikJari($soal, $csv);
    exit;
}

function jalankan(string $file): array
{
    $mulai = microtime(true);
    ob_start();
    try {
        (function () use ($file) { include $file; })();   // variabel skrip tidak bocor ke viewer
    } catch (Throwable $e) {
        echo 'ERROR: ' . $e->getMessage() . ' (baris ' . $e->getLine() . ')';
    }
    $out = ob_get_clean();
    return [$out, round((microtime(true) - $mulai) * 1000, 1)];
}

function bacaCsv(string $path): array
{
    $h = @fopen($path, 'r');
    if (!$h) return [[], []];
    $kolom = fgetcsv($h, 0, ',', '"', '\\');
    $baris = [];
    while (($r = fgetcsv($h, 0, ',', '"', '\\')) !== false) {
        if ($r === [null] || count($r) !== count($kolom)) continue;
        $baris[] = array_combine($kolom, $r);
    }
    fclose($h);
    return [$kolom, $baris];
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$tab = $_GET['s'] ?? '1';
$tab = ($tab === 'data') ? 'data' : (isset($soal[(int)$tab]) ? (int)$tab : 1);

ini_set('highlight.comment', '#7E97A0');
ini_set('highlight.default', '#DCE6E8');
ini_set('highlight.html',    '#DCE6E8');
ini_set('highlight.keyword', '#7FC8D6');
ini_set('highlight.string',  '#9FD8A8');
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Praktikum Sains Data: PHP Native</title>
<style>
:root{--bg:#EEF1EE;--grid:#DCE3DF;--ink:#1B2830;--mute:#5A6B72;--card:#FAFBFA;--line:#C9D3CE;--teal:#1F6F6B;--mark:#F2C230;--code:#13222B;--codeink:#DCE6E8}
@media (prefers-color-scheme:dark){:root{--bg:#0F181D;--grid:#17242B;--ink:#E4EAEA;--mute:#92A5AB;--card:#16232A;--line:#2A3C44;--teal:#6CC3BC;--code:#0A1318}}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.55 system-ui,"Segoe UI",sans-serif;
 background-image:linear-gradient(var(--grid) 1px,transparent 1px),linear-gradient(90deg,var(--grid) 1px,transparent 1px);background-size:28px 28px}
.wrap{max-width:1280px;margin:0 auto;padding:28px 20px 60px}
h1{font-size:clamp(26px,4vw,40px);line-height:1.1;margin:0 0 6px;letter-spacing:-.02em}
h1 span{background:linear-gradient(transparent 62%,var(--mark) 62%,var(--mark) 92%,transparent 92%)}
.sub{color:var(--mute);margin:0 0 20px}
nav{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:22px}
nav a{padding:9px 14px;border:1px solid var(--line);border-left:6px solid var(--line);background:var(--card);color:var(--ink);text-decoration:none;border-radius:4px;font-weight:600;font-size:14px}
nav a:hover{border-left-color:var(--teal)}
nav a.on{border-left-color:var(--mark);box-shadow:3px 3px 0 var(--teal)}
.live{float:right;font:500 13px ui-monospace,monospace;color:var(--mute)}
.live i{display:inline-block;width:9px;height:9px;border-radius:50%;background:#3DBE6B;margin-right:6px;animation:p 1.6s infinite}
@keyframes p{50%{opacity:.3}}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:start}
@media (max-width:900px){.grid{grid-template-columns:1fr}}
.panel{background:var(--code);color:var(--codeink);border-radius:6px;overflow:hidden;border:1px solid var(--line);min-width:0}
.ptop{display:flex;justify-content:space-between;gap:10px;padding:8px 12px;border-bottom:1px solid rgba(255,255,255,.1);font:500 12.5px ui-monospace,monospace;color:#7E97A0}
pre,.panel code{margin:0;font:13.5px/1.55 ui-monospace,"Cascadia Mono",Consolas,monospace}
.panel pre,.panel>code{display:block;padding:14px;overflow:auto;max-height:640px;white-space:pre}
.card{background:var(--card);border:1px solid var(--line);border-radius:6px;padding:16px;margin-bottom:18px;overflow:auto}
.card h3{margin:0 0 10px;font-size:18px}
table{border-collapse:collapse;width:100%;font-size:14px}
th,td{padding:6px 10px;text-align:left;border-bottom:1px solid var(--line)}
th{background:var(--grid)}
.kv{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px}
.kv div{padding:8px 14px;border:1px solid var(--line);border-radius:4px;background:var(--bg)}
.kv b{display:block;font-size:20px}
.kv small{color:var(--mute)}
.btn{display:inline-block;margin-top:10px;padding:8px 14px;background:var(--teal);color:var(--card);border-radius:4px;text-decoration:none;font-weight:600}
.uraian{margin-bottom:20px}
.uraian h2{font-size:clamp(22px,3vw,30px);margin:0 0 4px;letter-spacing:-.01em}
.lede{color:var(--mute);margin:0 0 14px}
ol.steps{margin:0;padding:0;list-style:none;counter-reset:s;display:grid;gap:7px}
ol.steps li{counter-increment:s;display:grid;grid-template-columns:28px 1fr;gap:10px;align-items:start}
ol.steps li::before{content:counter(s);width:24px;height:24px;line-height:24px;text-align:center;border-radius:50%;background:var(--teal);color:var(--card);font-weight:700;font-size:13px}
:root{--tr:#2F7F78;--te:#D9822B}
.legend{display:flex;gap:18px;font-size:14px;margin:8px 0}
.legend i{display:inline-block;width:12px;height:12px;border-radius:3px;margin-right:6px;vertical-align:-1px}
.chips{display:flex;flex-wrap:wrap;gap:5px;margin-top:10px}
.chip{width:34px;height:30px;display:grid;place-items:center;border-radius:4px;font:500 12px ui-monospace,monospace;color:#fff}
.chip.tr{background:var(--tr)}.chip.te{background:var(--te)}
</style>
</head>
<body>
<div class="wrap">
<h1>Praktikum Sains Data dengan <span>PHP Native</span></h1>
<p class="sub">Kode dibaca dari file, output dihasilkan dengan menjalankan file itu, dan data dibaca dari CSV. Ubah file, halaman ikut berubah.
 <span class="live"><i></i>auto-update</span></p>

<nav>
<?php foreach ($soal as $n => $x): $judul = $x[1]; ?>
 <a href="?s=<?= $n ?>" class="<?= $tab === $n ? 'on' : '' ?>">Soal <?= $n ?>: <?= e($judul) ?></a>
<?php endforeach; ?>
 <a href="?s=data" class="<?= $tab === 'data' ? 'on' : '' ?>">Dataset CSV</a>
</nav>

<?php if ($tab !== 'data'):
    [$file, $judul, $deskripsi, $langkah] = $soal[$tab];
    $path = $dir . '/' . $file;
    if (!is_file($path)) { echo '<div class="card">File ' . e($file) . ' tidak ditemukan.</div>'; }
    else {
        [$out, $ms] = jalankan($path);
        $baris = count(file($path));
?>
<div class="uraian">
  <h2><?= e($judul) ?></h2>
  <p class="lede"><?= e($deskripsi) ?></p>
  <ol class="steps">
  <?php foreach ($langkah as $l): ?><li><span><?= e($l) ?></span></li><?php endforeach; ?>
  </ol>
</div>
<div class="grid">
  <div class="panel">
    <div class="ptop"><span><?= e($file) ?> (<?= $baris ?> baris)</span><span>diubah <?= date('H:i:s', filemtime($path)) ?></span></div>
    <?= highlight_file($path, true) ?>
  </div>
  <div class="panel">
    <div class="ptop"><span>output hasil menjalankan <?= e($file) ?></span><span><?= $ms ?> ms</span></div>
    <pre><?= e($out) ?></pre>
  </div>
</div>
<?php if ($tab === 5):
    $contoh = [];
    for ($i = 1; $i <= 50; $i++) $contoh[] = ['id' => $i];
    $b = train_test_split($contoh, 0.2, true);   // fungsi dari soal5_split.php
?>
<div class="card" style="margin-top:18px">
  <h3>Coba sendiri</h3>
  <p>Setiap klik menjalankan ulang <code>train_test_split()</code> dari file di atas pada 50 id. Latih <?= count($b['train']) ?>, uji <?= count($b['test']) ?>, irisan <?= count(array_intersect(array_column($b['train'], 'id'), array_column($b['test'], 'id'))) ?>.</p>
  <div class="legend"><span><i style="background:var(--tr)"></i>Data latih</span><span><i style="background:var(--te)"></i>Data uji</span></div>
  <div class="chips">
  <?php foreach ($b['train'] as $r): ?><span class="chip tr"><?= $r['id'] ?></span><?php endforeach; ?>
  <?php foreach ($b['test'] as $r): ?><span class="chip te"><?= $r['id'] ?></span><?php endforeach; ?>
  </div>
  <a class="btn" href="?s=5">Acak ulang</a>
</div>
<?php endif; ?>
<?php } ?>

<?php else:
    [$kolom, $rows] = bacaCsv($csv);
    // jalankan soal 4 dan 5 hanya untuk memuat fungsi-fungsinya, lalu pakai pada data CSV
    jalankan($dir . '/soal4_statistik.php');
    jalankan($dir . '/soal5_split.php');
    $numerik = array_values(array_filter($kolom, function ($k) use ($rows) {
        foreach ($rows as $r) if (!is_numeric($r[$k])) return false;
        return count($rows) > 0 && $k !== 'nim';
    }));
?>
<div class="uraian">
  <h2>Dataset CSV</h2>
  <p class="lede">Halaman ini membaca dataset_mahasiswa.csv langsung dari berkas. Ubah isi CSV, simpan, dan tabel serta statistik di bawah ikut berubah.</p>
  <ol class="steps">
    <li><span>Berkas dibuka dengan fopen() dan dibaca baris demi baris memakai fgetcsv().</span></li>
    <li><span>Kolom numerik dikenali otomatis, kolom nim dilewati karena hanya identitas.</span></li>
    <li><span>Mean, median, modus, standar deviasi, dan histogram dihitung memakai fungsi dari soal4_statistik.php.</span></li>
    <li><span>Pembagian latih dan uji memakai train_test_split() dari soal5_split.php.</span></li>
  </ol>
</div>
<div class="card">
  <h3>dataset_mahasiswa.csv (<?= count($rows) ?> rekaman, diubah <?= is_file($csv) ? date('H:i:s', filemtime($csv)) : '-' ?>)</h3>
  <table>
    <tr><?php foreach ($kolom as $k): ?><th><?= e($k) ?></th><?php endforeach; ?></tr>
    <?php foreach ($rows as $r): ?><tr><?php foreach ($r as $v): ?><td><?= e($v) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
  </table>
</div>

<?php foreach ($numerik as $k):
    $d = array_map('floatval', array_column($rows, $k));
    if (!$d) continue;
    $mod = modus($d);
    ob_start();
    barChartASCII($d, ['< 60' => [0, 59.99], '60-69' => [60, 69.99], '70-79' => [70, 79.99], '80-89' => [80, 89.99], '90-100' => [90, 100]]);
    $grafik = ob_get_clean();
?>
<div class="card">
  <h3>Statistik kolom <code><?= e($k) ?></code> (dihitung memakai fungsi dari soal4_statistik.php)</h3>
  <div class="kv">
    <div><small>Mean</small><b><?= number_format(mean($d), 2) ?></b></div>
    <div><small>Median</small><b><?= number_format(median($d), 2) ?></b></div>
    <div><small>Modus</small><b><?= $mod ? e(implode(', ', $mod)) : '-' ?></b></div>
    <div><small>Std dev (populasi)</small><b><?= number_format(standarDeviasi($d), 2) ?></b></div>
    <div><small>Std dev (sampel)</small><b><?= count($d) > 1 ? number_format(standarDeviasi($d, true), 2) : '-' ?></b></div>
  </div>
  <div class="panel"><pre><?= e($grafik) ?></pre></div>
</div>
<?php endforeach; ?>

<?php
    $hasil = train_test_split($rows, 0.2, true);
    $nimTest = array_column($hasil['test'], 'nim');
    $nimTrain = array_column($hasil['train'], 'nim');
?>
<div class="card">
  <h3>Train-test split pada data CSV (fungsi dari soal5_split.php)</h3>
  <div class="kv">
    <div><small>Total</small><b><?= count($rows) ?></b></div>
    <div><small>Data latih</small><b><?= count($hasil['train']) ?></b></div>
    <div><small>Data uji</small><b><?= count($hasil['test']) ?></b></div>
    <div><small>Irisan</small><b><?= count(array_intersect($nimTrain, $nimTest)) ?></b></div>
  </div>
  <p>NIM data uji: <?= e(implode(', ', $nimTest)) ?></p>
  <a class="btn" href="?s=data">Acak ulang</a>
</div>
<?php endif; ?>
</div>
<script>
(function(){
  var last=null;
  function cek(){
    fetch("?poll=1",{cache:"no-store"}).then(function(r){return r.text()}).then(function(t){
      if(last!==null&&t!==last){location.reload();return}
      last=t;
    }).catch(function(){});
  }
  cek();setInterval(cek,2000);
})();
</script>
</body>
</html>
