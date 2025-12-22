<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blank Medical Profile Form</title>
    <style>
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
        
        .form-section {
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
        }
        
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .form-table td {
            padding: 8px 10px;
            border-bottom: 1px dashed #e5e7eb;
            vertical-align: top;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            width: 40%;
        }
        
        .form-field {
            width: 60%;
        }
        
        .field-line {
            border-bottom: 1px solid #d1d5db;
            min-height: 20px;
            padding-bottom: 4px;
        }
        
        .checkbox-group {
            margin-top: 8px;
        }
        
        .checkbox-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        
        .checkbox-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #9ca3af;
            margin-right: 5px;
            vertical-align: middle;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 9px;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 40px;
            color: rgba(0, 0, 0, 0.1);
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="watermark">MEDICAL FORM</div>
    
    <div class="header">
        <h1>COMPREHENSIVE MEDICAL PROFILE FORM</h1>
        <div style="color: #6b7280; font-size: 12px;">
            Complete All Sections Accurately - Keep for Personal Records
        </div>
        <div style="color: #ef4444; font-size: 10px; margin-top: 5px;">
            <strong>CONFIDENTIAL MEDICAL INFORMATION</strong>
        </div>
    </div>
    
    <!-- Section 1: Personal Information -->
    <div class="form-section">
        <div class="section-title">SECTION 1: PERSONAL INFORMATION</div>
        <table class="form-table">
            <tr>
                <td class="form-label">Full Name (Last, First, Middle):</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Date of Birth:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Gender:</td>
                <td class="form-field">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> Male
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> Female
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> Other
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Phone Number:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Email Address:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Residential Address:</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 40px;"></div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Section 2: Medical Information -->
    <div class="form-section">
        <div class="section-title">SECTION 2: MEDICAL INFORMATION</div>
        <table class="form-table">
            <tr>
                <td class="form-label">Blood Type:</td>
                <td class="form-field">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> A+
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> A-
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> B+
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> B-
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> O+
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> O-
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> AB+
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> AB-
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> Unknown
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Genotype:</td>
                <td class="form-field">
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> AA
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> AS
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> SS
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> AC
                        </div>
                        <div class="checkbox-item">
                            <span class="checkbox-box"></span> Unknown
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Height (cm):</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Weight (kg):</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Known Allergies:</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 50px;"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Current Medications:</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 50px;"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Chronic Conditions:</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 50px;"></div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Section 3: Emergency Contact -->
    <div class="form-section">
        <div class="section-title">SECTION 3: EMERGENCY CONTACT</div>
        <table class="form-table">
            <tr>
                <td class="form-label">Emergency Contact Name:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Emergency Contact Phone:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Relationship:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Emergency Contact Address:</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 40px;"></div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Section 4: Insurance Information -->
    <div class="form-section">
        <div class="section-title">SECTION 4: INSURANCE INFORMATION</div>
        <table class="form-table">
            <tr>
                <td class="form-label">Insurance Provider:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Policy Number:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Member ID:</td>
                <td class="form-field">
                    <div class="field-line"></div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Section 5: Authorization -->
    <div class="form-section">
        <div class="section-title">SECTION 5: AUTHORIZATION & SIGNATURE</div>
        <table class="form-table">
            <tr>
                <td class="form-label">Patient's Signature:</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 60px; border-bottom: 2px solid #9ca3af;"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Date:</td>
                <td class="form-field">
                    <div class="field-line" style="width: 100px;"></div>
                </td>
            </tr>
            <tr>
                <td class="form-label">Witness Signature (Optional):</td>
                <td class="form-field">
                    <div class="field-line" style="min-height: 40px;"></div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <div style="margin-bottom: 5px;">
            <strong>CONFIDENTIAL MEDICAL RECORD - FORM MF-001</strong>
        </div>
        <div style="font-size: 8px; line-height: 1.2;">
            This document contains protected health information (PHI).<br>
            Store securely and shred when no longer needed.<br>
            Generated: {{ $export_date ?? date('F j, Y') }} • Form ID: {{ $form_id ?? 'MF-' . date('Ymd') . '-001' }}
        </div>
    </div>
</body>
</html>