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

    /** @var array File display options */
    private array $options;

    /**
     * Constructor.
     *
     * @param \stored_file $file File
     * @param array $options File display options
     */
    public function __construct(\stored_file $file, array $options) {
        $this->file = $file;
        $this->options = $options;
    }

    #[\Override]
    public function export_for_template(renderer_base $output): array {
        $filename = $this->file->get_filename();
        $filenamedisplay = clean_filename($filename);

        $url = \moodle_url::make_pluginfile_url($this->file->get_contextid(), $this->file->get_component(),
            $this->file->get_filearea(), $this->file->get_itemid(), $this->file->get_filepath(), $filename);
        $image = $output->pix_icon(file_file_icon($this->file), '');

        if (!empty($this->options['forcedownload'])) {
            $url->param('forcedownload', 1);
        }

        $data = [
            'name' => $filenamedisplay,
            'icon' => $image,
            'url' => $url
        ];

        if (!empty($this->options['portfoliobutton'])) {
            $portfolioopts = $this->options['portfoliobutton'];
            $data['portfoliobutton'] = $this->get_portfolio_button($portfolioopts['class'], $portfolioopts['params'],
                $portfolioopts['component']);
        }

        if (!empty($this->options['plagiarismlinks'])) {
            $data['plagiarismlinks'] = $this->get_plagiarism_links($this->options['plagiarismlinks']);
        }

        if (!empty($this->options['filesize'])) {
            $data['filesize'] = display_size($this->file->get_filesize());
        }

        if (!empty($this->options['modifiedtime'])) {
            $data['modifiedtime'] = userdate($this->file->get_timemodified(), get_string('strftimedatetime', 'langconfig'));
        }

        return $data;
    }

    /**
     * Get the portfolio button content for this file.
     *
     * @param string $class Name of the portfolio caller class to use
     * @param array $params Arguments to pass to the portfolio caller callback functions
     * @param string $component This is the name of the component in Moodle, eg 'mod_forum'
     * @return string portfolio button HTML
     */
    protected function get_portfolio_button(string $class, array $params, string $component): string {
        global $CFG;
        if (empty($CFG->enableportfolios)) {
            return '';
        }
        require_once($CFG->libdir . '/portfoliolib.php');

        $button = new \portfolio_add_button();
        $portfolioparams = array_merge(['fileid' => $this->file->get_id()], $params);
        $button->set_callback_options($class, $portfolioparams, $component);
        $button->set_format_by_file($this->file);

        return (string) $button->to_html(PORTFOLIO_ADD_ICON_LINK);
    }

    /**
     * Get the plagiarism links for this file.
     *
     * @param array $linkarray All relevant information for the plugin to generate a link
     * @return string url to allow login/viewing of a similarity report, or empty string if plagiarism plugins are not enabled
     */
    protected function get_plagiarism_links(array $linkarray): string {
        global $CFG;
        if ($CFG->enableplagiarism) {
            require_once($CFG->libdir.'/plagiarismlib.php');
            $linkarray['file'] = $this->file;
            if (!array_key_exists('userid', $linkarray)) {
                $linkarray['userid'] = $this->file->get_userid();
            }
            $plagiarismlinks = plagiarism_get_links($linkarray);
        } else {
            $plagiarismlinks = '';
        }

        return $plagiarismlinks;
    }
}
