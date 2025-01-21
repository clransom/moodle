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
    /** @var array The directory to create a file tree from */
    private array $directory;

    /** @var string Label for the tree */
    private string $label;

    /** @var bool Whether to display sub folders expanded */
    private bool $expandfolders = false;

    /** @var bool Whether to display the root directory */
    private bool $displayroot = false;

    /** @var array File display options */
    private array $options;

    /**
     * Constructor.
     *
     * @param array $filetree The file tree to work with (e.g. output of {@see file_storage::get_area_tree()})
     * @param string $label Aria label for the tree
     * @param array $options File display options
     */
    public function __construct(array $filetree, string $label, array $options = []) {
        $this->options = $options;
        $this->directory = $filetree;
        $this->label = $label;
    }

    /**
     * Set whether to display sub folders expanded.
     *
     * @param bool $value
     * @return void
     */
    public function expand_all_folders(bool $value): void {
        $this->expandfolders = $value;
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
        $elements = $this->get_tree_elements($output, ['files' => [], 'subdirs' => [$this->directory]], true);

        return [
            'content' => $elements,
            'treelabel' => $this->label,
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
                'displayname' => !$isroot || $this->displayroot,
                'showexpanded' => $isroot || $this->expandfolders,
            ];
        }
        foreach ($dir['files'] as $file) {
            $data = [
                'isdir' => false,
                'displayname' => true,
            ];
            $filedisplay = new file_display($file, $this->options);
            $elements[] = array_merge($data, $filedisplay->export_for_template($output));
        }

        return $elements;
    }
}
