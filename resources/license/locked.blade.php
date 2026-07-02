<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .lock-card {
            max-width: 440px;
            width: 100%;
            padding: 3rem 2.5rem;
            background: #111118;
            border: 1px solid rgba(220,53,69,0.25);
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 0 80px rgba(220,53,69,0.07);
        }

        .lock-icon {
            width: 72px;
            height: 72px;
            background: rgba(220,53,69,0.08);
            border: 1px solid rgba(220,53,69,0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #dc3545;
        }

        h1 {
            font-size: 1.3rem;
            color: #f0f0f0;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .error-code {
            font-family: monospace;
            font-size: 0.75rem;
            color: #444;
            background: rgba(0,0,0,0.3);
            padding: 0.3rem 0.75rem;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 1.5rem;
            letter-spacing: 0.05em;
        }

        p {
            color: #666;
            font-size: 0.875rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .contact-box {
            background: rgba(184,148,74,0.05);
            border: 1px solid rgba(184,148,74,0.15);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            color: #b8944a;
            font-size: 0.83rem;
        }

        .contact-box strong {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #666;
            margin-bottom: 0.35rem;
        }
    </style>
</head>
<body>
    <div class="lock-card">
        <div class="lock-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        <h1>System Configuration Error</h1>
        <span class="error-code">ERR_SYS_CFG_0x{{ strtoupper(substr(md5(now()), 0, 8)) }}</span>

        <p>
            A critical system configuration mismatch has been detected.
            This software cannot continue running on this system.
            All local data has been secured.
        </p>

        <div class="contact-box">
            <strong>Contact Vendor to Restore</strong>
            Please contact your software provider with the error code above.
            Your data is safe and can be fully restored upon verification.
        </div>
    </div>
</body>
</html>