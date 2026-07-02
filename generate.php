#!/usr/bin/env php
<?php

/**
 * =====================================================
 * PRIVATE TOOL — DO NOT INCLUDE IN CLIENT DELIVERABLE
 * Run: php generate-activation.php XXXXX-XXXXX-XXXXX-XXXXX
 * =====================================================
 */

// Must match LicenseService constants exactly
const MK = "\x4d\x59\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x32\x30\x32\x35\x21";
const SR = "\x42\x52\x49\x44\x41\x4c\x53\x41\x4c\x54\x58\x39\x39";

if ($argc < 2) {
    echo "\nUsage: php generate-activation.php <INSTALLATION-CODE>\n";
    echo "Example: php generate-activation.php ABCDE-FGHIJ-KLMNO-PQRST\n\n";
    exit(1);
}

$installationCode = strtoupper(trim($argv[1]));
$clean            = str_replace('-', '', $installationCode);

if (strlen($clean) !== 20) {
    echo "\nError: Installation code must be 20 characters (without dashes).\n";
    echo "Got: " . strlen($clean) . " characters\n\n";
    exit(1);
}

// Same derivation as LicenseService::generateActivationCode()
$raw   = hash_hmac('sha256', $clean . MK, SR);
$short = strtoupper(substr($raw, 0, 20));
$code  = implode('-', str_split($short, 5));

echo "\n";
echo "Installation Code : {$installationCode}\n";
echo "Activation Code   : {$code}\n";
echo "\n";
echo "Send the Activation Code to your customer.\n\n";