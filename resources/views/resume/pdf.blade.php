<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 42px; }
        body {
            color: #111827;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.42;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        h1 {
            border-bottom: 2px solid #111827;
            font-size: 26px;
            letter-spacing: 0;
            margin: 0 0 14px;
            padding-bottom: 9px;
            text-transform: uppercase;
        }
        h2 {
            color: #0f766e;
            font-size: 12px;
            letter-spacing: 0;
            margin: 18px 0 7px;
            text-transform: uppercase;
        }
        p { margin: 0 0 8px; overflow-wrap: anywhere; word-break: break-word; }
        ul { margin: 6px 0 0 18px; padding: 0; }
        li { margin-bottom: 4px; overflow-wrap: anywhere; word-break: break-word; }
        .skills span {
            display: inline-block;
            margin: 0 6px 6px 0;
        }
        .item { margin-bottom: 12px; }
        .item-title { font-weight: bold; }
        .muted { color: #4b5563; }
        .contact { color: #4b5563; margin: -6px 0 12px; }
        .project-description { display: block; color: #4b5563; font-size: 11px; margin-top: 2px; }
    </style>
</head>
<body>
    @php
        $text = function ($value) {
            if (is_array($value)) {
                return collect($value)->flatten()->filter(fn ($part) => is_scalar($part) && trim((string) $part) !== '')->join(' - ');
            }

            return trim((string) ($value ?? ''));
        };
    @endphp

    <h1>{{ $resume['name'] ?: 'Your Name' }}</h1>
    @if(!empty($resume['contact']) || !empty($resume['address']))
        <p class="contact">{{ $resume['contact'] ?? '' }} @if(!empty($resume['address'])) | {{ $resume['address'] }} @endif</p>
    @endif

    @if($resume['summary'])
        <h2>Summary</h2>
        <p>{{ $resume['summary'] }}</p>
    @endif

    @if(count($resume['skills']))
        <h2>Skills</h2>
        <p class="skills">
            @foreach($resume['skills'] as $skill)
                <span>{{ $text($skill) }}</span>
            @endforeach
        </p>
    @endif

    @if(count($resume['experience']))
        <h2>Experience</h2>
        @foreach($resume['experience'] as $experience)
            <div class="item">
                <div class="item-title">{{ $text($experience['role'] ?? '') ?: 'Role' }}</div>
                @if($experience['company'])
                    <div class="muted">{{ $text($experience['company']) }}</div>
                @endif
                @if(count($experience['points']))
                    <ul>
                        @foreach($experience['points'] as $point)
                            <li>{{ $text($point) }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    @endif

    @if(count($resume['education']))
        <h2>Education</h2>
        <ul>
            @foreach($resume['education'] as $education)
                <li>{{ $text($education) }}</li>
            @endforeach
        </ul>
    @endif

    @if(count($resume['projects']))
        <h2>Projects</h2>
        <ul>
            @foreach($resume['projects'] as $project)
                @if(is_array($project))
                    <li>
                        @if($text($project['name'] ?? '') !== '')
                            <strong>{{ $text($project['name'] ?? '') }}</strong>
                        @endif
                        @if($text($project['description'] ?? '') !== '')
                            <span class="project-description">{{ $text($project['description'] ?? '') }}</span>
                        @endif
                    </li>
                @else
                    <li>{{ $text($project) }}</li>
                @endif
            @endforeach
        </ul>
    @endif
</body>
</html>
