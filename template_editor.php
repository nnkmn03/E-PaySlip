<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Template | E-PaySlip HR Desk</title>
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
  }
  #gridWrap {
    overflow: auto;
    max-height: 65vh;
  }
  table.grid-table {
    border-collapse: collapse;
    font-size: 11px;
  }
  table.grid-table th, table.grid-table td {
    border: 1px solid #E4DFD1;
    min-width: 64px;
    height: 30px;
    padding: 2px 4px;
    vertical-align: middle;
  }
  table.grid-table th {
    background: #F1ECDD;
    position: sticky;
    top: 0;
    z-index: 5;
    font-family: 'IBM Plex Mono', monospace;
    color: #5B6478;
    font-weight: 500;
  }
  table.grid-table th.rowhead {
    position: sticky;
    left: 0;
    z-index: 6;
  }
  table.grid-table td.datacell {
    cursor: pointer;
    background: #fff;
    color: #1F2A44;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  table.grid-table td.datacell:hover {
    outline: 2px solid #A9793F;
    outline-offset: -2px;
  }
  .armed td.datacell {
    cursor: crosshair;
  }
  .field-row.armed {
    box-shadow: 0 0 0 2px #A9793F inset;
    background: #FBF3E7 !important;
  }
  .field-row.conflict {
    box-shadow: 0 0 0 2px #E11D48 inset;
  }
</style>
</head>
<body class="min-h-screen font-body text-ink">

<div class="max-w-7xl mx-auto px-4 py-10">

    <div class="flex items-center justify-between mb-8 gap-4 flex-wrap">
        <div>
            <p class="font-mono text-xs tracking-[0.3em] text-brass uppercase mb-2">HR Desk · Template Setup</p>
            <h1 class="font-display text-3xl font-semibold text-ink">Update Payslip Template</h1>
            <p class="text-inksoft mt-2 text-sm max-w-2xl">Replace the master Excel layout, then click a field below and click the cell on the grid where its value should be written.</p>
        </div>
        <a href="index.php" class="text-xs font-medium text-inksoft hover:text-ink border border-line rounded-lg px-4 py-2 whitespace-nowrap">← Back to HR Desk</a>
    </div>

    <!-- Replace template file -->
    <div class="bg-white border border-line rounded-xl shadow-sm p-6 mb-6">
        <h2 class="font-display text-lg font-semibold mb-1">1. Replace Template File (optional)</h2>
        <p class="text-xs text-inksoft mb-4">Upload a new .xlsx layout to replace the current one. Your previous template is kept as a single backup automatically.</p>
        <form id="formUploadTemplate" class="flex flex-wrap items-center gap-3">
            <input type="file" id="templateFile" name="template_file" accept=".xlsx" required
                   class="text-sm border border-line rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brasslt file:text-ink hover:file:bg-brasslt/80">
            <button type="submit" id="btnUploadTemplate"
                    class="px-4 py-2 text-xs font-semibold rounded-lg bg-ink hover:bg-ink/90 text-white">
                Upload New Template
            </button>
            <span id="uploadTemplateFeedback" class="text-xs"></span>
        </form>
    </div>

    <div class="grid lg:grid-cols-[340px_1fr] gap-6">

        <!-- Field list -->
        <div class="bg-white border border-line rounded-xl shadow-sm p-5 h-fit">
            <h2 class="font-display text-lg font-semibold mb-1">2. Map Fields to Cells</h2>
            <p class="text-xs text-inksoft mb-4">Click <span class="font-semibold">Pick on grid</span> next to a field, then click the cell on the right where it should go.</p>
            <div id="pickBanner" class="hidden text-xs bg-brasslt/60 text-ink border border-brass/40 rounded-lg px-3 py-2 mb-3"></div>
            <div id="fieldGroups" class="space-y-5 max-h-[65vh] overflow-y-auto pr-1"></div>
        </div>

        <!-- Grid -->
        <div class="bg-white border border-line rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-display text-lg font-semibold">Template Grid</h2>
                <button id="btnReloadGrid" class="text-xs text-inksoft hover:text-ink border border-line rounded-lg px-3 py-1.5">↻ Reload</button>
            </div>
            <div id="gridWrap" class="border border-line rounded-lg">
                <table class="grid-table" id="gridTable"></table>
            </div>
            <p class="text-[11px] text-inksoft mt-2">Cells already highlighted with a colored tag are assigned to a field. A red outline in the field list means two fields share the same cell.</p>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 mt-6">
        <span id="saveFeedback" class="text-xs"></span>
        <button id="btnSaveMapping" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-emerald-800 hover:bg-emerald-900 text-white">
            Save Mapping
        </button>
    </div>
</div>

<script>
const FIELD_GROUPS = [
    { title: 'Employee Info', color: 'bg-sky-100 text-sky-800 border-sky-300', keys: ['name','ic_number','position','salary_month','payment_date','bank_account'] },
    { title: 'Earnings', color: 'bg-emerald-100 text-emerald-800 border-emerald-300', keys: ['basic_salary','overtime','others'] },
    { title: 'Employee Deductions', color: 'bg-rose-100 text-rose-800 border-rose-300', keys: ['epf_employee','socso_employee','socso24_employee','staff_loan'] },
    { title: "Employer Contributions", color: 'bg-violet-100 text-violet-800 border-violet-300', keys: ['epf_employer','socso_employer','eis_employer'] },
];

let fieldsByKey = {};   // key -> {key, name, cell, label, show_label}
let cellsPreview = {};  // "A1" -> preview text
let merges = [];
let rowCount = 20, colCount = 12;
let armedFieldKey = null;

function colLetters(n) {
    let s = '';
    while (n > 0) {
        const rem = (n - 1) % 26;
        s = String.fromCharCode(65 + rem) + s;
        n = Math.floor((n - 1) / 26);
    }
    return s;
}

function shortCode(name) {
    return name.split(/\s+/).map(w => w[0]).join('').toUpperCase().slice(0, 4);
}

function groupColorFor(key) {
    for (const g of FIELD_GROUPS) if (g.keys.includes(key)) return g.color;
    return 'bg-slate-100 text-slate-700 border-slate-300';
}

async function loadGrid() {
    const res = await fetch('api/get_template_grid.php');
    const data = await res.json();
    if (data.status !== 'success') {
        document.getElementById('gridTable').innerHTML = `<tr><td class="p-4 text-rose-600">${data.message}</td></tr>`;
        return;
    }
    rowCount = data.rowCount;
    colCount = data.colCount;
    cellsPreview = data.cells;
    merges = data.merges;

    fieldsByKey = {};
    data.fields.forEach(f => { fieldsByKey[f.key] = f; });

    renderFieldGroups();
    renderGrid();
}

function findConflicts() {
    const byCell = {};
    Object.values(fieldsByKey).forEach(f => {
        if (!f.cell) return;
        byCell[f.cell] = byCell[f.cell] || [];
        byCell[f.cell].push(f.key);
    });
    const conflicted = new Set();
    Object.values(byCell).forEach(keys => { if (keys.length > 1) keys.forEach(k => conflicted.add(k)); });
    return conflicted;
}

function renderFieldGroups() {
    const conflicts = findConflicts();
    const container = document.getElementById('fieldGroups');
    container.innerHTML = '';

    FIELD_GROUPS.forEach(group => {
        const wrap = document.createElement('div');
        const heading = document.createElement('p');
        heading.className = 'text-[11px] font-mono uppercase tracking-widest text-inksoft mb-2';
        heading.textContent = group.title;
        wrap.appendChild(heading);

        group.keys.forEach(key => {
            const f = fieldsByKey[key];
            if (!f) return;
            const row = document.createElement('div');
            row.className = 'field-row border border-line rounded-lg p-3 mb-2' + (armedFieldKey === key ? ' armed' : '') + (conflicts.has(key) ? ' conflict' : '');
            row.dataset.key = key;

            row.innerHTML = `
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-sm font-medium text-ink">${f.name}</span>
                    <span class="font-mono text-xs px-2 py-0.5 rounded border ${groupColorFor(key)}">${f.cell || '—'}</span>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <button type="button" data-action="pick" class="text-xs px-2.5 py-1 rounded-md bg-ink text-white hover:bg-ink/90">Pick on grid</button>
                    <input type="text" data-action="manual-cell" value="${f.cell || ''}" placeholder="e.g. B4"
                        class="w-20 text-xs font-mono border border-line rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-brass/50">
                </div>
                <label class="flex items-center gap-2 text-xs text-inksoft mb-1">
                    <input type="checkbox" data-action="show-label" ${f.show_label ? 'checked' : ''}> Include a label in the cell
                </label>
                <input type="text" data-action="label-text" value="${(f.label || '').replace(/"/g, '&quot;')}" placeholder="Label text, e.g. Employee Name :"
                    class="w-full text-xs border border-line rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-brass/50">
            `;

            row.querySelector('[data-action="pick"]').addEventListener('click', () => armField(key));
            row.querySelector('[data-action="manual-cell"]').addEventListener('change', (e) => {
                setFieldCell(key, e.target.value.trim().toUpperCase());
            });
            row.querySelector('[data-action="show-label"]').addEventListener('change', (e) => {
                fieldsByKey[key].show_label = e.target.checked;
            });
            row.querySelector('[data-action="label-text"]').addEventListener('input', (e) => {
                fieldsByKey[key].label = e.target.value;
            });

            wrap.appendChild(row);
        });

        container.appendChild(wrap);
    });
}

function armField(key) {
    armedFieldKey = (armedFieldKey === key) ? null : key;
    const banner = document.getElementById('pickBanner');
    const gridTable = document.getElementById('gridTable');
    if (armedFieldKey) {
        banner.classList.remove('hidden');
        banner.textContent = `Click a cell in the grid to assign "${fieldsByKey[armedFieldKey].name}" (click the button again to cancel).`;
        gridTable.classList.add('armed');
    } else {
        banner.classList.add('hidden');
        gridTable.classList.remove('armed');
    }
    renderFieldGroups();
}

function setFieldCell(key, cell) {
    if (!/^[A-Z]{1,3}[1-9][0-9]{0,6}$/.test(cell)) {
        alert(`"${cell}" doesn't look like a valid cell reference (e.g. B4).`);
        renderFieldGroups();
        return;
    }
    fieldsByKey[key].cell = cell;
    renderFieldGroups();
    renderGrid();
}

function renderGrid() {
    // Figure out which cells are covered by a merge (and by what top-left cell)
    const mergeTopLeft = {};   // "r-c" -> {rowspan, colspan}
    const covered = new Set(); // "r-c" of cells hidden inside a merge

    merges.forEach(m => {
        mergeTopLeft[`${m.startRow}-${m.startCol}`] = {
            rowspan: m.endRow - m.startRow + 1,
            colspan: m.endCol - m.startCol + 1,
        };
        for (let r = m.startRow; r <= m.endRow; r++) {
            for (let c = m.startCol; c <= m.endCol; c++) {
                if (r === m.startRow && c === m.startCol) continue;
                covered.add(`${r}-${c}`);
            }
        }
    });

    // cell -> [fieldKey,...] assigned to it
    const assigned = {};
    Object.values(fieldsByKey).forEach(f => {
        if (!f.cell) return;
        assigned[f.cell] = assigned[f.cell] || [];
        assigned[f.cell].push(f.key);
    });

    let html = '<thead><tr><th class="rowhead"></th>';
    for (let c = 1; c <= colCount; c++) html += `<th>${colLetters(c)}</th>`;
    html += '</tr></thead><tbody>';

    for (let r = 1; r <= rowCount; r++) {
        html += `<tr><th class="rowhead">${r}</th>`;
        for (let c = 1; c <= colCount; c++) {
            const key = `${r}-${c}`;
            if (covered.has(key)) continue;

            const coord = colLetters(c) + r;
            const span = mergeTopLeft[key];
            const spanAttrs = span ? `rowspan="${span.rowspan}" colspan="${span.colspan}"` : '';

            const fieldKeys = assigned[coord] || [];
            let cellClass = 'datacell';
            let content = cellsPreview[coord] || '';

            if (fieldKeys.length) {
                const badgeColor = groupColorFor(fieldKeys[0]);
                const codes = fieldKeys.map(k => shortCode(fieldsByKey[k].name)).join('+');
                content = `<span class="inline-block text-[10px] font-mono font-semibold px-1 rounded border ${badgeColor}">${codes}</span> ${content}`;
            }

            html += `<td class="${cellClass}" data-coord="${coord}" ${spanAttrs} title="${coord}">${content}</td>`;
        }
        html += '</tr>';
    }
    html += '</tbody>';

    const table = document.getElementById('gridTable');
    table.innerHTML = html;

    table.querySelectorAll('td.datacell').forEach(td => {
        td.addEventListener('click', () => {
            if (!armedFieldKey) return;
            setFieldCell(armedFieldKey, td.dataset.coord);
            armField(armedFieldKey); // disarm after assigning
        });
    });
}

async function saveMapping() {
    const feedback = document.getElementById('saveFeedback');
    const btn = document.getElementById('btnSaveMapping');
    btn.disabled = true;
    feedback.className = 'text-xs text-inksoft';
    feedback.textContent = 'Saving…';

    const payload = {};
    Object.values(fieldsByKey).forEach(f => {
        payload[f.key] = { cell: f.cell, label: f.label, show_label: !!f.show_label };
    });

    try {
        const res = await fetch('api/save_template_config.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.status === 'success') {
            feedback.className = 'text-xs text-emerald-700';
            feedback.textContent = data.message;
        } else {
            feedback.className = 'text-xs text-rose-600';
            feedback.textContent = data.message;
        }
    } catch (e) {
        feedback.className = 'text-xs text-rose-600';
        feedback.textContent = 'Server communication error.';
    } finally {
        btn.disabled = false;
    }
}

document.getElementById('btnSaveMapping').addEventListener('click', saveMapping);
document.getElementById('btnReloadGrid').addEventListener('click', loadGrid);

document.getElementById('formUploadTemplate').addEventListener('submit', async (e) => {
    e.preventDefault();
    const feedback = document.getElementById('uploadTemplateFeedback');
    const btn = document.getElementById('btnUploadTemplate');
    const fileInput = document.getElementById('templateFile');

    if (!fileInput.files.length) return;

    btn.disabled = true;
    feedback.className = 'text-xs text-inksoft';
    feedback.textContent = 'Uploading…';

    const formData = new FormData();
    formData.append('template_file', fileInput.files[0]);

    try {
        const res = await fetch('api/upload_template.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.status === 'success') {
            feedback.className = 'text-xs text-emerald-700';
            feedback.textContent = data.message;
            fileInput.value = '';
            await loadGrid();
        } else {
            feedback.className = 'text-xs text-rose-600';
            feedback.textContent = data.message;
        }
    } catch (err) {
        feedback.className = 'text-xs text-rose-600';
        feedback.textContent = 'Server communication error.';
    } finally {
        btn.disabled = false;
    }
});

loadGrid();
</script>

</body>
</html>
