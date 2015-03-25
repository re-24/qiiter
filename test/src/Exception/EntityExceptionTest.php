<?php
namespace re24\Qiiter\Test\Exception;

use re24\Qiiter\Exception\EntityException;

class EntityExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage testEntity does not contain a property by the name of "testProperty"
     */
    public function testnotContinPropertyThrows()
    {
        $ex = EntityException::notContinProperty('testEntity', 'testProperty');
        
        $this->assertInstanceOf('re24\Qiiter\Exception\QiitaException', $ex);
        throw $ex;
    }
}
