<?php

class CleanParamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @link https://help.yandex.ru/webmaster/controlling-robot/robots-txt.xml#clean-param
     *
     * @dataProvider generateDataForTest
     * @covers       RobotsTxtParser::isDisallowed
     * @covers       RobotsTxtParser::getCleanParam
     * @param string $robotsTxtContent
     */
    public function testCleanParam($robotsTxtContent)
    {
        // init parser
        $parser = new RobotsTxtParser($robotsTxtContent);
        $this->assertInstanceOf('RobotsTxtParser', $parser);

        $this->assertTrue($parser->isDisallowed("http://www.site1.com/forums/showthread.php?s=681498b9648949605&ref=parent"));
        $this->assertFalse($parser->isAllowed("http://www.site1.com/forums/showthread.php?s=681498b9648949605&ref=parent"));
        $this->assertContains('Rule match: clean-param directive', $parser->getLog());

        $this->assertArrayHasKey('/forum/showthread.php', $parser->getCleanParam());
        $this->assertEquals(array('abc'), $parser->getCleanParam()['/forum/showthread.php']);

        $this->assertArrayHasKey('/forum/*.php', $parser->getCleanParam());
        $this->assertEquals(array('sid', 'sort'), $parser->getCleanParam()['/forum/*.php']);

        $this->assertArrayHasKey('/*', $parser->getCleanParam());
        $this->assertEquals(array('someTrash', 'otherTrash'), $parser->getCleanParam()['/*']);

        // Duplicate check
        $checkURLs = $parser->checkURLs(array('http://www.site1.com/', 'http://www.site1.com/?someTrash=123'));
        $this->assertTrue($checkURLs['http://www.site1.com/?someTrash=123']['duplicate']);
        $this->assertFalse($checkURLs['http://www.site1.com/']['duplicate']);
    }

    /**
     * Generate test case data
     * @return array
     */
    public function generateDataForTest()
    {
        return array(
            array(<<<ROBOTS
User-agent: *
Disallow: Clean-param: s&ref /forum*/sh*wthread.php
Clean-param: abc /forum/showthread.php
Clean-param: sid&sort /forum/*.php
Clean-param: someTrash&otherTrash
ROBOTS
            )
        );
    }
}
