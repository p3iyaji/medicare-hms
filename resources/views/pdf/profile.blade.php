<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Medical Profile - {{ $user['full_name'] }}</title>
    <style>
        /* Tabular CSS for PDF generation */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #1e40af;
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        
        .header .subtitle {
            color: #6b7280;
            font-size: 12px;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #3b82f6;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 13px;
            border-radius: 3px;
        }
        
        .table-container {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .table th {
            background-color: #f8fafc;
            color: #4b5563;
            text-align: left;
            padding: 8px 10px;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #e5e7eb;
        }
        
        .table td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 11px;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .table tr:hover {
            background-color: #f3f4f6;
        }
        
        .label {
            font-weight: 600;
            color: #374151;
            min-width: 150px;
        }
        
        .value {
            color: #111827;
        }
        
        .value.empty {
            color: #9ca3af;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 9px;
            line-height: 1.3;
        }
        
        .medical-alert {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        
        .medical-alert h4 {
            color: #dc2626;
            margin: 0 0 5px 0;
            font-size: 11px;
            font-weight: bold;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo h2 {
            color: #3b82f6;
            margin: 0;
            font-size: 18px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .full-width {
            width: 100%;
        }
        
        .half-width {
            width: 50%;
        }
        
        .status-active {
            color: #059669;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }
        
        .compact-table {
            font-size: 10px;
        }
        
        .compact-table th,
        .compact-table td {
            padding: 5px 8px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h2>MEDICAL PROFILE REPORT</h2>
    </div>
    
    <div class="header">
        <h1>{{ $user['full_name'] }}</h1>
        <div class="subtitle">
            Complete Medical Profile - Exported on {{ $export_date }}
        </div>
    </div>
    
    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">1. Personal Information</div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Basic Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Full Name</td>
                        <td class="value">{{ $user['full_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email Address</td>
                        <td class="value">{{ $user['email'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Phone Number</td>
                        <td class="value">{{ $user['phone'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date of Birth</td>
                        <td class="value">{{ $user['date_of_birth'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Age</td>
                        <td class="value">{{ $user['age'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Gender</td>
                        <td class="value">{{ $user['gender'] ?? 'Not set' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Demographic Information</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Nationality</td>
                        <td class="value">{{ $user['nationality'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">State/Region</td>
                        <td class="value">{{ $user['state'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Ethnic Region</td>
                        <td class="value">{{ $user['ethnic_region'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Religion</td>
                        <td class="value">{{ $user['religion'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Spoken Languages</td>
                        <td class="value">{{ $user['spoken_languages'] ?? 'Not set' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Address & Location</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Residential Address</td>
                        <td class="value">{{ $user['residential_address'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Region</td>
                        <td class="value">{{ $user['region'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">County</td>
                        <td class="value">{{ $user['county'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">District</td>
                        <td class="value">{{ $user['district'] ?? 'Not set' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Professional Information</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Occupation</td>
                        <td class="value">{{ $user['occupation'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Industry</td>
                        <td class="value">{{ $user['industry'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Work Address</td>
                        <td class="value">{{ $user['work_address'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">User Type</td>
                        <td class="value">{{ ucfirst(str_replace('_', ' ', $user['user_type'])) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Medical Information -->
    <div class="section">
        <div class="section-title">2. Medical Information</div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="4">Vital Statistics & Health Data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Blood Type</td>
                        <td class="value">{{ $medical['blood_type'] ?? 'Not set' }}</td>
                        <td class="label">Genotype</td>
                        <td class="value">{{ $medical['genotype'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Height</td>
                        <td class="value">{{ $medical['height'] }}</td>
                        <td class="label">Weight</td>
                        <td class="value">{{ $medical['weight'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Body Mass Index (BMI)</td>
                        <td class="value" colspan="3">{{ $medical['bmi'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Emergency Contact -->
    <div class="section">
        <div class="section-title">3. Emergency Contact Information</div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Emergency Contact Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Contact Name</td>
                        <td class="value">{{ $emergency['contact_name'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Contact Number</td>
                        <td class="value">{{ $emergency['contact_number'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Relationship</td>
                        <td class="value">{{ $emergency['relationship'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Same as User Address</td>
                        <td class="value">{{ $emergency['same_as_user_address'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Emergency Address</td>
                        <td class="value">{{ $emergency['address'] ?? 'Not set' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Insurance Information -->
    <div class="section">
        <div class="section-title">4. Insurance Information</div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Insurance Coverage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Insurance Provider</td>
                        <td class="value">{{ $insurance['provider'] ?? 'Not set' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Policy Number</td>
                        <td class="value">{{ $insurance['policy_number'] ?? 'Not set' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Health Conditions & Allergies -->
    <div class="section">
        <div class="section-title">5. Health Conditions & Allergies</div>
        
        @if($health['allergies'] !== 'None recorded')
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Allergies</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="value">{{ $health['allergies'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        
        @if($health['chronic_conditions'] !== 'None recorded')
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Chronic Conditions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="value">{{ $health['chronic_conditions'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        
        @if($health['allergies'] === 'None recorded' && $health['chronic_conditions'] === 'None recorded')
        <div class="table-container">
            <table class="table">
                <tbody>
                    <tr>
                        <td class="value empty">No allergies or chronic conditions recorded</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>
    
    <!-- Account Information -->
    <div class="section">
        <div class="section-title">6. Account Information</div>
        <div class="table-container">
            <table class="table compact-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Account Status</td>
                        <td class="value">{{ $user['is_verified'] }}</td>
                        <td class="label">Last Login</td>
                        <td class="value">{{ $user['last_login'] ?? 'Never' }}</td>
                    </tr>
                    <tr>
                        <td class="label">User Type</td>
                        <td class="value">{{ ucfirst(str_replace('_', ' ', $user['user_type'])) }}</td>
                        <td class="label">Export Date</td>
                        <td class="value">{{ $export_date }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="footer">
        <table class="table" style="border: none;">
            <tr>
                <td style="border: none; padding: 5px 0; text-align: center;">
                    <strong>CONFIDENTIAL MEDICAL DOCUMENT</strong><br>
                    This document contains sensitive medical information. Please handle with care.<br>
                    Generated by Medical System on {{ $export_date }}<br>
                    © {{ date('Y') }} Medical System. All rights reserved.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>