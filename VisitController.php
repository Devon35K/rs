<?php
class VisitController {
    public function index(): void {
        $db = getDB();

        $totalVisits = (int)$db->query("SELECT COUNT(*) FROM visit_logs")->fetchColumn();
        $todayVisits = (int)$db->query(
            "SELECT COUNT(*) FROM visit_logs WHERE DATE(visited_at) = CURDATE()"
        )->fetchColumn();

        $userModel = new User();
        $totalUsers    = count($userModel->all());
        $totalFaculty  = $userModel->countByRole('faculty');
        $totalStudents = $userModel->countByRole('student');

        $announcementModel = new Announcement();
        $totalAnnouncements = $announcementModel->count();

        $memoModel   = new Memo();
        $totalMemos  = $memoModel->count();

        $docModel  = new Document();
        $totalDocs = $docModel->count();

        $recentVisits = $db->query(
            "SELECT vl.*, u.name, u.role FROM visit_logs vl 
             LEFT JOIN users u ON vl.user_id = u.id 
             ORDER BY vl.visited_at DESC LIMIT 20"
        )->fetchAll();

        $users = $userModel->all();

        require BASE_PATH . '/views/visits/index.php';
    }
}