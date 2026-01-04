<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Exam Result - {{ $student->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .student-info {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
        }
        .student-info p {
            margin: 5px 0;
        }
        .result-summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .result-box {
            display: table-cell;
            text-align: center;
            padding: 20px;
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            margin-right: 10px;
        }
        .result-box:last-child {
            margin-right: 0;
        }
        .result-box .label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .result-box .value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .passed {
            background: #d5f4e6;
            color: #27ae60;
        }
        .failed {
            background: #fadbd8;
            color: #c0392b;
        }
        .status {
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead {
            background: #34495e;
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        .feedback-section {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #e74c3c;
            margin-top: 20px;
        }
        .feedback-section h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #bdc3c7;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Exam Result Report</h1>
        <p>SKILLFLOW - Online Examination System</p>
    </div>

    <div class="student-info">
        <strong>Student Name:</strong> {{ $student->name }} <br>
        <strong>Email:</strong> {{ $student->email }} <br>
        <strong>Exam:</strong> {{ $exam->title }} <br>
        <strong>Date Submitted:</strong> {{ $result->examSession->submitted_at->format('d M Y, H:i A') }}
    </div>

    <div class="status {{ $result->is_passed ? 'passed' : 'failed' }}">
        {{ $result->is_passed ? '✓ PASSED' : '✗ FAILED' }}
    </div>

    <div class="result-summary">
        <div class="result-box">
            <div class="label">Obtained Marks</div>
            <div class="value">{{ $result->obtained_marks }}</div>
        </div>
        <div class="result-box">
            <div class="label">Total Marks</div>
            <div class="value">{{ $result->total_marks }}</div>
        </div>
        <div class="result-box">
            <div class="label">Percentage</div>
            <div class="value">{{ $result->percentage }}%</div>
        </div>
        <div class="result-box">
            <div class="label">Passing Marks</div>
            <div class="value">{{ $exam->passing_marks }}</div>
        </div>
    </div>

    @if($result->question_scores)
    <h3>Question-wise Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Question #</th>
                <th>Type</th>
                <th>Obtained</th>
                <th>Total</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result->question_scores as $qId => $scores)
            <tr>
                <td>{{ $scores['question_number'] }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $scores['type'])) }}</td>
                <td>{{ $scores['obtained'] }}</td>
                <td>{{ $scores['total'] }}</td>
                <td>{{ $scores['percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($result->ai_feedback)
    <div class="feedback-section">
        <h3>Feedback</h3>
        <p><strong>{{ $result->ai_feedback['overall'] ?? '' }}</strong></p>
        <p>Score: {{ $result->ai_feedback['percentage'] ?? 0 }}%</p>
    </div>
    @endif

    @if($result->teacher_remarks)
    <div class="feedback-section">
        <h3>Teacher Remarks</h3>
        <p>{{ $result->teacher_remarks }}</p>
    </div>
    @endif

    <div class="footer">
        <p>This is an official exam result report generated by SKILLFLOW.</p>
        <p>Generated on {{ now()->format('d M Y, H:i A') }}</p>
    </div>
</body>
</html>
