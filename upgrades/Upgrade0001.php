<?php

namespace Sprint\Editor;

class Upgrade0001 extends Upgrade
{

    public function getDescription() {
        return 'Обновить блоки с картинками (1.0.1 -> 1.0.2)';
    }

    public function execute() {
        \CModule::IncludeModule('iblock');

        $aprops = array();
        $dbRes = \CIBlockProperty::GetList(array('SORT' => 'ASC'), array('USER_TYPE' => 'sprint_editor'));
        while ($aProp = $dbRes->Fetch()) {
            $aprops[] = $aProp;
        }

        $allUpdated = 0;
        foreach ($aprops as $aProp) {

            $propvalue = 'PROPERTY_' . $aProp['CODE'] . '_VALUE';
            $propcode = 'PROPERTY_' . $aProp['CODE'];

            $dbRes = \CIBlockElement::GetList(array('SORT' => 'ASC'), array(
                'IBLOCK_ID' => $aProp['IBLOCK_ID'],
                '!' . $propcode => false

            ), false, false, array(
                'ID',
                'IBLOCK_ID',
                'NAME',
                'CODE',
                $propcode
            ));


            while ($aElem = $dbRes->Fetch()) {

                if (empty($aElem[$propvalue])) {
                    continue;
                }

                $jsonValue = json_decode($aElem[$propvalue], true);

                if (json_last_error() != JSON_ERROR_NONE) {
                    continue;
                }


                $needUpdate = 0;

                foreach ($jsonValue as $key => $val) {

                    if ($val['name'] == 'image' && isset($val['image'])) {
                        $val = array(
                            'name' => 'image',
                            'file' => isset($val['image']['file']) ? $val['image']['file'] : array(),
                            'desc' => isset($val['image']['desc']) ? $val['image']['desc'] : '',
                        );
                        $needUpdate++;
                    }

                    if ($val['name'] == 'video' && isset($val['preview']['image'])) {
                        $val = array(
                            'name' => 'video',
                            'url' => $val['url'],
                            'preview' => array(
                                'file' => isset($val['preview']['image']['file']) ? $val['preview']['image']['file'] : array(),
                                'desc' => isset($val['preview']['image']['desc']) ? $val['preview']['image']['desc'] : '',
                            )
                        );
                        $needUpdate++;
                    }

                    $jsonValue[$key] = $val;
                }


                if (!$needUpdate) {
                    continue;
                }

                $jsonValue = json_encode($jsonValue);

                \CIBlockElement::SetPropertyValuesEx($aElem['ID'], $aProp['IBLOCK_ID'], array(
                    $aProp['CODE'] => $jsonValue
                ));


                $allUpdated++;

            }


        }

        $this->out('Обновлено элементов: %d', $allUpdated);

    }

}