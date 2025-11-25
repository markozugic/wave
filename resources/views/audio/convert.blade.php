<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audio Converter</title>
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;line-height:1.5;margin:0;padding:2rem;background:#f8fafc;color:#0f172a}
        .container{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px}
        h1{font-size:1.5rem;margin:0 0 1rem}
        .field{margin-bottom:1rem}
        label{display:block;font-weight:600;margin-bottom:0.5rem}
        input[type="file"],select{display:block;width:100%;padding:0.5rem;border:1px solid #cbd5e1;border-radius:6px;background:#fff}
        .error{color:#b91c1c;font-size:0.9rem;margin-top:0.25rem}
        .alert{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca;border-radius:6px;padding:0.75rem;margin-bottom:1rem}
        .actions{display:flex;gap:.5rem;align-items:center}
        button{background:#0ea5e9;border:none;color:#fff;padding:.6rem 1rem;border-radius:6px;cursor:pointer}
        a.button{display:inline-block;text-decoration:none;background:#64748b;color:#fff;padding:.6rem 1rem;border-radius:6px}
        .note{font-size:.9rem;color:#475569}
    </style>
</head>
<body>
<div class="container">
    <h1>Convert WAV to another audio format</h1>

    @if ($errors->any())
        <div class="alert">
            <strong>There were some problems with your submission:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('audio.convert.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="field">
            <label for="audio">WAV file</label>
            <input id="audio" type="file" name="audio" accept="audio/wav" required>
            @error('audio')
            <div class="error">{{ $message }}</div>
            @enderror
            <p class="note">Only .wav files are accepted.</p>
        </div>

        <div class="field">
            <label for="format">Convert to</label>
            <select id="format" name="format" required>
                @foreach(($formats ?? ['mp3','aac','ogg','flac']) as $fmt)
                    <option value="{{ $fmt }}" @selected(old('format')===$fmt)>{{ strtoupper($fmt) }}</option>
                @endforeach
            </select>
            @error('format')
            <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="actions">
            <button type="submit">Convert & Download</button>
            <a class="button" href="{{ url('/') }}">Back</a>
        </div>
    </form>

    <p class="note" style="margin-top:1rem;">FFmpeg must be installed and available on the server. The converted file will download automatically.</p>
</div>
</body>
</html>
