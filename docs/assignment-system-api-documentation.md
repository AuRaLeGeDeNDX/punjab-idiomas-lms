# Complete Assignment System - API Documentation

## Overview

This document provides comprehensive API documentation for the Complete Assignment System in the Laravel LMS. It covers all endpoints, request/response formats, authentication requirements, and usage examples.

**Version**: 1.0  
**Last Updated**: February 2, 2026  
**Base URL**: `/api/v1`

---

## Table of Contents

1. [Authentication](#authentication)
2. [Assignments API](#assignments-api)
3. [Submissions API](#submissions-api)
4. [Grades API](#grades-api)
5. [Analytics API](#analytics-api)
6. [Bulk Operations API](#bulk-operations-api)
7. [Templates API](#templates-api)
8. [Rubrics API](#rubrics-api)
9. [Notifications API](#notifications-api)
10. [Error Handling](#error-handling)

---

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens.

### Obtaining a Token

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response**:
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "Teacher"
  }
}
```

### Using the Token

Include the token in the Authorization header:

```http
Authorization: Bearer 1|abc123...
```

---

## Assignments API

### List Assignments

Retrieve a paginated list of assignments with optional filtering.

```http
GET /api/assignments
Authorization: Bearer {token}
```

**Query Parameters**:
- `course_id` (integer, optional): Filter by course
- `type` (string, optional): Filter by type (homework, project, quiz, exam, essay)
- `status` (string, optional): Filter by status (draft, published)
- `due_date_from` (date, optional): Filter assignments due after this date
- `due_date_to` (date, optional): Filter assignments due before this date
- `search` (string, optional): Search in title, description, instructions
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Essay on Climate Change",
      "description": "Write a 500-word essay...",
      "type": "essay",
      "submission_type": "both",
      "max_score": 100,
      "due_date": "2026-02-15T23:59:59Z",
      "is_published": true,
      "scheduled_publish_at": null,
      "allow_late_submission": true,
      "course": {
        "id": 1,
        "name": "Environmental Science"
      },
      "stats": {
        "total_submissions": 25,
        "graded_count": 20,
        "average_score": 85.5
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

### Get Assignment Details

```http
GET /api/assignments/{id}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "id": 1,
  "title": "Essay on Climate Change",
  "description": "Write a 500-word essay...",
  "instructions": "Include at least 3 sources...",
  "type": "essay",
  "submission_type": "both",
  "max_score": 100,
  "due_date": "2026-02-15T23:59:59Z",
  "is_published": true,
  "scheduled_publish_at": null,
  "allow_late_submission": true,
  "created_at": "2026-02-01T10:00:00Z",
  "updated_at": "2026-02-01T10:00:00Z",
  "course": {
    "id": 1,
    "name": "Environmental Science"
  },
  "rubric": {
    "id": 1,
    "title": "Essay Rubric",
    "criteria": [
      {
        "id": 1,
        "name": "Content Quality",
        "description": "Depth and accuracy of content",
        "max_points": 40
      }
    ]
  }
}
```

### Create Assignment

```http
POST /api/assignments
Authorization: Bearer {token}
Content-Type: application/json

{
  "course_id": 1,
  "module_id": 2,
  "subpage_id": 3,
  "title": "New Assignment",
  "description": "Assignment description",
  "instructions": "Detailed instructions",
  "type": "homework",
  "submission_type": "file",
  "max_score": 100,
  "due_date": "2026-03-01T23:59:59Z",
  "allow_late_submission": true,
  "is_published": false,
  "scheduled_publish_at": "2026-02-20T08:00:00Z"
}
```

**Response**: `201 Created`
```json
{
  "id": 42,
  "title": "New Assignment",
  "message": "Assignment created successfully"
}
```

### Update Assignment

```http
PUT /api/assignments/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Updated Title",
  "due_date": "2026-03-15T23:59:59Z"
}
```

**Response**: `200 OK`

### Delete Assignment

```http
DELETE /api/assignments/{id}
Authorization: Bearer {token}
```

**Response**: `204 No Content`

### Publish Assignment

```http
POST /api/assignments/{id}/publish
Authorization: Bearer {token}
```

**Response**: `200 OK`
```json
{
  "message": "Assignment published successfully",
  "notifications_sent": 25
}
```

### Schedule Assignment

```http
POST /api/assignments/{id}/schedule
Authorization: Bearer {token}
Content-Type: application/json

{
  "scheduled_publish_at": "2026-02-20T08:00:00Z"
}
```

**Response**: `200 OK`

### Cancel Schedule

```http
POST /api/assignments/{id}/cancel-schedule
Authorization: Bearer {token}
```

**Response**: `200 OK`

---

## Submissions API

### List Submissions

```http
GET /api/assignments/{assignment_id}/submissions
Authorization: Bearer {token}
```

**Query Parameters**:
- `status` (string, optional): Filter by status (pending, submitted, graded)
- `is_late` (boolean, optional): Filter late submissions
- `grade_min` (number, optional): Minimum grade filter
- `grade_max` (number, optional): Maximum grade filter
- `student_id` (integer, optional): Filter by student
- `page` (integer, optional): Page number

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "student": {
        "id": 5,
        "name": "Jane Smith",
        "email": "jane@example.com"
      },
      "content": "My submission text...",
      "status": "graded",
      "is_late": false,
      "submitted_at": "2026-02-10T14:30:00Z",
      "files": [
        {
          "id": 1,
          "file_name": "essay.pdf",
          "file_size": 245678,
          "mime_type": "application/pdf"
        }
      ],
      "grade": {
        "score": 92,
        "feedback": "Excellent work!",
        "is_published": true
      }
    }
  ]
}
```

### Get Submission Details

```http
GET /api/submissions/{id}
Authorization: Bearer {token}
```

**Response**: Includes full submission details, files, versions, and grade.

### Create Submission

```http
POST /api/assignments/{assignment_id}/submissions
Authorization: Bearer {token}
Content-Type: multipart/form-data

content: "My submission text"
files[]: (file upload)
files[]: (file upload)
```

**Response**: `201 Created`

### Update Submission

```http
PUT /api/submissions/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

content: "Updated submission text"
files[]: (file upload)
```

**Response**: `200 OK`

### Download Submission File

```http
GET /api/submission-files/{id}/download
Authorization: Bearer {token}
```

**Response**: File download with signed URL redirect

### Get Submission Versions

```http
GET /api/submissions/{id}/versions
Authorization: Bearer {token}
```

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "version_number": 1,
      "content": "Original submission...",
      "file_paths_snapshot": [...],
      "created_at": "2026-02-10T14:30:00Z"
    },
    {
      "id": 2,
      "version_number": 2,
      "content": "Updated submission...",
      "file_paths_snapshot": [...],
      "created_at": "2026-02-11T10:15:00Z"
    }
  ]
}
```

---

## Grades API

### Create/Update Grade

```http
POST /api/submissions/{submission_id}/grade
Authorization: Bearer {token}
Content-Type: application/json

{
  "score": 92,
  "feedback": "Excellent work! Well researched.",
  "is_published": false,
  "rubric_scores": {
    "1": 38,
    "2": 28,
    "3": 26
  }
}
```

**Response**: `200 OK`

### Publish Grade

```http
POST /api/grades/{id}/publish
Authorization: Bearer {token}
```

**Response**: `200 OK`
```json
{
  "message": "Grade published successfully",
  "notification_sent": true
}
```

### Lock Grade

```http
POST /api/grades/{id}/lock
Authorization: Bearer {token}
```

**Response**: `200 OK`

### Override Locked Grade (Admin Only)

```http
POST /api/grades/{id}/override
Authorization: Bearer {token}
Content-Type: application/json

{
  "new_score": 95,
  "reason": "Student provided additional evidence that was not considered in original grading."
}
```

**Response**: `200 OK`
```json
{
  "message": "Grade overridden successfully",
  "override_id": 5
}
```

### Get Override History

```http
GET /api/grades/{id}/overrides
Authorization: Bearer {token}
```

**Response**:
```json
{
  "data": [
    {
      "id": 5,
      "admin": {
        "id": 1,
        "name": "Admin User"
      },
      "original_score": 92,
      "new_score": 95,
      "reason": "Student provided additional evidence...",
      "overridden_at": "2026-02-12T09:00:00Z"
    }
  ]
}
```

---

## Analytics API

### Teacher Dashboard Analytics

```http
GET /api/analytics/teacher/dashboard
Authorization: Bearer {token}
```

**Query Parameters**:
- `course_id` (integer, required): Course to analyze

**Response**:
```json
{
  "overall_completion_rate": 85.5,
  "average_score": 82.3,
  "submission_timeline": [
    {
      "date": "2026-02-01",
      "count": 12
    }
  ],
  "top_performers": [
    {
      "student_id": 5,
      "name": "Jane Smith",
      "average_score": 95.2
    }
  ],
  "at_risk_students": [
    {
      "student_id": 8,
      "name": "John Doe",
      "average_score": 62.5,
      "missing_assignments": 3
    }
  ],
  "assignment_stats": {
    "total": 10,
    "published": 8,
    "draft": 2
  }
}
```

### Student Dashboard Analytics

```http
GET /api/analytics/student/dashboard
Authorization: Bearer {token}
```

**Response**:
```json
{
  "completion_progress": 75.0,
  "average_score": 85.5,
  "upcoming_assignments": [
    {
      "id": 5,
      "title": "Midterm Essay",
      "due_date": "2026-02-20T23:59:59Z",
      "days_until_due": 5
    }
  ],
  "overdue_assignments": [],
  "missed_assignments": [],
  "recent_grades": [
    {
      "assignment_title": "Quiz 3",
      "score": 92,
      "max_score": 100,
      "graded_at": "2026-02-10T10:00:00Z"
    }
  ],
  "performance_trend": [
    {
      "date": "2026-01-15",
      "score": 85
    },
    {
      "date": "2026-02-01",
      "score": 92
    }
  ]
}
```

### Assignment Analytics

```http
GET /api/analytics/assignments/{id}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "completion_rate": 90.0,
  "average_score": 85.5,
  "late_count": 3,
  "not_submitted_count": 2,
  "grade_distribution": {
    "A": 12,
    "B": 8,
    "C": 3,
    "D": 1,
    "F": 0
  },
  "student_list": [
    {
      "student_id": 5,
      "name": "Jane Smith",
      "status": "graded",
      "score": 95,
      "submitted_at": "2026-02-10T14:30:00Z"
    }
  ]
}
```

---

## Bulk Operations API

### Bulk Download Submissions

```http
POST /api/bulk/download
Authorization: Bearer {token}
Content-Type: application/json

{
  "submission_ids": [1, 2, 3, 4, 5]
}
```

**Response**: `200 OK`
```json
{
  "download_url": "/storage/temp/submissions_20260202_143000.zip",
  "expires_at": "2026-02-02T15:30:00Z"
}
```

### Bulk Export to CSV

```http
POST /api/bulk/export
Authorization: Bearer {token}
Content-Type: application/json

{
  "submission_ids": [1, 2, 3, 4, 5]
}
```

**Response**: `200 OK`
```json
{
  "download_url": "/storage/temp/submissions_export_20260202_143000.csv",
  "expires_at": "2026-02-02T15:30:00Z"
}
```

### Bulk Send Reminders

```http
POST /api/bulk/reminders
Authorization: Bearer {token}
Content-Type: application/json

{
  "assignment_id": 5,
  "student_ids": [1, 2, 3]
}
```

**Response**: `200 OK`
```json
{
  "successful": 3,
  "failed": 0,
  "message": "Reminders sent to 3 students"
}
```

### Bulk Grade

```http
POST /api/bulk/grade
Authorization: Bearer {token}
Content-Type: application/json

{
  "submission_ids": [1, 2, 3],
  "score": 85,
  "feedback": "Good work on this assignment."
}
```

**Response**: `200 OK`
```json
{
  "successful": 3,
  "failed": 0,
  "message": "Graded 3 submissions"
}
```

### Bulk Publish Grades

```http
POST /api/assignments/{id}/publish-grades
Authorization: Bearer {token}
```

**Response**: `200 OK`
```json
{
  "published_count": 15,
  "message": "Published 15 grades"
}
```

---

## Templates API

### List Templates

```http
GET /api/templates
Authorization: Bearer {token}
```

**Query Parameters**:
- `is_public` (boolean, optional): Filter public/private templates

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Standard Essay Template",
      "description": "Template for essay assignments",
      "type": "essay",
      "submission_type": "both",
      "max_score": 100,
      "is_public": true,
      "teacher": {
        "id": 2,
        "name": "Prof. Johnson"
      }
    }
  ]
}
```

### Create Template

```http
POST /api/templates
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "My Template",
  "description": "Template description",
  "instructions": "Template instructions",
  "type": "homework",
  "submission_type": "file",
  "max_score": 100,
  "is_public": false
}
```

**Response**: `201 Created`

### Apply Template

```http
POST /api/templates/{id}/apply
Authorization: Bearer {token}
Content-Type: application/json

{
  "course_id": 1,
  "module_id": 2,
  "subpage_id": 3,
  "title": "New Assignment from Template",
  "due_date": "2026-03-01T23:59:59Z"
}
```

**Response**: `201 Created`

---

## Rubrics API

### Create Rubric

```http
POST /api/assignments/{assignment_id}/rubric
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Essay Rubric",
  "description": "Grading criteria for essays",
  "criteria": [
    {
      "name": "Content Quality",
      "description": "Depth and accuracy",
      "max_points": 40
    },
    {
      "name": "Organization",
      "description": "Structure and flow",
      "max_points": 30
    },
    {
      "name": "Grammar",
      "description": "Language mechanics",
      "max_points": 30
    }
  ]
}
```

**Response**: `201 Created`

### Get Rubric

```http
GET /api/rubrics/{id}
Authorization: Bearer {token}
```

**Response**:
```json
{
  "id": 1,
  "title": "Essay Rubric",
  "description": "Grading criteria for essays",
  "criteria": [
    {
      "id": 1,
      "name": "Content Quality",
      "description": "Depth and accuracy",
      "max_points": 40,
      "order_index": 0
    }
  ]
}
```

---

## Notifications API

### Get Notification Preferences

```http
GET /api/notifications/preferences
Authorization: Bearer {token}
```

**Response**:
```json
{
  "data": [
    {
      "notification_type": "assignment_published",
      "in_app_enabled": true,
      "email_enabled": true
    },
    {
      "notification_type": "grade_published",
      "in_app_enabled": true,
      "email_enabled": false
    }
  ]
}
```

### Update Notification Preferences

```http
PUT /api/notifications/preferences
Authorization: Bearer {token}
Content-Type: application/json

{
  "assignment_published": {
    "in_app_enabled": true,
    "email_enabled": true
  },
  "grade_published": {
    "in_app_enabled": true,
    "email_enabled": false
  }
}
```

**Response**: `200 OK`

---

## Error Handling

### Error Response Format

All errors follow this format:

```json
{
  "message": "Human-readable error message",
  "errors": {
    "field_name": [
      "Specific validation error"
    ]
  }
}
```

### HTTP Status Codes

- `200 OK`: Successful request
- `201 Created`: Resource created successfully
- `204 No Content`: Successful deletion
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

### Common Error Examples

**Validation Error**:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "due_date": ["The due date must be a date after today."]
  }
}
```

**Authorization Error**:
```json
{
  "message": "This action is unauthorized."
}
```

**Not Found Error**:
```json
{
  "message": "Assignment not found."
}
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests**: 60 requests per minute
- **Bulk operations**: 10 requests per minute
- **File uploads**: 20 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1643812800
```

---

## Pagination

List endpoints support pagination with these parameters:

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Pagination metadata is included in responses:

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "/api/assignments?page=1",
    "last": "/api/assignments?page=5",
    "prev": null,
    "next": "/api/assignments?page=2"
  }
}
```

---

## Webhooks

The system supports webhooks for real-time event notifications:

### Supported Events

- `assignment.published`
- `assignment.updated`
- `submission.created`
- `submission.updated`
- `grade.published`
- `grade.overridden`

### Webhook Payload Example

```json
{
  "event": "grade.published",
  "timestamp": "2026-02-02T14:30:00Z",
  "data": {
    "grade_id": 42,
    "submission_id": 15,
    "assignment_id": 5,
    "student_id": 8,
    "score": 92,
    "max_score": 100
  }
}
```

---

## Best Practices

1. **Use Pagination**: Always paginate list requests to avoid performance issues
2. **Cache Responses**: Cache analytics data on the client side (2-5 minutes)
3. **Handle Errors Gracefully**: Always check for error responses and display user-friendly messages
4. **Use Bulk Operations**: For multiple items, use bulk endpoints instead of individual requests
5. **Respect Rate Limits**: Implement exponential backoff when rate limited
6. **Validate Before Submitting**: Validate data client-side before API calls
7. **Use Signed URLs**: For file downloads, use the provided signed URLs
8. **Monitor Webhooks**: Implement webhook handlers for real-time updates

---

## Support

For API support, contact:
- **Email**: api-support@example.com
- **Documentation**: https://docs.example.com/api
- **Status Page**: https://status.example.com

---

**Document Version**: 1.0  
**Last Updated**: February 2, 2026
