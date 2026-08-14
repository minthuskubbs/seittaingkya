<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorNoteController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Login only (self-registration is disabled — accounts are created by admins).
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::middleware(['auth', 'active', 'clinic.context'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super admin: pick the "working clinic" that new records are filed under.
    Route::get('/switch-clinic/{clinic}', function (\App\Models\Clinic $clinic) {
        session(['active_clinic_id' => $clinic->id]);
        return back()->with('status', "Working clinic set to {$clinic->name}.");
    })->name('switch-clinic');

    // Patients & clinical records
    Route::resource('patients', PatientController::class);
    Route::resource('doctors', \App\Http\Controllers\DoctorController::class)->except('show');
    Route::resource('staff', \App\Http\Controllers\StaffController::class)->except('show')->parameters(['staff' => 'staff']);
    // Calendar must be declared before the resource so "calendar" isn't treated as an {appointment} id.
    Route::get('appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::resource('appointments', AppointmentController::class);
    Route::put('appointments/{appointment}/doctor-note', [DoctorNoteController::class, 'update'])->name('appointments.doctor_note');

    // Doctor feedback in patient history (add = staff; edit = super admin only).
    Route::post('patients/{patient}/feedback', [\App\Http\Controllers\DoctorFeedbackController::class, 'store'])->name('patients.feedback.store');
    Route::put('feedback/{feedback}', [\App\Http\Controllers\DoctorFeedbackController::class, 'update'])->name('feedback.update');

    // Treatment is the billing record: charges, payments and the printable invoice.
    Route::resource('treatments', TreatmentController::class);
    Route::post('treatments/{treatment}/payments', [PaymentController::class, 'store'])->name('treatments.payments.store');
    Route::delete('treatments/{treatment}/payments/{payment}', [PaymentController::class, 'destroy'])->name('treatments.payments.destroy');
    Route::get('treatments/{treatment}/invoice', [PaymentController::class, 'invoice'])->name('treatments.invoice');
    Route::resource('procedures', ProcedureController::class)->except('show');
    Route::resource('treatment-types', \App\Http\Controllers\TreatmentTypeController::class)->except('show')->parameters(['treatment-types' => 'treatmentType']);
    Route::resource('tooth-charge-types', \App\Http\Controllers\ToothChargeTypeController::class)->except('show')->parameters(['tooth-charge-types' => 'toothChargeType']);
    Route::resource('denture-types', \App\Http\Controllers\DentureTypeController::class)->except('show')->parameters(['denture-types' => 'dentureType']);

    // Attachments (x-ray / documents)
    Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Fees (super admin managed)
    Route::resource('fees', FeeController::class)->except('show');
    Route::resource('sale-types', \App\Http\Controllers\SaleTypeController::class)->except('show')->parameters(['sale-types' => 'saleType']);

    // Inventory
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('stock', [StockController::class, 'store'])->name('stock.store');

    // POS / billing
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

    // Finance (super admin only via permission)
    Route::get('finance/revenue', [FinanceController::class, 'revenue'])->name('finance.revenue');
    Route::get('finance/outstanding', [FinanceController::class, 'outstanding'])->name('finance.outstanding');
    Route::get('finance/incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::post('finance/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::delete('finance/incomes/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');

    // Expenses + payroll (finance = super admin only)
    Route::get('finance/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('finance/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('finance/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('finance/doctor-payroll', [\App\Http\Controllers\DoctorPayrollController::class, 'index'])->name('finance.doctor_payroll');
    Route::post('finance/doctor-payroll', [\App\Http\Controllers\DoctorPayrollController::class, 'store'])->name('finance.doctor_payroll.store');
    Route::get('finance/staff-payroll', [\App\Http\Controllers\StaffPayrollController::class, 'index'])->name('finance.staff_payroll');
    Route::post('finance/staff-payroll', [\App\Http\Controllers\StaffPayrollController::class, 'store'])->name('finance.staff_payroll.store');
    Route::resource('expense-types', \App\Http\Controllers\ExpenseTypeController::class)->except('show')->parameters(['expense-types' => 'expenseType']);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/patients', [ReportController::class, 'patients'])->name('reports.patients');
    Route::get('reports/treatments', [ReportController::class, 'treatments'])->name('reports.treatments');
    Route::get('reports/treatment-list', [ReportController::class, 'treatmentList'])->name('reports.treatment_list');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');

    // Administration
    Route::resource('users', UserController::class)->except('show');
    Route::resource('clinics', ClinicController::class)->except('show');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // Active user sessions (who is logged in, device, IP) — super admin only.
    Route::get('sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::delete('sessions/{id}', [\App\Http\Controllers\SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::controller(BackupController::class)->prefix('backup')->name('backup.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('create', 'create')->name('create');
        Route::get('download/{file}', 'download')->name('download');
        Route::post('restore', 'restore')->name('restore');
    });

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // PWA push + offline sync
    Route::post('push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push.store');
    Route::delete('push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.destroy');
    Route::post('sync', [SyncController::class, 'push'])->name('sync.push');
});
