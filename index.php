<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-PaySlip | HR Desk</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ url_for('static', filename='favicon.png') }}">
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

<div class="max-w-4xl mx-auto px-4 py-14 sm:py-20">

    <!-- Header -->
    <div class="text-center mb-14">
        <p class="font-mono text-xs tracking-[0.3em] text-brass uppercase mb-3">HR Desk · Offline System</p>
        <h1 class="font-display text-4xl sm:text-5xl font-semibold text-ink">E-PaySlip</h1>
        <p class="text-inksoft mt-3">Pick a desk to get started.</p>
    </div>

    <!-- Two option cards -->
    <div class="grid sm:grid-cols-2 gap-8 sm:gap-10">

        <!-- Payslip option -->
        <a href="payslip.php" class="group block">
            <div class="tab bg-ink text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit mx-auto -mb-1 relative z-10">
                Form 001
            </div>
            <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-150 p-8 text-center">
                <div class="text-5xl mb-4">🧾</div>
                <h2 class="font-display text-2xl font-semibold text-ink mb-2">Payslip</h2>
                <p class="text-sm text-inksoft leading-relaxed mb-5">
                    Look up a staff member, confirm their details, and generate this month's Excel payslip.
                </p>
                <span class="inline-flex items-center gap-1 text-sm font-medium text-brass group-hover:gap-2 transition-all">
                    Open the payslip desk →
                </span>
            </div>
        </a>

        <!-- Ask AI option -->
        <button type="button" id="btnOpenAskAI" class="group block text-left w-full">
            <div class="tab bg-brass text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit mx-auto -mb-1 relative z-10">
                Desk 002
            </div>
            <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-150 p-8 text-center h-full">
                <div class="text-5xl mb-4">✉️</div>
                <h2 class="font-display text-2xl font-semibold text-ink mb-2">Ask AI</h2>
                <p class="text-sm text-inksoft leading-relaxed mb-5">
                    Describe the email you need — a payroll notice, a reminder, an announcement — and let AI draft it.
                </p>
                <span class="inline-flex items-center gap-1 text-sm font-medium text-brass group-hover:gap-2 transition-all">
                    Open the AI desk →
                </span>
            </div>
        </button>

    </div>

    <p class="text-center text-xs text-inksoft mt-14 font-mono">Running locally on Laragon · No data leaves this machine</p>
</div>

<!-- ============ Ask AI Modal ============ -->
<div id="askAiModal" class="hidden fixed inset-0 bg-ink/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg overflow-hidden">

        <div class="bg-ink px-6 py-4 flex items-center justify-between">
            <div>
                <p class="font-mono text-[10px] tracking-[0.3em] text-brasslt uppercase">Desk 002</p>
                <h2 class="font-display text-lg font-semibold text-paper">Ask AI to draft an email</h2>
            </div>
            <button type="button" onclick="closeModal('askAiModal')" class="text-paper/70 hover:text-paper text-xl leading-none">&times;</button>
        </div>

        <div class="p-6">
            <label class="block text-sm font-medium text-ink mb-1">What do you need?</label>
            <textarea id="aiPrompt" rows="4" placeholder="e.g. Write a short email to all staff reminding them that November payslips will be issued this Friday."
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

document.getElementById('btnOpenAskAI').addEventListener('click', () => {
    document.getElementById('aiError').classList.add('hidden');
    document.getElementById('aiResultWrap').classList.add('hidden');
    openModal('askAiModal');
    document.getElementById('aiPrompt').focus();
});

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

        // NOTE: api/ask_ai.php is currently a placeholder — the real AI
        // provider call gets wired in there in the next step.
        const res = await fetch('api/ask_ai.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            document.getElementById('aiResult').value = data.email;
            document.getElementById('aiResultWrap').classList.remove('hidden');
        } else {
            errorEl.textContent = data.error || 'Something went wrong.';
            errorEl.classList.remove('hidden');
        }
    } catch (e) {
        errorEl.textContent = 'Could not reach the AI service.';
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
