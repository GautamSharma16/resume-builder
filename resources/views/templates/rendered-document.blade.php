<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cvbliss Resume</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #fff; 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            -webkit-print-color-adjust: exact;
        }

        .tpl-resume, .tpl-cover {
            width: 100%; max-width: 794px; margin: 0 auto; background: #fff; padding: 42px; color: #0f172a; font-size: 13px; line-height: 1.6; overflow-wrap: anywhere; word-break: break-word;
        }

        .tpl-cover {
            font-family: 'EB Garamond', serif;
            font-size: 16px;
            line-height: 1.5;
        }

        .tpl-resume *, .tpl-cover * { min-width: 0; max-width: 100%; overflow-wrap: anywhere; word-break: break-word; }

        .tpl-resume h1 { font-size: 28px; line-height: 1.1; margin: 0 0 8px; font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif; }
        .tpl-cover h1 { font-size: 32px; line-height: 1.1; margin: 0 0 12px; font-weight: 500; font-family: 'EB Garamond', serif; }
        .tpl-resume h2, .tpl-cover h2 { font-size: 12px; text-transform: uppercase; margin: 16px 0 7px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; font-weight: 700; }
        .tpl-resume h3 { font-size: 13px; margin: 8px 0 4px; font-weight: 700; }
        .tpl-resume p { margin: 0 0 7px; }
        .tpl-cover p { margin: 0 0 16px; }
        .tpl-resume ul, .tpl-resume ol { margin: 5px 0 0 18px; padding: 0; }
        .tpl-resume li { margin: 2px 0; }

        .tpl-role { margin: 0 0 10px; }
        .tpl-role-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px; }
        .tpl-role-head strong { display: block; font-weight: 700; }
        .tpl-role-head span { color: #64748b; font-size: 11px; text-align: right; }
        .tpl-badge { display: inline-block; border: 1px solid #d1d5db; border-radius: 999px; padding: 2px 7px; margin: 0 4px 5px 0; font-size: 10px; max-width: 100%; }
        .tpl-badges { margin: 5px 0; }
        .tpl-description { display: block; margin-top: 2px; color: #64748b; font-size: 0.92em; line-height: 1.45; }

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
        .tpl-cover {
            background: #fff;
            padding: 64px;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            color: #111827;
            font-size: 16px;
            line-height: 1.75;
        }

        .tpl-cover h1 {
            font-size: 34px;
            font-weight: 800;
            margin: 0 0 6px;
            letter-spacing: -0.01em;
        }

        .tpl-cover h2 {
            font-size: 20px;
            font-weight: 800;
            margin: 24px 0 8px;
            color: #0f172a;
        }

        .tpl-cover main,
        .tpl-cover section {
            margin-top: 48px;
        }

        .tpl-cover-modern header {
            border-bottom: 4px solid #0f766e;
            padding-bottom: 22px;
        }

        .tpl-cover-modern aside {
            margin-top: 28px;
            background: #ecfdf5;
            padding: 18px;
            font-weight: 700;
            border-radius: 8px;
        }

        .tpl-cover-executive {
            font-family: Georgia, 'Times New Roman', Times, serif;
        }

        .tpl-cover-executive header {
            text-align: center;
            border-bottom: 1px solid #111827;
            padding-bottom: 28px;
        }

        .tpl-cover-fresher header {
            background: #eff6ff;
            margin: -64px -64px 36px;
            padding: 54px 64px 32px;
        }

        .tpl-cover-switch {
            border-left: 16px solid #7c3aed;
        }

        .tpl-cover-minimal {
            font-family: Georgia, 'Times New Roman', Times, serif;
        }

        .tpl-cover-minimal header {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 20px;
        }

        .tpl-cover-clean header {
            border-top: 8px solid #111827;
            padding-top: 26px;
        }

        .tpl-kicker {
            font-weight: 800;
            color: #0f766e;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .tpl-company {
            font-weight: 800;
            color: #0f766e;
            margin: 8px 0;
        }

        /* Print styles */
        @media print {
            body { margin: 0; padding: 0; }
            .tpl-resume, .tpl-cover {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                page-break-inside: auto;
            }
            .tpl-resume *,
            .tpl-cover * {
                max-height: none !important;
                overflow: visible !important;
            }
            .tpl-resume article,
            .tpl-cover article,
            .tpl-role,
            .tpl-panel,
            .tpl-badges,
            .tpl-description {
                break-inside: avoid-page;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>{!! $html !!}</body>
</html>
