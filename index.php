<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-PaySlip | HR Desk</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="static/favicon.png">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          paper:   '#FAF7F0',
          ink:     '#1F2A44',
          inksoft: '#5B6478',
          brass:   '#A9793F',
          brasslt: '#E4D3B4',
          line:    '#E4DFD1',
        },
        fontFamily: {
          display: ['Fraunces', 'serif'],
          body:    ['Inter', 'sans-serif'],
          mono:    ['"IBM Plex Mono"', 'monospace'],
        },
      },
    },
  };
</script>
<style>
  body {
    background-color: #FAF7F0;
    background-image: repeating-linear-gradient(
      to bottom,
      transparent 0px, transparent 27px, #EFE9D8 28px
    );
  }
  .tab {
    clip-path: polygon(0 100%, 0 30%, 10% 0, 90% 0, 100% 30%, 100% 100%);
  }
  .perforated {
    border-top: 2px dashed #D8CFB8;
  }
</style>
</head>
<body class="min-h-screen font-body text-ink">

<div class="max-w-5xl mx-auto px-4 py-14 sm:py-20">

    <!-- Header -->
    <div class="text-center mb-14">
        <p class="font-mono text-xs tracking-[0.3em] text-brass uppercase mb-3">HR Desk · On-Premise System</p>
        <h1 class="font-display text-4xl sm:text-5xl font-semibold text-ink">E-PaySlip</h1>
        <p class="text-inksoft mt-3">Pick an operation desk to get started.</p>
    </div>

    <!-- Three option cards -->
    <div class="grid sm:grid-cols-3 gap-6 sm:gap-8">

        <!-- Desk 001: Upload Payroll Excel -->
        <button type="button" id="btnOpenUpload" class="group block text-left w-full">
            <div class="tab bg-emerald-800 text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit mx-auto -mb-1 relative z-10">
                Desk 001
            </div>
            <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-150 p-6 text-center h-full flex flex-col justify-between">
                <div>
                    <div class="text-4xl mb-3">📊</div>
                    <h2 class="font-display text-xl font-semibold text-ink mb-2">Import Payroll</h2>
                    <p class="text-xs text-inksoft leading-relaxed mb-4">
                        Upload this month's master Excel spreadsheet to batch-populate staff payslip data.
                    </p>
                </div>
                <span class="inline-flex items-center justify-center gap-1 text-xs font-medium text-emerald-700 group-hover:gap-2 transition-all">
                    Upload monthly batch →
                </span>
            </div>
        </button>

        <!-- Desk 002: Payslip Generator -->
        <a href="payslip.php" class="group block">
            <div class="tab bg-ink text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit mx-auto -mb-1 relative z-10">
                Desk 002
            </div>
            <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-150 p-6 text-center h-full flex flex-col justify-between">
                <div>
                    <div class="text-4xl mb-3">🧾</div>
                    <h2 class="font-display text-xl font-semibold text-ink mb-2">Payslip Desk</h2>
                    <p class="text-xs text-inksoft leading-relaxed mb-4">
                        Select a salary month, choose a staff member, and preview or download their official payslip.
                    </p>
                </div>
                <span class="inline-flex items-center justify-center gap-1 text-xs font-medium text-brass group-hover:gap-2 transition-all">
                    Open payslips desk →
                </span>
            </div>
        </a>

        <!-- Desk 003: Ask AI -->
        <button type="button" id="btnOpenAskAI" class="group block text-left w-full">
            <div class="tab bg-brass text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit mx-auto -mb-1 relative z-10">
                Desk 003
            </div>
            <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-150 p-6 text-center h-full flex flex-col justify-between">
                <div>
                    <div class="text-4xl mb-3">✉️</div>
                    <h2 class="font-display text-xl font-semibold text-ink mb-2">Ask AI</h2>
                    <p class="text-xs text-inksoft leading-relaxed mb-4">
                        Draft staff salary emails, payroll disbursement memos, and notices automatically using AI.
                    </p>
                </div>
                <span class="inline-flex items-center justify-center gap-1 text-xs font-medium text-brass group-hover:gap-2 transition-all">
                    Open the AI desk →
                </span>
            </div>
        </button>

    </div>

    <p class="text-center text-xs text-inksoft mt-14 font-mono">Running on MariaDB Server · Persistent Storage</p>
</div>

<!-- ============ Upload Payroll Excel Modal ============ -->
<div id="uploadModal" class="hidden fixed inset-0 bg-ink/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg overflow-hidden">

        <div class="bg-emerald-800 px-6 py-4 flex items-center justify-between">
            <div>
                <p class="font-mono text-[10px] tracking-[0.3em] text-emerald-200 uppercase">Desk 001</p>
                <h2 class="font-display text-lg font-semibold text-paper">Upload Monthly Payroll Excel</h2>
            </div>
            <button type="button" onclick="closeModal('uploadModal')" class="text-paper/70 hover:text-paper text-xl leading-none">&times;</button>
        </div>

        <form id="formUploadPayroll" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-ink uppercase tracking-wider mb-1">Salary Month Label</label>
                <input type="text" id="uploadSalaryMonth" name="salary_month" required
                       placeholder="e.g. September 2026"
                       class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-700/50">
                <p class="text-[11px] text-inksoft mt-1">This label groups the payslips for dropdown retrieval.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-ink uppercase tracking-wider mb-1">Disbursement / Payment Date</label>
                <input type="date" id="uploadPaymentDate" name="payment_date" required
                       class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-700/50">
            </div>

            <div>
                <label class="block text-xs font-semibold text-ink uppercase tracking-wider mb-1">Excel File (.xlsx)</label>
                <input type="file" id="uploadPayrollFile" name="payroll_file" required accept=".xlsx, .xls"
                       class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-700/50 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div id="uploadFeedback" class="text-sm p-3 rounded-lg hidden"></div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('uploadModal')"
                        class="px-4 py-2 text-xs font-medium rounded-lg border border-line text-inksoft hover:bg-slate-50">Cancel</button>
                <button type="submit" id="btnSubmitUpload"
                        class="px-5 py-2 text-xs font-semibold rounded-lg bg-emerald-800 hover:bg-emerald-900 text-white flex items-center gap-2">
                    <span id="uploadBtnLabel">Import Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============ Ask AI Modal ============ -->
<div id="askAiModal" class="hidden fixed inset-0 bg-ink/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg overflow-hidden">

        <div class="bg-ink px-6 py-4 flex items-center justify-between">
            <div>
                <p class="font-mono text-[10px] tracking-[0.3em] text-brasslt uppercase">Desk 003</p>
                <h2 class="font-display text-lg font-semibold text-paper">Ask AI to draft an email</h2>
            </div>
            <button type="button" onclick="closeModal('askAiModal')" class="text-paper/70 hover:text-paper text-xl leading-none">&times;</button>
        </div>

        <div class="p-6">
            <label class="block text-sm font-medium text-ink mb-1">What do you need?</label>
            <textarea id="aiPrompt" rows="4" placeholder="e.g. Write a short email to all staff reminding them that September payslips will be issued this Friday."
                      class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brass/50"></textarea>

            <button type="button" id="btnGenerateAI"
                    class="w-full mt-3 bg-brass hover:bg-brass/90 text-white font-medium py-2.5 rounded-lg text-sm flex items-center justify-center gap-2">
                <span id="aiGenerateLabel">✨ Generate Email</span>
            </button>

            <p id="aiError" class="text-rose-600 text-sm mt-2 hidden"></p>

            <div id="aiResultWrap" class="hidden mt-5 perforated pt-4">
                <label class="block text-sm font-medium text-ink mb-1">Draft</label>
                <textarea id="aiResult" rows="8"
                          class="w-full border border-line rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brass/50"></textarea>
                <div class="flex justify-end gap-2 mt-3">
                    <button type="button" id="btnCopyAI"
                            class="px-3 py-1.5 text-xs rounded-md bg-slate-100 hover:bg-slate-200 text-ink">Copy</button>
                    <button type="button" id="btnRegenerateAI"
                            class="px-3 py-1.5 text-xs rounded-md bg-slate-100 hover:bg-slate-200 text-ink">Regenerate</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// --- Modal Triggers ---
document.getElementById('btnOpenUpload').addEventListener('click', () => {
    const feedback = document.getElementById('uploadFeedback');
    feedback.classList.add('hidden');
    feedback.textContent = '';
    // Default payment date to today
    document.getElementById('uploadPaymentDate').value = new Date().toISOString().split('T')[0];
    openModal('uploadModal');
});

document.getElementById('btnOpenAskAI').addEventListener('click', () => {
    document.getElementById('aiError').classList.add('hidden');
    document.getElementById('aiResultWrap').classList.add('hidden');
    openModal('askAiModal');
    document.getElementById('aiPrompt').focus();
});

// --- Handle Excel Upload ---
document.getElementById('formUploadPayroll').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const feedback = document.getElementById('uploadFeedback');
    const submitBtn = document.getElementById('btnSubmitUpload');
    const btnLabel = document.getElementById('uploadBtnLabel');

    feedback.classList.add('hidden');
    submitBtn.disabled = true;
    btnLabel.textContent = 'Importing...';

    const formData = new FormData(form);

    try {
        const res = await fetch('api/upload_payroll_excel.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        feedback.classList.remove('hidden');
        if (res.ok && data.status === 'success') {
            feedback.className = 'text-sm p-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200';
            feedback.textContent = data.message;
            form.reset();
            setTimeout(() => {
                closeModal('uploadModal');
            }, 1800);
        } else {
            feedback.className = 'text-sm p-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200';
            feedback.textContent = data.message || 'Import failed. Check file format.';
        }
    } catch (err) {
        feedback.classList.remove('hidden');
        feedback.className = 'text-sm p-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200';
        feedback.textContent = 'Server communication error. Verify MariaDB is running.';
    } finally {
        submitBtn.disabled = false;
        btnLabel.textContent = 'Import Data';
    }
});

// --- Ask AI Logic ---
async function runAskAI() {
    const prompt = document.getElementById('aiPrompt').value.trim();
    const errorEl = document.getElementById('aiError');
    const label = document.getElementById('aiGenerateLabel');
    errorEl.classList.add('hidden');

    if (!prompt) {
        errorEl.textContent = 'Type what you need first.';
        errorEl.classList.remove('hidden');
        return;
    }

    label.textContent = 'Generating…';

    try {
        const formData = new FormData();
        formData.append('prompt', prompt);

        const res = await fetch('api/ask_ai.php', { method: 'POST', body: formData });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (jsonErr) {
            throw new Error(`Server returned non-JSON response (${res.status}): ${text.substring(0, 150)}`);
        }

        if (data.success) {
            document.getElementById('aiResult').value = data.email;
            document.getElementById('aiResultWrap').classList.remove('hidden');
        } else {
            errorEl.textContent = data.error || 'Something went wrong.';
            errorEl.classList.remove('hidden');
        }
    } catch (e) {
        errorEl.textContent = e.message;
        errorEl.classList.remove('hidden');
    } finally {
        label.textContent = '✨ Generate Email';
    }
}

document.getElementById('btnGenerateAI').addEventListener('click', runAskAI);
document.getElementById('btnRegenerateAI').addEventListener('click', runAskAI);

document.getElementById('btnCopyAI').addEventListener('click', () => {
    const el = document.getElementById('aiResult');
    el.select();
    document.execCommand('copy');
});
</script>

</body>
</html>