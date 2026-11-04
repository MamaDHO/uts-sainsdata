<?php
$namaFile = 'dataset_mahasiswa.csv';

// (Opsional) buat data sampel bila file asli belum ada
if (!file_exists($namaFile)) {
    $sampel = "nim,nama,nilai_tugas,nilai_ujian\n"
        . "2301001,Andi,85,78\n2301002,Budi,90,82\n2301003,Citra,75,88\n"
        . "2301004,Dewi,80,70\n2301005,Eko,95,91\n2301006,Fajar,70,65\n"
        . "2301007,Gita,88,84\n2301008,Hadi,60,72\n";
    file_put_contents($namaFile, $sampel);
}

// Deteksi tipe data satu kolom
function deteksiTipe(array $nilai): string
{
    $terisi = array_filter($nilai, fn($v) => trim((string)$v) !== '');
    if (count($terisi) === 0) return 'kosong';

    $semuaAngka = true;
    $semuaInt   = true;
    foreach ($terisi as $v) {
        $v = trim($v);
        if (!is_numeric($v)) { $semuaAngka = false; $semuaInt = false; break; }
        if (!preg_match('/^-?\d+$/', $v)) $semuaInt = false;
    }
    if (!$semuaAngka) return 'string';
    return $semuaInt ? 'integer' : 'float';
}

// ===== BACA CSV =====
$handle = fopen($namaFile, 'r');
if ($handle === false) {
    die("Gagal membuka file {$namaFile}" . PHP_EOL);
}

$header = fgetcsv($handle, 0, ',', '"', '\\');   // baris pertama = nama kolom
$data = [];
while (($baris = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
    if ($baris === [null]) continue;              // lewati baris kosong
    $data[] = array_combine($header, $baris);
}
fclose($handle);

// ===== TAMPILKAN HASIL EDA =====
echo "1. Jumlah total rekaman : " . count($data) . PHP_EOL . PHP_EOL;

echo "2. Daftar nama kolom    : " . implode(', ', $header) . PHP_EOL . PHP_EOL;

echo "3. Pratinjau 5 baris pertama (head):" . PHP_EOL;
echo implode("\t", $header) . PHP_EOL;
foreach (array_slice($data, 0, 5) as $baris) {
    echo implode("\t", $baris) . PHP_EOL;
}

echo PHP_EOL . "4. Tipe data tiap kolom:" . PHP_EOL;
foreach ($header as $kolom) {
    $nilaiKolom = array_column($data, $kolom);
    $tipe = deteksiTipe($nilaiKolom);
    $kosong = count(array_filter($nilaiKolom, fn($v) => trim($v) === ''));
    printf("   %-12s : %-8s (missing: %d)\n", $kolom, $tipe, $kosong);
}
