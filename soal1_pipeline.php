<?php
// ===== TAHAP 1: DATA MENTAH (sengaja ada baris bermasalah) =====
$dataMentah = [
    ['id' => 1, 'produk' => 'Laptop',   'jumlah' => 2,    'harga' => 8000000],
    ['id' => 2, 'produk' => 'mouse',    'jumlah' => 5,    'harga' => 150000],
    ['id' => 3, 'produk' => 'Keyboard', 'jumlah' => -1,   'harga' => 300000],   // jumlah negatif
    ['id' => 4, 'produk' => '',         'jumlah' => 3,    'harga' => 100000],   // produk kosong
    ['id' => 5, 'produk' => 'Monitor',  'jumlah' => '4',  'harga' => '2500000'],// string numerik (masih valid)
    ['id' => 6, 'produk' => 'Webcam',   'jumlah' => 2],                         // kunci 'harga' hilang
    ['id' => 7, 'produk' => 'Headset',  'jumlah' => 'abc','harga' => 250000],   // bukan angka
];

// ===== TAHAP 2: VALIDASI STRUKTUR 1 BARIS =====
function validasiBaris($baris): bool
{
    if (!is_array($baris)) return false;

    foreach (['id', 'produk', 'jumlah', 'harga'] as $kunci) {
        if (!array_key_exists($kunci, $baris)) return false;
    }
    if (!is_string($baris['produk']) || trim($baris['produk']) === '') return false;
    if (!is_numeric($baris['jumlah']) || $baris['jumlah'] <= 0)        return false;
    if (!is_numeric($baris['harga'])  || $baris['harga']  <= 0)        return false;

    return true;
}

// ===== TAHAP 3-5: PIPELINE =====
function jalankanPipeline(array $mentah): array
{
    $valid = [];
    $ditolak = [];

    // Tahap saring
    foreach ($mentah as $indeks => $baris) {
        if (validasiBaris($baris)) {
            $valid[] = $baris;
        } else {
            $ditolak[] = ['baris_ke' => $indeks + 1, 'data' => $baris];
        }
    }

    // Tahap proses: standarisasi tipe & fitur turunan
    $diproses = array_map(function ($b) {
        $jumlah = (int) $b['jumlah'];
        $harga  = (float) $b['harga'];
        return [
            'id'     => (int) $b['id'],
            'produk' => ucwords(strtolower(trim($b['produk']))),
            'jumlah' => $jumlah,
            'harga'  => $harga,
            'total'  => $jumlah * $harga,
        ];
    }, $valid);

    // Siap analisis: dataset final yang sudah bersih
    $siapAnalisis = $diproses;

    return [
        'ditolak'       => $ditolak,
        'siap_analisis' => $siapAnalisis,
        'laporan'       => [
            'Data masuk (mentah)' => count($mentah),
            'Valid'               => count($valid),
            'Ditolak'             => count($ditolak),
            'Diproses'            => count($diproses),
            'Siap analisis'       => count($siapAnalisis),
        ],
    ];
}

// ===== OUTPUT =====
$hasil = jalankanPipeline($dataMentah);

echo "=== LAPORAN STATUS PIPELINE ===" . PHP_EOL;
foreach ($hasil['laporan'] as $tahap => $jumlah) {
    printf("%-22s : %d\n", $tahap, $jumlah);
}

echo PHP_EOL . "--- Baris ditolak ---" . PHP_EOL;
foreach ($hasil['ditolak'] as $d) {
    echo "Baris ke-{$d['baris_ke']}: " . json_encode($d['data']) . PHP_EOL;
}

echo PHP_EOL . "--- Data siap analisis ---" . PHP_EOL;
foreach ($hasil['siap_analisis'] as $r) {
    printf("#%d %-10s qty=%d harga=%s total=%s\n",
        $r['id'], $r['produk'], $r['jumlah'],
        number_format($r['harga'], 0, ',', '.'),
        number_format($r['total'], 0, ',', '.'));
}
