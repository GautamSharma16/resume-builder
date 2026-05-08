<?php
$file = 'd:\resume-builder\resources\views\resume\create.blade.php';
$content = file_get_contents($file);

// Replace everything between <style> and </style> with the new CSS
$css = <<<CSS
        @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Bricolage+Grotesque:opsz,wght@12..96,300;12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&display=swap');

        :root {
            --blue:          #3b82f6;
            --blue-dark:     #2563eb;
            --blue-light:    #eff6ff;
            --navy:          #0f172a;
            --ink:           #334155;
            --muted:         #64748b;
            --soft:          #94a3b8;
            --surface:       #f8fafc;
            --border:        #e2e8f0;
            --white:         #ffffff;
            --green:         #22c55e;
            --pink:          #ec4899;
            
            --font-display:  'DM Serif Display', serif;
            --font-body:     'Bricolage Grotesque', sans-serif;
            --r-md:   8px;
            --r-lg:   12px;
            --r-xl:   16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-body);
            background: var(--surface);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        .rp-root { min-height: 100vh; background: #f8fafc; }

        /* ── ONBOARDING VIEW ── */
        .rp-onboarding {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 2rem;
            animation: fadeIn 0.4s ease-out;
        }
        .rp-onboarding h1 {
            font-family: var(--font-display);
            font-size: 2.5rem;
            color: var(--navy);
            margin-bottom: 3rem;
            font-weight: 400;
        }
        .ob-cards {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 800px;
        }
        .ob-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: 2.5rem 2rem;
            flex: 1;
            min-width: 300px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .ob-card:hover {
            border-color: var(--blue);
            box-shadow: 0 10px 25px -5px rgba(59,130,246,0.1);
            transform: translateY(-4px);
        }
        .ob-icon { margin: 0 auto 1.5rem; }
        .ob-card h3 { font-size: 1.1rem; font-weight: 700; color: var(--navy); margin-bottom: 0.5rem; }
        .ob-card p { font-size: 0.9rem; color: var(--muted); line-height: 1.5; }

        /* ── BUILDER LAYOUT ── */
        .rp-builder {
            display: none;
            max-width: 1600px;
            margin: 0 auto;
            padding: 1.5rem;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            height: calc(100vh - 80px); /* header offset */
        }

        /* Form Panel */
        .rp-form-panel {
            background: white;
            border-radius: var(--r-xl);
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: 100%;
        }

        /* Step Nav (Timeline) */
        .rp-step-nav {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 1.5rem 2rem 0;
            position: relative;
            background: white;
        }
        .rp-step-nav::after {
            content: ''; position: absolute; bottom: 6px; left: 2rem; right: 2rem;
            height: 2px; background: var(--blue-light); z-index: 1;
        }
        .rp-step-tab {
            background: transparent; border: none; cursor: pointer;
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            z-index: 2; position: relative; padding-bottom: 12px; flex: 1;
        }
        .rp-step-name { font-size: 0.8rem; font-weight: 600; color: var(--muted); transition: color 0.2s; }
        .rp-step-circle {
            width: 14px; height: 14px; border-radius: 50%; background: white;
            border: 2px solid var(--blue-light); transition: all 0.2s; position: absolute; bottom: -1px;
        }
        .rp-step-tab.active .rp-step-name { color: var(--blue); }
        .rp-step-tab.active .rp-step-circle { border-color: var(--blue); border-width: 4px; }
        .rp-step-tab.completed .rp-step-circle { border-color: var(--blue); background: var(--blue); }

        /* Form Body */
        .rp-form-body { flex: 1; overflow-y: auto; padding: 2rem; }
        .rp-step-content { display: none; }
        .rp-step-content.active { display: block; animation: fadeIn 0.3s; }
        
        .step-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .step-header h2 { font-family: var(--font-display); font-size: 2rem; color: var(--navy); }
        .step-header p { color: var(--muted); font-size: 0.95rem; margin-top: 0.25rem; }
        .tips-btn { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; padding: 0.4rem 0.8rem; border-radius: var(--r-md); font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; }

        /* Fields */
        .rp-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
        .rp-field-row.single { grid-template-columns: 1fr; }
        .field-group { position: relative; }
        .field-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--ink); margin-bottom: 0.4rem; }
        
        /* Validated Inputs */
        .rp-input-wrap { position: relative; }
        .rp-input {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: var(--r-md);
            font-family: var(--font-body); font-size: 0.95rem; color: var(--navy); transition: all 0.2s; background: #fcfcfc;
        }
        .rp-input:focus { outline: none; border-color: var(--blue); background: white; box-shadow: 0 0 0 3px var(--blue-light); }
        
        /* Simulating green check logic based on placeholder value being filled - simplified */
        .rp-input:not(:placeholder-shown) { border-color: #86efac; background: #f0fdf4; }
        .rp-input:not(:placeholder-shown) ~ .valid-icon { display: block; }
        
        .valid-icon { position: absolute; right: 12px; top: 12px; color: var(--green); display: none; }
        
        /* Rich Text / Description */
        .rich-text-wrapper { border: 1px solid var(--blue-light); border-radius: var(--r-md); overflow: hidden; background: white; box-shadow: 0 0 0 2px var(--blue-light); }
        .rich-text-toolbar { display: flex; gap: 0.5rem; padding: 0.5rem; border-bottom: 1px solid var(--border); background: #f8fafc; }
        .rt-btn { background: transparent; border: none; color: var(--ink); cursor: pointer; padding: 0.3rem; border-radius: 4px; }
        .rt-btn:hover { background: #e2e8f0; }
        .ai-gen-btn { background: #818cf8; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: var(--r-md); font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; margin-left: auto; transition: background 0.2s; }
        .ai-gen-btn:hover { background: #6366f1; }
        .rp-input-ta { border: none; border-radius: 0; background: white; min-height: 120px; padding: 1rem; box-shadow: none !important; width: 100%; }

        /* Entry Cards (Exp/Edu) */
        .rp-entry-card { border: 1px solid var(--border); border-radius: var(--r-md); padding: 1.5rem; margin-bottom: 1rem; background: white; position: relative; }
        .rp-entry-remove { position: absolute; top: 1rem; right: 1rem; color: #ef4444; background: transparent; border: none; cursor: pointer; }

        /* Footer */
        .rp-form-footer { padding: 1.25rem 2rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; background: white; border-radius: 0 0 var(--r-xl) var(--r-xl); }
        .btn-nav { padding: 0.75rem 1.5rem; border-radius: var(--r-md); font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; border: 1px solid var(--border); background: white; color: var(--ink); }
        .btn-nav:hover { background: #f1f5f9; }
        .btn-nav.primary { background: var(--blue); color: white; border-color: var(--blue); }
        .btn-nav.primary:hover { background: var(--blue-dark); }

        /* Preview Panel */
        .rp-preview-panel { display: flex; flex-direction: column; height: 100%; }
        .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .score-badge { background: white; border: 1px solid var(--border); padding: 0.4rem 0.8rem; border-radius: var(--r-xl); font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; color: var(--navy); }
        .score-val { background: var(--green); color: white; padding: 0.1rem 0.4rem; border-radius: 4px; }
        .change-tpl-btn { background: white; border: 1px solid var(--border); padding: 0.4rem 0.8rem; border-radius: var(--r-xl); font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; color: var(--navy); }
        .change-tpl-btn:hover { background: #f8fafc; }
        .rp-viewport { flex: 1; background: var(--border); border-radius: var(--r-xl); overflow: auto; display: flex; justify-content: center; padding: 2rem; }
        #cv-preview { background: white; width: 794px; min-height: 1123px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); transform-origin: top center; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Popup */
        .rp-popup-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 1.5rem; }
        .rp-popup-overlay.visible { display: flex; }
        .rp-popup { background: white; border-radius: 24px; width: 100%; max-width: 1020px; max-height: 88vh; display: flex; flex-direction: column; overflow: hidden; }
        .rp-popup-head { padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .rp-popup-head h3 { font-family: var(--font-display); font-size: 1.5rem; color: var(--navy); }
        .rp-popup-close { width: 34px; height: 34px; border: none; background: #f1f5f9; border-radius: 50%; cursor: pointer; color: #64748b; }
        .rp-popup-body { padding: 1.5rem; overflow-y: auto; }
        .rp-tpl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1.25rem; }
        .rp-tpl-card { border: 2px solid var(--border); border-radius: 16px; overflow: hidden; cursor: pointer; background: white; display: flex; flex-direction: column; transition: all 0.2s;}
        .rp-tpl-card:hover, .rp-tpl-card.selected { border-color: var(--blue); }
        .rp-tpl-thumb { position: relative; width: 100%; height: 240px; overflow: hidden; background: #f8fafc; }
        .rp-tpl-thumb-inner { position: absolute; top: 0; left: 0; width: 794px; transform: scale(0.26); transform-origin: top left; pointer-events: none; }
        .rp-tpl-name { padding: 0.8rem; font-size: 0.85rem; font-weight: 600; text-align: center; border-top: 1px solid var(--border); }
CSS;

$content = preg_replace('/<style>.*?<\/style>/s', "<style>\n" . $css . "\n    </style>", $content);

// Replace everything between <div class="rp-page"> and <script> with the new HTML layout
$html = <<<HTML
    {{-- ONBOARDING VIEW --}}
    <div id="rp-onboarding-view" class="rp-onboarding">
        <h1>How will you make your resume?</h1>
        <div class="ob-cards">
            <div class="ob-card" onclick="document.getElementById('resume-autofill-file').click()">
                <div class="ob-icon"><img src="https://img.icons8.com/color/96/upload-to-cloud.png" alt="Upload" width="64"></div>
                <h3>I already have a resume</h3>
                <p>Upload your existing resume to make quick edits</p>
                <input id="resume-autofill-file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx" style="display:none">
            </div>
            <div class="ob-card" onclick="startFromScratch()">
                <div class="ob-icon"><img src="https://img.icons8.com/color/96/document--v1.png" alt="Document" width="64"></div>
                <h3>Start from scratch</h3>
                <p>Our AI will guide you through creating a resume</p>
            </div>
        </div>
        <p id="resume-autofill-status" style="margin-top:2rem;color:var(--muted);"></p>
    </div>

    {{-- BUILDER VIEW --}}
    <div id="rp-builder-view" class="rp-builder">
        
        {{-- LEFT PANEL (FORM) --}}
        <section class="rp-form-panel">
            
            <select id="template-id" style="display:none">
                @foreach(\$templates as \$template)
                    <option value="{{\$template->id}}" @selected((string) \$selectedTemplateId === (string) \$template->id)>{{ \$template->name }}</option>
                @endforeach
            </select>

            <nav class="rp-step-nav">
                <button class="rp-step-tab active" data-step="1"><span class="rp-step-name">Contacts</span><div class="rp-step-circle"></div></button>
                <button class="rp-step-tab" data-step="2"><span class="rp-step-name">Experience</span><div class="rp-step-circle"></div></button>
                <button class="rp-step-tab" data-step="3"><span class="rp-step-name">Education</span><div class="rp-step-circle"></div></button>
                <button class="rp-step-tab" data-step="4"><span class="rp-step-name">Skills</span><div class="rp-step-circle"></div></button>
                <button class="rp-step-tab" data-step="5"><span class="rp-step-name">Summary</span><div class="rp-step-circle"></div></button>
                <button class="rp-step-tab" data-step="6"><span class="rp-step-name">Finalize</span><div class="rp-step-circle"></div></button>
            </nav>

            <div class="rp-form-body">
                
                {{-- STEP 1: Contacts --}}
                <div class="rp-step-content active" data-step="1">
                    <div class="step-header">
                        <div>
                            <h2>Contacts</h2>
                            <p>Add your up-to-date contact information so employers and recruiters can easily reach you.</p>
                        </div>
                    </div>

                    <div class="rp-field-row">
                        <div class="field-group">
                            <label class="field-label">First name</label>
                            <div class="rp-input-wrap">
                                <input id="cv-name" class="rp-input cv-field" placeholder="Jane" data-field="name">
                                <svg class="valid-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Last name</label>
                            <div class="rp-input-wrap">
                                <input id="cv-last-name" class="rp-input cv-field" placeholder="Smith" data-field="last_name">
                                <svg class="valid-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="rp-field-row single">
                        <div class="field-group">
                            <label class="field-label">Desired job title</label>
                            <div class="rp-input-wrap">
                                <input id="cv-job-title" class="rp-input cv-field" placeholder="Job Title" data-field="job_title">
                                <svg class="valid-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="rp-field-row">
                        <div class="field-group">
                            <label class="field-label">Phone</label>
                            <div class="rp-input-wrap">
                                <input id="cv-mobile" class="rp-input cv-field" placeholder="+1 123 456 7890" data-field="mobile">
                                <svg class="valid-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Email</label>
                            <div class="rp-input-wrap">
                                <input id="cv-email" class="rp-input cv-field" type="email" placeholder="email@example.com" data-field="email">
                                <svg class="valid-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="rp-field-row single">
                        <div class="field-group">
                            <label class="field-label">Location</label>
                            <div class="rp-input-wrap">
                                <input id="cv-location" class="rp-input cv-field" placeholder="City, State, Country" data-field="location">
                            </div>
                        </div>
                    </div>
                    
                    <div class="rp-field-row single">
                        <div class="field-group">
                            <label class="field-label">Social Links (comma separated)</label>
                            <div class="rp-input-wrap">
                                <input id="cv-social" class="rp-input cv-field" placeholder="linkedin.com/in/you, github.com/you" data-field="social_links">
                            </div>
                        </div>
                    </div>

                    <div id="image-upload-section" style="display:none; margin-top:1rem;">
                        <label class="field-label">Profile Photo</label>
                        <div style="display:flex; gap:1rem; align-items:center;">
                            <img id="cv-image-preview" src="" style="width:60px; height:60px; border-radius:50%; object-fit:cover; display:none;">
                            <input type="file" id="cv-image-input" accept="image/*" style="display:none;">
                            <button type="button" class="btn-nav" onclick="document.getElementById('cv-image-input').click()">Upload Photo</button>
                            <button type="button" class="btn-nav" id="remove-image-btn">Remove</button>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Experience --}}
                <div class="rp-step-content" data-step="2">
                    <div class="step-header">
                        <div>
                            <h2>Experience</h2>
                            <p>List your work experience starting with the most recent position first.</p>
                        </div>
                        <button class="tips-btn"><svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/></svg> Experience tips</button>
                    </div>

                    <div id="rp-exp-editor"></div>
                    <button type="button" id="add-exp-btn" class="btn-nav" style="width:100%; border-style:dashed;">+ Add Experience</button>
                </div>

                {{-- STEP 3: Education --}}
                <div class="rp-step-content" data-step="3">
                    <div class="step-header">
                        <div>
                            <h2>Education</h2>
                            <p>Add your educational background.</p>
                        </div>
                    </div>
                    <div id="rp-edu-editor"></div>
                    <button type="button" id="add-edu-btn" class="btn-nav" style="width:100%; border-style:dashed; margin-top:1rem;">+ Add Education</button>
                </div>

                {{-- STEP 4: Skills --}}
                <div class="rp-step-content" data-step="4">
                    <div class="step-header">
                        <div>
                            <h2>Skills</h2>
                            <p>Highlight your core competencies.</p>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Skills (comma separated)</label>
                        <textarea id="cv-skills" class="rp-input cv-field" rows="4" placeholder="React, Node.js, PHP" data-field="skills"></textarea>
                    </div>
                </div>

                {{-- STEP 5: Summary --}}
                <div class="rp-step-content" data-step="5">
                    <div class="step-header">
                        <div>
                            <h2>Summary</h2>
                            <p>Write a brief summary of your background.</p>
                        </div>
                    </div>
                    <div class="field-group rich-text-wrapper">
                        <div class="rich-text-toolbar">
                            <button class="rt-btn"><b>B</b></button>
                            <button class="rt-btn"><i>I</i></button>
                            <button class="rt-btn"><u>U</u></button>
                            <button class="ai-gen-btn" onclick="generateAISummary()">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM15 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0115 10zM6.5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 016.5 10zM14.61 5.39a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 01-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM7.51 12.49a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 11-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM14.61 14.61a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 011.06-1.06l1.06 1.06a.75.75 0 010 1.06zM7.51 7.51a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 011.06-1.06l1.06 1.06a.75.75 0 010 1.06z"/></svg>
                                Generate with AI
                            </button>
                        </div>
                        <textarea id="cv-summary" class="rp-input rp-input-ta cv-field" placeholder="Experienced professional..." data-field="summary"></textarea>
                    </div>
                </div>

                {{-- STEP 6: Finalize --}}
                <div class="rp-step-content" data-step="6">
                    <div class="step-header">
                        <div>
                            <h2>Finalize</h2>
                            <p>Review and save your resume.</p>
                        </div>
                    </div>
                    <div style="text-align:center; padding: 2rem;">
                        <h3 style="font-size:1.5rem; color:var(--navy); margin-bottom:1rem;">Ready to download?</h3>
                        <button type="button" id="save-cv-btn" class="btn-nav primary" style="font-size:1.1rem; padding: 1rem 3rem;">Save &amp; Download PDF</button>
                    </div>
                </div>
            </div>

            <div class="rp-form-footer">
                <button type="button" class="btn-nav" id="btn-back" style="visibility:hidden;">Back</button>
                <button type="button" class="btn-nav primary" id="btn-next">Next: Experience</button>
            </div>
        </section>

        {{-- RIGHT PANEL (PREVIEW) --}}
        <section class="rp-preview-panel">
            <div class="preview-header">
                <div class="score-badge">
                    <span class="score-val">90%</span> Your resume score 😍
                </div>
                <button class="change-tpl-btn" id="change-template-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Change Template
                </button>
            </div>
            <div class="rp-viewport" id="preview-viewport">
                <div id="cv-preview"></div>
            </div>
        </section>
    </div>

    {{-- TEMPLATE POPUP --}}
    <div id="template-popup" class="rp-popup-overlay">
        <div class="rp-popup">
            <div class="rp-popup-head">
                <h3>Choose Template</h3>
                <button class="rp-popup-close" id="close-template-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rp-popup-body">
                <div class="rp-tpl-grid" id="template-grid"></div>
            </div>
        </div>
    </div>
HTML;

$content = preg_replace('/<div class="rp-page">.*?<script>/s', $html . "\n\n<script>", $content);

file_put_contents('d:\resume-builder\resources\views\resume\create.blade.php', $content);
?>
