<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-PaySlip | Payslip Desk</title>
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

    <a href="index.php" class="inline-flex items-center gap-1 text-sm text-inksoft hover:text-ink mb-6">
        ← Back to desk selection
    </a>

    <!-- Header -->
    <div class="mb-8">
        <div class="tab bg-ink text-paper text-xs font-mono tracking-widest uppercase px-4 py-2 w-fit -mb-1 relative z-10">
            Desk 002
        </div>
        <div class="bg-white border border-line rounded-b-xl rounded-tr-xl shadow-sm px-6 py-5">
            <h1 class="font-display text-2xl font-semibold text-ink">Payslip Desk</h1>
            <p class="text-sm text-inksoft mt-1">Select month and staff to review details or generate the payslip.</p>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-line p-6">

        <!-- Month & Employee Selectors -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6 bg-slate-50 border border-line p-3.5 rounded-xl">
            <div>
                <label class="block text-xs font-semibold text-ink uppercase tracking-wider mb-1">1. Salary Month</label>
                <select id="monthSelect"
                        class="w-full border border-line rounded-lg px-3 py-2 text-ink bg-white font-medium text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    <option value="">-- Select Payroll Month --</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-ink uppercase tracking-wider mb-1">2. Employee</label>
                <select id="employeeSelect" disabled
                        class="w-full border border-line rounded-lg px-3 py-2 text-ink bg-white font-medium text-sm focus:outline-none focus:ring-2 focus:ring-brass/50 disabled:opacity-50">
                    <option value="">-- Choose Month First --</option>
                </select>
            </div>
        </div>

        <!-- Payslip Form -->
        <form id="payslipForm" action="generate.php" method="POST" class="space-y-6">
            <input type="hidden" name="payslip_id" id="field_payslip_id">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Employee Name</label>
                    <input type="text" name="name" id="field_name" required
                           class="w-full border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brass/50">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-inksoft mb-1">IC Number (NRIC)</label>
                        <input type="text" name="ic_number" id="field_ic_number" required
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-inksoft mb-1">Position</label>
                        <input type="text" name="position" id="field_position"
                               class="w-full border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-inksoft mb-1">Bank Account Number</label>
                        <input type="text" name="bank_account" id="field_bank_account"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-inksoft mb-1">Disbursement Date</label>
                        <input type="date" name="payment_date" id="field_payment_date"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-inksoft mb-1">Payroll Month</label>
                    <input type="text" name="salary_month" id="field_salary_month" readonly
                           class="w-full border border-line rounded-lg px-3 py-2 font-mono">
                </div>
            </div>

            <!-- Earnings -->
            <div>
                <p class="section-label text-xs font-mono uppercase text-inksoft mb-2 border-t border-line pt-4">Earnings (RM)</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-inksoft mb-1">Basic Salary</label>
                        <input type="number" step="0.01" name="basic_salary" id="field_basic_salary"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">Overtime</label>
                        <input type="number" step="0.01" name="overtime" id="field_overtime"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">Others</label>
                        <input type="number" step="0.01" name="others" id="field_others"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div>
                <p class="section-label text-xs font-mono uppercase text-inksoft mb-2 border-t border-line pt-4">Deductions (RM)</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-inksoft mb-1">EPF (Employee)</label>
                        <input type="number" step="0.01" name="epf_employee" id="field_epf_employee"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">SOCSO</label>
                        <input type="number" step="0.01" name="socso_employee" id="field_socso_employee"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">SOCSO 24 Jam</label>
                        <input type="number" step="0.01" name="socso24_employee" id="field_socso24_employee"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">HPCS Staff Loan</label>
                        <input type="number" step="0.01" name="staff_loan" id="field_staff_loan"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>
            </div>

            <!-- Employer Contributions -->
            <div>
                <p class="section-label text-xs font-mono uppercase text-inksoft mb-2 border-t border-line pt-4">Employer's Contribution (RM)</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-inksoft mb-1">Employer EPF</label>
                        <input type="number" step="0.01" name="employer_epf" id="field_employer_epf"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">Employer SOCSO</label>
                        <input type="number" step="0.01" name="employer_socso" id="field_employer_socso"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                    <div>
                        <label class="block text-xs text-inksoft mb-1">Employer EIS</label>
                        <input type="number" step="0.01" name="employer_eis" id="field_employer_eis"
                               class="w-full border border-line rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-brass/50">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-line flex flex-col sm:flex-row gap-3">
                <button type="submit" name="format" value="xlsx"
                        class="flex-1 bg-ink hover:bg-ink/90 text-white font-semibold py-3 rounded-lg flex items-center justify-center gap-2">
                    📄 Generate Excel Payslip
                </button>
                <button type="submit" name="format" value="pdf"
                        class="flex-1 bg-rose-700 hover:bg-rose-800 text-white font-semibold py-3 rounded-lg flex items-center justify-center gap-2">
                    📄 Generate PDF Payslip
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const monthSelect = document.getElementById('monthSelect');
const employeeSelect = document.getElementById('employeeSelect');

async function loadMonths() {
    try {
        const res = await fetch('api/get_months.php');
        const data = await res.json();

        monthSelect.innerHTML = '<option value="">-- Select Payroll Month --</option>';
        if (data.status === 'success' && data.months && data.months.length > 0) {
            data.months.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m;
                monthSelect.appendChild(opt);
            });
        } else {
            monthSelect.innerHTML = '<option value="">No payroll uploaded yet</option>';
        }
    } catch (e) {
        monthSelect.innerHTML = '<option value="">Failed to load months</option>';
    }
}

monthSelect.addEventListener('change', async function () {
    const selectedMonth = this.value;
    employeeSelect.innerHTML = '<option value="">Loading staff...</option>';
    employeeSelect.disabled = true;

    if (!selectedMonth) {
        employeeSelect.innerHTML = '<option value="">-- Choose Month First --</option>';
        return;
    }

    try {
        const res = await fetch(`api/get_employees.php?month=${encodeURIComponent(selectedMonth)}`);
        const employees = await res.json();

        employeeSelect.innerHTML = '<option value="">-- Select Employee --</option>';
        if (employees.length > 0) {
            employees.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = `${emp.employee_name} (${emp.nric})`;
                employeeSelect.appendChild(opt);
            });
            employeeSelect.disabled = false;
        } else {
            employeeSelect.innerHTML = '<option value="">No records for this month</option>';
        }
    } catch (e) {
        employeeSelect.innerHTML = '<option value="">Error loading employees</option>';
    }
});

employeeSelect.addEventListener('change', async function () {
    const id = this.value;
    if (!id) return;

    try {
        const res = await fetch(`api/get_employee.php?id=${id}`);
        if (!res.ok) return;

        const p = await res.json();

        document.getElementById('field_payslip_id').value = p.id ?? '';
        document.getElementById('field_name').value = p.employee_name ?? '';
        document.getElementById('field_ic_number').value = p.nric ?? '';
        document.getElementById('field_position').value = p.position ?? '';
        document.getElementById('field_bank_account').value = p.bank_account_no ?? '';
        document.getElementById('field_salary_month').value = p.salary_month ?? '';
        document.getElementById('field_payment_date').value = p.payment_date ?? '';

        document.getElementById('field_basic_salary').value = parseFloat(p.basic_salary || 0).toFixed(2);
        document.getElementById('field_overtime').value = parseFloat(p.overtime || 0).toFixed(2);
        document.getElementById('field_others').value = parseFloat(p.others || 0).toFixed(2);

        document.getElementById('field_epf_employee').value = parseFloat(p.deduction_epf || 0).toFixed(2);
        document.getElementById('field_socso_employee').value = parseFloat(p.deduction_socso || 0).toFixed(2);
        document.getElementById('field_socso24_employee').value = parseFloat(p.deduction_socso_lindung_24jam || 0).toFixed(2);
        document.getElementById('field_staff_loan').value = parseFloat(p.hpcs_staff_loan || 0).toFixed(2);

        document.getElementById('field_employer_epf').value = parseFloat(p.employer_epf || 0).toFixed(2);
        document.getElementById('field_employer_socso').value = parseFloat(p.employer_socso || 0).toFixed(2);
        document.getElementById('field_employer_eis').value = parseFloat(p.employer_eis || 0).toFixed(2);
    } catch (e) {
        alert('Could not fetch employee details.');
    }
});

loadMonths();
</script>

</body>
</html>