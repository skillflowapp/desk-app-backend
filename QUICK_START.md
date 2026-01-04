# SKILLFLOW - Quick Start Guide

## 🚀 5-Minute Setup

### Step 1: Install Dependencies
```bash
cd /home/anonynoman/Desktop/skillflow
composer install
npm install
```

### Step 2: Configure Environment
```bash
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials
# DB_DATABASE=skillflow
# DB_USERNAME=root
# DB_PASSWORD=
```

### Step 3: Setup Database
```bash
# Create database
mysql -u root -p < /dev/null <<EOF
CREATE DATABASE skillflow;
EOF

# Run migrations
php artisan migrate

# Seed initial roles
php artisan db:seed --class=RoleSeeder
```

### Step 4: Start Development Server
```bash
# In one terminal
php artisan serve

# In another terminal (for queue jobs)
php artisan queue:work database

# In another terminal (for assets)
npm run dev
```

**API is now available at:** `http://localhost:8000/api`

---

## 🔑 Quick Test

### 1. Register a Teacher
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "teacher@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "teacher"
  }'
```

### 2. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "teacher@example.com",
    "password": "password123"
  }'
```

**Save the token from response!**

### 3. Create an Exam
```bash
TOKEN="your-token-here"

curl -X POST http://localhost:8000/api/exams \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Math Final Exam",
    "description": "Final exam for Math 101",
    "duration_minutes": 60,
    "total_marks": 100,
    "passing_marks": 40
  }'
```

### 4. Add a Question
```bash
EXAM_ID=1  # From previous response

curl -X POST http://localhost:8000/api/exams/$EXAM_ID/questions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "mcq",
    "question_text": "What is 2+2?",
    "options": {"A": "3", "B": "4", "C": "5", "D": "6"},
    "correct_answer": "B",
    "marks": 5,
    "explanation": "2 + 2 equals 4"
  }'
```

### 5. Publish Exam
```bash
curl -X POST http://localhost:8000/api/exams/$EXAM_ID/publish \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### 6. Generate Access Code
```bash
curl -X POST http://localhost:8000/api/exams/$EXAM_ID/generate-code \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "max_attempts": 1,
    "expires_at": "2026-12-31T23:59:59Z"
  }'
```

### 7. Register a Student
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "student@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "student"
  }'
```

### 8. Student Takes Exam
```bash
STUDENT_TOKEN="student-token"
CODE="ABC123DE"  # From generate-code response

# Enter exam
curl -X POST http://localhost:8000/api/exams/enter \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"code": "'$CODE'"}'

# Returns: session ID
SESSION_ID=1

# Get exam questions
curl -X GET http://localhost:8000/api/exams/session/$SESSION_ID \
  -H "Authorization: Bearer $STUDENT_TOKEN"

# Submit answer
curl -X POST http://localhost:8000/api/exams/session/$SESSION_ID/answer \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "question_id": 1,
    "selected_option": "B"
  }'

# Submit exam
curl -X POST http://localhost:8000/api/exams/session/$SESSION_ID/submit \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### 9. Teacher Grades Exam
```bash
# Wait for auto-grading (check queue)

# Get results
curl -X GET http://localhost:8000/api/exams/$EXAM_ID/results \
  -H "Authorization: Bearer $TOKEN"

RESULT_ID=1  # From response

# Publish result
curl -X POST http://localhost:8000/api/results/$RESULT_ID/publish \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'

# Export as PDF
curl -X GET http://localhost:8000/api/results/$RESULT_ID/export \
  -H "Authorization: Bearer $TOKEN" \
  --output result.pdf
```

---

## 📝 Features Overview

### ✅ Implemented (All 10 Steps)

1. **Database Schema** - 11 tables with proper relationships
2. **Authentication** - JWT-based with roles (teacher/student/admin)
3. **Exam APIs** - Create, edit, publish exams
4. **PDF Upload** - OCR processing (async)
5. **AI Questions** - Generate from PDF or manual entry
6. **Exam Sessions** - Timed sessions with device binding
7. **Auto-Grading** - AI-powered grading with partial marks
8. **Results** - Publishing, feedback, PDF export
9. **Offline Sync** - Validation and conflict resolution
10. **Security** - Audit logs, rate limiting, anti-cheating

### 🔧 Configuration

Edit `.env` for:
- Database settings
- Queue driver (`database` for dev, `redis` for production)
- AI provider (openai, gemini, local)
- File storage (local, s3, etc)

### 📦 Queue Jobs

Two async jobs implemented:

1. **ProcessPdfOcr** - Extracts text from PDFs
2. **GradeExamSession** - Grades essays/short answers

Run with: `php artisan queue:work database`

---

## 🎯 Next Steps

1. **Build Desktop Client** - Electron app with React
2. **Configure AI Provider** - Set OpenAI/Gemini API keys
3. **Deploy to Production** - VPS/Docker setup
4. **Database Backups** - Configure automated backups
5. **Monitoring** - Setup error tracking (Sentry)
6. **Load Testing** - Test with multiple concurrent users

---

## 🐛 Troubleshooting

### Queue Jobs Not Running
```bash
# Make sure queue worker is running
php artisan queue:work database

# Check for failed jobs
php artisan queue:failed
```

### PDF OCR Not Working
```bash
# Install required tools
sudo apt-get install poppler-utils

# Test manually
pdftotext /path/to/pdf.pdf output.txt
```

### AI Integration Issues
```bash
# Check .env has AI_PROVIDER and API keys
# Test with tinker:
php artisan tinker
> config('ai.provider')
> config('ai.openai.api_key')
```

### Database Issues
```bash
# Reset database
php artisan migrate:fresh --seed

# Check migrations
php artisan migrate:status
```

---

## 📊 Database Queries (Tinker)

```php
php artisan tinker

# View teachers
User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->get()

# View students
User::whereHas('roles', fn($q) => $q->where('name', 'student'))->get()

# View all exams
Exam::with('teacher', 'questions')->get()

# View results for an exam
Result::where('exam_id', 1)->with('student')->get()

# View audit logs
AuditLog::where('action', 'like', '%login%')->latest()->limit(10)->get()

# View failed jobs
DB::table('failed_jobs')->get()
```

---

## 🚀 Production Deployment

1. **Server Setup**
   ```bash
   # Install PHP 8.4, MySQL, Redis
   sudo apt update && sudo apt upgrade -y
   ```

2. **Clone Repository**
   ```bash
   git clone <repo> /var/www/skillflow
   cd /var/www/skillflow
   ```

3. **Install & Configure**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   ```

4. **Setup Supervisor** (for queue worker)
   ```bash
   # See Laravel documentation for supervisor setup
   ```

5. **Setup Nginx**
   ```nginx
   server {
       server_name api.skillflow.com;
       root /var/www/skillflow/public;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
   }
   ```

---

**Congratulations! Your SKILLFLOW backend is ready! 🎉**

For detailed documentation, see `IMPLEMENTATION.md`
