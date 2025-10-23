<?php
/**
 * HMS Design Conversion Helper Script
 * This script helps convert existing HMS views to the unified design system
 */

class HMSDesignConverter {
    
    private $viewsPath;
    private $backupPath;
    
    public function __construct() {
        $this->viewsPath = __DIR__ . '/app/Views/';
        $this->backupPath = __DIR__ . '/backup_views/';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Convert a view file to unified design
     */
    public function convertView($viewPath) {
        $fullPath = $this->viewsPath . $viewPath;
        
        if (!file_exists($fullPath)) {
            echo "❌ File not found: $viewPath\n";
            return false;
        }
        
        // Create backup
        $this->createBackup($viewPath);
        
        // Read current content
        $content = file_get_contents($fullPath);
        
        // Apply conversions
        $content = $this->convertSidebar($content);
        $content = $this->convertFlashMessages($content);
        $content = $this->convertMetaTags($content);
        $content = $this->convertPageStructure($content);
        $content = $this->convertButtons($content);
        $content = $this->convertModals($content);
        $content = $this->addDismissFlashFunction($content);
        
        // Write updated content
        file_put_contents($fullPath, $content);
        
        echo "✅ Converted: $viewPath\n";
        return true;
    }
    
    /**
     * Create backup of original file
     */
    private function createBackup($viewPath) {
        $originalPath = $this->viewsPath . $viewPath;
        $backupPath = $this->backupPath . str_replace('/', '_', $viewPath) . '.backup';
        
        copy($originalPath, $backupPath);
        echo "📁 Backup created: $backupPath\n";
    }
    
    /**
     * Convert sidebar includes to unified sidebar
     */
    private function convertSidebar($content) {
        // Pattern to match role-specific sidebar includes
        $pattern = '/<!-- Sidebar -->\s*<?php if \(\$userRole === \'[^\']+\'\): \?>\s*<?= \$this->include\(\'[^\']+\/components\/sidebar\'\) \?>\s*(?:<?php elseif[^?]+\?>\s*<?= \$this->include\(\'[^\']+\/components\/sidebar\'\) \?>\s*)*<?php endif; \?>/s';
        
        $replacement = '<!-- Unified Sidebar -->
    <?= $this->include(\'unified/components/sidebar\') ?>';
        
        return preg_replace($pattern, $replacement, $content);
    }
    
    /**
     * Convert flash messages to unified format
     */
    private function convertFlashMessages($content) {
        // Look for existing flash message patterns and replace
        $unifiedFlashMessage = '<?php if (session()->getFlashdata(\'success\') || session()->getFlashdata(\'error\')): ?>
            <div id="flashNotice" role="alert" aria-live="polite" style="
                margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 8px;
                border: 1px solid <?= session()->getFlashdata(\'success\') ? \'#86efac\' : \'#fecaca\' ?>;
                background: <?= session()->getFlashdata(\'success\') ? \'#dcfce7\' : \'#fee2e2\' ?>;
                color: <?= session()->getFlashdata(\'success\') ? \'#166534\' : \'#991b1b\' ?>; display:flex; align-items:center; gap:0.5rem;">
                <i class="fas <?= session()->getFlashdata(\'success\') ? \'fa-check-circle\' : \'fa-exclamation-triangle\' ?>" aria-hidden="true"></i>
                <span>
                    <?= esc(session()->getFlashdata(\'success\') ?: session()->getFlashdata(\'error\')) ?>
                </span>
                <button type="button" onclick="dismissFlash()" aria-label="Dismiss notification" style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>';
        
        // Replace various flash message patterns
        $patterns = [
            '/<?php if \(session\(\)->getFlashdata\([^}]+\}\s*<?php endif; \?>/s',
            '/<div[^>]*alert[^>]*>.*?<\/div>/s'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $unifiedFlashMessage, $content, 1);
                break;
            }
        }
        
        return $content;
    }
    
    /**
     * Add required meta tags
     */
    private function convertMetaTags($content) {
        $metaTags = '    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="user-role" content="<?= esc($userRole ?? \'admin\') ?>">';
        
        // Insert after viewport meta tag
        $content = str_replace(
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n" . $metaTags,
            $content
        );
        
        return $content;
    }
    
    /**
     * Convert page structure
     */
    private function convertPageStructure($content) {
        // Add role="main" to main content area
        $content = str_replace('<main class="content">', '<main class="content" role="main">', $content);
        
        return $content;
    }
    
    /**
     * Convert buttons to unified classes
     */
    private function convertButtons($content) {
        $buttonMappings = [
            'btn-primary' => 'btn btn-primary',
            'btn-secondary' => 'btn btn-secondary',
            'btn-success' => 'btn btn-success',
            'btn-warning' => 'btn btn-warning',
            'btn-danger' => 'btn btn-danger',
        ];
        
        foreach ($buttonMappings as $old => $new) {
            $content = str_replace("class=\"$old\"", "class=\"$new\"", $content);
        }
        
        return $content;
    }
    
    /**
     * Convert modals to HMS modal structure
     */
    private function convertModals($content) {
        // Convert modal classes
        $modalMappings = [
            'modal-overlay' => 'hms-modal-overlay',
            'modal-dialog' => 'hms-modal',
            'modal-header' => 'hms-modal-header',
            'modal-title' => 'hms-modal-title',
            'modal-body' => 'hms-modal-body',
            'modal-footer' => 'hms-modal-actions',
        ];
        
        foreach ($modalMappings as $old => $new) {
            $content = str_replace("class=\"$old\"", "class=\"$new\"", $content);
        }
        
        return $content;
    }
    
    /**
     * Add dismissFlash function if not present
     */
    private function addDismissFlashFunction($content) {
        if (strpos($content, 'dismissFlash') === false) {
            $dismissFunction = '<script>
function dismissFlash() {
    const flashNotice = document.getElementById(\'flashNotice\');
    if (flashNotice) {
        flashNotice.style.display = \'none\';
    }
}
</script>';
            
            // Insert before closing body tag
            $content = str_replace('</body>', $dismissFunction . "\n</body>", $content);
        }
        
        return $content;
    }
    
    /**
     * Get list of views to convert
     */
    public function getViewsList() {
        $views = [];
        
        // Admin views
        $adminViews = glob($this->viewsPath . 'admin/*.php');
        foreach ($adminViews as $view) {
            $views[] = 'admin/' . basename($view);
        }
        
        // Doctor views
        $doctorViews = glob($this->viewsPath . 'doctor/*.php');
        foreach ($doctorViews as $view) {
            $views[] = 'doctor/' . basename($view);
        }
        
        // Other role views
        $roles = ['nurse', 'receptionist', 'pharmacist', 'laboratorist', 'accountant'];
        foreach ($roles as $role) {
            $roleViews = glob($this->viewsPath . $role . '/*.php');
            foreach ($roleViews as $view) {
                $views[] = $role . '/' . basename($view);
            }
        }
        
        return $views;
    }
    
    /**
     * Convert all views
     */
    public function convertAllViews() {
        $views = $this->getViewsList();
        $converted = 0;
        
        echo "🚀 Starting HMS Design Conversion...\n";
        echo "Found " . count($views) . " views to convert.\n\n";
        
        foreach ($views as $view) {
            if ($this->convertView($view)) {
                $converted++;
            }
        }
        
        echo "\n✨ Conversion complete!\n";
        echo "Converted: $converted/" . count($views) . " views\n";
        echo "Backups saved to: " . $this->backupPath . "\n";
    }
}

// Usage
if (php_sapi_name() === 'cli') {
    $converter = new HMSDesignConverter();
    
    if (isset($argv[1])) {
        // Convert specific view
        $converter->convertView($argv[1]);
    } else {
        // Convert all views
        $converter->convertAllViews();
    }
} else {
    echo "This script should be run from command line.\n";
    echo "Usage: php convert_to_unified_design.php [view_path]\n";
    echo "Example: php convert_to_unified_design.php admin/dashboard.php\n";
}
?>
