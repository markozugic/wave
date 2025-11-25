<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Vorbis;
use FFMpeg\Format\Audio\Flac;

class AudioConvertController extends Controller
{
    /**
     * Show the audio conversion form.
     */
    public function create()
    {
        $formats = ['mp3', 'aac', 'ogg', 'flac'];

        return view('audio.convert', compact('formats'));
    }

    /**
     * Handle the uploaded WAV file and convert it to the selected format.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'audio' => [
                'required',
                'file',
                // Restrict to WAV
                'mimes:wav',
                // Some browsers provide more specific mime types; include a wider set for reliability
                'mimetypes:audio/wav,audio/x-wav,audio/wave,audio/vnd.wave',
            ],
            'format' => 'required|in:mp3,aac,ogg,flac',
        ]);

        $uploaded = $validated['audio'];
        $targetFormat = $validated['format'];

        // Store the uploaded file in a temporary location
        $tmpDir = 'tmp/audio';
        Storage::makeDirectory($tmpDir);

        $inputFilename = Str::uuid()->toString() . '.wav';
        $inputPath = Storage::path($tmpDir . '/' . $inputFilename);
        $uploaded->move(dirname($inputPath), basename($inputPath));

        // Prepare output path
        $baseName = pathinfo($inputFilename, PATHINFO_FILENAME);
        $outputFilename = $baseName . '.' . $targetFormat;
        $outputPath = Storage::path($tmpDir . '/' . $outputFilename);

        // Initialize FFMpeg - relies on ffmpeg/ffprobe binaries available in PATH
        $ffmpeg = FFMpeg::create();
        $audio = $ffmpeg->open($inputPath);

        // Select appropriate format
        $format = match ($targetFormat) {
            'mp3' => (new Mp3())->setAudioKiloBitrate(192),
            'aac' => (new Aac())->setAudioKiloBitrate(192),
            'ogg' => (new Vorbis())->setAudioKiloBitrate(160),
            'flac' => new Flac(),
            default => (new Mp3()),
        };

        // Save converted file
        $audio->save($format, $outputPath);

        // Also schedule deletion of input (uploaded) file only — keep the converted output
        register_shutdown_function(function () use ($inputPath) {
            if (file_exists($inputPath)) {
                @unlink($inputPath);
            }
        });

        // Redirect to a dedicated result page to allow a clean PRG (Post/Redirect/Get) flow
        return redirect()->route('audio.convert.result', ['filename' => $outputFilename]);
    }

    /**
     * Serve the converted file for download by filename from tmp/audio.
     */
    public function download(string $filename)
    {
        $tmpDir = 'tmp/audio';
        // Basic allowlist for extensions as an extra safeguard
        if (!preg_match('/^[A-Za-z0-9\-]+\.(mp3|aac|ogg|flac)$/', $filename)) {
            abort(404);
        }

        $path = $tmpDir . '/' . $filename;
        if (!Storage::exists($path)) {
            abort(404);
        }

        // Force a download in the browser with explicit headers using BinaryFileResponse
        $downloadName = 'converted-' . $filename;
        $absolutePath = Storage::path($path);

        // Use BinaryFileResponse to avoid any filesystem layer altering our headers
        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($absolutePath);
        // Set content type strictly to octet-stream to discourage inline playback
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Add cache headers to reduce intermediary transformations
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Let Symfony set the Content-Length automatically based on the file, if possible
        // Explicitly set content disposition to attachment with a UTF-8 safe fallback
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $downloadName,
            $downloadName
        );

        // Prepare the response for the current request (sets headers like Content-Length)
        $response->prepare(request());

        return $response;
    }

    /**
     * Show the result page for a converted file by filename.
     */
    public function result(string $filename)
    {
        $tmpDir = 'tmp/audio';
        // Basic allowlist for extensions as an extra safeguard
        if (!preg_match('/^[A-Za-z0-9\-]+\.(mp3|aac|ogg|flac)$/', $filename)) {
            abort(404);
        }

        $relativePath = $tmpDir . '/' . $filename;
        if (!Storage::exists($relativePath)) {
            abort(404);
        }

        // Derive format from extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $downloadUrl = route('audio.convert.download', ['filename' => $filename]);

        $filesize = null;
        try {
            $filesize = Storage::size($relativePath);
        } catch (\Throwable $e) {
            // ignore size errors
        }

        return view('audio.result', [
            'format' => $ext,
            'filename' => $filename,
            'downloadUrl' => $downloadUrl,
            'filesize' => $filesize,
        ]);
    }
}
