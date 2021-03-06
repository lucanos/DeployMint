<?php

class DeployMintMultiSite extends DeployMintAbstract
{

    public function setup()
    {
        parent::setup();

        if (is_network_admin()) {
            add_action('network_admin_menu', array($this,'adminMenu'));
        }
        if (!$this->allOptionsSet()) {
            add_action('network_admin_notices', array($this, 'showFillOptionsMessage'));
        }
    }

    public function adminMenu()
    {
        extract($this->getOptions(), EXTR_OVERWRITE);
        add_menu_page("DeployMint", "DeployMint", 'manage_network', self::PAGE_INDEX, array($this, 'actionIndex'), WP_PLUGIN_URL . '/DeployMint/images/deployMintIcon.png');
        add_submenu_page(self::PAGE_INDEX, "Manage Projects", "Manage Projects", 'manage_network', self::PAGE_PROJECTS, array($this, 'actionIndex'));
        $projects = $this->pdb->get_results("SELECT id, name FROM dep_projects WHERE deleted=0", ARRAY_A);
        for ($i = 0; $i < sizeof($projects); $i++) {
            add_submenu_page(self::PAGE_INDEX, "Proj: " . $projects[$i]['name'], "Proj: " . $projects[$i]['name'], 'manage_network', self::PAGE_PROJECTS . '/' . $projects[$i]['id'], array($this, 'actionManageProject_' . $projects[$i]['id']));
        }
        if (!$backupDisabled) {
            add_submenu_page(self::PAGE_INDEX, "Emergency Revert", "Emergency Revert", 'manage_network', self::PAGE_REVERT, array($this, 'actionRevert'));
        }
        add_submenu_page(self::PAGE_INDEX, "Options", "Options", 'manage_network', self::PAGE_OPTIONS, array($this, 'actionOptions'));
        add_submenu_page(self::PAGE_INDEX, "Help", "Help", 'manage_network', self::PAGE_HELP, array($this, 'actionHelp'));
    }

    public function initHandler()
    {
        parent::initHandler();
        if (is_admin()) {
            wp_enqueue_script('deploymint-mu-js', plugin_dir_url(__FILE__) . 'js/deploymint.mu.js', array('jquery'));
        }
    }

    protected function createSnapshot($projectId, $blogId, $name, $desc, $username=null, $password=null)
    {
        $valid = parent::createSnapshot($projectId, $blogId, $name, $desc, $username, $password);
        if ($valid) {
            return $this->doSnapshot($projectId, $blogId, $name, $desc);
        } else {
            throw new Exception("Could not create snapshot. Details could not be validated");
        }
    }

    protected function deploySnapshot($snapshot, $blogId, $projectId, $username=null, $password=null, $deployParts=array())
    {
        $valid = parent::deploySnapshot($snapshot, $blogId, $projectId, $username, $password, $deployParts);
        if ($valid) {
            return $this->doDeploySnapshot($snapshot, $blogId, $projectId, $deployParts);
        } else {
            throw new Exception("Could not deploy snapshot. Details could not be validated");
        }
    }

    protected function getTablePrefix($blogId)
    {
        if ($blogId == 1) {
            $prefix = $this->pdb->base_prefix;
        } else {
            $prefix = $this->pdb->base_prefix . $blogId . '_';
        }
        return $prefix;
    }

    protected function doSnapshot($pid, $blogid, $name, $desc)
    {
        $opt = $this->getOptions();
        extract($opt, EXTR_OVERWRITE);
        
        $proj = $this->getProject($projectId);
        $dir = $datadir . $proj['dir'] . '/';
        $mexists = $this->pdb->get_results($this->pdb->prepare("SELECT blog_id FROM dep_members WHERE blog_id=%d AND project_id=%d AND deleted=0", $blogid, $pid), ARRAY_A);
        if (sizeof($mexists) < 1) {
            $this->ajaxError("That blog doesn't exist or is not a member of this project.");
        }
        
        return parent::doSnapshot($pid, $blogid, $name, $desc);
    }

    protected function copyFilesToDataDir($blogId, $dest)
    {
        extract($this->getOptions(), EXTR_OVERWRITE);
        DeployMintTools::mexec("$rsync -rd --exclude '.git' " . WP_CONTENT_DIR . "/blogs.dir/$blogId/* $dest" . "blogs.dir/", './', null, 60);
    }

    protected function copyFilesFromDataDir($blogId, $src, $deployParts=array())
    {
        extract($this->getOptions(), EXTR_OVERWRITE);        
        if (isset($deployParts[self::DP_DIR_UPLOADS])) {
            DeployMintTools::mexec("$rsync -rd --exclude '.git' $src" . "blogs.dir/* " . WP_CONTENT_DIR . "/blogs.dir/$blogId/", './', null, 60);
        }
    }

    protected function getTablesToDeploy($projectId=0, $prefix='')
    {
        $tables = $parent->getTablesToDeploy($projectId, $prefix);
        
        $t = array();

        foreach($tables as $table) {
            switch($table) {
            case "users":
            case "usermeta":
                break;
            default:
                $t[] = $table;
            }
        }

        return $tables;
    }
}