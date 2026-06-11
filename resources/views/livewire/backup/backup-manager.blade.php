<div>
    @if (session('success'))
        <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="page-header-sticky">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="page-title">Database Backup</div>
                <div class="page-subtitle">SQLite backup & restore</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Take Backup --}}
        <div class="col-4">
            <div class="table-card" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px;">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Take New Backup
                </div>

                <div class="mb-3">
                    <label class="form-label">Note (optional)</label>
                    <input type="text" wire:model="note" class="form-control"
                        placeholder="e.g. before adding new products">
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                        Short note to identify this backup later.
                    </div>
                </div>

                <button wire:click="takeBackup" wire:loading.attr="disabled" class="btn btn-primary w-100"
                    style="height:44px; font-weight:700;">
                    <span wire:loading wire:target="takeBackup">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                    </span>
                    <i class="bi bi-database-add me-2"></i>
                    Take Backup Now
                </button>

                <div
                    style="margin-top:16px; background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:12px; font-size:12px; color:#c53030;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Restore Warning:</strong> Restoring a backup will replace all current data.
                    An automatic backup is always saved before any restore.
                </div>
            </div>

            {{-- Stats --}}
            <div class="table-card mt-3" style="padding:20px;">
                <div
                    style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px;">
                    <i class="bi bi-info-circle me-1"></i> Info
                </div>
                <div style="font-size:13px; line-height:2;">
                    <div class="d-flex justify-content-between">
                        <span style="color:var(--text-muted);">Total Backups</span>
                        <strong>{{ count($backups) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color:var(--text-muted);">Manual Backups</span>
                        <strong>{{ collect($backups)->where('is_auto', false)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color:var(--text-muted);">Auto Backups</span>
                        <strong>{{ collect($backups)->where('is_auto', true)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color:var(--text-muted);">Storage Location</span>
                        <strong style="font-size:11px; font-family:monospace;">storage/app/backups/</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Backup List --}}
        <div class="col-8">
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">Backup History</span>
                    <span style="font-size:12px; color:var(--text-muted);">
                        Read from storage — never lost during restore
                    </span>
                </div>

                <table class="table mb-0" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center;">Sr</th>
                            <th>Date & Time</th>
                            <th>Note</th>
                            <th>Size</th>
                            <th>Type</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $i => $backup)
                            <tr style="{{ $backup['is_auto'] ? 'background:rgba(0,0,0,0.02);' : '' }}">
                                <td style="text-align:center; color:var(--text-muted); font-weight:600;">
                                    {{ $i + 1 }}
                                </td>
                                <td>
                                    <div style="font-weight:700; font-family:monospace; font-size:12px;">
                                        {{ $backup['datetime'] ? $backup['datetime']->format('d/m/Y') : '—' }}
                                    </div>
                                    <div style="font-size:11px; color:var(--text-muted);">
                                        {{ $backup['datetime'] ? $backup['datetime']->format('H:i:s') : '' }}
                                    </div>
                                    <div style="font-size:10px; color:var(--text-muted); margin-top:2px;">
                                        {{ $backup['datetime'] ? $backup['datetime']->diffForHumans() : '' }}
                                    </div>
                                </td>
                                <td>
                                    @if ($backup['note'])
                                        <span style="font-size:12px; color:#000;">{{ $backup['note'] }}</span>
                                    @else
                                        <span style="font-size:11px; color:var(--text-muted);">No note</span>
                                    @endif
                                </td>
                                <td style="font-size:12px; font-family:monospace;">
                                    {{ $backup['size'] }}
                                </td>
                                <td>
                                    @if ($backup['is_auto'])
                                        <span
                                            style="font-size:10px; background:#ebf8ff; color:#2c5282; padding:2px 8px; border-radius:4px; font-weight:700;">
                                            Auto
                                        </span>
                                    @else
                                        <span
                                            style="font-size:10px; background:#f0fff4; color:#276749; padding:2px 8px; border-radius:4px; font-weight:700;">
                                            Manual
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('backup.download', $backup['filename']) }}"
                                            class="btn btn-sm btn-outline-secondary action-btn" title="Download backup">
                                            <i class="bi bi-download" style="font-size:12px;"></i>
                                        </a>
                                        <button wire:click="confirmRestore('{{ $backup['filename'] }}')"
                                            class="btn btn-sm btn-outline-warning action-btn"
                                            title="Restore this backup">
                                            <i class="bi bi-arrow-counterclockwise" style="font-size:12px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    style="text-align:center; padding:40px; color:var(--text-muted); font-size:13px;">
                                    <i class="bi bi-database"
                                        style="font-size:36px; display:block; margin-bottom:10px; color:var(--gold);"></i>
                                    No backups yet. Take your first backup above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Restore Confirm Modal --}}
    @if ($showRestore)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content">
                    <div class="modal-header" style="background:#fff5f5; border-bottom:1px solid #fed7d7;">
                        <h6 class="modal-title" style="color:#c53030;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Restore
                        </h6>
                    </div>
                    <div class="modal-body" style="font-size:13px;">
                        <p>You are about to restore:</p>
                        <div
                            style="background:#f7fafc; border:1px solid var(--border); border-radius:8px; padding:10px 14px; font-family:monospace; font-size:12px; word-break:break-all; margin-bottom:12px;">
                            {{ $restoreName }}
                        </div>
                        <div
                            style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:10px 14px; font-size:12px; color:#c53030;">
                            <i class="bi bi-shield-exclamation me-1"></i>
                            <strong>All current data will be replaced.</strong><br>
                            An automatic backup of current data will be saved first.
                        </div>
                    </div>
                    <div class="modal-footer gap-2">
                        <button class="btn btn-sm btn-outline-secondary"
                            wire:click="$set('showRestore', false)">Cancel</button>
                        <button class="btn btn-sm btn-danger" wire:click="restore" wire:loading.attr="disabled">
                            <span wire:loading wire:target="restore">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </span>
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Yes, Restore
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
