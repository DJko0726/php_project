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
                array(
                    "DPPClass" => 1,
                    "PassportStartDate" => "2023-01-01",
                    "PassportEndDate" => "2027-12-31",
                    "SerialNo" => "D0C9UINJ20P3S095",
                    "MftDate" => "2023-01-01",
                    "WarrantyDate" => "2027-12-31",
                    "ProdCycleStatus" => 1,
                    "DPPStatus" => 0
                ),
                array(
                    "DPPClass" => 1,
                    "PassportStartDate" => "2023-01-01",
                    "PassportEndDate" => "2027-12-31",
                    "SerialNo" => "D0AHTT8720P3S095",
                    "MftDate" => "2023-01-01",
                    "WarrantyDate" => "2027-12-31",
                    "ProdCycleStatus" => 1,
                    "DPPStatus" => 0
                )
            ),
            "DPPInfo" => array(
                "GTIN" => "4866280058893",
                "SSCC" => "123456789123456000",
                "BatchLot" => "abc123",
                "TARIC" => "8471300000",
                "UniqueFacilityIdentifierDUNS" => "123456789",
                "UniqueFacilityIdentifierGLN" => "1234567890123",
                "ORIGIN" => "471"
            ),
            "ProductInfo" => array(
                "Model" => "Optiplex 1950",
                "ProdName" => "產品二千號",
                "FID" => "99612345",
                "CCCCode" => "84713000008",
                "SpecInfo" => array(
                    "Type" => array(
                        "unit" => "",
                        "value" => "桌機",
                        "description" => "類型(桌機/筆電/顯卡/網卡/...)"
                    ),
                    "Product Category" => "computer"
                ),
                "CFP" => "73 kg CO2e/kWh",
                "CFV" => "2021: 13,297 ton CO2e",
                "PEF" => "PEF test",
                "ProdInfoLink" => array(
                    "ProdWebPageLink" => "https://www.acer.com/tw-zh/laptops/aspire/aspire-1",
                    "ProductManualLink" => "https://www.acer.com/tw-zh/laptops/aspire/aspire-1/manual.pdf",
                    "MaintenanceManualLink" => "https://www.acer.com/tw-zh/laptops/aspire/aspire-1/maintenance_manual.pdf"
                ),
                "Description" => "Bingo",
                "ProdPhoto" => array(
                    "imgOne" => "photo/1606121138_Image.jpg",
                    "imgTwo" => null,
                    "imgThree" => null

                ),
            ),
            "StandardCertificate" => array(
                "CertName" => "1",
                "CertificateNo" => "SCS-II-61175",
                "CertificationBody" => "BODYBODY",
                "CertLink" => "http://www.zghhjs.com/upload/UEditorImages/20230302/6381334124448596163833880.jpg"
            ),
            "RepairabilityIndex" => array(
                "IdxA" => 10,
                "IdxB" => 10,
                "IdxC" => 10,
                "IdxE" => 10,
                "IdxF" => 10
            ),
            "Material" => array(
                array(
                    "MaterType" => 1,
                    "Material" => array(
                        "composition" => "test1",
                        "weight" => "101",
                        "unit" => "101",
                        "error_value" => "101",
                        "parts" => "101",
                        "buying_time" => "2020-01-01"
                    ),
                    "Description" => "註解1"
                ),
                array(
                    "MaterType" => 3,
                    "Material" => array(
                        "composition" => "test2",
                        "weight" => "101",
                        "unit" => "101",
                        "error_value" => "101",
                        "parts" => "101",
                        "buying_time" => "2020-01-01"
                    ),
                    "Description" => "註解2"
                )
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