<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-PaySlip | Payslip Desk</title>
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
  .section-label {
    letter-spacing: 0.12em;
  }
  input:disabled, input[readonly] {
    background-color: #F3F1EA;
    color: #8A8471;
  }
</style>
</head>
<body class="min-h-screen font-body text-ink">

<div class="max-w-2xl mx-auto px-4 py-10 sm:py-14">

    <!-- Breadcrumb / back -->
    <a href="index.php" class="inline-flex items-center gap-1 text-sm text-inksoft hover:text-ink mb-6">
        ← Back to desk selection
    </a>

    <!-- Header -->
    <div class="mb-8">
        <div class="tab bg-ink text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit -mb-1 relative z-10">
            Form 001
        </div>
        <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm px-6 py-5">
            <h1 class="font-display text-2xl font-semibold text-ink">Payslip Desk</h1>
            <p class="text-sm text-inksoft mt-1">Select staff, confirm details, generate the Excel payslip.</p>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-line p-6">

        <!-- Employee selector row -->
        <div class="flex flex-col sm:flex-row gap-2 mb-6">
            <select id="employeeSelect"
                    class="flex-1 border border-line rounded-lg px-3 py-2 text-ink bg-white focus:outline-none focus:ring-2 focus:ring-brass/50">
                <option value="">-- Select Employee --</option>
            </select>

            <button id="btnAddStaff" type="button"
                    class="px-4 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium whitespace-nowrap">
                ➕ Add New Staff
            </button>

            <button id="btnDeleteStaff" type="button"
                    class="px-4 py-2 rounded-lg bg-rose-700 hover:bg-rose-800 text-white text-sm font-medium whitespace-nowrap">
                🗑️ Delete Staff
            </button>
        </div>

        <!-- Payslip form -->
        <form id="payslipForm" action="generate.php" method="POST" class="space-y-6">

            <!-- ============ Profile fields ============ -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Employee Name</label>
                    <input type="text" name="name" id="field_name"
                           class="w-full border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">IC Number</label>
                    <input type="text" name="ic_number" id="field_ic_number"
                           class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Position</label>
                    <input type="text" name="position" id="field_position"
                           class="w-full border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Bank Account Number</label>
                    <input type="text" name="bank_account" id="field_bank_account"
                           class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Net Salary (RM) <span class="text-inksoft/60 font-normal">— Basic Salary</span></label>
                    <input type="number" step="0.01" name="net_salary" id="field_net_salary"
                           class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-inksoft mb-1">Month</label>
                        <select name="month" id="field_month"
                                class="w-full border border-line rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brass/50">
                            <?php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                            ];
                            $currentMonth = (int) date('n');
                            foreach ($months as $num => $label) {
                                $selected = $num === $currentMonth ? 'selected' : '';
                                echo "<option value=\"{$num}\" {$selected}>{$label}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-inksoft mb-1">Year</label>
                        <select name="year" id="field_year"
                                class="w-full border border-line rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-brass/50">
                            <?php
                            $currentYear = (int) date('Y');
                            for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
                                $selected = $y === $currentYear ? 'selected' : '';
                                echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Payment Date</label>
                    <input type="date" name="payment_date" id="field_payment_date"
                           class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>
            </div>

            <!-- ============ EPF / SOCSO toggle ============ -->
            <label class="flex items-center gap-2 bg-brasslt/30 border border-brasslt rounded-lg px-4 py-3 cursor-pointer">
                <input type="checkbox" id="field_epf_socso_enabled" checked
                       class="w-4 h-4 accent-brass">
                <span class="text-sm text-ink">Deduct EPF / SOCSO for this payslip</span>
                <input type="hidden" name="epf_socso_enabled" id="field_epf_socso_enabled_hidden" value="1">
            </label>

            <!-- ============ Deductions ============ -->
            <div class="perforated-none">
                <p class="section-label text-xs font-mono uppercase text-inksoft mb-2 border-t border-line pt-4">Deductions</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-inksoft mb-1">EPF (11%)</label>
                        <input type="text" id="field_epf_employee" readonly
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm">
                        <input type="hidden" name="epf_employee" id="field_epf_employee_hidden" value="0">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">SOCSO</label>
                        <input type="number" step="0.01" name="socso_employee" id="field_socso_employee"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">SOCSO Lindung 24 Jam</label>
                        <input type="number" step="0.01" name="socso24_employee" id="field_socso24_employee"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>
            </div>

            <!-- ============ Employer's Contribution ============ -->
            <div>
                <p class="section-label text-xs font-mono uppercase text-inksoft mb-2 border-t border-line pt-4">Employer's Contribution</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-inksoft mb-1">EPF (13%)</label>
                        <input type="text" id="field_epf_employer" readonly
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm">
                        <input type="hidden" name="epf_employer" id="field_epf_employer_hidden" value="0">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">SOCSO</label>
                        <input type="number" step="0.01" name="socso_employer" id="field_socso_employer"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">EIS</label>
                        <input type="number" step="0.01" name="eis_employer" id="field_eis_employer"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full mt-2 bg-ink hover:bg-ink/90 text-white font-semibold py-3 rounded-lg">
                📄 Generate Excel Payslip
            </button>
        </form>
    </div>
</div>

<!-- ============ Add Staff Modal ============ -->
<div id="addStaffModal" class="hidden fixed inset-0 bg-ink/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="font-display text-lg font-semibold text-ink mb-4">➕ Add New Staff</h2>

        <div class="space-y-3">
            <input type="text" id="new_name" placeholder="Employee Name"
                   class="w-full border border-line rounded-lg px-3 py-2">
            <input type="text" id="new_ic_number" placeholder="IC Number"
                   class="w-full border border-line rounded-lg px-3 py-2 font-mono">
            <input type="text" id="new_position" placeholder="Position"
                   class="w-full border border-line rounded-lg px-3 py-2">
            <input type="text" id="new_bank_account" placeholder="Bank Account Number"
                   class="w-full border border-line rounded-lg px-3 py-2 font-mono">
            <input type="number" step="0.01" id="new_net_salary" placeholder="Net Salary"
                   class="w-full border border-line rounded-lg px-3 py-2 font-mono">

            <label class="flex items-center gap-2 bg-brasslt/30 border border-brasslt rounded-lg px-3 py-2 cursor-pointer">
                <input type="checkbox" id="new_epf_socso_enabled" checked class="w-4 h-4 accent-brass">
                <span class="text-sm text-ink">Staff is subject to EPF / SOCSO deduction</span>
            </label>
        </div>

        <p class="text-xs text-inksoft mt-2">
            SOCSO / EIS amounts are entered fresh on each payslip, so they're not asked for here.
        </p>

        <p id="addStaffError" class="text-rose-600 text-sm mt-2 hidden"></p>

        <div class="flex justify-end gap-2 mt-5">
            <button type="button" onclick="closeModal('addStaffModal')"
                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-ink text-sm">Cancel</button>
            <button type="button" id="btnSaveStaff"
                    class="px-4 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-sm">Save Staff</button>
        </div>
    </div>
</div>

<!-- ============ Delete Staff Modal ============ -->
<div id="deleteStaffModal" class="hidden fixed inset-0 bg-ink/50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="font-display text-lg font-semibold text-ink mb-4">🗑️ Delete Staff</h2>

        <ul id="deleteStaffList" class="divide-y divide-line max-h-72 overflow-y-auto"></ul>

        <div class="flex justify-end mt-5">
            <button type="button" onclick="closeModal('deleteStaffModal')"
                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-ink text-sm">Close</button>
        </div>
    </div>
</div>

<script>
// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

const els = {
    netSalary:        document.getElementById('field_net_salary'),
    enabled:           document.getElementById('field_epf_socso_enabled'),
    enabledHidden:      document.getElementById('field_epf_socso_enabled_hidden'),
    epfEmployee:       document.getElementById('field_epf_employee'),
    epfEmployeeHidden: document.getElementById('field_epf_employee_hidden'),
    epfEmployer:       document.getElementById('field_epf_employer'),
    epfEmployerHidden: document.getElementById('field_epf_employer_hidden'),
    socsoEmployee:     document.getElementById('field_socso_employee'),
    socso24Employee:   document.getElementById('field_socso24_employee'),
    socsoEmployer:     document.getElementById('field_socso_employer'),
    eisEmployer:       document.getElementById('field_eis_employer'),
};

// ---------------------------------------------------------------
// EPF (11% / 13%) auto-calculation + enable/disable of manual fields
// ---------------------------------------------------------------
function recalcEPF() {
    const isEnabled = els.enabled.checked;
    const net = parseFloat(els.netSalary.value) || 0;

    const epfEmployee = isEnabled ? (net * 0.11) : 0;
    const epfEmployer = isEnabled ? (net * 0.13) : 0;

    els.epfEmployee.value = epfEmployee.toFixed(2);
    els.epfEmployeeHidden.value = epfEmployee.toFixed(2);
    els.epfEmployer.value = epfEmployer.toFixed(2);
    els.epfEmployerHidden.value = epfEmployer.toFixed(2);
}

function applyEnabledState() {
    const isEnabled = els.enabled.checked;
    els.enabledHidden.value = isEnabled ? '1' : '0';

    [els.socsoEmployee, els.socso24Employee, els.socsoEmployer, els.eisEmployer].forEach(el => {
        el.disabled = !isEnabled;
        if (!isEnabled) el.value = '0';
    });

    recalcEPF();
}

els.netSalary.addEventListener('input', recalcEPF);
els.enabled.addEventListener('change', applyEnabledState);

// ---------------------------------------------------------------
// Load employee dropdown
// ---------------------------------------------------------------
async function loadEmployeeDropdown(selectedId = '') {
    const res = await fetch('api/get_employees.php');
    const employees = await res.json();

    const select = document.getElementById('employeeSelect');
    select.innerHTML = '<option value="">-- Select Employee --</option>';

    employees.forEach(emp => {
        const opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.name;
        if (String(emp.id) === String(selectedId)) opt.selected = true;
        select.appendChild(opt);
    });
}

// ---------------------------------------------------------------
// Autofill form when an employee is selected
//
// Sticky (left alone):    Month, Year, Payment Date
// From employee profile:  Name, IC, Position, Bank, Net Salary,
//                          EPF/SOCSO toggle
// Fresh every time:       SOCSO, SOCSO 24hr, Employer SOCSO, Employer EIS
// ---------------------------------------------------------------
document.getElementById('employeeSelect').addEventListener('change', async function () {
    const id = this.value;
    if (!id) return;

    const res = await fetch(`api/get_employee.php?id=${id}`);
    if (!res.ok) return;

    const emp = await res.json();

    document.getElementById('field_name').value = emp.name ?? '';
    document.getElementById('field_ic_number').value = emp.ic_number ?? '';
    document.getElementById('field_position').value = emp.position ?? '';
    document.getElementById('field_bank_account').value = emp.bank_account ?? '';
    els.netSalary.value = emp.net_salary ?? '';
    // field_month / field_year / field_payment_date deliberately NOT touched (sticky)

    els.enabled.checked = String(emp.epf_socso_enabled) !== '0';

    // Manual entries reset fresh for every payslip run
    els.socsoEmployee.value = '';
    els.socso24Employee.value = '';
    els.socsoEmployer.value = '';
    els.eisEmployer.value = '';

    applyEnabledState();
});

// ---------------------------------------------------------------
// Add Staff modal
// ---------------------------------------------------------------
document.getElementById('btnAddStaff').addEventListener('click', () => {
    document.getElementById('new_name').value = '';
    document.getElementById('new_ic_number').value = '';
    document.getElementById('new_position').value = '';
    document.getElementById('new_bank_account').value = '';
    document.getElementById('new_net_salary').value = '';
    document.getElementById('new_epf_socso_enabled').checked = true;
    document.getElementById('addStaffError').classList.add('hidden');
    openModal('addStaffModal');
});

document.getElementById('btnSaveStaff').addEventListener('click', async () => {
    const name = document.getElementById('new_name').value.trim();
    const errorEl = document.getElementById('addStaffError');

    if (!name) {
        errorEl.textContent = 'Employee name is required.';
        errorEl.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('ic_number', document.getElementById('new_ic_number').value.trim());
    formData.append('position', document.getElementById('new_position').value.trim());
    formData.append('bank_account', document.getElementById('new_bank_account').value.trim());
    formData.append('net_salary', document.getElementById('new_net_salary').value || 0);
    formData.append('epf_socso_enabled', document.getElementById('new_epf_socso_enabled').checked ? '1' : '0');

    const res = await fetch('api/add_employee.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
        closeModal('addStaffModal');
        await loadEmployeeDropdown(data.id);
        document.getElementById('employeeSelect').dispatchEvent(new Event('change'));
    } else {
        errorEl.textContent = data.error || 'Something went wrong.';
        errorEl.classList.remove('hidden');
    }
});

// ---------------------------------------------------------------
// Delete Staff modal
// ---------------------------------------------------------------
document.getElementById('btnDeleteStaff').addEventListener('click', async () => {
    const res = await fetch('api/get_employees.php');
    const employees = await res.json();

    const list = document.getElementById('deleteStaffList');
    list.innerHTML = '';

    if (employees.length === 0) {
        list.innerHTML = '<li class="py-3 text-sm text-inksoft">No staff records yet.</li>';
    }

    employees.forEach(emp => {
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between py-2';
        li.innerHTML = `
            <span class="text-sm text-ink">${emp.name}</span>
            <button class="px-3 py-1 text-xs rounded-md bg-rose-700 hover:bg-rose-800 text-white"
                    data-id="${emp.id}" data-name="${emp.name}">Delete</button>
        `;
        list.appendChild(li);
    });

    openModal('deleteStaffModal');
});

document.getElementById('deleteStaffList').addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;

    const id = btn.dataset.id;
    const name = btn.dataset.name;

    if (!confirm(`Are you sure you want to delete "${name}"? This cannot be undone.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    const res = await fetch('api/delete_employee.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
        btn.closest('li').remove();
        await loadEmployeeDropdown();

        // If the deleted employee was currently loaded in the form, clear the form
        if (document.getElementById('employeeSelect').value === id) {
            document.getElementById('payslipForm').reset();
        }
    } else {
        alert(data.error || 'Failed to delete employee.');
    }
});

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------
applyEnabledState();
loadEmployeeDropdown();
</script>

</body>
</html>
