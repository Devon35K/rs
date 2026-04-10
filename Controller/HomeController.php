<?php
class HomeController {
    private Announcement $announcementModel;
    private Memo $memoModel;

    public function __construct() {
        $this->announcementModel = new Announcement();
        $this->memoModel = new Memo();
    }

    public function index(): void {
        $role = $_SESSION['user_role'] ?? 'guest'; // Treat unauthenticated as guest
        
        $announcements = ($role === 'admin')
            ? $this->announcementModel->all()
            // If guest, maybe show all 'student' or 'all' public ones? 
            // In AnnouncementModel, forAudience 'student' might be default public.
            : $this->announcementModel->forAudience($role === 'guest' ? 'all' : $role);

        $memos = $this->memoModel->all(); // Assuming all memos are public to view, per MemoController

        require BASE_PATH . '/views/home/index.php';
    }
}
