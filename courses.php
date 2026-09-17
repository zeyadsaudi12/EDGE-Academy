<?php
$page_title = "مكتبة الكورسات | EDGE Academy";
$active_tab = "courses";
$body_class = "courses-library-page";
$extra_headers = '
<style>
/* ═══════════════════════════════════════════
   COURSES LIBRARY PAGE
═══════════════════════════════════════════ */

.courses-library-hero {
    background: linear-gradient(135deg, var(--primary-red) 0%, #c0392b 100%);
    padding: 60px 5% 40px;
    text-align: center;
    color: #fff;
}
.courses-library-hero h1 {
    font-size: 2.2rem;
    font-weight: 900;
    margin-bottom: 10px;
}
.courses-library-hero p {
    font-size: 1.05rem;
    opacity: 0.88;
}

/* ─── Filter Bar ─── */
.courses-filter-bar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 18px 5%;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    position: sticky;
    top: 64px;
    z-index: 200;
    backdrop-filter: blur(12px);
}

.filter-label {
    font-weight: 700;
    color: var(--text-dark);
    font-size: 0.95rem;
    flex-shrink: 0;
}

.filter-chips {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}

.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 50px;
    border: 2px solid var(--border);
    background: var(--bg);
    color: var(--text-dark);
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.22s;
    white-space: nowrap;
}
.filter-chip:hover {
    border-color: var(--primary-red);
    color: var(--primary-red);
    transform: translateY(-1px);
}
.filter-chip.active {
    background: var(--primary-red);
    border-color: var(--primary-red);
    color: #fff;
    box-shadow: 0 4px 14px rgba(231, 76, 60, 0.35);
}
.filter-chip img {
    width: 22px; height: 22px;
    border-radius: 50%;
    object-fit: cover;
    background: #eee;
}

/* ─── Search box ─── */
.courses-search-wrap {
    position: relative;
    min-width: 200px;
}
.courses-search-wrap i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 0.9rem;
}
.courses-search-input {
    width: 100%;
    padding: 9px 36px 9px 14px;
    border: 2px solid var(--border);
    border-radius: 50px;
    background: var(--bg);
    color: var(--text-dark);
    font-size: 0.88rem;
    font-family: inherit;
    transition: border-color 0.2s;
    outline: none;
}
.courses-search-input:focus {
    border-color: var(--primary-red);
}

/* ─── Results Info ─── */
.courses-results-info {
    padding: 20px 5% 10px;
    color: var(--gray);
    font-size: 0.9rem;
    font-weight: 600;
}
.courses-results-info span {
    color: var(--primary-red);
    font-weight: 800;
}

/* ─── Grid ─── */
.courses-library-grid {
    padding: 10px 5% 60px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 24px;
}

/* Course card — override to full-width grid style */
.courses-library-grid .course-card {
    flex: unset !important;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: transform 0.25s, box-shadow 0.25s;
    display: flex;
    flex-direction: column;
}
.courses-library-grid .course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}

.course-card-teacher-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px 0;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--gray);
}
.course-card-teacher-badge img {
    width: 24px; height: 24px;
    border-radius: 50%;
    object-fit: cover;
    background: #eee;
}

/* ─── Empty state ─── */
.courses-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--gray);
    grid-column: 1 / -1;
}
.courses-empty i {
    font-size: 3.5rem;
    display: block;
    margin-bottom: 16px;
    opacity: 0.4;
}
.courses-empty h3 {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--text-dark);
}

/* ─── Skeleton ─── */
.course-skeleton {
    border-radius: 16px;
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--border);
    animation: skeleton-pulse 1.4s ease-in-out infinite;
}
.course-skeleton .sk-thumb {
    width: 100%; height: 170px;
    background: var(--border);
}
.course-skeleton .sk-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.course-skeleton .sk-line {
    height: 14px;
    border-radius: 8px;
    background: var(--border);
}
.course-skeleton .sk-line.short { width: 50%; }
@keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 600px) {
    .courses-library-hero h1 { font-size: 1.5rem; }
    .courses-filter-bar { top: 0; }
    .courses-library-grid { grid-template-columns: 1fr; }
}
</style>
';
include 'header.php';
?>

<!-- Hero -->
<section class="courses-library-hero">
    <h1><i class="fas fa-layer-group" style="margin-left:10px;"></i>مكتبة الكورسات</h1>
    <p id="courses-grade-subtitle">اختر كورسك وابدأ رحلة التعلم</p>
</section>

<!-- Filter Bar -->
<div class="courses-filter-bar" id="coursesFilterBar">
    <span class="filter-label"><i class="fas fa-chalkboard-teacher" style="margin-left:5px;"></i>فلتر المدرس:</span>
    <div class="filter-chips" id="teacherChips">
        <button class="filter-chip active" data-teacher-id="all" onclick="filterByTeacher('all', this)">
            <i class="fas fa-border-all"></i> الكل
        </button>
        <!-- Teacher chips injected by JS -->
    </div>
    <div class="courses-search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" id="coursesSearchInput" class="courses-search-input"
               placeholder="ابحث عن كورس..." oninput="applyCoursesFilter()">
    </div>
</div>

<!-- Results info -->
<p class="courses-results-info" id="coursesResultsInfo">&nbsp;</p>

<!-- Grid -->
<div class="courses-library-grid" id="coursesGrid">
    <!-- Skeletons while loading -->
    <?php for($i=0;$i<8;$i++): ?>
    <div class="course-skeleton">
        <div class="sk-thumb"></div>
        <div class="sk-body">
            <div class="sk-line"></div>
            <div class="sk-line short"></div>
            <div class="sk-line short"></div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<?php include 'footer.php'; ?>

<script>
/* ════════════════════════════════════════
   COURSES LIBRARY — SHOWS COURSES (not individual videos)
════════════════════════════════════════ */

let _allCourses   = [];
let _allTeachers  = [];
let _activeTeacher = 'all';
let _searchQuery   = '';
let _API_URL = '';

// ─── Global helpers ───
function resolveImg(path) {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    if (path.startsWith('/uploads/')) return _API_URL + path;
    return path;
}

function getEnrolledCourseIds() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    const isAdmin = currentUser && (currentUser.role === 'admin' || currentUser.phone === '01556448880' || currentUser.phone === '01234567890' || currentUser.isAdmin === true);
    if (isAdmin) {
        return _allCourses.map(c => String(c._id));
    }
    const subs = currentUser?.subscribedCourses || [];
    return subs.map(e => {
        // e could be a plain string ID or an object { courseId: '...' }
        if (typeof e === 'string') return e;
        return String(e.courseId || e._id || '');
    }).filter(Boolean);
}

(async function initCoursesPage() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
    const isAdmin = currentUser && (currentUser.role === 'admin' || currentUser.phone === '01556448880' || currentUser.phone === '01234567890' || currentUser.isAdmin === true);
    const studentGrade = (!isAdmin && currentUser?.grade) ? currentUser.grade : null;

    if (isAdmin) {
        document.getElementById('courses-grade-subtitle').textContent =
            `حساب مسؤول: جميع الكورسات بجميع المراحل مفتوحة ومتاحة لك بالكامل 🛡️`;
    } else if (studentGrade) {
        document.getElementById('courses-grade-subtitle').textContent =
            `كورسات متاحة لمرحلتك: ${studentGrade}`;
    }

    _API_URL = localStorage.getItem('apiUrl') ||
        window.location.origin + (window.location.pathname.includes('/masar') ? '/masar' : '');

    try {
        const [cRes, tRes, vRes] = await Promise.all([
            fetch(`${_API_URL}/api/courses`),
            fetch(`${_API_URL}/api/teachers`),
            fetch(`${_API_URL}/api/videos`)
        ]);

        let allCourses  = cRes.ok ? await cRes.json() : [];
        _allTeachers = tRes.ok ? await tRes.json() : [];
        const allVideos  = vRes.ok ? await vRes.json() : [];

        // إذا مفيش كورسات في DB، حوّل الفيديوهات المستقلة لبطاقات كورس وعرّضها
        if (allCourses.length === 0 && allVideos.length > 0) {
            allCourses = allVideos.map(v => ({
                _id: v._id,
                title: v.title,
                description: v.playlistName || '',
                price: v.price,
                teacherId: v.teacherId,
                grades: v.grades || [],
                imagePath: v.imagePath || '',
                hidden: v.hidden || false,
                videoCount: 1,
                _isVideo: true  // علامة إن ده فيديو مش كورس حقيقي
            }));
        }

        // فلترة الكورسات المظهرة حسب المرحلة وإخفاء المخفية (المسؤول يرى كافة الكورسات)
        _allCourses = allCourses.filter(c => {
            if (!isAdmin && c.hidden) return false;
            if (!isAdmin && studentGrade && c.grades && c.grades.length > 0) {
                return c.grades.includes(studentGrade);
            }
            return true;
        });

    } catch(e) {
        _allCourses  = [];
        _allTeachers = [];
    }

    buildTeacherChips();
    renderCoursesGrid();
})();

/* ─── Build teacher filter chips ─── */
function buildTeacherChips() {
    const chipsContainer = document.getElementById('teacherChips');
    const teacherIdsWithCourses = new Set(_allCourses.map(c => c.teacherId).filter(Boolean));
    const relevantTeachers = _allTeachers.filter(t => teacherIdsWithCourses.has(t._id));

    relevantTeachers.forEach(teacher => {
        const chip = document.createElement('button');
        chip.className = 'filter-chip';
        chip.dataset.teacherId = teacher._id;
        chip.onclick = () => filterByTeacher(teacher._id, chip);
        const img = (teacher.image || teacher.imagePath)
            ? `<img src="${resolveImg(teacher.image || teacher.imagePath)}" alt="${teacher.name}" onerror="this.style.display='none'">`
            : `<i class="fas fa-user-circle"></i>`;
        chip.innerHTML = `${img} ${teacher.name}`;
        chipsContainer.appendChild(chip);
    });
}

function filterByTeacher(teacherId, chipEl) {
    _activeTeacher = teacherId;
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    chipEl.classList.add('active');
    renderCoursesGrid();
}

function applyCoursesFilter() {
    _searchQuery = document.getElementById('coursesSearchInput').value.trim().toLowerCase();
    renderCoursesGrid();
}

function renderCoursesGrid() {
    const grid = document.getElementById('coursesGrid');
    const enrolledCourseIds = getEnrolledCourseIds();

    let filtered = _allCourses;
    if (_activeTeacher !== 'all') {
        filtered = filtered.filter(c => c.teacherId === _activeTeacher);
    }
    if (_searchQuery) {
        filtered = filtered.filter(c =>
            (c.title || '').toLowerCase().includes(_searchQuery) ||
            (c.description || '').toLowerCase().includes(_searchQuery)
        );
    }

    const infoEl = document.getElementById('coursesResultsInfo');
    infoEl.innerHTML = `تم العثور على <span>${filtered.length}</span> كورس`;

    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="courses-empty">
                <i class="fas fa-search-minus"></i>
                <h3>لا توجد نتائج</h3>
                <p>جرّب تغيير الفلتر أو كلمة البحث</p>
            </div>`;
        return;
    }

    grid.innerHTML = filtered.map(course => {
        const isEnrolled = enrolledCourseIds.includes(String(course._id));
        // الدخول للكورس دايمًا يفتح صفحة الكورس واستعراض محتواه
        const destUrl = `course-view.php?id=${course._id}`;
        const subscribeUrl = isEnrolled ? destUrl : `course-view.php?id=${course._id}&activate=1`;

        const isFree = (course.price === null || course.price === undefined);
        const priceLabelText = isFree
            ? 'مجاني'
            : (course.price > 0 ? `${course.price} جنية` : 'مجاني بكود');

        const priceBadgeClass = isFree ? 'course-price-badge-overlay free' : 'course-price-badge-overlay';

        // ─── الزرارين:
        // الزرار العلوي: "الدخول للكورس" (يفتح صفحة الكورس لرؤية المحاضرات)
        const enterBtn = `<a href="${destUrl}" class="btn-enter" onclick="event.stopPropagation()">الدخول للكورس</a>`;

        // الزرار السفلي: "الإشتراك في الكورس !" (يفتح نافذة إدخال الكود) أو "أنت مشترك"
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

        const teacher = _allTeachers.find(t => String(t._id) === String(course.teacherId));
        const teacherName = teacher ? teacher.name : '';
        const teacherSubject = teacher ? (teacher.subjectAr || '') : '';
        const teacherBadge = teacher ? `
            <div class="course-card-teacher-badge" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:0.83rem;color:var(--gray);">
                <img src="${resolveImg(teacher.image || teacher.imagePath)}" alt="${teacher.name}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;"
                     onerror="this.onerror=null;this.style.display='none'">
                <span>${teacherName} ${teacherSubject ? `(${teacherSubject})` : ''}</span>
            </div>` : '';

        const vidCount = course.videoCount
            ? `<span><i class="fas fa-play-circle" style="margin-left:4px;"></i>${course.videoCount} محاضرة</span>` : '';
        const gradeText = (course.grades && course.grades.length)
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
                ${teacherBadge}
                <h3 class="course-title">${course.title}</h3>
                
                <div class="course-meta" style="margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:0.8rem;color:var(--gray);">
                        ${vidCount}
                        ${gradeText}
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
</script>
