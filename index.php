<?php
$page_title = "EDGE Academy";
$active_tab = "index";
$body_class = "home-page";
$extra_headers = '
    <style>
        /* Hero Banner Slideshow */
        .hero-banner { position: relative; overflow: hidden; }
        .hero-slides-track { position: relative; width: 100%; height: 100%; }
        .hero-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.7s ease; }
        .hero-slide.active { opacity: 1; position: relative; }
        .hero-slide--img {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .hero-banner-caption {
            position: absolute;
            bottom: 20px; right: 24px;
            background: rgba(0,0,0,0.55);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            backdrop-filter: blur(4px);
        }
        .hero-nav-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            z-index: 10;
            background: rgba(0,0,0,0.35);
            border: none; color: #fff;
            width: 40px; height: 40px;
            border-radius: 50%;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .hero-nav-btn:hover { background: rgba(0,0,0,0.6); }
        .hero-prev { right: 16px; }
        .hero-next { left: 16px; }
    </style>
';
include 'header.php';
?>

 <section class="main-hero-area">
    <!-- Hero Slideshow -->
    <div class="hero-banner" id="heroBanner">
        <!-- Slides Container -->
        <div class="hero-slides-track" id="heroSlidesTrack">
            <!-- الـ Default Slide -->
            <div class="hero-slide hero-slide--default active" id="defaultSlide">
                <div class="hero-text-box">
                    <div id="userGreeting" style="display: none;">
                        <h2 class="welcome-label"> </h2>
                        <h1 class="dynamic-name" id="userNamePlaceholder"></h1>
                    </div>
                    <div id="guestGreeting">
                        <h1 class="main-title"> </h1>
                    </div>
                    <p class="sub-title">   </p>
                    <div class="title-separator"></div>
                </div>
            </div>
        </div>

        <!-- أزرار التنقل -->
        <button class="hero-nav-btn hero-prev" id="heroPrev" onclick="heroSlide(-1)" style="display:none;">&#10094;</button>
        <button class="hero-nav-btn hero-next" id="heroNext" onclick="heroSlide(1)" style="display:none;">&#10095;</button>

        <!-- النقاط السفلية -->
        <div class="hero-pagination" id="heroPagination">
            <span class="hp-dot active"></span>
        </div>
    </div>
</section>

<!-- لوحة وصول المسؤول السريعة في الرئيسية -->
<div id="adminHeroBadge" style="display:none; max-width:1200px; margin: 18px auto 0 auto; padding: 0 20px;">
    <div style="background: linear-gradient(135deg, #1e293b, #0f172a); border: 1px solid #3b82f6; border-radius: 14px; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 25px rgba(0,0,0,0.15); flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa; font-size: 1.3rem;">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h4 style="margin: 0; color: #f8fafc; font-size: 1.05rem; font-weight: 700;">أهلاً بك في لوحة تحكم المسؤول (Admin)</h4>
                <p style="margin: 3px 0 0 0; color: #94a3b8; font-size: 0.85rem;">يمكنك إدارة المنصة بكل سهولة، وجميع المحاضرات والامتحانات والكورسات مفتوحة لك بالكامل للمعاينة بدون أي قيود.</p>
            </div>
        </div>
        <a href="admin.php" class="btn-fill" style="background: #2563eb; color: #fff; padding: 10px 22px; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(37,99,235,0.35);">
            <i class="fas fa-tachometer-alt"></i>
            <span>صفحة الادمن</span>
        </a>
    </div>
</div>
    

    <!-- قسم المعلمين -->
    <section class="teachers-section">
        <div class="section-container">
            <div class="teachers-section-header">
                <div class="teachers-header-text">
                    <h2 class="section-title" style="text-align:right; margin-bottom:4px;">المُعلمين المتميزين</h2>
                    <p class="section-subtitle" style="text-align:right; margin:0;">تعلم من أفضل الأساتذة المتخصصين لتصل لأعلى الدرجات</p>
                </div>
                <div class="teachers-header-actions">
                    <div class="teachers-nav-buttons">
                        <button class="teachers-nav-btn" onclick="scrollTeachers(1)" aria-label="السابق">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="teachers-nav-btn" onclick="scrollTeachers(-1)" aria-label="التالي">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                    <a href="teachers.php" class="btn-fill teachers-all-btn">
                        <span>تصفح الجميع</span>
                        <i class="fas fa-arrow-left" style="font-size:0.85rem;"></i>
                    </a>
                </div>
            </div>

            <div class="teachers-carousel-wrap">
                <div class="teachers-grid" id="teachersGrid"></div>
            </div>

            <div class="teachers-dots-container" id="teachersDots"></div>
        </div>
    </section>
<section class="subjects-section">
    <h2 class="section-title">المواد الدراسية</h2>
    
    <div class="subjects-container">
        <div class="subjects-wrapper" id="subjectsWrapper">
            <!-- الكروت ستظهر هنا بواسطة JS -->
        </div>
    </div>

    <!-- شريط التحكم السفلي للمواد الدراسية -->
<div class="subjects-controls">
    <button class="nav-btn prev" onclick="scrollSubjects(-1)" aria-label="السابق">
        <i class="fas fa-chevron-right"></i>
    </button>
    <div class="pagination-dots" id="paginationDots"></div>
    <button class="nav-btn next" onclick="scrollSubjects(1)" aria-label="التالي">
        <i class="fas fa-chevron-left"></i>
    </button>
</div>
</section>
<section class="courses-section" id="latest-videos-section" style="background-color: var(--surface-alt);">
    <div class="container">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-bottom:10px;">
            <h2 class="section-main-title" style="margin-bottom:0;">الكورسات التعليمية المتاحة</h2>
            <a href="courses.php" class="btn-fill" style="font-size:0.9rem; padding:10px 22px;">
                <i class="fas fa-th-large" style="margin-left:6px;"></i>عرض جميع الكورسات
            </a>
        </div>
        <div class="video-grid" id="latest-videos">
            <!-- الكورسات ستظهر هنا بواسطة JS -->
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
