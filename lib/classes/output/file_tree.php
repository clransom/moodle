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
    /** @var string The id for this file tree */
    private $treeid;

    /** @var array The directory to create a file tree from */
    private $directory;

    /** @var bool Whether to display sub folders expanded */
    private $showexpanded = true;

    /** @var bool Whether to force download of files, rather than showing them in the browser */
    private $forcedownload = false;

    //Todo: can specify more than one file area?

    /**
     * Constructor.
     *
     * @param int $contextid context ID
     * @param string $component component
     * @param string $filearea file area
     */
    public function __construct(string $treeid, int $contextid, string $component, string $filearea) {
        $this->treeid = $treeid;

        $fs = get_file_storage();
        $this->directory = $fs->get_area_tree($contextid, $component, $filearea, 0);
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
     * Set whether to force download of files, rather than showing them in the browser.
     *
     * @param bool $value
     * @return void
     */
    public function force_download(bool $value): void {
        $this->forcedownload = $value;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'id' => $this->treeid,
            'showexpanded' => $this->showexpanded,
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
            $filedisplay = new file_display($file);
            $filedisplay->force_download($this->forcedownload);
            $elements[] = array_merge($data, $filedisplay->export_for_template($output));
        }

        return $elements;
    }
}

//todo: is this necessary?

// Alias this class to the old name.
// This file will be autoloaded by the legacyclasses autoload system.
// In future all uses of this class will be corrected and the legacy references will be removed.
class_alias(file_tree::class, \file_tree::class);
