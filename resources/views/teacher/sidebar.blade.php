<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.courses.*') ? 'active' : '' }}" href="{{ route('teacher.courses.index') }}">
            <i class="fas fa-book me-2"></i>My Courses
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.courses.create') ? 'active' : '' }}" href="{{ route('teacher.courses.create') }}">
            <i class="fas fa-plus me-2"></i>Create Course
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.grading.*') ? 'active' : '' }}" href="{{ route('teacher.grading.index') }}">
            <i class="fas fa-clipboard-check me-2"></i>Grading Queue
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.progress.*') ? 'active' : '' }}" href="{{ route('teacher.progress.index') }}">
            <i class="fas fa-users me-2"></i>Student Progress
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.contact-messages.*') ? 'active' : '' }}" href="{{ route('teacher.contact-messages.index') }}">
            <i class="fas fa-envelope me-2"></i>Mensajes de Contacto
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.logs.*') ? 'active' : '' }}" href="{{ route('teacher.logs.index') }}">
            <i class="fas fa-list-alt me-2"></i>Student Activity Logs
        </a>
    </li>
</ul>
