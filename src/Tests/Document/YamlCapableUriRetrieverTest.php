<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Dev\Document;

use KleijnWeb\SwaggerBundle\Document\YamlCapableUriRetriever;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlCapableUriRetrieverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canRetrieveYamlFileAsJsonString()
    {
        $retriever = new YamlCapableUriRetriever();
        $result = $retriever->retrieve('file://' . realpath('src/Tests/Functional/PetStore/app/swagger/petstore.yml'));
        $this->assertInternalType('string', $result);
        $array = json_decode($result, true);
        $this->assertNotNull($array);
        $this->assertInternalType('array', $array);
    }
}
