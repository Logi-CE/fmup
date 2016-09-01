<?php
/**
 * FileIterator.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Iterator;


class FileIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testFailWhenFileDontExists()
    {
        $iterator = new \FMUP\Import\Iterator\FileIterator();
        $this->expectException(\FMUP\Import\Exception::class);
        $this->expectExceptionMessage("Le fichier specifie n'existe pas ou est introuvable");
        $iterator->rewind();
    }

    public function testRead()
    {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'composer.json'));
        $iterator = new \FMUP\Import\Iterator\FileIterator();
        $iterator->setPath($filePath);
        $count = 0;
        foreach ($iterator as $key => $current) {
            if ($key == 1) {
                $this->assertSame(trim('"name": "femtopixel/fmup",'), trim($current));
            }
            $count++;
        }
        $this->assertSame(count(file($filePath)), $count);
    }
}
