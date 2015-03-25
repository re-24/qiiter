<?php
namespace re24\Qiiter\Test\Exception;

use re24\Qiiter\Exception\QiitaException;

class QiitaExeptionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateException()
    {
        
        $ex = new QiitaException();
        
        $this->assertInstanceOf('\RuntimeException', $ex);
    }
}
