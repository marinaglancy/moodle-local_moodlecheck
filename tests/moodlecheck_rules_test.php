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

/**
 * This file contains unit tests for covering "moodle" PHPDoc rules.
 *
 * @package    local_moodlecheck
 * @subpackage phpunit
 * @category   phpunit
 * @copyright  2018 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die(); // Remove this to use me out from Moodle.


class local_moodlecheck_rules_testcase extends advanced_testcase {

    public function setUp() {
        global $CFG;
        parent::setUp();
        // Add the moodlecheck machinery.
        require_once($CFG->dirroot . '/local/moodlecheck/locallib.php');
        // Load all files from rules directory.
        if ($dh = opendir($CFG->dirroot. '/local/moodlecheck/rules')) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    $pathinfo = pathinfo($file);
                    if (isset($pathinfo['extension']) && $pathinfo['extension'] == 'php') {
                        require_once($CFG->dirroot. '/local/moodlecheck/rules/'. $file);
                    }
                }
            }
            closedir($dh);
        }
        // Load all rules.
        local_moodlecheck_registry::enable_all_rules();
    }

    /**
     * Verify the ::class constant is not reported as phpdoc problem.
     */
    public function test_constantclass() {
        global $PAGE;
        $output = $PAGE->get_renderer('local_moodlecheck');
        $path = new local_moodlecheck_path('local/moodlecheck/tests/fixtures/constantclass.php ', null);
        $result = $output->display_path($path, 'xml');

        // Convert results to XML Objext.
        $xmlresult = new DOMDocument();
        $xmlresult->loadXML($result);

        // Let's verify we have received a xml with file top element and 2 children.
        $expect = new DOMDocument();
        $expect->loadXML('<file name="">' .
                str_repeat('<error line="" severity="" message="" source=""/>', 2) .
                '</file>');
        $this->assertEqualXMLStructure($expect->firstChild, $xmlresult->firstChild, true);
        // Also verify that contents do not include any problem with line 42 / classesdocumented. Use simple string matching here.
        $this->assertContains('line="20"', $result);
        $this->assertContains('packagevalid', $result);
        $this->assertNotContains('line="42"', $result);
        $this->assertNotContains('classesdocumented', $result);
    }

    /**
     * Verify various phpdoc tags in general directories.
     */
    public function test_phpdoc_tags_general() {
        global $PAGE;
        $output = $PAGE->get_renderer('local_moodlecheck');
        $path = new local_moodlecheck_path('local/moodlecheck/tests/fixtures/phpdoc_tags_general.php ', null);
        $result = $output->display_path($path, 'xml');

        // Convert results to XML Objext.
        $xmlresult = new DOMDocument();
        $xmlresult->loadXML($result);

        // Let's verify we have received a xml with file top element and 2 children.
        $expect = new DOMDocument();
        $expect->loadXML('<file name="">' .
                str_repeat('<error line="" severity="" message="" source=""/>', 8) .
                '</file>');
        $this->assertEqualXMLStructure($expect->firstChild, $xmlresult->firstChild, true);
        // Also verify various bits by content.
        $this->assertContains('packagevalid', $result);
        $this->assertContains('Invalid phpdocs tag @small', $result);
        $this->assertContains('Invalid phpdocs tag @zzzing', $result);
        $this->assertContains('Invalid phpdocs tag @inheritdoc', $result);
        $this->assertContains('Incorrect path for phpdocs tag @covers', $result);
        $this->assertContains('Incorrect path for phpdocs tag @dataProvider', $result);
        $this->assertContains('Incorrect path for phpdocs tag @group', $result);
        $this->assertNotContains('@deprecated', $result);
        $this->assertNotContains('@codingStandardsIgnoreLine', $result);
    }

    /**
     * Verify various phpdoc tags in tests directories.
     */
    public function test_phpdoc_tags_tests() {
        global $PAGE;
        $output = $PAGE->get_renderer('local_moodlecheck');
        $path = new local_moodlecheck_path('local/moodlecheck/tests/fixtures/phpdoc_tags_test.php ', null);
        $result = $output->display_path($path, 'xml');

        // Convert results to XML Objext.
        $xmlresult = new DOMDocument();
        $xmlresult->loadXML($result);

        // Let's verify we have received a xml with file top element and 2 children.
        $expect = new DOMDocument();
        $expect->loadXML('<file name="">' .
                str_repeat('<error line="" severity="" message="" source=""/>', 5) .
                '</file>');
        $this->assertEqualXMLStructure($expect->firstChild, $xmlresult->firstChild, true);
        // Also verify various bits by content.
        $this->assertContains('packagevalid', $result);
        $this->assertContains('Invalid phpdocs tag @small', $result);
        $this->assertContains('Invalid phpdocs tag @zzzing', $result);
        $this->assertContains('Invalid phpdocs tag @inheritdoc', $result);
        $this->assertNotContains('Incorrect path for phpdocs tag @covers', $result);
        $this->assertNotContains('Incorrect path for phpdocs tag @dataProvider', $result);
        $this->assertNotContains('Incorrect path for phpdocs tag @group', $result);
        $this->assertNotContains('@deprecated', $result);
        $this->assertNotContains('@codingStandardsIgnoreLine', $result);
    }
}
