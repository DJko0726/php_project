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
        $this->assertArrayHasKey('DPPID', $result['payload']['DPP']);
        $this->assertArrayHasKey('DPPClass', $result['payload']['DPP']);
        $this->assertArrayHasKey('PassportStartDate', $result['payload']['DPP']);
        $this->assertArrayHasKey('PassportEndDate', $result['payload']['DPP']);
        $this->assertArrayHasKey('SerialNo', $result['payload']['DPP']);
        $this->assertArrayHasKey('MftDate', $result['payload']['DPP']);
        $this->assertArrayHasKey('WarrantyDate', $result['payload']['DPP']);
        $this->assertArrayHasKey('ProdCycleStatus', $result['payload']['DPP']);
        $this->assertArrayHasKey('DPPStatus', $result['payload']['DPP']);
        //check dpp info
        $this->assertArrayHasKey('DPPInfo', $result['payload']);
        $this->assertArrayHasKey('GTIN', $result['payload']['DPPInfo']);
        $this->assertArrayHasKey('SSCC', $result['payload']['DPPInfo']);
        $this->assertArrayHasKey('BatchLot', $result['payload']['DPPInfo']);
        $this->assertArrayHasKey('TARIC', $result['payload']['DPPInfo']);
        $this->assertArrayHasKey('UniqueFacilityIdentifierDUNS', $result['payload']['DPPInfo']);
        $this->assertArrayHasKey('UniqueFacilityIdentifierGLN', $result['payload']['DPPInfo']);
        $this->assertArrayHasKey('ORIGIN', $result['payload']['DPPInfo']);
        //check productinfo
        $this->assertArrayHasKey('ProductInfo', $result['payload']);
        $this->assertArrayHasKey('ProdInfoID', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('Model', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('ProdName', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('FID', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('CCCCode', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('SpecInfo', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('CFP', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('CFV', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('PEF', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('ProdInfoLink', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('Description', $result['payload']['ProductInfo']);
        $this->assertArrayHasKey('ProdPhoto', $result['payload']['ProductInfo']);
        //check StandardCertificate
        $this->assertArrayHasKey('StandardCertificate', $result['payload']);
        $this->assertArrayHasKey('CertName', $result['payload']['StandardCertificate']);
        $this->assertArrayHasKey('CertificateNo', $result['payload']['StandardCertificate']);
        $this->assertArrayHasKey('CertificationBody', $result['payload']['StandardCertificate']);
        $this->assertArrayHasKey('CertLink', $result['payload']['StandardCertificate']);
        //check RepairabilityIndex
        $this->assertArrayHasKey('RepairabilityIndex', $result['payload']);
        $this->assertArrayHasKey('IdxA', $result['payload']['RepairabilityIndex']);
        $this->assertArrayHasKey('IdxB', $result['payload']['RepairabilityIndex']);
        $this->assertArrayHasKey('IdxC', $result['payload']['RepairabilityIndex']);
        $this->assertArrayHasKey('IdxE', $result['payload']['RepairabilityIndex']);
        $this->assertArrayHasKey('IdxF', $result['payload']['RepairabilityIndex']);
        //check Material
        $this->assertArrayHasKey('Material', $result['payload']);
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