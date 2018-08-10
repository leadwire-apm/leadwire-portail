<?php

namespace Tests\ATS\CoreBundle\Service\Parser;

use ATS\CoreBundle\Service\Parser\Parser;
use ATS\CoreBundle\Service\Parser\CsvStrategy;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use ATS\CoreBundle\Exception\InvalidParserTypeException;

class ParserTest extends TestCase
{
    public function testCSVParser()
    {
        $parser = new Parser();
        $data = "firstName,lastName,age\nJean,Dupont,30\nJeannette,Dupont,28";

        $parsed = $parser->parse(
            $data,
            Parser::CSV_TYPE,
            [
                CsvStrategy::HAS_HEADER_OPTION => true,
            ]
        );

        $this->assertCount(2, $parsed);
        $this->assertCount(3, $parsed[0]);
        $this->assertCount(3, $parsed[1]);
        $this->assertEquals('Jean', $parsed[0][0]);

        $parsed = $parser->parse(
            $data,
            Parser::CSV_TYPE,
            [
                CsvStrategy::HAS_HEADER_OPTION => false,
            ]
        );

        $this->assertCount(3, $parsed);
        $this->assertCount(3, $parsed[0]);
        $this->assertCount(3, $parsed[1]);
        $this->assertCount(3, $parsed[2]);

        $parsed = $parser->parse(
            $data,
            Parser::CSV_TYPE,
            [
                CsvStrategy::DELIMITER_OPTION => ';',
            ]
        );

        $this->assertCount(3, $parsed);
        $this->assertCount(1, $parsed[0]);
        $this->assertCount(1, $parsed[1]);
        $this->assertCount(1, $parsed[2]);
    }

    public function testJsonParse()
    {
        $parser = new Parser();
        $data = '[{"firstName":"Jean", "lastName":"Dupont"},{"firstName":"Jeannne", "lastName":"Dupont"}]';

        $parsed = $parser->parse($data, Parser::JSON_TYPE);
        $this->assertCount(2, $parsed);
        $this->assertEquals("Jean", $parsed[0]['firstName']);
        $this->assertEquals("Dupont", $parsed[1]['lastName']);
    }

    public function testUnknownParse()
    {
        $this->expectException(InvalidParserTypeException::class);
        $parser = new Parser();

        $parsed = $parser->parse("", 'unknown');
    }
}
