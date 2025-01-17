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
class a_private_tree implements renderable, templatable {
    /** @var int Course module ID */
    private int $cmid;

    /** @var array The directory to create a file tree from */
    private $directory;

    /** @var bool Whether to display sub folders expanded */
    private $showexpanded = true;

    /** @var bool Whether to display the root directory */
    private $displayroot = false;

    /** @var array File display options */
    private array $options;

    //Todo: can specify more than one file area?

    /**
     * Constructor.
     *
     * @param int $cmid course module ID
     * @param string $component component
     * @param string $filearea file area
     * @param int $itemid item ID
     * @param array $options File display options
     *
     */
    public function __construct(array $filetree, int $cmid = 0, array $options = []) {
        $this->options = $options;
        $this->cmid = $cmid;
        $this->directory = $filetree;
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

    public function export_for_template(renderer_base $output) {
//        $dirs = $this->displayroot ? $this->directory : $this->directory['subdirs'];
//        $files = $this->displayroot ? [] : $this->directory['files'];
//        $elements = $this->get_tree_elements($output, ['files' => $files, 'subdirs' => $dirs]);
        $elements = $this->get_tree_elements($output, ['files' => [], 'subdirs' => [$this->directory]], true);

        return [
            'showexpanded' => $this->showexpanded,
            'displayname' => $this->displayroot,
            'content' => $elements,
            'treelabel' => s(get_string('privatefiles')),
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
     * @return array The structure to be rendered by core/accessible_tree template.
     */
    protected function get_tree_elements(renderer_base $output, array $dir, bool $isroot): array {
        global $OUTPUT;
        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return [];
        }
        $elements = [];
        foreach ($dir['subdirs'] as $subdir) {
            $image = $OUTPUT->pix_icon(file_folder_icon(), '');
            $content = $this->get_tree_elements($output, $subdir, false);
            $elements[] = [
                'name' => $subdir['dirname'],
                'icon' => $image,
                'isroot' => $isroot,
                'content' => $content,
                'hascontent' => !empty($content),
                'isdir' => true,
                'displayname' => true,
            ];
        }
        foreach ($dir['files'] as $file) {
            $data = [
                'isdir' => false,
            ];
            $filedisplay = new accessible_treeitem($file, $this->cmid, $this->options);
            $elements[] = array_merge($data, $filedisplay->export_for_template($output));
        }

        return $elements;
    }
}
