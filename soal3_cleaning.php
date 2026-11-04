<?php
$nilai = [80, null, 75, "", 90, null, 65, 85, 100];

// Cek apakah sebuah elemen dianggap missing
function isMissing($v): bool
{
    return $v === null || $v === '';
}

// 1. Mean dari nilai valid saja
function hitungMeanValid(array $data): float
{
    $valid = array_filter($data, fn($v) => !isMissing($v));
    if (count($valid) === 0) return 0.0;
    return array_sum($valid) / count($valid);
}

// 2. Imputasi mean
function imputasiMean(array $data, float $mean): array
{
    return array_map(fn($v) => isMissing($v) ? $mean : $v, $data);
}

// 3. Normalisasi Min-Max ke [0, 1]
function normalisasiMinMax(array $data): array
{
    $min = min($data);
    $max = max($data);
    $rentang = $max - $min;

    if ($rentang == 0) {                       // hindari pembagian nol
        return array_map(fn($v) => 0.0, $data);
    }
    return array_map(fn($v) => ($v - $min) / $rentang, $data);
}

// ===== EKSEKUSI =====
$mean       = hitungMeanValid($nilai);
$terimputasi = imputasiMean($nilai, $mean);
$ternormal   = normalisasiMinMax($terimputasi);

echo "Data asli       : " . json_encode($nilai) . PHP_EOL;
echo "Mean (valid)    : " . $mean . PHP_EOL;
echo "Setelah imputasi: " . json_encode($terimputasi) . PHP_EOL;
echo "Min = " . min($terimputasi) . ", Max = " . max($terimputasi) . PHP_EOL . PHP_EOL;

echo "Hasil Normalisasi Min-Max:" . PHP_EOL;
foreach ($terimputasi as $i => $asli) {
    printf("  %6.1f  ->  %.4f\n", $asli, $ternormal[$i]);
}
