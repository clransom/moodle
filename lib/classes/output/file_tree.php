<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core\output;

/**
 * Data structure representing a simple form with only one button.
 *
 * @package   core
 * @category  output
 * @copyright 2024 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_tree implements renderable, templatable {
    /** @var int Course module ID */
    private int $cmid;

    /** @var array The directory to create a file tree from */
    private $directory;

    /** @var bool Whether to display sub folders expanded */
    private $showexpanded = true;

    /** @var bool Whether to display the root directory */
    private $displayroot = true;

    /** @var bool Whether to force download of files, rather than showing them in the browser */
    private $forcedownload = false;

    /** @var bool Whether to add a portfolio button to the files */
    private $addportfoliobutton = false;

    /** @var bool Whether to add plagiarism links to the files */
    private $addplagiarismlinks = false;

    /** @var bool Whether to include the modified time of the files */
    private $includemodifiedtime = false;

    //Todo: can specify more than one file area?

    /**
     * Constructor.
     *
     * @param int $cmid course module ID
     * @param string $component component
     * @param string $filearea file area
     * @param int $itemid item ID
     */
    public function __construct(int $cmid, string $component, string $filearea, int $itemid = 0) {
        $this->cmid = $cmid;
        $fs = get_file_storage();
        $this->directory = $fs->get_area_tree(\context_module::instance($cmid)->id, $component, $filearea, $itemid);
    }

    /**
     * Set whether to display sub folders expanded.
     *
     * @param bool $value
     * @return void
     */
    public function show_expanded(bool $value): void {
        $this->showexpanded = $value;
    }

    /**
     * Set whether to display the root directory.
     *
     * @param bool $value
     * @return void
     */
    public function display_root(bool $value): void {
        $this->displayroot = $value;
    }

    /**
     * Set whether to force download of files, rather than showing them in the browser.
     *
     * @param bool $value
     * @return void
     */
    public function force_download(bool $value): void {
        $this->forcedownload = $value;
    }

    /**
     * Set whether to add a portfolio button.
     *
     * @param bool $value
     * @return void
     */
    public function add_portfolio_button(bool $value): void {
        $this->addportfoliobutton = $value;
    }

    /**
     * Set whether to include plagiarism links.
     *
     * @param bool $value
     * @return void
     */
    public function add_plagiarism_links(bool $value): void {
        $this->addplagiarismlinks = $value;
    }

    /**
     * Set whether to include modified time.
     *
     * @param bool $value
     * @return void
     */
    public function include_modified_time(bool $value): void {
        $this->includemodifiedtime = $value;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'showexpanded' => $this->showexpanded,
            'displayroot' => $this->displayroot,
            'dir' => $this->get_tree_elements($output, ['files' => [], 'subdirs' => [$this->directory]]),
        ];
    }

    /**
     * Set the name to display for the root directory.
     *
     * @param string $name
     * @return void
     */
    public function set_root_name(string $name): void {
        $this->directory['dirname'] = $name;
    }

    /**
     * Internal function - Creates elements structure suitable for core/file_tree template.
     *
     * @param array $dir The subdir and files structure to convert into a tree.
     * @return array The structure to be rendered by core/file_tree template.
     */
    protected function get_tree_elements(renderer_base $output, array $dir): array {
        global $OUTPUT;
        if (empty($dir['subdirs']) && empty($dir['files'])) {
            return [];
        }
        $elements = [];
        foreach ($dir['subdirs'] as $subdir) {
            $htmllize = $this->get_tree_elements($output, $subdir);
            $image = $OUTPUT->pix_icon(file_folder_icon(), $subdir['dirname'], 'moodle');
            $elements[] = [
                'name' => $subdir['dirname'],
                'icon' => $image,
                'subdirs' => $htmllize,
                'hassubdirs' => !empty($htmllize),
            ];
        }
        foreach ($dir['files'] as $file) {
            $data = [
                'subdirs' => null,
                'hassubdirs' => false,
            ];
            $filedisplay = new file_display($file, $this->cmid);
            $filedisplay->force_download($this->forcedownload);
            $filedisplay->add_portfolio_button($this->addportfoliobutton);
            $filedisplay->add_plagiarism_links($this->addplagiarismlinks);
            $filedisplay->include_modified_time($this->includemodifiedtime);
            $elements[] = array_merge($data, $filedisplay->export_for_template($output));
        }

        return $elements;
    }
}
