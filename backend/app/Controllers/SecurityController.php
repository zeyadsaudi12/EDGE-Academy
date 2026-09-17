<?php

namespace App\Controllers;

use App\Core\Controller;

class SecurityController extends Controller {

    private function getReportsFilePath() {
        return __DIR__ . '/../../../security-reports.json';
    }

    // POST /api/security/report
    public function report($input) {
        $reportsFile = $this->getReportsFilePath();

        $securityReports = [];
        if (file_exists($reportsFile)) {
            $content = file_get_contents($reportsFile);
            $securityReports = json_decode($content, true) ?? [];
        }

        // إضافة IP وتوقيت السيرفر
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $report = array_merge((array)$input, [
            'serverTimestamp' => date('c'),
            'ip'              => $ip,
            '_id'             => uniqid('viol_', true),
        ]);

        $securityReports[] = $report;

        // احتفظ بآخر 1000 مخالفة فقط
        if (count($securityReports) > 1000) {
            $securityReports = array_slice($securityReports, -1000);
        }

        try {
            file_put_contents(
                $reportsFile,
                json_encode($securityReports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
            $this->success(['message' => 'Report saved']);
        } catch (\Exception $e) {
            $this->error('Failed to log report: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/security/reports
    public function reports($input) {
        $reportsFile = $this->getReportsFilePath();

        $securityReports = [];
        if (file_exists($reportsFile)) {
            $content = file_get_contents($reportsFile);
            $securityReports = json_decode($content, true) ?? [];
        }

        // رتب من الأحدث للأقدم
        usort($securityReports, function($a, $b) {
            $ta = $a['serverTimestamp'] ?? $a['timestamp'] ?? '';
            $tb = $b['serverTimestamp'] ?? $b['timestamp'] ?? '';
            return strcmp($tb, $ta);
        });

        $this->json([
            'success' => true,
            'reports' => $securityReports,
            'total'   => count($securityReports),
        ]);
    }

    // DELETE /api/security/reports  —  مسح كل السجل
    public function deleteAll($input) {
        $reportsFile = $this->getReportsFilePath();

        try {
            file_put_contents($reportsFile, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
            $this->success(['message' => 'All reports cleared']);
        } catch (\Exception $e) {
            $this->error('Failed to clear reports: ' . $e->getMessage(), 500);
        }
    }

    // POST /api/security/unlock  —  إلغاء حظر طالب من فيديو معين
    public function unlock($input) {
        $reportsFile = $this->getReportsFilePath();

        $studentPhone = trim($input['studentPhone'] ?? '');
        $videoId      = trim($input['videoId'] ?? '');

        if (!$studentPhone) {
            $this->error('studentPhone is required', 400);
            return;
        }

        // احذف كل المخالفات المرتبطة بهذا الطالب وهذا الفيديو
        $securityReports = [];
        if (file_exists($reportsFile)) {
            $content = file_get_contents($reportsFile);
            $securityReports = json_decode($content, true) ?? [];
        }

        $securityReports = array_values(array_filter($securityReports, function($r) use ($studentPhone, $videoId) {
            $phoneMatch = ($r['studentPhone'] ?? $r['userId'] ?? '') === $studentPhone;
            $videoMatch = $videoId ? (($r['videoId'] ?? '') === $videoId) : true;
            // احذف لو الاتنين متطابقين
            return !($phoneMatch && $videoMatch);
        }));

        try {
            file_put_contents(
                $reportsFile,
                json_encode($securityReports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
            $this->success([
                'message'       => 'Student unlocked successfully',
                'studentPhone'  => $studentPhone,
                'videoId'       => $videoId,
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to unlock: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/security/check-lock?phone=xxx&videoId=yyy
    // يُستدعى من الطالب عند فتح الصفحة للتحقق من حالة الحظر
    public function checkLock($input) {
        $reportsFile  = $this->getReportsFilePath();
        $studentPhone = trim($_GET['phone'] ?? $input['phone'] ?? '');
        $videoId      = trim($_GET['videoId'] ?? $input['videoId'] ?? '');

        if (!$studentPhone || !$videoId) {
            $this->json(['locked' => false]);
            return;
        }

        $securityReports = [];
        if (file_exists($reportsFile)) {
            $content = file_get_contents($reportsFile);
            $securityReports = json_decode($content, true) ?? [];
        }

        // هل فيه مخالفة حرجة (isCritical) لهذا الطالب وهذا الفيديو؟
        $isLocked = false;
        foreach ($securityReports as $r) {
            $phoneMatch = ($r['studentPhone'] ?? $r['userId'] ?? '') === $studentPhone;
            $videoMatch = ($r['videoId'] ?? '') === $videoId;
            $critical   = !empty($r['isCritical']);
            if ($phoneMatch && $videoMatch && $critical) {
                $isLocked = true;
                break;
            }
        }

        $this->json(['locked' => $isLocked]);
    }
}
