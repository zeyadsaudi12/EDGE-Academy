<?php
$page_title = "بروفايل المدرس | EDGE Academy";
$active_tab = "teachers";
$body_class = "teacher-profile-page";
$extra_headers = '
<style>
/* ═══════════════════════════════════════════════════
   👨‍🏫 TEACHER PROFILE — MODERN BASSTHALK STYLE
═══════════════════════════════════════════════════ */
.tp-hero-section {
    background: linear-gradient(135deg, #152038 0%, #1a2744 50%, #0d1526 100%);
    padding: 50px 5% 55px;
    position: relative;
    overflow: hidden;
    color: #ffffff;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.tp-hero-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 80% 40%, rgba(26, 107, 219, 0.25) 0%, transparent 60%);
    pointer-events: none;
}
.tp-hero-container {
    max-width: 1140px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 36px;
    position: relative;
    z-index: 2;
    flex-wrap: wrap;
}
.tp-avatar-box {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
}
.tp-avatar-img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #1a6bdb;
    box-shadow: 0 12px 32px rgba(0,0,0,0.5);
    background: #1a2744;
}
.tp-avatar-badge {
    position: absolute;
    bottom: 4px;
    left: 4px;
    background: #00b894;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    border: 2px solid #152038;
}
.tp-hero-details {
    flex: 1;
    min-width: 280px;
}
.tp-teacher-name {
    font-size: 2rem;
    font-weight: 900;
    margin: 0 0 8px;
    color: #ffffff;
}
.tp-subject-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(26, 107, 219, 0.25);
    color: #3d8ef0;
    border: 1px solid rgba(61, 142, 240, 0.35);
    padding: 5px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 700;
    margin-bottom: 14px;
}
.tp-bio-text {
    font-size: 0.95rem;
    color: #cbd5e1;
    line-height: 1.6;
    margin: 0 0 18px;
    max-width: 680px;
}
.tp-stats-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.tp-stat-pill {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 8px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.88rem;
    color: #e2e8f0;
}
.tp-stat-pill strong {
    color: #00b894;
    font-size: 1.15rem;
}

/* ─── Filter Bar ─── */
.tp-filter-bar {
    max-width: 1140px;
    margin: 30px auto 0;
    padding: 0 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.tp-grade-chips {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.tp-chip {
    padding: 8px 18px;
    border-radius: 50px;
    background: var(--surface);
    border: 1.5px solid var(--border);
    color: var(--text-dark);
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.22s ease;
}
.tp-chip:hover {
    border-color: #1a6bdb;
    color: #1a6bdb;
}
.tp-chip.active {
    background: #1a6bdb;
    color: #ffffff;
    border-color: #1a6bdb;
    box-shadow: 0 4px 14px rgba(26, 107, 219, 0.3);
}

.tp-search-box {
    position: relative;
    min-width: 220px;
}
.tp-search-box i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 0.85rem;
}
.tp-search-input {
    width: 100%;
    padding: 9px 36px 9px 14px;
    border-radius: 50px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    color: var(--text-dark);
    font-family: inherit;
    font-size: 0.88rem;
    outline: none;
    box-sizing: border-box;
}
.tp-search-input:focus {
    border-color: #1a6bdb;
}

/* ─── Courses Grid Section ─── */
.tp-content-section {
    max-width: 1140px;
    margin: 0 auto;
    padding: 24px 5% 70px;
}
.tp-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.tp-section-heading h2 {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-dark);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tp-section-heading h2 i {
    color: #1a6bdb;
}

.tp-courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 24px;
}

.tp-empty-box {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: var(--surface);
    border-radius: 20px;
    border: 1px solid var(--border);
    color: var(--gray);
}
.tp-empty-box i {
    font-size: 3rem;
    opacity: 0.4;
    margin-bottom: 12px;
    display: block;
}

@media (max-width: 600px) {
    .tp-hero-container {
        flex-direction: column;
        text-align: center;
    }
    .tp-stats-row {
        justify-content: center;
    }
    .tp-teacher-name {
        font-size: 1.6rem;
    }
    .tp-courses-grid {
        grid-template-columns: 1fr;
    }
}
</style>
';
include 'header.php';
?>

<!-- ═══ Teacher Hero ═══ -->
<section class="tp-hero-section" id="tpHeroSection">
    <div class="tp-hero-container" id="tpHeroContainer">
        <div class="tp-avatar-box">
            <div style="width:140px;height:140px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#64748b;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>
        <div class="tp-hero-details">
            <h1 class="tp-teacher-name">جاري تحميل بيانات المدرس...</h1>
        </div>
    </div>
</section>

<!-- ═══ Filters Bar ═══ -->
<div class="tp-filter-bar" id="tpFilterBar">
    <div class="tp-grade-chips" id="tpGradeChips">
        <button class="tp-chip active" data-grade="all" onclick="filterTeacherGrade('all', this)">
            <i class="fas fa-border-all"></i> جميع المراحل
        </button>
    </div>
    <div class="tp-search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="tpSearchInput" class="tp-search-input" placeholder="ابحث في الكورسات..." oninput="applyTeacherFilter()">
    </div>
</div>

<!-- ═══ Courses Grid ═══ -->
<main class="tp-content-section">
    <div class="tp-section-heading">
        <h2><i class="fas fa-layer-group"></i> الكورسات والمحاضرات المتاحة</h2>
        <span style="font-size:0.88rem; font-weight:700; color:var(--gray);" id="tpCoursesCountText">0 كورس</span>
    </div>
    <div class="tp-courses-grid" id="tpCoursesGrid">
        <div style="grid-column:1/-1; text-align:center; padding:50px; color:var(--gray);">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p style="margin-top:10px;">جاري تحميل الكورسات...</p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
let _tpTeacherId     = null;
let _tpTeacherData   = null;
let _tpAllCourses    = [];
let _tpActiveGrade   = 'all';
let _tpSearchQuery   = '';

function resolveImg(path) {
    const base = window._TP_API_URL || '';
    if (!path) return '';
    if (path.startsWith('http')) return path;
    if (path.startsWith('/uploads/')) return base + path;
    return path;
}

(async function initTeacherProfile() {
    const params = new URLSearchParams(window.location.search);
    _tpTeacherId = params.get('id');

    window._TP_API_URL = localStorage.getItem('apiUrl') ||
        window.location.origin + (window.location.pathname.includes('/masar') ? '/masar' : '');

    if (!_tpTeacherId) {
        renderError('لم يتم تحديد مدرس', 'يرجى العودة واختيار مدرس صالح.');
        return;
    }

    try {
        const [tRes, cRes, vRes] = await Promise.all([
            fetch(`${window._TP_API_URL}/api/teachers/${_tpTeacherId}`),
            fetch(`${window._TP_API_URL}/api/courses`),
            fetch(`${window._TP_API_URL}/api/videos`)
        ]);

        _tpTeacherData = tRes.ok ? await tRes.json() : null;
        let allCourses = cRes.ok ? await cRes.json() : [];
        const allVideos  = vRes.ok ? await vRes.json() : [];

        if (!_tpTeacherData) {
            renderError('المدرس غير موجود', 'تعذر العثور على بيانات هذا المدرس.');
            return;
        }

        document.title = `${_tpTeacherData.name} | EDGE Academy`;

        // فلترة الكورسات الخاصة بهذا المدرس
        let teacherCourses = allCourses.filter(c =>
            c.teacherId && String(c.teacherId) === String(_tpTeacherId) && !c.hidden
        );

        // Fallback لو مفيش كورسات منفصلة: استخدم الفيديوهات المنسوبة لهذا المدرس
        if (teacherCourses.length === 0) {
            teacherCourses = allVideos
                .filter(v => v.teacherId && String(v.teacherId) === String(_tpTeacherId) && !v.hidden)
                .map(v => ({
                    _id: v._id,
                    title: v.title,
                    description: v.playlistName || '',
                    price: v.price,
                    teacherId: v.teacherId,
                    grades: v.grades || [],
                    imagePath: v.imagePath || '',
                    hidden: v.hidden || false,
                    videoCount: 1,
                    _isVideo: true
                }));
        }

        _tpAllCourses = teacherCourses;

        renderHero(_tpTeacherData, _tpAllCourses.length);
        buildGradeChips(_tpAllCourses);
        renderCourses();

    } catch (e) {
        console.error(e);
        renderError('حدث خطأ في الاتصال', 'تعذر تحميل بيانات المدرس.');
    }
})();

function renderHero(teacher, count) {
    const avatar = (teacher.image || teacher.imagePath)
        ? `<img src="${resolveImg(teacher.image || teacher.imagePath)}" class="tp-avatar-img" alt="${teacher.name}" onerror="this.onerror=null;this.src='imges/man.png'">`
        : `<img src="imges/man.png" class="tp-avatar-img" alt="${teacher.name}">`;

    const bio = teacher.bio || 'معلم خبير ومتخصص على منصة EDGE Academy، يقدم شروحات متميزة لمساعدة الطلاب على التفوق والنجاح.';

    document.getElementById('tpHeroContainer').innerHTML = `
        <div class="tp-avatar-box">
            ${avatar}
            <div class="tp-avatar-badge" title="معلم موثق"><i class="fas fa-check"></i></div>
        </div>
        <div class="tp-hero-details">
            <h1 class="tp-teacher-name">${teacher.name}</h1>
            <div class="tp-subject-tag">
                <i class="fas fa-graduation-cap"></i> ${teacher.subjectAr || teacher.subject || 'مدرس المادة'}
            </div>
            <p class="tp-bio-text">${bio}</p>
            <div class="tp-stats-row">
                <div class="tp-stat-pill">
                    <i class="fas fa-layer-group" style="color:#1a6bdb;"></i>
                    <span>الكورسات المتاحة: <strong>${count}</strong></span>
                </div>
                <div class="tp-stat-pill">
                    <i class="fas fa-shield-alt" style="color:#00b894;"></i>
                    <span>شروحات ومتابعات مستمرة</span>
                </div>
            </div>
        </div>`;
}

function buildGradeChips(courses) {
    const gradesSet = new Set();
    courses.forEach(c => {
        if (c.grades && Array.isArray(c.grades)) {
            c.grades.forEach(g => gradesSet.add(g));
        }
    });

    const container = document.getElementById('tpGradeChips');
    gradesSet.forEach(grade => {
        const btn = document.createElement('button');
        btn.className = 'tp-chip';
        btn.dataset.grade = grade;
        btn.onclick = () => filterTeacherGrade(grade, btn);
        btn.innerHTML = `<i class="fas fa-book-reader" style="margin-left:4px;"></i>${grade}`;
        container.appendChild(btn);
    });
}

function filterTeacherGrade(grade, btnEl) {
    _tpActiveGrade = grade;
    document.querySelectorAll('.tp-chip').forEach(c => c.classList.remove('active'));
    btnEl.classList.add('active');
    renderCourses();
}

function applyTeacherFilter() {
    _tpSearchQuery = document.getElementById('tpSearchInput').value.trim().toLowerCase();
    renderCourses();
}

function renderCourses() {
    const grid = document.getElementById('tpCoursesGrid');
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    const enrolledIds = (currentUser?.subscribedCourses || []).map(e => {
        if (typeof e === 'string') return e;
        return String(e.courseId || e._id || '');
    }).filter(Boolean);

    let filtered = _tpAllCourses;
    if (_tpActiveGrade !== 'all') {
        filtered = filtered.filter(c => c.grades && c.grades.includes(_tpActiveGrade));
    }
    if (_tpSearchQuery) {
        filtered = filtered.filter(c =>
            (c.title || '').toLowerCase().includes(_tpSearchQuery) ||
            (c.description || '').toLowerCase().includes(_tpSearchQuery)
        );
    }

    document.getElementById('tpCoursesCountText').textContent = `${filtered.length} كورس`;

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="tp-empty-box">
                <i class="fas fa-search-minus"></i>
                <h3 style="color:var(--text-dark); margin:0 0 6px;">لا توجد نتائج مطابقة</h3>
                <p style="margin:0;">جرّب اختيار مرحلة أخرى أو تعديل كلمة البحث.</p>
            </div>`;
        return;
    }

    grid.innerHTML = filtered.map(course => {
        const isEnrolled = enrolledIds.includes(String(course._id));
        const destUrl = `course-view.php?id=${course._id}`;
        const subscribeUrl = isEnrolled ? destUrl : `course-view.php?id=${course._id}&activate=1`;

        const isFree = (course.price === null || course.price === undefined);
        const priceLabelText = isFree ? 'مجاني' : (course.price > 0 ? `${course.price} جنية` : 'مجاني بكود');
        const priceBadgeClass = isFree ? 'course-price-badge-overlay free' : 'course-price-badge-overlay';

        const enterBtn = `<a href="${destUrl}" class="btn-enter" onclick="event.stopPropagation()">الدخول للكورس</a>`;
        let subscribeBtn;
        if (isEnrolled) {
            subscribeBtn = `<a href="${destUrl}" class="btn-join btn-enrolled-green" onclick="event.stopPropagation()">
                <i class="fas fa-check-circle"></i> أنت مشترك بالفعل
            </a>`;
        } else if (isFree) {
            subscribeBtn = `<a href="${destUrl}" class="btn-join" onclick="event.stopPropagation()">
                <i class="fas fa-play"></i> مشاهدة الكورس مجاناً !
            </a>`;
        } else {
            subscribeBtn = `<a href="${subscribeUrl}" class="btn-join" onclick="event.stopPropagation()">
                الإشتراك في الكورس !
            </a>`;
        }

        const vidBadge = course.videoCount
            ? `<span><i class="fas fa-play-circle" style="margin-left:4px;"></i>${course.videoCount} محاضرة</span>` : '';
        const gradeBadge = (course.grades && course.grades.length)
            ? `<span><i class="fas fa-graduation-cap" style="margin-left:4px;"></i>${course.grades.join('، ')}</span>` : '';

        const descSnippet = course.description
            ? `<p style="font-size:0.82rem;color:var(--gray);margin:0 0 14px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${course.description}</p>` : '';

        return `
        <div class="course-card" onclick="location.href='${destUrl}'" style="cursor:pointer;">
            <div class="course-thumb-wrap">
                <span class="${priceBadgeClass}">${priceLabelText}</span>
                <img src="${resolveImg(course.imagePath) || 'imges/st.jpg'}"
                     class="course-thumb" alt="${course.title}" loading="lazy"
                     onerror="this.onerror=null;this.src='imges/st.jpg';">
            </div>
            <div class="course-body">
                <h3 class="course-title">${course.title}</h3>
                <div class="course-meta" style="margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:0.8rem;color:var(--gray);">
                        ${vidBadge}
                        ${gradeBadge}
                    </div>
                </div>
                ${descSnippet}
                <div class="course-price" style="margin-bottom:12px;">${priceLabelText}</div>
                <div class="course-btns">
                    ${enterBtn}
                    ${subscribeBtn}
                </div>
            </div>
        </div>`;
    }).join('');
}

function renderError(title, msg) {
    document.getElementById('tpHeroSection').style.display = 'none';
    document.getElementById('tpFilterBar').style.display = 'none';
    document.getElementById('tpCoursesGrid').innerHTML = `
        <div class="tp-empty-box" style="padding:80px 20px;">
            <i class="fas fa-user-slash" style="font-size:3.5rem; color:#ef4444; margin-bottom:16px;"></i>
            <h2 style="color:var(--text-dark);">${title}</h2>
            <p>${msg}</p>
            <a href="teachers.php" class="btn-join" style="display:inline-flex; width:auto; padding:12px 30px; margin-top:20px;">
                <i class="fas fa-arrow-right"></i> تصفح جميع المعلمين
            </a>
        </div>`;
}
</script>
