<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skillflow API Documentation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .header h1 i {
            margin-right: 15px;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        .header .version {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            display: inline-block;
            color: #555;
        }

        .api-section {
            background: white;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-header i {
            font-size: 1.2em;
        }

        .section-header.auth { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .section-header.exam { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .section-header.pdf { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .section-header.session { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .section-header.result { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        .section-header.sync { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .section-header.admin { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }

        .endpoint {
            border-bottom: 1px solid #eee;
            padding: 25px 30px;
        }

        .endpoint:last-child {
            border-bottom: none;
        }

        .endpoint-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .method-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85em;
            text-transform: uppercase;
            color: white;
            min-width: 60px;
            text-align: center;
        }

        .method-badge.get { background: #4facfe; }
        .method-badge.post { background: #43e97b; }
        .method-badge.put { background: #f093fb; }
        .method-badge.delete { background: #f5576c; }
        .method-badge.head { background: #999; }

        .endpoint-path {
            font-family: 'Courier New', monospace;
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            background: #f5f5f5;
            padding: 10px 15px;
            border-radius: 5px;
            flex-grow: 1;
            min-width: 300px;
            word-break: break-all;
        }

        .endpoint-description {
            color: #666;
            margin: 10px 0;
            font-size: 0.95em;
        }

        .endpoint-details {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .detail-block {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .detail-label {
            font-weight: 600;
            color: #667eea;
            font-size: 0.9em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .detail-content {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #333;
            word-break: break-all;
        }

        .auth-required {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            background: #fff3cd;
            color: #856404;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .admin-only {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .teacher-only {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            background: #cce5ff;
            color: #004085;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .student-only {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            background: #d4edda;
            color: #155724;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }

        th {
            background: #f0f0f0;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .highlight {
            background: #fff9e6;
            padding: 15px;
            border-left: 4px solid #ffc107;
            border-radius: 5px;
            margin: 20px 0;
        }

        .highlight h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #856404;
        }

        .highlight ul li {
            margin: 8px 0;
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .endpoint-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .endpoint-path {
                width: 100%;
                min-width: auto;
            }

            .header h1 {
                font-size: 1.8em;
            }

            .section-header {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> SKILLFLOW API Documentation</h1>
            <p>Complete API reference for AI-Powered School Examination System</p>
            <div class="version">
                <strong>Base URL:</strong> /api/
                <br>
                <strong>Version:</strong> 1.0
                <br>
                <strong>Framework:</strong> Laravel 12 + Sanctum JWT
            </div>
        </div>

        <!-- Authentication Section -->
        <div class="api-section">
            <div class="section-header auth"><i class="fas fa-lock"></i> Authentication Endpoints</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/register</span>
                </div>
                <div class="endpoint-description">Register a new user (teacher or student)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "teacher" // or "student"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "User registered successfully",
  "user": {...},
  "token": "eyJ0eXAiOiJKV1Q..."
}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/login</span>
                </div>
                <div class="endpoint-description">Login user and receive JWT token</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "john@example.com",
  "password": "password123"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">10 attempts per 60 seconds</div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-globe"></i> Unauthenticated</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/logout</span>
                </div>
                <div class="endpoint-description">Logout user and revoke token</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Headers Required</div>
                        <div class="detail-content">Authorization: Bearer {token}</div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/refresh</span>
                </div>
                <div class="endpoint-description">Refresh JWT token</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Headers Required</div>
                        <div class="detail-content">Authorization: Bearer {token}</div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/auth/me</span>
                </div>
                <div class="endpoint-description">Get current authenticated user details with roles</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">Current user object with roles array</div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/send-email-otp</span>
                </div>
                <div class="endpoint-description">Send OTP code to email for email verification</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "user@example.com"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "OTP sent to your email",
  "email": "user@example.com"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Notes</div>
                        <div class="detail-content">
                            <ul>
                                <li>OTP expires in 15 minutes</li>
                                <li>6-digit code sent via Gmail</li>
                                <li>User must not be already verified</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-globe"></i> Unauthenticated</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/verify-email-otp</span>
                </div>
                <div class="endpoint-description">Verify email with OTP code and set email_verified_at</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "user@example.com",
  "otp_code": "123456"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "Email verified successfully",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "email_verified_at": "2026-01-03T15:25:40.000000Z",
    ...
  }
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Error Responses</div>
                        <div class="detail-content">
                            <ul>
                                <li>Invalid OTP code</li>
                                <li>OTP expired (15 min timeout)</li>
                                <li>Max attempts exceeded (5 attempts)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-globe"></i> Unauthenticated</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/send-password-reset-otp</span>
                </div>
                <div class="endpoint-description">Send OTP code to email for password reset</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "user@example.com"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "Password reset OTP sent to your email",
  "email": "user@example.com"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Notes</div>
                        <div class="detail-content">
                            <ul>
                                <li>OTP expires in 10 minutes</li>
                                <li>6-digit code sent via Gmail</li>
                                <li>User email must exist in system</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-globe"></i> Unauthenticated</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/reset-password-otp</span>
                </div>
                <div class="endpoint-description">Reset password using OTP code</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "user@example.com",
  "otp_code": "123456",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "Password reset successfully"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation Rules</div>
                        <div class="detail-content">
                            <ul>
                                <li>New password must be at least 8 characters</li>
                                <li>Passwords must match</li>
                                <li>OTP must be valid and not expired</li>
                                <li>Max 5 failed attempts per OTP</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-globe"></i> Unauthenticated</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/check-otp-validity</span>
                </div>
                <div class="endpoint-description">Check if OTP code is valid and not expired</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "user@example.com",
  "type": "email_verification" // or "password_reset"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response (Valid)</div>
                        <div class="detail-content">
                            <pre>
{
  "is_valid": true,
  "expires_in_seconds": 450
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response (Invalid)</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "No valid OTP found",
  "is_valid": false
}</pre>
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-globe"></i> Unauthenticated</div>
            </div>
        </div>

        <!-- Exam Management Section -->
        <div class="api-section">
            <div class="section-header exam"><i class="fas fa-file-alt"></i> Exam Management</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams</span>
                </div>
                <div class="endpoint-description">Create a new exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "title": "Mathematics Final Exam",
  "description": "Final exam for algebra",
  "duration_minutes": 120,
  "total_marks": 100,
  "passing_marks": 40,
  "ai_prompt": "Generate 50 algebra questions"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams</span>
                </div>
                <div class="endpoint-description">List all exams (teacher's exams or available exams)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">
                            page, per_page, status (draft/published/archived), sort
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/{exam}</span>
                </div>
                <div class="endpoint-description">Get exam details with questions</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">URL Parameter</div>
                        <div class="detail-content">exam - Exam ID</div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge put">PUT</span>
                    <span class="endpoint-path">/api/exams/{exam}</span>
                </div>
                <div class="endpoint-description">Update exam (draft only, teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Editable Fields</div>
                        <div class="detail-content">
                            title, description, duration_minutes, total_marks, passing_marks, ai_prompt
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/publish</span>
                </div>
                <div class="endpoint-description">Publish exam (make available to students)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">Exam owner (teacher) only</div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Requirements</div>
                        <div class="detail-content">Exam must be in draft status</div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge delete">DELETE</span>
                    <span class="endpoint-path">/api/exams/{exam}</span>
                </div>
                <div class="endpoint-description">Delete exam (draft only, teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Restrictions</div>
                        <div class="detail-content">Cannot delete published exams</div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/{exam}/results</span>
                </div>
                <div class="endpoint-description">Get all results for an exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">page, per_page, status (graded/published/disputed)</div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="api-section">
            <div class="section-header exam"><i class="fas fa-question-circle"></i> Exam Questions</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/questions</span>
                </div>
                <div class="endpoint-description">Add question to exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "type": "mcq", // mcq, short_answer, essay
  "question_text": "What is 2+2?",
  "options": ["3", "4", "5", "6"], // MCQ only
  "correct_answer": "4", // MCQ: index, others: text
  "model_answer": "2+2 equals 4", // Short answer
  "marks": 5,
  "explanation": "Basic arithmetic"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge put">PUT</span>
                    <span class="endpoint-path">/api/exams/{exam}/questions/{question}</span>
                </div>
                <div class="endpoint-description">Update exam question (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Fields</div>
                        <div class="detail-content">Same as create endpoint</div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge delete">DELETE</span>
                    <span class="endpoint-path">/api/exams/{exam}/questions/{question}</span>
                </div>
                <div class="endpoint-description">Delete question from exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Restrictions</div>
                        <div class="detail-content">Cannot delete from published exams</div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>
        </div>

        <!-- PDF Upload & AI Generation -->
        <div class="api-section">
            <div class="section-header pdf"><i class="fas fa-file-pdf"></i> PDF Upload & AI Generation</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/pdfs/upload</span>
                </div>
                <div class="endpoint-description">Upload PDF for OCR and AI processing</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Form Data</div>
                        <div class="detail-content">
                            file: PDF file (max 10MB), type: multipart/form-data
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Processing</div>
                        <div class="detail-content">
                            Async queue job extracts text via pdftotext
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/pdfs</span>
                </div>
                <div class="endpoint-description">List user's uploaded PDFs</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">page, per_page, status (pending/completed/failed)</div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/pdfs/{pdf}</span>
                </div>
                <div class="endpoint-description">Get PDF details with extracted text</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            PDF metadata, OCR status, extracted text (if ready)
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/generate-from-pdf</span>
                </div>
                <div class="endpoint-description">Generate exam questions from uploaded PDF using AI</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "pdf_upload_id": 1,
  "number_of_questions": 20,
  "question_types": ["mcq", "short_answer", "essay"],
  "difficulty": "medium"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">AI Provider</div>
                        <div class="detail-content">
                            OpenAI, Gemini, or Local LLM (Ollama)
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>
        </div>

        <!-- Exam Codes & Entry -->
        <div class="api-section">
            <div class="section-header exam"><i class="fas fa-key"></i> Exam Access Codes</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/generate-code</span>
                </div>
                <div class="endpoint-description">Generate unique exam access code</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "max_attempts": 1,
  "expires_at": "2026-01-10" // Optional
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Generated code: 6-character alphanumeric
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/enter</span>
                </div>
                <div class="endpoint-description">Enter exam using access code</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "code": "ABC123",
  "device_fingerprint": "unique-device-id"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation</div>
                        <div class="detail-content">
                            Code active, not expired, attempts available, not already attempted
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">5 attempts per 60 seconds</div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>
        </div>

        <!-- Exam Sessions -->
        <div class="api-section">
            <div class="section-header session"><i class="fas fa-hourglass-end"></i> Exam Sessions & Answers</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/active</span>
                </div>
                <div class="endpoint-description">Get student's currently active exam session</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Active session with exam details, time remaining, answered questions
                        </div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/session/{session}</span>
                </div>
                <div class="endpoint-description">Get exam session details</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Session status, time remaining, questions, previously answered
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Auto-Expiry</div>
                        <div class="detail-content">
                            Status updated to 'timed_out' if duration exceeded
                        </div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/session/{session}/answer</span>
                </div>
                <div class="endpoint-description">Submit answer to exam question</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "question_id": 5,
  "answer": "Option B", // MCQ: option text
  "time_spent_seconds": 45
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation</div>
                        <div class="detail-content">
                            Session active, not expired, question belongs to exam
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">100 requests per 60 seconds</div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/session/{session}/submit</span>
                </div>
                <div class="endpoint-description">Submit exam (finalize answers)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Processing</div>
                        <div class="detail-content">
                            Dispatches GradeExamSession async job for AI grading
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Result</div>
                        <div class="detail-content">
                            Creates Result entry with status 'pending', queues grading
                        </div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>
        </div>

        <!-- Results & Grading -->
        <div class="api-section">
            <div class="section-header result"><i class="fas fa-chart-bar"></i> Results & Grading</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/results</span>
                </div>
                <div class="endpoint-description">Get student's exam results</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Filters</div>
                        <div class="detail-content">
                            Only shows published results to students
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">page, per_page, status</div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/results/{result}</span>
                </div>
                <div class="endpoint-description">Get result details with marks, feedback, remarks</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">
                            Students see published only, Teachers see all
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Includes</div>
                        <div class="detail-content">
                            Question-wise scores, AI feedback, teacher remarks
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/results/{result}/publish</span>
                </div>
                <div class="endpoint-description">Publish result to student (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Requirements</div>
                        <div class="detail-content">
                            Result must be graded status
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Effect</div>
                        <div class="detail-content">
                            Sets published_at timestamp, student can now view
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/results/{result}/remarks</span>
                </div>
                <div class="endpoint-description">Add teacher remarks to result</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "remarks": "Good effort, work on algebra concepts"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="teacher-only"><i class="fas fa-chalkboard-user"></i> Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/results/{result}/flag</span>
                </div>
                <div class="endpoint-description">Flag result for review (suspicious activity)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "reason": "Unusual answers pattern",
  "description": "Optional detailed explanation"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">
                            Student (self-flag) or Teacher
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/results/{result}/export</span>
                </div>
                <div class="endpoint-description">Export result as PDF</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">
                            Students: published only, Teachers: all results
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            PDF file with marks, feedback, remarks
                        </div>
                    </div>
                </div>
                <div class="auth-required"><i class="fas fa-key"></i> Bearer Token Required</div>
            </div>
        </div>

        <!-- Offline Sync -->
        <div class="api-section">
            <div class="section-header sync"><i class="fas fa-sync"></i> Offline Synchronization</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/sync</span>
                </div>
                <div class="endpoint-description">Sync offline exam data to server</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "sync_items": [
    {
      "entity_type": "exam_session",
      "entity_id": 5,
      "action": "create",
      "payload": {...}
    },
    {
      "entity_type": "student_answer",
      "entity_id": 12,
      "action": "create",
      "payload": {...}
    }
  ]
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation</div>
                        <div class="detail-content">
                            Code validity, device fingerprint check, time limit validation
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">20 requests per 60 seconds</div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/sync/pending</span>
                </div>
                <div class="endpoint-description">Get pending sync items for offline client</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Array of items with status 'pending'
                        </div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/sync/acknowledge</span>
                </div>
                <div class="endpoint-description">Mark sync item as synced</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "sync_queue_id": 42
}</pre>
                        </div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/sync/status</span>
                </div>
                <div class="endpoint-description">Get sync status summary</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            <pre>
{
  "pending_count": 3,
  "failed_count": 1,
  "last_sync_at": "2026-01-03T10:30:00Z",
  "sync_health": "healthy"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="student-only"><i class="fas fa-user-graduate"></i> Students Only</div>
            </div>
        </div>

        <!-- Admin Audit Logs -->
        <div class="api-section">
            <div class="section-header admin"><i class="fas fa-search"></i> Audit Logs (Admin Only)</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/audit-logs</span>
                </div>
                <div class="endpoint-description">List all system audit logs</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">
                            page, per_page, user_id, action, status (success/failed), date_from, date_to
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Tracked Actions</div>
                        <div class="detail-content">
                            create, read, update, delete, publish, submit, grade, enter, answer, sync
                        </div>
                    </div>
                </div>
                <div class="admin-only"><i class="fas fa-user-shield"></i> Admin Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/audit-logs/{log}</span>
                </div>
                <div class="endpoint-description">Get detailed audit log entry</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Includes</div>
                        <div class="detail-content">
                            User agent, IP address, old/new values, meta data, error messages
                        </div>
                    </div>
                </div>
                <div class="admin-only"><i class="fas fa-user-shield"></i> Admin Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/users/{user}/activity</span>
                </div>
                <div class="endpoint-description">Get user activity summary with login history</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Last login, login count, actions performed, suspicious activities
                        </div>
                    </div>
                </div>
                <div class="admin-only"><i class="fas fa-user-shield"></i> Admin Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/audit-logs/export</span>
                </div>
                <div class="endpoint-description">Export audit logs to CSV or JSON</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "format": "csv", // or "json"
  "date_from": "2026-01-01",
  "date_to": "2026-01-10",
  "filters": {
    "action": "submit",
    "status": "success"
  }
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Downloadable file with audit log data
                        </div>
                    </div>
                </div>
                <div class="admin-only"><i class="fas fa-user-shield"></i> Admin Only</div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="highlight">
            <h3><i class="fas fa-exclamation-triangle"></i> Important Information</h3>
            <ul style="margin-left: 20px; margin-top: 15px;">
                <li><strong>Authentication:</strong> All endpoints (except register/login) require Bearer token in Authorization header</li>
                <li><strong>Rate Limiting:</strong> Different limits apply per endpoint (login: 10/min, exam entry: 5/min, general: 100/min)</li>
                <li><strong>Device Fingerprinting:</strong> Exam entry and sync use device fingerprinting for offline session binding</li>
                <li><strong>Async Processing:</strong> PDF OCR and exam grading happen asynchronously (check status via GET endpoints)</li>
                <li><strong>AI Grading:</strong> MCQ instant, short answer/essay evaluated by AI with fallback partial credit</li>
                <li><strong>Offline Sync:</strong> Desktop client can cache answers and sync when online</li>
                <li><strong>Audit Trail:</strong> All API actions logged for security and compliance</li>
            </ul>
        </div>

        <!-- Pagination & Filters -->
        <div class="api-section">
            <div class="section-header exam"><i class="fas fa-list"></i> Common Query Parameters</div>

            <div style="padding: 30px;">
                <h3>Pagination</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Default</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>page</td>
                                <td>integer</td>
                                <td>1</td>
                                <td>Page number</td>
                            </tr>
                            <tr>
                                <td>per_page</td>
                                <td>integer</td>
                                <td>15</td>
                                <td>Items per page (max 100)</td>
                            </tr>
                            <tr>
                                <td>sort</td>
                                <td>string</td>
                                <td>-created_at</td>
                                <td>Sort field (prefix with - for descending)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin-top: 30px;">Response Format</h3>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 15px; font-family: 'Courier New', monospace; font-size: 0.9em;">
                    <pre>
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 127,
    "last_page": 9
  },
  "links": {
    "first": "...",
    "last": "...",
    "next": "..."
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- Error Codes -->
        <div class="api-section">
            <div class="section-header admin"><i class="fas fa-times-circle"></i> HTTP Status Codes & Errors</div>

            <div style="padding: 30px;">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Meaning</th>
                                <th>Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>200</strong></td>
                                <td>OK</td>
                                <td>Request successful, data returned</td>
                            </tr>
                            <tr>
                                <td><strong>201</strong></td>
                                <td>Created</td>
                                <td>Resource created successfully</td>
                            </tr>
                            <tr>
                                <td><strong>400</strong></td>
                                <td>Bad Request</td>
                                <td>Invalid input, validation errors returned</td>
                            </tr>
                            <tr>
                                <td><strong>401</strong></td>
                                <td>Unauthorized</td>
                                <td>Missing/invalid token</td>
                            </tr>
                            <tr>
                                <td><strong>403</strong></td>
                                <td>Forbidden</td>
                                <td>Insufficient permissions/role</td>
                            </tr>
                            <tr>
                                <td><strong>404</strong></td>
                                <td>Not Found</td>
                                <td>Resource not found</td>
                            </tr>
                            <tr>
                                <td><strong>429</strong></td>
                                <td>Too Many Requests</td>
                                <td>Rate limit exceeded, includes retry_after header</td>
                            </tr>
                            <tr>
                                <td><strong>500</strong></td>
                                <td>Server Error</td>
                                <td>Internal server error</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>SKILLFLOW API Documentation - v1.0</p>
            <p>Last Updated: January 3, 2026</p>
            <p>For support or questions, contact the development team</p>
        </div>
    </div>
</body>
</html>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .header p {
            color: #666;
            font-size: 1.1em;
        }

        .header .version {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            display: inline-block;
            color: #555;
        }

        .api-section {
            background: white;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            font-size: 1.5em;
            font-weight: 600;
        }

        .section-header.auth { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .section-header.exam { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .section-header.pdf { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .section-header.session { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .section-header.result { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        .section-header.sync { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .section-header.admin { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }

        .endpoint {
            border-bottom: 1px solid #eee;
            padding: 25px 30px;
        }

        .endpoint:last-child {
            border-bottom: none;
        }

        .endpoint-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .method-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.85em;
            text-transform: uppercase;
            color: white;
            min-width: 60px;
            text-align: center;
        }

        .method-badge.get { background: #4facfe; }
        .method-badge.post { background: #43e97b; }
        .method-badge.put { background: #f093fb; }
        .method-badge.delete { background: #f5576c; }
        .method-badge.head { background: #999; }

        .endpoint-path {
            font-family: 'Courier New', monospace;
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            background: #f5f5f5;
            padding: 10px 15px;
            border-radius: 5px;
            flex-grow: 1;
            min-width: 300px;
            word-break: break-all;
        }

        .endpoint-description {
            color: #666;
            margin: 10px 0;
            font-size: 0.95em;
        }

        .endpoint-details {
            margin-top: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .detail-block {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .detail-label {
            font-weight: 600;
            color: #667eea;
            font-size: 0.9em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .detail-content {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #333;
            word-break: break-all;
        }

        .auth-required {
            display: inline-block;
            padding: 5px 10px;
            background: #fff3cd;
            color: #856404;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .admin-only {
            display: inline-block;
            padding: 5px 10px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .teacher-only {
            display: inline-block;
            padding: 5px 10px;
            background: #cce5ff;
            color: #004085;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .student-only {
            display: inline-block;
            padding: 5px 10px;
            background: #d4edda;
            color: #155724;
            border-radius: 3px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }

        th {
            background: #f0f0f0;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .highlight {
            background: #fff9e6;
            padding: 15px;
            border-left: 4px solid #ffc107;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .endpoint-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .endpoint-path {
                width: 100%;
                min-width: auto;
            }

            .header h1 {
                font-size: 1.8em;
            }

            .section-header {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎓 SKILLFLOW API Documentation</h1>
            <p>Complete API reference for AI-Powered School Examination System</p>
            <div class="version">
                <strong>Base URL:</strong> /api/
                <br>
                <strong>Version:</strong> 1.0
                <br>
                <strong>Framework:</strong> Laravel 12 + Sanctum JWT
            </div>
        </div>

        <!-- Authentication Section -->
        <div class="api-section">
            <div class="section-header auth">🔐 Authentication Endpoints</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/register</span>
                </div>
                <div class="endpoint-description">Register a new user (teacher or student)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "teacher" // or "student"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Response</div>
                        <div class="detail-content">
                            <pre>
{
  "message": "User registered successfully",
  "user": {...},
  "token": "eyJ0eXAiOiJKV1Q..."
}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/login</span>
                </div>
                <div class="endpoint-description">Login user and receive JWT token</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "email": "john@example.com",
  "password": "password123"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">10 attempts per 60 seconds</div>
                    </div>
                </div>
                <div class="auth-required">Unauthenticated</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/logout</span>
                </div>
                <div class="endpoint-description">Logout user and revoke token</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Headers Required</div>
                        <div class="detail-content">Authorization: Bearer {token}</div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/auth/refresh</span>
                </div>
                <div class="endpoint-description">Refresh JWT token</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Headers Required</div>
                        <div class="detail-content">Authorization: Bearer {token}</div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/auth/me</span>
                </div>
                <div class="endpoint-description">Get current authenticated user details with roles</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">Current user object with roles array</div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>
        </div>

        <!-- Exam Management Section -->
        <div class="api-section">
            <div class="section-header exam">📝 Exam Management</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams</span>
                </div>
                <div class="endpoint-description">Create a new exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "title": "Mathematics Final Exam",
  "description": "Final exam for algebra",
  "duration_minutes": 120,
  "total_marks": 100,
  "passing_marks": 40,
  "ai_prompt": "Generate 50 algebra questions"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams</span>
                </div>
                <div class="endpoint-description">List all exams (teacher's exams or available exams)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">
                            page, per_page, status (draft/published/archived), sort
                        </div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/{exam}</span>
                </div>
                <div class="endpoint-description">Get exam details with questions</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">URL Parameter</div>
                        <div class="detail-content">exam - Exam ID</div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge put">PUT</span>
                    <span class="endpoint-path">/api/exams/{exam}</span>
                </div>
                <div class="endpoint-description">Update exam (draft only, teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Editable Fields</div>
                        <div class="detail-content">
                            title, description, duration_minutes, total_marks, passing_marks, ai_prompt
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/publish</span>
                </div>
                <div class="endpoint-description">Publish exam (make available to students)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">Exam owner (teacher) only</div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Requirements</div>
                        <div class="detail-content">Exam must be in draft status</div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge delete">DELETE</span>
                    <span class="endpoint-path">/api/exams/{exam}</span>
                </div>
                <div class="endpoint-description">Delete exam (draft only, teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Restrictions</div>
                        <div class="detail-content">Cannot delete published exams</div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/{exam}/results</span>
                </div>
                <div class="endpoint-description">Get all results for an exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">page, per_page, status (graded/published/disputed)</div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="api-section">
            <div class="section-header exam">❓ Exam Questions</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/questions</span>
                </div>
                <div class="endpoint-description">Add question to exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "type": "mcq", // mcq, short_answer, essay
  "question_text": "What is 2+2?",
  "options": ["3", "4", "5", "6"], // MCQ only
  "correct_answer": "4", // MCQ: index, others: text
  "model_answer": "2+2 equals 4", // Short answer
  "marks": 5,
  "explanation": "Basic arithmetic"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge put">PUT</span>
                    <span class="endpoint-path">/api/exams/{exam}/questions/{question}</span>
                </div>
                <div class="endpoint-description">Update exam question (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Fields</div>
                        <div class="detail-content">Same as create endpoint</div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge delete">DELETE</span>
                    <span class="endpoint-path">/api/exams/{exam}/questions/{question}</span>
                </div>
                <div class="endpoint-description">Delete question from exam (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Restrictions</div>
                        <div class="detail-content">Cannot delete from published exams</div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>
        </div>

        <!-- PDF Upload & AI Generation -->
        <div class="api-section">
            <div class="section-header pdf">📄 PDF Upload & AI Generation</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/pdfs/upload</span>
                </div>
                <div class="endpoint-description">Upload PDF for OCR and AI processing</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Form Data</div>
                        <div class="detail-content">
                            file: PDF file (max 10MB), type: multipart/form-data
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Processing</div>
                        <div class="detail-content">
                            Async queue job extracts text via pdftotext
                        </div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/pdfs</span>
                </div>
                <div class="endpoint-description">List user's uploaded PDFs</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">page, per_page, status (pending/completed/failed)</div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/pdfs/{pdf}</span>
                </div>
                <div class="endpoint-description">Get PDF details with extracted text</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            PDF metadata, OCR status, extracted text (if ready)
                        </div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/generate-from-pdf</span>
                </div>
                <div class="endpoint-description">Generate exam questions from uploaded PDF using AI</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "pdf_upload_id": 1,
  "number_of_questions": 20,
  "question_types": ["mcq", "short_answer", "essay"],
  "difficulty": "medium"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">AI Provider</div>
                        <div class="detail-content">
                            OpenAI, Gemini, or Local LLM (Ollama)
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>
        </div>

        <!-- Exam Codes & Entry -->
        <div class="api-section">
            <div class="section-header exam">🔑 Exam Access Codes</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/{exam}/generate-code</span>
                </div>
                <div class="endpoint-description">Generate unique exam access code</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "max_attempts": 1,
  "expires_at": "2026-01-10" // Optional
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Generated code: 6-character alphanumeric
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/enter</span>
                </div>
                <div class="endpoint-description">Enter exam using access code</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "code": "ABC123",
  "device_fingerprint": "unique-device-id"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation</div>
                        <div class="detail-content">
                            Code active, not expired, attempts available, not already attempted
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">5 attempts per 60 seconds</div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>
        </div>

        <!-- Exam Sessions -->
        <div class="api-section">
            <div class="section-header session">⏱️ Exam Sessions & Answers</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/active</span>
                </div>
                <div class="endpoint-description">Get student's currently active exam session</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Active session with exam details, time remaining, answered questions
                        </div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/exams/session/{session}</span>
                </div>
                <div class="endpoint-description">Get exam session details</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Session status, time remaining, questions, previously answered
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Auto-Expiry</div>
                        <div class="detail-content">
                            Status updated to 'timed_out' if duration exceeded
                        </div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/session/{session}/answer</span>
                </div>
                <div class="endpoint-description">Submit answer to exam question</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "question_id": 5,
  "answer": "Option B", // MCQ: option text
  "time_spent_seconds": 45
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation</div>
                        <div class="detail-content">
                            Session active, not expired, question belongs to exam
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">100 requests per 60 seconds</div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/exams/session/{session}/submit</span>
                </div>
                <div class="endpoint-description">Submit exam (finalize answers)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Processing</div>
                        <div class="detail-content">
                            Dispatches GradeExamSession async job for AI grading
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Result</div>
                        <div class="detail-content">
                            Creates Result entry with status 'pending', queues grading
                        </div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>
        </div>

        <!-- Results & Grading -->
        <div class="api-section">
            <div class="section-header result">📊 Results & Grading</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/results</span>
                </div>
                <div class="endpoint-description">Get student's exam results</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Filters</div>
                        <div class="detail-content">
                            Only shows published results to students
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">page, per_page, status</div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/results/{result}</span>
                </div>
                <div class="endpoint-description">Get result details with marks, feedback, remarks</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">
                            Students see published only, Teachers see all
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Includes</div>
                        <div class="detail-content">
                            Question-wise scores, AI feedback, teacher remarks
                        </div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/results/{result}/publish</span>
                </div>
                <div class="endpoint-description">Publish result to student (teacher only)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Requirements</div>
                        <div class="detail-content">
                            Result must be graded status
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Effect</div>
                        <div class="detail-content">
                            Sets published_at timestamp, student can now view
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/results/{result}/remarks</span>
                </div>
                <div class="endpoint-description">Add teacher remarks to result</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "remarks": "Good effort, work on algebra concepts"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="teacher-only">Teachers Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/results/{result}/flag</span>
                </div>
                <div class="endpoint-description">Flag result for review (suspicious activity)</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "reason": "Unusual answers pattern",
  "description": "Optional detailed explanation"
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">
                            Student (self-flag) or Teacher
                        </div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/results/{result}/export</span>
                </div>
                <div class="endpoint-description">Export result as PDF</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Authorization</div>
                        <div class="detail-content">
                            Students: published only, Teachers: all results
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            PDF file with marks, feedback, remarks
                        </div>
                    </div>
                </div>
                <div class="auth-required">Bearer Token Required</div>
            </div>
        </div>

        <!-- Offline Sync -->
        <div class="api-section">
            <div class="section-header sync">🔄 Offline Synchronization</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/sync</span>
                </div>
                <div class="endpoint-description">Sync offline exam data to server</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "sync_items": [
    {
      "entity_type": "exam_session",
      "entity_id": 5,
      "action": "create",
      "payload": {...}
    },
    {
      "entity_type": "student_answer",
      "entity_id": 12,
      "action": "create",
      "payload": {...}
    }
  ]
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Validation</div>
                        <div class="detail-content">
                            Code validity, device fingerprint check, time limit validation
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Rate Limit</div>
                        <div class="detail-content">20 requests per 60 seconds</div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/sync/pending</span>
                </div>
                <div class="endpoint-description">Get pending sync items for offline client</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Array of items with status 'pending'
                        </div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/sync/acknowledge</span>
                </div>
                <div class="endpoint-description">Mark sync item as synced</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "sync_queue_id": 42
}</pre>
                        </div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/sync/status</span>
                </div>
                <div class="endpoint-description">Get sync status summary</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            <pre>
{
  "pending_count": 3,
  "failed_count": 1,
  "last_sync_at": "2026-01-03T10:30:00Z",
  "sync_health": "healthy"
}</pre>
                        </div>
                    </div>
                </div>
                <div class="student-only">Students Only</div>
            </div>
        </div>

        <!-- Admin Audit Logs -->
        <div class="api-section">
            <div class="section-header admin">🔍 Audit Logs (Admin Only)</div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/audit-logs</span>
                </div>
                <div class="endpoint-description">List all system audit logs</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Query Parameters</div>
                        <div class="detail-content">
                            page, per_page, user_id, action, status (success/failed), date_from, date_to
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Tracked Actions</div>
                        <div class="detail-content">
                            create, read, update, delete, publish, submit, grade, enter, answer, sync
                        </div>
                    </div>
                </div>
                <div class="admin-only">Admin Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/audit-logs/{log}</span>
                </div>
                <div class="endpoint-description">Get detailed audit log entry</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Includes</div>
                        <div class="detail-content">
                            User agent, IP address, old/new values, meta data, error messages
                        </div>
                    </div>
                </div>
                <div class="admin-only">Admin Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/users/{user}/activity</span>
                </div>
                <div class="endpoint-description">Get user activity summary with login history</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Last login, login count, actions performed, suspicious activities
                        </div>
                    </div>
                </div>
                <div class="admin-only">Admin Only</div>
            </div>

            <div class="endpoint">
                <div class="endpoint-title">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/audit-logs/export</span>
                </div>
                <div class="endpoint-description">Export audit logs to CSV or JSON</div>
                <div class="endpoint-details">
                    <div class="detail-block">
                        <div class="detail-label">Required Body</div>
                        <div class="detail-content">
                            <pre>
{
  "format": "csv", // or "json"
  "date_from": "2026-01-01",
  "date_to": "2026-01-10",
  "filters": {
    "action": "submit",
    "status": "success"
  }
}</pre>
                        </div>
                    </div>
                    <div class="detail-block">
                        <div class="detail-label">Returns</div>
                        <div class="detail-content">
                            Downloadable file with audit log data
                        </div>
                    </div>
                </div>
                <div class="admin-only">Admin Only</div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="highlight">
            <h3>⚠️ Important Information</h3>
            <ul style="margin-left: 20px; margin-top: 15px;">
                <li><strong>Authentication:</strong> All endpoints (except register/login) require Bearer token in Authorization header</li>
                <li><strong>Rate Limiting:</strong> Different limits apply per endpoint (login: 10/min, exam entry: 5/min, general: 100/min)</li>
                <li><strong>Device Fingerprinting:</strong> Exam entry and sync use device fingerprinting for offline session binding</li>
                <li><strong>Async Processing:</strong> PDF OCR and exam grading happen asynchronously (check status via GET endpoints)</li>
                <li><strong>AI Grading:</strong> MCQ instant, short answer/essay evaluated by AI with fallback partial credit</li>
                <li><strong>Offline Sync:</strong> Desktop client can cache answers and sync when online</li>
                <li><strong>Audit Trail:</strong> All API actions logged for security and compliance</li>
            </ul>
        </div>

        <!-- Pagination & Filters -->
        <div class="api-section">
            <div class="section-header exam">📋 Common Query Parameters</div>

            <div style="padding: 30px;">
                <h3>Pagination</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Default</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>page</td>
                                <td>integer</td>
                                <td>1</td>
                                <td>Page number</td>
                            </tr>
                            <tr>
                                <td>per_page</td>
                                <td>integer</td>
                                <td>15</td>
                                <td>Items per page (max 100)</td>
                            </tr>
                            <tr>
                                <td>sort</td>
                                <td>string</td>
                                <td>-created_at</td>
                                <td>Sort field (prefix with - for descending)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin-top: 30px;">Response Format</h3>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 15px; font-family: 'Courier New', monospace; font-size: 0.9em;">
                    <pre>
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 127,
    "last_page": 9
  },
  "links": {
    "first": "...",
    "last": "...",
    "next": "..."
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- Error Codes -->
        <div class="api-section">
            <div class="section-header admin">❌ HTTP Status Codes & Errors</div>

            <div style="padding: 30px;">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Meaning</th>
                                <th>Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>200</strong></td>
                                <td>OK</td>
                                <td>Request successful, data returned</td>
                            </tr>
                            <tr>
                                <td><strong>201</strong></td>
                                <td>Created</td>
                                <td>Resource created successfully</td>
                            </tr>
                            <tr>
                                <td><strong>400</strong></td>
                                <td>Bad Request</td>
                                <td>Invalid input, validation errors returned</td>
                            </tr>
                            <tr>
                                <td><strong>401</strong></td>
                                <td>Unauthorized</td>
                                <td>Missing/invalid token</td>
                            </tr>
                            <tr>
                                <td><strong>403</strong></td>
                                <td>Forbidden</td>
                                <td>Insufficient permissions/role</td>
                            </tr>
                            <tr>
                                <td><strong>404</strong></td>
                                <td>Not Found</td>
                                <td>Resource not found</td>
                            </tr>
                            <tr>
                                <td><strong>429</strong></td>
                                <td>Too Many Requests</td>
                                <td>Rate limit exceeded, includes retry_after header</td>
                            </tr>
                            <tr>
                                <td><strong>500</strong></td>
                                <td>Server Error</td>
                                <td>Internal server error</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>SKILLFLOW API Documentation - v1.0</p>
            <p>Last Updated: January 3, 2026</p>
            <p>For support or questions, contact the development team</p>
        </div>
    </div>
</body>
</html>