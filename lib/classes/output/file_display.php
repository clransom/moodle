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
    /** @var \stored_file File to display */
    private \stored_file $file;

    /** @var int Course module ID */
    private int $cmid;

    /** @var bool Whether to force download of files, rather than showing them in the browser */
    private $forcedownload = false;

    /** @var bool Whether to add a portfolio button */
    private $addportfoliobutton = false;

    /** @var bool Whether to add plagiarism links to the file */
    private $addplagiarismlinks = false;

    /** @var bool Whether to include the modified time of the file */
    private $includemodifiedtime = false;

    /**
     * Constructor.
     *
     * @param \stored_file $file File
     * @param int $cmid Course module ID
     */
    public function __construct(\stored_file $file, int $cmid) {
        $this->file = $file;
        $this->cmid = $cmid;
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

        $data = [
            'name' => $filenamedisplay,
            'icon' => $image,
            'url' => $url
        ];

        if ($this->addportfoliobutton) {
            $data['portfoliobutton'] = $this->get_portfolio_button();
        }

        if ($this->addplagiarismlinks) {
            $data['plagiarismlinks'] = $this->get_plagiarism_links();
        }

        if($this->includemodifiedtime) {
            $data['modifiedtime'] = userdate($this->file->get_timemodified(), get_string('strftimedatetime', 'langconfig'));
        }

        return $data;
    }

    // todo: think of better names for these functions, so they are more easily distinguished from the setters above

    /**
     * Get the portfolio button content for this file.
     *
     * @return string portfolio button HTML
     */
    protected function get_portfolio_button(): string {
        global $CFG;
        if (empty($CFG->enableportfolios)) {
            return '';
        }

        require_once($CFG->libdir . '/portfoliolib.php');

        $button = new \portfolio_add_button();
        $portfolioparams = [
            'cmid' => $this->cmid,
            'fileid' => $this->file->get_id(),
        ];
        $button->set_callback_options('assign_portfolio_caller', $portfolioparams, 'mod_assign');
        $button->set_format_by_file($this->file);

        return (string) $button->to_html(PORTFOLIO_ADD_ICON_LINK);
    }

    /**
     * Get the plagiarism links for this file.
     *
     * @return string plagiarism links HTML
     */
    protected function get_plagiarism_links(): string {
        global $CFG;
        if ($CFG->enableplagiarism) {
            require_once($CFG->libdir.'/plagiarismlib.php');
            [$course, $cm] = get_course_and_cm_from_cmid($this->cmid);
            $plagiarismlinks = plagiarism_get_links([
                'userid' => $this->file->get_userid(),
                'file' => $this->file,
                'cmid' => $this->cmid,
                'course' => $course]);
        } else {
            $plagiarismlinks = '';
        }

        return $plagiarismlinks;
    }

}
