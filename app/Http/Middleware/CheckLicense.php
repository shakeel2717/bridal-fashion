<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function __construct(protected LicenseService $license) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Always allow license routes and Livewire internal routes (file uploads etc.)
        if ($request->routeIs('license.*') || $request->is('livewire*')) {
            return $next($request);
        }

        // ── Step 1: First boot — no installation code yet ──────────────────
        if (! $this->license->isInstalled()) {
            $code = $this->license->firstBoot();
            return redirect()->route('license.activation')->with('installation_code', $code);
        }

        // ── Step 2: Tamper check — machine fingerprint changed ──────────────
        if ($this->license->isTampered()) {
            // Backup then nuke — no mercy
            $this->license->executeTamperResponse();
            return redirect()->route('license.locked')->with('reason', 'tampered');
        }

        // ── Step 3: Activation check — valid activation code must exist ─────
        if (! $this->license->isActivated()) {
            return redirect()->route('license.activation');
        }

        return $next($request);
    }
}
