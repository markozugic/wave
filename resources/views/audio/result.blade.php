<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conversion Complete</title>
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;line-height:1.5;margin:0;padding:2rem;background:#f8fafc;color:#0f172a}
        .container{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px}
        h1{font-size:1.5rem;margin:0 0 1rem}
        p{margin:.25rem 0}
        .actions{display:flex;gap:.5rem;align-items:center;margin-top:1rem}
        a.button,button{display:inline-block;text-decoration:none;background:#0ea5e9;color:#fff;padding:.6rem 1rem;border-radius:6px;border:none;cursor:pointer}
        a.secondary{background:#64748b}
        .note{font-size:.9rem;color:#475569;margin-top:.5rem}
        .filebox{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:.75rem;margin:.75rem 0}
        .muted{color:#475569}
    </style>
    <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="container">
    <h1>Your audio is ready</h1>

    <div class="filebox">
        <p><strong>Format:</strong> {{ strtoupper($format ?? '') }}</p>
        <p><strong>File:</strong> {{ $filename ?? '' }}</p>
        @if(!empty($filesize))
            <p class="muted"><strong>Size:</strong> {{ number_format(($filesize/1024/1024), 2) }} MB</p>
        @endif
    </div>

    <div class="actions">
        <a class="button" href="{{ $downloadUrl }}" download="converted-{{ $filename }}">Download file</a>
        <a class="button secondary" href="{{ route('audio.convert.create') }}">Convert another</a>
        <a class="button secondary" href="{{ url('/') }}">Home</a>
    </div>

</div>
</body>
</html>
