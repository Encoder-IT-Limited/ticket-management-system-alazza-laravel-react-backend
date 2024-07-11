<?php

function perPage($perPage = 15)
{
    $perPage = request('per_page', $perPage);
    if ($perPage < 1) $perPage = 100000000; // get all data
    return $perPage;
}

function formatDateTime($dateTime): string
{
    return $dateTime->format('Y-m-d H:i:s');
}

function remove_($value): array|string
{
    return str_replace('_', ' ', $value);
}
