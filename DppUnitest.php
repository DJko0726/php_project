<?php
use App\Core\Common\DibiBaseModel;
use PHPUnit\Framework\TestCase;
use App\Service\DppService;

/**
* php backend/vendor/bin/phpunit --testdox -c backend/phpunit.xml --filter DppTest
 */
class DppTest extends TestCase
{

    private $testDbConfigName = 'main';
    /**
     * @var DibiBaseModel
     */
    private $dbmObj;
    private $dpp;

    protected function setUp(): void
    {
        $this->dbmObj = new DibiBaseModel($this->testDbConfigName);
        $this->dpp = new DppService();
    }
    public function testDPPList()
    {
        //request body
        $param = array(
            "page_limit" => 2,
        );
        $result = $this->dpp->List($param);
        $this->assertEquals(true, $result['success'], 'result false, message: ' . ($result['s_message'] ?? ''));
        $this->assertArrayHasKey('payload', $result);
        $this->assertArrayHasKey('records', $result['payload']);
        $this->assertArrayHasKey('page_num', $result['payload']);
        $this->assertArrayHasKey('sort', $result['payload']);
        $this->assertArrayHasKey('aggregations', $result['payload']);
    }

    public function testDPPInfo()
    {
        //request body
        $param = array(
            "DPPID" => "0116953846549610kos15921544EDG412D1524NVHE"
        );
        $result = $this->dpp->dppInfo($param);
        $this->assertEquals(true, $result['success'], 'result false, message: ' . ($result['s_message'] ?? ''));
        $this->assertArrayHasKey('payload', $result);
        //check dpp
        $this->assertArrayHasKey('DPP', $result['payload']);
        $this->assertArrayHasKey('UID', $result['payload']['DPP']);
        /*
        *skipped
        */
        //check dpp info
        $this->assertArrayHasKey('DPPInfo', $result['payload']);
        $this->assertArrayHasKey('GTIN', $result['payload']['DPPInfo']);
        /*
        *skipped
        */
        //check productinfo
        $this->assertArrayHasKey('ProductInfo', $result['payload']);
        $this->assertArrayHasKey('ProdInfoID', $result['payload']['ProductInfo']);
        /*
        *skipped
        */
        //check StandardCertificate
        $this->assertArrayHasKey('StandardCertificate', $result['payload']);
        $this->assertArrayHasKey('CertName', $result['payload']['StandardCertificate']);
        /*
        *skipped
        */
        //check RepairabilityIndex
        $this->assertArrayHasKey('RepairabilityIndex', $result['payload']);
        $this->assertArrayHasKey('IdxA', $result['payload']['RepairabilityIndex']);
        /*
        *skipped
        */
        //check Material
        $this->assertArrayHasKey('Material', $result['payload']);
        /*
        *skipped
        */
    }

    public function testDPPadd()
    {
        $param = array(
            "DPP" => array(
                /*
                * skipped
                */
            )
        );
        $result = $this->dpp->add($param);
        if ($result['success'] === true) {
            $this->assertEquals(true, $result['success'], 'result false, message: ' . ($result['s_message'] ?? ''));
            $this->assertArrayHasKey('payload', $result);
            $this->assertArrayHasKey('ProdInfoID', $result['payload']);
        }
    }

    // public function testDPPUploadFile()
    // {
    //     $_FILES['photo1'] = array(
    //         'name' => 'test.jpg',
    //         'type' => 'image/jpeg',
    //         'tmp_name' => '/tmp/test.jpg',
    //         'error' => 0,
    //         'size' => 1024
    //     );
    //     $result = $this->dpp->uploadFile();
    // }
}