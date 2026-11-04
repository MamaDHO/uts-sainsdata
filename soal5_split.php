<?php
function train_test_split(array $data, float $test_size = 0.2, bool $shuffle = true, ?int $seed = null): array
{
    $data = array_values($data);
    $n = count($data);

    if ($test_size <= 0 || $test_size >= 1) {
        throw new InvalidArgumentException('test_size harus di antara 0 dan 1.');
    }

    if ($seed !== null) mt_srand($seed);        // opsional: hasil dapat direproduksi

    // 1. Buat daftar indeks
    $indeks = range(0, $n - 1);

    // 2. Fisher-Yates shuffle pada INDEKS
    if ($shuffle) {
        for ($i = $n - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$indeks[$i], $indeks[$j]] = [$indeks[$j], $indeks[$i]];
        }
    }

    // 3. Hitung ukuran subset
    $jumlahTest  = (int) round($n * $test_size);
    $jumlahTrain = $n - $jumlahTest;

    // 4. Bentuk subset dari indeks
    $indeksTrain = array_slice($indeks, 0, $jumlahTrain);
    $indeksTest  = array_slice($indeks, $jumlahTrain);

    return [
        'train' => array_map(fn($i) => $data[$i], $indeksTrain),
        'test'  => array_map(fn($i) => $data[$i], $indeksTest),
    ];
}

// ===== DATASET 50 SAMPEL (array 2 dimensi) =====
$dataset = [];
for ($i = 1; $i <= 50; $i++) {
    $dataset[] = [
        'id'     => $i,
        'fitur1' => mt_rand(50, 100),
        'fitur2' => mt_rand(50, 100),
        'label'  => mt_rand(0, 1),
    ];
}

// ===== PEMBAGIAN =====
$hasil = train_test_split($dataset, 0.2, true);
$train = $hasil['train'];
$test  = $hasil['test'];

echo "Total data      : " . count($dataset) . PHP_EOL;
echo "Data latih      : " . count($train) . " baris (" . (count($train) / count($dataset) * 100) . "%)" . PHP_EOL;
echo "Data uji        : " . count($test)  . " baris (" . (count($test)  / count($dataset) * 100) . "%)" . PHP_EOL;

// ===== VERIFIKASI TIDAK ADA DUPLIKASI =====
$idTrain = array_column($train, 'id');
$idTest  = array_column($test, 'id');
$irisan  = array_intersect($idTrain, $idTest);

echo "Irisan train&test : " . count($irisan) . " (harus 0)" . PHP_EOL;
echo "ID data uji       : " . implode(', ', $idTest) . PHP_EOL;
