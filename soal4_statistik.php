<?php
function mean(array $d): float
{
    return array_sum($d) / count($d);
}

function median(array $d): float
{
    sort($d);
    $n = count($d);
    $tengah = intdiv($n, 2);
    return ($n % 2 === 0) ? ($d[$tengah - 1] + $d[$tengah]) / 2 : $d[$tengah];
}

function modus(array $d): array
{
    $frek = [];
    foreach ($d as $v) {
        $k = (string) $v;
        $frek[$k] = ($frek[$k] ?? 0) + 1;
    }
    $maks = max($frek);
    if ($maks === 1) return [];                // semua unik -> tidak ada modus

    $hasil = [];
    foreach ($frek as $k => $f) {
        if ($f === $maks) $hasil[] = $k + 0;
    }
    return $hasil;
}

function varians(array $d, bool $sampel = false): float
{
    $m = mean($d);
    $jumlahKuadrat = 0.0;
    foreach ($d as $v) {
        $jumlahKuadrat += ($v - $m) ** 2;
    }
    $pembagi = $sampel ? count($d) - 1 : count($d);
    return $jumlahKuadrat / $pembagi;
}

function standarDeviasi(array $d, bool $sampel = false): float
{
    return sqrt(varians($d, $sampel));
}

function barChartASCII(array $d, array $rentang): void
{
    echo PHP_EOL . "Distribusi Frekuensi:" . PHP_EOL;
    foreach ($rentang as $label => [$bawah, $atas]) {
        $jumlah = count(array_filter($d, fn($v) => $v >= $bawah && $v <= $atas));
        printf("%-8s | %-12s (%d)\n", $label, str_repeat('#', $jumlah), $jumlah);
    }
}

// ===== DATA =====
$data = [60, 65, 70, 72, 75, 75, 78, 80, 82, 85, 85, 85, 88, 90, 92, 95, 98, 100];

// ===== OUTPUT =====
echo "Data  : " . implode(', ', $data) . PHP_EOL;
echo "N     : " . count($data) . PHP_EOL . PHP_EOL;

printf("Mean                    : %.2f\n", mean($data));
printf("Median                  : %.2f\n", median($data));
$mod = modus($data);
echo   "Modus                   : " . (empty($mod) ? 'tidak ada' : implode(', ', $mod)) . PHP_EOL;
printf("Std Deviasi (populasi)  : %.2f\n", standarDeviasi($data));
printf("Std Deviasi (sampel)    : %.2f\n", standarDeviasi($data, true));

barChartASCII($data, [
    '< 60'   => [0, 59.99],
    '60-69'  => [60, 69.99],
    '70-79'  => [70, 79.99],
    '80-89'  => [80, 89.99],
    '90-100' => [90, 100],
]);
