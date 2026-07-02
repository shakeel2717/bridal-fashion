@extends('layouts.bare')

@section('content')
<div class="license-wrapper">
    <div class="license-card">

        <div class="license-header">
            <div class="license-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <h1 class="license-title">Software Activation</h1>
            <p class="license-subtitle">This software requires activation before use</p>
        </div>

        <div class="install-code-section">
            <label class="install-code-label">Your Installation Code</label>
            <div class="install-code-box">{{ $installationCode }}</div>
            <button class="btn-copy" onclick="navigator.clipboard.writeText('{{ $installationCode }}').then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy Code', 1500); })">Copy Code</button>
            <p class="install-code-hint">Send this code to your vendor to receive your activation key.</p>
        </div>

        <div class="license-divider"><span>Enter Activation Code</span></div>

        @if($error)
            <div class="license-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ $error }}
            </div>
        @endif

        <form method="POST" action="{{ route('license.activation.submit') }}" class="activation-form">
            @csrf
            <div class="code-input-group">
                <input
                    type="text"
                    name="activation_code"
                    class="code-input"
                    placeholder="XXXXX-XXXXX-XXXXX-XXXXX"
                    maxlength="24"
                    autocomplete="off"
                    spellcheck="false"
                    autofocus
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '')"
                />
            </div>
            <button type="submit" class="btn-activate">Activate</button>
        </form>

        <div class="license-footer">Need help? Contact your software vendor.</div>

    </div>
</div>

<style>
    body { margin:0; padding:0; background:#0a0a0f; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',system-ui,sans-serif; }
    .license-wrapper { min-height:100vh; width:100%; display:flex; align-items:center; justify-content:center; padding:2rem; background: radial-gradient(ellipse at 20% 50%, rgba(184,148,74,0.08) 0%, transparent 60%), radial-gradient(ellipse at 80% 20%, rgba(26,35,126,0.15) 0%, transparent 60%), #0a0a0f; }
    .license-card { width:100%; max-width:480px; background:#111118; border:1px solid rgba(184,148,74,0.25); border-radius:16px; padding:2.5rem; box-shadow:0 0 60px rgba(0,0,0,0.6); }
    .license-header { text-align:center; margin-bottom:2rem; }
    .license-icon { width:72px; height:72px; background:linear-gradient(135deg,rgba(184,148,74,0.15),rgba(184,148,74,0.05)); border:1px solid rgba(184,148,74,0.3); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; color:#b8944a; }
    .license-title { font-size:1.4rem; font-weight:700; color:#f0f0f0; margin:0 0 0.4rem; }
    .license-subtitle { font-size:0.85rem; color:#666; margin:0; }
    .install-code-section { background:rgba(184,148,74,0.05); border:1px solid rgba(184,148,74,0.2); border-radius:10px; padding:1.25rem; margin-bottom:1.5rem; text-align:center; }
    .install-code-label { display:block; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:0.75rem; }
    .install-code-box { font-family:'Courier New',monospace; font-size:1.2rem; font-weight:700; color:#b8944a; letter-spacing:0.15em; background:rgba(0,0,0,0.3); border:1px solid rgba(184,148,74,0.15); border-radius:8px; padding:0.75rem 1rem; margin-bottom:0.75rem; user-select:all; }
    .btn-copy { background:transparent; border:1px solid rgba(184,148,74,0.3); color:#b8944a; font-size:0.78rem; padding:0.35rem 1rem; border-radius:6px; cursor:pointer; transition:all 0.2s; margin-bottom:0.75rem; }
    .btn-copy:hover { background:rgba(184,148,74,0.1); }
    .install-code-hint { font-size:0.78rem; color:#555; margin:0; line-height:1.5; }
    .license-divider { display:flex; align-items:center; gap:1rem; margin:1.5rem 0; color:#444; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; }
    .license-divider::before, .license-divider::after { content:''; flex:1; height:1px; background:rgba(255,255,255,0.06); }
    .activation-form { display:flex; flex-direction:column; gap:1rem; }
    .license-error { background:rgba(220,53,69,0.1); border:1px solid rgba(220,53,69,0.3); color:#f08080; border-radius:8px; padding:0.75rem 1rem; font-size:0.83rem; display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem; }
    .code-input { width:100%; background:#0d0d14; border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:0.85rem 1rem; color:#f0f0f0; font-family:'Courier New',monospace; font-size:1rem; letter-spacing:0.12em; text-align:center; outline:none; transition:border-color 0.2s; box-sizing:border-box; }
    .code-input:focus { border-color:rgba(184,148,74,0.5); box-shadow:0 0 0 3px rgba(184,148,74,0.08); }
    .code-input::placeholder { color:#333; }
    .btn-activate { width:100%; padding:0.9rem; background:linear-gradient(135deg,#b8944a,#9a7a3a); border:none; border-radius:10px; color:#fff; font-size:0.95rem; font-weight:600; cursor:pointer; transition:all 0.2s; }
    .btn-activate:hover { background:linear-gradient(135deg,#c9a555,#b8944a); box-shadow:0 4px 20px rgba(184,148,74,0.25); }
    .license-footer { text-align:center; margin-top:1.5rem; font-size:0.78rem; color:#444; }
</style>
@endsection