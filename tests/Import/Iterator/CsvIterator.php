<?php
/**
 * CsvIterator.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Iterator;


class CsvIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetSeparator()
    {
        $csv = new \FMUP\Import\Iterator\CsvIterator();
        $this->assertSame("", $csv->getPath());
        $this->assertSame(\FMUP\Import\Iterator\CsvIterator::SEMICOLON_SEPARATOR, $csv->getSeparator());
        $this->assertSame($csv, $csv->setPath(__FILE__));
        $this->assertSame(__FILE__, $csv->getPath());
        $this->assertSame($csv, $csv->setSeparator(\FMUP\Import\Iterator\CsvIterator::COMMA_SEPARATOR));
        $this->assertSame(\FMUP\Import\Iterator\CsvIterator::COMMA_SEPARATOR, $csv->getSeparator());

        $csv = new \FMUP\Import\Iterator\CsvIterator(__FILE__, \FMUP\Import\Iterator\CsvIterator::TABULATION_SEPARATOR);
        $this->assertSame(__FILE__, $csv->getPath());
        $this->assertSame(\FMUP\Import\Iterator\CsvIterator::TABULATION_SEPARATOR, $csv->getSeparator());
    }

    public function testFailWhenFileDontExists()
    {
        $iterator = new \FMUP\Import\Iterator\CsvIterator();
        $this->expectException(\FMUP\Import\Exception::class);
        $this->expectExceptionMessage("Le fichier specifie n'existe pas ou est introuvable");
        $iterator->rewind();
    }

    public function testRead()
    {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', '.files', 'test.csv'));
        $iterator = new \FMUP\Import\Iterator\CsvIterator($filePath);
        $count = 0;
        foreach ($iterator as $key => $current) {
            if ($key == 1) {
                $this->assertSame(array('value', 'value2'), $current);
            }
            $count++;
        }
        $this->assertSame(count(file($filePath)), $count);
    }
}
