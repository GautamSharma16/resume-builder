<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #fff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }

        .tpl-resume, .tpl-cover {
            width: 100%; max-width: 794px; margin: 0 auto; background: #fff; padding: 38px; color: #111827; font-size: 12px; line-height: 1.6;
        }

        .tpl-resume h1, .tpl-cover h1 { font-size: 26px; line-height: 1.1; margin: 0 0 8px; font-weight: 800; }
        .tpl-resume h2, .tpl-cover h2 { font-size: 12px; text-transform: uppercase; margin: 16px 0 7px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; font-weight: 700; }
        .tpl-resume h3 { font-size: 13px; margin: 8px 0 4px; font-weight: 700; }
        .tpl-resume p, .tpl-cover p { margin: 0 0 7px; }
        .tpl-resume ul, .tpl-resume ol { margin: 5px 0 0 18px; padding: 0; }
        .tpl-resume li { margin: 2px 0; }

        .tpl-role { margin: 0 0 10px; }
        .tpl-role-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px; }
        .tpl-role-head strong { display: block; font-weight: 700; }
        .tpl-role-head span { color: #64748b; font-size: 11px; }
        .tpl-badge { display: inline-block; border: 1px solid #d1d5db; border-radius: 999px; padding: 2px 7px; margin: 0 4px 5px 0; font-size: 10px; }
        .tpl-badges { margin: 5px 0; }

        /* Two column layout */
        .tpl-two { display: grid; grid-template-columns: 200px 1fr; gap: 0; }
        .tpl-two aside { padding: 18px; background: #111827; color: #fff; }
        .tpl-two main { padding: 18px; }

        /* Carded layout */
        .tpl-carded header { padding: 18px; margin: 0 0 12px; background: #f3f4f6; }
        .tpl-carded section { margin: 10px 0; padding: 10px; border: 1px solid #e5e7eb; }

        /* Band layout */
        .tpl-band header { padding: 18px; margin: 0 0 12px; }
        .tpl-band .tpl-panel { background: #f3f4f6; padding: 12px; }
        .tpl-band .tpl-links { text-align: center; color: #666; font-size: 10px; }

        /* Grid layout */
        .tpl-grid header { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 12px; }
        .tpl-grid .span { grid-column: 1 / -1; }

        /* Minimal */
        .tpl-minimal header { text-align: center; margin-bottom: 12px; }
        .tpl-minimal p { text-align: center; }

        /* Accent box */
        .tpl-accentbox header > div { height: 3px; margin-bottom: 8px; }

        /* Executive */
        .tpl-executive { }
        .tpl-executive .tpl-highlight { border-left: 4px solid; padding: 10px 12px; margin: 10px 0; }

        /* Topline */
        .tpl-topline { border-top: 3px solid #000; padding-bottom: 8px; margin-bottom: 12px; }

        /* Centered */
        .tpl-centered { text-align: center; margin-bottom: 12px; }
        .tpl-rule { height: 2px; margin: 12px 0; }

        /* Leftline */
        .tpl-leftline footer { margin-top: 12px; font-size: 11px; color: #666; }

        /* Dense (ATS) */
        .tpl-dense section { margin: 6px 0; }
        .tpl-dense h2 { margin: 8px 0 4px; }
        .tpl-dense p { margin: 0 0 4px; }

        /* Cover letters */
        .tpl-cover { font-size: 13px; line-height: 1.65; }
        .tpl-cover main, .tpl-cover section { margin-top: 28px; }
        .tpl-cover header { margin-bottom: 20px; }
        .tpl-cover aside { background: #f3f4f6; padding: 12px; margin: 12px 0; font-weight: 600; }
        .tpl-cover footer { margin-top: 20px; }
        .tpl-kicker { font-weight: 800; margin-bottom: 8px; }
        .tpl-company { font-weight: 800; margin: 8px 0; }

        .tpl-cover-modern header { background: #f3f4f6; padding: 16px; margin: -38px -38px 16px; }
        .tpl-cover-executive header { text-align: right; margin-bottom: 20px; }
        .tpl-cover-fresher { }
        .tpl-cover-switch header { text-align: center; margin-bottom: 16px; }
        .tpl-cover-minimal { }
        .tpl-cover-clean { }

        /* Print styles */
        @media print {
            body { margin: 0; padding: 0; }
            .tpl-resume, .tpl-cover { padding: 20px; }
        }
    </style>
</head>
<body>{!! $html !!}</body>
</html>
