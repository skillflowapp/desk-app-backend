# SKILLFLOW - AI-Powered School Examination Platform

## Complete Implementation Guide

This document describes the fully implemented Laravel backend for SKILLFLOW, an AI-powered examination system with support for offline functionality.

---

## 📋 TABLE OF CONTENTS

1. [Architecture Overview](#architecture-overview)
2. [Database Schema](#database-schema)
3. [API Endpoints](#api-endpoints)
4. [Authentication & Authorization](#authentication--authorization)
5. [Exam Flow](#exam-flow)
6. [Grading System](#grading-system)
7. [Offline Sync](#offline-sync)
8. [Security Features](#security-features)
9. [Setup Instructions](#setup-instructions)
10. [Testing](#testing)

---

## 🏗️ Architecture Overview

### Core Principles

- **Laravel 12 API-only backend** - Single source of truth
- **MySQL database** - Authoritative storage
- **Stateless desktop client** - UI only, no logic
- **Queue-based processing** - Async PDF OCR and AI grading
- **Audit logging** - Complete security trail

### Technology Stack

| Component | Technology |
|-----------|-----------|
| Framework | Laravel 12.44.0 |
| Database | MySQL 8.0+ |
| Queue | Redis/Database |
| Authentication | Laravel Sanctum (JWT) |
| PDF Processing | pdftotext, DomPDF |
| AI Integration | OpenAI/Gemini/Local LLM |
| Testing | PHPUnit 11.5 |

---

## 🗄️ Database Schema

### Core Tables

#### **users**
Main user table with authentication.

#### **roles** & **user_roles**
RBAC implementation:
- `teacher` - Can create exams, upload PDFs, grade results
- `student` - Can take exams, view results
- `admin` - System administrator

#### **exams**
Exam metadata and configuration.
```
- id, teacher_id, pdf_upload_id
- title, description, duration_minutes
- total_marks, passing_marks
- ai_prompt (JSON for question generation)
- status (draft/published/archived)
```

#### **exam_questions**
Individual exam questions with support for multiple types.
```
- id, exam_id, question_number
- type (mcq/short_answer/essay)
- question_text, options (JSON)
- correct_answer, model_answer, marks
- explanation
```

#### **exam_codes**
One-time entry codes for exams.
```
- id, exam_id, code (unique)
- max_attempts, is_active
- expires_at
```

#### **exam_sessions**
Student exam sessions with timing and device binding.
```
- id, exam_id, student_id, exam_code_id
- started_at, submitted_at
- status (in_progress/submitted/timed_out/cancelled)
- device_fingerprint, ip_address, meta_data
- flagged_for_review, flag_reason
```

#### **student_answers**
Individual student answers with immutable log.
```
- id, exam_session_id, exam_question_id
- answer_text, selected_option
- answered_at, time_spent_seconds
- is_final
```

#### **results**
Final results with AI feedback.
```
- id, exam_session_id, exam_id, student_id
- obtained_marks, total_marks, percentage
- is_passed, status (pending/graded/published)
- question_scores (JSON), ai_feedback (JSON)
- teacher_remarks, graded_at, published_at
```

#### **pdf_uploads**
PDF upload tracking with OCR status.
```
- id, user_id, filename, storage_path
- pages, extracted_text
- ocr_processed, status (pending/processing/completed/failed)
```

#### **sync_queues**
Offline sync queue for data synchronization.
```
- id, student_id, entity_type, entity_id
- action (create/update/delete)
- payload (JSON), status (pending/synced/failed)
```

#### **audit_logs**
Complete audit trail for security.
```
- id, user_id, action, entity_type, entity_id
- old_values, new_values (JSON)
- ip_address, user_agent, status
- meta_data (JSON)
```

---

## 🔌 API Endpoints

### Authentication

```http
POST   /api/auth/register          # Register user (teacher/student)
POST   /api/auth/login             # Login and get JWT token
GET    /api/auth/me                # Get current user
POST   /api/auth/logout            # Logout and revoke token
POST   /api/auth/refresh           # Refresh JWT token
```

### Exam Management (Teacher Only)

```http
GET    /api/exams                  # List my exams
POST   /api/exams                  # Create exam
GET    /api/exams/{exam}           # Get exam details
PUT    /api/exams/{exam}           # Update exam (draft only)
DELETE /api/exams/{exam}           # Delete exam (draft only)
POST   /api/exams/{exam}/publish   # Publish exam

POST   /api/exams/{exam}/questions                # Add question
PUT    /api/exams/{exam}/questions/{question}    # Update question
DELETE /api/exams/{exam}/questions/{question}    # Delete question

POST   /api/exams/{exam}/generate-code           # Generate exam access code
POST   /api/exams/{exam}/generate-from-pdf       # AI-generate questions from PDF
```

### PDF Management (Teacher Only)

```http
POST   /api/pdfs/upload            # Upload PDF (triggers OCR job)
GET    /api/pdfs                   # List my PDFs
GET    /api/pdfs/{pdfUpload}       # Get PDF details
```

### Exam Session (Student Only)

```http
POST   /api/exams/enter                          # Enter exam with code
GET    /api/exams/active                         # Get active exam session
GET    /api/exams/session/{examSession}          # Get session details
POST   /api/exams/session/{examSession}/answer   # Submit answer
POST   /api/exams/session/{examSession}/submit   # Submit exam
```

### Results & Grading

```http
GET    /api/results                 # Student: Get my results
GET    /api/results/{result}        # Get result (auth required)
POST   /api/results/{result}/publish    # Teacher: Publish result
POST   /api/results/{result}/remarks    # Teacher: Add remarks
GET    /api/results/{result}/export     # Export result as PDF
POST   /api/results/{result}/flag       # Flag result for review
```

### Offline Sync

```http
POST   /api/sync                    # Sync offline data
GET    /api/sync/pending            # Get pending sync items
POST   /api/sync/acknowledge        # Mark sync item as synced
GET    /api/sync/status             # Check sync status
```

### Audit Logs (Admin Only)

```http
GET    /api/audit-logs              # List audit logs (filterable)
GET    /api/audit-logs/{auditLog}   # Get log details
GET    /api/users/{user}/activity   # Get user activity
POST   /api/audit-logs/export       # Export audit logs (CSV/JSON)
```

---

## 🔐 Authentication & Authorization

### JWT Token Flow

1. **Registration/Login** → Returns JWT token
2. **Token Storage** → Client stores token (encrypted in SQLite)
3. **API Requests** → Include token in `Authorization: Bearer {token}` header
4. **Token Expiry** → Auto-refresh endpoint available
5. **Logout** → Token is revoked on backend

### Role-Based Middleware

```php
// Teacher only
Route::middleware('teacher')->group(...)

// Student only
Route::middleware('student')->group(...)

// Custom roles
Route::middleware('role:admin,teacher')->group(...)
```

### User Methods

```php
$user->isTeacher()          // Check if teacher
$user->isStudent()          // Check if student
$user->isAdmin()            // Check if admin
$user->hasRole('teacher')   // Check specific role
$user->hasAnyRole(['teacher', 'admin'])  // Check multiple roles
```

---

## 📝 Exam Flow

### Teacher: Create Exam

```
1. POST /api/exams                      Create exam (draft)
2. POST /api/pdfs/upload                Upload PDF
3. POST /api/exams/{exam}/generate-from-pdf    Generate questions
   OR POST /api/exams/{exam}/questions  Add questions manually
4. POST /api/exams/{exam}/publish       Publish exam
5. POST /api/exams/{exam}/generate-code Generate access code
6. Share code with students
```

### Student: Take Exam

```
1. POST /api/exams/enter                Enter exam (with code)
   → Validates code, creates session, starts timer
2. GET /api/exams/session/{session}     Get exam questions
3. POST /api/exams/session/{session}/answer    Submit answers
4. POST /api/exams/session/{session}/submit    Finish exam
   → Dispatches GradeExamSession job
```

### Teacher: Grade & Publish

```
1. AI auto-grades essay/short answers (async)
2. GET /api/exams/{exam}/results        View all results
3. POST /api/results/{result}/remarks   Add teacher remarks (optional)
4. POST /api/results/{result}/publish   Publish to student
5. GET /api/results/{result}/export     Export result as PDF
```

---

## 🤖 Grading System

### Automatic Grading

#### MCQ Questions
- Instant comparison with correct answer
- Full marks if correct, 0 if wrong

#### Short Answer / Essay
- Uses AI (OpenAI/Gemini/Local LLM)
- Returns score (0-maxMarks) + feedback
- Supports partial credit

### Grading Service

```php
$gradingService = new GradingService();
$result = $gradingService->gradeExamSession($examSession);
// Returns: Result object with marks, feedback, etc
```

### Example Response

```json
{
  "question_scores": {
    "1": { "obtained": 5, "total": 5, "percentage": 100 },
    "2": { "obtained": 3, "total": 5, "percentage": 60 }
  },
  "ai_feedback": {
    "overall": "Good performance",
    "percentage": 80,
    "passed": true
  }
}
```

---

## 🔄 Offline Sync

### Client-Side (Desktop App)

```
Offline Mode:
- Cache exams locally
- Store answers in SQLite
- Queue changes in sync_queue table

Online Detection:
- Detect internet connection
- POST /api/sync with all pending items
- Receive feedback
```

### Server-Side Validation

```php
// Services/OfflineSyncService
$syncService->syncOfflineData($student, $offlineData);

Validates:
- Exam code still valid
- Session not expired
- Device fingerprint matches (anti-cheating)
- Time limit not exceeded
- Questions belong to exam
```

### Anti-Cheating Measures

- Device fingerprint validation
- IP address logging
- Session flagging on suspicious activity
- Complete audit trail

---

## 🛡️ Security Features

### 1. Authentication
- JWT tokens with automatic expiry
- Sanctum for API token management
- Secure password hashing

### 2. Authorization
- Role-based access control (RBAC)
- Resource ownership validation
- Middleware guards on all routes

### 3. Rate Limiting
- 5 exam entries per minute (per user/IP)
- 10 login attempts per minute
- 100 general API requests per minute
- Configurable per endpoint

### 4. Audit Logging
- All user actions logged
- Before/after values for updates
- IP address and user agent tracking
- Admin audit log export

### 5. Data Protection
- PDF files stored with private access
- Sensitive data in JSON fields (encrypted if configured)
- SQL injection prevention (Eloquent ORM)
- CSRF protection

### 6. Anti-Cheating
- Device fingerprint binding
- Exam time enforcement
- Session flagging for review
- Answer immutability
- Network monitoring via audit logs

---

## ⚙️ Setup Instructions

### Prerequisites

```bash
PHP 8.4+
MySQL 8.0+
Redis (optional, for queues)
Node.js 18+ (for Vite)
pdftotext (for PDF OCR)
```

### Installation

```bash
# 1. Install dependencies
composer install

# 2. Create environment file
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. Create database
mysql -u root -p
CREATE DATABASE skillflow;

# 5. Run migrations
php artisan migrate

# 6. Seed initial data
php artisan db:seed

# 7. Create storage links
php artisan storage:link

# 8. Configure AI provider (optional)
# Edit .env:
AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
```

### Environment Variables

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skillflow
DB_USERNAME=root
DB_PASSWORD=

# Queue (for async jobs)
QUEUE_CONNECTION=database    # or redis

# AI Configuration
AI_PROVIDER=openai            # openai, gemini, local
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4-turbo
```

### Running

```bash
# Development server
php artisan serve

# Queue worker (process jobs)
php artisan queue:work

# Watch for file changes (Vite)
npm run dev
```

---

## 🧪 Testing

### Seed Test Data

```php
// Create users
$teacher = User::create([...]);
$teacher->roles()->attach(Role::where('name', 'teacher')->first());

$student = User::create([...]);
$student->roles()->attach(Role::where('name', 'student')->first());

// Create exam
$exam = Exam::create([
    'teacher_id' => $teacher->id,
    'title' => 'Math 101',
    'duration_minutes' => 60,
    'total_marks' => 100,
    'passing_marks' => 40,
]);

// Create questions
ExamQuestion::create([
    'exam_id' => $exam->id,
    'type' => 'mcq',
    'question_text' => 'What is 2+2?',
    'options' => ['A' => '3', 'B' => '4', 'C' => '5'],
    'correct_answer' => 'B',
    'marks' => 5,
]);

// Create exam code
$code = ExamCode::create([
    'exam_id' => $exam->id,
    'code' => 'ABC123',
    'max_attempts' => 1,
]);
```

### API Testing

```bash
# Using curl
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teacher@example.com","password":"password"}'

# Using Laravel Tinker
php artisan tinker
> User::first()->createToken('test')->plainTextToken
```

---

## 📊 Monitoring & Maintenance

### Check Jobs/Queue

```bash
php artisan queue:list
php artisan queue:failed
php artisan queue:retry all
```

### View Audit Logs

```bash
# Via API
GET /api/audit-logs?action=login&status=failed

# Via Tinker
AuditLog::where('action', 'login')->where('status', 'failed')->get()
```

### Database Health

```bash
# Check migrations
php artisan migrate:status

# Clear cache
php artisan cache:clear
php artisan config:cache

# Optimize
php artisan optimize
```

---

## 📚 File Structure

```
app/
├── Http/Controllers/Api/
│   ├── AuthController.php
│   ├── ExamController.php
│   ├── ExamSessionController.php
│   ├── ResultController.php
│   ├── PdfUploadController.php
│   ├── SyncController.php
│   └── AuditLogController.php
├── Http/Middleware/
│   ├── EnsureUserHasRole.php
│   ├── EnsureUserIsTeacher.php
│   ├── EnsureUserIsStudent.php
│   ├── LogApiActivity.php
│   └── ApiRateLimit.php
├── Jobs/
│   ├── ProcessPdfOcr.php
│   └── GradeExamSession.php
├── Services/
│   ├── ExamGenerationService.php
│   ├── GradingService.php
│   ├── OfflineSyncService.php
│   └── PdfExportService.php
└── Models/
    ├── User.php, Role.php
    ├── Exam.php, ExamQuestion.php
    ├── ExamSession.php, ExamCode.php
    ├── Result.php, StudentAnswer.php
    ├── PdfUpload.php, SyncQueue.php
    └── AuditLog.php

routes/
├── api.php          # All API routes
└── web.php          # Web routes

database/
├── migrations/      # All table migrations
└── seeders/         # Database seeders

resources/
└── views/pdf/       # PDF export templates
    ├── result.blade.php
    └── exam.blade.php

config/
└── ai.php          # AI provider configuration
```

---

## 🚀 Deployment Checklist

- [ ] Set `APP_ENV=production` in .env
- [ ] Set `APP_DEBUG=false`
- [ ] Generate secure app key
- [ ] Configure HTTPS
- [ ] Set up database backups
- [ ] Configure Redis for queues
- [ ] Set up queue supervisor (Horizon)
- [ ] Configure email for notifications
- [ ] Set up monitoring/logging
- [ ] Configure PDF storage (S3/CDN)
- [ ] Set up AI API keys securely
- [ ] Test offline sync thoroughly
- [ ] Performance load testing

---

## 📞 Support & Documentation

For detailed API documentation, see `/docs/API.md`
For AI configuration guide, see `/docs/AI_SETUP.md`
For offline sync protocol, see `/docs/OFFLINE_SYNC.md`

---

**Version**: 1.0.0  
**Last Updated**: January 3, 2026  
**Status**: Production Ready ✅
