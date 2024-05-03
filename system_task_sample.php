<?php

require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Db\Db;
use App\Utility\Utility;

/*
 * php backend/system_tasks/migrateMetadataTable.php -t table -n limit 一定要給table 若沒給-n預設為全部執行
 * php backend/system_tasks/migrateMetadataTable.php -t table -p pid 若給pid 則指定pid進行更新
 * 會判斷有無pid存在 有的話就更新 沒有就新增 (更新不改動vid)
 */
// 先檢查參數是否存在

$options = getopt('t:n:p:');
if (empty($options['t']) || !in_array($options['t'], ['package', 'package_extra', 'package_nop', 'resource', 'all'])) {
    echo 'Please provide a currect table';
    exit;
}

(new MigrateLegacyData($options))->main();

class MigrateLegacyData
{
    public function __construct($options)
    {
        $this->db = Db::getInstance('main');
        $this->db_legacy = Db::getInstance('legacy');
        $this->verify_mapping = array(//legacy value=>ref value
            '0' => '1',//填報中
            '1' => '4',//審核中
            '2' => '5',//審核退回
            '9' => '2',//通過
        );
        $this->check_frequency_mapping = array(//legacy value=>ref value
            'tenyears' => '每10年',
            'fiveyears' => '每5年',
            'fouryears' => '每4年',
            'annually' => '每年',
            'halfyear' => '每半年',
            'seasonly' => '每季',
            'twomonths' => '每2月',
            'monthly' => '每月',
            'tendays' => '每10日',
            'weekly' => '每周',
            'everyday' => '每日',
        );
        $this->languqge_mapping = array(//legacy value=>ref value
            '中文' => 'zh',
            '英文' => 'en',
            '日文' => 'ja',
            '韓文' => 'ko',
        );
        $this->table = $options['t'];
        $this->limit = !empty($options['n']) && is_numeric($options['n']) ? $options['n'] : 'all';
        $this->pid = !empty($options['p']) ? $options['p'] : '';
        $this->countUpdate = 0;
        $this->countInsert = 0;
        $this->countFailed = 0;
        $this->date = date('Y-m-d H:i:s');
    }

    public function main()
    {
        $start_time = microtime(true);
        //執行主程式
        $this->migrate();

        $end_time = microtime(true);
        //計算執行時間
        $execution_time = ($end_time - $start_time);

        //echo log
        echo "Datetime: ".$this->date."\n";
        echo "Table: ".$this->table."\n";
        echo "Insert: ".$this->countInsert."\n";
        echo "Update: ".$this->countUpdate."\n";
        echo "Failed: ".$this->countFailed."\n";
        echo "Execution time: " . $execution_time . " seconds\n";
        echo 'Migrate completed!';
    }

    private function migrate()
    {
        //legacy meta_basic & ndc_quality資料
        if ($this->pid) {//指定pid
            $mb = $this->db_legacy->fetchAll('SELECT * FROM meta_basic WHERE PK = %s', $this->pid);
            $pk_nop = $this->db_legacy->fetchAll('SELECT 
                        mb.PK,
                        mb.DATASET_ID,
                        mb.POST_NDC_DATE,
                        mb.IS_UPLOAD_NDC,
                        mb.UPLOAD_METHOD, 
                        nq.QUALITY_RESULT_TIME,
                        nq.QUALITY_RESULT,
                        mss.SUM_VIEW,
                        mss.SUM_DOWNLOAD 
                            FROM meta_basic mb 
                            LEFT JOIN meta_sta_sum_ndc mss ON mb.PK = mss.META_PK 
                            LEFT JOIN ndc_quality nq ON mb.DATASET_ID = nq.DATASET_ID 
                        WHERE mb.PK = %s', $this->pid);
        } elseif ($this->limit == 'all') {
            $mb = $this->db_legacy->fetchAll('SELECT * FROM meta_basic');
            $pk_nop = $this->db_legacy->fetchAll('SELECT 
                        mb.PK,
                        mb.DATASET_ID,
                        mb.POST_NDC_DATE,
                        mb.IS_UPLOAD_NDC,
                        mb.UPLOAD_METHOD, 
                        nq.QUALITY_RESULT_TIME,
                        nq.QUALITY_RESULT,
                        mss.SUM_VIEW,
                        mss.SUM_DOWNLOAD 
                            FROM meta_basic mb 
                            LEFT JOIN meta_sta_sum_ndc mss ON mb.PK = mss.META_PK 
                            LEFT JOIN ndc_quality nq ON mb.DATASET_ID = nq.DATASET_ID 
                        ');
        } else {
            $mb = $this->db_legacy->fetchAll('SELECT * FROM meta_basic  %lmt', $this->limit);
            $pk_nop = $this->db_legacy->fetchAll('SELECT 
                        mb.PK,
                        mb.DATASET_ID,
                        mb.POST_NDC_DATE,
                        mb.IS_UPLOAD_NDC,
                        mb.UPLOAD_METHOD, 
                        nq.QUALITY_RESULT_TIME,
                        nq.QUALITY_RESULT,
                        mss.SUM_VIEW,
                        mss.SUM_DOWNLOAD 
                            FROM meta_basic mb 
                            LEFT JOIN meta_sta_sum_ndc mss ON mb.PK = mss.META_PK 
                            LEFT JOIN ndc_quality nq ON mb.DATASET_ID = nq.DATASET_ID 
                        %lmt', $this->limit);
        }

        switch ($this->table) {
            case 'package':
                $this->processPackage($mb);
                break;
            case 'package_extra':
                $this->processPackageExtra($mb);
                break;
            case 'package_nop':
                $this->processPackageNOP($pk_nop);
                break;
            case 'resource':
                $this->processResource($mb);
                break;
            case 'all':
                $this->processPackage($mb);
                $this->processPackageExtra($mb);
                $this->processPackageNOP($pk_nop);
                $this->processResource($mb);
                break;
        }
    }

    private function processPackage($mb)
    {
        // 處理package資料
        foreach ($mb as $key => $value) {
            $param['pid'] = $value['PK'];
            $param['vid'] = $this->generateUUIDv4();
            /*
            *skipped
            */
            $param['updater'] = $value['UPDATE_USER'];
            $param['update_mode'] = (int) $value['UPDATE_MODE'];
            if ($this->checkPackageExist($param['pid'])) {
                $this->updatePackage($param);
                $this->countUpdate++;
                echo "<pre> package-updated <pre> " . $param['pid'] . " <pre>\n";
            } else {
                $this->insertPackage($param);
                $this->countInsert++;
                echo "<pre> package-insert <pre> " . $param['pid'] . " <pre>\n";
            }
        }
    }

    private function processPackageExtra($mb)
    {
        // 處理package_extra資料
        foreach ($mb as $key => $value) {
            $param['pid'] = $value['PK'];
            $param['vid'] = $this->generateUUIDv4();
            /*
            *skipped
            */
            $param['changed'] = $value['UPDATE_TIME'];
            $param['updater'] = $value['UPDATE_USER'];
            if ($this->checkPackageExtraExist($param['pid'])) {
                $this->updatePackageExtra($param);
                $this->countUpdate++;
                echo "<pre> package_extra-updated <pre> " . $param['pid'] . " <pre>\n";
            } else {
                $this->insertPackageExtra($param);
                $this->countInsert++;
                echo "<pre> package_extra-insert <pre> " . $param['pid'] . " <pre>\n";
            }
        }
    }

    private function processPackageNOP($pk_nop)
    {
        // 處理package_nop資料
        foreach ($pk_nop as $key => $value) {
            $param['pid'] = $value['PK'];
            /*
            *skipped
            */
            if ($this->checkPackageNOPExist($param['pid'])) {
                $this->updatePackageNOP($param);
                $this->countUpdate++;
                echo "<pre> package_nop-updated <pre> " . $param['pid'] . " <pre>\n";
            } else {
                $this->insertPackageNOP($param);
                $this->countInsert++;
                echo "<pre> package_nop-insert <pre> " . $param['pid'] . " <pre>\n";
            }
        }
    }

    private function processResource($mb)
    {
        // 處理resource資料
        //meta_datasource_from
        foreach ($mb as $key => $value) {
            $qWhere = array(
                array('META_PK = ?', $value['PK']),
            );
            $mdf = $this->db_legacy->fetchall('SELECT * FROM meta_datasource_from WHERE %and', $qWhere);
            foreach ($mdf as $mdf_key => $mdf_value) {
                $param['rid'] = $mdf_value['PK'];
                $param['vid'] = $this->getPackageVID($value['PK']);
                /*
                *skipped
                */
                if ($this->checkResourceExist($param['rid'])) {
                    $this->updateResource($param);
                    $this->countUpdate++;
                    echo "<pre> resource-updated <pre> " . $param['rid'] . " <pre>\n";
                } else {
                    $this->insertResource($param);
                    $this->countInsert++;
                    echo "<pre> resource-insert <pre> " . $param['rid'] . " <pre>\n";
                }
            }
        }

        //meta_datasource_to
        foreach ($mb as $key => $value) {
            $qWhere = array(
                array('META_PK = ?', $value['PK']),
            );
            $mdt = $this->db_legacy->fetchall('SELECT * FROM meta_datasource_to WHERE %and', $qWhere);
            foreach ($mdt as $mdt_key => $mdt_value) {
                $param['rid'] = $mdt_value['PK'];
                $param['vid'] = $this->getPackageVID($value['PK']);
                /*
                *skipped
                */
                if ($this->checkResourceExist($param['rid'])) {
                    $this->updateResource($param);
                    $this->countUpdate++;
                    echo "<pre> resource-updated <pre> " . $param['rid'] . " <pre>\n";
                } else {
                    $this->insertResource($param);
                    $this->countInsert++;
                    echo "<pre> resource-insert <pre> " . $param['rid'] . " <pre>\n";
                }
            }
        }

        //release_datasource_from
        foreach ($mb as $key => $value) {
            $qWhere = array(
                array('META_PK = ?', $value['PK']),
            );
            $rdf = $this->db_legacy->fetchall('SELECT * FROM release_datasource_from WHERE %and', $qWhere);
            foreach ($rdf as $rdf_key => $rdf_value) {
                $param['rid'] = $rdf_value['PK'];
                $param['vid'] = $this->getPackageVID($value['PK']);
                /*
                *skipped
                */
                if ($this->checkResourceExist($param['rid'])) {
                    $this->updateResource($param);
                    $this->countUpdate++;
                    echo "<pre> resource-updated <pre> " . $param['rid'] . " <pre>\n";
                } else {
                    $this->insertResource($param);
                    $this->countInsert++;
                    echo "<pre> resource-insert <pre> " . $param['rid'] . " <pre>\n";
                }
            }
        }

        //release_datasource_to
        foreach ($mb as $key => $value) {
            $qWhere = array(
                array('META_PK = ?', $value['PK']),
            );
            $rdt = $this->db_legacy->fetchall('SELECT * FROM release_datasource_to WHERE %and', $qWhere);
            foreach ($rdt as $rdt_key => $rdt_value) {
                $param['rid'] = $rdt_value['PK'];
                $param['vid'] = $this->getPackageVID($value['PK']);
                /*
                *skipped
                */
                if ($this->checkResourceExist($param['rid'])) {
                    $this->updateResource($param);
                    $this->countUpdate++;
                    echo "<pre> resource-updated <pre> " . $param['rid'] . " <pre>\n";
                } else {
                    $this->insertResource($param);
                    $this->countInsert++;
                    echo "<pre> resource-insert <pre> " . $param['rid'] . " <pre>\n";
                }
            }
        }
    }
    private function getTaxonomyTermId($value, $vocab_name)
    {
        switch ($vocab_name) {
            case 'category':
                $qWhere[] = array('PARAM_VALUE = ?', $value);
                $mp = $this->db_legacy->fetch('SELECT PARAM_NAME FROM meta_parameter WHERE %and', $qWhere);
                $taxonomy_name = isset($mp['PARAM_NAME']) ? substr($mp['PARAM_NAME'], 4) : '';
                break;
            case 'theme':
                $qWhere[] = array('pk = ?', $value);
                $mp = $this->db_legacy->fetch('SELECT PARAM_NAME FROM meta_parameter WHERE %and', $qWhere);
                $taxonomy_name = isset($mp['PARAM_NAME']) ? substr($mp['PARAM_NAME'], 4) : '';
                break;
            case 'check frequency':    //若為check frequency 要先做mapping
                $taxonomy_name = isset($this->check_frequency_mapping[$value]) ? $this->check_frequency_mapping[$value] : '';
                $taxonomy_name == '乙類資料清單' ? $taxonomy_name = '依申請提供資料' : '';
                break;
        }

        $qWhere = array(
            array('t.name = ?', $taxonomy_name),
            array('v.name = ?', $vocab_name)
        );
        $result = $this->db->fetch('SELECT t.tid FROM taxonomy_term t JOIN vocabulary v ON t.vocabulary_id = v.id WHERE %and', $qWhere);
        return $result ? $result['tid'] : null;
    }

    private function getOrganizationID($org_ou)
    {
        $qWhere = array(
            array('org_code = ?', $org_ou),
        );
        $result = $this->db->fetch('SELECT * FROM organization WHERE %and', $qWhere);

        return $result ? $result['org_id'] : null;
    }

    private function checkPackageExist($pid)
    {
        $qWhere = array(
            array('pid = ?', $pid),
        );
        $result = $this->db->fetch('SELECT * FROM package WHERE %and', $qWhere);
        if ($result) {
            return true;
        }
    }

    private function checkPackageExtraExist($pid)
    {
        $qWhere = array(
            array('pid = ?', $pid),
        );
        $result = $this->db->fetch('SELECT * FROM package_extra WHERE %and', $qWhere);
        if ($result) {
            return true;
        }
    }

    private function checkPackageNOPExist($pid)
    {
        $qWhere = array(
            array('pid = ?', $pid),
        );
        $result = $this->db->fetch('SELECT * FROM package_nop WHERE %and', $qWhere);
        if ($result) {
            return true;
        }
    }

    private function checkResourceExist($rid)
    {
        $qWhere = array(
            array('rid = ?', $rid),
        );
        $result = $this->db->fetch('SELECT * FROM resource WHERE %and', $qWhere);
        if ($result) {
            return true;
        }
    }

    private function insertPackage($param)
    {
        try {
            $this->db->begin();
            $this->db->query(
                'INSERT INTO %n',
                'package',
                $param
            );
            $this->db->query(
                'INSERT INTO %n',
                'package_revision',
                $param
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function updatePackage($param)
    {
        try {
            $pid = $param['pid'];
            unset($param['pid']);
            unset($param['vid']);
            $this->db->begin();
            $this->db->query(
                'UPDATE %n SET %a WHERE `pid` = ?',
                'package',
                $param,
                $pid
            );
            $this->db->query(
                'UPDATE %n SET %a WHERE `pid` = ?',
                'package_revision',
                $param,
                $pid
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function insertPackageExtra($param)
    {
        try {
            $this->db->begin();
            $this->db->query(
                'INSERT INTO %n',
                'package_extra',
                $param
            );
            $this->db->query(
                'INSERT INTO %n',
                'package_extra_revision',
                $param
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function updatePackageExtra($param)
    {
        try {
            $pid = $param['pid'];
            unset($param['pid']);
            unset($param['vid']);
            $this->db->begin();
            $this->db->query(
                'UPDATE %n SET %a WHERE `pid` = ?',
                'package_extra',
                $param,
                $pid
            );
            $this->db->query(
                'UPDATE %n SET %a WHERE `pid` = ?',
                'package_extra_revision',
                $param,
                $pid
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function insertPackageNOP($param)
    {
        try {
            $this->db->begin();
            $this->db->query(
                'INSERT INTO %n',
                'package_nop',
                $param
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function updatePackageNOP($param)
    {
        try {
            $pid = $param['pid'];
            unset($param['pid']);
            $this->db->begin();
            $this->db->query(
                'UPDATE %n SET %a WHERE `pid` = ?',
                'package_nop',
                $param,
                $pid
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function insertResource($param)
    {
        try {
            $this->db->begin();
            $this->db->query(
                'INSERT INTO %n',
                'resource',
                $param
            );
            $this->db->query(
                'INSERT INTO %n',
                'resource_revision',
                $param
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function updateResource($param)
    {
        try {
            $rid = $param['rid'];
            unset($param['pid']);
            unset($param['vid']);
            unset($param['rid']);
            $this->db->begin();
            $this->db->query(
                'UPDATE %n SET %a WHERE `rid` = ?',
                'resource',
                $param,
                $rid
            );
            $this->db->query(
                'UPDATE %n SET %a WHERE `rid` = ?',
                'resource_revision',
                $param,
                $rid
            );
            $this->db->commit();
        } catch (\Dibi\Exception $e) {
            $this->db->rollback();
            $this->countFailed ++;
            throw new Exception('', $e->getMessage());
        }
    }

    private function getPackageVID($pid)
    {
        $qWhere = array(
            array('pid = ?', $pid),
        );
        $result = $this->db->fetch('SELECT * FROM package WHERE %and', $qWhere);
        if ($result) {
            return $result['vid'];
        }
    }

    private function generateUUIDv4()
    {
        return Utility::gen_uuid_v4();
    }
}
