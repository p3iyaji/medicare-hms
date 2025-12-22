<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quick Medical Profile Form</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            color: #1e40af;
            margin: 0 0 3px 0;
            font-size: 16px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .form-item {
            margin-bottom: 6px;
        }
        
        .form-item label {
            display: block;
            font-weight: bold;
            color: #374151;
            margin-bottom: 3px;
            font-size: 9px;
        }
        
        .field-line {
            border-bottom: 1px solid #d1d5db;
            min-height: 14px;
            padding-bottom: 2px;
        }
        
        .checkbox-row {
            display: flex;
            gap: 10px;
            margin-top: 3px;
            flex-wrap: wrap;
        }
        
        .checkbox {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .checkbox-box {
            width: 8px;
            height: 8px;
            border: 1px solid #9ca3af;
        }
        
        .section {
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        
        .section-title {
            background-color: #f8fafc;
            color: #1e40af;
            padding: 3px 6px;
            font-weight: bold;
            margin: -8px -8px 6px -8px;
            font-size: 9px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .signature-area {
            border-top: 1px dashed #9ca3af;
            padding-top: 8px;
            margin-top: 12px;
        }
        
        .footer {
            font-size: 7px;
            color: #6b7280;
            text-align: center;
            margin-top: 8px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }
        
        .photo-box {
            border: 1px dashed #9ca3af;
            width: 60px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            color: #6b7280;
            text-align: center;
            float: right;
            margin-left: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MEDICAL PROFILE QUICK FORM</h1>
        <div style="font-size: 8px; color: #6b7280;">
            Complete and keep with personal records
        </div>
    </div>
    
    <div class="photo-box">
        📷<br>
        Photo<br>
        3.5x4.5cm
    </div>
    
    <div class="section">
        <div class="section-title">PERSONAL INFORMATION</div>
        <div class="form-grid">
            <div class="form-item">
                <label>Full Name:</label>
                <div class="field-line"></div>
            </div>
            <div class="form-item">
                <label>Date of Birth:</label>
                <div class="field-line"></div>
            </div>
            <div class="form-item">
                <label>Phone:</label>
                <div class="field-line"></div>
            </div>
            <div class="form-item">
                <label>Emergency Contact:</label>
                <div class="field-line"></div>
            </div>
            <div class="form-item">
                <label>Emergency Phone:</label>
                <div class="field-line"></div>
            </div>
            <div class="form-item">
                <label>Relationship:</label>
                <div class="field-line"></div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">MEDICAL INFORMATION</div>
        <div style="margin-bottom: 6px;">
            <label>Blood Type:</label>
            <div class="checkbox-row">
                <div class="checkbox"><div class="checkbox-box"></div> A+</div>
                <div class="checkbox"><div class="checkbox-box"></div> A-</div>
                <div class="checkbox"><div class="checkbox-box"></div> B+</div>
                <div class="checkbox"><div class="checkbox-box"></div> B-</div>
                <div class="checkbox"><div class="checkbox-box"></div> O+</div>
                <div class="checkbox"><div class="checkbox-box"></div> O-</div>
            </div>
        </div>
        
        <div style="margin-bottom: 6px;">
            <label>Allergies:</label>
            <div class="field-line" style="min-height: 20px;"></div>
        </div>
        
        <div>
            <label>Current Medications:</label>
            <div class="field-line" style="min-height: 20px;"></div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">INSURANCE INFORMATION</div>
        <div class="form-grid">
            <div class="form-item">
                <label>Insurance Provider:</label>
                <div class="field-line"></div>
            </div>
            <div class="form-item">
                <label>Policy Number:</label>
                <div class="field-line"></div>
            </div>
        </div>
    </div>
    
    <div class="signature-area">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label>Patient Signature:</label>
                <div class="field-line" style="min-height: 25px; border-bottom: 2px solid #9ca3af;"></div>
                <div style="font-size: 7px; color: #6b7280; text-align: center;">Date: _____/_____/_____</div>
            </div>
            <div>
                <label>Physician/Witness:</label>
                <div class="field-line" style="min-height: 25px; border-bottom: 2px solid #9ca3af;"></div>
                <div style="font-size: 7px; color: #6b7280; text-align: center;">Date: _____/_____/_____</div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <strong>CONFIDENTIAL</strong> • Keep this form updated • Review annually • Generated: {{ $export_date ?? date('F j, Y') }}
    </div>
</body>
</html>