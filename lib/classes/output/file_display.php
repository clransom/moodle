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
class file_display implements renderable, templatable {
    private \stored_file $file;

    /** @var bool Whether to force download of files, rather than showing them in the browser */
    private $forcedownload = false;

    /**
     * Constructor.
     *
     * @param \stored_file $file file
     */
    public function __construct(\stored_file $file) {
        $this->file = $file;
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
        $filename = $this->file->get_filename();
        $filenamedisplay = clean_filename($filename);

        $url = \moodle_url::make_pluginfile_url($this->file->get_contextid(), $this->file->get_component(),
            $this->file->get_filearea(), $this->file->get_itemid(), $this->file->get_filepath(), $filename, false);
        if (file_extension_in_typegroup($filename, 'web_image')) {
            $image = $url->out(false, ['preview' => 'tinyicon', 'oid' => $this->file->get_timemodified()]);
            $image = html_writer::empty_tag('img', ['src' => $image]);
        } else {
            $image = $output->pix_icon(file_file_icon($this->file), $filenamedisplay, 'moodle');
        }

        if ($this->forcedownload) {
            $url->param('forcedownload', 1);
        }

        return [
            'name' => $filenamedisplay,
            'icon' => $image,
            'url' => $url
        ];
    }


}

//todo: is this necessary?

// Alias this class to the old name.
// This file will be autoloaded by the legacyclasses autoload system.
// In future all uses of this class will be corrected and the legacy references will be removed.
class_alias(file_display::class, \file_display::class);
