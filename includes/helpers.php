<?php

function dc_categories(): array
{
    return [
        'income' => ['Gaji', 'Freelance', 'Bonus', 'Investasi', 'Lainnya'],
        'expense' => ['Makanan', 'Transportasi', 'Belanja', 'Tagihan', 'Hiburan', 'Lainnya'],
    ];
}

function dc_rupiah($value): string
{
    $sign = ((float) $value) < 0 ? '-' : '';
    return $sign . 'Rp ' . number_format(abs((float) $value), 0, ',', '.');
}

function dc_tanggal($date): string
{
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
              'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

    $timestamp = strtotime((string) $date);
    if ($timestamp === false) {
        return '-';
    }

    return sprintf(
        '%02d %s %s',
        (int) date('d', $timestamp),
        $bulan[(int) date('n', $timestamp) - 1],
        date('Y', $timestamp)
    );
}
