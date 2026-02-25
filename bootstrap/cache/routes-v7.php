<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/api/user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::SlhRrCyG0jFgPvmi',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ICxIpZnLja9gqI7h',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Yc7Z2gT7JX3gJaBA',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'login.post',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/contact/submit' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'contact.submit',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/files/upload' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'files.upload',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/storage/stats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'files.storage-stats',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/secure-pdf/test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.test',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/courses/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/trashed' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.trashed',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/empty-trash' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.empty-trash',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/logs/health' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logs.health',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/activity-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.activity-logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/reports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/clear-cache' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.clear-cache',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/toggle-maintenance' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.toggle-maintenance',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/optimize' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.optimize',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/backup' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.backup',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/system-info' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.system-info',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/announcements' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/announcements/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/contact-messages' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contact-messages.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/file-repair' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/file-repair/batch' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.batch',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/file-repair/missing-files-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.missing-files-report',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/file-repair/schedule' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.schedule',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pdf-access-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pdf-access-logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/courses/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/grading' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.grading.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/bulk/download-submissions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.bulk.download-submissions',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/bulk/export-csv' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.bulk.export-csv',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/bulk/bulk-grade' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.bulk.bulk-grade',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/progress' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.progress.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/progress/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.progress.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/templates' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.templates.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.templates.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/templates/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.templates.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/contact-messages' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.contact-messages.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/teacher/activity-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/courses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/courses/enrolled' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.enrolled',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/activity-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/assignments/files/download' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'assignments.files.download',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/assignments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.assignments.overview',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/grades' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.grades.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/grades/statistics' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.grades.statistics',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/transcript' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.grades.transcript',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/student/analytics/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.analytics.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/messages' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'messages.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'messages.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/messages/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'messages.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/preferences' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.preferences',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.preferences.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/preferences/reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.preferences.reset',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/mark-read' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.mark-read',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/mark-all-read' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.mark-all-read',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/unread-count' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.unread-count',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/a(?|pi/(?|courses/([^/]++)/(?|hierarchy(*:47)|modules(?|(*:64)|/(?|([^/]++)(?|(*:86))|reorder(*:101)|([^/]++)/subpages(?|(*:129)|/(?|([^/]++)(?|(*:152))|reorder(*:168)))))|a(?|ssign\\-students(*:199)|vailable\\-students(*:225))|students/([^/]++)(?|(*:254)|/status(*:269))|enrolled\\-students(*:296))|students/([^/]++)/courses(*:330)|v1/courses/([^/]++)/modules/([^/]++)/(?|subpages(?|(*:389)|/([^/]++)/(?|content(?|(*:420)|\\-blocks(?|(*:439)|/(?|reorder(?|(*:461)|\\-sections(*:479))|update\\-layout(*:502)|paste(*:515)|empty\\-trash(*:535)|([^/]++)(?|(*:554)|/(?|duplicate(*:575)|move\\-section(*:596)|v(?|isibility(*:617)|ersions(*:632))|restore(?|(*:651)|\\-(?|layout(*:670)|from\\-trash(*:689)))|layout\\-history(*:714)|force\\-delete(*:735))))))|exercises/([^/]++)(*:766)))|content\\-types(*:790)))|dmin/(?|co(?|urses/(?|([^/]++)(?|(*:833)|/edit(*:846)|(*:854))|bulk\\-action(*:875)|([^/]++)/(?|modules/([^/]++)/(?|publish(*:922)|unpublish(*:939)|subpages(?|(*:958)|/(?|create(*:976)|([^/]++)(?|(*:995)|/(?|edit(*:1011)|content\\-builder(*:1036))|(*:1046))|reorder(*:1063)|([^/]++)/(?|toggle\\-active(*:1098)|restore(*:1114)|content(?|/(?|create(*:1143)|([^/]++)(?|/(?|edit(*:1171)|download(*:1188))|(*:1198))|reorder(*:1215)|([^/]++)/restore(*:1240))|(*:1250))|exercises(?|(*:1272)|/(?|create(*:1291)|([^/]++)(?|(*:1311)|/edit(*:1325)|(*:1334))|reorder(*:1351)|([^/]++)/(?|toggle\\-active(*:1386)|restore(*:1402)|submissions(?|(*:1425)|/(?|([^/]++)(?|(*:1449)|/grade(*:1464))|bulk\\-grade(*:1485)|([^/]++)/download(*:1511)))|export(*:1528)))|(*:1539))|assignments(?|/(?|create(*:1573)|([^/]++)(?|(*:1593)|/(?|edit(*:1610)|publish(*:1626)|unpublish(*:1644)|send\\-reminders(*:1668)|cancel\\-schedule(*:1693))|(*:1703))|reorder(*:1720)|([^/]++)/(?|restore(*:1748)|submissions(?|(*:1771)|/([^/]++)(?|(*:1792)|/(?|grade(?|(*:1813))|publish\\-grade(*:1837)|download/([^/]++)(*:1863))))))|(*:1877))))|(*:1889)))|gradebook(?|(*:1912)|/export(*:1928))))|ntact\\-messages/([^/]++)(?|(*:1967)|/reply(*:1982)))|users/([^/]++)(?|/(?|res(?|tore(*:2024)|et\\-password(*:2045))|force\\-delete(*:2068)|edit(*:2081)|toggle\\-status(*:2104))|(*:2114))|logs/([^/]++)/(?|download(*:2149)|clear(*:2163))|reports/([^/]++)/download(*:2198)|settings/backup/([^/]++)(*:2231)|announcements/([^/]++)(?|(*:2265)|/edit(*:2279)|(*:2288))|file\\-repair/(?|diagnose/([^/]++)(*:2331)|repair/([^/]++)(*:2355)|status/([^/]++)(*:2379))|grades/([^/]++)/override(?|(*:2416)|\\-history(*:2434))))|/files/(?|download/([^/]++)(*:2473)|([^/]++)(?|(*:2493)|/version(?|(*:2513)|s(*:2523))))|/s(?|ecure(?|\\-(?|files/(?|download/([a-zA-Z0-9]{64})(*:2588)|content/([^/]++)(?|(*:2616)|/generate\\-url(*:2639)))|pdf/(?|viewer/([^/]++)/([^/]++)(*:2681)|log\\-(?|page\\-view/([^/]++)(*:2717)|error/([^/]++)(*:2740)|devtools\\-detection/([^/]++)(*:2777))|st(?|ats/([^/]++)(*:2804)|ream/([^/]++)(*:2826))))|/(?|video/([^/]++)(*:2856)|audio/([^/]++)(*:2879)|image/([^/]++)(*:2902)))|t(?|udent/(?|courses/([^/]++)(?|(*:2945)|/(?|modules(?|(*:2968)|/([^/]++)(?|(*:2989)|/subpages(?|(*:3010)|/([^/]++)(?|(*:3031)|/(?|content/([^/]++)/(?|download(*:3072)|url(*:3084))|exercises(?|(*:3106)|/([^/]++)(?|(*:3127)|/(?|submit(?|(*:3149))|download\\-submission(*:3179))))|assignments(?|(*:3205)|/([^/]++)(?|(*:3226)|/submi(?|t(?|(*:3248))|ssions/([^/]++)(?|/(?|edit(*:3284)|download/([^/]++)(*:3310))|(*:3320))))))))))|enroll(*:3344)|unenroll(*:3361)|progress(*:3378)|grades(*:3393)))|assignments/([^/]++)/grade(*:3430))|orage/(.*)(?|(*:3453)))|ubmission\\-files/([^/]++)/download(*:3498))|/teacher/(?|co(?|urses/([^/]++)(?|(*:3542)|/(?|edit(*:3559)|publish(*:3575)|unpublish(*:3593)|modules(?|/(?|reorder(*:3623)|empty\\-trash(*:3644)|create(*:3659)|([^/]++)(?|(*:3679)|/(?|edit(*:3696)|content(*:3712)|publish(*:3728)|unpublish(*:3746)|restore(*:3762)|force\\-delete(*:3784)|subpages(?|(*:3804)|/(?|create(*:3823)|([^/]++)(?|(*:3843)|/(?|edit(*:3860)|content\\-builder(*:3885))|(*:3895))|reorder(*:3912)|([^/]++)/(?|toggle\\-active(*:3947)|restore(*:3963)|content(?|/(?|create(*:3992)|([^/]++)(?|/(?|edit(*:4020)|download(*:4037))|(*:4047))|reorder(*:4064)|([^/]++)/restore(*:4089))|(*:4099))|exercises(?|(*:4121)|/(?|create(*:4140)|([^/]++)(?|(*:4160)|/edit(*:4174)|(*:4183))|reorder(*:4200)|([^/]++)/(?|toggle\\-active(*:4235)|restore(*:4251)|submissions(?|(*:4274)|/(?|([^/]++)(?|(*:4298)|/grade(*:4313))|bulk\\-grade(*:4334)|([^/]++)/download(*:4360)))|export(*:4377)))|(*:4388))|assignments(?|/(?|create(*:4422)|([^/]++)(?|(*:4442)|/(?|edit(*:4459)|publish(*:4475)|unpublish(*:4493)|send\\-reminders(*:4517)|cancel\\-schedule(*:4542))|(*:4552))|reorder(*:4569)|([^/]++)/(?|restore(*:4597)|submissions(?|(*:4620)|/([^/]++)(?|(*:4641)|/(?|grade(?|(*:4662))|publish\\-grade(*:4686)|download/([^/]++)(*:4712))))))|(*:4726))))|(*:4738)))|(*:4749)))|(*:4760))|grad(?|ebook(?|(*:4785)|/export(*:4801))|ing\\-configuration(?|(*:4832)))|analytics(?|(*:4855)|/export(*:4871)))|(*:4882))|ntact\\-messages/([^/]++)(?|(*:4919)|/reply(*:4934)))|grad(?|ing/(?|([^/]++)(?|(*:4970)|/grade(*:4985))|bulk(*:4999))|es/([^/]++)/(?|lock(*:5028)|unlock(*:5043)|override\\-history(*:5069)))|bulk/(?|send\\-reminders/([^/]++)(*:5112)|publish\\-grades/([^/]++)(*:5145))|progress/student/([^/]++)(*:5180)|templates/(?|([^/]++)/apply/([^/]++)/([^/]++)/([^/]++)(*:5243)|duplicate/([^/]++)(*:5270)|([^/]++)(*:5287))|assignments/([^/]++)/(?|rubrics(?|/(?|create(*:5341)|([^/]++)(?|/edit(*:5366)|(*:5375)))|(*:5386))|s(?|ubmissions/([^/]++)/(?|grade(?|(*:5431))|save\\-draft(*:5452))|tats(*:5466))|publish\\-grades(*:5491)|analytics(*:5509)))|/me(?|dia/content/([^/]++)(*:5546)|ssages/([^/]++)(?|(*:5573)|/reply(?|(*:5591))))|/courses/([^/]++)/(?|forums(?|/(?|create(*:5643)|([^/]++)(?|(*:5663)|/(?|edit(*:5680)|topics(?|/(?|create(*:5708)|([^/]++)(?|(*:5728)|/(?|replies(*:5748)|posts/([^/]++)(?|/edit(*:5779)|(*:5788)))))|(*:5801)))|(*:5812)))|(*:5823))|announcements(?|(*:5849)|/(?|create(*:5868)|([^/]++)(?|(*:5888)|/edit(*:5902)|(*:5911)))|(*:5922))))/?$}sDu',
    ),
    3 => 
    array (
      47 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tnsdG25mQYGKvpr5',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      64 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::WnwEaX65GD2bmUII',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::nGRyVPRvJRpBdlp1',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      86 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::CHomDsTIDiCoZ1Jl',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3AAn77ABwER9tdwJ',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      101 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::6ngfC1vla6X6GSKb',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      129 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Awnd1yvrE21CsNCY',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      152 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::rGtq0w7IRtjO7P2H',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::4PBAoRwwJzPnvivT',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      168 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::v3DzaajAM2k1TMKR',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      199 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Bw1nTtMcflcwvpDg',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      225 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mzmhzSBO2IOxPJZ2',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      254 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::uSnQ75llyoe1Mpf8',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'student',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      269 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XgJueY4tQl6u6SBL',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'student',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      296 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::YrCTYqyO2HTjhT1V',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      330 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::1zcz9GHXWCMalZB0',
          ),
          1 => 
          array (
            0 => 'student',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      389 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.subpages.list',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      420 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.subpages.content.list',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      439 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      461 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      479 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.reorder-sections',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      502 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.update-layout',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      515 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.paste',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      535 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.empty-trash',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      554 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      575 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.duplicate',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      596 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.move-section',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      617 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.update-visibility',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      632 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.version-history',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      651 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.restore-version',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      670 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.restore-layout',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      689 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.restore-from-trash',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      714 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.layout-history',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentBlock',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      735 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-blocks.force-delete',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentId',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      766 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.exercises.api.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      790 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'api.content-types',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      833 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      846 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.edit',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      854 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.update',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.destroy',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      875 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.bulk-action',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      922 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.publish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      939 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.modules.unpublish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      958 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      976 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      995 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1011 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1036 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content-builder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1046 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1063 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1098 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.toggle-active',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1114 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1143 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1171 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1188 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1198 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1215 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1240 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1250 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.content.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1272 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1291 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1311 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1325 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1334 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1351 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1386 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.toggle-active',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1402 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exerciseId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1425 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.submissions.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1449 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.submissions.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1464 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.submissions.grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
            4 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1485 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.submissions.bulk-grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1511 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.submissions.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1528 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.export',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1539 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.exercises.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1573 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1593 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1610 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1626 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.publish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1644 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.unpublish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1668 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.send-reminders',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1693 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.cancel-schedule',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1703 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1720 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1748 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignmentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1771 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.submissions.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1792 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.submissions.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1813 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.submissions.grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.submissions.store-grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1837 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.submissions.publish-grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1863 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.submissions.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
            5 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1877 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.assignments.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1889 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.courses.modules.subpages.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1912 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.gradebook.index',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1928 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.gradebook.export',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1967 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contact-messages.show',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1982 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contact-messages.reply',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2024 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.restore',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2045 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.reset-password',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2068 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.force-delete',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2081 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2104 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.toggle-status',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2114 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2149 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logs.download',
          ),
          1 => 
          array (
            0 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2163 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logs.clear',
          ),
          1 => 
          array (
            0 => 'filename',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2198 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reports.download',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2231 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.download-backup',
          ),
          1 => 
          array (
            0 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2265 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.show',
          ),
          1 => 
          array (
            0 => 'announcement',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2279 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.edit',
          ),
          1 => 
          array (
            0 => 'announcement',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2288 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.update',
          ),
          1 => 
          array (
            0 => 'announcement',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.announcements.destroy',
          ),
          1 => 
          array (
            0 => 'announcement',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2331 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.diagnose',
          ),
          1 => 
          array (
            0 => 'contentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2355 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.repair',
          ),
          1 => 
          array (
            0 => 'contentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2379 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.file-repair.status',
          ),
          1 => 
          array (
            0 => 'correlationId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2416 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.gradebook.show-override',
          ),
          1 => 
          array (
            0 => 'grade',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.gradebook.override',
          ),
          1 => 
          array (
            0 => 'grade',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2434 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.gradebook.override-history',
          ),
          1 => 
          array (
            0 => 'grade',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2473 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'files.download',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2493 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'files.show',
          ),
          1 => 
          array (
            0 => 'fileUpload',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'files.destroy',
          ),
          1 => 
          array (
            0 => 'fileUpload',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2513 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'files.create-version',
          ),
          1 => 
          array (
            0 => 'fileUpload',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2523 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'files.versions',
          ),
          1 => 
          array (
            0 => 'fileUpload',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2588 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure-files.download-token',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2616 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure-files.download-content',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2639 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure-files.generate-url',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2681 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.viewer',
          ),
          1 => 
          array (
            0 => 'content',
            1 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2717 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.log-page-view',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2740 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.log-error',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2777 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.log-devtools-detection',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2804 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.stats',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2826 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.pdf.stream',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2856 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.video.stream',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2879 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.audio.stream',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2902 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'secure.image.serve',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2945 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2968 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2989 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.module',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3010 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3031 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3072 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.content.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3084 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.content.url',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3106 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.exercises.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3127 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.exercises.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3149 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.exercises.submit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.exercises.store-submission',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3179 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.exercises.download-submission',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3205 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3226 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3248 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.submit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.store-submission',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3284 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.submissions.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3310 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.submissions.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
            5 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3320 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.modules.subpages.assignments.submissions.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3344 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.enroll',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3361 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.unenroll',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3378 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.courses.progress',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3393 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.grades.course',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3430 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'student.grades.assignment',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3453 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local.upload',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3498 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'submission.file.download',
          ),
          1 => 
          array (
            0 => 'submissionFile',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3542 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.show',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3559 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.edit',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3575 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.publish',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3593 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.unpublish',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3623 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.reorder',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3644 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.empty-trash',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3659 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.create',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3679 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3696 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3712 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.add-content',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3728 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.publish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3746 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.unpublish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3762 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'moduleId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3784 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.force-delete',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'moduleId',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3804 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3823 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3843 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3860 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3885 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content-builder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3895 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3912 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3947 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.toggle-active',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3963 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3992 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4020 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4037 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4047 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'content',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4064 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4089 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'contentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4099 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.content.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4121 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4140 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4160 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4174 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4183 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4200 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4235 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.toggle-active',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4251 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exerciseId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4274 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.submissions.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4298 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.submissions.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4313 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.submissions.grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
            4 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4334 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.submissions.bulk-grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4360 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.submissions.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4377 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.export',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'exercise',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4388 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.exercises.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4422 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4442 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4459 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4475 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.publish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4493 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.unpublish',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4517 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.send-reminders',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4542 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.cancel-schedule',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4552 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4569 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.reorder',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4597 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.restore',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignmentId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4620 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.submissions.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4641 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.submissions.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4662 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.submissions.grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.submissions.store-grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4686 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.submissions.publish-grade',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4712 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.submissions.download',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
            3 => 'assignment',
            4 => 'submission',
            5 => 'filename',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4726 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.assignments.index',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
            2 => 'subpage',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4738 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.modules.subpages.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4749 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'module',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4760 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'modules.index',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.modules.store',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4785 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.index',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4801 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.export',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4832 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.configuration',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.update-configuration',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4855 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.analytics.dashboard',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4871 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.analytics.export',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4882 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.update',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.courses.destroy',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4919 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.contact-messages.show',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4934 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.contact-messages.reply',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4970 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.grading.show',
          ),
          1 => 
          array (
            0 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4985 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.grading.grade',
          ),
          1 => 
          array (
            0 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4999 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.grading.bulk',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5028 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.lock',
          ),
          1 => 
          array (
            0 => 'grade',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5043 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.unlock',
          ),
          1 => 
          array (
            0 => 'grade',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5069 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.override-history',
          ),
          1 => 
          array (
            0 => 'grade',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5112 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.bulk.send-reminders',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5145 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.bulk.publish-grades',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5180 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.progress.show',
          ),
          1 => 
          array (
            0 => 'student',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5243 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.templates.apply',
          ),
          1 => 
          array (
            0 => 'template',
            1 => 'course',
            2 => 'module',
            3 => 'subpage',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5270 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.templates.duplicate',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5287 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.templates.destroy',
          ),
          1 => 
          array (
            0 => 'template',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5341 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.rubrics.create',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5366 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.rubrics.edit',
          ),
          1 => 
          array (
            0 => 'assignment',
            1 => 'rubric',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5375 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.rubrics.update',
          ),
          1 => 
          array (
            0 => 'assignment',
            1 => 'rubric',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.rubrics.destroy',
          ),
          1 => 
          array (
            0 => 'assignment',
            1 => 'rubric',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5386 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.rubrics.store',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5431 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.grade',
          ),
          1 => 
          array (
            0 => 'assignment',
            1 => 'submission',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.store-grade',
          ),
          1 => 
          array (
            0 => 'assignment',
            1 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5452 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.save-draft',
          ),
          1 => 
          array (
            0 => 'assignment',
            1 => 'submission',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5466 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.assignment-stats',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5491 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.gradebook.publish-grades',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5509 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'teacher.analytics.assignment',
          ),
          1 => 
          array (
            0 => 'assignment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5546 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'media.content',
          ),
          1 => 
          array (
            0 => 'content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5573 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'messages.show',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'messages.destroy',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5591 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'messages.reply',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'messages.reply.store',
          ),
          1 => 
          array (
            0 => 'message',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5643 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.create',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5663 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5680 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5708 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.topics.create',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5728 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.topics.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
            2 => 'topic',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5748 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.topics.replies.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
            2 => 'topic',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5779 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.posts.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
            2 => 'topic',
            3 => 'post',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5788 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.posts.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
            2 => 'topic',
            3 => 'post',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.posts.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
            2 => 'topic',
            3 => 'post',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5801 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.topics.store',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5812 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'forum',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5823 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.store',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.forums.index',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5849 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.index',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5868 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.create',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5888 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.show',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'announcement',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5902 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.edit',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'announcement',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5911 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.update',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'announcement',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.destroy',
          ),
          1 => 
          array (
            0 => 'course',
            1 => 'announcement',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5922 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'courses.announcements.store',
          ),
          1 => 
          array (
            0 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'generated::SlhRrCyG0jFgPvmi' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:77:"function (\\Illuminate\\Http\\Request $request) {
    return $request->user();
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000082b0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::SlhRrCyG0jFgPvmi',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tnsdG25mQYGKvpr5' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/courses/{course}/hierarchy',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@getCourseHierarchy',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@getCourseHierarchy',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::tnsdG25mQYGKvpr5',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::WnwEaX65GD2bmUII' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@getCourseHierarchy',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@getCourseHierarchy',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::WnwEaX65GD2bmUII',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::nGRyVPRvJRpBdlp1' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@createModule',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@createModule',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::nGRyVPRvJRpBdlp1',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::CHomDsTIDiCoZ1Jl' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@updateModule',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@updateModule',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::CHomDsTIDiCoZ1Jl',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3AAn77ABwER9tdwJ' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@deleteModule',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@deleteModule',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::3AAn77ABwER9tdwJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::6ngfC1vla6X6GSKb' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/courses/{course}/modules/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@reorderModules',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@reorderModules',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::6ngfC1vla6X6GSKb',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Awnd1yvrE21CsNCY' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@createSubpage',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@createSubpage',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::Awnd1yvrE21CsNCY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::rGtq0w7IRtjO7P2H' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@updateSubpage',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@updateSubpage',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::rGtq0w7IRtjO7P2H',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::4PBAoRwwJzPnvivT' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@deleteSubpage',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@deleteSubpage',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::4PBAoRwwJzPnvivT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::v3DzaajAM2k1TMKR' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/courses/{course}/modules/{module}/subpages/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@reorderSubpages',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseHierarchyController@reorderSubpages',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::v3DzaajAM2k1TMKR',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Bw1nTtMcflcwvpDg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/courses/{course}/assign-students',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@assignStudents',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@assignStudents',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::Bw1nTtMcflcwvpDg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::uSnQ75llyoe1Mpf8' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/courses/{course}/students/{student}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@removeStudent',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@removeStudent',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::uSnQ75llyoe1Mpf8',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::YrCTYqyO2HTjhT1V' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/courses/{course}/enrolled-students',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getEnrolledStudents',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getEnrolledStudents',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::YrCTYqyO2HTjhT1V',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mzmhzSBO2IOxPJZ2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/courses/{course}/available-students',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getAvailableStudents',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getAvailableStudents',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::mzmhzSBO2IOxPJZ2',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::1zcz9GHXWCMalZB0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/students/{student}/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getStudentCourses',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getStudentCourses',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::1zcz9GHXWCMalZB0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XgJueY4tQl6u6SBL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/courses/{course}/students/{student}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getEnrollmentStatus',
        'controller' => 'App\\Http\\Controllers\\Api\\CourseAssignmentController@getEnrollmentStatus',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::XgJueY4tQl6u6SBL',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ICxIpZnLja9gqI7h' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:806:"function () {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'/var/www/lms/vendor/laravel/framework/src/Illuminate/Foundation/Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"00000000000008320000000000000000";}}',
        'as' => 'generated::ICxIpZnLja9gqI7h',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Yc7Z2gT7JX3gJaBA' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@index',
        'controller' => 'App\\Http\\Controllers\\HomeController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::Yc7Z2gT7JX3gJaBA',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\HomeController@dashboard',
        'controller' => 'App\\Http\\Controllers\\HomeController@dashboard',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@showLoginForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@showLoginForm',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login.post' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@login',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login.post',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\LoginController@logout',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'contact.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'contact/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@store',
        'controller' => 'App\\Http\\Controllers\\ContactController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'contact.submit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.upload' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'files/upload',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@upload',
        'controller' => 'App\\Http\\Controllers\\FileController@upload',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.upload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'files/download/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@download',
        'controller' => 'App\\Http\\Controllers\\FileController@download',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'files/{fileUpload}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@show',
        'controller' => 'App\\Http\\Controllers\\FileController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'files/{fileUpload}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@destroy',
        'controller' => 'App\\Http\\Controllers\\FileController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.create-version' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'files/{fileUpload}/version',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@createVersion',
        'controller' => 'App\\Http\\Controllers\\FileController@createVersion',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.create-version',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.versions' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'files/{fileUpload}/versions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@versions',
        'controller' => 'App\\Http\\Controllers\\FileController@versions',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.versions',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'files.storage-stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'file.access',
        ),
        'uses' => 'App\\Http\\Controllers\\FileController@storageStats',
        'controller' => 'App\\Http\\Controllers\\FileController@storageStats',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'files.storage-stats',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure-files.download-token' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure-files/download/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecureFileController@downloadByToken',
        'controller' => 'App\\Http\\Controllers\\SecureFileController@downloadByToken',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure-files.download-token',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'token' => '[a-zA-Z0-9]{64}',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure-files.download-content' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure-files/content/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecureFileController@downloadContentFile',
        'controller' => 'App\\Http\\Controllers\\SecureFileController@downloadContentFile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure-files.download-content',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure-files.generate-url' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'secure-files/content/{content}/generate-url',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecureFileController@generateSecureUrl',
        'controller' => 'App\\Http\\Controllers\\SecureFileController@generateSecureUrl',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure-files.generate-url',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.video.stream' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure/video/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\SecureVideoController@stream',
        'controller' => 'App\\Http\\Controllers\\SecureVideoController@stream',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure.video.stream',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.viewer' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure-pdf/viewer/{content}/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@viewer',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@viewer',
        'namespace' => NULL,
        'prefix' => '/secure-pdf',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.viewer',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.log-page-view' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'secure-pdf/log-page-view/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@logPageView',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@logPageView',
        'namespace' => NULL,
        'prefix' => '/secure-pdf',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.log-page-view',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure-pdf/stats/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@getStats',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@getStats',
        'namespace' => NULL,
        'prefix' => '/secure-pdf',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.stats',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.test' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure-pdf/test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@test',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@test',
        'namespace' => NULL,
        'prefix' => '/secure-pdf',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.test',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.audio.stream' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure/audio/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\SecureMediaController@streamAudio',
        'controller' => 'App\\Http\\Controllers\\SecureMediaController@streamAudio',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure.audio.stream',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.image.serve' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure/image/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\SecureMediaController@serveImage',
        'controller' => 'App\\Http\\Controllers\\SecureMediaController@serveImage',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure.image.serve',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.stream' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'secure-pdf/stream/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@stream',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@stream',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.stream',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.log-error' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'secure-pdf/log-error/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@logError',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@logError',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.log-error',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'secure.pdf.log-devtools-detection' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'secure-pdf/log-devtools-detection/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SecurePdfController@logDevToolsDetection',
        'controller' => 'App\\Http\\Controllers\\SecurePdfController@logDevToolsDetection',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'secure.pdf.log-devtools-detection',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.bulk-action' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/bulk-action',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\CourseController@bulkAction',
        'controller' => 'App\\Http\\Controllers\\Admin\\CourseController@bulkAction',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.courses.bulk-action',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.publish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/publish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@publish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@publish',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'admin.modules.publish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.modules.unpublish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/unpublish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@unpublish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@unpublish',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'admin.modules.unpublish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.trashed' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/trashed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@trashed',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@trashed',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.users.trashed',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users/{user}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@restore',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@restore',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.users.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => true,
    ),
    'admin.users.force-delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{user}/force-delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@forceDelete',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@forceDelete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.users.force-delete',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => true,
    ),
    'admin.users.empty-trash' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/empty-trash',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@emptyTrash',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@emptyTrash',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.users.empty-trash',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.users.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.reset-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users/{user}/reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@resetPassword',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@resetPassword',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.users.reset-password',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users/{user}/toggle-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserController@toggleStatus',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.users.toggle-status',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemLogController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemLogController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logs.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/logs/{filename}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemLogController@download',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemLogController@download',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.logs.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logs.clear' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/logs/{filename}/clear',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemLogController@clear',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemLogController@clear',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.logs.clear',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logs.health' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/logs/health',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemLogController@health',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemLogController@health',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.logs.health',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.activity-logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/activity-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.activity-logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ReportController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ReportController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.reports.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reports.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/reports/{type}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ReportController@download',
        'controller' => 'App\\Http\\Controllers\\Admin\\ReportController@download',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.reports.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.clear-cache' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/clear-cache',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@clearCache',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@clearCache',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.clear-cache',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.toggle-maintenance' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/toggle-maintenance',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@toggleMaintenance',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@toggleMaintenance',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.toggle-maintenance',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.optimize' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/optimize',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@optimize',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@optimize',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.optimize',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.backup' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/backup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@backup',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@backup',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.backup',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.download-backup' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings/backup/{filename}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@downloadBackup',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@downloadBackup',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.download-backup',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.system-info' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings/system-info',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@systemInfo',
        'controller' => 'App\\Http\\Controllers\\Admin\\SystemSettingsController@systemInfo',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.system-info',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/announcements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/announcements/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/announcements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/announcements/{announcement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/announcements/{announcement}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/announcements/{announcement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.announcements.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/announcements/{announcement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.announcements.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\AnnouncementController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contact-messages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contact-messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@index',
        'controller' => 'App\\Http\\Controllers\\ContactController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.contact-messages.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contact-messages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contact-messages/{message}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@show',
        'controller' => 'App\\Http\\Controllers\\ContactController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.contact-messages.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contact-messages.reply' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/contact-messages/{message}/reply',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@reply',
        'controller' => 'App\\Http\\Controllers\\ContactController@reply',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.contact-messages.reply',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/file-repair',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@index',
        'as' => 'admin.file-repair.index',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.diagnose' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/file-repair/diagnose/{contentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@diagnose',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@diagnose',
        'as' => 'admin.file-repair.diagnose',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.repair' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/file-repair/repair/{contentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@repair',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@repair',
        'as' => 'admin.file-repair.repair',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.batch' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/file-repair/batch',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@batchRepair',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@batchRepair',
        'as' => 'admin.file-repair.batch',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.missing-files-report' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/file-repair/missing-files-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@missingFilesReport',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@missingFilesReport',
        'as' => 'admin.file-repair.missing-files-report',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/file-repair/status/{correlationId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@status',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@status',
        'as' => 'admin.file-repair.status',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.file-repair.schedule' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/file-repair/schedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FileRepairController@scheduleRepair',
        'controller' => 'App\\Http\\Controllers\\Admin\\FileRepairController@scheduleRepair',
        'as' => 'admin.file-repair.schedule',
        'namespace' => NULL,
        'prefix' => 'admin/file-repair',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pdf-access-logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pdf-access-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\PdfAccessLogController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\PdfAccessLogController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.pdf-access-logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\DashboardController@index',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.index',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@index',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@create',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@store',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@show',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@edit',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@update',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'as' => 'teacher.courses.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@destroy',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.publish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/publish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@publish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@publish',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.courses.publish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.unpublish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/unpublish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\CourseController@unpublish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\CourseController@unpublish',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.courses.unpublish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.grading.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/grading',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@index',
        'as' => 'teacher.grading.index',
        'namespace' => NULL,
        'prefix' => 'teacher/grading',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.grading.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/grading/{submission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@show',
        'as' => 'teacher.grading.show',
        'namespace' => NULL,
        'prefix' => 'teacher/grading',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.grading.grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/grading/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@grade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@grade',
        'as' => 'teacher.grading.grade',
        'namespace' => NULL,
        'prefix' => 'teacher/grading',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.grading.bulk' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/grading/bulk',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@bulkGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradingQueueController@bulkGrade',
        'as' => 'teacher.grading.bulk',
        'namespace' => NULL,
        'prefix' => 'teacher/grading',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.bulk.download-submissions' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/bulk/download-submissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@downloadSubmissions',
        'controller' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@downloadSubmissions',
        'as' => 'teacher.bulk.download-submissions',
        'namespace' => NULL,
        'prefix' => 'teacher/bulk',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.bulk.export-csv' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/bulk/export-csv',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@exportToCSV',
        'controller' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@exportToCSV',
        'as' => 'teacher.bulk.export-csv',
        'namespace' => NULL,
        'prefix' => 'teacher/bulk',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.bulk.send-reminders' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/bulk/send-reminders/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@sendReminders',
        'controller' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@sendReminders',
        'as' => 'teacher.bulk.send-reminders',
        'namespace' => NULL,
        'prefix' => 'teacher/bulk',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.bulk.bulk-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/bulk/bulk-grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@bulkGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@bulkGrade',
        'as' => 'teacher.bulk.bulk-grade',
        'namespace' => NULL,
        'prefix' => 'teacher/bulk',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.bulk.publish-grades' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/bulk/publish-grades/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@publishAllGrades',
        'controller' => 'App\\Http\\Controllers\\Teacher\\BulkOperationsController@publishAllGrades',
        'as' => 'teacher.bulk.publish-grades',
        'namespace' => NULL,
        'prefix' => 'teacher/bulk',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.progress.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/progress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\StudentProgressController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\StudentProgressController@index',
        'as' => 'teacher.progress.index',
        'namespace' => NULL,
        'prefix' => 'teacher/progress',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.progress.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/progress/student/{student}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\StudentProgressController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\StudentProgressController@show',
        'as' => 'teacher.progress.show',
        'namespace' => NULL,
        'prefix' => 'teacher/progress',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.progress.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/progress/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\StudentProgressController@export',
        'controller' => 'App\\Http\\Controllers\\Teacher\\StudentProgressController@export',
        'as' => 'teacher.progress.export',
        'namespace' => NULL,
        'prefix' => 'teacher/progress',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.templates.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/templates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@index',
        'as' => 'teacher.templates.index',
        'namespace' => NULL,
        'prefix' => 'teacher/templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.templates.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/templates/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@create',
        'as' => 'teacher.templates.create',
        'namespace' => NULL,
        'prefix' => 'teacher/templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.templates.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/templates',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@store',
        'as' => 'teacher.templates.store',
        'namespace' => NULL,
        'prefix' => 'teacher/templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.templates.apply' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/templates/{template}/apply/{course}/{module}/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@apply',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@apply',
        'as' => 'teacher.templates.apply',
        'namespace' => NULL,
        'prefix' => 'teacher/templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.templates.duplicate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/templates/duplicate/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@duplicate',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@duplicate',
        'as' => 'teacher.templates.duplicate',
        'namespace' => NULL,
        'prefix' => 'teacher/templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.templates.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/templates/{template}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentTemplateController@destroy',
        'as' => 'teacher.templates.destroy',
        'namespace' => NULL,
        'prefix' => 'teacher/templates',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.rubrics.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/assignments/{assignment}/rubrics/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\RubricController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\RubricController@create',
        'as' => 'teacher.rubrics.create',
        'namespace' => NULL,
        'prefix' => 'teacher/assignments/{assignment}/rubrics',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.rubrics.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/assignments/{assignment}/rubrics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\RubricController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\RubricController@store',
        'as' => 'teacher.rubrics.store',
        'namespace' => NULL,
        'prefix' => 'teacher/assignments/{assignment}/rubrics',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.rubrics.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/assignments/{assignment}/rubrics/{rubric}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\RubricController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\RubricController@edit',
        'as' => 'teacher.rubrics.edit',
        'namespace' => NULL,
        'prefix' => 'teacher/assignments/{assignment}/rubrics',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.rubrics.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'teacher/assignments/{assignment}/rubrics/{rubric}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\RubricController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\RubricController@update',
        'as' => 'teacher.rubrics.update',
        'namespace' => NULL,
        'prefix' => 'teacher/assignments/{assignment}/rubrics',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.rubrics.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/assignments/{assignment}/rubrics/{rubric}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\RubricController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\RubricController@destroy',
        'as' => 'teacher.rubrics.destroy',
        'namespace' => NULL,
        'prefix' => 'teacher/assignments/{assignment}/rubrics',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.contact-messages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/contact-messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@index',
        'controller' => 'App\\Http\\Controllers\\ContactController@index',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.contact-messages.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.contact-messages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/contact-messages/{message}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@show',
        'controller' => 'App\\Http\\Controllers\\ContactController@show',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.contact-messages.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.contact-messages.reply' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/contact-messages/{message}/reply',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\ContactController@reply',
        'controller' => 'App\\Http\\Controllers\\ContactController@reply',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.contact-messages.reply',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/activity-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ActivityLogController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ActivityLogController@index',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
        'as' => 'teacher.logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@reorder',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.reorder',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.empty-trash' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/empty-trash',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@emptyTrash',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@emptyTrash',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.empty-trash',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'modules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'modules.index',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@index',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.modules.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@create',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.modules.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@store',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.modules.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@show',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.modules.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@edit',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.modules.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@update',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.modules.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@destroy',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.add-content' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/content',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@addContent',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@addContent',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.add-content',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.publish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/publish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@publish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@publish',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.publish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.unpublish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/unpublish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@unpublish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@unpublish',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.unpublish',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{moduleId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@restore',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.modules.force-delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/{moduleId}/force-delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ModuleController@forceDelete',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ModuleController@forceDelete',
        'namespace' => NULL,
        'prefix' => '/teacher/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'teacher.modules.force-delete',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\DashboardController@index',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.dashboard',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@index',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.enrolled' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/enrolled',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@enrolled',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@enrolled',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.enrolled',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@show',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@modules',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@modules',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.modules',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.module' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@showModule',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@showModule',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.module',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.enroll' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'student/courses/{course}/enroll',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@enroll',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@enroll',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.enroll',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.unenroll' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'student/courses/{course}/unenroll',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@unenroll',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@unenroll',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.unenroll',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.progress' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/progress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\CourseController@progress',
        'controller' => 'App\\Http\\Controllers\\Student\\CourseController@progress',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.courses.progress',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/activity-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ActivityLogController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\ActivityLogController@index',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
        'as' => 'student.logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.index',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@index',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@create',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@store',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@show',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@edit',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@update',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@destroy',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content-builder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@contentBuilder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@contentBuilder',
        'as' => 'teacher.courses.modules.subpages.content-builder',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@reorder',
        'as' => 'teacher.courses.modules.subpages.reorder',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@toggleActive',
        'as' => 'teacher.courses.modules.subpages.toggle-active',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@restore',
        'as' => 'teacher.courses.modules.subpages.restore',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.content.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@create',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.content.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@store',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.content.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@edit',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.content.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@update',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.content.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@destroy',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@download',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@download',
        'as' => 'teacher.courses.modules.subpages.content.download',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@reorder',
        'as' => 'teacher.courses.modules.subpages.content.reorder',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.content.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/content/{contentId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@restore',
        'as' => 'teacher.courses.modules.subpages.content.restore',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.index',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@index',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@create',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@store',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@show',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@edit',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@update',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.exercises.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@destroy',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@reorder',
        'as' => 'teacher.courses.modules.subpages.exercises.reorder',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@toggleActive',
        'as' => 'teacher.courses.modules.subpages.exercises.toggle-active',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exerciseId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@restore',
        'as' => 'teacher.courses.modules.subpages.exercises.restore',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.submissions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'as' => 'teacher.courses.modules.subpages.exercises.submissions.index',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.submissions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/{submission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'as' => 'teacher.courses.modules.subpages.exercises.submissions.show',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.submissions.grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'as' => 'teacher.courses.modules.subpages.exercises.submissions.grade',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.submissions.bulk-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/bulk-grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@bulkGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@bulkGrade',
        'as' => 'teacher.courses.modules.subpages.exercises.submissions.bulk-grade',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.submissions.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/{submission}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'as' => 'teacher.courses.modules.subpages.exercises.submissions.download',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.exercises.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@export',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@export',
        'as' => 'teacher.courses.modules.subpages.exercises.export',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\SubpageController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\SubpageController@index',
        'as' => 'student.courses.modules.subpages.index',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\SubpageController@show',
        'controller' => 'App\\Http\\Controllers\\Student\\SubpageController@show',
        'as' => 'student.courses.modules.subpages.show',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.content.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\SubpageController@downloadContent',
        'controller' => 'App\\Http\\Controllers\\Student\\SubpageController@downloadContent',
        'as' => 'student.courses.modules.subpages.content.download',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.content.url' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}/url',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\SubpageController@getContentUrl',
        'controller' => 'App\\Http\\Controllers\\Student\\SubpageController@getContentUrl',
        'as' => 'student.courses.modules.subpages.content.url',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.exercises.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/exercises',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ExerciseController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\ExerciseController@index',
        'as' => 'student.courses.modules.subpages.exercises.index',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.exercises.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ExerciseController@show',
        'controller' => 'App\\Http\\Controllers\\Student\\ExerciseController@show',
        'as' => 'student.courses.modules.subpages.exercises.show',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.exercises.submit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ExerciseController@submit',
        'controller' => 'App\\Http\\Controllers\\Student\\ExerciseController@submit',
        'as' => 'student.courses.modules.subpages.exercises.submit',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.exercises.store-submission' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ExerciseController@storeSubmission',
        'controller' => 'App\\Http\\Controllers\\Student\\ExerciseController@storeSubmission',
        'as' => 'student.courses.modules.subpages.exercises.store-submission',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.exercises.download-submission' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/download-submission',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ExerciseController@downloadSubmission',
        'controller' => 'App\\Http\\Controllers\\Student\\ExerciseController@downloadSubmission',
        'as' => 'student.courses.modules.subpages.exercises.download-submission',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.index',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@index',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@create',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@store',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@show',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@edit',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@update',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@destroy',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content-builder' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content-builder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@contentBuilder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@contentBuilder',
        'as' => 'admin.courses.modules.subpages.content-builder',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@reorder',
        'as' => 'admin.courses.modules.subpages.reorder',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@toggleActive',
        'as' => 'admin.courses.modules.subpages.toggle-active',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubpageController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubpageController@restore',
        'as' => 'admin.courses.modules.subpages.restore',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.content.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@create',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.content.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@store',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.content.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@edit',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.content.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@update',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.content.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@destroy',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/{content}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@download',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@download',
        'as' => 'admin.courses.modules.subpages.content.download',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@reorder',
        'as' => 'admin.courses.modules.subpages.content.reorder',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.content.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/content/{contentId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ContentController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ContentController@restore',
        'as' => 'admin.courses.modules.subpages.content.restore',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.index',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@index',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@create',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@store',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@show',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@edit',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@update',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.exercises.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@destroy',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@reorder',
        'as' => 'admin.courses.modules.subpages.exercises.reorder',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.toggle-active' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@toggleActive',
        'as' => 'admin.courses.modules.subpages.exercises.toggle-active',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exerciseId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\ExerciseController@restore',
        'as' => 'admin.courses.modules.subpages.exercises.restore',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.submissions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'as' => 'admin.courses.modules.subpages.exercises.submissions.index',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.submissions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/{submission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'as' => 'admin.courses.modules.subpages.exercises.submissions.show',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.submissions.grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'as' => 'admin.courses.modules.subpages.exercises.submissions.grade',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.submissions.bulk-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/bulk-grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@bulkGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@bulkGrade',
        'as' => 'admin.courses.modules.subpages.exercises.submissions.bulk-grade',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.submissions.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/submissions/{submission}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'as' => 'admin.courses.modules.subpages.exercises.submissions.download',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.exercises.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@export',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@export',
        'as' => 'admin.courses.modules.subpages.exercises.export',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.subpages.list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:500:"function (\\Course $course, \\Module $module) {
            if (!\\auth()->user()->can(\'view\', $course)) {
                return \\response()->json([\'error\' => \'Unauthorized\'], 403);
            }
            
            $query = $module->subpages()->ordered();
            
            // Students only see active subpages
            if (\\auth()->user()->hasRole(\'Student\')) {
                $query->active();
            }
            
            return \\response()->json($query->get());
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000090c0000000000000000";}}',
        'as' => 'api.subpages.list',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.subpages.content.list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:554:"function (\\Course $course, \\Module $module, \\Subpage $subpage) {
            if (!$subpage->canBeAccessedBy(\\auth()->user())) {
                return \\response()->json([\'error\' => \'Unauthorized\'], 403);
            }
            
            $query = $subpage->contents()->ordered();
            
            // Students only see student-visible content
            if (\\auth()->user()->hasRole(\'Student\')) {
                $query->active()->visibleToStudents();
            }
            
            return \\response()->json($query->get());
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009200000000000000000";}}',
        'as' => 'api.subpages.content.list',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@index',
        'as' => 'api.content-blocks.index',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@store',
        'as' => 'api.content-blocks.store',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@reorder',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@reorder',
        'as' => 'api.content-blocks.reorder',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.update-layout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/update-layout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@updateLayout',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@updateLayout',
        'as' => 'api.content-blocks.update-layout',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.reorder-sections' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/reorder-sections',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@reorderSections',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@reorderSections',
        'as' => 'api.content-blocks.reorder-sections',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.paste' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/paste',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@paste',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@paste',
        'as' => 'api.content-blocks.paste',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.empty-trash' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/empty-trash',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@emptyTrash',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@emptyTrash',
        'as' => 'api.content-blocks.empty-trash',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@show',
        'as' => 'api.content-blocks.show',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@update',
        'as' => 'api.content-blocks.update',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@destroy',
        'as' => 'api.content-blocks.destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.duplicate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/duplicate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@duplicate',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@duplicate',
        'as' => 'api.content-blocks.duplicate',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.move-section' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/move-section',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@moveToSection',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@moveToSection',
        'as' => 'api.content-blocks.move-section',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.update-visibility' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/visibility',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@updateVisibility',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@updateVisibility',
        'as' => 'api.content-blocks.update-visibility',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.version-history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/versions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@versionHistory',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@versionHistory',
        'as' => 'api.content-blocks.version-history',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.restore-version' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@restoreVersion',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@restoreVersion',
        'as' => 'api.content-blocks.restore-version',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.layout-history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/layout-history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@layoutHistory',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@layoutHistory',
        'as' => 'api.content-blocks.layout-history',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.restore-layout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentBlock}/restore-layout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@restoreLayoutVersion',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@restoreLayoutVersion',
        'as' => 'api.content-blocks.restore-layout',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.restore-from-trash' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentId}/restore-from-trash',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@restore',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@restore',
        'as' => 'api.content-blocks.restore-from-trash',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-blocks.force-delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/content-blocks/{contentId}/force-delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@forceDelete',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@forceDelete',
        'as' => 'api.content-blocks.force-delete',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.content-types' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/content-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ContentBlockController@contentTypes',
        'controller' => 'App\\Http\\Controllers\\Api\\ContentBlockController@contentTypes',
        'as' => 'api.content-types',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'api.exercises.api.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/courses/{course}/modules/{module}/subpages/{subpage}/exercises/{exercise}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\ExerciseController@apiShow',
        'controller' => 'App\\Http\\Controllers\\Student\\ExerciseController@apiShow',
        'as' => 'api.exercises.api.show',
        'namespace' => NULL,
        'prefix' => 'api/v1/courses/{course}/modules/{module}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'media.content' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'media/content/{content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\MediaController@serveContentFile',
        'controller' => 'App\\Http\\Controllers\\MediaController@serveContentFile',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'media.content',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'submission.file.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'submission-files/{submissionFile}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\SubmissionFileController@download',
        'controller' => 'App\\Http\\Controllers\\SubmissionFileController@download',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'submission.file.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'assignments.files.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'assignments/files/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'signed',
        ),
        'uses' => 'App\\Http\\Controllers\\SubmissionFileController@downloadByPath',
        'controller' => 'App\\Http\\Controllers\\SubmissionFileController@downloadByPath',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'assignments.files.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.assignments.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@create',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.assignments.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@store',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.assignments.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@show',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.assignments.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@edit',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.assignments.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@update',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'as' => 'teacher.courses.modules.subpages.assignments.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@destroy',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@index',
        'as' => 'teacher.courses.modules.subpages.assignments.index',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.publish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/publish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@publish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@publish',
        'as' => 'teacher.courses.modules.subpages.assignments.publish',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.unpublish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/unpublish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@unpublish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@unpublish',
        'as' => 'teacher.courses.modules.subpages.assignments.unpublish',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.send-reminders' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/send-reminders',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@sendReminders',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@sendReminders',
        'as' => 'teacher.courses.modules.subpages.assignments.send-reminders',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.cancel-schedule' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/cancel-schedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@cancelSchedule',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@cancelSchedule',
        'as' => 'teacher.courses.modules.subpages.assignments.cancel-schedule',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@reorder',
        'as' => 'teacher.courses.modules.subpages.assignments.reorder',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignmentId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@restore',
        'as' => 'teacher.courses.modules.subpages.assignments.restore',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.submissions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'as' => 'teacher.courses.modules.subpages.assignments.submissions.index',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.submissions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'as' => 'teacher.courses.modules.subpages.assignments.submissions.show',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.submissions.grade' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'as' => 'teacher.courses.modules.subpages.assignments.submissions.grade',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.submissions.store-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@storeGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@storeGrade',
        'as' => 'teacher.courses.modules.subpages.assignments.submissions.store-grade',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.submissions.publish-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/publish-grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@publishGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@publishGrade',
        'as' => 'teacher.courses.modules.subpages.assignments.submissions.publish-grade',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.courses.modules.subpages.assignments.submissions.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/download/{filename}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher|Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'as' => 'teacher.courses.modules.subpages.assignments.submissions.download',
        'namespace' => NULL,
        'prefix' => 'teacher/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.assignments.overview' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@overview',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@overview',
        'as' => 'student.assignments.overview',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@index',
        'as' => 'student.courses.modules.subpages.assignments.index',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@show',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@show',
        'as' => 'student.courses.modules.subpages.assignments.show',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.submit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@createSubmission',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@createSubmission',
        'as' => 'student.courses.modules.subpages.assignments.submit',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.store-submission' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@storeSubmission',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@storeSubmission',
        'as' => 'student.courses.modules.subpages.assignments.store-submission',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.submissions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@editSubmission',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@editSubmission',
        'as' => 'student.courses.modules.subpages.assignments.submissions.edit',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.submissions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@updateSubmission',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@updateSubmission',
        'as' => 'student.courses.modules.subpages.assignments.submissions.update',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.courses.modules.subpages.assignments.submissions.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/download/{filename}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AssignmentController@downloadFile',
        'controller' => 'App\\Http\\Controllers\\Student\\AssignmentController@downloadFile',
        'as' => 'student.courses.modules.subpages.assignments.submissions.download',
        'namespace' => NULL,
        'prefix' => 'student/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.assignments.create',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@create',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@create',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.assignments.store',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@store',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@store',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.assignments.show',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@show',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.assignments.edit',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@edit',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@edit',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.assignments.update',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@update',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@update',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'as' => 'admin.courses.modules.subpages.assignments.destroy',
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@destroy',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@index',
        'as' => 'admin.courses.modules.subpages.assignments.index',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.publish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/publish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@publish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@publish',
        'as' => 'admin.courses.modules.subpages.assignments.publish',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.unpublish' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/unpublish',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@unpublish',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@unpublish',
        'as' => 'admin.courses.modules.subpages.assignments.unpublish',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.send-reminders' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/send-reminders',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@sendReminders',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@sendReminders',
        'as' => 'admin.courses.modules.subpages.assignments.send-reminders',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.cancel-schedule' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/cancel-schedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@cancelSchedule',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@cancelSchedule',
        'as' => 'admin.courses.modules.subpages.assignments.cancel-schedule',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@reorder',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@reorder',
        'as' => 'admin.courses.modules.subpages.assignments.reorder',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignmentId}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@restore',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AssignmentController@restore',
        'as' => 'admin.courses.modules.subpages.assignments.restore',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.submissions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@index',
        'as' => 'admin.courses.modules.subpages.assignments.submissions.index',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.submissions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@show',
        'as' => 'admin.courses.modules.subpages.assignments.submissions.show',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.submissions.grade' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@grade',
        'as' => 'admin.courses.modules.subpages.assignments.submissions.grade',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.submissions.store-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@storeGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@storeGrade',
        'as' => 'admin.courses.modules.subpages.assignments.submissions.store-grade',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.submissions.publish-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/publish-grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@publishGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@publishGrade',
        'as' => 'admin.courses.modules.subpages.assignments.submissions.publish-grade',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.courses.modules.subpages.assignments.submissions.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}/submissions/{submission}/download/{filename}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'controller' => 'App\\Http\\Controllers\\Teacher\\SubmissionController@downloadFile',
        'as' => 'admin.courses.modules.subpages.assignments.submissions.download',
        'namespace' => NULL,
        'prefix' => 'admin/courses/{course}/modules/{module}/subpages/{subpage}/assignments/{assignment}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/gradebook',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@index',
        'as' => 'teacher.gradebook.index',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.grade' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/assignments/{assignment}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@grade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@grade',
        'as' => 'teacher.gradebook.grade',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.store-grade' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/assignments/{assignment}/submissions/{submission}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@storeGrade',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@storeGrade',
        'as' => 'teacher.gradebook.store-grade',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.save-draft' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/assignments/{assignment}/submissions/{submission}/save-draft',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@saveDraft',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@saveDraft',
        'as' => 'teacher.gradebook.save-draft',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.publish-grades' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/assignments/{assignment}/publish-grades',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@publishGrades',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@publishGrades',
        'as' => 'teacher.gradebook.publish-grades',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/gradebook/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@export',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@export',
        'as' => 'teacher.gradebook.export',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.configuration' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/grading-configuration',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@configuration',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@configuration',
        'as' => 'teacher.gradebook.configuration',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.update-configuration' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'teacher/courses/{course}/grading-configuration',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@updateConfiguration',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@updateConfiguration',
        'as' => 'teacher.gradebook.update-configuration',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.assignment-stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/assignments/{assignment}/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@assignmentStats',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@assignmentStats',
        'as' => 'teacher.gradebook.assignment-stats',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.lock' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/grades/{grade}/lock',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@lock',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@lock',
        'as' => 'teacher.gradebook.lock',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.unlock' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'teacher/grades/{grade}/unlock',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@unlock',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@unlock',
        'as' => 'teacher.gradebook.unlock',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.gradebook.override-history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/grades/{grade}/override-history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@overrideHistory',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@overrideHistory',
        'as' => 'teacher.gradebook.override-history',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.grades.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/grades',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\GradeController@index',
        'controller' => 'App\\Http\\Controllers\\Student\\GradeController@index',
        'as' => 'student.grades.index',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.grades.course' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/courses/{course}/grades',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\GradeController@course',
        'controller' => 'App\\Http\\Controllers\\Student\\GradeController@course',
        'as' => 'student.grades.course',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.grades.assignment' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/assignments/{assignment}/grade',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\GradeController@assignment',
        'controller' => 'App\\Http\\Controllers\\Student\\GradeController@assignment',
        'as' => 'student.grades.assignment',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.grades.statistics' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/grades/statistics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\GradeController@statistics',
        'controller' => 'App\\Http\\Controllers\\Student\\GradeController@statistics',
        'as' => 'student.grades.statistics',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.grades.transcript' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/transcript',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\GradeController@transcript',
        'controller' => 'App\\Http\\Controllers\\Student\\GradeController@transcript',
        'as' => 'student.grades.transcript',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.gradebook.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/gradebook',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@index',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@index',
        'as' => 'admin.gradebook.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.gradebook.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/courses/{course}/gradebook/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@export',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@export',
        'as' => 'admin.gradebook.export',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.gradebook.show-override' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/grades/{grade}/override',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@showOverride',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@showOverride',
        'as' => 'admin.gradebook.show-override',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.gradebook.override' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/grades/{grade}/override',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@override',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@override',
        'as' => 'admin.gradebook.override',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.gradebook.override-history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/grades/{grade}/override-history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@overrideHistory',
        'controller' => 'App\\Http\\Controllers\\Teacher\\GradeBookController@overrideHistory',
        'as' => 'admin.gradebook.override-history',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.forums.create',
        'uses' => 'App\\Http\\Controllers\\ForumController@create',
        'controller' => 'App\\Http\\Controllers\\ForumController@create',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/forums',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.forums.store',
        'uses' => 'App\\Http\\Controllers\\ForumController@store',
        'controller' => 'App\\Http\\Controllers\\ForumController@store',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums/{forum}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.forums.show',
        'uses' => 'App\\Http\\Controllers\\ForumController@show',
        'controller' => 'App\\Http\\Controllers\\ForumController@show',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums/{forum}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.forums.edit',
        'uses' => 'App\\Http\\Controllers\\ForumController@edit',
        'controller' => 'App\\Http\\Controllers\\ForumController@edit',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'courses/{course}/forums/{forum}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.forums.update',
        'uses' => 'App\\Http\\Controllers\\ForumController@update',
        'controller' => 'App\\Http\\Controllers\\ForumController@update',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}/forums/{forum}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.forums.destroy',
        'uses' => 'App\\Http\\Controllers\\ForumController@destroy',
        'controller' => 'App\\Http\\Controllers\\ForumController@destroy',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@index',
        'controller' => 'App\\Http\\Controllers\\ForumController@index',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.topics.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@createTopic',
        'controller' => 'App\\Http\\Controllers\\ForumController@createTopic',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.topics.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.topics.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@storeTopic',
        'controller' => 'App\\Http\\Controllers\\ForumController@storeTopic',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.topics.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.topics.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics/{topic}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@showTopic',
        'controller' => 'App\\Http\\Controllers\\ForumController@showTopic',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.topics.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.topics.replies.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics/{topic}/replies',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@storeReply',
        'controller' => 'App\\Http\\Controllers\\ForumController@storeReply',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.topics.replies.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.posts.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics/{topic}/posts/{post}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@editPost',
        'controller' => 'App\\Http\\Controllers\\ForumController@editPost',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.posts.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.posts.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics/{topic}/posts/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@updatePost',
        'controller' => 'App\\Http\\Controllers\\ForumController@updatePost',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.posts.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.forums.posts.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}/forums/{forum}/topics/{topic}/posts/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\ForumController@destroyPost',
        'controller' => 'App\\Http\\Controllers\\ForumController@destroyPost',
        'namespace' => NULL,
        'prefix' => 'courses/{course}/forums/{forum}',
        'where' => 
        array (
        ),
        'as' => 'courses.forums.posts.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.analytics.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/analytics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher,Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AnalyticsController@dashboard',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AnalyticsController@dashboard',
        'as' => 'teacher.analytics.dashboard',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.analytics.assignment' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/assignments/{assignment}/analytics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher,Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AnalyticsController@assignment',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AnalyticsController@assignment',
        'as' => 'teacher.analytics.assignment',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'teacher.analytics.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'teacher/courses/{course}/analytics/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Teacher,Admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Teacher\\AnalyticsController@exportCsv',
        'controller' => 'App\\Http\\Controllers\\Teacher\\AnalyticsController@exportCsv',
        'as' => 'teacher.analytics.export',
        'namespace' => NULL,
        'prefix' => '/teacher',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'student.analytics.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'student/analytics/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'role:Student',
        ),
        'uses' => 'App\\Http\\Controllers\\Student\\AnalyticsController@dashboard',
        'controller' => 'App\\Http\\Controllers\\Student\\AnalyticsController@dashboard',
        'as' => 'student.analytics.dashboard',
        'namespace' => NULL,
        'prefix' => '/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/announcements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.index',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@index',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@index',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/announcements/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.create',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@create',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@create',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'courses/{course}/announcements',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.store',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@store',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@store',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/announcements/{announcement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.show',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@show',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@show',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'courses/{course}/announcements/{announcement}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.edit',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@edit',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@edit',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'courses/{course}/announcements/{announcement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.update',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@update',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@update',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'courses.announcements.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'courses/{course}/announcements/{announcement}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'courses.announcements.destroy',
        'uses' => 'App\\Http\\Controllers\\AnnouncementController@destroy',
        'controller' => 'App\\Http\\Controllers\\AnnouncementController@destroy',
        'namespace' => NULL,
        'prefix' => '/courses/{course}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'messages.index',
        'uses' => 'App\\Http\\Controllers\\MessageController@index',
        'controller' => 'App\\Http\\Controllers\\MessageController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'messages/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'messages.create',
        'uses' => 'App\\Http\\Controllers\\MessageController@create',
        'controller' => 'App\\Http\\Controllers\\MessageController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'messages.store',
        'uses' => 'App\\Http\\Controllers\\MessageController@store',
        'controller' => 'App\\Http\\Controllers\\MessageController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'messages/{message}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'messages.show',
        'uses' => 'App\\Http\\Controllers\\MessageController@show',
        'controller' => 'App\\Http\\Controllers\\MessageController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'messages/{message}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'as' => 'messages.destroy',
        'uses' => 'App\\Http\\Controllers\\MessageController@destroy',
        'controller' => 'App\\Http\\Controllers\\MessageController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.reply' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'messages/{message}/reply',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\MessageController@reply',
        'controller' => 'App\\Http\\Controllers\\MessageController@reply',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'messages.reply',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'messages.reply.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'messages/{message}/reply',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\MessageController@storeReply',
        'controller' => 'App\\Http\\Controllers\\MessageController@storeReply',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'messages.reply.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.preferences' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'notifications/preferences',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationPreferenceController@edit',
        'controller' => 'App\\Http\\Controllers\\NotificationPreferenceController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.preferences',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.preferences.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'notifications/preferences',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationPreferenceController@update',
        'controller' => 'App\\Http\\Controllers\\NotificationPreferenceController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.preferences.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.preferences.reset' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'notifications/preferences/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationPreferenceController@reset',
        'controller' => 'App\\Http\\Controllers\\NotificationPreferenceController@reset',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.preferences.reset',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.mark-read' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'notifications/mark-read',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationPreferenceController@markAsRead',
        'controller' => 'App\\Http\\Controllers\\NotificationPreferenceController@markAsRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.mark-read',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.mark-all-read' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'notifications/mark-all-read',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationPreferenceController@markAllAsRead',
        'controller' => 'App\\Http\\Controllers\\NotificationPreferenceController@markAllAsRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.mark-all-read',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.unread-count' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'notifications/unread-count',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationPreferenceController@getUnreadCount',
        'controller' => 'App\\Http\\Controllers\\NotificationPreferenceController@getUnreadCount',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.unread-count',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:32:"/var/www/lms/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:1;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"000000000000085b0000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local.upload' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:32:"/var/www/lms/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:1;}s:8:"function";s:325:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ReceiveFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000009a80000000000000000";}}',
        'as' => 'storage.local.upload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
