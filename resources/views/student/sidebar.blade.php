<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.courses.enrolled') ? 'active' : '' }}" href="{{ route('student.courses.enrolled') }}">
            <i class="fas fa-book me-2"></i>My Courses
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.courses.index') ? 'active' : '' }}" href="{{ route('student.courses.index') }}">
            <i class="fas fa-search me-2"></i>Browse Courses
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.assignments.*') ? 'active' : '' }}" href="{{ route('student.assignments.overview') }}">
            <i class="fas fa-clipboard-list me-2"></i>Assignments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.grades.*') ? 'active' : '' }}" href="{{ route('student.grades.index') }}">
            <i class="fas fa-chart-line me-2"></i>Grades
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.logs.*') ? 'active' : '' }}" href="{{ route('student.logs.index') }}">
            <i class="fas fa-history me-2"></i>My Activity History
        </a>
    </li>
</ul>
