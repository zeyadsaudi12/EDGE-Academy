<?php
$page_title = "الحساب الشخصي | EDGE Academy";
$body_class = "royal-dashboard";
include 'header.php';
?>

<!-- الخلفية المتحركة -->
<div class="vortex-bg">
    <div class="shape s1"></div><div class="shape s2"></div><div class="shape s3"></div>
</div>

<div class="royal-wrapper container">
    <!-- القائمة الجانبية الموحدة -->
    <?php include 'sidebar.php'; ?>

    <!-- منطقة المحتوى الرئيسية -->
    <main class="royal-content">
        
        <!-- 1. ملف المستخدم والإحصائيات -->
        <div id="tab-user" class="profile-pane active">
            <div class="glass-section-title"><h3><i class="fas fa-id-card"></i> المعلومات الشخصية</h3></div>
            <div class="user-info-display">
                <div class="info-item"><span>الاسم:</span> <b id="u-name">-</b></div>
                <div class="info-item"><span>الهاتف:</span> <b id="u-phone">-</b></div>
                <div class="info-item"><span>البريد:</span> <b id="u-email">-</b></div>
            </div>

            <div class="glass-section-title"><h3><i class="fas fa-chart-pie"></i> إحصائيات التعلم</h3></div>
            <div class="dynamic-stats-row">
                <div class="stat-progress-item">
                    <div class="circular-progress blue">
                        <svg><circle class="bg" cx="50" cy="50" r="45"></circle><circle class="progress" cx="50" cy="50" r="45"></circle></svg>
                        <div class="content"><h2 class="counter" id="v-count" data-target="0">0</h2></div>
                    </div>
                    <p>فيديو تمت مشاهدته</p>
                </div>
                <div class="stat-progress-item">
                    <div class="circular-progress pink">
                        <svg><circle class="bg" cx="50" cy="50" r="45"></circle><circle class="progress" cx="50" cy="50" r="45"></circle></svg>
                        <div class="content"><h2 class="counter" id="e-count" data-target="0">0</h2></div>
                    </div>
                    <p>امتحان مكتمل</p>
                </div>
                <div class="stat-progress-item">
                    <div class="circular-progress gold">
                        <svg><circle class="bg" cx="50" cy="50" r="45"></circle><circle class="progress" cx="50" cy="50" r="45"></circle></svg>
                        <div class="content"><h2 class="counter" id="g-count" data-target="0">0</h2><span>%</span></div>
                    </div>
                    <p>متوسط النتائج</p>
                </div>
            </div>
        </div>

        <!-- 2. المدرسين -->
        <div id="tab-teachers" class="profile-pane">
            <div class="glass-section-title"><h3>المدرسين المتابعين</h3></div>
            <div class="empty-state-royal"><i class="fas fa-users-viewfinder"></i><p>لا تتابع أي مدرس حالياً.</p></div>
        </div>

        <!-- 3. شحن الكود -->
        <div id="tab-recharge" class="profile-pane">
            <div class="glass-section-title"><h3>شحن كود السنتر</h3></div>
            <div class="recharge-container">
                <div class="input-glow-row">
                    <input type="text" id="centerCode" placeholder="أدخل الكود المكون من 12 رقم">
                    <button onclick="checkCenterCode()" class="btn-royal-action">تحقق</button>
                </div>
                <div id="codePreview" class="code-preview-card" style="display: none;">
                    <!-- يظهر هنا الفيديو بعد التحقق -->
                </div>
            </div>
        </div>

        <!-- 4. كورساتي -->
        <div id="tab-my-courses" class="profile-pane">
            <div class="glass-section-title"><h3>مكتبة كورساتي</h3></div>
            <div class="filter-controls">
                <select id="courseSubject"><option>كل المواد</option></select>
                <select id="courseTeacher"><option>كل المدرسين</option></select>
            </div>
            <div class="empty-state-royal"><p>لا توجد كورسات مشتراة حالياً.</p></div>
        </div>

        <!-- 5. الفواتير -->
        <div id="tab-invoices" class="profile-pane">
            <div class="glass-section-title"><h3>سجل الفواتير</h3></div>
            <div class="table-responsive">
                <table class="royal-table">
                    <thead><tr><th>التاريخ</th><th>البيان</th><th>المبلغ</th><th>الحالة</th></tr></thead>
                    <tbody><tr><td colspan="4" style="text-align:center; padding:30px; opacity:0.5">لا يوجد سجلات</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- 6. الاشتراكات -->
        <div id="tab-subs" class="profile-pane">
            <div class="glass-section-title"><h3>الاشتراكات النشطة</h3></div>
            <div class="empty-state-royal"><i class="fas fa-calendar-alt"></i><p>لا يوجد اشتراكات شهرية حالياً.</p></div>
        </div>

        <!-- 7. نتائج الامتحانات -->
        <div id="tab-results" class="profile-pane">
            <style>
                #tab-results .royal-table {
                    display: table !important;
                    width: 100% !important;
                    border-collapse: separate !important;
                    border-spacing: 0 10px !important;
                    margin-top: 25px !important;
                }
                #tab-results .royal-table thead {
                    display: table-header-group !important;
                }
                #tab-results .royal-table tbody {
                    display: table-row-group !important;
                }
                #tab-results .royal-table tr {
                    display: table-row !important;
                    transition: all 0.2s ease !important;
                }
                #tab-results .royal-table th,
                #tab-results .royal-table td {
                    display: table-cell !important;
                    text-align: right !important;
                    vertical-align: middle !important;
                    padding: 14px 16px !important;
                }
                #tab-results .royal-table th {
                    font-size: 0.9rem !important;
                    font-weight: 800 !important;
                    color: var(--text-dark) !important;
                    opacity: 0.8 !important;
                    border-bottom: 2px solid var(--glass-border) !important;
                }
                #tab-results .royal-table td {
                    font-size: 0.9rem !important;
                    background: rgba(255, 255, 255, 0.03) !important;
                    border-top: 1px solid var(--glass-border) !important;
                    border-bottom: 1px solid var(--glass-border) !important;
                    color: var(--text-dark) !important;
                }
                .dark-mode #tab-results .royal-table td {
                    background: rgba(255, 255, 255, 0.01) !important;
                }
                #tab-results .royal-table tr td:first-child {
                    border-right: 1px solid var(--glass-border) !important;
                    border-top-right-radius: 12px !important;
                    border-bottom-right-radius: 12px !important;
                    font-weight: 800 !important;
                }
                #tab-results .royal-table tr td:last-child {
                    border-left: 1px solid var(--glass-border) !important;
                    border-top-left-radius: 12px !important;
                    border-bottom-left-radius: 12px !important;
                }
                #tab-results .royal-table tbody tr:hover td {
                    background: rgba(221, 168, 82, 0.08) !important;
                    border-color: rgba(221, 168, 82, 0.3) !important;
                }
                #tab-results .filter-controls select {
                    border: 1px solid var(--glass-border) !important;
                    background: var(--glass-bg) !important;
                    color: var(--text-dark) !important;
                    border-radius: 12px !important;
                    font-weight: 700 !important;
                    font-family: inherit !important;
                    padding: 0 15px !important;
                    height: 48px !important;
                    outline: none !important;
                    transition: all 0.3s ease !important;
                }
                #tab-results .filter-controls select:focus {
                    border-color: var(--accent-gold) !important;
                    box-shadow: 0 0 0 3px rgba(221, 168, 82, 0.15) !important;
                }
            </style>
            <div class="glass-section-title"><h3>📊 نتائج الاختبارات المنجزة</h3></div>
            
            <div class="filter-controls">
                <select id="resSub" onchange="filterStudentResults()">
                    <option value="">كل المواد الدراسية</option>
                </select>
                <select id="resTeach" onchange="filterStudentResults()">
                    <option value="">كل المعلمين</option>
                </select>
            </div>

            <div class="empty-state-royal" id="student-results-empty">
                <i class="fas fa-poll-h"></i>
                <p>لا توجد نتائج امتحانات متطابقة مع التصفية الحالية</p>
            </div>

            <div class="table-responsive" id="student-results-table-wrapper" style="display:none; margin-top:20px;">
                <table class="royal-table">
                    <thead>
                        <tr>
                            <th>اسم الامتحان</th>
                            <th>المادة</th>
                            <th>المعلم</th>
                            <th>الدرجة الكلية</th>
                            <th>النسبة المئوية</th>
                            <th>حالة التصحيح</th>
                            <th>تاريخ حل الامتحان</th>
                        </tr>
                    </thead>
                    <tbody id="student-results-tbody">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<?php include 'footer.php'; ?>
