<?php
/* Admin Panel CSS - Play Console Compliant */
?>
* {
margin: 0;
padding: 0;
box-sizing: border-box;
}

body {
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
background: #f5f5f5;
}

/* Header */
.header {
background: linear-gradient(135deg, #1a2a5e 0%, #2d1b69 100%);
color: white;
padding: 20px 30px;
display: flex;
justify-content: space-between;
align-items: center;
box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header h1 {
font-size: 24px;
}

.user-info {
display: flex;
align-items: center;
gap: 20px;
}

.user-info span {
font-size: 14px;
opacity: 0.9;
}

.btn-logout {
background: rgba(255,255,255,0.2);
color: white;
border: none;
padding: 8px 20px;
border-radius: 6px;
cursor: pointer;
font-size: 14px;
text-decoration: none;
transition: background 0.3s;
}

.btn-logout:hover {
background: rgba(255,255,255,0.3);
}

/* Navigation */
.nav {
background: white;
padding: 0 30px;
box-shadow:0 2px 5px rgba(0,0,0,0.05);
display: flex;
gap: 5px;
}

.nav a {
padding: 15px 20px;
color: #666;
text-decoration: none;
border-bottom: 3px solid transparent;
transition: all 0.3s;
}

.nav a:hover, .nav a.active {
color: #1a2a5e;
border-bottom-color: #DAA520;
}

/* Container */
.container {
max-width: 1200px;
margin: 30px auto;
padding: 0 20px;
}

/* Page Header */
.page-header {
display: flex;
justify-content: space-between;
align-items: center;
margin-bottom: 30px;
}

.page-header h1 {
color: #1a2a5e;
}

/* Cards */
.card {
background: white;
border-radius: 10px;
padding: 25px;
box-shadow: 0 2px 10px rgba(0,0,0,0.05);
margin-bottom: 20px;
}

/* Buttons */
.btn {
padding: 10px 20px;
border: none;
border-radius: 6px;
cursor: pointer;
font-size: 14px;
font-weight: 500;
transition: all 0.3s;
text-decoration: none;
display: inline-block;
}

.btn-primary {
background: linear-gradient(135deg, #DAA520, #B8860B);
color: #000;
}

.btn-primary:hover {
transform: translateY(-2px);
box-shadow: 0 4px 15px rgba(218,165,32,0.4);
}

.btn-danger {
background: #ef4444;
color: white;
}

.btn-danger:hover {
background: #dc2626;
}

.btn-sm {
padding: 6px 12px;
font-size: 12px;
}

/* Alerts */
.alert {
padding: 15px 20px;
border-radius: 8px;
margin-bottom: 20px;
font-size: 14px;
}

.alert-success {
background: #d1fae5;
border: 1px solid #6ee7b7;
color: #065f46;
}

.alert-error {
background: #fee2e2;
border: 1px solid #fca5a5;
color: #991b1b;
}

/* Tables */
.data-table {
width: 100%;
border-collapse: collapse;
}

.data-table thead {
background: #f9fafb;
border-bottom: 2px solid #e5e7eb;
}

.data-table th {
padding: 12px;
text-align: left;
font-weight: 600;
color: #374151;
font-size: 13px;
}

.data-table td {
padding: 12px;
border-bottom: 1px solid #f3f4f6;
font-size: 14px;
color: #4b5563;
}

.data-table tr:hover {
background: #f9fafb;
}

/* Forms */
.form-group {
margin-bottom: 20px;
}

.form-group label {
display: block;
margin-bottom: 8px;
font-weight: 500;
color: #374151;
font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="number"],
.form-group input[type="date"],
.form-group input[type="color"],
.form-group select,
.form-group textarea {
width: 100%;
padding: 10px 12px;
border: 1px solid #d1d5db;
border-radius: 6px;
font-size: 14px;
transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
outline: none;
border-color: #DAA520;
}

.form-row {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
gap: 20px;
}

/* Modal */
.modal {
display: none;
position: fixed;
z-index: 1000;
left: 0;
top: 0;
width: 100%;
height: 100%;
background: rgba(0,0,0,0.5);
overflow: auto;
}

.modal-content {
background: white;
margin: 50px auto;
padding: 30px;
width: 90%;
max-width: 600px;
border-radius: 10px;
position: relative;
}

.close {
position: absolute;
right: 20px;
top: 20px;
font-size: 28px;
font-weight: bold;
color: #9ca3af;
cursor: pointer;
}

.close:hover {
color: #000;
}

.modal-content h2 {
margin-bottom: 25px;
color: #1a2a5e;
}