const fs = require('fs');
const path = require('path');

const reportsFile = path.join(__dirname, '..', 'security-reports.json');

const getReports = () => {
    let securityReports = [];
    try {
        if (fs.existsSync(reportsFile)) {
            securityReports = JSON.parse(fs.readFileSync(reportsFile, 'utf8'));
        }
    } catch (e) {
        securityReports = [];
    }
    return securityReports;
};

const saveReports = (reports) => {
    try {
        fs.writeFileSync(reportsFile, JSON.stringify(reports, null, 2), 'utf8');
        return true;
    } catch (e) {
        console.error('Failed to save security reports:', e);
        return false;
    }
};

exports.report = (req, res) => {
    const reports = getReports();
    const report = { ...req.body, serverTimestamp: new Date().toISOString(), ip: req.ip };
    reports.push(report);
    
    // Keep last 1000 reports
    if (reports.length > 1000) {
        reports.splice(0, reports.length - 1000);
    }
    
    saveReports(reports);
    res.json({ success: true });
};

exports.reports = (req, res) => {
    const reports = getReports();
    // Sort from newest to oldest
    reports.sort((a, b) => {
        const ta = a.serverTimestamp || a.timestamp || '';
        const tb = b.serverTimestamp || b.timestamp || '';
        return tb.localeCompare(ta);
    });
    res.json({ success: true, reports });
};

exports.deleteAll = (req, res) => {
    if (saveReports([])) {
        res.json({ success: true, message: 'All reports cleared' });
    } else {
        res.status(500).json({ success: false, message: 'Failed to clear reports' });
    }
};

exports.unlock = (req, res) => {
    const { studentPhone, videoId } = req.body;
    if (!studentPhone) {
        return res.status(400).json({ success: false, message: 'studentPhone is required' });
    }

    let reports = getReports();
    const beforeLength = reports.length;
    
    reports = reports.filter(r => {
        const phoneMatch = (r.studentPhone || r.userId || '') === studentPhone;
        const videoMatch = videoId ? (r.videoId === videoId) : true;
        return !(phoneMatch && videoMatch);
    });

    if (saveReports(reports)) {
        res.json({
            success: true,
            message: 'Student unlocked successfully',
            studentPhone,
            videoId,
            removedCount: beforeLength - reports.length
        });
    } else {
        res.status(500).json({ success: false, message: 'Failed to unlock' });
    }
};

exports.checkLock = (req, res) => {
    const studentPhone = req.query.phone || req.body.phone || '';
    const videoId = req.query.videoId || req.body.videoId || '';

    if (!studentPhone || !videoId) {
        return res.json({ locked: false });
    }

    const reports = getReports();
    const isLocked = reports.some(r => {
        const phoneMatch = (r.studentPhone || r.userId || '') === studentPhone;
        const videoMatch = r.videoId === videoId;
        const critical = !!r.isCritical;
        return phoneMatch && videoMatch && critical;
    });

    res.json({ locked: isLocked });
};
