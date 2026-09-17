<?php
$page_title = "تفاصيل ومحتوى الكورس | EDGE Academy";
$active_tab = "courses";
$body_class = "course-view-page";
$extra_headers = '<style>
/* ═══════════════════════════════════════════════════
   🎬 COURSE VIEW PAGE — MODERN BASSTHALK STYLE
═══════════════════════════════════════════════════ */
.cv-page-wrap {
    max-width: 1240px;
    margin: 0 auto;
    padding: 30px 5% 70px;
}

/* ─── Breadcrumb ─── */
.cv-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--gray);
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.cv-breadcrumb a {
    color: var(--gray);
    text-decoration: none;
    transition: color 0.2s;
}
.cv-breadcrumb a:hover {
    color: #1a6bdb;
}
.cv-breadcrumb i {
    font-size: 0.75rem;
    opacity: 0.6;
}
.cv-breadcrumb span {
    color: #1a6bdb;
}

/* ─── 2-Column Grid Layout ─── */
.cv-main-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 32px;
    align-items: start;
}

/* ─── Right Column: Course Content ─── */
.cv-content-col {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.cv-header-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 30px;
    box-shadow: 0 4px 24px rgba(26, 107, 219, 0.06);
}

.cv-course-title {
    font-size: 1.75rem;
    font-weight: 900;
    color: var(--text-dark);
    margin: 0 0 14px;
    line-height: 1.35;
}

.cv-teacher-row {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
}
.cv-teacher-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2.5px solid #1a6bdb;
}
.cv-teacher-info-name {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text-dark);
    text-decoration: none;
}
.cv-teacher-info-name:hover {
    color: #1a6bdb;
}
.cv-teacher-info-sub {
    font-size: 0.84rem;
    color: var(--gray);
}

.cv-tags-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.cv-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.84rem;
    font-weight: 700;
    background: var(--surface-alt);
    color: var(--text-dark);
}
.cv-badge.badge-enrolled {
    background: rgba(0, 184, 148, 0.15);
    color: #00b894;
    border: 1px solid rgba(0, 184, 148, 0.3);
}

.cv-desc-box {
    font-size: 0.95rem;
    color: var(--gray);
    line-height: 1.65;
    margin: 0;
    border-top: 1px solid var(--border);
    padding-top: 18px;
}

/* ─── Lectures Section ─── */
.cv-lectures-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 26px 30px;
    box-shadow: 0 4px 24px rgba(26, 107, 219, 0.06);
}

.cv-lectures-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 10px;
}
.cv-lectures-header h2 {
    font-size: 1.3rem;
    font-weight: 800;
    margin: 0;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}
.cv-lectures-header h2 i {
    color: #1a6bdb;
}
.cv-count-pill {
    background: rgba(26, 107, 219, 0.1);
    color: #1a6bdb;
    padding: 5px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 800;
}

/* ─── Video Item Card (Interactive Lecture Block) ─── */
.cv-videos-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.cv-v-item {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 16px 20px;
    border-radius: 18px;
    background: var(--surface-alt);
    border: 1.5px solid transparent;
    transition: all 0.25s ease;
    cursor: pointer;
}
.cv-v-item:hover {
    border-color: rgba(26, 107, 219, 0.35);
    background: var(--surface);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(26, 107, 219, 0.09);
}
.cv-v-item.unlocked:hover {
    border-color: rgba(0, 184, 148, 0.5);
}

.cv-v-num {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 0.95rem;
    flex-shrink: 0;
    background: var(--surface);
    color: var(--text-dark);
    border: 1.5px solid var(--border);
}
.cv-v-item.unlocked .cv-v-num {
    background: rgba(0, 184, 148, 0.15);
    color: #00b894;
    border-color: rgba(0, 184, 148, 0.3);
}

.cv-v-thumbnail {
    width: 96px;
    height: 62px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
    background: #e2e8f0;
}

.cv-v-details {
    flex: 1;
    min-width: 0;
}
.cv-v-title {
    font-size: 1.02rem;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 6px;
    line-height: 1.35;
}
.cv-v-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 0.82rem;
    color: var(--gray);
    flex-wrap: wrap;
}
.cv-v-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.cv-v-action-btn {
    flex-shrink: 0;
}
.cv-play-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 20px;
    border-radius: 12px;
    background: #00b894;
    color: #ffffff !important;
    font-weight: 800;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(0, 184, 148, 0.25);
}
.cv-play-btn:hover {
    background: #009879;
    transform: scale(1.04);
}
.cv-lock-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 12px;
    background: rgba(26, 107, 219, 0.08);
    color: #1a6bdb;
    font-weight: 800;
    font-size: 0.86rem;
    border: 1.5px solid rgba(26, 107, 219, 0.2);
    cursor: pointer;
}
.cv-exam-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #ffffff !important;
    font-weight: 800;
    font-size: 0.88rem;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
}
.cv-exam-btn:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.45);
}

/* ─── Left Column: Sticky Summary Card ─── */
.cv-sidebar-col {
    position: sticky;
    top: 90px;
}

.cv-summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(26, 107, 219, 0.08);
}

.cv-summary-thumb-box {
    position: relative;
    width: 100%;
    height: 195px;
    background: #e8effe;
}
.cv-summary-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cv-summary-price-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: rgba(13, 21, 38, 0.85);
    backdrop-filter: blur(6px);
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 800;
    padding: 6px 14px;
    border-radius: 8px;
}

.cv-summary-body {
    padding: 24px;
}
.cv-summary-price-text {
    font-size: 1.45rem;
    font-weight: 900;
    color: #1a6bdb;
    margin-bottom: 16px;
    text-align: right;
}

.cv-features-list {
    list-style: none;
    padding: 0;
    margin: 0 0 22px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    font-size: 0.88rem;
    color: var(--text-dark);
}
.cv-features-list li {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cv-features-list li i {
    color: #00b894;
    font-size: 0.95rem;
    width: 18px;
}

.cv-summary-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.cv-btn-subscribe-main {
    width: 100%;
    padding: 13px;
    border-radius: 12px;
    background: #1a6bdb;
    color: #ffffff;
    border: none;
    font-weight: 800;
    font-size: 1.02rem;
    cursor: pointer;
    text-align: center;
    box-shadow: 0 6px 18px rgba(26, 107, 219, 0.3);
    transition: all 0.25s ease;
}
.cv-btn-subscribe-main:hover {
    background: #1355b5;
    transform: translateY(-2px);
}

.cv-enrolled-banner {
    background: rgba(0, 184, 148, 0.15);
    border: 1.5px solid #00b894;
    border-radius: 12px;
    padding: 13px;
    color: #00b894;
    font-weight: 800;
    font-size: 0.95rem;
    text-align: center;
}

/* ─── Activation Modal ─── */
.cv-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(13, 21, 38, 0.75);
    backdrop-filter: blur(6px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
}
.cv-modal-backdrop.active {
    opacity: 1;
    pointer-events: all;
}
.cv-modal-dialog {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 34px 28px;
    width: 90%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    transform: scale(0.92);
    transition: transform 0.25s ease;
}
.cv-modal-backdrop.active .cv-modal-dialog {
    transform: scale(1);
}
.cv-modal-dialog h3 {
    color: var(--text-dark);
    font-size: 1.3rem;
    font-weight: 900;
    margin: 10px 0 6px;
}
.cv-modal-dialog p {
    color: var(--gray);
    font-size: 0.88rem;
    margin: 0 0 20px;
}
.cv-code-input {
    width: 100%;
    padding: 13px;
    border-radius: 12px;
    border: 2px solid var(--border);
    background: var(--surface-alt);
    color: var(--text-dark);
    font-size: 1.15rem;
    text-align: center;
    letter-spacing: 2px;
    font-family: inherit;
    outline: none;
    margin-bottom: 14px;
    box-sizing: border-box;
}
.cv-code-input:focus {
    border-color: #1a6bdb;
}
.cv-submit-btn {
    width: 100%;
    padding: 12px;
    background: #1a6bdb;
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    margin-bottom: 10px;
    transition: all 0.2s;
}
.cv-submit-btn:hover {
    background: #1355b5;
}
.cv-cancel-btn {
    color: var(--gray);
    font-size: 0.88rem;
    cursor: pointer;
}
.cv-cancel-btn:hover {
    color: var(--text-dark);
}

@media (max-width: 900px) {
    .cv-main-layout {
        grid-template-columns: 1fr;
    }
    .cv-sidebar-col {
        position: static;
        order: -1;
    }
}
</style>';
include 'header.php';
?>

<div class="cv-page-wrap">
    <!-- Breadcrumb -->
    <nav class="cv-breadcrumb" id="cvBreadcrumb">
        <a href="index.php">الرئيسية</a>
        <i class="fas fa-chevron-left"></i>
        <a href="courses.php">الكورسات</a>
        <i class="fas fa-chevron-left"></i>
        <span id="cvBreadcrumbCurrent">جاري التحميل...</span>
    </nav>

    <!-- 2-Column Main Layout -->
    <div class="cv-main-layout" id="cvMainLayout">
        <!-- Right: Course Information & Lectures List -->
        <div class="cv-content-col">
            <!-- Header Card -->
            <div class="cv-header-card" id="cvHeaderCard">
                <div style="text-align:center; padding:30px; color:var(--gray);">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top:10px;">جاري تحميل بيانات الكورس...</p>
                </div>
            </div>

            <!-- Lectures Card -->
            <div class="cv-lectures-card">
                <div class="cv-lectures-header">
                    <h2><i class="fas fa-play-circle"></i> محاضرات ومحتوى الكورس</h2>
                    <div class="cv-count-pill" id="cvLecturesCountBadge">0 محاضرة</div>
                </div>
                <div class="cv-videos-list" id="cvVideosList">
                    <div style="text-align:center; padding:30px; color:var(--gray);">
                        <i class="fas fa-spinner fa-spin"></i> جاري تحميل المحاضرات...
                    </div>
                </div>
            </div>
        </div>

        <!-- Left: Sticky Course Summary Sidebar -->
        <div class="cv-sidebar-col" id="cvSidebarCol">
            <!-- Injected by JS -->
        </div>
    </div>
</div>

<!-- ═══ Activation Modal ═══ -->
<div class="cv-modal-backdrop" id="cvBuyModal">
    <div class="cv-modal-dialog">
        <div style="font-size: 2.5rem; margin-bottom: 6px;">🔑</div>
        <h3>تفعيل واشتراك في الكورس</h3>
        <p id="cvModalDesc">أدخل كود الشحن الخاص بالكورس لتفعيل كافة محاضراته فوراً:</p>
        <input type="text" class="cv-code-input" id="cvModalCodeInput" placeholder="أدخل الكود هنا..." maxlength="40">
        <button class="cv-submit-btn" id="cvModalSubmitBtn" onclick="handleCourseActivation()">✅ تفعيل الكورس الآن</button>
        <div id="cvModalFeedback" style="margin-top:8px;font-weight:700;font-size:0.9rem;"></div>
        <div class="cv-cancel-btn" onclick="closeActivationModal()">إلغاء</div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
let _activeCourseId = null;
let _courseData     = null;
let _isEnrolled     = false;

function resolveImg(path) {
    const base = window._CV_API_URL || '';
    if (!path) return '';
    if (path.startsWith('http')) return path;
    if (path.startsWith('/uploads/')) return base + path;
    return path;
}

(async function initCourseViewPage() {
    const params = new URLSearchParams(window.location.search);
    _activeCourseId = params.get('id');

    if (!_activeCourseId) {
        renderPageError('لم يتم تحديد كورس', 'يرجى العودة واختيار كورس صالح.');
        return;
    }

    const API_URL = localStorage.getItem('apiUrl') ||
        window.location.origin + (window.location.pathname.includes('/masar') ? '/masar' : '');
    window._CV_API_URL = API_URL;

    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    const isAdmin = currentUser && (currentUser.role === 'admin' || currentUser.phone === '01556448880' || currentUser.phone === '01234567890' || currentUser.isAdmin === true);
    const studentId = currentUser ? (currentUser._id || currentUser.id || '') : '';

    try {
        const [cRes, tRes, eRes] = await Promise.all([
            fetch(`${API_URL}/api/courses/${_activeCourseId}/videos?userId=${studentId}`),
            fetch(`${API_URL}/api/teachers`),
            studentId ? fetch(`${API_URL}/api/exams/student/${studentId}/results`).catch(() => null) : Promise.resolve(null)
        ]);

        if (!cRes.ok) throw new Error('فشل جلب بيانات الكورس');

        const courseResponse = await cRes.json();
        const teachersList = tRes.ok ? await tRes.json() : [];
        const examResultsMap = {};
        if (eRes && eRes.ok) {
            try {
                const eData = await eRes.json();
                const rList = eData.results || [];
                rList.forEach(r => {
                    examResultsMap[r.examId] = r;
                });
            } catch (err) {}
        }

        _courseData = courseResponse.course;
        _isEnrolled = isAdmin ? true : courseResponse.isEnrolled;
        const videosList = courseResponse.videos || [];

        const teacher = teachersList.find(t => String(t._id) === String(_courseData.teacherId));

        document.title = `${_courseData.title} | EDGE Academy`;
        document.getElementById('cvBreadcrumbCurrent').textContent = _courseData.title;

        renderCourseHeader(_courseData, teacher, _isEnrolled, isAdmin);
        renderCourseSidebar(_courseData, teacher, _isEnrolled, videosList, isAdmin);
        renderCourseVideos(videosList, _isEnrolled, _courseData, examResultsMap, isAdmin);

        // فتح نافذة التفعيل تلقائياً عند الضغط على "الإشتراك في الكورس" من الخارج
        if ((params.get('activate') === '1' || params.get('openModal') === '1') && !_isEnrolled) {
            setTimeout(() => openActivationModal(), 300);
        }

    } catch (e) {
        console.error("Course view error:", e);
        renderPageError('تعذر تحميل بيانات الكورس', 'يرجى التأكد من تشغيل السيرفر والمحاولة مرة أخرى.');
    }
})();

function renderCourseHeader(course, teacher, isEnrolled, isAdmin = false) {
    const teacherHtml = teacher ? `
        <div class="cv-teacher-row">
            <img src="${resolveImg(teacher.image || teacher.imagePath) || 'imges/man.png'}" class="cv-teacher-avatar" alt="${teacher.name}">
            <div>
                <a href="teacher-profile.php?id=${teacher._id}" class="cv-teacher-info-name">${teacher.name}</a>
                <div class="cv-teacher-info-sub">${teacher.subjectAr || 'مدرس المادة'}</div>
            </div>
        </div>` : '';

    const enrolledBadge = isAdmin
        ? `<span class="cv-badge" style="background:rgba(37,99,235,0.15); color:#2563eb; font-weight:700;"><i class="fas fa-user-shield"></i> حساب مسؤول (وصول كامل للمحتوى)</span>`
        : (isEnrolled
            ? `<span class="cv-badge badge-enrolled"><i class="fas fa-check-circle"></i> أنت مشترك في هذا الكورس</span>`
            : '');
    const gradeBadge = (course.grades && course.grades.length)
        ? `<span class="cv-badge"><i class="fas fa-graduation-cap"></i> ${course.grades.join('، ')}</span>`
        : '';

    const descHtml = course.description
        ? `<p class="cv-desc-box">${course.description}</p>`
        : '';

    document.getElementById('cvHeaderCard').innerHTML = `
        <h1 class="cv-course-title">${course.title}</h1>
        ${teacherHtml}
        <div class="cv-tags-bar">
            ${enrolledBadge}
            ${gradeBadge}
        </div>
        ${descHtml}`;
}

function renderCourseSidebar(course, teacher, isEnrolled, videos, isAdmin = false) {
    const isFree = (course.price === null || course.price === undefined);
    const priceText = isFree ? 'مجاني بالكامل 🎁' : (course.price > 0 ? `${course.price} جنية` : 'مجاني بكود 🎟️');

    let actionHtml;
    if (isAdmin) {
        actionHtml = `
            <div class="cv-enrolled-banner" style="background:rgba(37,99,235,0.15);border-color:#2563eb;color:#2563eb;font-weight:700;">
                <i class="fas fa-user-shield"></i> متاح بالكامل (صلاحيات مسؤول)
            </div>`;
    } else if (isEnrolled) {
        actionHtml = `
            <div class="cv-enrolled-banner">
                <i class="fas fa-check-circle"></i> أنت مشترك في هذا الكورس
            </div>`;
    } else if (isFree) {
        actionHtml = `
            <div class="cv-enrolled-banner" style="background:rgba(26,107,219,0.1);border-color:#1a6bdb;color:#1a6bdb;">
                <i class="fas fa-unlock"></i> متاح مجاناً للجميع
            </div>`;
    } else {
        actionHtml = `
            <button class="cv-btn-subscribe-main" onclick="openActivationModal()">
                <i class="fas fa-key" style="margin-left:6px;"></i> الإشتراك في الكورس !
            </button>`;
    }

    const teacherName = teacher ? teacher.name : 'مدرس المادة';

    document.getElementById('cvSidebarCol').innerHTML = `
        <div class="cv-summary-card">
            <div class="cv-summary-thumb-box">
                <span class="cv-summary-price-badge">${priceText}</span>
                <img src="${resolveImg(course.imagePath) || 'imges/st.jpg'}" class="cv-summary-thumb" alt="${course.title}" onerror="this.onerror=null;this.src='imges/st.jpg'">
            </div>
            <div class="cv-summary-body">
                <div class="cv-summary-price-text">${priceText}</div>
                <ul class="cv-features-list">
                    <li><i class="fas fa-play-circle"></i> ${videos.length} محاضرات وشروحات شاملة</li>
                    <li><i class="fas fa-user-tie"></i> إشراف الأستاذ: ${teacherName}</li>
                    <li><i class="fas fa-infinity"></i> وصول كامل لمحتوى الكورس</li>
                    <li><i class="fas fa-file-alt"></i> واجبات وامتحانات إلكترونية</li>
                    <li><i class="fas fa-shield-alt"></i> مشاهدة آمنة وحصرية</li>
                </ul>
                <div class="cv-summary-actions">
                    ${actionHtml}
                </div>
            </div>
        </div>`;
}

function renderCourseVideos(videos, isEnrolled, course, examResultsMap = {}, isAdmin = false) {
    document.getElementById('cvLecturesCountBadge').textContent = `${videos.length} محاضرة`;
    const list = document.getElementById('cvVideosList');

    if (!videos || videos.length === 0) {
        list.innerHTML = `
            <div style="text-align:center; padding: 40px 20px; color:var(--gray);">
                <i class="fas fa-video-slash" style="font-size:2.5rem; margin-bottom:12px; opacity:0.4;"></i>
                <h4 style="color:var(--text-dark);">لا توجد محاضرات في هذا الكورس بعد</h4>
                <p style="font-size:0.88rem;">سيتم إضافة المحاضرات قريباً.</p>
            </div>`;
        return;
    }

    list.innerHTML = videos.map((v, idx) => {
        const isVideoFree = (v.price === null || v.price === undefined);
        const hasEnrolledOrFree = isEnrolled || isVideoFree;

        // Check required exam prerequisite
        const hasRequiredExam = !!v.requiredExamId;
        const examResult = hasRequiredExam ? examResultsMap[v.requiredExamId] : null;
        const examPassed = examResult && Number(examResult.percentage || 0) >= 50;
        const isExamLocked = !isAdmin && (hasRequiredExam && !examPassed);

        const canWatch = isAdmin || (hasEnrolledOrFree && !isExamLocked);
        const watchUrl = `watch.php?videoId=${v._id}&code=${(isVideoFree || isAdmin) ? 'FREE_ACCESS' : 'ALREADY_SUBSCRIBED'}`;
        const examUrl = `exams.php?examId=${v.requiredExamId}`;

        const partsCount = (v.parts && v.parts.length)
            ? `<span><i class="fas fa-layer-group"></i> ${v.parts.length + 1} أجزاء</span>` : '';

        // Attachments badge
        const bookletCount = (v.bookletFiles && Array.isArray(v.bookletFiles)) ? v.bookletFiles.length : 0;
        const homeworkCount = (v.homeworkFiles && Array.isArray(v.homeworkFiles)) ? v.homeworkFiles.length : 0;
        let attachmentsBadge = '';
        if (bookletCount > 0 || homeworkCount > 0) {
            attachmentsBadge = `<span style="background:rgba(26,107,219,0.1); color:#1a6bdb; padding:2px 8px; border-radius:6px; font-size:0.75rem; margin-right:6px;"><i class="fas fa-paperclip"></i> مرفقات (${bookletCount + homeworkCount})</span>`;
        }

        let actionBtn, clickAction, statusBadge;
        if (isAdmin) {
            actionBtn = `<a href="${watchUrl}" class="cv-play-btn" style="background:#2563eb;" onclick="event.stopPropagation()">
                <i class="fas fa-play"></i> مشاهدة
            </a>`;
            clickAction = `onclick="location.href='${watchUrl}'"`;
            const reqText = hasRequiredExam ? ' (امتحان قبلي معفى)' : '';
            statusBadge = `<span style="color:#2563eb; font-weight:700;"><i class="fas fa-user-shield"></i> متاح للمسؤول${reqText}</span>`;
        } else if (isExamLocked) {
            actionBtn = `<a href="${examUrl}" class="cv-exam-btn" onclick="event.stopPropagation()">
                <i class="fas fa-file-signature"></i> دخول الامتحان
            </a>`;
            clickAction = `onclick="location.href='${examUrl}'"`;
            const examNote = examResult ? `(درجتك ${examResult.percentage}% - يلزم 50%)` : '(يلزم اجتياز 50% لفتح المحاضرة)';
            statusBadge = `<span style="color:#d97706; font-weight:600;"><i class="fas fa-lock"></i> امتحان قبلي مطلوب ${examNote}</span>`;
        } else if (canWatch) {
            actionBtn = `<a href="${watchUrl}" class="cv-play-btn" onclick="event.stopPropagation()">
                <i class="fas fa-play"></i> مشاهدة
            </a>`;
            clickAction = `onclick="location.href='${watchUrl}'"`;
            const passedBadge = hasRequiredExam ? `<span style="color:#00b894; font-size:0.8rem; margin-left:8px;"><i class="fas fa-check-circle"></i> تم اجتياز الامتحان (${examResult.percentage}%)</span>` : '';
            statusBadge = `<span><i class="fas fa-unlock-alt" style="color:#00b894;"></i> متاح للمشاهدة</span> ${passedBadge}`;
        } else {
            actionBtn = `<button class="cv-lock-pill" onclick="event.stopPropagation(); openActivationModal();">
                <i class="fas fa-lock"></i> مقفول
            </button>`;
            clickAction = `onclick="openActivationModal()"`;
            statusBadge = `<span><i class="fas fa-lock"></i> يتطلب اشتراك</span>`;
        }

        return `
        <div class="cv-v-item ${canWatch ? 'unlocked' : 'locked'}" ${clickAction}>
            <div class="cv-v-num">${idx + 1}</div>
            <img src="${resolveImg(v.imagePath) || 'imges/st.jpg'}" class="cv-v-thumbnail" alt="${v.title}" onerror="this.onerror=null;this.src='imges/st.jpg'">
            <div class="cv-v-details">
                <div class="cv-v-title">${v.title}</div>
                <div class="cv-v-meta">
                    ${partsCount}
                    ${attachmentsBadge}
                    ${statusBadge}
                </div>
            </div>
            <div class="cv-v-action-btn">
                ${actionBtn}
            </div>
        </div>`;
    }).join('');
}

function openActivationModal() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    if (!currentUser) {
        if (confirm('يرجى تسجيل الدخول أولاً لتفعيل الكورس في حسابك. الانتقال لتسجيل الدخول؟')) {
            window.location.href = 'login.php';
        }
        return;
    }
    document.getElementById('cvModalFeedback').innerHTML = '';
    document.getElementById('cvModalCodeInput').value = '';
    document.getElementById('cvBuyModal').classList.add('active');
    setTimeout(() => document.getElementById('cvModalCodeInput').focus(), 250);
}

function closeActivationModal() {
    document.getElementById('cvBuyModal').classList.remove('active');
}

async function handleCourseActivation() {
    const code = document.getElementById('cvModalCodeInput').value.trim();
    if (!code) return;

    const btn = document.getElementById('cvModalSubmitBtn');
    const feedback = document.getElementById('cvModalFeedback');
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    const userId = currentUser ? (currentUser._id || currentUser.id) : null;

    btn.disabled = true;
    btn.textContent = 'جاري التحقق والتفعيل...';
    feedback.innerHTML = '';

    const API_URL = localStorage.getItem('apiUrl') ||
        window.location.origin + (window.location.pathname.includes('/masar') ? '/masar' : '');

    try {
        const res = await fetch(`${API_URL}/api/courses/${_activeCourseId}/buy`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codeStr: code, userId: userId })
        });
        const data = await res.json();

        if (data.success) {
            feedback.style.color = '#00b894';
            feedback.innerHTML = `🎉 ${data.message || 'تم تفعيل الكورس بنجاح!'}`;

            currentUser.subscribedCourses = currentUser.subscribedCourses || [];
            currentUser.subscribedCourses.push({ courseId: _activeCourseId, codeUsed: code });
            localStorage.setItem('currentUser', JSON.stringify(currentUser));

            setTimeout(() => location.reload(), 1500);
        } else {
            feedback.style.color = '#ef4444';
            feedback.innerHTML = `❌ ${data.message || 'كود غير صحيح'}`;
            btn.disabled = false;
            btn.textContent = '✅ تفعيل الكورس الآن';
        }
    } catch (e) {
        feedback.style.color = '#ef4444';
        feedback.innerHTML = '❌ حدث خطأ في الاتصال بالسيرفر';
        btn.disabled = false;
        btn.textContent = '✅ تفعيل الكورس الآن';
    }
}

function renderPageError(title, msg) {
    document.getElementById('cvMainLayout').innerHTML = `
        <div style="grid-column:1/-1; text-align:center; padding:80px 20px; color:var(--gray);">
            <i class="fas fa-exclamation-triangle" style="font-size:3.5rem; color:#ef4444; margin-bottom:16px;"></i>
            <h2 style="color:var(--text-dark);">${title}</h2>
            <p>${msg}</p>
            <a href="courses.php" class="cv-btn-subscribe-main" style="margin-top:20px; display:inline-flex; width:auto; padding:12px 30px;">العودة للكورسات</a>
        </div>`;
}
</script>