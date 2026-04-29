<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; line-height: 1.65; margin: 36px; }
        h1 { font-size: 24px; margin: 0 0 24px; }
        .meta { color: #4b5563; margin-bottom: 24px; }
        p { white-space: pre-line; }
    </style>
</head>
<body>
    <h1>{{ $letter['name'] ?? 'Cover Letter' }}</h1>
    <div class="meta">{{ $letter['job_role'] ?? '' }} @if(!empty($letter['company'])) · {{ $letter['company'] }} @endif</div>
    <p>{{ $letter['body'] ?? '' }}</p>
</body>
</html>
