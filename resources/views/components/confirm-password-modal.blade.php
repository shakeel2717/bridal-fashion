@props([
    'show'          => false,
    'title'         => 'Confirm Action',
    'subtitle'      => 'This action cannot be undone. Please enter your password to continue.',
    'passwordModel' => 'cancelPassword',
    'errorModel'    => 'cancelPasswordError',
    'confirmAction' => 'confirmWithPassword',
    'cancelAction'  => '$set(\'showCancelConfirm\', false)',
    'confirmLabel'  => 'Confirm',
    'confirmClass'  => 'btn-danger',
])

@if($show)
<div class="confirm-modal-overlay" wire:click.self="{{ $cancelAction }}">
    <div class="confirm-modal-box">
        <div class="confirm-title">
            <i class="bi bi-shield-lock me-2" style="color:#e53e3e;"></i>
            {{ $title }}
        </div>
        <div class="confirm-subtitle">{{ $subtitle }}</div>

        <div class="confirm-password-input">
            <label class="form-label">Your Password <span class="text-danger">*</span></label>
            <input type="password"
                   wire:model="{{ $passwordModel }}"
                   wire:keydown.enter="{{ $confirmAction }}"
                   class="form-control {{ $errorModel && $errors->has($errorModel) ? 'is-invalid' : '' }}"
                   placeholder="Enter your password"
                   autofocus>
            @if($errorModel)
                @error($errorModel)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif

            {{-- Show wire model error --}}
            @if($show)
            <div wire:key="pass-error">
                @php $errProp = $errorModel; @endphp
                @if(!empty($this->$errProp ?? ''))
                    <div style="color:#e53e3e; font-size:12px; margin-top:4px;">
                        {{ $this->$errProp ?? '' }}
                    </div>
                @endif
            </div>
            @endif
        </div>

        <div class="confirm-actions">
            <button class="btn btn-sm btn-outline-secondary"
                    wire:click="{{ $cancelAction }}">
                Cancel
            </button>
            <button class="btn btn-sm {{ $confirmClass }}"
                    wire:click="{{ $confirmAction }}"
                    wire:loading.attr="disabled">
                <span wire:loading wire:target="{{ $confirmAction }}">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                </span>
                {{ $confirmLabel }}
            </button>
        </div>
    </div>
</div>
@endif