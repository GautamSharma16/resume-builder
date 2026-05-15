const puppeteer = require('C:/xampp/htdocs/resume-build/node_modules/puppeteer');
const fs = require('fs');
const os = require('os');
const path = require('path');

(async () => {
    const htmlPath = process.argv[2];
    const outPath  = process.argv[3];

    const profileDir = path.join(os.tmpdir(), 'puppeteer-profile');
    if (!fs.existsSync(profileDir)) fs.mkdirSync(profileDir, { recursive: true });

    const browser = await puppeteer.launch({
        headless: true,
        executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
        userDataDir: profileDir,
        ignoreHTTPSErrors: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
        ],
    });

    const page = await browser.newPage();
    const html = fs.readFileSync(htmlPath, 'utf8');
    await page.setContent(html, { waitUntil: 'networkidle0' });
    await page.pdf({
        path: outPath,
        format: 'A4',
        printBackground: true,
        margin: { top: '0mm', right: '0mm', bottom: '0mm', left: '0mm' },
    });

    await browser.close();
})().catch((err) => {
    console.error(err && err.stack ? err.stack : String(err));
    process.exit(1);
});