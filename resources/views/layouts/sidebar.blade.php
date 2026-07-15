@php $u = auth()->user(); @endphp
<nav class="app-sidebar">
    <div class="brand">
        <img src="{{ asset('vendor/pwa/logo.png') }}" alt="{{ config('app.name') }}" class="brand-logo">
    </div>

    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    @canany(['patients.view','appointments.view','clinical.view'])
        <div class="nav-section">Clinical</div>
        @can('patients.view')
        <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Patients</a>
        @endcan
        @can('doctors.manage')
        <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Doctors</a>
        @endcan
        @can('appointments.view')
        <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.index') || request()->routeIs('appointments.show') || request()->routeIs('appointments.create') || request()->routeIs('appointments.edit') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Appointments</a>
        <a href="{{ route('appointments.calendar') }}" class="nav-link {{ request()->routeIs('appointments.calendar') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Appointment Calendar</a>
        @endcan
        @can('clinical.view')
        <a href="{{ route('treatments.index') }}" class="nav-link {{ request()->routeIs('treatments.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse"></i> Treatments</a>
        <a href="{{ route('prescriptions.index') }}" class="nav-link {{ request()->routeIs('prescriptions.*') ? 'active' : '' }}">
            <i class="bi bi-capsule"></i> Prescriptions</a>
        @endcan
        @can('procedures.manage')
        <a href="{{ route('procedures.index') }}" class="nav-link {{ request()->routeIs('procedures.*') ? 'active' : '' }}">
            <i class="bi bi-list-check"></i> Procedures</a>
        @endcan
    @endcanany

    @canany(['inventory.view','pos.use','suppliers.manage'])
        <div class="nav-section">Inventory & Sales</div>
        @can('inventory.manage')
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Products</a>
        @endcan
        @canany(['inventory.view','inventory.stockout'])
        <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> @can('inventory.manage') Stock In / Out @else Stock Out @endcan</a>
        @endcanany
        @can('suppliers.manage')
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Suppliers</a>
        @endcan
        @can('pos.use')
        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i class="bi bi-cart3"></i> Medicine Sales (POS)</a>
        @endcan
    @endcanany

    @can('finance.view')
        <div class="nav-section">Finance</div>
        <a href="{{ route('finance.revenue') }}" class="nav-link {{ request()->routeIs('finance.revenue') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i> Revenue</a>
        <a href="{{ route('finance.outstanding') }}" class="nav-link {{ request()->routeIs('finance.outstanding') ? 'active' : '' }}">
            <i class="bi bi-exclamation-circle"></i> Outstanding</a>
        <a href="{{ route('incomes.index') }}" class="nav-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> Income</a>
        <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i> Expenses</a>
        <a href="{{ route('finance.doctor_payroll') }}" class="nav-link {{ request()->routeIs('finance.doctor_payroll') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-data"></i> Doctor Payroll</a>
        <a href="{{ route('finance.staff_payroll') }}" class="nav-link {{ request()->routeIs('finance.staff_payroll') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> Staff Payroll</a>
    @endcan

    @can('reports.view')
        <div class="nav-section">Reports</div>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
    @endcan

    @canany(['fees.manage','users.manage','clinics.manage','audit.view','backup.manage'])
        <div class="nav-section">Administration</div>
        @can('fees.manage')
        <a href="{{ route('fees.index') }}" class="nav-link {{ request()->routeIs('fees.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Fees & Charges</a>
        @endcan
        @role('super_admin')
        <a href="{{ route('sale-types.index') }}" class="nav-link {{ request()->routeIs('sale-types.*') ? 'active' : '' }}">
            <i class="bi bi-bookmark-star"></i> Sale Types</a>
        <a href="{{ route('expense-types.index') }}" class="nav-link {{ request()->routeIs('expense-types.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill"></i> Expense Types</a>
        <a href="{{ route('treatment-types.index') }}" class="nav-link {{ request()->routeIs('treatment-types.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i> Treatment Types</a>
        @endrole
        @can('staff.manage')
        <a href="{{ route('staff.index') }}" class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
            <i class="bi bi-person-vcard"></i> Staff</a>
        @endcan
        @can('users.manage')
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Users</a>
        @endcan
        @can('clinics.manage')
        <a href="{{ route('clinics.index') }}" class="nav-link {{ request()->routeIs('clinics.*') ? 'active' : '' }}">
            <i class="bi bi-hospital"></i> Clinics</a>
        @endcan
        @can('audit.view')
        <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Audit Logs</a>
        @endcan
        @role('super_admin')
        <a href="{{ route('sessions.index') }}" class="nav-link {{ request()->routeIs('sessions.*') ? 'active' : '' }}">
            <i class="bi bi-display"></i> Logged-in Users</a>
        @endrole
        @can('backup.manage')
        <a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
            <i class="bi bi-hdd"></i> Backup & Restore</a>
        @endcan
    @endcanany
</nav>
